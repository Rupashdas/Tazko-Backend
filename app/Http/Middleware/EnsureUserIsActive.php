<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive {
    public function handle(Request $request, Closure $next): Response {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            // Only the SPA's cookie requests carry a session. A token client
            // has none, and touching session() there throws — the old app
            // learned that as a 500 instead of this 403.
            if ($request->hasSession()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'code'    => 'account_deactivated',
                'message' => 'This account has been deactivated.',
            ], 403);
        }

        return $next($request);
    }
}
