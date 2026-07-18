<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Akses;
use App\Models\Project;
use App\Models\RoleAkses;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AksesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $role = Role::factory()->create([
            'nama_role' => 'Admin',
        ]);

        $project = Project::factory()->create();

        foreach ([
            'read_akses',
            'create_akses',
            'update_akses',
            'delete_akses',
        ] as $permission) {

            $akses = Akses::factory()->create([
                'nama_akses' => $permission,
            ]);

            RoleAkses::factory()->create([
                'id_role' => $role->id,
                'id_akses' => $akses->id,
            ]);
        }

        User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project->id,
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'admin@gmail.com',
            'password' => '12345678',
        ]);

        $login->assertStatus(200);

        return $login['data']['token'];
    }

    protected function authHeader()
    {
        return [
            'Authorization' => 'Bearer ' . $this->authenticate(),
        ];
    }

    public function test_index_success()
    {
        Akses::factory()->count(3)->create();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/akses');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data akses berhasil diambil',
            'status_code' => 200,
        ]);

        // 3 data factory + 4 permission yang dibuat authenticate()
        $response->assertJsonCount(7, 'data');
    }

    public function test_index_empty()
    {
        $headers = $this->authHeader();

        Akses::whereNotIn('nama_akses', [
            'read_akses',
            'create_akses',
            'update_akses',
            'delete_akses',
        ])->delete();

        $response = $this->withHeaders($headers)
            ->getJson('/api/akses');

        $response->assertStatus(200);

        $response->assertJsonCount(4, 'data');
    }
}