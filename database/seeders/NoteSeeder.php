<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Seed demo notes for the demo users.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $employeesFolder = Folder::where('name', 'Employees')->firstOrFail();
        $generalFolder = Folder::where('name', 'General')->firstOrFail();
        $important = Tag::where('name', 'important')->firstOrFail();
        $todo = Tag::where('name', 'todo')->firstOrFail();
        $meeting = Tag::where('name', 'meeting')->firstOrFail();

        $welcome = $admin->notes()->create([
            'folder_id' => $employeesFolder->id,
            'title' => 'Welcome to the team',
            'content' => 'This note is visible to the Employees group.',
        ]);
        $welcome->tags()->attach([$important->id, $meeting->id]);

        $admin->notes()->create([
            'folder_id' => $employeesFolder->id,
            'title' => 'Onboarding checklist',
            'content' => 'Setup laptop, create accounts, book induction.',
            'is_public' => true,
        ])->tags()->attach($todo->id);

        $employee->notes()->create([
            'folder_id' => $generalFolder->id,
            'title' => 'Personal notes',
            'content' => 'A private note owned by the employee.',
        ])->tags()->attach($meeting->id);
    }
}
