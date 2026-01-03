<?php

use Livewire\Volt\Component;
use App\Actions\Contact\StoreContactMessage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageMail;
use App\Notifications\NewContactMessageNotification;
use App\Models\User;

new class extends Component {
    public bool $submitting = false;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $message = '';

    public bool $success = false;

    public function submit(StoreContactMessage $store): void
    {
        if ($this->submitting) {
            return;
        }

        $this->submitting = true;

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],
        ]);

        $contactMessage = $store->execute($data, request()->ip());

        app(\App\Services\Analytics\AnalyticsService::class)->track('contact_submitted', [
            'entity_type' => 'contact_message',
            'entity_id' => $contactMessage->id,
            'source' => 'contact_form',
        ]);

        // Notify admins
        User::role(['admin', 'super-admin'])
            ->get()
            ->each(fn($user) => $user->notify(new NewContactMessageNotification($contactMessage)));

        // Send email to admin
        if ($to = settings('contact.email_to')) {
            Mail::to($to)->send(new ContactMessageMail($contactMessage));
        }

        $this->reset(['name', 'email', 'phone', 'message']);

        $this->submitting = false;
        $this->success = true;
    }
};
?>

<div class="af-card af-form-card">
    <h2 class="af-title">تواصل معنا</h2>
    <p class="af-sub">
        اترك بياناتك وسنتواصل معك بأقرب وقت لتجهيز عرض سعر مناسب لطلبك.
    </p>

    <form wire:submit.prevent="submit" class="af-form">
        <div class="af-field">
            <label class="ms-3" for="name">{{ __('Name') }}</label>
            <input type="text" wire:model.defer="name" placeholder="{{ __('Your name') }}"
                class="@error('name') is-invalid @enderror" required>

            @error('name')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="af-field">
            <label class="ms-3" for="email">{{ __('Email') }}</label>
            <input type="email" wire:model.defer="email" placeholder="{{ __('Your email') }}"
                class="@error('email') is-invalid @enderror" required>
            @error('email')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="af-field">
            <label class="ms-3" for="phone">{{ __('Phone') }}</label>
            <input type="phone" wire:model.defer="phone" placeholder="{{ __('05xxxxxxxx') }}"
                class="@error('phone') is-invalid @enderror" required>
            @error('phone')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="af-field">
            <label class="ms-3" for="message">{{ __('Message') }}</label>
            <textarea wire:model.defer="message" placeholder="{{ __('Your message') }}"
                class="@error('message') is-invalid @enderror" required></textarea>

            @error('message')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex items-center gap-3 mt-2">

            <button type="submit" class="af-btn" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">
                    {{ __('Send message') }}
                </span>

                <span wire:loading wire:target="submit" class="af-loading">
                    {{ __('Sending...') }}
                </span>
            </button>

            {{-- Success message --}}
            @if ($success)
                <span class="af-success">
                    {{ __('Your message has been sent successfully') }}
                </span>
            @endif

        </div>

    </form>
</div>
