<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
    public function register(RegisterRequest $request): JsonResponse {
        $user = User::create($request->validated());

        // Laravel listens for Registered and sends the verification email.
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return (new UserResource($user->load('preference')))->response()->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            // One message for a wrong password and an unknown email alike.
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::guard('web')->logout();

            return response()->json([
                'code'    => 'account_deactivated',
                'message' => 'This account has been deactivated.',
            ], 403);
        }

        // A new session id after login stops session fixation.
        $request->session()->regenerate();

        return (new UserResource(Auth::user()->load('preference')))->response();
    }

    public function logout(Request $request): Response {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function user(Request $request): JsonResponse {
        // Explicit 200: JsonResource answers 201 for a model it thinks was
        // just created, which the signed-in user can be right after sign up.
        return (new UserResource($request->user()->load('preference')))->response()->setStatusCode(200);
    }
}
