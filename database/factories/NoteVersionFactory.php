<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\NoteVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NoteVersion>
 */
class NoteVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'note_id' => Note::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
        ];
    }
}
