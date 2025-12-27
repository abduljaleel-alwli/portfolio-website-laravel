<?php

use Livewire\Volt\Component;
use App\Actions\Contact\StoreContactMessage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageMail;
use App\Notifications\NewContactMessageNotification;
use App\Models\User;
use Illuminate\Http\Request;

new class extends Component {

    public string $name = '';
    public string $email = '';
    public string $message = '';

    public function submit(StoreContactMessage $store): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $message = $store->execute($data, request()->ip());

        // Notify admins
        User::role(['admin', 'super-admin'])
            ->get()
            ->each(
                fn($user) =>
                $user->notify(new NewContactMessageNotification($message))
            );

        // Send email to admin
        if ($to = settings('contact.email_to')) {
            Mail::to($to)->send(
                new ContactMessageMail($message)
            );
        }

        $this->reset(['name', 'email', 'message']);

        $this->js("
        window.dispatchEvent(
            new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: '" . __('Your message has been sent successfully') . "'
                }
            })
        );
    ");
    }
};
?>

<div class="max-w-xl mx-auto space-y-6">
    <h1 class="text-3xl font-bold">
        {{ __('Contact us') }}
    </h1>

    <section class="space-y-6 max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold">{{ settings('contact.title') }}</h1>

        <p class="text-gray-700">
            {{ settings('contact.description') }}
        </p>

        @if (settings('contact.phone'))
            <p><strong>{{ __('Phone') }}:</strong> {{ settings('contact.phone') }}</p>
        @endif

        @if (settings('contact.map_url'))
            <iframe src="{{ settings('contact.map_url') }}" class="w-full h-64 border rounded" loading="lazy"></iframe>
        @endif

        <div class="flex gap-4">
            @foreach ((array) settings('contact.social_links', []) as $social)
                <a href="{{ $social['url'] }}" target="_blank" class="text-blue-600">
                    {{ ucfirst($social['platform']) }}
                </a>
            @endforeach
        </div>

        {{-- Contact Form --}}
        @livewire('contact.contact-form')
    </section>
</div>