<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Banners;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Banner>
 */
class BannersFactory extends Factory
{
    protected $model = Banners::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random = rand(10000, 99999);
        $filename = "Banners_$random.jpg";

        return [
            'id'        => Str::uuid()->toString(),
            'judul'     => $this->faker->words(2, true),
            'gambar'    => 'storage/' . $filename, // simpan path relatif ke public
            'deskripsi' => $this->faker->sentence(),
            'status'    => $this->faker->randomElement(['active', 'non-active']),
        ];
    }
}
