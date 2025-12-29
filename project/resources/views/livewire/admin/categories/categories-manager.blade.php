<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Category;
use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\UpdateCategory;
use App\Actions\Categories\DeleteCategory;

new class extends Component {
    use AuthorizesRequests;

    public string $search = '';

    public $categories;

    public bool $showModal = false;
    public ?Category $editing = null;

    public string $name = '';

    public bool $showConfirmDelete = false;
    public ?int $deleteId = null;

    public bool $showCannotDeleteModal = false;
    public int $linkedProductsCount = 0;

    public function mount(): void
    {
        $this->authorize('access-dashboard');
        $this->loadCategories();
    }

    public function updatedSearch(): void
    {
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $this->categories = Category::query()->withCount('products')->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))->orderBy('name')->get();
    }

    public function getTotalCountProperty(): int
    {
        return Category::count();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->editing = $category;
        $this->name = $category->name;
        $this->showModal = true;
    }

    public function save(CreateCategory $create, UpdateCategory $update): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($this->editing) {
            $update->execute($this->editing, $data);
            $this->toast('success', __('Category updated successfully'));
        } else {
            $create->execute($data);
            $this->toast('success', __('Category created successfully'));
        }

        $this->closeModal();
        $this->loadCategories();
    }

    public function askDelete(int $id): void
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            $this->linkedProductsCount = $category->products_count;
            $this->showCannotDeleteModal = true;
            return;
        }

        $this->deleteId = $id;
        $this->showConfirmDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->showConfirmDelete = false;
    }

    public function confirmDelete(DeleteCategory $delete): void
    {
        if (!$this->deleteId) {
            return;
        }

        $delete->execute(Category::findOrFail($this->deleteId));

        $this->toast('success', __('Category deleted successfully'));

        $this->cancelDelete();
        $this->loadCategories();
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function closeCannotDeleteModal(): void
    {
        $this->showCannotDeleteModal = false;
        $this->linkedProductsCount = 0;
    }

    private function resetForm(): void
    {
        $this->reset(['editing', 'name']);
    }

    private function toast(string $type, string $message): void
    {
        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: '{$type}', message: '{$message}' }
            }));
        ");
    }
};
?>


<div class="space-y-6">

    {{-- Header + Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        {{-- Search --}}
        <div class="relative w-full sm:max-w-sm">
            <span
    class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center text-slate-400">

                {{-- Magnifying glass --}}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35m1.6-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>

            <input wire:model.live="search" type="text" placeholder="{{ __('Search categories...') }}"
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
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ __('Add category') }}
        </button>

    </div>

    {{-- Counter --}}
    <div class="text-sm text-slate-500 dark:text-slate-400">
        {{ __('Total categories') }}
        <span
            class="ml-1 inline-flex items-center rounded-md bg-slate-200/50 dark:bg-slate-800/60
               px-2 py-0.5 text-xs font-semibold text-slate-900 dark:text-white">
            {{ $this->totalCount }}
        </span>
    </div>


    {{-- Table --}}
        <div
            class="lg:col-span-1 rounded-2xl overflow-hidden
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900/90">
        <table class="w-full text-sm">
            <thead class="bg-slate-100/70 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200">

                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Products') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($categories as $category)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                            {{ $category->name }}
                        </td>

                        {{-- Badge --}}
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium
        {{ $category->products_count > 0
            ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400'
            : 'bg-slate-200/40 text-slate-500 dark:bg-slate-700/40 dark:text-slate-400' }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 7.5l-8-4.5-8 4.5m16 0v9l-8 4.5-8-4.5v-9" />
                                </svg>

                                {{ $category->products_count }}
                            </span>
                        </td>


                        {{-- Actions --}}
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">

                                {{-- Edit --}}
                                <button wire:click="edit({{ $category->id }})"
                                    class="p-1.5 rounded-lg text-sky-600 dark:text-sky-400
                   hover:bg-sky-500/10 transition"
                                    title="{{ __('Edit') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07
                       a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685
                       a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <button wire:click="askDelete({{ $category->id }})"
                                    class="p-1.5 rounded-lg text-red-500 dark:text-red-400
                   hover:bg-red-500/10 transition"
                                    title="{{ __('Delete') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21
                       c.342.052.682.107 1.022.166m-1.022-.165L18.16
                       19.673a2.25 2.25 0 01-2.244 2.077H8.084
                       a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456
                       0a48.108 48.108 0 00-3.478-.397m-12
                       .562c.34-.059.68-.114 1.022-.165m0
                       0a48.11 48.11 0 013.478-.397m7.5
                       0v-.916c0-1.18-.91-2.164-2.09-2.201
                       a51.964 51.964 0 00-3.32 0
                       c-1.18.037-2.09 1.022-2.09
                       2.201v.916m7.5 0" />
                                    </svg>
                                </button>

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            {{ __('No categories found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modals --}}
    @if ($showModal)
        @include('livewire.admin.categories.form-modal')
    @endif

    <x-modals.confirm :show="$showConfirmDelete" type="danger" :title="__('Delete category')" :message="__('Are you sure you want to delete this category? This action cannot be undone.')" :confirmAction="'wire:click=confirmDelete'"
        :cancelAction="'wire:click=cancelDelete'" confirmLoadingTarget="confirmDelete" :confirmText="__('Delete')" />

    <x-modals.confirm :show="$showCannotDeleteModal" type="warning" :title="__('Cannot delete category')" :message="__(
        'This category is linked to :count product(s). You must remove the category from those products before deleting it.',
        ['count' => $linkedProductsCount],
    )" :confirmText="__('OK')"
        :confirmAction="'wire:click=closeCannotDeleteModal'" :cancelAction="'wire:click=closeCannotDeleteModal'" />

</div>
