<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\layout;

layout('layouts.public');

new class extends Component {
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';

    public function products()
    {
        return Product::query()
            ->active()
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%");
            })
            ->public() // يعتمد على display_order ثم الأحدث
            ->paginate(12);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function imageUrl(?string $path): string
    {
        if (!$path) return 'https://placehold.co/600x400?text=No+Image';

        // لو كنت تخزن path داخل storage/app/public
        return str_starts_with($path, 'http')
            ? $path
            : Storage::url($path);
    }
};
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">المنتجات</h1>
                <p class="text-sm text-gray-600">استعرض منتجاتنا المتاحة</p>
            </div>

            <div class="w-full md:w-80">
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="ابحث باسم المنتج..."
                    class="w-full border rounded-lg px-3 py-2 bg-white"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($this->products() as $product)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
                    <div class="aspect-[16/10] bg-gray-100 overflow-hidden">
                        <img
                            src="{{ $this->imageUrl($product->main_image) }}"
                            alt="{{ $product->title }}"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                    </div>

                    <div class="p-4 space-y-2">
                        <h3 class="font-semibold text-gray-900 line-clamp-1">
                            {{ $product->title }}
                        </h3>

                        @if($product->description)
                            <p class="text-sm text-gray-600 line-clamp-3">
                                {{ strip_tags($product->description) }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400">لا يوجد وصف</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white border rounded-lg p-8 text-center text-gray-500">
                    لا توجد منتجات مطابقة للبحث.
                </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $this->products()->links() }}
        </div>
    </div>
</div>