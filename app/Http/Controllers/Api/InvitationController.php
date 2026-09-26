<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvitationResource;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\WorkspaceMember;
use App\Support\CurrentWorkspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller {
    /*---------------------------------------------------------------------------
    | Inside the workspace
    ---------------------------------------------------------------------------*/

    public function index(): AnonymousResourceCollection {
        return InvitationResource::collection(
            Invitation::whereNull('accepted_at')->with(['role', 'invitedBy'])->latest()->get()
        );
    }

    public function store(Request $request, CurrentWorkspace $current): JsonResponse {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('workspace_id', $current->id())],
        ]);

        $email = Str::lower($validated['email']);

        $alreadyMember = WorkspaceMember::where('workspace_id', $current->id())
            ->whereHas('user', fn($user) => $user->where('email', $email))
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages(['email' => 'That person is already a member of this workspace.']);
        }

        $invitation = DB::transaction(function () use ($validated, $email, $request) {
            // A newer invitation replaces any pending one to the same address.
            Invitation::whereNull('accepted_at')->where('email', $email)->delete();

            return Invitation::create([
                'name'       => $validated['name'],
                'email'      => $email,
                'role_id'    => $validated['role_id'],
                'invited_by' => $request->user()->id,
                'token'      => Invitation::generateToken(),
                'expires_at' => now()->addDays(Invitation::LIFETIME_DAYS),
            ]);
        });

        $this->send($invitation);

        return (new InvitationResource($invitation))->response()->setStatusCode(201);
    }

    public function resend(Invitation $invitation): InvitationResource {
        abort_if($invitation->isAccepted(), 422, 'This invitation has already been used.');

        // A fresh token, so a leaked copy of the old email stops working.
        $invitation->update([
            'token'      => Invitation::generateToken(),
            'expires_at' => now()->addDays(Invitation::LIFETIME_DAYS),
        ]);

        $this->send($invitation);

        return new InvitationResource($invitation);
    }

    /*---------------------------------------------------------------------------
    | Internals
    ---------------------------------------------------------------------------*/
    private function send(Invitation $invitation): void {
        Mail::to($invitation->email)->send(new InvitationMail($invitation->load(['workspace', 'role', 'invitedBy'])));
    }
}
