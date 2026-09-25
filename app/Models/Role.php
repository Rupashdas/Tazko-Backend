<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Role extends Model {
    use BelongsToWorkspace;

    // workspace_id is fillable so CreateWorkspace can seed another
    // workspace's roles. Controllers only ever pass validated keys.
    protected $fillable = ['workspace_id', 'name', 'label'];

    public function members(): HasMany {
        return $this->hasMany(WorkspaceMember::class);
    }

    /** @return list<string> */
    public function capabilityNames(): array {
        return DB::table('role_capabilities')->where('role_id', $this->id)->orderBy('capability')->pluck('capability')->all();
    }

    /** Replace this role's capabilities. Names are checked against the registry in Task 6. */
    public function syncCapabilities(array $names): void {
        DB::transaction(function () use ($names) {
            DB::table('role_capabilities')->where('role_id', $this->id)->delete();
            DB::table('role_capabilities')->insert(
                array_map(fn (string $name) => ['role_id' => $this->id, 'capability' => $name], array_values(array_unique($names))),
            );
        });
    }
}
