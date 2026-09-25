<?php

namespace App\Models;

use App\Support\CapabilityRegistry;
use App\Support\WorkspaceScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Role extends Model {
    // workspace_id is fillable so a brand-new workspace can be given its
    // roles before anyone is inside it. Controllers only pass validated keys.
    protected $fillable = ['workspace_id', 'name', 'label'];

    protected static function booted(): void {
        // Every query on roles stays inside the current workspace.
        static::addGlobalScope(new WorkspaceScope());
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
