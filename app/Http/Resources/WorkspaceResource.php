<?php

namespace App\Http\Resources;

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
            'me' => [
                'is_owner' => $this->isOwnedBy($request->user()),
            ],
        ];
    }
}
