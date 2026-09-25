<?php

namespace App\Http\Middleware;

use App\Support\CurrentWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `capability:members.invite` — the caller must hold every named capability
 * in the current workspace. Runs after ResolveWorkspace.
 */
class RequiresCapability {
    public function handle(Request $request, Closure $next, string ...$capabilities): Response {
        $current = app(CurrentWorkspace::class);

        foreach ($capabilities as $capability) {
            if (! $current->allows($capability)) {
                return response()->json([
                    'code'       => 'missing_capability',
                    'message'    => 'You do not have permission to do this.',
                    'capability' => $capability,
                ], 403);
            }
        }

        return $next($request);
    }
}
