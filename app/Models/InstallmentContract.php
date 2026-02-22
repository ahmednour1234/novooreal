<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InstallmentContract extends Model
{
    protected $fillable = [
        'customer_id',
        'total_amount',
        'start_date',
        'duration_months',
        'interest_percent',
        'status',
        'order_id'
    ];

    public function scheduledInstallments()
    {
        return $this->hasMany(ScheduledInstallment::class, 'contract_id');
    }
    public function guarantor()
{
    return $this->hasOne(Guarantor::class, 'contract_id');
}


    public function installments()
    {
        return $this->hasMany(Installment::class, 'contract_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
public function order() {
    return $this->belongsTo(Order::class, 'order_id');
}

}
