<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;

class DeleteProduct
{
    use Auditable;

    public function execute(Product $product): void
    {
        Gate::authorize('delete', $product);
        
        $product->delete();

        $this->audit('product.deleted', $product);
    }
}
