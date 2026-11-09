<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->adminUser = User::where('role', 'admin')->first();
        $this->staffUser = User::where('role', 'staff')->first();
    }

    public function test_staff_cannot_access_user_management_endpoints(): void
    {
        Sanctum::actingAs($this->staffUser);

        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(403);

        $response = $this->postJson('/api/v1/users', [
            'name' => '新しいスタッフ',
            'email' => 'newstaff@test.com',
            'password' => 'secret123',
            'role' => 'staff',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_list_users(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'users' => [
                '*' => ['id', 'name', 'email', 'role', 'is_active', 'transactions_count', 'created_at'],
            ],
        ]);
    }

    public function test_admin_can_create_staff_user(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/users', [
            'name' => '経理スタッフ（追加）',
            'email' => 'budi@accounting.local',
            'password' => 'budiPass123',
            'role' => 'staff',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('user.name', '経理スタッフ（追加）');
        $response->assertJsonPath('user.role', 'staff');
        $response->assertJsonPath('user.is_active', true);

        $this->assertDatabaseHas('users', [
            'email' => 'budi@accounting.local',
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_edit_user(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->putJson("/api/v1/users/{$this->staffUser->id}", [
            'name' => '経理スタッフ（更新）',
            'email' => 'staff.updated@accounting.local',
            'role' => 'staff',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('user.name', '経理スタッフ（更新）');
        $response->assertJsonPath('user.email', 'staff.updated@accounting.local');
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->putJson("/api/v1/users/{$this->adminUser->id}", [
            'name' => '管理者アカウント',
            'email' => $this->adminUser->email,
            'role' => 'staff',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', '自分自身の管理者権限を解除することはできません。');
    }

    public function test_admin_can_reset_staff_password(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson("/api/v1/users/{$this->staffUser->id}/reset-password", [
            'password' => 'newPassword456',
        ]);

        $response->assertStatus(200);

        // Staff can login with new password
        $loginRes = $this->postJson('/api/auth/login', [
            'email' => $this->staffUser->email,
            'password' => 'newPassword456',
        ]);
        $loginRes->assertStatus(200);
    }

    public function test_admin_can_toggle_staff_status_and_inactive_user_cannot_login(): void
    {
        Sanctum::actingAs($this->adminUser);

        // Deactivate staff
        $response = $this->patchJson("/api/v1/users/{$this->staffUser->id}/toggle-status");
        $response->assertStatus(200);
        $response->assertJsonPath('user.is_active', false);

        // Attempt login with deactivated staff
        $loginRes = $this->postJson('/api/auth/login', [
            'email' => $this->staffUser->email,
            'password' => 'password123',
        ]);
        $loginRes->assertStatus(403);
        $loginRes->assertJsonPath('message', 'アカウントは管理者によって無効化されています。会社の管理者にお問い合わせください。');

        // Reactivate staff
        $reactivateRes = $this->patchJson("/api/v1/users/{$this->staffUser->id}/toggle-status");
        $reactivateRes->assertStatus(200);
        $reactivateRes->assertJsonPath('user.is_active', true);

        // Now can login again
        $loginRes2 = $this->postJson('/api/auth/login', [
            'email' => $this->staffUser->email,
            'password' => 'password123',
        ]);
        $loginRes2->assertStatus(200);
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->patchJson("/api/v1/users/{$this->adminUser->id}/toggle-status");
        $response->assertStatus(422);
        $response->assertJsonPath('message', '自分自身のアカウントを無効化することはできません。');
    }
}
