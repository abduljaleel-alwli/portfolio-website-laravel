<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Support\Auditable;

class UpdateProduct
{
    use Auditable;

    /**
     * Update an existing product.
     */
    public function execute(Product $product, array $data): Product
    {
        Gate::authorize('update', $product);

        // Update main image if provided
        if (!empty($data['main_image'])) {
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }

            $product->main_image = $data['main_image']->store('products', 'public');
        }

        // Update gallery images if provided
        if (!empty($data['images']) && is_array($data['images'])) {
            if (!empty($product->images)) {
                foreach ($product->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $images = [];
            foreach ($data['images'] as $image) {
                $images[] = $image->store('products/gallery', 'public');
            }

            $product->images = $images;
        }

        // Update basic fields
        $product->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'is_active' => $data['is_active'] ?? $product->is_active,
            'display_order' => $data['display_order'] ?? $product->display_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        $this->audit('product.updated', $product, [
            'fields' => array_keys($data),
        ]);

        return $product;
    }
}
