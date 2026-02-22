<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Services\ZATCAService;

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
        'uuid',
        'invoice_number',
        'invoice_counter',
        'previous_invoice_hash',
        'zatca_submitted',
        'zatca_submitted_at',
        'zatca_qr_code',
        'currency_code',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->uuid)) {
                $order->uuid = ZATCAService::generateUUID();
            }
            if (empty($order->currency_code)) {
                $order->currency_code = 'SAR';
            }
        });
    }

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

    /**
     * Generate invoice number based on counter
     *
     * @return string
     */
    public function generateInvoiceNumber(): string
    {
        if (empty($this->invoice_counter)) {
            $this->invoice_counter = ZATCAService::getNextInvoiceCounter($this->company_id);
        }
        $this->invoice_number = ZATCAService::generateInvoiceNumber($this->invoice_counter);
        return $this->invoice_number;
    }

    /**
     * Generate previous invoice hash for chain validation
     *
     * @return string|null
     */
    public function generatePreviousInvoiceHash(): ?string
    {
        $previousHash = ZATCAService::getPreviousInvoiceHash($this->company_id);
        $this->previous_invoice_hash = $previousHash;
        return $previousHash;
    }

    /**
     * Calculate and set invoice hash for this order
     *
     * @return string
     */
    public function calculateInvoiceHash(): string
    {
        $invoiceData = [
            'uuid' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'invoice_counter' => $this->invoice_counter,
            'order_amount' => $this->order_amount,
            'total_tax' => $this->total_tax,
            'created_at' => $this->created_at?->toIso8601String(),
        ];

        return ZATCAService::calculateHash($invoiceData);
    }
}
