<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Akses;
use App\Models\RoleAkses;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoleAkses>
 */

class RoleAksesFactory extends Factory
{
        /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = RoleAkses::class;

    public function definition(): array
    {
        return [
            'id_role' => Role::factory(),
            'id_akses' => Akses::factory(),
        ];
    }
}