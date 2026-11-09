<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@accounting.local'],
            [
                'name' => 'オーナー / 管理者',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@accounting.local'],
            [
                'name' => '経理スタッフ',
                'password' => Hash::make('password123'),
                'role' => 'staff',
            ]
        );

        // 2. Accounts (JPY)
        $cash = Account::firstOrCreate(
            ['name' => '現金 / 店舗'],
            [
                'name_en' => 'Petty Cash / Store',
                'name_ja' => '現金 / 店舗',
                'account_number' => 'CASH-001',
                'initial_balance' => 500000,
                'description' => '店内金庫の現金',
                'is_active' => true,
            ]
        );

        $bank = Account::firstOrCreate(
            ['name' => '銀行口座（事業用）'],
            [
                'name_en' => 'Business Bank Account',
                'name_ja' => '銀行口座（事業用）',
                'account_number' => '8820192831',
                'initial_balance' => 3500000,
                'description' => '事業用のメイン口座',
                'is_active' => true,
            ]
        );

        $reserve = Account::firstOrCreate(
            ['name' => '予備口座'],
            [
                'name_en' => 'Reserve Account',
                'name_ja' => '予備口座',
                'account_number' => '137001928371',
                'initial_balance' => 1500000,
                'description' => '予備資金・給与振込用',
                'is_active' => true,
            ]
        );

        // 3. Categories (cash in)
        $sales = Category::firstOrCreate(
            ['name' => '売上高', 'type' => 'cash_in'],
            ['name_en' => 'Sales Revenue', 'name_ja' => '売上高', 'description' => '製品・商品の販売収入']
        );
        $service = Category::firstOrCreate(
            ['name' => 'サービス収入', 'type' => 'cash_in'],
            ['name_en' => 'Service Revenue', 'name_ja' => 'サービス収入', 'description' => 'コンサルティング等の役務収入']
        );
        $otherIncome = Category::firstOrCreate(
            ['name' => 'その他収入', 'type' => 'cash_in'],
            ['name_en' => 'Other Revenue', 'name_ja' => 'その他収入', 'description' => '雑収入・一時的な収入']
        );

        // Categories (cash out)
        $salary = Category::firstOrCreate(
            ['name' => '給与手当', 'type' => 'cash_out'],
            ['name_en' => 'Salaries', 'name_ja' => '給与手当', 'description' => 'スタッフへの給与・報酬']
        );
        $rent = Category::firstOrCreate(
            ['name' => '地代家賃', 'type' => 'cash_out'],
            ['name_en' => 'Rent', 'name_ja' => '地代家賃', 'description' => '事務所・店舗の賃料']
        );
        $utilities = Category::firstOrCreate(
            ['name' => '水道光熱費', 'type' => 'cash_out'],
            ['name_en' => 'Utilities', 'name_ja' => '水道光熱費', 'description' => '電気・ガス・水道・通信費']
        );
        $advertising = Category::firstOrCreate(
            ['name' => '広告宣伝費', 'type' => 'cash_out'],
            ['name_en' => 'Advertising', 'name_ja' => '広告宣伝費', 'description' => '広告・販促・インフルエンサー費用']
        );
        $supplies = Category::firstOrCreate(
            ['name' => '消耗品費', 'type' => 'cash_out'],
            ['name_en' => 'Supplies', 'name_ja' => '消耗品費', 'description' => '事務用品・梱包資材などの消耗品']
        );

        // 4. Sample transactions over the past 30 days (JPY)
        $today = Carbon::today();

        $sampleData = [
            ['days_ago' => 28, 'type' => 'cash_in', 'amount' => 250000, 'cat' => $sales, 'acc' => $bank, 'method' => '銀行振込', 'desc' => 'PO #1021 の代金振込'],
            ['days_ago' => 27, 'type' => 'cash_in', 'amount' => 80000, 'cat' => $sales, 'acc' => $cash, 'method' => '現金', 'desc' => '店舗レジ1 現金売上'],
            ['days_ago' => 25, 'type' => 'cash_out', 'amount' => 120000, 'cat' => $supplies, 'acc' => $bank, 'method' => '銀行振込', 'desc' => '梱包資材の仕入れ'],
            ['days_ago' => 24, 'type' => 'cash_out', 'amount' => 50000, 'cat' => $advertising, 'acc' => $bank, 'method' => 'デビットカード', 'desc' => 'Meta広告・TikTok広告のチャージ'],

            ['days_ago' => 20, 'type' => 'cash_in', 'amount' => 480000, 'cat' => $service, 'acc' => $bank, 'method' => '銀行振込', 'desc' => 'Alphaプロジェクトコンサル料の入金'],
            ['days_ago' => 18, 'type' => 'cash_out', 'amount' => 12000, 'cat' => $supplies, 'acc' => $cash, 'method' => '現金', 'desc' => '事務用品・会議用飲料の購入'],
            ['days_ago' => 15, 'type' => 'cash_out', 'amount' => 280000, 'cat' => $salary, 'acc' => $reserve, 'method' => '銀行振込', 'desc' => 'スタッフ月次給与の振込'],
            ['days_ago' => 14, 'type' => 'cash_in', 'amount' => 150000, 'cat' => $sales, 'acc' => $bank, 'method' => 'QRIS', 'desc' => '週末QRIS売上の精算'],

            ['days_ago' => 10, 'type' => 'cash_in', 'amount' => 320000, 'cat' => $sales, 'acc' => $bank, 'method' => '銀行振込', 'desc' => 'Surabaya二次販売店向け売上'],
            ['days_ago' => 8, 'type' => 'cash_out', 'amount' => 85000, 'cat' => $utilities, 'acc' => $bank, 'method' => '銀行振込', 'desc' => '電気・インターネット・水道料金'],
            ['days_ago' => 6, 'type' => 'cash_out', 'amount' => 60000, 'cat' => $advertising, 'acc' => $bank, 'method' => '銀行振込', 'desc' => 'Instagramインフルエンサー案件'],

            ['days_ago' => 4, 'type' => 'cash_in', 'amount' => 400000, 'cat' => $sales, 'acc' => $bank, 'method' => '銀行振込', 'desc' => '請求書 #1089 の入金（ユニフォーム）'],
            ['days_ago' => 2, 'type' => 'cash_in', 'amount' => 95000, 'cat' => $sales, 'acc' => $cash, 'method' => '現金', 'desc' => '週末小売売上'],
            ['days_ago' => 1, 'type' => 'cash_out', 'amount' => 8000, 'cat' => $supplies, 'acc' => $cash, 'method' => '現金', 'desc' => '配送・宅配の燃料費'],
        ];

        foreach ($sampleData as $item) {
            Transaction::create([
                'date' => $today->copy()->subDays($item['days_ago'])->format('Y-m-d'),
                'type' => $item['type'],
                'amount' => $item['amount'],
                'category_id' => $item['cat']->id,
                'account_id' => $item['acc']->id,
                'payment_method' => $item['method'],
                'description' => $item['desc'],
                'created_by' => $staff->id,
            ]);
        }

        // 5. Japanese chart-of-accounts presets (account_categories)
        $this->call([
            AccountCategorySeeder::class,
        ]);
    }
}
