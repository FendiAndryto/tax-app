<?php

namespace App\Services\AI;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiToolRegistry
{
    /**
     * Get function definitions formatted for Google Gemini API
     */
    public static function getToolDefinitions(): array
    {
        return [
            [
                'name' => 'get_transaction',
                'description' => '取引IDを指定して、1件の取引の詳細を取得します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'transaction_id' => [
                            'type' => 'INTEGER',
                            'description' => '照会する取引の数値ID',
                        ],
                    ],
                    'required' => ['transaction_id'],
                ],
            ],
            [
                'name' => 'search_transaction',
                'description' => 'キーワード・日付・カテゴリー・種別で入金または出金の取引を検索します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => [
                            'type' => 'STRING',
                            'description' => '取引の備考を対象とする検索キーワード',
                        ],
                        'type' => [
                            'type' => 'STRING',
                            'description' => '取引種別: cash_in または cash_out',
                            'enum' => ['cash_in', 'cash_out'],
                        ],
                        'start_date' => [
                            'type' => 'STRING',
                            'description' => '絞り込み開始日（形式 YYYY-MM-DD）',
                        ],
                        'end_date' => [
                            'type' => 'STRING',
                            'description' => '絞り込み終了日（形式 YYYY-MM-DD）',
                        ],
                        'limit' => [
                            'type' => 'INTEGER',
                            'description' => '表示する取引の最大件数（既定値 10）',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'create_draft',
                'description' => '保存前にユーザーが確認・修正できるよう、入金または出金の取引ドラフトを作成します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'type' => [
                            'type' => 'STRING',
                            'description' => '取引種別: cash_in（入金）または cash_out（出金）',
                            'enum' => ['cash_in', 'cash_out'],
                        ],
                        'amount' => [
                            'type' => 'NUMBER',
                            'description' => '取引金額（日本円、カンマや小数点なしの数値）',
                        ],
                        'category_name' => [
                            'type' => 'STRING',
                            'description' => 'カテゴリー名（例: 売上高、給与手当、消耗品費、地代家賃）',
                        ],
                        'account_name' => [
                            'type' => 'STRING',
                            'description' => '口座名 / 財布 / 銀行（例: 現金、銀行口座（事業用）、予備口座）',
                        ],
                        'payment_method' => [
                            'type' => 'STRING',
                            'description' => '支払方法（銀行振込、現金、QRIS、デビットカード）',
                        ],
                        'description' => [
                            'type' => 'STRING',
                            'description' => '取引の説明・備考',
                        ],
                        'date' => [
                            'type' => 'STRING',
                            'description' => '取引日（形式 YYYY-MM-DD、指定がない場合は本日）',
                        ],
                    ],
                    'required' => ['type', 'amount', 'description'],
                ],
            ],
            [
                'name' => 'category_transaction',
                'description' => '取引の説明を分析し、最も適切な cash_in / cash_out カテゴリーを提案します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'description' => [
                            'type' => 'STRING',
                            'description' => '支出または収入の説明・メモ',
                        ],
                    ],
                    'required' => ['description'],
                ],
            ],
            [
                'name' => 'cash_flow',
                'description' => '指定期間のキャッシュフロー（総入金、総出金、純キャッシュフロー）を集計します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'start_date' => [
                            'type' => 'STRING',
                            'description' => '開始日（YYYY-MM-DD）',
                        ],
                        'end_date' => [
                            'type' => 'STRING',
                            'description' => '終了日（YYYY-MM-DD）',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'income_summary',
                'description' => 'カテゴリー別または口座別に、総収入（Cash In）の概要を生成します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'start_date' => ['type' => 'STRING', 'description' => '開始日（YYYY-MM-DD）'],
                        'end_date' => ['type' => 'STRING', 'description' => '終了日（YYYY-MM-DD）'],
                    ],
                ],
            ],
            [
                'name' => 'expense_summary',
                'description' => 'カテゴリー別または口座別に、総支出（Cash Out）の概要を生成します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'start_date' => ['type' => 'STRING', 'description' => '開始日（YYYY-MM-DD）'],
                        'end_date' => ['type' => 'STRING', 'description' => '終了日（YYYY-MM-DD）'],
                    ],
                ],
            ],
            [
                'name' => 'balance_summary',
                'description' => '全口座（現金、銀行口座など）の現在の実残高と、総現預金を表示します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'account_id' => [
                            'type' => 'INTEGER',
                            'description' => '任意: 1つの口座の残高のみ確認する場合の口座ID',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'trend_analysis',
                'description' => '財務トレンドの統計（日次平均支出・収入、効率比率、成長トレンド）を算出します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'days' => [
                            'type' => 'INTEGER',
                            'description' => '分析対象の過去日数（既定値 30日）',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'forecasting',
                'description' => '日次平均キャッシュフロー（バーンレート / ランレート）に基づき、X日後の残高予測を算出します。',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'days_ahead' => [
                            'type' => 'INTEGER',
                            'description' => '予測する将来の日数（既定値 30日）',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Execute a tool by name with arguments
     */
    public static function executeTool(string $name, array $args = []): array
    {
        return match ($name) {
            'get_transaction' => self::getTransaction($args),
            'search_transaction' => self::searchTransaction($args),
            'create_draft' => self::createDraft($args),
            'category_transaction' => self::categoryTransaction($args),
            'cash_flow' => self::cashFlow($args),
            'income_summary' => self::incomeSummary($args),
            'expense_summary' => self::expenseSummary($args),
            'balance_summary' => self::balanceSummary($args),
            'trend_analysis' => self::trendAnalysis($args),
            'forecasting' => self::forecasting($args),
            default => ['error' => "Unknown tool {$name}"],
        };
    }

    // 1. Transaction Tools
    private static function getTransaction(array $args): array
    {
        $id = $args['transaction_id'] ?? null;
        if (! $id) {
            return ['error' => 'transaction_id is required'];
        }

        $tx = Transaction::with(['category', 'account', 'creator'])->find($id);
        if (! $tx) {
            return ['error' => "取引ID #{$id} は見つかりませんでした。"];
        }

        return [
            'id' => $tx->id,
            'date' => $tx->date->format('Y-m-d'),
            'type' => $tx->type,
            'amount' => (float) $tx->amount,
            'amount_formatted' => '¥'.number_format($tx->amount),
            'category' => $tx->category?->name ?? '未分類',
            'account' => $tx->account?->name ?? 'メイン口座',
            'payment_method' => $tx->payment_method,
            'description' => $tx->description,
            'created_by' => $tx->creator?->name ?? 'システム',
            'created_at' => $tx->created_at->format('Y-m-d H:i:s'),
        ];
    }

    private static function searchTransaction(array $args): array
    {
        $query = Transaction::with(['category', 'account'])->latest('date');

        if (! empty($args['keyword'])) {
            $query->where('description', 'like', '%'.$args['keyword'].'%');
        }

        if (! empty($args['type'])) {
            $query->where('type', $args['type']);
        }

        if (! empty($args['start_date'])) {
            $query->where('date', '>=', $args['start_date']);
        }

        if (! empty($args['end_date'])) {
            $query->where('date', '<=', $args['end_date']);
        }

        $limit = min($args['limit'] ?? 10, 50);
        $results = $query->take($limit)->get()->map(function ($tx) {
            return [
                'id' => $tx->id,
                'date' => $tx->date->format('Y-m-d'),
                'type' => $tx->type,
                'amount' => (float) $tx->amount,
                'amount_formatted' => '¥'.number_format($tx->amount),
                'category' => $tx->category?->name,
                'account' => $tx->account?->name,
                'payment_method' => $tx->payment_method,
                'description' => $tx->description,
            ];
        });

        return [
            'total_found' => $results->count(),
            'transactions' => $results->toArray(),
        ];
    }

    private static function createDraft(array $args): array
    {
        $type = $args['type'] ?? 'cash_out';
        $amount = (float) ($args['amount'] ?? 0);
        $desc = $args['description'] ?? '新規取引';
        $date = $args['date'] ?? Carbon::today()->format('Y-m-d');
        $paymentMethod = $args['payment_method'] ?? '銀行振込';

        // Find or suggest Category
        $categoryName = $args['category_name'] ?? null;
        $category = null;
        if ($categoryName) {
            $category = Category::where('name', 'like', "%{$categoryName}%")->first();
        }
        if (! $category) {
            // Pick default based on type
            $category = Category::where('type', $type)->first();
        }

        // Find or suggest Account
        $accountName = $args['account_name'] ?? null;
        $account = null;
        if ($accountName) {
            $account = Account::where('name', 'like', "%{$accountName}%")->first();
        }
        if (! $account) {
            $account = Account::first();
        }

        return [
            'status' => 'draft_created',
            'action_required' => 'confirm_or_edit',
            'draft' => [
                'date' => $date,
                'type' => $type,
                'amount' => $amount,
                'amount_formatted' => '¥'.number_format($amount),
                'category_id' => $category?->id,
                'category_name' => $category?->name ?? '一般',
                'account_id' => $account?->id,
                'account_name' => $account?->name ?? 'メイン口座',
                'payment_method' => $paymentMethod,
                'description' => $desc,
            ],
            'available_accounts' => Account::where('is_active', true)->get(['id', 'name'])->toArray(),
            'available_categories' => Category::where('type', $type)->get(['id', 'name'])->toArray(),
        ];
    }

    private static function categoryTransaction(array $args): array
    {
        $desc = strtolower($args['description'] ?? '');
        $categories = Category::all();

        $recommended = null;
        $detectedType = 'cash_out';

        // Heuristic detection: income keywords first
        if (preg_match('/(sale|receive|deposit|invoice|revenue|fee|income|売上|入金|収入|受取|請求)/iu', $desc)) {
            $detectedType = 'cash_in';
        }

        foreach ($categories as $cat) {
            if (str_contains($desc, strtolower($cat->name))) {
                $recommended = $cat;
                break;
            }
        }

        if (! $recommended) {
            if ($detectedType === 'cash_in') {
                $recommended = $categories->firstWhere('type', 'cash_in');
            } else {
                $recommended = $categories->firstWhere('type', 'cash_out');
            }
        }

        return [
            'detected_type' => $detectedType,
            'recommended_category' => [
                'id' => $recommended?->id,
                'name' => $recommended?->name,
                'type' => $recommended?->type,
            ],
            'all_matching_categories' => $categories->where('type', $detectedType)->values()->toArray(),
        ];
    }

    // 2. Financial Tools
    private static function cashFlow(array $args): array
    {
        $startDate = $args['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $args['end_date'] ?? Carbon::now()->format('Y-m-d');

        $cashIn = (float) Transaction::where('type', 'cash_in')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $cashOut = (float) Transaction::where('type', 'cash_out')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $netFlow = $cashIn - $cashOut;

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'total_cash_in' => $cashIn,
            'total_cash_in_formatted' => '¥'.number_format($cashIn),
            'total_cash_out' => $cashOut,
            'total_cash_out_formatted' => '¥'.number_format($cashOut),
            'net_cash_flow' => $netFlow,
            'net_cash_flow_formatted' => ($netFlow >= 0 ? '+' : '-').'¥'.number_format(abs($netFlow)),
            'status' => $netFlow >= 0 ? '黒字（プラス）' : '赤字（マイナス）',
            'status_en' => $netFlow >= 0 ? 'surplus' : 'deficit',
        ];
    }

    private static function incomeSummary(array $args): array
    {
        $startDate = $args['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $args['end_date'] ?? Carbon::now()->format('Y-m-d');

        $byCategory = Transaction::where('transactions.type', 'cash_in')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 'categories.name_en as category_name_en', DB::raw('SUM(transactions.amount) as total_amount'), DB::raw('COUNT(transactions.id) as total_count'))
            ->groupBy('categories.id', 'categories.name', 'categories.name_en')
            ->orderByDesc('total_amount')
            ->get();

        $totalIncome = $byCategory->sum('total_amount');

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'total_income' => (float) $totalIncome,
            'total_income_formatted' => '¥'.number_format($totalIncome),
            'breakdown_by_category' => $byCategory->map(fn ($item) => [
                'category' => $item->category_name,
                'category_en' => $item->category_name_en ?? $item->category_name,
                'amount' => (float) $item->total_amount,
                'amount_formatted' => '¥'.number_format($item->total_amount),
                'percentage' => $totalIncome > 0 ? round(($item->total_amount / $totalIncome) * 100, 1).'%' : '0%',
                'count' => $item->total_count,
            ])->toArray(),
        ];
    }

    private static function expenseSummary(array $args): array
    {
        $startDate = $args['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $args['end_date'] ?? Carbon::now()->format('Y-m-d');

        $byCategory = Transaction::where('transactions.type', 'cash_out')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 'categories.name_en as category_name_en', DB::raw('SUM(transactions.amount) as total_amount'), DB::raw('COUNT(transactions.id) as total_count'))
            ->groupBy('categories.id', 'categories.name', 'categories.name_en')
            ->orderByDesc('total_amount')
            ->get();

        $totalExpense = $byCategory->sum('total_amount');

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'total_expense' => (float) $totalExpense,
            'total_expense_formatted' => '¥'.number_format($totalExpense),
            'breakdown_by_category' => $byCategory->map(fn ($item) => [
                'category' => $item->category_name,
                'category_en' => $item->category_name_en ?? $item->category_name,
                'amount' => (float) $item->total_amount,
                'amount_formatted' => '¥'.number_format($item->total_amount),
                'percentage' => $totalExpense > 0 ? round(($item->total_amount / $totalExpense) * 100, 1).'%' : '0%',
                'count' => $item->total_count,
            ])->toArray(),
        ];
    }

    private static function balanceSummary(array $args): array
    {
        $accountId = $args['account_id'] ?? null;
        $accountsQuery = Account::where('is_active', true);

        if ($accountId) {
            $accountsQuery->where('id', $accountId);
        }

        $accounts = $accountsQuery->get()->map(function ($acc) {
            $in = (float) $acc->transactions()->where('type', 'cash_in')->sum('amount');
            $out = (float) $acc->transactions()->where('type', 'cash_out')->sum('amount');
            $currentBalance = (float) ($acc->initial_balance + $in - $out);

            return [
                'id' => $acc->id,
                'name' => $acc->name,
                'name_en' => $acc->name_en ?? $acc->name,
                'account_number' => $acc->account_number,
                'initial_balance' => (float) $acc->initial_balance,
                'total_in' => $in,
                'total_out' => $out,
                'current_balance' => $currentBalance,
                'current_balance_formatted' => '¥'.number_format($currentBalance),
            ];
        });

        $totalCashInAll = $accounts->sum('current_balance');

        return [
            'total_liquid_cash' => (float) $totalCashInAll,
            'total_liquid_cash_formatted' => '¥'.number_format($totalCashInAll),
            'accounts' => $accounts->toArray(),
        ];
    }

    // 3. Analysis Tools
    private static function trendAnalysis(array $args): array
    {
        $days = $args['days'] ?? 30;
        $startDate = Carbon::today()->subDays($days)->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        $in = (float) Transaction::where('type', 'cash_in')->whereBetween('date', [$startDate, $endDate])->sum('amount');
        $out = (float) Transaction::where('type', 'cash_out')->whereBetween('date', [$startDate, $endDate])->sum('amount');

        $avgDailyIn = $days > 0 ? $in / $days : 0;
        $avgDailyOut = $days > 0 ? $out / $days : 0;
        $netDaily = $avgDailyIn - $avgDailyOut;

        // Group by 7-day windows for trend comparison
        $halfPeriod = (int) ($days / 2);
        $midDate = Carbon::today()->subDays($halfPeriod)->format('Y-m-d');

        $recentIn = (float) Transaction::where('type', 'cash_in')->whereBetween('date', [$midDate, $endDate])->sum('amount');
        $prevIn = (float) Transaction::where('type', 'cash_in')->whereBetween('date', [$startDate, $midDate])->sum('amount');

        $growthRate = $prevIn > 0 ? round((($recentIn - $prevIn) / $prevIn) * 100, 2) : 0;

        return [
            'period_analyzed' => "過去 {$days} 日間（{$startDate} 〜 {$endDate}）",
            'period_analyzed_en' => "last {$days} days ({$startDate} to {$endDate})",
            'avg_daily_income' => round($avgDailyIn, 2),
            'avg_daily_income_formatted' => '¥'.number_format($avgDailyIn),
            'avg_daily_burn_rate' => round($avgDailyOut, 2),
            'avg_daily_burn_rate_formatted' => '¥'.number_format($avgDailyOut),
            'net_daily_flow' => round($netDaily, 2),
            'recent_vs_previous_growth' => "{$growthRate}%",
            'health_score' => $in > $out ? '良好（キャッシュフロー黒字）' : '注意（支出が収入を超過）',
            'health_score_en' => $in > $out ? 'healthy (positive cash flow)' : 'warning (expenses exceed income)',
        ];
    }

    private static function forecasting(array $args): array
    {
        $daysAhead = $args['days_ahead'] ?? 30;

        // Balance now
        $balanceData = self::balanceSummary([]);
        $currentBalance = $balanceData['total_liquid_cash'];

        // Historical 30 days burn rate & run rate
        $trends = self::trendAnalysis(['days' => 30]);
        $netDaily = $trends['net_daily_flow'];

        $projectedNetChange = $netDaily * $daysAhead;
        $projectedBalance = $currentBalance + $projectedNetChange;

        $runwayDays = null;
        if ($trends['avg_daily_burn_rate'] > 0) {
            $runwayDays = round($currentBalance / $trends['avg_daily_burn_rate']);
        }

        return [
            'current_balance' => $currentBalance,
            'current_balance_formatted' => '¥'.number_format($currentBalance),
            'projection_horizon' => "今後 {$daysAhead} 日間",
            'projection_horizon_en' => "next {$daysAhead} days",
            'daily_net_velocity' => $netDaily,
            'daily_net_velocity_formatted' => ($netDaily >= 0 ? '+' : '-').'¥'.number_format(abs($netDaily)),
            'projected_cash_position' => $projectedBalance,
            'projected_cash_position_formatted' => '¥'.number_format($projectedBalance),
            'cash_runway' => $runwayDays ? "推定 {$runwayDays} 日（追加収入がない場合）" : '無制限',
            'cash_runway_en' => $runwayDays ? "estimated {$runwayDays} days (assuming no additional income)" : 'unlimited',
            'recommended_actions' => $projectedBalance < $currentBalance
                ? ['支出の大きい項目を抑制する', '売掛金の回収を促進する']
                : ['キャッシュフローは黒字です。設備投資や予備資金の確保を検討してください'],
            'recommended_actions_en' => $projectedBalance < $currentBalance
                ? ['Reduce the largest expense categories', 'Accelerate collection of accounts receivable']
                : ['Cash flow is positive. Consider capital investment or building a cash reserve'],
        ];
    }
}
