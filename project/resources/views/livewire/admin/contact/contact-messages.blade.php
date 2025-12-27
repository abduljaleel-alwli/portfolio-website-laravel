<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\ContactMessage;

new class extends Component {
    use AuthorizesRequests;

    public $messages;
    public ?ContactMessage $selected = null;
    public bool $showModal = false;

    public function mount(): void
    {
        $this->authorize('viewAny', ContactMessage::class);
        $this->loadMessages();
    }

    public function loadMessages(): void
    {
        $this->messages = ContactMessage::query()
            ->latest()
            ->get();
    }

    public function view(ContactMessage $message): void
    {
        $this->selected = $message;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->selected = null;
        $this->showModal = false;
    }
};
?>

<div class="space-y-6">
    @include('partials.settings-heading', [
        'title' => __('Contact messages'),
        'description' => __('Messages submitted via the contact form'),
    ])

    <div class="card">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr>
                        <td>{{ $message->name }}</td>
                        <td>{{ $message->email }}</td>
                        <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-right">
                            <button
                                wire:click="view({{ $message->id }})"
                                class="link"
                            >
                                {{ __('View') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500">
                            {{ __('No messages found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showModal && $selected)
        <div class="modal">
            <div class="modal-content space-y-4">
                <h3 class="text-lg font-semibold">
                    {{ __('Message details') }}
                </h3>

                <div>
                    <strong>{{ __('Name') }}:</strong>
                    {{ $selected->name }}
                </div>

                <div>
                    <strong>{{ __('Email') }}:</strong>
                    {{ $selected->email }}
                </div>

                <div>
                    <strong>{{ __('IP address') }}:</strong>
                    {{ $selected->ip_address ?? '—' }}
                </div>

                <div>
                    <strong>{{ __('Message') }}:</strong>
                    <p class="mt-2 text-gray-700">
                        {{ $selected->message }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <button wire:click="closeModal" class="btn-secondary">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
