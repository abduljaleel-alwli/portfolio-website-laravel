<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Product;
use App\Models\Category;
use App\Actions\Products\CreateProduct;
use App\Actions\Products\UpdateProduct;
use App\Actions\Products\DeleteProduct;
use App\Actions\Products\ToggleProductStatus;
use App\Actions\Products\ReorderProducts;

new class extends Component {
    use WithFileUploads;
    use AuthorizesRequests;

    /** Search */
    public string $search = '';

    /** Listing */
    public $products;

    /** Form state */
    public bool $showModal = false;
    public ?Product $editing = null;

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public bool $showViewModal = false;
    public ?Product $viewing = null;

    public string $title = '';
    public string $description = '';
    public ?int $category_id = null;
    public $main_image = null;
    public array $images = [];
    public bool $is_active = true;
    public string $meta_title = '';
    public string $meta_description = '';

    /** Data */
    public $categories = [];

    public function mount(): void
    {
        // Only admin & super-admin (super-admin bypass via Gate::before)
        $this->authorize('access-dashboard');

        $this->categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $this->products = Product::query()
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('display_order')
            ->get();
    }

    public function updatedSearch(): void
    {
        $this->loadProducts();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Product $product): void
    {
        $this->editing = $product;

        $this->title = $product->title;
        $this->description = (string) $product->description;
        $this->category_id = $product->category_id;
        $this->is_active = (bool) $product->is_active;
        $this->display_order = (int) $product->display_order;

        $this->meta_title = (string) $product->meta_title;
        $this->meta_description = (string) $product->meta_description;

        $this->showModal = true;
    }

    public function save(CreateProduct $create, UpdateProduct $update): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'main_image' => ['nullable', 'image', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editing) {
            $update->execute($this->editing, $data);

            $this->toast('success', __('Product updated successfully'));
        } else {
            $create->execute($data);

            $this->toast('success', __('Product created successfully'));
        }

        $this->closeModal();
        $this->loadProducts();
    }

    public function toggle(ToggleProductStatus $toggle, Product $product): void
    {
        $toggle->execute($product);

        $this->toast('success', $product->is_active ? __('Product activated successfully') : __('Product deactivated successfully'));

        $this->loadProducts();
    }

    // Delete with confirmation modal

    public function askDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->showDeleteModal = false;
    }

    public function confirmDelete(DeleteProduct $delete): void
    {
        if (!$this->deleteId) {
            return;
        }

        $product = Product::findOrFail($this->deleteId);
        $delete->execute($product);

        $this->toast('success', __('Product deleted successfully'));

        $this->cancelDelete();
        $this->loadProducts();
    }

    public function delete(DeleteProduct $delete, Product $product): void
    {
        $delete->execute($product);

        $this->toast('success', __('Product deleted successfully'));
        $this->loadProducts();
    }

    public function reorder(array $ids): void
    {
        app(ReorderProducts::class)->execute($ids);

        $this->toast('success', __('Products reordered successfully'));
        $this->loadProducts();
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = false;
    }

    public function view(Product $product): void
    {
        $this->viewing = $product;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->viewing = null;
        $this->showViewModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['editing', 'title', 'description', 'category_id', 'main_image', 'images', 'is_active', 'meta_title', 'meta_description']);

        $this->is_active = true;
        $this->display_order = 0;
    }

    private function toast(string $type, string $message): void
    {
        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: { type: '{$type}', message: '{$message}' }
                })
            );
        ");
    }
};
?>

