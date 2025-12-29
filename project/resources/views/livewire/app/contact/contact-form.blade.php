<?php

use Livewire\Volt\Component;
use App\Actions\Contact\StoreContactMessage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageMail;
use App\Notifications\NewContactMessageNotification;
use App\Models\User;

new class extends Component {

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $message = '';

    public function submit(StoreContactMessage $store): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],
        ]);

        $contactMessage = $store->execute($data, request()->ip());

        app(\App\Services\Analytics\AnalyticsService::class)
            ->track('contact_submitted', [
                'entity_type' => 'contact_message',
                'entity_id' => $contactMessage->id,
                'source' => 'contact_form',
            ]);

        // Notify admins
        User::role(['admin', 'super-admin'])
            ->get()
            ->each(fn($user) => $user->notify(
                new NewContactMessageNotification($contactMessage)
            ));

        // Send email to admin
        if ($to = settings('contact.email_to')) {
            Mail::to($to)->send(new ContactMessageMail($contactMessage));
        }

        $this->reset(['name', 'email', 'phone', 'message']);

        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: '" . __('Your message has been sent successfully') . "' }
            }));
        ");
    }
};
?>

<form wire:submit.prevent="submit" class="space-y-4">
    <input type="text" wire:model.defer="name" placeholder="{{ __('Your name') }}" class="input w-full" />
    <input type="email" wire:model.defer="email" placeholder="{{ __('Your email') }}" class="input w-full" />
    <input type="phone" wire:model.defer="phone" placeholder="{{ __('Your phone') }}" class="input w-full" />

    <textarea wire:model.defer="message" placeholder="{{ __('Your message') }}" class="textarea w-full"
        rows="4"></textarea>

    <div class="flex justify-end">
        <button type="submit" class="btn-primary">
            {{ __('Send message') }}
        </button>
    </div>
</form>