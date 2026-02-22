<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    // تحديد اسم الجدول بشكل صريح (اختياري إذا كان اسم الجدول مطابق للنمط)
    protected $table = 'inventory_adjustments';

    // الأعمدة التي يمكن تعبئتها بشكل جماعي
    protected $fillable = [
        'inventory_count_id',
        'branch_id',
        'adjustment_date',
        'status',
        'created_by',
        'notes'
    ];

    // العلاقة مع تفاصيل أمر التسوية
    public function items()
    {
        return $this->hasMany(InventoryAdjustmentItem::class, 'inventory_adjustment_id');
    }
       public function creator()
    {
        return $this->belongsTo(Seller::class, 'created_by');
    }
           public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
