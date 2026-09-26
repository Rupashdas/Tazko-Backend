<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CapabilityRegistry;
use Illuminate\Http\JsonResponse;

class CapabilityController extends Controller {
    /** GET /capabilities — what the role editor can offer, grouped by module. */
    public function index(): JsonResponse {
        return response()->json([
            'data' => collect(CapabilityRegistry::all())
                ->map(fn (array $capabilities, string $module) => ['module' => $module, 'capabilities' => $capabilities])
                ->values(),
        ]);
    }
}
