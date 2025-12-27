<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $contactMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this
            ->subject(
                settings('contact.email_subject', __('New contact message'))
            )
            ->view('emails.contact-message', [
                'message' => $this->contactMessage,
            ]);
    }
}
