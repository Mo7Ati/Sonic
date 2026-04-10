<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\Product;

class ProductObserver
{
    /**
     * Auto-assign the product to all branches of its store.
     */
    public function created(Product $product): void
    {
        $branchIds = Branch::where('store_id', $product->store_id)->pluck('id');

        if ($branchIds->isEmpty()) {
            return;
        }

        $product->branches()->attach(
            $branchIds->mapWithKeys(fn (int $id) => [
                $id => [
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ])->all()
        );
    }
}
