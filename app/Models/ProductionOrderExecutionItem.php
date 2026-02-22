<?php
// app/Models/ProductionOrderExecutionItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderExecutionItem extends Model
{
    protected $fillable = [
        'execution_id',
        'product_id',
        'reserved_quantity',
        'consumed_quantity',
        'unit_cost',
        'reserved_cost',
        'consumed_cost',
    ];

    public function execution()
    {
        return $this->belongsTo(ProductionOrderExecution::class, 'execution_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
