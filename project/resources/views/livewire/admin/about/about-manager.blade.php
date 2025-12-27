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
        $this->authorize('viewAny', \App\Models\User::class);

        $this->title       = (string) $settings->get('about.title', '');
        $this->subtitle    = (string) $settings->get('about.subtitle', '');
        $this->description = (string) $settings->get('about.description', '');

        $this->features = (array) $settings->get('about.features', []);
    }

    public function addFeature(): void
    {
        $this->features[] = [
            'title' => '',
            'description' => '',
            'icon' => '',
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
            'features.*.icon' => ['nullable', 'string', 'max:100'],
        ]);

        $settings->set('about.title', $this->title, 'string', 'about');
        $settings->set('about.subtitle', $this->subtitle, 'string', 'about');
        $settings->set('about.description', $this->description, 'text', 'about');
        $settings->set('about.features', $this->features, 'json', 'about');

        // Toast (project-wide standard)
        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" . __('About page updated successfully') . "'
                    }
                })
            );
        ");
    }
};
?>

<div class="space-y-6">
    @include('partials.settings-heading', [
        'title' => __('About us'),
        'description' => __('Manage About Us page content'),
    ])

    <div class="card space-y-4">
        <input
            type="text"
            wire:model.defer="title"
            placeholder="{{ __('Title') }}"
            class="input w-full"
        />

        <input
            type="text"
            wire:model.defer="subtitle"
            placeholder="{{ __('Subtitle (optional)') }}"
            class="input w-full"
        />

        <textarea
            wire:model.defer="description"
            placeholder="{{ __('Description') }}"
            class="textarea w-full"
            rows="4"
        ></textarea>
    </div>

    <div class="card space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold">{{ __('Features') }}</h3>
            <button wire:click="addFeature" class="btn-secondary">
                {{ __('Add feature') }}
            </button>
        </div>

        @foreach ($features as $index => $feature)
            <div class="border rounded p-4 space-y-3">
                <input
                    type="text"
                    wire:model.defer="features.{{ $index }}.title"
                    placeholder="{{ __('Feature title') }}"
                    class="input w-full"
                />

                <textarea
                    wire:model.defer="features.{{ $index }}.description"
                    placeholder="{{ __('Feature description') }}"
                    class="textarea w-full"
                    rows="2"
                ></textarea>

                <input
                    type="text"
                    wire:model.defer="features.{{ $index }}.icon"
                    placeholder="{{ __('Icon (optional, e.g. heroicon name)') }}"
                    class="input w-full"
                />

                <div class="flex justify-end">
                    <button
                        wire:click="removeFeature({{ $index }})"
                        class="text-red-600 text-sm"
                    >
                        {{ __('Remove') }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end">
        <button wire:click="save" class="btn-primary">
            {{ __('Save changes') }}
        </button>
    </div>
</div>
