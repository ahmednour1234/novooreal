<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationDetail extends Model
{
    use HasFactory;
  protected $fillable = [
        'order_id', // Add order_id to fillable properties
        'product_details',
        'product_id',
        'quantity',
        'tax_amount',
        'unit',
        'discount_on_product',
        'extra_discount_on_product',
        'active',
        'price'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the user that owns the OrderDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
{
    return $this->belongsTo(Order::class);
}
}
