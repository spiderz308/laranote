<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Seed the application's tags.
     */
    public function run(): void
    {
        foreach (['todo', 'idea', 'important', 'meeting', 'reference'] as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }
    }
}
