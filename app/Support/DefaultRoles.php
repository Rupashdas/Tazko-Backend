<?php

namespace App\Support;

use App\Models\Role;
use App\Models\Workspace;

/**
 * The roles every new workspace starts with. A starting point, not a
 * hierarchy: the owner can rename, change or delete them afterwards.
 */
final class DefaultRoles {
    /** '*' = every capability in the registry, so Admin never falls behind a new one. */
    public const DEFINITIONS = [
        'admin'  => ['label' => 'Admin',  'capabilities' => '*'],
        'member' => ['label' => 'Member', 'capabilities' => ['members.view', 'roles.view']],
        'guest'  => ['label' => 'Guest',  'capabilities' => []],
    ];

    /** @return list<string> */
    public static function capabilitiesFor(string $role): array {
        $capabilities = self::DEFINITIONS[$role]['capabilities'];

        return $capabilities === '*' ? CapabilityRegistry::names() : $capabilities;
    }

    /** @return array<string, Role> keyed by role name */
    public static function createFor(Workspace $workspace): array {
        $roles = [];

        foreach (self::DEFINITIONS as $name => $definition) {
            $role = Role::create([
                'workspace_id' => $workspace->id,
                'name'         => $name,
                'label'        => $definition['label'],
            ]);
            $role->syncCapabilities(self::capabilitiesFor($name));
            $roles[$name] = $role;
        }

        return $roles;
    }
}
