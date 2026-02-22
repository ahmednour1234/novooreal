<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    // اسم الجدول
    protected $table = 'transfer_items';

    // الحقول القابلة للتعبئة
    protected $fillable = [
        'transfer_id',
        'product_id',
        'quantity',
        'unit',
        'cost',
        'total_cost'
    ];

    /**
     * العلاقة مع عملية التحويل (transfer)
     */
    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transfer_id');
    }

    /**
     * العلاقة مع المنتج
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
