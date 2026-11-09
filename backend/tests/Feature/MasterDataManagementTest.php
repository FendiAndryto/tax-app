<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MasterDataManagementTest extends TestCase
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

    public function test_staff_cannot_create_or_modify_accounts(): void
    {
        Sanctum::actingAs($this->staffUser);

        $response = $this->postJson('/api/v1/accounts', [
            'name' => 'テスト口座',
            'initial_balance' => 1000000,
        ]);
        $response->assertStatus(403);

        $account = Account::first();
        $response = $this->putJson("/api/v1/accounts/{$account->id}", [
            'name' => '新しい名称',
        ]);
        $response->assertStatus(403);

        $response = $this->patchJson("/api/v1/accounts/{$account->id}/toggle-status");
        $response->assertStatus(403);

        $response = $this->deleteJson("/api/v1/accounts/{$account->id}");
        $response->assertStatus(403);
    }

    public function test_staff_cannot_create_or_modify_categories(): void
    {
        Sanctum::actingAs($this->staffUser);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'テストカテゴリー',
            'type' => 'cash_in',
        ]);
        $response->assertStatus(403);

        $category = Category::first();
        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => '新しい名称',
            'type' => 'cash_in',
        ]);
        $response->assertStatus(403);

        $response = $this->patchJson("/api/v1/categories/{$category->id}/toggle-status");
        $response->assertStatus(403);

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");
        $response->assertStatus(403);
    }

    public function test_admin_can_manage_clean_account(): void
    {
        Sanctum::actingAs($this->adminUser);

        // 1. Create Account
        $res = $this->postJson('/api/v1/accounts', [
            'name' => 'みずほ銀行 事業口座',
            'account_number' => '137001928374',
            'initial_balance' => 25000000,
            'description' => '事業用の予備口座',
        ]);
        $res->assertStatus(201);
        $accountId = $res->json('account.id');

        // 2. Edit Account (including initial balance when 0 transactions)
        $updateRes = $this->putJson("/api/v1/accounts/{$accountId}", [
            'name' => 'みずほ銀行 事業口座',
            'account_number' => '137001928374',
            'initial_balance' => 30000000,
            'description' => 'メインの事業口座',
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('account.name', 'みずほ銀行 事業口座');
        $updateRes->assertJsonPath('account.initial_balance', 30000000);

        // 3. Toggle Status
        $toggleRes = $this->patchJson("/api/v1/accounts/{$accountId}/toggle-status");
        $toggleRes->assertStatus(200);
        $toggleRes->assertJsonPath('account.is_active', false);

        // 4. Delete clean account
        $deleteRes = $this->deleteJson("/api/v1/accounts/{$accountId}");
        $deleteRes->assertStatus(200);
        $this->assertDatabaseMissing('accounts', ['id' => $accountId]);
    }

    public function test_admin_cannot_alter_initial_balance_or_delete_account_with_transactions(): void
    {
        Sanctum::actingAs($this->adminUser);

        $account = Account::first();

        // Ensure there is a transaction on this account
        Transaction::create([
            'date' => '2026-10-02',
            'type' => 'cash_in',
            'amount' => 5000000,
            'category_id' => Category::where('type', 'cash_in')->first()->id,
            'account_id' => $account->id,
            'payment_method' => '銀行振込',
            'description' => 'テスト取引',
            'created_by' => $this->adminUser->id,
        ]);

        // Attempt to change initial_balance
        $updateRes = $this->putJson("/api/v1/accounts/{$account->id}", [
            'name' => $account->name,
            'account_number' => $account->account_number,
            'initial_balance' => 999999999,
        ]);
        $updateRes->assertStatus(422);
        $this->assertStringContainsString('期首残高は変更できません', $updateRes->json('message'));

        // Attempt to delete account with transactions
        $deleteRes = $this->deleteJson("/api/v1/accounts/{$account->id}");
        $deleteRes->assertStatus(422);
        $this->assertStringContainsString('取引履歴があるため削除できません', $deleteRes->json('message'));

        // But toggle status IS ALLOWED
        $toggleRes = $this->patchJson("/api/v1/accounts/{$account->id}/toggle-status");
        $toggleRes->assertStatus(200);
    }

    public function test_admin_can_manage_clean_category(): void
    {
        Sanctum::actingAs($this->adminUser);

        // 1. Create Category
        $res = $this->postJson('/api/v1/categories', [
            'name' => 'ロイヤルティ収入',
            'type' => 'cash_in',
            'description' => 'ソフトウェア使用許諾のロイヤルティ',
        ]);
        $res->assertStatus(201);
        $catId = $res->json('category.id');

        // 2. Edit Category (including type when 0 transactions)
        $updateRes = $this->putJson("/api/v1/categories/{$catId}", [
            'name' => 'ロイヤルティ費用',
            'type' => 'cash_out',
            'description' => 'ロイヤルティの支払い',
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('category.name', 'ロイヤルティ費用');
        $updateRes->assertJsonPath('category.type', 'cash_out');

        // 3. Toggle Status
        $toggleRes = $this->patchJson("/api/v1/categories/{$catId}/toggle-status");
        $toggleRes->assertStatus(200);
        $toggleRes->assertJsonPath('category.is_active', false);

        // 4. Delete clean category
        $deleteRes = $this->deleteJson("/api/v1/categories/{$catId}");
        $deleteRes->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $catId]);
    }

    public function test_admin_cannot_alter_type_or_delete_category_with_transactions(): void
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::where('type', 'cash_out')->first();
        $account = Account::first();

        // Create transaction using this category
        Transaction::create([
            'date' => '2026-10-02',
            'type' => 'cash_out',
            'amount' => 150000,
            'category_id' => $category->id,
            'account_id' => $account->id,
            'payment_method' => '銀行振込',
            'description' => '事務用品の購入',
            'created_by' => $this->adminUser->id,
        ]);

        // Attempt to change category type
        $updateRes = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => $category->name,
            'type' => 'cash_in',
            'description' => $category->description,
        ]);
        $updateRes->assertStatus(422);
        $this->assertStringContainsString('カテゴリー種別を', $updateRes->json('message'));

        // Attempt to delete category with transactions
        $deleteRes = $this->deleteJson("/api/v1/categories/{$category->id}");
        $deleteRes->assertStatus(422);
        $this->assertStringContainsString('使用されているため削除できません', $deleteRes->json('message'));

        // But toggle status IS ALLOWED
        $toggleRes = $this->patchJson("/api/v1/categories/{$category->id}/toggle-status");
        $toggleRes->assertStatus(200);
    }
}
