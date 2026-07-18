<?php

namespace Database\Factories;

use App\Models\Akses;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Akses>
 */
class AksesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_akses' => fake()->unique()->word(),
        ];
    }
}
