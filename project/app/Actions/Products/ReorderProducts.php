<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;

class ReorderProducts
{
    use Auditable;

    /**
     * @param array<int, int> $orderedIds
     */
    public function execute(array $orderedIds): void
    {
        Gate::authorize('update', Product::class);

        foreach ($orderedIds as $index => $productId) {
            Product::where('id', $productId)
                ->update(['display_order' => $index + 1]);
        }

        $this->audit('product.reordered', null, [
            'order' => $orderedIds,
        ]);
    }
}
