<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Akses;
use App\Models\RoleAkses;
use App\Models\Lokasi;
use App\Models\Fasilitas;
use App\Models\Kerusakan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }


    protected function authenticate(): void
    {
        $role = Role::factory()->create();

        $project = Project::factory()->create();


        foreach ([
            'read_user',
            'update_user',
            'delete_user',
        ] as $permission) {

            $akses = Akses::factory()->create([
                'nama_akses' => $permission,
            ]);

            RoleAkses::factory()->create([
                'id_role'  => $role->id,
                'id_akses' => $akses->id,
            ]);
        }


        $this->admin = User::factory()->create([
            'id_role'    => $role->id,
            'id_project' => $project->id,
            'name'       => 'Admin',
            'nip'        => '001',
            'email'      => 'admin@gmail.com',
            'password'   => bcrypt('12345678'),
        ]);


        $login = $this->postJson('/api/login', [
            'email'    => 'admin@gmail.com',
            'password' => '12345678',
        ]);


        $login->assertStatus(200);


        $this->token = $login['data']['token'];
    }


    protected function authHeader(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
        ];
    }

    public function test_get_profile_success()
    {
        $response = $this
            ->withHeaders($this->authHeader())
            ->getJson('/api/users/profile');


        $response->assertStatus(200);


        $response->assertJson([
            'message' => 'Data user berhasil diambil',
            'status_code' => 200,
        ]);


        $response->assertJsonStructure([
            'message',
            'status_code',
            'data' => [
                'id',
                'id_role',
                'role',
                'id_project',
                'project',
                'nip',
                'name',
                'notlp',
                'alamat',
                'email',
            ]
        ]);
    }


    public function test_index_success()
    {

        User::factory()->count(3)->create();


        $response = $this
            ->withHeaders($this->authHeader())
            ->getJson('/api/user');


        $response->assertStatus(200);


        $response->assertJson([
            'message' => 'Data user berhasil diambil',
            'status_code' => 200,
        ]);


        $response->assertJsonCount(4,'data');
    }


    public function test_index_filter_project()
    {

        $project = Project::factory()->create();


        $user = User::factory()->create([
            'id_project' => $project->id,
        ]);


        User::factory()->create();


        $response = $this
            ->withHeaders($this->authHeader())
            ->getJson(
                "/api/user?id_project={$project->id}"
            );


        $response->assertStatus(200);


        $response->assertJsonFragment([
            'id' => $user->id,
        ]);


    }


    public function test_index_empty()
    {

        User::query()->delete();


        $response = $this
            ->withHeaders($this->authHeader())
            ->getJson('/api/user');


        $response->assertStatus(200);


        $response->assertJson([
            'message' => 'Tidak ada data user',
            'status_code' => 200,
            'data' => [],
        ]);

    }

    public function test_update_by_user_success()
    {
        $response = $this
            ->withHeaders($this->authHeader())
            ->putJson('/api/users/update', [

                'id_project' => $this->admin->id_project,
                'id_role' => $this->admin->id_role,

                'name' => 'Admin Update',
                'nip' => '002',
                'notlp' => '08123456789',
                'alamat' => 'Yogyakarta',
                'email' => 'adminupdate@gmail.com',

            ]);


        $response->assertStatus(200);


        $response->assertJson([
            'message' => 'Data user berhasil diperbarui',
            'status_code' => 200,
        ]);


        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Admin Update',
            'nip' => '002',
        ]);
    }

    public function test_update_by_user_duplicate_nip()
    {
        User::factory()->create([
            'id_role' => $this->admin->id_role,
            'nip' => '999',
        ]);


        $response = $this
            ->withHeaders($this->authHeader())
            ->putJson('/api/users/update', [

                'id_project' => $this->admin->id_project,
                'id_role' => $this->admin->id_role,

                'name' => 'Update',
                'nip' => '999',
                'notlp' => '081111111',
                'alamat' => 'Jogja',
                'email' => 'update@gmail.com',

            ]);
    }


    public function test_update_by_user_validation_failed()
    {

        $response = $this
            ->withHeaders($this->authHeader())
            ->putJson('/api/user/update', [
                'nip' => '',
            ]);


        $response->assertStatus(422);
    }

    public function test_update_user_success()
    {

        $role = Role::factory()->create();

        $project = Project::factory()->create();


        $user = User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project->id,
            'name' => 'User Lama',
            'nip' => '111',
            'email' => 'user@gmail.com',
        ]);

        $response = $this
            ->withHeaders($this->authHeader())
            ->putJson("/api/user/{$user->id}", [

                'id_project' => $project->id,
                'id_role' => $role->id,
                'name' => 'User Baru',
                'nip' => '222',
                'notlp' => '081111111',
                'alamat' => 'Jakarta',
                'email' => 'userbaru@gmail.com',

            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Data user berhasil diperbarui',
            'status_code' => 200,
        ]);

        $this->assertDatabaseHas('users', [

            'id' => $user->id,
            'name' => 'User Baru',
            'nip' => '222',

        ]);
    }


    public function test_update_user_not_found()
    {

        $role = Role::factory()->create();
        $project = Project::factory()->create();

        $response = $this
        ->withHeaders($this->authHeader())
        ->putJson('/api/user/999', [

            'id_project' => $project->id,
            'id_role' => $role->id,

            'name' => 'Test',
            'nip' => '123',

            'notlp' => '081234567',
            'alamat' => 'Jakarta',
            'email' => 'test@gmail.com',

        ]);

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'User tidak ditemukan',
            'status_code' => 404,
        ]);

    }

    public function test_update_user_duplicate_nip()
    {
        $role = Role::factory()->create();

        $project = Project::factory()->create();


        User::factory()->create([
            'id_role' => $role->id,
            'nip' => '555',
        ]);


        $user = User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project->id,
            'nip' => '111',
        ]);


        $response = $this
            ->withHeaders($this->authHeader())
            ->putJson("/api/user/{$user->id}", [

                'id_project' => $project->id,
                'id_role' => $role->id,

                'name' => 'Update',
                'nip' => '555',

                'notlp' => '08123456789',
                'alamat' => 'Yogyakarta',
                'email' => 'update@gmail.com',

            ]);


        $response->assertStatus(422);


        $response->assertJson([
            'message' => 'NIP sudah digunakan oleh pengguna lain dengan role yang sama.',
            'status_code' => 422,
        ]);
    }


    public function test_update_user_validation_failed()
    {
        $response = $this
            ->withHeaders($this->authHeader())
            ->putJson("/api/user/{$this->admin->id}", [

                'name' => '',

            ]);
        $response->assertStatus(422);
    }

    public function test_destroy_success()
    {

        $user = User::factory()->create();

        $response = $this
            ->withHeaders($this->authHeader())
            ->deleteJson("/api/user/{$user->id}");

       $response->assertStatus(200);

        $response->assertJson([
            'message' => 'User berhasil dihapus',
            'status_code' => 200,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

    }

    public function test_destroy_not_found()
    {

        $response = $this
            ->withHeaders($this->authHeader())
            ->deleteJson('/api/user/999');


        $response->assertStatus(404);

    }

    public function test_delete_user_used_in_kerusakan()
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
            ->deleteJson("/api/user/{$user->id}");

        $response->assertStatus(400);

        $response->assertJson([
            'message' => 'User tidak bisa di hapus karena telah digunakan di kerusakan',
            'status_code' => 400,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }
}