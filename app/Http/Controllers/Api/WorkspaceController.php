<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkspaceResource;
use App\Http\Resources\WorkspaceSummaryResource;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Support\CurrentWorkspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller {
    /** GET /workspaces — every workspace the caller belongs to, for the switcher. */
    public function index(Request $request): AnonymousResourceCollection {
        $memberships = WorkspaceMember::query()
            ->where('user_id', $request->user()->id)
            ->with('workspace')
            ->get()
            ->sortBy(fn (WorkspaceMember $m) => $m->workspace->name)
            ->values();

        return WorkspaceSummaryResource::collection($memberships);
    }

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

    /** GET /workspace — the workspace named by the X-Workspace header. */
    public function show(CurrentWorkspace $current): WorkspaceResource {
        return new WorkspaceResource($current->get());
    }
}
