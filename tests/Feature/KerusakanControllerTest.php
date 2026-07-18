<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Akses;
use App\Models\Project;
use App\Models\Lokasi;
use App\Models\Fasilitas;
use App\Models\Kerusakan;
use App\Models\RoleAkses;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KerusakanControllerTest extends TestCase
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
            'read_kerusakan',
            'create_kerusakan',
            'update_kerusakan',
            'delete_kerusakan',
            'create_perbaikan',
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

    protected function createKerusakan($status = 'pending')
    {
        $role = Role::factory()->create();

        $project = Project::factory()->create();

        $user = User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project->id,
        ]);

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        return Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'status' => $status,
        ]);
    }

    public function test_index_success()
    {
        $headers = $this->authHeader();

        $project = Project::first();

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        Kerusakan::factory()->count(3)->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson('/api/kerusakan');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data kerusakan berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_index_empty()
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/kerusakan');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Tidak ada data kerusakan',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(0, 'data');
    }

    public function test_index_filter_status()
    {
        $headers = $this->authHeader();

        $project = Project::first();

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'status' => 'pending',
        ]);

        Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'status' => 'selesai',
        ]);

        $response = $this->withHeaders($headers)
            ->getJson('/api/kerusakan?status=selesai');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_index_filter_tanggal()
    {
        $headers = $this->authHeader();

        $project = Project::first();

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'tanggal' => '2025-01-01',
        ]);

        Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'tanggal' => '2025-02-01',
        ]);

        $response = $this->withHeaders($headers)
            ->getJson('/api/kerusakan?start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_indexadmin_success()
    {
        $headers = $this->authHeader();

        $this->createKerusakan();
        $this->createKerusakan();
        $this->createKerusakan();

        $response = $this->withHeaders($headers)
            ->getJson('/api/kerusakan/admin');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data kerusakan berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_indexadmin_filter_project()
    {
        $headers = $this->authHeader();

        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $role = Role::factory()->create();

        $user1 = User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project1->id,
        ]);

        $user2 = User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project2->id,
        ]);

        $lokasi1 = Lokasi::factory()->create([
            'id_project' => $project1->id,
        ]);

        $lokasi2 = Lokasi::factory()->create([
            'id_project' => $project2->id,
        ]);

        $fasilitas1 = Fasilitas::factory()->create([
            'id_project' => $project1->id,
        ]);

        $fasilitas2 = Fasilitas::factory()->create([
            'id_project' => $project2->id,
        ]);

        Kerusakan::factory()->count(2)->create([
            'user_id' => $user1->id,
            'id_project' => $project1->id,
            'id_lokasi' => $lokasi1->id,
            'id_fasilitas' => $fasilitas1->id,
        ]);

        Kerusakan::factory()->count(3)->create([
            'user_id' => $user2->id,
            'id_project' => $project2->id,
            'id_lokasi' => $lokasi2->id,
            'id_fasilitas' => $fasilitas2->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson("/api/kerusakan/admin?id_project={$project2->id}");

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_indexadmin_total_count()
    {
        $headers = $this->authHeader();

        $this->createKerusakan('pending');
        $this->createKerusakan('diperbaiki');
        $this->createKerusakan('selesai');

        $response = $this->withHeaders($headers)
            ->getJson('/api/kerusakan/admin');

        $response->assertStatus(200);

        $response->assertJson([
            'total_kerusakan' => 3,
            'total_perbaikan' => 1,
            'total_selesai' => 1,
        ]);
    }

    public function test_store_kerusakan_success()
    {
        Storage::fake('public');

        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $response = $this->withHeaders($headers)
            ->postJson('/api/kerusakan', [
                'user_id' => $user->id,
                'id_lokasi' => $lokasi->id,
                'id_fasilitas' => $fasilitas->id,
                'tanggal' => now()->toDateString(),
                'lat_posisi' => '-7.801389',
                'lng_posisi' => '110.364444',
                'deskripsi' => 'Lampu rusak',
                'foto_kerusakan' => UploadedFile::fake()->image('kerusakan.jpg'),
            ]);

        $response->assertStatus(201);

        $response->assertJson([
            'message' => 'Kerusakan berhasil ditambahkan',
            'status_code' => 201,
        ]);

        $this->assertDatabaseHas('kerusakan', [
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'deskripsi' => 'Lampu rusak',
            'status' => 'pending',
        ]);
    }

    public function test_update_status_success()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $kerusakan = Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders($headers)
            ->putJson("/api/kerusakan/{$kerusakan->id}/status", [
                'status' => 'diperbaiki',
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Status berhasil diupdate',
            'status_code' => 200,
        ]);

        $this->assertDatabaseHas('kerusakan', [
            'id' => $kerusakan->id,
            'status' => 'diperbaiki',
        ]);
    }

    public function test_update_status_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->putJson('/api/kerusakan/999/status', [
                'status' => 'diperbaiki',
            ]);

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'Kerusakan tidak ditemukan',
            'status_code' => 404,
        ]);
    }

    public function test_store_perbaikan_success()
    {
        Storage::fake('public');

        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $kerusakan = Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'status' => 'diperbaiki',
        ]);

        $response = $this->withHeaders($headers)
            ->post('/api/kerusakan/perbaikan', [
                'kerusakan_id' => $kerusakan->id,
                'tanggal_perbaikan' => now()->toDateString(),
                'deskripsi_perbaikan' => 'Sudah diperbaiki',
                'foto_perbaikan' => UploadedFile::fake()->image('perbaikan.jpg'),
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Laporan perbaikan berhasil ditambahkan',
            'status_code' => 200,
        ]);

        $this->assertDatabaseHas('kerusakan', [
            'id' => $kerusakan->id,
            'status' => 'selesai',
            'deskripsi_perbaikan' => 'Sudah diperbaiki',
        ]);
    }

    public function test_delete_kerusakan_success()
    {
        Storage::fake('public');

        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $kerusakan = Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders($headers)
            ->deleteJson("/api/kerusakan/{$kerusakan->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Kerusakan berhasil dihapus',
            'status_code' => 200,
        ]);

        $this->assertDatabaseMissing('kerusakan', [
            'id' => $kerusakan->id,
        ]);
    }

    public function test_delete_kerusakan_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->deleteJson('/api/kerusakan/999');

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'Kerusakan tidak ditemukan',
            'status_code' => 404,
        ]);
    }

    public function test_delete_kerusakan_status_selesai()
    {
        $headers = $this->authHeader();

        $project = Project::factory()->create();

        $lokasi = Lokasi::factory()->create([
            'id_project' => $project->id,
        ]);

        $fasilitas = Fasilitas::factory()->create([
            'id_project' => $project->id,
        ]);

        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);

        $kerusakan = Kerusakan::factory()->create([
            'user_id' => $user->id,
            'id_project' => $project->id,
            'id_lokasi' => $lokasi->id,
            'id_fasilitas' => $fasilitas->id,
            'status' => 'selesai',
        ]);

        $response = $this->withHeaders($headers)
            ->deleteJson("/api/kerusakan/{$kerusakan->id}");

        $response->assertStatus(403);

        $response->assertJson([
            'message' => 'Kerusakan hanya bisa dihapus jika masih pending',
            'status_code' => 403,
        ]);

        $this->assertDatabaseHas('kerusakan', [
            'id' => $kerusakan->id,
        ]);
    }

}