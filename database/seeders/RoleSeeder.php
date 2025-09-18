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
                'description' => 'Full system access'
            ],
            [
                'name' => 'finance',
                'description' => 'Finance department access'
            ],
            [
                'name' => 'manager',
                'description' => 'manager role'
            ],
            [
                'name' => 'supervisor',
                'description' => 'supervisor role'
            ],
            [
                'name' => 'user',
                'description' => 'user public'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
