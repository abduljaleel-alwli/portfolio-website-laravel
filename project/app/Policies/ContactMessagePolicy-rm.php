<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContactMessage;

class ContactMessagePolicy
{
    public function view(User $user, ContactMessage $message): bool
    {
        return $user->hasRole(['admin', 'super-admin']);
    }
}
