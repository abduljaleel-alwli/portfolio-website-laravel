<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Category;
use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\UpdateCategory;
use App\Actions\Categories\DeleteCategory;

new class extends Component {
    use AuthorizesRequests;

    public $categories;

    public bool $showModal = false;
    public ?Category $editing = null;

    public string $name = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $this->categories = Category::query()
            ->orderBy('name')
            ->get();
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

    public function save(
        CreateCategory $create,
        UpdateCategory $update
    ): void {
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

    public function delete(DeleteCategory $delete, Category $category): void
    {
        try {
            $delete->execute($category);
            $this->toast('success', __('Category deleted successfully'));
            $this->loadCategories();
        } catch (\Throwable $e) {
            $this->toast('error', $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['editing', 'name']);
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
        'title' => __('Categories'),
        'description' => __('Manage product categories'),
    ])

    <div class="flex justify-end">
        <button wire:click="create" class="btn-primary">
            {{ __('Add category') }}
        </button>
    </div>

    <div class="card">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td class="text-right space-x-2">
                            <button wire:click="edit({{ $category->id }})" class="link">
                                {{ __('Edit') }}
                            </button>
                            <button wire:click="delete({{ $category->id }})" class="link text-red-600">
                                {{ __('Delete') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal">
            <div class="modal-content space-y-4">
                <h3 class="text-lg font-semibold">
                    {{ $editing ? __('Edit category') : __('Create category') }}
                </h3>

                <input
                    type="text"
                    wire:model.defer="name"
                    placeholder="{{ __('Category name') }}"
                    class="input w-full"
                />

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
