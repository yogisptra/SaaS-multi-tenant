<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProjectAndTaskTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private User $adminA;
    private User $memberA;
    private string $tokenAdminA;
    private string $tokenMemberA;

    private Company $companyB;
    private User $adminB;
    private string $tokenAdminB;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Company A
        $this->companyA = Company::create([
            'name' => 'Company A',
            'slug' => 'company-a',
        ]);

        $this->adminA = User::create([
            'company_id' => $this->companyA->id,
            'name' => 'Admin A',
            'email' => 'admin_a@saas.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->memberA = User::create([
            'company_id' => $this->companyA->id,
            'name' => 'Member A',
            'email' => 'member_a@saas.test',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        $this->tokenAdminA = auth('api')->fromUser($this->adminA);
        $this->tokenMemberA = auth('api')->fromUser($this->memberA);

        // Setup Company B
        $this->companyB = Company::create([
            'name' => 'Company B',
            'slug' => 'company-b',
        ]);

        $this->adminB = User::create([
            'company_id' => $this->companyB->id,
            'name' => 'Admin B',
            'email' => 'admin_b@saas.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->tokenAdminB = auth('api')->fromUser($this->adminB);
    }



    /**
     * Test Project CRUD permissions & isolation
     */
    public function test_admin_can_crud_projects_member_cannot_modify(): void
    {
        // 1. Admin A creates a project
        $response = $this->actingAs($this->adminA, 'api')->postJson('/api/v1/projects', [
            'name' => 'Project A',
            'description' => 'Description of Project A',
        ]);

        $response->assertStatus(201);
        $projectUuid = $response->json('data.id');

        // 2. Member A tries to create a project (Forbidden - Policy)
        $responseMemberCreate = $this->actingAs($this->memberA, 'api')->postJson('/api/v1/projects', [
            'name' => 'Project by Member',
        ]);
        $responseMemberCreate->assertStatus(403);

        // 3. Member A can view project
        $responseMemberShow = $this->actingAs($this->memberA, 'api')->getJson("/api/v1/projects/{$projectUuid}");
        $responseMemberShow->assertStatus(200)
            ->assertJsonPath('data.name', 'Project A');

        // 4. Admin B tries to view project (Not Found - Global Scope filtering)
        $responseAdminBShow = $this->actingAs($this->adminB, 'api')->getJson("/api/v1/projects/{$projectUuid}");
        $responseAdminBShow->assertStatus(404);

        // 5. Admin B tries to update project (Not Found - Global Scope)
        $responseAdminBUpdate = $this->actingAs($this->adminB, 'api')->patchJson("/api/v1/projects/{$projectUuid}", [
            'name' => 'Malicious Update',
        ]);
        $responseAdminBUpdate->assertStatus(404);

        // 6. Admin A updates project
        $responseUpdate = $this->actingAs($this->adminA, 'api')->patchJson("/api/v1/projects/{$projectUuid}", [
            'name' => 'Project A Updated',
        ]);
        $responseUpdate->assertStatus(200)
            ->assertJsonPath('data.name', 'Project A Updated');

        // 7. Admin A deletes project
        $responseDelete = $this->actingAs($this->adminA, 'api')->deleteJson("/api/v1/projects/{$projectUuid}");
        $responseDelete->assertStatus(200);

        // Verify soft deleted
        $this->assertSoftDeleted('projects', [
            'uuid' => $projectUuid,
        ]);
    }

    /**
     * Test Task CRUD permissions & isolation
     */
    public function test_task_rbac_and_isolation(): void
    {
        // Setup a project for Company A
        $project = Project::create([
            'company_id' => $this->companyA->id,
            'name' => 'Website Redesign',
            'created_by' => $this->adminA->id,
        ]);

        // 1. Admin A creates a task
        $response = $this->actingAs($this->adminA, 'api')->postJson("/api/v1/projects/{$project->uuid}/tasks", [
            'title' => 'Design Mockups',
            'description' => 'Create high-fidelity mockups',
            'assigned_to' => $this->memberA->id,
        ]);

        $response->assertStatus(201);
        $taskUuid = $response->json('data.id');

        // 2. Member A updates task status (Allowed because it's assigned to him)
        $responseMemberUpdate = $this->actingAs($this->memberA, 'api')->patchJson("/api/v1/projects/{$project->uuid}/tasks/{$taskUuid}", [
            'status' => 'in_progress',
        ]);
        $responseMemberUpdate->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');

        // 3. Member A is NOT assigned to another task we create
        $unassignedTask = Task::create([
            'company_id' => $this->companyA->id,
            'project_id' => $project->id,
            'title' => 'Database Migration',
            'assigned_to' => null,
        ]);

        // 4. Member A tries to update unassigned task (Forbidden - Policy)
        $responseMemberUpdateForbidden = $this->actingAs($this->memberA, 'api')->patchJson("/api/v1/projects/{$project->uuid}/tasks/{$unassignedTask->uuid}", [
            'status' => 'in_progress',
        ]);
        $responseMemberUpdateForbidden->assertStatus(403);

        // 5. Admin B tries to access Company A's task (Not Found - Global Scope)
        $responseAdminBTask = $this->actingAs($this->adminB, 'api')->getJson("/api/v1/projects/{$project->uuid}/tasks/{$taskUuid}");
        $responseAdminBTask->assertStatus(404);
    }
}
