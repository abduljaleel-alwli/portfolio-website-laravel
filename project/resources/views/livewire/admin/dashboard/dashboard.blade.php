<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Dashboard\DashboardMetrics;

new class extends Component {
    use AuthorizesRequests;

    public array $metrics = [];

    public function mount(DashboardMetrics $dashboard): void
    {
        $this->authorize('viewAny', \App\Models\User::class);

        $this->metrics = $dashboard->all();
    }
};
?>


<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">{{ __('Dashboard') }}</h1>
        <p class="text-sm text-gray-500">
            {{ __('Overview of system statistics') }}
        </p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <x-dashboard.card title="Visitors" :value="$metrics['visits']" />
        <x-dashboard.card title="Contacts" :value="$metrics['contacts']" />
        <x-dashboard.card title="Users" :value="$metrics['users']" />
        <x-dashboard.card title="Products" :value="$metrics['products']" />
        <x-dashboard.card title="WhatsApp Clicks" :value="$metrics['whatsapp_clicks']" />
        <x-dashboard.card title="Social Clicks" :value="$metrics['social_clicks']" />
        <x-dashboard.card title="Conversion Rate" :value="$metrics['conversion_rate'] . '%'" />
    </div>

    <div class="bg-white rounded-lg shadow p-4">
    <h2 class="font-semibold mb-3">{{ __('Top Pages') }}</h2>

    <ul class="space-y-2 text-sm">
        @foreach ($metrics['top_pages'] as $page)
            <li class="flex justify-between">
                <span class="truncate">{{ $page->page }}</span>
                <span class="font-semibold">{{ $page->visits }}</span>
            </li>
        @endforeach
    </ul>
</div>

<div class="bg-white rounded-lg shadow p-4">
    <h2 class="font-semibold mb-3">{{ __('Recent Activity') }}</h2>

    <ul class="space-y-2 text-sm">
        @foreach ($metrics['activities'] as $activity)
            <li class="flex justify-between text-gray-600">
                <span>
                    {{ $activity->event }}
                    <span class="text-xs text-gray-400">
                        ({{ $activity->page ?? '-' }})
                    </span>
                </span>
                <span class="text-xs">
                    {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                </span>
            </li>
        @endforeach
    </ul>
</div>
