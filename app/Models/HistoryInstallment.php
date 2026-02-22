<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryInstallment extends Model
{
    use HasFactory;

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
        public function scheduledInstallment()
    {
        return $this->belongsTo(ScheduledInstallment::class, 'scheduled_installment_id');
    }
}
