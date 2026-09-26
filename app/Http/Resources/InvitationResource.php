<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role ? ['id' => $this->role->id, 'name' => $this->role->name, 'label' => $this->role->label] : null,
            'invited_by' => ['id' => $this->invitedBy->id, 'name' => $this->invitedBy->name],
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
