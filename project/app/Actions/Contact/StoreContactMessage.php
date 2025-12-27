<?php

namespace App\Actions\Contact;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class StoreContactMessage
{
    /**
     * Store a new contact message.
     */
    public function execute(array $data, ?string $ipAddress = null): ContactMessage
    {
        // Public action – no auth required
        return ContactMessage::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'message'    => $data['message'],
            'ip_address' => $ipAddress,
        ]);
    }
}
