<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User default (admin)
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'), // pakai hash
        ]);

        // Tambahan pengguna lain
        User::factory()->create([
            'name' => 'Kasir',
            'email' => 'kasir@example.com',
            'password' => Hash::make('kasir123'),
        ]);

        User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('manager123'),
        ]);

        User::factory()->create([
            'name' => 'Supervisor',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('supervisor123'),
        ]);

        // Generate dummy users tambahan
        User::factory(10)->create();

        // Generate 100 Product
        Product::factory()->count(100)->create();

        // Generate 1000 Customer
        Customer::factory()->count(1000)->create();

        // seeder role
        $this->call([
            RoleSeeder::class,
        ]);
    }
}
