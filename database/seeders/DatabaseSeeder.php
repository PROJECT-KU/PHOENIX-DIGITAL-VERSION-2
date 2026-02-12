<?php

namespace Database\Seeders;

use App\Models\DataAkun;
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
        // seeder role
        $this->call([
            RoleSeeder::class,
            LowonganSeeder::class,
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $financeRole = Role::firstOrCreate(['name' => 'finance']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $customerServiceRole = Role::firstOrCreate(['name' => 'customer-service']);
        $mimin = Role::firstOrCreate(['name' => 'admin-mimin']);

        // User default (admin)
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
        ]);

        User::factory()->create([
            'name' => 'Admin Mimin',
            'email' => 'mimin@example.com',
            'password' => Hash::make('mimin123'),
            'role_id' => $mimin->id,
        ]);

        User::factory()->create([
            'name' => 'customer service',
            'email' => 'cs@example.com',
            'password' => Hash::make('customerservice123'),
            'role_id' => $customerServiceRole->id,
        ]);

        User::factory()->create([
            'name' => 'Finance',
            'email' => 'finance@example.com',
            'password' => Hash::make('finance123'),
            'role_id' => $financeRole->id,
        ]);

        User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('manager123'),
            'role_id' => $managerRole->id,
        ]);

        // Generate dummy users tambahan
        User::factory(10)->create([
            'role_id' => $userRole->id,
        ]);

        // Generate 100 Product
        DataAkun::factory()->count(100)->create();

        Product::factory()->count(100)->create();

        // promo seeder
        $this->call([
            PromoSeeder::class,
            PermissionSeeder::class,
        ]);
    }
}
