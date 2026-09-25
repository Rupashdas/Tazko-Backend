<?php

namespace App\Support;

use App\Models\Workspace;
use LogicException;

/**
 * The workspace the current request is acting in. ResolveWorkspace sets it;
 * anything later in the same request reads it.
 */
final class CurrentWorkspace {
    private ?Workspace $workspace = null;

    public function set(Workspace $workspace): void {
        $this->workspace = $workspace;
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
}
