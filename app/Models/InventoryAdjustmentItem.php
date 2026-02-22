<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentItem extends Model
{
    protected $table = 'inventory_adjustment_items';

    protected $fillable = [
        'inventory_adjustment_id',
        'product_id',
        'adjustment_amount',
        'new_system_quantity',
        'reason'
    ];

    // العلاقة مع أمر التسوية الرئيسي
    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    // العلاقة مع المنتج
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
