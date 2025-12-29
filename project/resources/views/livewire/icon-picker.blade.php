<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Modelable;

new class extends Component {

    #[Modelable]
    public ?string $model = null;

    public bool $open = false;
    public string $search = '';

    protected array $icons = [
        'sparkles',
        'check-circle',
        'shield-check',
        'bolt',
        'heart',
        'star',
        'globe-alt',
        'users',
        'briefcase',
        'rocket-launch',
        'cube',
        'cog-6-tooth',
        'chart-bar',
        'light-bulb',
        'academic-cap',
    ];

    public function openPicker(): void
    {
        $this->search = '';
        $this->open = true;
    }

    public function select(string $icon): void
    {
        $this->model = $icon;
        $this->open = false;
    }

    public function getFilteredIconsProperty(): array
    {
        return collect($this->icons)
            ->filter(
                fn($icon) =>
                $this->search === '' || str_contains($icon, $this->search)
            )
            ->values()
            ->all();
    }
};
?>

<div class="flex items-center gap-3">

    {{-- Preview --}}
    <div class="w-10 h-10 rounded-lg flex items-center justify-center
               bg-slate-100 dark:bg-slate-800
               ring-1 ring-slate-200 dark:ring-slate-700">

        @if ($model)
            <flux:icon name="{{ $model }}" class="w-5 h-5 text-accent" />
        @else
            <span class="text-xs text-slate-400">—</span>
        @endif
    </div>

    {{-- Open --}}
    <button type="button" wire:click="openPicker" class="px-3 py-2 rounded-lg text-xs
               bg-slate-100 dark:bg-slate-800
               hover:opacity-80 transition">
        {{ __('Choose icon') }}
    </button>

    {{-- Clear --}}
    @if ($model)
        <button type="button" wire:click="$set('model', null)" class="text-xs text-red-500 hover:underline">
            {{ __('Clear') }}
        </button>
    @endif

    {{-- Modal --}}
    @if ($open)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('open', false)">
            </div>

            <div class="relative h-full w-full flex items-center justify-center p-4">
                <div class="w-full max-w-lg rounded-2xl
                               bg-white dark:bg-slate-900
                               border border-slate-200 dark:border-slate-800
                               shadow-2xl overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800
                                   flex items-center justify-between">
                        <h3 class="text-base font-semibold">
                            {{ __('Choose an icon') }}
                        </h3>

                        <button wire:click="$set('open', false)" class="text-slate-400 hover:text-slate-600">
                            ✕
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-6 pt-4">
                        <input type="text" wire:model.live="search" placeholder="{{ __('Search icons...') }}" class="w-full px-4 py-2 rounded-xl
                                       border border-slate-200 dark:border-slate-800
                                       bg-white dark:bg-slate-900 text-sm">
                    </div>

                    {{-- Icons --}}
                    <div class="p-6 grid grid-cols-5 sm:grid-cols-6 gap-4">
                        @forelse ($this->filteredIcons as $icon)
                            <button wire:click="select('{{ $icon }}')" class="group w-12 h-12 rounded-xl
                                               flex items-center justify-center
                                               bg-slate-100 dark:bg-slate-800
                                               hover:bg-accent/10 transition">

                                <flux:icon name="{{ $icon }}" class="w-6 h-6 text-slate-600 group-hover:text-accent" />
                            </button>
                        @empty
                            <div class="col-span-full text-center text-sm text-slate-500">
                                {{ __('No icons found') }}
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>