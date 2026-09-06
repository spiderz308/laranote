<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Group;
use App\Models\Note;
use App\Models\Role;
use App\Models\User;
use App\Services\FolderAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'editor', 'viewer'] as $name) {
            Role::create(['name' => $name]);
        }
    }

    public function test_guest_cannot_access_folders_index(): void
    {
        $this->get('/folders')->assertRedirect('/login');
    }

    public function test_user_can_create_a_folder(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/folders', [
            'name' => 'Projects',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('folders', [
            'name' => 'Projects',
            'user_id' => $user->id,
            'parent_id' => null,
        ]);
    }

    public function test_folder_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/folders/create')->post('/folders', [
            'name' => '',
        ])->assertSessionHasErrors('name');
    }

    public function test_user_can_create_a_nested_folder(): void
    {
        $user = User::factory()->create();
        $parent = Folder::factory()->for($user, 'owner')->create();

        $this->actingAs($user)->post('/folders', [
            'name' => 'Sub folder',
            'parent_id' => $parent->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('folders', [
            'name' => 'Sub folder',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_user_can_rename_a_folder(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->for($user, 'owner')->create(['name' => 'Old name']);

        $this->actingAs($user)->patch("/folders/{$folder->id}", [
            'name' => 'New name',
        ])->assertRedirect();

        $this->assertSame('New name', $folder->fresh()->name);
    }

    public function test_user_can_delete_a_folder_and_notes_are_unassigned(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->for($user, 'owner')->create();
        $note = Note::factory()->for($user, 'owner')->create(['folder_id' => $folder->id]);

        $response = $this->actingAs($user)->delete("/folders/{$folder->id}");

        $response->assertRedirect('/folders');
        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
        $this->assertNull($note->fresh()->folder_id);
    }

    public function test_folder_admin_can_update_access_assignments(): void
    {
        $owner = User::factory()->create();
        $folder = Folder::factory()->for($owner, 'owner')->create();
        $employee = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Employees']);
        $viewer = Role::where('name', 'viewer')->first();
        $editor = Role::where('name', 'editor')->first();

        $response = $this->actingAs($owner)->patch("/folders/{$folder->id}", [
            'name' => $folder->name,
            'group_assignments' => [
                $group->id => ['group_id' => $group->id, 'role_id' => $viewer->id],
            ],
            'user_assignments' => [
                $employee->id => ['user_id' => $employee->id, 'role_id' => $editor->id],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('folder_group', [
            'folder_id' => $folder->id,
            'group_id' => $group->id,
            'role_id' => $viewer->id,
        ]);
        $this->assertDatabaseHas('folder_user', [
            'folder_id' => $folder->id,
            'user_id' => $employee->id,
            'role_id' => $editor->id,
        ]);
    }

    public function test_group_member_can_view_a_granted_folder(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->create();
        $folder = Folder::factory()->for($owner, 'owner')->create(['name' => 'Team folder']);
        $viewer = Role::where('name', 'viewer')->first();

        $group->users()->attach($member->id);
        $folder->groups()->attach($group->id, ['role_id' => $viewer->id]);

        $this->actingAs($member)->get("/folders/{$folder->id}")->assertOk()->assertSee('Team folder');
    }

    public function test_user_without_access_cannot_view_folder(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $folder = Folder::factory()->for($owner, 'owner')->create();

        $this->actingAs($stranger)->get("/folders/{$folder->id}")->assertForbidden();
    }

    public function test_non_admin_cannot_update_or_delete_folder(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->create();
        $folder = Folder::factory()->for($owner, 'owner')->create();
        $viewer = Role::where('name', 'viewer')->first();

        $group->users()->attach($member->id);
        $folder->groups()->attach($group->id, ['role_id' => $viewer->id]);

        $this->actingAs($member)
            ->patch("/folders/{$folder->id}", ['name' => 'Hacked'])
            ->assertForbidden();

        $this->actingAs($member)
            ->delete("/folders/{$folder->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('folders', ['id' => $folder->id, 'name' => $folder->name]);
    }

    public function test_folder_cannot_be_nested_inside_itself(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->for($user, 'owner')->create();

        $this->actingAs($user)
            ->from("/folders/{$folder->id}/edit")
            ->patch("/folders/{$folder->id}", [
                'name' => $folder->name,
                'parent_id' => $folder->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($folder->fresh()->parent_id);
    }

    public function test_folder_cannot_be_nested_inside_its_own_descendant(): void
    {
        $user = User::factory()->create();
        $root = Folder::factory()->for($user, 'owner')->create();
        $child = Folder::factory()->for($user, 'owner')->create(['parent_id' => $root->id]);
        $grandchild = Folder::factory()->for($user, 'owner')->create(['parent_id' => $child->id]);

        $this->actingAs($user)
            ->patch("/folders/{$root->id}", [
                'name' => $root->name,
                'parent_id' => $grandchild->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($root->fresh()->parent_id);
    }

    public function test_user_cannot_create_a_folder_under_an_inaccessible_folder(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $folder = Folder::factory()->for($owner, 'owner')->create();

        $this->actingAs($stranger)
            ->from('/folders/create')
            ->post('/folders', [
                'name' => 'Sneaky',
                'parent_id' => $folder->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('folders', ['name' => 'Sneaky']);
    }

    public function test_user_cannot_move_a_folder_under_an_inaccessible_folder(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $mine = Folder::factory()->for($stranger, 'owner')->create();
        $theirs = Folder::factory()->for($owner, 'owner')->create();

        $this->actingAs($stranger)
            ->patch("/folders/{$mine->id}", [
                'name' => $mine->name,
                'parent_id' => $theirs->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($mine->fresh()->parent_id);
    }

    public function test_folder_access_service_tolerates_a_pre_existing_cycle(): void
    {
        $user = User::factory()->create();
        $a = Folder::factory()->for($user, 'owner')->create();
        $b = Folder::factory()->for($user, 'owner')->create();
        $a->update(['parent_id' => $b->id]);
        $b->update(['parent_id' => $a->id]);

        $service = app(FolderAccessService::class);

        $ids = $service->foldersUnder($a);
        $this->assertCount(2, $ids);
        $this->assertSame('admin', $service->roleFor($user, $a));
        $this->assertSame('admin', $service->roleFor($user, $b));
    }
}
