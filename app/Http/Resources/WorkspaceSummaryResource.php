<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** One entry in the workspace switcher. Wraps a WorkspaceMember. */
class WorkspaceSummaryResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'       => $this->workspace->id,
            'name'     => $this->workspace->name,
            'slug'     => $this->workspace->slug,
            'is_owner' => $this->workspace->isOwnedBy($request->user()),
            'role'     => $this->role ? ['id' => $this->role->id, 'name' => $this->role->name, 'label' => $this->role->label] : null,
        ];
    }
}
