<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // If 'all' is not true, only return active categories (for transaction entry forms)
        if (! $request->boolean('all')) {
            $query->where('is_active', true);
        }

        if ($request->filled('type') && in_array($request->type, ['cash_in', 'cash_out'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->withCount('transactions')
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'name_en' => $cat->name_en,
                    'name_ja' => $cat->name_ja,
                    'type' => $cat->type,
                    'description' => $cat->description,
                    'is_active' => (bool) $cat->is_active,
                    'transactions_count' => $cat->transactions_count,
                    'created_at' => $cat->created_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $category = Category::create([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? null,
            'name_ja' => $validated['name_ja'] ?? null,
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'カテゴリーを追加しました',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'name_en' => $category->name_en,
                'name_ja' => $category->name_ja,
                'type' => $category->type,
                'description' => $category->description,
                'is_active' => (bool) $category->is_active,
                'transactions_count' => 0,
            ],
        ], 201);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::withCount('transactions')->findOrFail($id);

        $validated = $request->validated();

        // Accounting Integrity Check:
        // Category type (Cash In vs Cash Out) cannot be altered once transactions exist.
        if ($category->transactions_count > 0 && $validated['type'] !== $category->type) {
            $currentType = $category->type === 'cash_in' ? '収入（Cash In）' : '支出（Cash Out）';

            return response()->json([
                'message' => "「{$category->name}」は {$category->transactions_count} 件の取引で使用されているため、カテゴリー種別を {$currentType} から変更できません。種別を変更するとキャッシュフローの整合性が損なわれます。",
            ], 422);
        }

        $category->name = $validated['name'];
        if (array_key_exists('name_en', $validated)) {
            $category->name_en = $validated['name_en'];
        }
        if (array_key_exists('name_ja', $validated)) {
            $category->name_ja = $validated['name_ja'];
        }
        $category->type = $validated['type'];
        $category->description = $validated['description'] ?? null;
        $category->save();

        return response()->json([
            'message' => 'カテゴリーを更新しました',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'name_en' => $category->name_en,
                'name_ja' => $category->name_ja,
                'type' => $category->type,
                'description' => $category->description,
                'is_active' => (bool) $category->is_active,
                'transactions_count' => $category->transactions_count,
            ],
        ]);
    }

    /**
     * Toggle active/inactive status of a category.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $category = Category::withCount('transactions')->findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        $statusText = $category->is_active ? '有効化' : '無効化';

        return response()->json([
            'message' => "「{$category->name}」を{$statusText}しました。",
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'is_active' => (bool) $category->is_active,
                'transactions_count' => $category->transactions_count,
            ],
        ]);
    }

    /**
     * Remove the specified category if it has no transactions.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $category = Category::withCount('transactions')->findOrFail($id);

        if ($category->transactions_count > 0) {
            return response()->json([
                'message' => "「{$category->name}」は {$category->transactions_count} 件の取引で使用されているため削除できません。履歴を保持するため、カテゴリーの無効化機能をご利用ください。",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => "「{$category->name}」を完全に削除しました。",
        ]);
    }
}
