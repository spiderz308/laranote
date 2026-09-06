<?php

namespace Database\Factories;

use App\Models\Folder;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'folder_id' => Folder::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'is_public' => false,
        ];
    }
}
