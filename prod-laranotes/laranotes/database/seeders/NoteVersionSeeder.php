<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;

class NoteVersionSeeder extends Seeder
{
    /**
     * Seed version history for the demo notes.
     */
    public function run(): void
    {
        $note = Note::firstOrFail();

        $note->versions()->create([
            'user_id' => $note->user_id,
            'title' => $note->title,
            'content' => $note->content,
        ]);
    }
}
