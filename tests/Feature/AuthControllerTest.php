<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Akses;
use App\Models\RoleAkses;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat user beserta relasi Role, Project, dan Akses.
     */
    private function createUser(
        string $email = 'admin@gmail.com',
        string $password = '12345678'
    ): User {
        $role = Role::factory()->create([
            'nama_role' => 'Admin',
        ]);

        $project = Project::factory()->create([
            'nama_project' => 'Project Test',
        ]);

        $akses = Akses::factory()->create([
            'nama_akses' => 'Dashboard',
        ]);

        RoleAkses::factory()->create([
            'id_role' => $role->id,
            'id_akses' => $akses->id,
        ]);

        return User::factory()->create([
            'id_role' => $role->id,
            'id_project' => $project->id,
            'email' => $email,
            'password' => bcrypt($password),
        ]);
    }

    /**
     * Test Login Berhasil
     */
    public function test_user_can_login()
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => 'admin@gmail.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Login berhasil',
            'status_code' => 200,
        ]);

        $response->assertJsonStructure([
            'message',
            'status_code',
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'id_project',
                'akses',
                'nama_project',
                'token',
            ],
        ]);
    }

    /**
     * Test Login Gagal
     */
    public function test_login_failed()
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => 'admin@gmail.com',
            'password' => 'password_salah',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Email atau password salah',
            'status_code' => 401,
        ]);
    }

    /**
     * Test Endpoint Me
     */
    public function test_me()
    {
        $user = $this->createUser();

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);

        $login->assertStatus(200);

        $token = $login->json('data.token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'User ditemukan',
            'status_code' => 200,
        ]);

        $response->assertJsonStructure([
            'message',
            'status_code',
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'akses',
                'id_project',
                'nama_project',
            ],
        ]);
    }

    /**
     * Test Logout
     */
    public function test_logout()
    {
        $user = $this->createUser();

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);

        $login->assertStatus(200);

        $token = $login->json('data.token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Logout berhasil',
            'status_code' => 200,
            'data' => null,
        ]);
    }
}