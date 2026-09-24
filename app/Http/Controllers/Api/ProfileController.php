<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller {
    public function update(UpdateProfileRequest $request): UserResource {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            // The new address has not been proven to be theirs yet.
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();
        }

        return new UserResource($user->load('preference'));
    }

    public function updatePassword(Request $request): Response {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return response()->noContent();
    }

    public function uploadAvatar(Request $request): UserResource {
        $request->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);

        $user = $request->user();
        $previous = $user->avatar;

        $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);

        // Delete the old file only after the new one is saved, so a failed
        // upload never leaves the user with no picture at all.
        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        return new UserResource($user->load('preference'));
    }

    public function removeAvatar(Request $request): UserResource {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return new UserResource($user->load('preference'));
    }
}
