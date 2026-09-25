<?php

namespace App\Models;

use App\Support\WorkspaceScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One person's membership of one workspace: their role there and whether
 * their access is switched on.
 */
class WorkspaceMember extends Model {
    protected $fillable = ['workspace_id', 'user_id', 'role_id', 'is_active'];

    protected function casts(): array {
        return ['is_active' => 'boolean'];
    }

    public function workspace(): BelongsTo {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo {
        return $this->belongsTo(Role::class)->withoutGlobalScope(WorkspaceScope::class);
    }
}
