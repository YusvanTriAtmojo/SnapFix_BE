<?php

namespace Database\Factories;

use App\Models\Lokasi;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lokasi>
 */
class LokasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Lokasi::class;

    public function definition(): array
    {
        return [
            'id_project' => Project::factory(),
            'nama_lokasi' => fake()->unique()->word(),
        ];
    }
}
