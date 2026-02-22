<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentOrder extends Model
{
    use HasFactory;
  protected $fillable = [
      'id',
        'user_id',
         'owner_id',
        'total_tax', // Add total_tax to fillable properties
        'order_amount',
        'cash',
        'extra_discount',
        'coupon_discount_amount',
        'collected_cash',
        'transaction_reference',
        'type',
        'payment_id',
        'active',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class);
    }
    
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'owner_id');
    }
    
    public function account()
    {
        return $this->belongsTo(Account::class, 'payment_id');
    }
}
