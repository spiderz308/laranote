<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            GroupSeeder::class,
            TagSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
        ]);

        $admin->groups()->attach(Group::where('name', 'Management')->first());
        $employee->groups()->attach(Group::where('name', 'Employees')->first());

        $this->call(FolderSeeder::class);

        $employeesFolder = Folder::where('name', 'Employees')->first();
        $managementFolder = Folder::where('name', 'Management')->first();
        $employeesGroup = Group::where('name', 'Employees')->first();
        $managementGroup = Group::where('name', 'Management')->first();
        $viewer = Role::where('name', 'viewer')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $employeesFolder->groups()->attach($employeesGroup, ['role_id' => $viewer->id]);
        $managementFolder->groups()->attach($managementGroup, ['role_id' => $adminRole->id]);

        $this->call(NoteSeeder::class);
        $this->call(NoteVersionSeeder::class);
    }
}
