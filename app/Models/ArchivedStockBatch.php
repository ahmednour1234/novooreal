<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivedStockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'branch_id',
        'product_code'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Add a new stock batch.
     */
    public static function addStockBatch($productId, $quantity, $unitCost)
    {
        self::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $unitCost
        ]);
    }

    /**
     * Reduce stock following FIFO logic.
     */
    public static function reduceStock($productId, $quantitySold)
    {
        $remainingQty = $quantitySold;

        $batches = self::where('product_id', $productId)
                        ->where('quantity', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            if ($batch->quantity >= $remainingQty) {
                $batch->decrement('quantity', $remainingQty);
                break;
            } else {
                $remainingQty -= $batch->quantity;
                $batch->update(['quantity' => 0]);
            }
        }
    }

    /**
     * Calculate the Cost of Goods Sold (COGS) using FIFO logic.
     */
    public static function calculateCOGS($productId, $quantitySold)
    {
        $remainingQty = $quantitySold;
        $totalCost = 0;

        $batches = self::where('product_id', $productId)
                        ->where('quantity', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            if ($batch->quantity >= $remainingQty) {
                $totalCost += $remainingQty * $batch->price;
                break;
            } else {
                $totalCost += $batch->quantity * $batch->price;
                $remainingQty -= $batch->quantity;
            }
        }

        return $totalCost;
    }
}
