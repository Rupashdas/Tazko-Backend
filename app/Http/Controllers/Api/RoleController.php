<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller {
    /** GET /roles — no workspace filter here: Role's scope adds it. */
    public function index(): AnonymousResourceCollection {
        return RoleResource::collection(Role::orderBy('id')->get());
    }
}
