<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;

new class extends Component {
    use AuthorizesRequests;

    public string $title = '';
    public string $description = '';
    public string $email_to = '';
    public string $phone = '';
    public string $map_url = '';

    public array $social_links = [];

    public function mount(SettingsService $settings): void
    {
        $this->authorize('viewAny', \App\Models\User::class);

        $this->title        = (string) $settings->get('contact.title', '');
        $this->description  = (string) $settings->get('contact.description', '');
        $this->email_to     = (string) $settings->get('contact.email_to', '');
        $this->phone        = (string) $settings->get('contact.phone', '');
        $this->map_url      = (string) $settings->get('contact.map_url', '');

        $this->social_links = (array) $settings->get('contact.social_links', []);
    }

    public function addSocial(): void
    {
        $this->social_links[] = [
            'platform' => '',
            'url' => '',
        ];
    }

    public function removeSocial(int $index): void
    {
        unset($this->social_links[$index]);
        $this->social_links = array_values($this->social_links);
    }

    public function save(SettingsService $settings): void
    {
        try {
            $this->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'email_to' => ['required', 'email'],
                'phone' => ['nullable', 'string', 'max:50'],
                'map_url' => ['nullable', 'string', 'max:500'],
                'social_links' => ['array'],
                'social_links.*.platform' => ['required', 'string', 'max:50'],
                'social_links.*.url' => ['required', 'string', 'max:500'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->failedValidation();
            throw $e;
        }

        // Save only if validation passed
        $settings->set('contact.title', $this->title, 'string', 'contact');
        $settings->set('contact.description', $this->description, 'text', 'contact');
        $settings->set('contact.email_to', $this->email_to, 'string', 'contact');
        $settings->set('contact.phone', $this->phone, 'string', 'contact');
        $settings->set('contact.map_url', $this->map_url, 'string', 'contact');
        $settings->set('contact.social_links', $this->social_links, 'json', 'contact');

        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" . __('Contact page updated successfully') . "'
                    }
                })
            );
        ");
    }

    protected function failedValidation(): void
    {
        // Force Livewire to re-sync state after validation failure
        $this->map_url = (string) $this->map_url;
        $this->social_links = array_values($this->social_links);
    }

};
?>

<div class="space-y-6">
    @include('partials.settings-heading', [
        'title' => __('Contact page'),
        'description' => __('Manage contact page information'),
    ])

    <div class="card space-y-4">
        <input wire:model.defer="title" class="input w-full" placeholder="{{ __('Title') }}" />
        <textarea wire:model.defer="description" class="textarea w-full" rows="3"
            placeholder="{{ __('Description') }}"></textarea>

        <input wire:model.defer="email_to" class="input w-full" placeholder="{{ __('Email recipient') }}" />
        <input wire:model.defer="phone" class="input w-full" placeholder="{{ __('Phone number') }}" />
        <input wire:model="map_url" class="input w-full" placeholder="{{ __('Google Maps URL') }}" />
    </div>

    <div class="card space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold">{{ __('Social links') }}</h3>
            <button wire:click="addSocial" class="btn-secondary">
                {{ __('Add link') }}
            </button>
        </div>

        @foreach ($social_links as $index => $social)
            <div class="grid grid-cols-2 gap-3">
                <input wire:model="social_links.{{ $index }}.platform"
                    class="input" placeholder="{{ __('Platform') }}" />
                <input wire:model="social_links.{{ $index }}.url"
                    class="input" placeholder="{{ __('URL') }}" />
                <button wire:click="removeSocial({{ $index }})"
                    class="text-red-600 text-sm col-span-2 text-right">
                    {{ __('Remove') }}
                </button>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end">
        <button wire:click="save" class="btn-primary">
            {{ __('Save changes') }}
        </button>
    </div>
</div>
