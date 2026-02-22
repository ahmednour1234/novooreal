<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductionOrderBatch extends Pivot
{
    protected $table = 'production_order_batches';

    protected $fillable = [
        'production_order_id',
        'stock_batch_id',
        'reserved_quantity',
        'actual_quantity',
        'waste_quantity',
    ];

    // عند تحديث الاستهلاك الفعلي، احسب الضياع:
    public function recordActual(float $qty)
    {
        $this->actual_quantity = $qty;
        $this->waste_quantity  = max(0, $this->reserved_quantity - $qty);
        $this->save();
    }
}
