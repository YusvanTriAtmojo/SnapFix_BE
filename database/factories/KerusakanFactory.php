<?php

namespace Database\Factories;

use App\Models\Kerusakan;
use App\Models\User;
use App\Models\Project;
use App\Models\Lokasi;
use App\Models\Fasilitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kerusakan>
 */
class KerusakanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Kerusakan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'id_project' => Project::factory(),
            'id_lokasi' => Lokasi::factory(),
            'id_fasilitas' => Fasilitas::factory(),

            'tanggal' => fake()->date(),
            'tanggal_perbaikan' => null,

            'lat_posisi' => fake()->latitude(),
            'lng_posisi' => fake()->longitude(),

            'deskripsi' => fake()->sentence(),
            'deskripsi_perbaikan' => null,

            'foto_kerusakan' => 'kerusakan.jpg',
            'foto_perbaikan' => null,

            'status' => 'pending',
        ];
    }
}
