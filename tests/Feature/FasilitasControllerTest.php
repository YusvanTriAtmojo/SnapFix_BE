<?php

namespace Tests\Feature;
use App\Models\User;
use App\Models\Role;
use App\Models\Akses;
use App\Models\Kerusakan;
use App\Models\Fasilitas;
use App\Models\Project;
use App\Models\Lokasi;
use App\Models\RoleAkses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FasilitasControllerTest extends TestCase
{

use RefreshDatabase;

    protected function authenticate()
    {
        $role = Role::factory()->create([
            'nama_role' => 'Admin',
        ]);

        $project = Project::factory()->create([
            'nama_project' => 'Project Auth',
        ]);

        foreach ([
            'read_fasilitas',
            'create_fasilitas',
            'update_fasilitas',
            'delete_fasilitas',
            'create_kerusakan',
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
            'Authorization' => 'Bearer '.$this->authenticate(),
        ];
    }

    public function test_fasilitas_dropdown_success()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        Fasilitas::factory()->count(3)->create([
            'id_project' => $project->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson("/api/fasilitas/{$user->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data fasilitas berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_fasilitas_dropdown_user_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/fasilitas/999');

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'User tidak ditemukan',
            'status_code' => 404,
        ]);
    }

    public function test_fasilitas_dropdown_empty()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson("/api/fasilitas/{$user->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Tidak ada fasilitas untuk divisi ini',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(0, 'data');
    }

    public function test_index_returns_all_fasilitas()
    {
        Fasilitas::factory()->count(3)->create();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/fasilitas');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data fasilitas berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_index_filter_by_project()
    {
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        Fasilitas::factory()->count(2)->create([
            'id_project' => $project1->id,
        ]);

        Fasilitas::factory()->count(3)->create([
            'id_project' => $project2->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/fasilitas?id_project={$project2->id}");

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_index_empty()
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/fasilitas');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Tidak ada data fasilitas',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(0, 'data');
    }

    public function test_store_fasilitas_success()
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/fasilitas', [
                'id_project' => $project->id,
                'nama_fasilitas' => 'AC',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('fasilitas', [
            'id_project' => $project->id,
            'nama_fasilitas' => 'AC',
        ]);
    }

    public function test_store_duplicate_fasilitas()
    {
        $project = Project::factory()->create();

        Fasilitas::factory()->create([
            'id_project' => $project->id,
            'nama_fasilitas' => 'AC',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/fasilitas', [
                'id_project' => $project->id,
                'nama_fasilitas' => 'AC',
            ]);

        $response->assertStatus(409);
    }

    public function test_update_fasilitas_success()
    {
        $project = Project::factory()->create();

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
            'nama_fasilitas' => 'AC Lama',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/fasilitas/{$fasilitas->id}", [
                'id_project' => $project->id,
                'nama_fasilitas' => 'AC Baru',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('fasilitas', [
            'id' => $fasilitas->id,
            'nama_fasilitas' => 'AC Baru',
        ]);
    }

    public function test_update_fasilitas_not_found()
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->putJson('/api/fasilitas/999', [
                'id_project' => $project->id,
                'nama_fasilitas' => 'AC',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_duplicate_fasilitas()
    {
        $project = Project::factory()->create();

        Fasilitas::factory()->create([
            'id_project' => $project->id,
            'nama_fasilitas' => 'AC',
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
            'nama_fasilitas' => 'Lampu',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/fasilitas/{$fasilitas->id}", [
                'id_project' => $project->id,
                'nama_fasilitas' => 'AC',
            ]);

        $response->assertStatus(409);
    }

    public function test_delete_fasilitas_success()
    {
        $fasilitas = Fasilitas::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/fasilitas/{$fasilitas->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('fasilitas', [
            'id' => $fasilitas->id,
        ]);
    }

    public function test_delete_fasilitas_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->deleteJson('/api/fasilitas/999');

        $response->assertStatus(404);
    }

    public function test_delete_fasilitas_used_in_kerusakan()
    {
        $project = Project::factory()->create();

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        $role = Role::factory()->create();

        $user = User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project->id,
        ]);

        Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/fasilitas/{$fasilitas->id}");

        $response->assertStatus(400);

        $this->assertDatabaseHas('fasilitas', [
            'id' => $fasilitas->id,
        ]);
    }
}
