<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Seed a folder structure for the demo users.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $admin->folders()->create(['name' => 'Employees']);
        $admin->folders()->create(['name' => 'Management']);
        $admin->folders()->create(['name' => 'General']);
    }
}
