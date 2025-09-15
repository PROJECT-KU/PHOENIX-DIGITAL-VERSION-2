<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access'
            ],
            [
                'name' => 'finance',
                'display_name' => 'Finance',
                'description' => 'Finance department access'
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Regular user access'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
