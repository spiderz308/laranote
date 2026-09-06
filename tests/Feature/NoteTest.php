<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Group;
use App\Models\Note;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'editor', 'viewer'] as $name) {
            Role::create(['name' => $name]);
        }
    }

    public function test_guest_cannot_access_notes_index(): void
    {
        $this->get('/notes')->assertRedirect('/login');
    }

    public function test_user_can_create_a_note_with_tags(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->post('/notes', [
            'title' => 'My first note',
            'content' => 'Some content here',
            'tags' => "{$tag->name}, brand-new",
        ]);

        $response->assertRedirect('/notes');

        $note = Note::first();
        $this->assertSame('My first note', $note->title);
        $this->assertSame($user->id, $note->user_id);
        $this->assertTrue($note->tags->contains('name', $tag->name));
        $this->assertTrue($note->tags->contains('name', 'brand-new'));
        $this->assertNotNull($note->versions()->first());
    }

    public function test_note_title_and_content_are_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/notes/create')->post('/notes', [
            'title' => '',
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'content']);
    }

    public function test_user_can_edit_a_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user, 'owner')->create();

        $response = $this->actingAs($user)->patch("/notes/{$note->id}", [
            'title' => 'Updated title',
            'content' => 'Updated content',
            'is_public' => false,
        ]);

        $response->assertRedirect("/notes/{$note->id}");

        $this->assertSame('Updated title', $note->fresh()->title);
        $this->assertSame('Updated content', $note->fresh()->content);
    }

    public function test_user_can_delete_a_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user, 'owner')->create();

        $response = $this->actingAs($user)->delete("/notes/{$note->id}");

        $response->assertRedirect('/notes');

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_user_cannot_view_a_private_note_of_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create(['is_public' => false]);

        $this->actingAs($other)->get("/notes/{$note->id}")->assertForbidden();
    }

    public function test_user_cannot_edit_another_users_note(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create();

        $this->actingAs($other)
            ->patch("/notes/{$note->id}", ['title' => 'Hacked', 'content' => 'Changed'])
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_note(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create();

        $this->actingAs($other)->delete("/notes/{$note->id}")->assertForbidden();

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'deleted_at' => null]);
    }

    public function test_public_note_is_viewable_by_guest(): void
    {
        $owner = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create(['is_public' => true]);

        $this->get("/public/notes/{$note->id}")->assertOk()->assertSee($note->title);
    }

    public function test_private_note_is_not_viewable_by_guest(): void
    {
        $owner = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create(['is_public' => false]);

        $this->get("/public/notes/{$note->id}")->assertNotFound();
    }

    public function test_user_can_search_notes_by_title(): void
    {
        $user = User::factory()->create();
        Note::factory()->for($user, 'owner')->create(['title' => 'Meeting agenda']);
        Note::factory()->for($user, 'owner')->create(['title' => 'Grocery list']);

        $response = $this->actingAs($user)->get('/notes?search=Meeting');

        $response->assertOk()->assertSee('Meeting agenda')->assertDontSee('Grocery list');
    }

    public function test_user_can_filter_notes_by_tag(): void
    {
        $user = User::factory()->create();
        $todo = Tag::factory()->create(['name' => 'todo']);
        $idea = Tag::factory()->create(['name' => 'idea']);

        $todoNote = Note::factory()->for($user, 'owner')->create(['title' => 'Things to do']);
        $ideaNote = Note::factory()->for($user, 'owner')->create(['title' => 'A new idea']);
        $todoNote->tags()->attach($todo);
        $ideaNote->tags()->attach($idea);

        $response = $this->actingAs($user)->get("/notes?tag={$todo->id}");

        $response->assertOk()->assertSee('Things to do')->assertDontSee('A new idea');
    }

    public function test_user_notes_include_notes_from_granted_folder(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $folder = Folder::factory()->for($owner, 'owner')->create(['name' => 'Shared Team Folder']);

        $sharedNote = Note::factory()->for($owner, 'owner')->create([
            'title' => 'Visible shared note',
            'folder_id' => $folder->id,
        ]);
        Note::factory()->for($owner, 'owner')->create(['title' => 'Hidden private note']);

        $member->accessibleFolders()->attach($folder, ['role_id' => Role::where('name', 'viewer')->first()->id]);

        $response = $this->actingAs($member)->get('/notes');

        $response->assertOk()->assertSee('Visible shared note')->assertDontSee('Hidden private note');
        $this->assertTrue($sharedNote->fresh()->exists());
    }

    public function test_group_member_can_view_notes_in_group_folder(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Employees']);

        $folder = Folder::factory()->for($owner, 'owner')->create(['name' => 'HR']);
        $role = Role::where('name', 'viewer')->first();
        $folder->groups()->attach($group->id, ['role_id' => $role->id]);
        $group->users()->attach($member->id);

        Note::factory()->for($owner, 'owner')->create(['title' => 'Group note', 'folder_id' => $folder->id]);

        $this->actingAs($member)
            ->get('/notes')
            ->assertOk()
            ->assertSee('Group note');
    }

    public function test_owner_can_grant_collaborator_access(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create(['is_public' => false]);
        $editor = Role::where('name', 'editor')->first();

        $response = $this->actingAs($owner)->put("/notes/{$note->id}/collaborators", [
            'collaborators' => [
                $collaborator->id => ['user_id' => $collaborator->id, 'role_id' => $editor->id],
            ],
        ]);

        $response->assertRedirect("/notes/{$note->id}");

        $this->assertDatabaseHas('note_user', [
            'note_id' => $note->id,
            'user_id' => $collaborator->id,
            'role_id' => $editor->id,
        ]);
    }

    public function test_collaborator_can_view_a_private_note(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create([
            'title' => 'Shared secret',
            'is_public' => false,
        ]);
        $note->collaborators()->attach($collaborator->id, [
            'role_id' => Role::where('name', 'viewer')->first()->id,
        ]);

        $this->actingAs($collaborator)
            ->get("/notes/{$note->id}")
            ->assertOk()
            ->assertSee('Shared secret');
    }

    public function test_viewer_collaborator_cannot_edit_note(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create();
        $note->collaborators()->attach($collaborator->id, [
            'role_id' => Role::where('name', 'viewer')->first()->id,
        ]);

        $this->actingAs($collaborator)
            ->patch("/notes/{$note->id}", ['title' => 'Nope', 'content' => 'Nope'])
            ->assertForbidden();
    }

    public function test_editor_collaborator_can_edit_note(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create();
        $note->collaborators()->attach($collaborator->id, [
            'role_id' => Role::where('name', 'editor')->first()->id,
        ]);

        $this->actingAs($collaborator)
            ->patch("/notes/{$note->id}", ['title' => 'Edited', 'content' => 'By collaborator'])
            ->assertRedirect("/notes/{$note->id}");

        $this->assertSame('Edited', $note->fresh()->title);
    }

    public function test_collaborator_cannot_manage_collaborators_unless_admin(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create();
        $note->collaborators()->attach($editor->id, [
            'role_id' => Role::where('name', 'editor')->first()->id,
        ]);

        $this->actingAs($editor)->get("/notes/{$note->id}/share")->assertForbidden();
        $this->actingAs($editor)->put("/notes/{$note->id}/collaborators", [
            'collaborators' => [],
        ])->assertForbidden();
    }

    public function test_admin_collaborator_can_manage_collaborators(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $newbie = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create();
        $note->collaborators()->attach($admin->id, [
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);

        $this->actingAs($admin)
            ->get("/notes/{$note->id}/share")
            ->assertOk()
            ->assertSee('Share Note');

        $this->actingAs($admin)->put("/notes/{$note->id}/collaborators", [
            'collaborators' => [
                $admin->id => ['user_id' => $admin->id, 'role_id' => Role::where('name', 'admin')->first()->id],
                $newbie->id => ['user_id' => $newbie->id, 'role_id' => Role::where('name', 'viewer')->first()->id],
            ],
        ])->assertRedirect("/notes/{$note->id}");

        $this->assertDatabaseHas('note_user', [
            'note_id' => $note->id,
            'user_id' => $newbie->id,
            'role_id' => Role::where('name', 'viewer')->first()->id,
        ]);
    }

    public function test_collaborated_note_appears_in_index(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create(['title' => 'Collaborated note']);
        $note->collaborators()->attach($collaborator->id, [
            'role_id' => Role::where('name', 'viewer')->first()->id,
        ]);

        $this->actingAs($collaborator)
            ->get('/notes')
            ->assertOk()
            ->assertSee('Collaborated note');
    }

    public function test_user_can_view_a_note_version(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user, 'owner')->create(['title' => 'Current', 'content' => 'Now']);
        $version = $note->versions()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'content' => 'Old content',
        ]);

        $this->actingAs($user)
            ->get("/notes/{$note->id}/versions/{$version->id}")
            ->assertOk()
            ->assertSee('Old title')
            ->assertSee('Old content');
    }

    public function test_user_can_restore_a_version_and_a_new_version_is_saved(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user, 'owner')->create(['title' => 'Current title', 'content' => 'Current content']);
        $version = $note->versions()->create([
            'user_id' => $user->id,
            'title' => 'Original title',
            'content' => 'Original content',
        ]);
        $versionCountBefore = $note->versions()->count();

        $response = $this->actingAs($user)
            ->post("/notes/{$note->id}/versions/{$version->id}/restore");

        $response->assertRedirect("/notes/{$note->id}");

        $this->assertSame('Original title', $note->fresh()->title);
        $this->assertSame('Original content', $note->fresh()->content);
        $this->assertSame($versionCountBefore + 1, $note->versions()->count());
    }

    public function test_editor_collaborator_can_restore_a_version(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create(['title' => 'Current', 'content' => 'Now']);
        $note->collaborators()->attach($editor->id, [
            'role_id' => Role::where('name', 'editor')->first()->id,
        ]);
        $version = $note->versions()->create([
            'user_id' => $owner->id,
            'title' => 'Restored title',
            'content' => 'Restored content',
        ]);

        $this->actingAs($editor)
            ->post("/notes/{$note->id}/versions/{$version->id}/restore")
            ->assertRedirect("/notes/{$note->id}");

        $this->assertSame('Restored title', $note->fresh()->title);
    }

    public function test_user_cannot_access_a_version_from_another_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user, 'owner')->create();
        $otherNote = Note::factory()->for($user, 'owner')->create();
        $version = $otherNote->versions()->create([
            'user_id' => $user->id,
            'title' => 'Other',
            'content' => 'Other content',
        ]);

        $this->actingAs($user)
            ->get("/notes/{$note->id}/versions/{$version->id}")
            ->assertNotFound();
    }

    public function test_trash_lists_only_deleted_notes_of_the_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $deleted = Note::factory()->for($owner, 'owner')->create(['title' => 'Deleted note']);
        $deleted->delete();
        Note::factory()->for($owner, 'owner')->create(['title' => 'Active note']);
        Note::factory()->for($other, 'owner')->create(['title' => 'Foreign deleted'])->delete();

        $this->actingAs($owner)
            ->get('/notes/trash')
            ->assertOk()
            ->assertSee('Deleted note')
            ->assertDontSee('Active note')
            ->assertDontSee('Foreign deleted');
    }

    public function test_user_can_restore_a_deleted_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user, 'owner')->create(['title' => 'Gone but not lost']);
        $note->delete();

        $this->actingAs($user)
            ->post("/notes/{$note->id}/restore")
            ->assertRedirect('/notes/trash');

        $this->assertNull($note->fresh()->deleted_at);
    }

    public function test_user_can_force_delete_a_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user, 'owner')->create(['title' => 'Erase me']);
        $note->delete();

        $this->actingAs($user)
            ->delete("/notes/{$note->id}/force-delete")
            ->assertRedirect('/notes/trash');

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_user_cannot_restore_or_force_delete_another_users_note(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create();
        $note->delete();

        $this->actingAs($other)->post("/notes/{$note->id}/restore")->assertForbidden();
        $this->actingAs($other)->delete("/notes/{$note->id}/force-delete")->assertForbidden();

        $this->assertNotNull($note->fresh()->deleted_at);
    }

    public function test_user_can_filter_notes_by_folder(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->for($user, 'owner')->create(['name' => 'Inbox folder']);
        Note::factory()->for($user, 'owner')->create(['title' => 'Filed note', 'folder_id' => $folder->id]);
        Note::factory()->for($user, 'owner')->create(['title' => 'Unfiled note']);

        $response = $this->actingAs($user)->get("/notes?folder={$folder->id}");

        $response->assertOk()->assertSee('Filed note')->assertDontSee('Unfiled note');
    }

    public function test_user_can_sort_notes_by_title(): void
    {
        $user = User::factory()->create();
        Note::factory()->for($user, 'owner')->create(['title' => 'Banana']);
        Note::factory()->for($user, 'owner')->create(['title' => 'Apple']);

        $response = $this->actingAs($user)->get('/notes?sort=title_asc');

        $response->assertOk();
        $this->assertTrue(
            strpos($response->getContent(), 'Apple') < strpos($response->getContent(), 'Banana')
        );
    }

    public function test_index_marks_notes_shared_by_others(): void
    {
        $owner = User::factory()->create(['name' => 'Alice']);
        $collaborator = User::factory()->create();
        $note = Note::factory()->for($owner, 'owner')->create(['title' => 'Shared note']);
        $note->collaborators()->attach($collaborator->id, [
            'role_id' => Role::where('name', 'viewer')->first()->id,
        ]);

        $this->actingAs($collaborator)
            ->get('/notes')
            ->assertOk()
            ->assertSee('Shared note')
            ->assertSee('Alice');
    }

    public function test_user_cannot_file_a_note_into_an_inaccessible_folder(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $folder = Folder::factory()->for($owner, 'owner')->create(['name' => 'Private folder']);

        $this->actingAs($stranger)
            ->from('/notes/create')
            ->post('/notes', [
                'title' => 'Sneaky',
                'content' => 'Should be rejected',
                'folder_id' => $folder->id,
            ])
            ->assertSessionHasErrors('folder_id');

        $this->assertDatabaseMissing('notes', ['title' => 'Sneaky']);
    }

    public function test_user_cannot_move_a_note_into_an_inaccessible_folder(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $note = Note::factory()->for($stranger, 'owner')->create(['folder_id' => null]);
        $folder = Folder::factory()->for($owner, 'owner')->create();

        $this->actingAs($stranger)
            ->patch("/notes/{$note->id}", [
                'title' => $note->title,
                'content' => $note->content,
                'folder_id' => $folder->id,
            ])
            ->assertSessionHasErrors('folder_id');

        $this->assertNull($note->fresh()->folder_id);
    }

    public function test_user_can_file_a_note_into_a_folder_they_can_edit(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $folder = Folder::factory()->for($owner, 'owner')->create(['name' => 'Team folder']);
        $folder->users()->attach($editor->id, [
            'role_id' => Role::where('name', 'editor')->first()->id,
        ]);

        $this->actingAs($editor)
            ->post('/notes', [
                'title' => 'Filed',
                'content' => 'Into an editor folder',
                'folder_id' => $folder->id,
            ])
            ->assertRedirect('/notes');

        $this->assertDatabaseHas('notes', [
            'title' => 'Filed',
            'folder_id' => $folder->id,
        ]);
    }
}
