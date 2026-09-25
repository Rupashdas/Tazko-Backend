<?php

namespace App\Http\Resources;

use App\Support\CurrentWorkspace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'slug'  => $this->slug,
            'owner' => [
                'id'     => $this->owner->id,
                'name'   => $this->owner->name,
                'avatar' => $this->owner->avatarUrl(),
            ],
            // Only a request inside this workspace has a "me" to describe.
            // POST /workspaces answers without it; the SPA opens the new
            // workspace next, and GET /workspace carries it.
            'me' => $this->when(
                app(CurrentWorkspace::class)->has(),
                fn() => $this->me(
                    app(CurrentWorkspace::class)
                )
            ),
        ];
    }

    private function me(CurrentWorkspace $current): array {
        $role = $current->membership()->role;

        return [
            'is_owner'     => $current->isOwner(),
            'role'         => $role ? ['id' => $role->id, 'name' => $role->name, 'label' => $role->label] : null,
            'capabilities' => $current->capabilities(),
        ];
    }
}
