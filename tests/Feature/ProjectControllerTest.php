<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Akses;
use App\Models\Project;
use App\Models\RoleAkses;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectControllerTest extends TestCase
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

        // Semua permission yang dibutuhkan endpoint project
        foreach ([
            'read_project',
            'create_project',
            'update_project',
            'delete_project',
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

    public function authHeader()
    {
        return [
            'Authorization' => 'Bearer '.$this->authenticate(),
        ];
    }

    public function test_index_returns_all_projects()
    {
        Project::factory()->count(3)->create();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/project');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Data project berhasil diambil',
            'status_code' => 200,
        ]);

        $response->assertJsonCount(4, 'data');
    }

    public function test_store_project_success()
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/project', [
                'nama_project' => 'Project Laravel',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('project', [
            'nama_project' => 'Project Laravel',
        ]);
    }

    public function test_store_project_duplicate()
    {
        Project::factory()->create([
            'nama_project' => 'Project Laravel',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/project', [
                'nama_project' => 'Project Laravel',
            ]);

        $response->assertStatus(409);
    }

    public function test_update_project_success()
    {
        $project = Project::factory()->create([
            'nama_project' => 'Project Lama',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/project/{$project->id}", [
                'nama_project' => 'Project Baru',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('project', [
            'id' => $project->id,
            'nama_project' => 'Project Baru',
        ]);
    }

    public function test_update_project_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->putJson('/api/project/999', [
                'nama_project' => 'Project Baru',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_duplicate_project_name()
    {
        Project::factory()->create([
            'nama_project' => 'Project A',
        ]);

        $project = Project::factory()->create([
            'nama_project' => 'Project B',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/project/{$project->id}", [
                'nama_project' => 'Project A',
            ]);

        $response->assertStatus(409);
    }

    public function test_delete_project_success()
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/project/{$project->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('project', [
            'id' => $project->id,
        ]);
    }

    public function test_delete_project_not_found()
    {
        $response = $this->withHeaders($this->authHeader())
            ->deleteJson('/api/project/999');

        $response->assertStatus(404);
    }
}