<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model {
    use BelongsToWorkspace;

    public const LIFETIME_DAYS = 7;

    // No workspace_id: it always comes from CurrentWorkspace via BelongsToWorkspace.
    protected $fillable = ['email', 'name', 'role_id', 'invited_by', 'token', 'expires_at', 'accepted_at'];

    protected function casts(): array {
        return ['expires_at' => 'datetime'];
    }

    public function role(): BelongsTo {
        return $this->belongsTo(Role::class);
    }

    public function invitedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * 64 random characters, re-drawn on the astronomically unlikely clash.
     * Tokens are unique across every workspace, so the scope is dropped.
     */
    public static function generateToken(): string {
        do {
            $token = Str::random(64);
        } while (static::withoutGlobalScopes()->where('token', $token)->exists());

        return $token;
    }

    public function isAccepted(): bool {
        return $this->accepted_at !== null;
    }

    public function isExpired(): bool {
        return $this->expires_at->isPast();
    }

    public function status(): string {
        return $this->isExpired() ? 'expired' : 'pending';
    }
}
