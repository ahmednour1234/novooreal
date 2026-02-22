<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ScheduledInstallment extends Model
{
    protected $fillable = [
        'contract_id',
        'due_date',
        'amount',
        'status',
    ];

    public function contract()
    {
        return $this->belongsTo(InstallmentContract::class, 'contract_id');
    }

    public function payments()
    {
        return $this->hasMany(Installment::class, 'scheduled_installment_id');
    }

    public function getIsLateAttribute()
    {
        return $this->status !== 'paid' && now()->gt($this->due_date);
    }
}
