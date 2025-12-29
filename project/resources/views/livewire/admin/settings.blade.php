<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;

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
    public ?string $current_logo = null;
    public ?string $current_favicon = null;

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
        $this->site_name = $settings->get('site_name', '');
        $this->site_description = $settings->get('site_description', '');

        $this->secondary_color = $settings->get('colors.secondary', '');
        $this->accent_color = $settings->get('colors.accent', '');
        $this->background_color = $settings->get('colors.background', '');

        $this->meta_title = $settings->get('seo.meta_title', '');
        $this->meta_description = $settings->get('seo.meta_description', '');
        $this->keywords = $settings->get('seo.keywords', '');

        // 👇 branding previews
        $this->current_logo = $settings->get('branding.logo');
        $this->current_favicon = $settings->get('branding.favicon');
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
        $this->js(
            "
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" .
                __('Settings have been saved successfully') .
                "'
                    }
                })
            );
        ",
        );
    }

    /**
     * Run: php artisan storage:link
     */
    public function storageLink(): void
    {
        $this->authorize('access-dashboard'); // أو super-admin فقط لو حاب

        try {
            Artisan::call('storage:link');

            $this->toastSuccess(__('Storage link created successfully'));
        } catch (\Throwable $e) {
            $this->toastError($e->getMessage());
        }
    }

    /**
     * Run: php artisan cache:clear
     */
    public function clearCache(): void
    {
        $this->authorize('access-dashboard');

        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            $this->toastSuccess(__('Cache cleared successfully'));
        } catch (\Throwable $e) {
            $this->toastError($e->getMessage());
        }
    }

    /**
     * Toast helpers (consistent with your project)
     */
    protected function toastSuccess(string $message): void
    {
        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: '{$message}' }
            }));
        ");
    }

    protected function toastError(string $message): void
    {
        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'error', message: '{$message}' }
            }));
        ");
    }
};

?>


