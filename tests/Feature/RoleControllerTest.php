<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_success()
    {
        Role::factory()->count(3)->create();

        $response = $this->getJson('/api/role');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data role berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_index_empty()
    {
        $response = $this->getJson('/api/role');

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => 'Tidak ada data role',
            'status_code' => 200,
            'data' => [],
        ]);
    }
}