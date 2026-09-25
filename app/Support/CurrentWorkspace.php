<?php

namespace App\Support;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use LogicException;

/**
 * The workspace the current request is acting in.
 *
 * ResolveWorkspace sets it; WorkspaceScope, the permission helpers on User and
 * anything that creates workspace-owned rows read it. It is bound as a scoped
 * instance, so it can never carry over from one request or queued job to the
 * next.
 */
final class CurrentWorkspace {
    private ?Workspace $workspace = null;
    private ?WorkspaceMember $membership = null;

    public function set(Workspace $workspace, ?WorkspaceMember $membership = null): void {
        $this->workspace  = $workspace;
        $this->membership = $membership;
    }

    public function clear(): void {
        $this->workspace  = null;
        $this->membership = null;
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
}
