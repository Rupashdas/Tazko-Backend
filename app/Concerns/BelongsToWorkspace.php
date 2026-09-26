<?php

namespace App\Concerns;

use App\Models\Workspace;
use App\Support\CurrentWorkspace;
use App\Support\WorkspaceScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * For models that live inside one workspace (roles, invitations, and later
 * projects). Reads are scoped to the current workspace; new rows are
 * stamped with it.
 */
trait BelongsToWorkspace {
    public static function bootBelongsToWorkspace(): void {
        static::addGlobalScope(new WorkspaceScope());

        static::creating(function (Model $model) {
            if ($model->workspace_id) {
                return;
            }

            $current = app(CurrentWorkspace::class);

            // A row with no workspace would belong to everyone and no one.
            if (! $current->has()) {
                throw new LogicException(static::class . ' needs a workspace: none was given and none is set.');
            }

            $model->workspace_id = $current->id();
        });
    }

    public function workspace(): BelongsTo {
        return $this->belongsTo(Workspace::class);
    }
}
