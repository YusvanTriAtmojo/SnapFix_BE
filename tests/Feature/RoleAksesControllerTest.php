<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Akses;
use App\Models\Project;
use App\Models\RoleAkses;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAksesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    protected function authenticate(): void
    {
        $this->adminRole = Role::factory()->create();

        $project = Project::factory()->create();

        foreach ([
            'read_roleakses',
            'update_roleakses',
            'delete_roleakses',
        ] as $permission) {

            $akses = Akses::factory()->create([
                'nama_akses' => $permission,
            ]);

            RoleAkses::factory()->create([
                'id_role'  => $this->adminRole->id,
                'id_akses' => $akses->id,
            ]);
        }

        User::factory()->create([
            'id_role'    => $this->adminRole->id,
            'id_project' => $project->id,
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

    public function test_index_success()
    {
        $role = Role::factory()->create();

        $akses1 = Akses::factory()->create([
            'nama_akses' => 'Dashboard',
        ]);

        $akses2 = Akses::factory()->create([
            'nama_akses' => 'User',
        ]);

        RoleAkses::factory()->create([
            'id_role' => $role->id,
            'id_akses' => $akses1->id,
        ]);

        RoleAkses::factory()->create([
            'id_role' => $role->id,
            'id_akses' => $akses2->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/roleakses');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data role akses berhasil diambil',
            'status_code' => 200,
        ]);
    }

    public function test_index_filter_role()
    {
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $akses = Akses::factory()->create();

        RoleAkses::factory()->create([
            'id_role' => $role1->id,
            'id_akses' => $akses->id,
        ]);

        RoleAkses::factory()->create([
            'id_role' => $role2->id,
            'id_akses' => $akses->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/roleakses?filterRole={$role1->id}");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id_role' => $role1->id,
        ]);

        $response->assertJsonCount(1, 'data');
    }

    public function test_update_success()
    {
        $role = Role::factory()->create();

        $akses1 = Akses::factory()->create();
        $akses2 = Akses::factory()->create();

        RoleAkses::factory()->create([
            'id_role' => $role->id,
            'id_akses' => $akses1->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/roleakses/{$role->id}", [
                'id_akses' => [
                    $akses1->id,
                    $akses2->id,
                ],
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Role akses berhasil diperbarui',
            'status_code' => 200,
        ]);

        $this->assertDatabaseHas('role_akses', [
            'id_role' => $role->id,
            'id_akses' => $akses1->id,
        ]);

        $this->assertDatabaseHas('role_akses', [
            'id_role' => $role->id,
            'id_akses' => $akses2->id,
        ]);
    }

    public function test_update_validation_failed()
    {
        $role = Role::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/roleakses/{$role->id}", []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'id_akses',
        ]);
    }

    public function test_update_role_not_found()
    {
        $akses = Akses::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->putJson('/api/roleakses/999', [
                'id_akses' => [
                    $akses->id,
                ],
            ]);

        $response->assertStatus(500);
    }
}