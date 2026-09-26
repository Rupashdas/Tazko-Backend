<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One person's membership of one workspace. */
class WorkspaceMember extends Model {
    protected $fillable = ['workspace_id', 'user_id', 'role_id'];

    public function workspace(): BelongsTo {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo {
        return $this->belongsTo(Role::class);
    }
}
