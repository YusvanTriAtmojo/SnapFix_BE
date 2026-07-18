<?php

namespace Database\Factories;

use App\Models\Fasilitas;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fasilitas>
 */
class FasilitasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Fasilitas::class;

    public function definition(): array
    {
        return [
            'id_project' => Project::factory(),
            'nama_fasilitas' => fake()->unique()->word(),
        ];
    }
}
