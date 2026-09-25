<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Keeps every query on a workspace-owned model inside the current workspace.
 *
 * With no workspace set (console commands, seeders, requests outside a
 * workspace) it adds nothing, so those can see across workspaces on purpose.
 */
final class WorkspaceScope implements Scope {
    public function apply(Builder $builder, Model $model): void {
        $current = app(CurrentWorkspace::class);

        if ($current->has()) {
            $builder->where($model->qualifyColumn('workspace_id'), $current->id());
        }
    }
}
