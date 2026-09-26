<?php

namespace App\Models;

use App\Support\CapabilityRegistry;
use App\Support\CurrentWorkspace;
use App\Support\WorkspaceScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class Role extends Model {
    // workspace_id is fillable so a brand-new workspace can be given its
    // roles before anyone is inside it. Controllers only pass validated keys.
    protected $fillable = ['workspace_id', 'name', 'label'];

    protected static function booted(): void {
        // Every query on roles stays inside the current workspace.
        static::addGlobalScope(new WorkspaceScope());

        // A role made inside a workspace request belongs to that workspace.
        static::creating(function (Role $role) {
            if ($role->workspace_id) {
                return;
            }

            $current = app(CurrentWorkspace::class);

            // A role with no workspace would belong to everyone and no one.
            if (! $current->has()) {
                throw new LogicException('A role needs a workspace: none was given and none is set.');
            }

            $role->workspace_id = $current->id();
        });
    }

    public function members(): HasMany {
        return $this->hasMany(WorkspaceMember::class);
    }

    /** @return list<string> */
    public function capabilityNames(): array {
        return DB::table('role_capabilities')->where('role_id', $this->id)->orderBy('capability')->pluck('capability')->all();
    }

    /** Replace this role's capabilities. Every name must be in CapabilityRegistry. */
    public function syncCapabilities(array $names): void {
        $unknown = array_values(array_filter($names, fn (string $name) => ! CapabilityRegistry::exists($name)));

        if ($unknown) {
            throw new InvalidArgumentException('Unknown capabilities: ' . implode(', ', $unknown));
        }

        DB::transaction(function () use ($names) {
            DB::table('role_capabilities')->where('role_id', $this->id)->delete();
            DB::table('role_capabilities')->insert(
                array_map(fn (string $name) => ['role_id' => $this->id, 'capability' => $name], array_values(array_unique($names))),
            );
        });
    }
}
