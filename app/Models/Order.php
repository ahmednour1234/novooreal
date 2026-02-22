<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'owner_id',
        'total_tax',
        'order_amount',
        'extra_discount',
        'coupon_discount_amount',
        'collected_cash',
        'type',
        'cash',
        'payment_id',
        'notification',
        'transaction_reference',
        'active',
        'supplier_id',
        'parent_id',
        'branch_id',
        'img',
        'qrcode',
    ];

    /**
     * Get all of the order details (order items).
     */
    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Get the customer associated with the order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    /**
     * Get the supplier associated with the order.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the seller (owner) associated with the order.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'owner_id');
    }

    /**
     * Get the branch associated with the order.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the account associated with the order.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'payment_id');
    }

    /**
     * Get the coupon used in the order.
     * This method relates the order's 'coupon_code' to the coupon's 'code' field.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    /**
     * Get the parent order (if this order is a return or linked to another order).
     */
    public function parent()
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }
}
