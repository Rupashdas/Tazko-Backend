<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One person's membership of one workspace. */
class WorkspaceMember extends Model {
    protected $fillable = ['workspace_id', 'user_id'];
}
