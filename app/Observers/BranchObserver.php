<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\Product;

class BranchObserver
{
    /**
     * Auto-assign all store products to the newly created branch.
     */
    public function created(Branch $branch): void
    {
        $productIds = Product::where('store_id', $branch->store_id)->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        $branch->products()->attach(
            $productIds->mapWithKeys(fn (int $id) => [
                $id => [
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ])->all()
        );
    }
}
