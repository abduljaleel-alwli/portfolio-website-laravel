<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;

new class extends Component {
    use AuthorizesRequests;

    // Fields
    public string $title = '';
    public string $subtitle = '';
    public string $description = '';

    // Repeatable features
    public array $features = [];

    public function mount(SettingsService $settings): void
    {
        // Only admin & super-admin (super-admin bypass via Gate::before)
        $this->authorize('access-dashboard');

        $this->title = (string) $settings->get('about.title', '');
        $this->subtitle = (string) $settings->get('about.subtitle', '');
        $this->description = (string) $settings->get('about.description', '');

        $this->features = (array) $settings->get('about.features', []);
    }

    public function addFeature(): void
    {
        $this->features[] = [
            'title' => '',
            'description' => '',
            'icon_type' => 'class',
            'icon_value' => '',
        ];
    }

    public function removeFeature(int $index): void
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function save(SettingsService $settings): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],

            'features' => ['array'],
            'features.*.title' => ['required', 'string', 'max:255'],
            'features.*.description' => ['required', 'string'],
            'features.*.icon_type' => ['required', 'in:class,svg'],
            'features.*.icon_value' => ['nullable', 'string'],
        ]);

        $settings->set('about.title', $this->title, 'string', 'about');
        $settings->set('about.subtitle', $this->subtitle, 'string', 'about');
        $settings->set('about.description', $this->description, 'text', 'about');
        $settings->set('about.features', $this->features, 'json', 'about');

        // Toast (project-wide standard)
        $this->js(
            "
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" .
                __('About page updated successfully') .
                "'
                    }
                })
            );
        ",
        );
    }
};
?>

