<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller {
    /** POST /workspaces — the caller becomes its owner and first member. */
    public function store(Request $request): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', ...Workspace::slugRules()],
        ]);

        // Both rows or neither: a workspace nobody belongs to could never be opened.
        $workspace = DB::transaction(function () use ($request, $validated) {
            $workspace = Workspace::create([
                'name'     => $validated['name'],
                'slug'     => $validated['slug'] ?? Workspace::uniqueSlugFrom($validated['name']),
                'owner_id' => $request->user()->id,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id'      => $request->user()->id,
            ]);

            return $workspace;
        });

        return (new WorkspaceResource($workspace))->response()->setStatusCode(201);
    }
}
