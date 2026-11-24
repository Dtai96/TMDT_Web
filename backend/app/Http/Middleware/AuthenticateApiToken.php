<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'Vui lòng đăng nhập.'], 401);
        }
        $record = DB::table('api_tokens')->where('token_hash', hash('sha256', $token))->where('expires_at', '>', now())->first();
        $user = $record ? User::find($record->user_id) : null;
        if (! $user || ! $user->is_active) {
            return response()->json(['message' => 'Phiên đăng nhập không hợp lệ hoặc đã hết hạn.'], 401);
        }
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
