<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

new class extends Component {
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('viewAny', \App\Models\User::class);
    }

    public function markAsRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        auth()->user()
            ->unreadNotifications
            ->markAsRead();
    }
};
?>

<div class="space-y-6">
    @include('partials.settings-heading', [
        'title' => __('Notifications'),
        'description' => __('System notifications and alerts'),
    ])

    <div class="flex justify-end">
        <button wire:click="markAllAsRead" class="btn-secondary">
            {{ __('Mark all as read') }}
        </button>
    </div>

    <div class="space-y-3">
        @forelse (auth()->user()->notifications as $notification)
            <div
                class="p-4 rounded border
                {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold">
                            {{ $notification->data['title'] ?? __('Notification') }}
                        </p>

                        <p class="text-sm text-gray-600">
                            {{ $notification->data['message'] ?? __('No details available') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>

                    @if (!$notification->read_at)
                        <button
                            wire:click="markAsRead('{{ $notification->id }}')"
                            class="text-sm text-blue-600"
                        >
                            {{ __('Mark as read') }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500">
                {{ __('No notifications found') }}
            </p>
        @endforelse
    </div>
</div>
