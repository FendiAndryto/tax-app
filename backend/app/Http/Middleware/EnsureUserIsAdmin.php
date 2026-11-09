<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => '認証されていません。',
            ], 401);
        }

        if (! $user->is_active) {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'アカウントは管理者によって無効化されています。',
            ], 403);
        }

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'アクセスが拒否されました。この機能は管理者のみ利用できます。',
            ], 403);
        }

        return $next($request);
    }
}
