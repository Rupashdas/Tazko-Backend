<?php

namespace App\Support;

/**
 * Everything a workspace role can allow.
 *
 * These are workspace-level actions only. What someone may do inside a single
 * project is decided by their project role, not here.
 *
 * Every name must gate something. A capability for a feature that does not
 * exist yet is added when the feature ships, not before. (The old app listed
 * 80, and half of them protected nothing.)
 */
final class CapabilityRegistry {
    public static function all(): array {
        return [
            'workspace' => [
                ['name' => 'workspace.settings.manage', 'label' => 'Manage workspace settings'],
            ],
            'members' => [
                ['name' => 'members.view',   'label' => 'View members'],
                ['name' => 'members.invite', 'label' => 'Invite members'],
                ['name' => 'members.manage', 'label' => 'Change roles, deactivate and remove members'],
            ],
            'roles' => [
                ['name' => 'roles.view',   'label' => 'View roles'],
                // Whoever edits roles can grant any capability, including to
                // their own role. Treat it as an admin power.
                ['name' => 'roles.manage', 'label' => 'Create, edit and delete roles'],
            ],
        ];
    }

    /** @return list<string> */
    public static function names(): array {
        return array_merge(...array_map(
            fn (array $capabilities) => array_column($capabilities, 'name'),
            array_values(self::all()),
        ));
    }

    public static function exists(string $name): bool {
        return in_array($name, self::names(), true);
    }
}
