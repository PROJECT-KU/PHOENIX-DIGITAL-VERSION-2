<?php

namespace Database\Seeders;

use App\Models\Lowongan;
use Illuminate\Database\Seeder;

class LowonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lowongan::updateOrCreate(['title' => 'Frontend Engineer', 'slug' => 'frontend-engineer']);
        Lowongan::updateOrCreate(['title' => 'Backend Engineer', 'slug' => 'backend-engineer']);
    }
}
