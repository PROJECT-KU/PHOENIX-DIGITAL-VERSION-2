<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class DataAkunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'nama_akun' => $this->faker->word(),
            'username_akun' => $this->faker->userName(),
            'password_akun' => $this->faker->password(),
            'link_login_akun' => $this->faker->url(),
            'pj_akun' => $this->faker->name(),
            'deskripsi' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['active', 'non-active']),
        ];
    }
}
