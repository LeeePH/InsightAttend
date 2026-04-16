<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator']
        );

        Role::firstOrCreate(
            ['slug' => 'employee'],
            ['name' => 'Employee']
        );

        Role::firstOrCreate(
            ['slug' => 'staff'],
            ['name' => 'Staff']
        );

        // Create default admin only when it doesn't already exist.
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
