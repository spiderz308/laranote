<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'editor', 'viewer'] as $name) {
            Role::create(['name' => $name]);
        }
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_shows_note_stats(): void
    {
        $user = User::factory()->create();
        Note::factory()->for($user, 'owner')->count(3)->create();
        Note::factory()->for($user, 'owner')->create();
        Note::factory()->for($user, 'owner')->create()->delete();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSee('My Notes')
            ->assertSee('Trash');
    }

    public function test_dashboard_shows_recent_notes_and_shared_by_others(): void
    {
        $owner = User::factory()->create(['name' => 'Bob']);
        $user = User::factory()->create();
        Note::factory()->for($user, 'owner')->create(['title' => 'My recent note']);
        $shared = Note::factory()->for($owner, 'owner')->create(['title' => 'Shared with me']);
        $shared->collaborators()->attach($user->id, [
            'role_id' => Role::where('name', 'viewer')->first()->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSee('My recent note')
            ->assertSee('Shared with me')
            ->assertSee('Bob');
    }

    public function test_dashboard_shows_top_tags_for_the_user(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => 'work']);
        $note = Note::factory()->for($user, 'owner')->create(['title' => 'Tagged']);
        $note->tags()->attach($tag);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('#work');
    }
}
