<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount('transactions');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && in_array($request->input('role'), ['admin', 'staff'])) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => (bool) $user->is_active,
                    'transactions_count' => $user->transactions_count,
                    'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created user (staff or admin).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        $roleLabel = $user->role === 'admin' ? '管理者' : 'スタッフ';

        return response()->json([
            'message' => "{$roleLabel}アカウントを作成しました。",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'transactions_count' => 0,
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * Update the specified user's information.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validated();

        // Anti-lockout: Admin cannot demote themselves
        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return response()->json([
                'message' => '自分自身の管理者権限を解除することはできません。',
            ], 422);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'ユーザー情報を更新しました。',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'transactions_count' => $user->transactions()->count(),
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Reset the user's password and revoke active sessions.
     */
    public function resetPassword(ResetPasswordRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validated();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return response()->json([
            'message' => "{$user->name} のパスワードをリセットしました。すべてのログインセッションを切断しました。",
        ]);
    }

    /**
     * Toggle active/inactive status of a user.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Anti-lockout: Admin cannot deactivate themselves
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => '自分自身のアカウントを無効化することはできません。',
            ], 422);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        if (! $user->is_active) {
            // Revoke tokens if deactivated
            $user->tokens()->delete();
        }

        $statusText = $user->is_active ? '有効化' : '無効化';

        return response()->json([
            'message' => "{$user->name} のアカウントを{$statusText}しました。",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'transactions_count' => $user->transactions()->count(),
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
