<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Workspace extends Model {
    /** Paths the frontend or a future marketing site may need. */
    public const RESERVED_SLUGS = [
        'api', 'admin', 'app', 'login', 'logout', 'register', 'signup', 'settings',
        'www', 'invite', 'invitations', 'share', 'help', 'support',
    ];

    protected $fillable = ['name', 'slug', 'owner_id'];

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOwnedBy(User $user): bool {
        return (int) $this->owner_id === (int) $user->id;
    }

    /** Validation for a slug someone typed. */
    public static function slugRules(): array {
        return [
            'string', 'min:3', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::notIn(self::RESERVED_SLUGS),
            Rule::unique('workspaces', 'slug'),
        ];
    }

    /** "Acme Studio" → "acme-studio", or "acme-studio-2" if that is taken. */
    public static function uniqueSlugFrom(string $name): string {
        $base = substr(Str::slug($name), 0, 50);

        if (strlen($base) < 3) {
            $base = trim($base . '-team', '-');
        }

        $slug = $base;
        $n = 2;

        while (in_array($slug, self::RESERVED_SLUGS, true) || static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
