<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable {
    use Queueable, SerializesModels;

    public string $link;

    public function __construct(public Invitation $invitation) {
        $this->link = rtrim(config('app.frontend_url'), '/') . '/invitations/' . $invitation->token;
    }

    public function envelope(): Envelope {
        return new Envelope(subject: "Join {$this->invitation->workspace->name} on " . config('app.name'));
    }

    public function content(): Content {
        return new Content(view: 'emails.invitation', with: [
            'invitation' => $this->invitation,
            'link'       => $this->link,
            'appName'    => config('app.name'),
            'days'       => Invitation::LIFETIME_DAYS,
        ]);
    }
}
