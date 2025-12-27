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

    /** Listing */
    public $products;

    /** Form state */
    public bool $showModal = false;
    public ?Product $editing = null;

    public string $title = '';
    public string $description = '';
    public ?int $category_id = null;
    public $main_image = null;
    public array $images = [];
    public bool $is_active = true;
    public int $display_order = 0;
    public string $meta_title = '';
    public string $meta_description = '';


    /** Data */
    public $categories = [];

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);

        $this->categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $this->products = Product::query()
            ->orderBy('display_order')
            ->latest()
            ->get();
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

    public function save(
        CreateProduct $create,
        UpdateProduct $update
    ): void {
        $data = $this->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'category_id'   => ['nullable', 'exists:categories,id'],
            'main_image'    => ['nullable', 'image', 'max:2048'],
            'images.*'      => ['nullable', 'image', 'max:2048'],
            'is_active'     => ['boolean'],
            'display_order' => ['integer'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
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

        $this->toast(
            'success',
            $product->is_active
                ? __('Product activated successfully')
                : __('Product deactivated successfully')
        );

        $this->loadProducts();
    }

    public function delete(DeleteProduct $delete, Product $product): void
    {
        $delete->execute($product);

        $this->toast('success', __('Product deleted successfully'));
        $this->loadProducts();
    }

    public function reorder(ReorderProducts $reorder, array $ids): void
    {
        $reorder->execute($ids);

        $this->toast('success', __('Products reordered successfully'));
        $this->loadProducts();
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editing',
            'title',
            'description',
            'category_id',
            'main_image',
            'images',
            'is_active',
            'display_order',
            'meta_title',
            'meta_description',

        ]);

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

<div class="space-y-6">
    @include('partials.settings-heading', [
        'title' => __('Products'),
        'description' => __('Manage products displayed on the website'),
    ])

    <div class="flex justify-end">
        <button wire:click="create" class="btn-primary">
            {{ __('Add product') }}
        </button>
    </div>

    {{-- List --}}
    <div class="card">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Order') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->title }}</td>
                        <td>{{ $product->category->name ?? '—' }}</td>
                        <td>
                            <button wire:click="toggle({{ $product->id }})" class="link">
                                {{ $product->is_active ? __('Active') : __('Inactive') }}
                            </button>
                        </td>
                        <td>{{ $product->display_order }}</td>
                        <td class="text-right space-x-2">
                            <button wire:click="edit({{ $product->id }})" class="link">
                                {{ __('Edit') }}
                            </button>
                            <button wire:click="delete({{ $product->id }})" class="link text-red-600">
                                {{ __('Delete') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="modal">
            <div class="modal-content space-y-4">
                <h3 class="text-lg font-semibold">
                    {{ $editing ? __('Edit product') : __('Create product') }}
                </h3>

                <input type="text" wire:model.defer="title" placeholder="{{ __('Title') }}" class="input w-full" />
                <textarea wire:model.defer="description" placeholder="{{ __('Description') }}" class="textarea w-full"></textarea>

                <select wire:model.defer="category_id" class="select w-full">
                    <option value="">{{ __('No category') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <input type="file" wire:model="main_image" />
                <input type="file" wire:model="images" multiple />

                <div class="border-t pt-4 space-y-3">
                    <h4 class="font-semibold text-sm">
                        {{ __('SEO settings') }}
                    </h4>

                    <input
                        type="text"
                        wire:model.defer="meta_title"
                        placeholder="{{ __('Meta title') }}"
                        class="input w-full"
                    />

                    <textarea
                        wire:model.defer="meta_description"
                        placeholder="{{ __('Meta description') }}"
                        class="textarea w-full"
                        rows="2"
                    ></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="is_active" />
                        <span>{{ __('Active') }}</span>
                    </label>

                    <input type="number" wire:model.defer="display_order" class="input w-32" />
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="closeModal" class="btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button wire:click="save" class="btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
