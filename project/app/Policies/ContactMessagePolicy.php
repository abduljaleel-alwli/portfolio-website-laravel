<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContactMessage;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super-admin']);
    }

    public function view(User $user, ContactMessage $message): bool
    {
        return $user->hasRole(['admin', 'super-admin']);
    }
}