<div class="space-y-6">
    @include('partials.settings-heading', [
        'title' => __('About us'),
        'description' => __('Manage About Us page content'),
    ])

    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white/90 dark:bg-slate-900/80
           backdrop-blur
           p-6 space-y-6">

        {{-- Title --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">
                {{ __('Title') }}
            </label>

            <input type="text" wire:model.defer="title"
                class="input w-full @error('title') ring-1 ring-red-500 @enderror"
                placeholder="{{ __('About page title') }}" />

            @error('title')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Subtitle --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">
                {{ __('Subtitle') }}
                <span class="text-slate-400">({{ __('Optional') }})</span>
            </label>

            <input type="text" wire:model.defer="subtitle"
                class="input w-full @error('subtitle') ring-1 ring-red-500 @enderror"
                placeholder="{{ __('Short subtitle under the title') }}" />

            @error('subtitle')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">
                {{ __('Description') }}
            </label>

            <textarea wire:model.defer="description" rows="5"
                class="textarea w-full @error('description') ring-1 ring-red-500 @enderror"
                placeholder="{{ __('Main description of the About page') }}"></textarea>

            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>



    <div class="card space-y-4">
        <div
            class="rounded-2xl border border-slate-200 dark:border-slate-800
            bg-white dark:bg-slate-900/90 overflow-hidden">

            <div
                class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white/90 dark:bg-slate-900/80
           backdrop-blur overflow-hidden">

                {{-- Header --}}
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-800
               flex items-center justify-between">

                    <div class="flex items-center gap-2">
                        {{-- Heroicon: sparkles --}}
                        <svg class="w-5 h-5 text-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846
                       a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813
                       a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846
                       a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813
                       a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>

                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            {{ __('Features') }}
                        </h3>
                    </div>

                    <button wire:click="addFeature"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm
                   bg-accent text-white hover:opacity-90 transition">
                        {{-- Heroicon: plus --}}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Add feature') }}
                    </button>
                </div>



                <div class="p-6 space-y-4">
                    @forelse ($features as $index => $feature)
                        @php
                            $featureHasError =
                                $errors->has("features.$index.title") ||
                                $errors->has("features.$index.description") ||
                                $errors->has("features.$index.icon_type") ||
                                $errors->has("features.$index.icon_value");
                        @endphp

                        <div
                            class="rounded-xl border p-5 space-y-4 transition
    {{ $featureHasError
        ? 'border-red-300 bg-red-50 dark:bg-red-950/30 ring-1 ring-red-500'
        : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50' }}">

                            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                                {{-- Heroicon: puzzle-piece --}}
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.75V5.25A2.25 2.25 0 0012 3
                   a2.25 2.25 0 00-2.25 2.25v1.5H8.25
                   A2.25 2.25 0 006 9v.75a2.25 2.25 0 002.25 2.25
                   h1.5v1.5A2.25 2.25 0 0012 15
                   a2.25 2.25 0 002.25-2.25v-1.5h1.5
                   A2.25 2.25 0 0018 9.75V9
                   a2.25 2.25 0 00-2.25-2.25h-1.5z" />
                                </svg>

                                {{ __('Feature') }} #{{ $index + 1 }}
                            </div>

                            {{-- Title --}}
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">
                                    {{ __('Feature title') }}
                                </label>
                                <input type="text" wire:model.defer="features.{{ $index }}.title"
                                    class="input w-full
        @error('features.' . $index . '.title') ring-1 ring-red-500 @enderror" />

                                @error('features.' . $index . '.title')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">
                                    {{ __('Feature description') }}
                                </label>
                                <textarea wire:model.defer="features.{{ $index }}.description"
                                    class="textarea w-full
        @error('features.' . $index . '.description') ring-1 ring-red-500 @enderror"
                                    rows="3"></textarea>

                                @error('features.' . $index . '.description')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror

                            </div>

                            {{-- Icon type --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">
                                        {{ __('Icon type') }}
                                    </label>
                                    <select wire:model.defer="features.{{ $index }}.icon_type"
                                        class="input w-full @error('features.' . $index . '.icon_type') ring-1 ring-red-500 @enderror">
                                        <option value="class">
                                            {{ __('Icon class (Font Awesome)') }}
                                        </option>
                                        <option value="svg">
                                            {{ __('SVG icon') }}
                                        </option>
                                    </select>
                                    @error('features.' . $index . '.icon_type')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">
                                        {{ __('Icon value') }}
                                    </label>

                                    @if ($features[$index]['icon_type'] === 'class')
                                        <input type="text"
                                            wire:model.defer="features.{{ $index }}.icon_value"
                                            class="input w-full
            @error('features.' . $index . '.icon_value') ring-1 ring-red-500 @enderror" />
                                    @else
                                        <textarea wire:model.defer="features.{{ $index }}.icon_value"
                                            class="textarea w-full font-mono text-xs
            @error('features.' . $index . '.icon_value') ring-1 ring-red-500 @enderror"
                                            rows="3"></textarea>
                                    @endif

                                    @error('features.' . $index . '.icon_value')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror

                                </div>
                            </div>

                            {{-- Remove --}}
                            <div class="flex justify-end">
                                <button wire:click="removeFeature({{ $index }})"
                                    class="inline-flex items-center gap-1 text-xs text-red-500 hover:underline">
                                    {{-- Heroicon: trash --}}
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21
                   c.342.052.682.107 1.022.166M5.772 5.79
                   L6.84 19.673a2.25 2.25 0 002.244 2.077h7.832
                   a2.25 2.25 0 002.244-2.077L18.228 5.79" />
                                    </svg>
                                    {{ __('Remove feature') }}
                                </button>
                            </div>

                        </div>
                    @empty
                        <div class="text-sm text-slate-500 text-center py-6">
                            {{ __('No features added yet') }}
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <div
            class="sticky bottom-0 z-10
           bg-white/80 dark:bg-slate-900/80
           backdrop-blur
           border-t border-slate-200 dark:border-slate-800
           px-6 py-4 flex justify-end">

            <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="px-6 py-2 rounded-xl text-sm
               bg-accent text-white
               hover:opacity-90 transition
               disabled:opacity-50 disabled:cursor-not-allowed
               flex items-center gap-2">

                <span wire:loading.remove wire:target="save">
                    {{ __('Save changes') }}
                </span>

                <span wire:loading wire:target="save">
                    {{ __('Saving...') }}
                </span>
            </button>
        </div>

    </div>
