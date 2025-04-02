<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use App\Models\Employee;
use App\Models\HistoryAccess;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $role = Role::create([
            'name' => 'role_admin_room_911'
        ]);

        User::factory()->create([
            'name' => 'danielcalderon',
            'password' => bcrypt('daniel'),
            'active' => true,
            'role_id' => $role->id,
        ]);

        Department::factory(5)->create();

        Employee::factory()->create([
            'name' => 'Daniel',
            'last_name' => 'Calderon',
            'department_id' => 1,
            'user_id' => 1,
        ]);

        Employee::factory(5)->create();

        HistoryAccess::factory(5)->create();
    }
}
