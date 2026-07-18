<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Akses;
use App\Models\Kerusakan;
use App\Models\Fasilitas;
use App\Models\Project;
use App\Models\Lokasi;
use App\Models\RoleAkses;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LokasiControllerTest extends TestCase
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
            'read_lokasi',
            'create_lokasi',
            'update_lokasi',
            'delete_lokasi',
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

    public function test_lokasi_dropdown_success()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        Lokasi::factory()->count(3)->create([
            'id_project' => $project->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson("/api/lokasi/{$user->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data lokasi berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_lokasi_dropdown_user_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/lokasi/999');

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'User tidak ditemukan',
            'status_code' => 404,
        ]);
    }

    public function test_lokasi_dropdown_empty()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson("/api/lokasi/{$user->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Tidak ada lokasi untuk divisi ini',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(0, 'data');
    }

    public function test_index_returns_all_lokasi()
    {
        $headers = $this->authHeader();

        Lokasi::factory()->count(3)->create();

        $response = $this->withHeaders($headers)
            ->getJson('/api/lokasi');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data Lokasi berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_index_filter_by_project()
    {
        $headers = $this->authHeader();

        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        Lokasi::factory()->count(2)->create([
            'id_project' => $project1->id,
        ]);

        Lokasi::factory()->count(3)->create([
            'id_project' => $project2->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson("/api/lokasi?id_project={$project2->id}");

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_index_empty()
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/lokasi');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Tidak ada data Lokasi',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(0, 'data');
    }

        public function test_store_lokasi_success()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $response = $this->withHeaders($headers)
            ->postJson('/api/lokasi', [
                'id_project' => $project->id,
                'nama_lokasi' => 'Gudang A',
            ]);

        $response->assertStatus(201);

        $response->assertJson([
            'message' => 'Lokasi berhasil ditambahkan',
            'status_code' => 201,
        ]);

        $this->assertDatabaseHas('lokasi', [
            'id_project' => $project->id,
            'nama_lokasi' => 'Gudang A',
        ]);
    }

    public function test_store_duplicate_lokasi()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        Lokasi::factory()->create([
            'id_project' => $project->id,
            'nama_lokasi' => 'Gudang A',
        ]);

        $response = $this->withHeaders($headers)
            ->postJson('/api/lokasi', [
                'id_project' => $project->id,
                'nama_lokasi' => 'Gudang A',
            ]);

        $response->assertStatus(409);

        $response->assertJson([
            'message' => 'Nama lokasi sudah ada',
            'status_code' => 409,
        ]);
    }

    public function test_update_lokasi_success()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
            'nama_lokasi' => 'Gudang Lama',
        ]);

        $response = $this->withHeaders($headers)
            ->putJson("/api/lokasi/{$lokasi->id}", [
                'id_project' => $project->id,
                'nama_lokasi' => 'Gudang Baru',
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Lokasi berhasil diubah',
            'status_code' => 200,
        ]);

        $this->assertDatabaseHas('lokasi', [
            'id' => $lokasi->id,
            'id_project' => $project->id,
            'nama_lokasi' => 'Gudang Baru',
        ]);
    }

    public function test_update_lokasi_not_found()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $response = $this->withHeaders($headers)
            ->putJson('/api/lokasi/999', [
                'id_project' => $project->id,
                'nama_lokasi' => 'Gudang Baru',
            ]);

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'Lokasi tidak ditemukan',
            'status_code' => 404,
        ]);
    }

    public function test_update_duplicate_lokasi()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        Lokasi::factory()->create([
            'id_project' => $project->id,
            'nama_lokasi' => 'Gudang A',
        ]);

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
            'nama_lokasi' => 'Gudang B',
        ]);

        $response = $this->withHeaders($headers)
            ->putJson("/api/lokasi/{$lokasi->id}", [
                'id_project' => $project->id,
                'nama_lokasi' => 'Gudang A',
            ]);

        $response->assertStatus(409);

        $response->assertJson([
            'message' => 'Nama lokasi sudah ada',
            'status_code' => 409,
        ]);
    }

        public function test_delete_lokasi_success()
    {
        $headers = $this->authHeader();

        $lokasi = Lokasi::factory()->create();

        $response = $this->withHeaders($headers)
            ->deleteJson("/api/lokasi/{$lokasi->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Lokasi berhasil dihapus',
            'status_code' => 200,
        ]);

        $this->assertDatabaseMissing('lokasi', [
            'id' => $lokasi->id,
        ]);
    }

    public function test_delete_lokasi_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->deleteJson('/api/lokasi/999');

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'Lokasi tidak ditemukan',
            'status_code' => 404,
        ]);
    }

    public function test_delete_lokasi_used_in_kerusakan()
    {
        $headers = $this->authHeader();

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

        $response = $this->withHeaders($headers)
            ->deleteJson("/api/lokasi/{$lokasi->id}");

        $response->assertStatus(400);

        $response->assertJson([
            'message' => 'Lokasi tidak bisa di hapus karena telah digunakan di kerusakan',
            'status_code' => 400,
        ]);

        $this->assertDatabaseHas('lokasi', [
            'id' => $lokasi->id,
        ]);
    }
}