<div class="space-y-8">

    {{-- Header + Actions --}}
    <div class="flex flex-col gap-4">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            {{-- Search --}}
            <div class="relative w-full sm:max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center text-slate-400">
                    {{-- Heroicon: magnifying-glass --}}
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35m1.6-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>

                <input wire:model.live="search" type="text" placeholder="{{ __('Search products...') }}"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-800
                       bg-white/80 dark:bg-slate-900/80
                       pl-10 pr-4 py-2.5 text-sm
                       text-slate-900 dark:text-white
                       placeholder-slate-400
                       backdrop-blur
                       focus:outline-none focus:ring-2 focus:ring-accent/40">
            </div>

            {{-- Add --}}
            <button wire:click="create"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                   bg-accent text-white text-sm font-medium
                   hover:opacity-90 transition">
                {{-- Heroicon: plus --}}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Add product') }}
            </button>
        </div>

        {{-- Counter --}}
        <div class="text-sm text-slate-500 dark:text-slate-400">
            {{ __('Total products') }}
            <span
                class="ml-1 inline-flex items-center rounded-md
                   bg-slate-200/50 dark:bg-slate-800/60
                   px-2 py-0.5 text-xs font-semibold
                   text-slate-900 dark:text-white">
                {{ $this->totalCount ?? count($products) }}
            </span>
        </div>

    </div>


    {{-- Products table --}}
    <div
        class="lg:col-span-1 rounded-2xl overflow-hidden
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900/90">

        <table class="w-full text-sm">
            <thead
                class="bg-slate-100 text-slate-700
                       dark:bg-gradient-to-r dark:from-slate-800 dark:to-slate-900
                       dark:text-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('#') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Image') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Title') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Category') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Order') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody x-data x-init="new Sortable($el, {
                handle: '[data-drag-handle]',
                animation: 150,
                onEnd() {
                    const ids = Array.from($el.children)
                        .map(el => el.getAttribute('data-id'))
            
                    $wire.reorder(ids)
                }
            })" class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($products as $product)
                    <tr data-id="{{ $product->id }}" wire:key="product-{{ $product->id }}"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <td class="px-2 text-slate-400 cursor-move" data-drag-handle>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 20 20">
                                <path
                                    d="M7 4h2v2H7V4zm4 0h2v2h-2V4zM7 8h2v2H7V8zm4 0h2v2h-2V8zM7 12h2v2H7v-2zm4 0h2v2h-2v-2z" />
                            </svg>
                        </td>


                        <td class="px-4 py-3">
                            <div
                                class="w-14 h-14 rounded-lg overflow-hidden
                bg-slate-100 dark:bg-slate-800
                ring-1 ring-slate-200 dark:ring-slate-700">
                                @if ($product->main_image)
                                    <img src="{{ asset('storage/' . $product->main_image) }}"
                                        alt="{{ $product->title }}"
                                        class="w-full h-full object-cover
                       hover:scale-110 transition-transform duration-300" />
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center
                        text-slate-400 text-xs">
                                        —
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium">
                            {{ $product->title }}
                        </td>

                        <td class="px-4 py-3 text-slate-500">
                            {{ $product->category->name ?? __('—') }}
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3">
                            <button wire:click="toggle({{ $product->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
               text-xs font-medium transition
        {{ $product->is_active
            ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 ring-1 ring-emerald-500/30'
            : 'bg-slate-500/10 text-slate-500 ring-1 ring-slate-500/30' }}">
                                {{-- Heroicon: check / x --}}
                                @if ($product->is_active)
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ __('Active') }}
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    {{ __('Inactive') }}
                                @endif
                            </button>
                        </td>

                        <td class="px-4 py-3">
                            {{ $product->display_order }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">

                                {{-- View --}}
                                <button wire:click="view({{ $product->id }})"
                                    class="p-1.5 rounded-lg text-accent
                   hover:bg-accent/10 transition"
                                    title="{{ __('View') }}">
                                    {{-- eye --}}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12
                       18 18.75 12 18.75 2.25 12 2.25 12z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>

                                {{-- Edit --}}
                                <button wire:click="edit({{ $product->id }})"
                                    class="p-1.5 rounded-lg text-sky-600 dark:text-sky-400
                   hover:bg-sky-500/10 transition"
                                    title="{{ __('Edit') }}">
                                    {{-- pencil --}}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688
                       a1.875 1.875 0 112.652 2.652L10.582 16.07
                       a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685
                       a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <button wire:click="askDelete({{ $product->id }})"
                                    class="p-1.5 rounded-lg text-red-500 dark:text-red-400
                   hover:bg-red-500/10 transition"
                                    title="{{ __('Delete') }}">
                                    {{-- trash --}}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21
                       c.342.052.682.107 1.022.166M5.772 5.79
                       L6.84 19.673a2.25 2.25 0 002.244 2.077h7.832
                       a2.25 2.25 0 002.244-2.077L18.228 5.79" />
                                    </svg>
                                </button>

                            </div>
                        </td>


                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">
                            {{ __('No products found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50">

            {{-- Overlay --}}
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

            {{-- Center Wrapper --}}
            <div class="relative h-full w-full flex items-start justify-center
                    px-4 py-6 sm:py-10">

                {{-- Modal Container --}}
                <div
                    class="w-full max-w-2xl
                       rounded-2xl
                       bg-white dark:bg-slate-900
                       border border-slate-200 dark:border-slate-800
                       shadow-2xl
                       max-h-[90vh]
                       flex flex-col overflow-hidden">

                    {{-- Header (Sticky) --}}
                    <div
                        class="sticky top-0 z-10
                           px-6 py-4
                           bg-white dark:bg-slate-900
                           border-b border-slate-200 dark:border-slate-800
                           flex items-center justify-between">

                        <h3 class="text-lg font-semibold tracking-tight">
                            {{ $editing ? __('Edit product') : __('Create product') }}
                        </h3>

                        <button wire:click="closeModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300
                               transition"
                            aria-label="{{ __('Close') }}">
                            ✕
                        </button>
                    </div>

                    {{-- Body (Scrollable) --}}
                    <div class="p-6 space-y-6 overflow-y-auto">

                        {{-- Validation summary (ERROR COLORS ONLY) --}}
                        @if ($errors->any())
                            <div
                                class="rounded-xl
                                   border border-red-200
                                   bg-red-50 dark:bg-red-950/30
                                   p-4 text-sm
                                   text-red-700 dark:text-red-400">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Form --}}
                        <div class="space-y-5">

                            {{-- Title --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Title') }}
                                </label>
                                <input type="text" wire:model.defer="title"
                                    class="w-full rounded-lg input
                                       @error('title') ring-1 ring-red-500 @enderror" />
                                @error('title')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Description') }}
                                </label>
                                <textarea wire:model.defer="description" rows="3"
                                    class="w-full rounded-lg textarea
                                       @error('description') ring-1 ring-red-500 @enderror"></textarea>
                                @error('description')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Category') }}
                                </label>
                                <select wire:model.defer="category_id"
                                    class="w-full rounded-lg select
                                       @error('category_id') ring-1 ring-red-500 @enderror">
                                    <option value="">{{ __('No category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Images --}}
                            <div class="space-y-6">

                                {{-- Main Image --}}
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-slate-500">
                                        {{ __('Main image') }}
                                    </label>

                                    <input type="file" wire:model="main_image" class="text-sm text-slate-500" />

                                    @if ($main_image)
                                        <img src="{{ $main_image->temporaryUrl() }}"
                                            class="mt-3 w-40 h-40 rounded-xl object-cover
                                               ring-1 ring-slate-200 dark:ring-slate-700" />
                                    @elseif ($editing && $editing->main_image)
                                        <img src="{{ asset('storage/' . $editing->main_image) }}"
                                            class="mt-3 w-40 h-40 rounded-xl object-cover
                                               ring-1 ring-slate-200 dark:ring-slate-700" />
                                    @endif

                                    @error('main_image')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Gallery Images --}}
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-slate-500">
                                        {{ __('Gallery images') }}
                                    </label>

                                    <input type="file" wire:model="images" multiple
                                        class="text-sm text-slate-500" />

                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-3">
                                        @if ($images)
                                            @foreach ($images as $img)
                                                <div
                                                    class="w-full h-24 rounded-xl overflow-hidden
                                                       ring-1 ring-slate-200 dark:ring-slate-700">
                                                    <img src="{{ $img->temporaryUrl() }}"
                                                        class="w-full h-full object-cover
                                                           hover:scale-110 transition-transform" />
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    @error('images.*')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                            {{-- SEO --}}
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                                <h4 class="text-sm font-semibold text-slate-500">
                                    {{ __('SEO settings') }}
                                </h4>

                                <input type="text" wire:model.defer="meta_title"
                                    placeholder="{{ __('Meta title') }}" class="w-full rounded-lg input" />

                                <textarea wire:model.defer="meta_description" rows="2" placeholder="{{ __('Meta description') }}"
                                    class="w-full rounded-lg textarea"></textarea>
                            </div>

                        </div>
                    </div>

                    {{-- Footer (Sticky) --}}
                    <div
                        class="sticky bottom-0
                           px-6 py-4
                           bg-white dark:bg-slate-900
                           border-t border-slate-200 dark:border-slate-800
                           flex items-center justify-between">

                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model.defer="is_active" />
                            {{ __('Active') }}
                        </label>

                        <div class="flex gap-2">
                            <button wire:click="closeModal"
                                class="px-4 py-2 rounded-lg text-sm
                                   bg-slate-200 dark:bg-slate-800
                                   hover:opacity-80 transition">
                                {{ __('Cancel') }}
                            </button>

                            <button wire:click="save"
                                class="px-4 py-2 rounded-lg text-sm
                                   bg-accent text-white
                                   hover:opacity-90 transition">
                                {{ __('Save') }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- View Modal --}}
    @if ($showViewModal && $viewing)
        <div class="fixed inset-0 z-50">
            {{-- Overlay --}}
            <div wire:click="closeViewModal" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

            {{-- Center wrapper (scroll-safe) --}}
            <div class="relative h-full w-full flex items-start justify-center py-10 px-4">
                {{-- Modal --}}
                <div
                    class="w-full max-w-3xl rounded-xl
                       bg-white dark:bg-slate-900
                       border border-slate-200 dark:border-slate-800
                       shadow-xl overflow-hidden">

                    {{-- Header ثابت --}}
                    <div
                        class="flex justify-between items-center px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="text-lg font-semibold">
                            {{ __('Product details') }}
                        </h3>

                        <button wire:click="closeViewModal" class="text-slate-400 hover:text-slate-600">
                            ✕
                        </button>
                    </div>

                    {{-- Body قابل للسكرول --}}
                    <div class="p-6 space-y-6 max-h-[85vh] overflow-y-auto">

                        {{-- Main Image --}}
                        @if ($viewing->main_image)
                            <div
                                class="w-full h-64 rounded-lg overflow-hidden ring-1 ring-slate-200 dark:ring-slate-800">
                                <img src="{{ asset('storage/' . $viewing->main_image) }}"
                                    alt="{{ $viewing->title }}" class="w-full h-full object-cover" />
                            </div>
                        @endif

                        {{-- Gallery (other images) --}}
                        @php
                            $gallery = $viewing->images ?? [];
                            // fallback لو كانت JSON string
                            if (is_string($gallery)) {
                                $decoded = json_decode($gallery, true);
                                $gallery = is_array($decoded) ? $decoded : [];
                            }
                            $gallery = is_array($gallery) ? $gallery : [];
                            // استبعاد main_image لو كانت مكررة
                            $gallery = array_values(
                                array_filter($gallery, function ($path) use ($viewing) {
                                    return $path && $path !== $viewing->main_image;
                                }),
                            );
                        @endphp

                        @if (count($gallery))
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-slate-500">
                                        {{ __('More images') }}
                                    </h4>
                                    <span class="text-xs text-slate-400">
                                        {{ count($gallery) }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach ($gallery as $img)
                                        <div
                                            class="group relative rounded-lg overflow-hidden
                                                bg-slate-100 dark:bg-slate-800
                                                ring-1 ring-slate-200 dark:ring-slate-700">
                                            <img src="{{ asset('storage/' . $img) }}" alt="image"
                                                class="w-full h-24 object-cover
                                                   group-hover:scale-110 transition-transform duration-300" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Info --}}
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-slate-400">{{ __('Title') }}</span>
                                <p class="font-medium">{{ $viewing->title }}</p>
                            </div>

                            <div>
                                <span class="text-slate-400">{{ __('Category') }}</span>
                                <p>{{ $viewing->category->name ?? '—' }}</p>
                            </div>

                            <div>
                                <span class="text-slate-400">{{ __('Status') }}</span>
                                <p>{{ $viewing->is_active ? __('Active') : __('Inactive') }}</p>
                            </div>

                            <div>
                                <span class="text-slate-400">{{ __('Order') }}</span>
                                <p>{{ $viewing->display_order }}</p>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if ($viewing->description)
                            <div>
                                <span class="text-slate-400 text-sm">{{ __('Description') }}</span>
                                <p class="mt-1 text-slate-600 dark:text-slate-300">
                                    {{ $viewing->description }}
                                </p>
                            </div>
                        @endif

                        {{-- SEO --}}
                        <div class="border-t border-slate-200 dark:border-slate-800 pt-4 space-y-2 text-sm">
                            <div>
                                <span class="text-slate-400">{{ __('Meta title') }}</span>
                                <p>{{ $viewing->meta_title ?: '—' }}</p>
                            </div>

                            <div>
                                <span class="text-slate-400">{{ __('Meta description') }}</span>
                                <p>{{ $viewing->meta_description ?: '—' }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- Delete Confirmation Modal --}}
    <x-modals.confirm :show="$showDeleteModal" type="danger" :title="__('Delete product')" :message="__('Are you sure you want to delete this product? This action cannot be undone.')" :confirmAction="'wire:click=confirmDelete'"
        :cancelAction="'wire:click=cancelDelete'" confirmLoadingTarget="confirmDelete" :confirmText="__('Yes, delete')" />
</div>
