<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Job::updateOrCreate(['title' => 'Frontend Engineer', 'slug' => 'frontend-engineer']);
        Job::updateOrCreate(['title' => 'Backend Engineer', 'slug' => 'backend-engineer']);
    }
}
