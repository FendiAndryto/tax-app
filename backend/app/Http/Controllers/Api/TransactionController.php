<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\AI\AiToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['category', 'account', 'creator'])->latest('date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->filled('keyword')) {
            $query->where('description', 'like', '%'.$request->keyword.'%');
        }

        // Calculate summary for this filter
        $statsQuery = clone $query;
        $totalIn = (float) (clone $statsQuery)->where('type', 'cash_in')->sum('amount');
        $totalOut = (float) (clone $statsQuery)->where('type', 'cash_out')->sum('amount');
        $netFlow = $totalIn - $totalOut;

        $perPage = min((int) ($request->per_page ?? 20), 100);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'summary' => [
                'total_cash_in' => $totalIn,
                'total_cash_in_formatted' => '¥'.number_format($totalIn),
                'total_cash_out' => $totalOut,
                'total_cash_out_formatted' => '¥'.number_format($totalOut),
                'net_flow' => $netFlow,
                'net_flow_formatted' => ($netFlow >= 0 ? '+' : '-').'¥'.number_format(abs($netFlow)),
            ],
            'transactions' => $transactions,
        ]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['created_by'] = $request->user()?->id;

        $transaction = Transaction::create($validated);
        $transaction->load(['category', 'account', 'creator']);

        return response()->json([
            'message' => '取引をシステムに記録しました',
            'transaction' => $transaction,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = Transaction::with(['category', 'account', 'creator'])->findOrFail($id);

        return response()->json(['transaction' => $transaction]);
    }

    public function update(UpdateTransactionRequest $request, int $id): JsonResponse
    {
        $transaction = Transaction::findOrFail($id);

        $validated = $request->validated();

        $transaction->update($validated);
        $transaction->load(['category', 'account', 'creator']);

        return response()->json([
            'message' => '取引を更新しました',
            'transaction' => $transaction,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'message' => '取引の削除は管理者のみ実行できます。',
            ], 403);
        }

        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => '取引を削除しました']);
    }

    public function financialOverview(): JsonResponse
    {
        $cf = AiToolRegistry::executeTool('cash_flow', []);
        $balance = AiToolRegistry::executeTool('balance_summary', []);
        $trend = AiToolRegistry::executeTool('trend_analysis', ['days' => 30]);
        $forecast = AiToolRegistry::executeTool('forecasting', ['days_ahead' => 30]);

        return response()->json([
            'cash_flow' => $cf,
            'balances' => $balance,
            'trend' => $trend,
            'forecasting' => $forecast,
        ]);
    }
}
