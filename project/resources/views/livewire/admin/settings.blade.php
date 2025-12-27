<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

new class extends Component {
    use WithFileUploads;
    use AuthorizesRequests;

    public string $tab = 'general';

    // General
    public string $site_name = '';
    public string $site_description = '';

    // Branding
    public $logo;
    public $favicon;

    // Colors
    public string $secondary_color = '';
    public string $accent_color = '';
    public string $background_color = '';

    // SEO
    public string $meta_title = '';
    public string $meta_description = '';
    public string $keywords = '';

    public function mount(SettingsService $settings): void
    {
        $this->authorize('viewAny', \App\Models\User::class);

        $this->site_name = $settings->get('site_name', '');
        $this->site_description = $settings->get('site_description', '');

        $this->secondary_color = $settings->get('colors.secondary', '');
        $this->accent_color = $settings->get('colors.accent', '');
        $this->background_color = $settings->get('colors.background', '');

        $this->meta_title = $settings->get('seo.meta_title', '');
        $this->meta_description = $settings->get('seo.meta_description', '');
        $this->keywords = $settings->get('seo.keywords', '');
    }

    /**
     * Save all settings and show toast notification.
     */
    public function save(SettingsService $settings): void
    {
        // General settings
        $settings->set('site_name', $this->site_name, 'string', 'general');
        $settings->set('site_description', $this->site_description, 'text', 'general');

        // Branding settings
        if ($this->logo) {
            $path = $this->logo->store('branding', 'public');
            $settings->set('branding.logo', $path, 'image', 'branding');
        }

        if ($this->favicon) {
            $path = $this->favicon->store('branding', 'public');
            $settings->set('branding.favicon', $path, 'image', 'branding');
        }

        // Color settings
        $settings->set('colors.secondary', $this->secondary_color, 'color', 'colors');
        $settings->set('colors.accent', $this->accent_color, 'color', 'colors');
        $settings->set('colors.background', $this->background_color, 'color', 'colors');

        // SEO settings
        $settings->set('seo.meta_title', $this->meta_title, 'string', 'seo');
        $settings->set('seo.meta_description', $this->meta_description, 'text', 'seo');
        $settings->set('seo.keywords', $this->keywords, 'text', 'seo');

        // Toast notification (project-wide standard)
        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" . __('Settings have been saved successfully') . "'
                    }
                })
            );
        ");
    }
};

?>


<div class="space-y-6">
    @include('partials.settings-heading', [
        'title' => __('Settings'),
        'description' => __('Manage global site settings'),
    ])

    {{-- Tabs --}}
    <div class="flex gap-3 border-b pb-2">
        @foreach ([
            'general' => __('General'),
            'branding' => __('Branding'),
            'colors' => __('Colors'),
            'seo' => __('SEO')
        ] as $key => $label)
            <button wire:click="$set('tab','{{ $key }}')"
                class="px-4 py-2 rounded {{ $tab === $key ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- General --}}
    @if ($tab === 'general')
        <div class="space-y-4">
            <input
                type="text"
                wire:model.defer="site_name"
                placeholder="{{ __('Site name') }}"
                class="w-full input"
            />

            <textarea
                wire:model.defer="site_description"
                placeholder="{{ __('Site description') }}"
                class="w-full textarea"
            ></textarea>
        </div>
    @endif

    {{-- Branding --}}
    @if ($tab === 'branding')
        <div class="space-y-4">
            <input type="file" wire:model="logo" />
            <input type="file" wire:model="favicon" />
        </div>
    @endif

    {{-- Colors --}}
    @if ($tab === 'colors')
        <div class="grid grid-cols-3 gap-4">
            <input type="color" wire:model.defer="secondary_color" />
            <input type="color" wire:model.defer="accent_color" />
            <input type="color" wire:model.defer="background_color" />
        </div>
    @endif

    {{-- SEO --}}
    @if ($tab === 'seo')
        <div class="space-y-4">
            <input type="text" wire:model.defer="meta_title" placeholder="{{ __('Meta Title') }}" class="w-full input" />
            <textarea wire:model.defer="meta_description" placeholder="{{ __('Meta Description') }}" class="w-full textarea"></textarea>
            <textarea wire:model.defer="keywords" placeholder="{{ __('Keywords') }}" class="w-full textarea"></textarea>
        </div>
    @endif

    {{-- Save --}}
    <div class="pt-4">
        <button wire:click="save" class="px-6 py-2 bg-blue-600 text-white rounded">
            {{ __('Save settings') }}
        </button>
    </div>

</div>