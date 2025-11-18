<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_akun'       => $this->faker->word(),
            'image'           => null,
            'harga_awal'    => $this->faker->numberBetween(20000, 100000),
            'harga_perbulan'    => $this->faker->numberBetween(20000, 100000),
            'harga_5_perbulan'  => $this->faker->numberBetween(80000, 400000),
            'harga_10_perbulan' => $this->faker->numberBetween(150000, 700000),
            'harga_pertahun'    => $this->faker->numberBetween(300000, 1200000),
            'deskripsi'       => $this->faker->sentence(),
        ];
    }
}
