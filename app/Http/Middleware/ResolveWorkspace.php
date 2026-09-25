<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Support\CurrentWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the request inside the workspace named by the X-Workspace header,
 * after checking the caller really is a member of it.
 */
class ResolveWorkspace {
    public function handle(Request $request, Closure $next): Response {
        $slug = $request->header('X-Workspace');

        if (! $slug) {
            return response()->json(['code' => 'workspace_missing', 'message' => 'No workspace selected.'], 400);
        }

        $workspace = Workspace::where('slug', $slug)->first();

        $isMember = $workspace
            && WorkspaceMember::where('workspace_id', $workspace->id)->where('user_id', $request->user()->id)->exists();

        // Not found and not yours look the same, so a slug cannot be probed
        // to learn that a workspace exists.
        if (! $isMember) {
            return response()->json(['code' => 'workspace_not_found', 'message' => 'Workspace not found.'], 404);
        }

        app(CurrentWorkspace::class)->set($workspace);

        return $next($request);
    }
}
