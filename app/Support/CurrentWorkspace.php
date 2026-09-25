<?php

namespace App\Support;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use LogicException;

/**
 * The workspace the current request is acting in, and the caller's
 * membership of it. ResolveWorkspace sets both; anything later in the same
 * request reads them.
 */
final class CurrentWorkspace {
    private ?Workspace $workspace = null;
    private ?WorkspaceMember $membership = null;

    public function set(Workspace $workspace, WorkspaceMember $membership): void {
        $this->workspace  = $workspace;
        $this->membership = $membership;
    }

    public function get(): ?Workspace {
        return $this->workspace;
    }

    public function has(): bool {
        return $this->workspace !== null;
    }

    public function id(): int {
        if ($this->workspace === null) {
            throw new LogicException('No workspace is set for this request.');
        }

        return $this->workspace->id;
    }

    public function membership(): ?WorkspaceMember {
        return $this->membership;
    }

    /** Whether the person making this request owns this workspace. */
    public function isOwner(): bool {
        return $this->membership !== null
            && (int) $this->workspace->owner_id === (int) $this->membership->user_id;
    }

    /**
     * What the person making this request may do here. The owner may do
     * everything, in their own workspace only; everyone else gets what their
     * role here allows.
     *
     * @return list<string>
     */
    public function capabilities(): array {
        if ($this->isOwner()) {
            return CapabilityRegistry::names();
        }

        return $this->membership?->role?->capabilityNames() ?? [];
    }

    public function allows(string $capability): bool {
        return in_array($capability, $this->capabilities(), true);
    }
}
