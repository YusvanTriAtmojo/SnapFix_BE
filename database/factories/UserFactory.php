<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_role' => Role::factory(),
            'id_project' => Project::factory(),
            'name' => fake()->name(),
            'nip' => fake()->unique()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'alamat' => fake()->address(),
            'notlp' => fake()->phoneNumber(),
            'password' => Hash::make('12345678'),
        ];
    }
}