<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller {
    public function store(Request $request): JsonResponse {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Your email address is already verified.']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'A new verification link has been sent.'], 202);
    }
}
