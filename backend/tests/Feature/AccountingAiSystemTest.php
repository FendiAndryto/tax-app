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

class AccountingAiSystemTest extends TestCase
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

        // Default acting as admin
        Sanctum::actingAs($this->adminUser);
    }

    public function test_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@accounting.local',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ]);
        $response->assertJsonPath('user.role', 'admin');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@accounting.local',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'メールアドレスまたはパスワードが一致しません。');
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        $this->app['auth']->forgetGuards();

        $loginRes = $this->postJson('/api/auth/login', [
            'email' => 'admin@accounting.local',
            'password' => 'password123',
        ]);
        $token = $loginRes->json('token');

        $logoutRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');

        $logoutRes->assertStatus(200);
        $logoutRes->assertJsonPath('message', 'ログアウトしました');

        $this->app['auth']->forgetGuards();
        $checkRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->withHeader('Accept', 'application/json')
            ->getJson('/api/auth/me');
        $checkRes->assertStatus(401);
    }

    public function test_unauthenticated_request_to_v1_is_rejected(): void
    {
        // Unset authenticated user
        auth()->forgetGuards();

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/v1/accounts');

        $response->assertStatus(401);
    }

    public function test_admin_can_delete_transaction(): void
    {
        Sanctum::actingAs($this->adminUser);
        $transaction = Transaction::first();

        $response = $this->deleteJson('/api/v1/transactions/'.$transaction->id);
        $response->assertStatus(200);
        $response->assertJsonPath('message', '取引を削除しました');
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_staff_cannot_delete_transaction(): void
    {
        Sanctum::actingAs($this->staffUser);
        $transaction = Transaction::first();

        $response = $this->deleteJson('/api/v1/transactions/'.$transaction->id);
        $response->assertStatus(403);
        $response->assertJsonPath('message', '取引の削除は管理者のみ実行できます。');
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    public function test_can_fetch_accounts_and_balances(): void
    {
        $response = $this->getJson('/api/v1/accounts');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_balance',
            'total_balance_formatted',
            'accounts' => [
                '*' => ['id', 'name', 'current_balance', 'current_balance_formatted'],
            ],
        ]);
    }

    public function test_can_fetch_financial_overview(): void
    {
        $response = $this->getJson('/api/v1/financial-overview');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cash_flow' => ['total_cash_in', 'total_cash_out', 'net_cash_flow', 'status'],
            'balances' => ['total_liquid_cash', 'accounts'],
            'trend' => ['avg_daily_income', 'avg_daily_burn_rate', 'health_score'],
            'forecasting' => ['projection_horizon', 'projected_cash_position', 'cash_runway'],
        ]);
    }

    public function test_can_create_transaction_with_authenticated_user(): void
    {
        Sanctum::actingAs($this->staffUser);
        $account = Account::first();
        $category = Category::where('type', 'cash_in')->first();

        $payload = [
            'date' => '2026-10-02',
            'type' => 'cash_in',
            'amount' => 1750000,
            'category_id' => $category->id,
            'account_id' => $account->id,
            'payment_method' => '銀行振込',
            'description' => '現金売上のテスト取引',
        ];

        $response = $this->postJson('/api/v1/transactions', $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('transaction.amount', '1750000.00');
        $response->assertJsonPath('transaction.created_by', $this->staffUser->id);
    }

    public function test_ai_agent_chat_returns_draft_card_when_prompted(): void
    {
        $payload = [
            'message' => '事務用品の購入費 850,000円を現金から記録してください',
        ];

        $response = $this->postJson('/api/v1/ai/chat', $payload);
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'status',
            'reply',
            'executed_tools',
            'draft_card' => [
                'type',
                'amount',
                'description',
                'account_name',
                'category_name',
            ],
            'provider',
        ]);
    }

    public function test_ai_agent_list_tools(): void
    {
        $response = $this->getJson('/api/v1/ai/tools');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('tools'));
    }

    public function test_ai_agent_chat_answers_expense_question_with_summary(): void
    {
        $payload = [
            'message' => '今月の支出合計はいくらですか？',
        ];

        $response = $this->postJson('/api/v1/ai/chat', $payload);
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertNotEmpty($response->json('reply'));
        $this->assertStringContainsString('支出', $response->json('reply'));
    }

    public function test_ai_agent_chat_answers_total_transaction_question_without_refusal(): void
    {
        $payload = [
            'message' => '今月の取引件数はいくつですか？',
        ];

        $response = $this->postJson('/api/v1/ai/chat', $payload);
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertNotEmpty($response->json('reply'));
        $this->assertStringNotContainsString('申し訳ありませんが、私はAUBE TERRA会計システムの専属財務AIアシスタントです', $response->json('reply'));
    }
}