<div class="space-y-6">

    {{-- Page heading --}}
    @include('partials.settings-heading', [
        'title' => __('Settings'),
        'description' => __('Manage global site settings'),
    ])

    {{-- Validation summary --}}
    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200 bg-red-50
                   dark:border-red-900 dark:bg-red-950/30
                   p-4 text-sm text-red-700 dark:text-red-400">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-2 flex-wrap
               border-b border-slate-200 dark:border-slate-800 pb-2">

        @foreach ([
        'general' => __('General'),
        'branding' => __('Branding'),
        'colors' => __('Colors'),
        'seo' => __('SEO'),
        'system' => __('System'),
    ] as $key => $label)
            <button wire:click="$set('tab','{{ $key }}')"
                class="px-4 py-2 rounded-xl text-sm font-medium transition
                {{ $tab === $key
                    ? 'bg-accent text-white shadow'
                    : 'bg-slate-200/60 text-slate-700
                                                                                                                                                                                       hover:bg-slate-300/60
                                                                                                                                                                                       dark:bg-slate-800 dark:text-slate-300
                                                                                                                                                                                       dark:hover:bg-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Content card --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
               bg-white dark:bg-slate-900/90
               p-6 space-y-6">

        {{-- ================= GENERAL ================= --}}
        @if ($tab === 'general')
            <div class="space-y-4">

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Site name') }}
                    </label>
                    <input type="text" wire:model.defer="site_name"
                        class="input w-full @error('site_name') ring-1 ring-red-500 @enderror"
                        placeholder="{{ __('Site name') }}" />
                    @error('site_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Site description') }}
                    </label>
                    <textarea wire:model.defer="site_description"
                        class="textarea w-full @error('site_description') ring-1 ring-red-500 @enderror" rows="3"
                        placeholder="{{ __('Site description') }}"></textarea>
                    @error('site_description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        @endif

        {{-- ================= BRANDING ================= --}}
        @if ($tab === 'branding')
            <div class="space-y-10">

                {{-- ========== Logo ========== --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-1 text-xs font-medium text-slate-500">
                        {{ __('Logo') }}

                        {{-- Tooltip --}}
                        <div class="relative inline-flex group">
                            <svg class="w-4 h-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-help"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25h1.5v5.25h-1.5v-5.25z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h.008v.008H12V7.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                            </svg>


                            <div
                                class="absolute z-20 bottom-full mb-2 left-1/2 -translate-x-1/2
                               w-64 rounded-xl
                               bg-white dark:bg-slate-900
                               border border-slate-200 dark:border-slate-700
                               shadow-lg
                               p-3 text-xs text-slate-600 dark:text-slate-300
                               opacity-0 invisible
                               group-hover:opacity-100 group-hover:visible
                               transition">

                                <p class="font-medium text-slate-700 dark:text-slate-200 mb-1">
                                    {{ __('Recommended logo specs') }}
                                </p>

                                <ul class="list-disc list-inside space-y-1">
                                    <li>{{ __('Width: 300–600px') }}</li>
                                    <li>{{ __('Height: up to 200px') }}</li>
                                    <li>{{ __('Format: PNG / SVG (transparent)') }}</li>
                                    <li>{{ __('Max size: 1MB') }}</li>
                                </ul>

                                <div
                                    class="absolute top-full left-1/2 -translate-x-1/2
                                   w-3 h-3 bg-white dark:bg-slate-900
                                   border-b border-r border-slate-200 dark:border-slate-700
                                   rotate-45">
                                </div>
                            </div>
                        </div>
                    </label>

                    <input type="file" wire:model="logo" class="text-sm text-slate-500" />

                    {{-- Preview --}}
                    @if ($logo || $current_logo)
                        <div
                            class="mt-4 inline-flex flex-col items-center gap-2
                           rounded-2xl border border-slate-200 dark:border-slate-800
                           bg-slate-50 dark:bg-slate-800/50
                           p-4">

                            <span class="text-[11px] uppercase tracking-wide text-slate-400">
                                {{ $logo ? __('New logo preview') : __('Current logo') }}
                            </span>

                            <div
                                class="relative w-48 h-24 flex items-center justify-center
                               rounded-xl overflow-hidden
                               ring-1 ring-slate-200 dark:ring-slate-700
                               bg-[linear-gradient(45deg,#e5e7eb_25%,transparent_25%,transparent_75%,#e5e7eb_75%,#e5e7eb),linear-gradient(45deg,#e5e7eb_25%,transparent_25%,transparent_75%,#e5e7eb_75%,#e5e7eb)]
                               bg-[length:16px_16px]
                               bg-[position:0_0,8px_8px]">

                                <img src="{{ $logo ? $logo->temporaryUrl() : asset('storage/' . $current_logo) }}"
                                    class="max-h-16 object-contain
                                   transition-transform duration-300
                                   hover:scale-105"
                                    alt="Logo preview">
                            </div>
                        </div>
                    @endif

                    @error('logo')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ========== Favicon ========== --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-1 text-xs font-medium text-slate-500">
                        {{ __('Favicon') }}

                        {{-- Tooltip --}}
                        <div class="relative inline-flex group">
                            <svg class="w-4 h-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-help"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25h1.5v5.25h-1.5v-5.25z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h.008v.008H12V7.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                            </svg>


                            <div
                                class="absolute z-20 bottom-full mb-2 left-1/2 -translate-x-1/2
                               w-64 rounded-xl
                               bg-white dark:bg-slate-900
                               border border-slate-200 dark:border-slate-700
                               shadow-lg
                               p-3 text-xs text-slate-600 dark:text-slate-300
                               opacity-0 invisible
                               group-hover:opacity-100 group-hover:visible
                               transition">

                                <p class="font-medium text-slate-700 dark:text-slate-200 mb-1">
                                    {{ __('Recommended favicon specs') }}
                                </p>

                                <ul class="list-disc list-inside space-y-1">
                                    <li>{{ __('Size: 32×32 or 64×64') }}</li>
                                    <li>{{ __('Square image') }}</li>
                                    <li>{{ __('Format: PNG / ICO / SVG') }}</li>
                                    <li>{{ __('Max size: 200KB') }}</li>
                                </ul>

                                <div
                                    class="absolute top-full left-1/2 -translate-x-1/2
                                   w-3 h-3 bg-white dark:bg-slate-900
                                   border-b border-r border-slate-200 dark:border-slate-700
                                   rotate-45">
                                </div>
                            </div>
                        </div>
                    </label>

                    <input type="file" wire:model="favicon" class="text-sm text-slate-500" />

                    {{-- Preview --}}
                    @if ($favicon || $current_favicon)
                        <div
                            class="mt-4 inline-flex flex-col items-center gap-2
                           rounded-2xl border border-slate-200 dark:border-slate-800
                           bg-slate-50 dark:bg-slate-800/50
                           p-4">

                            <span class="text-[11px] uppercase tracking-wide text-slate-400">
                                {{ $favicon ? __('New favicon preview') : __('Current favicon') }}
                            </span>

                            <div
                                class="relative w-16 h-16 flex items-center justify-center
                               rounded-xl overflow-hidden
                               ring-1 ring-slate-200 dark:ring-slate-700
                               bg-[linear-gradient(45deg,#e5e7eb_25%,transparent_25%,transparent_75%,#e5e7eb_75%,#e5e7eb),linear-gradient(45deg,#e5e7eb_25%,transparent_25%,transparent_75%,#e5e7eb_75%,#e5e7eb)]
                               bg-[length:16px_16px]
                               bg-[position:0_0,8px_8px]">

                                <img src="{{ $favicon ? $favicon->temporaryUrl() : asset('storage/' . $current_favicon) }}"
                                    class="max-h-10 max-w-10 object-contain
                                   transition-transform duration-300
                                   hover:scale-110"
                                    alt="Favicon preview">
                            </div>
                        </div>
                    @endif

                    @error('favicon')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        @endif




        {{-- ================= COLORS ================= --}}
        @if ($tab === 'colors')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Secondary color') }}
                    </label>
                    <input type="color" wire:model.defer="secondary_color" class="w-full h-10 rounded-lg border" />
                    @error('secondary_color')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Accent color') }}
                    </label>
                    <input type="color" wire:model.defer="accent_color" class="w-full h-10 rounded-lg border" />
                    @error('accent_color')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Background color') }}
                    </label>
                    <input type="color" wire:model.defer="background_color" class="w-full h-10 rounded-lg border" />
                    @error('background_color')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        @endif

        {{-- ================= SEO ================= --}}
        @if ($tab === 'seo')
            <div class="space-y-4">

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Meta title') }}
                    </label>
                    <input type="text" wire:model.defer="meta_title"
                        class="input w-full @error('meta_title') ring-1 ring-red-500 @enderror"
                        placeholder="{{ __('Meta title') }}" />
                    @error('meta_title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Meta description') }}
                    </label>
                    <textarea wire:model.defer="meta_description"
                        class="textarea w-full @error('meta_description') ring-1 ring-red-500 @enderror" rows="3"
                        placeholder="{{ __('Meta description') }}"></textarea>
                    @error('meta_description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('Keywords') }}
                    </label>
                    <textarea wire:model.defer="keywords" class="textarea w-full @error('keywords') ring-1 ring-red-500 @enderror"
                        rows="2" placeholder="keyword1, keyword2"></textarea>
                    @error('keywords')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        @endif

        {{-- ================= SYSTEM TOOLS ================= --}}
        @if ($tab === 'system')
            <div
                class="mt-6 rounded-2xl border border-slate-200 dark:border-slate-800
           bg-slate-50 dark:bg-slate-900/80 p-5 space-y-4">

                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ __('System tools') }}
                </p>

                <div class="flex flex-wrap gap-3">

                    {{-- Storage Link --}}
                    <button wire:click="storageLink"
                        class="px-4 py-2 rounded-xl text-sm font-medium
                   bg-sky-600 text-white
                   hover:bg-sky-700 transition">
                        {{ __('Create storage link') }}
                    </button>

                    {{-- Clear Cache --}}
                    <button wire:click="clearCache"
                        class="px-4 py-2 rounded-xl text-sm font-medium
                   bg-amber-600 text-white
                   hover:bg-amber-700 transition">
                        {{ __('Clear cache') }}
                    </button>

                </div>
            </div>
        @endif
    </div>

    {{-- Save --}}
    <div class="flex justify-end pt-4">
        <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
            class="px-6 py-2 rounded-xl text-sm
                   bg-accent text-white
                   hover:opacity-90 transition
                   disabled:opacity-50 disabled:cursor-not-allowed
                   flex items-center gap-2">

            <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25" />
                <path d="M12 2a10 10 0 0110 10" stroke-width="4" class="opacity-75" />
            </svg>

            <span wire:loading.remove wire:target="save">
                {{ __('Save settings') }}
            </span>

            <span wire:loading wire:target="save">
                {{ __('Saving...') }}
            </span>
        </button>
    </div>

</div>
