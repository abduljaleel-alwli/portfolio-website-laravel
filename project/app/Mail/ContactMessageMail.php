<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contact
    ) {}

    public function build(): self
    {
        return $this
            ->subject(
                settings('contact.email_subject', __('New contact message'))
            )
            ->view('emails.contact-message', [
                'contact' => $this->contact,
            ]);
    }
}
