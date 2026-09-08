<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Seed the application's groups.
     */
    public function run(): void
    {
        foreach (['Employees', 'Management', 'Customers'] as $name) {
            Group::firstOrCreate(['name' => $name]);
        }
    }
}
