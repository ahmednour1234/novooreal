<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guarantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'name',
        'national_id',
        'phone',
        'address',
        'job',
        'monthly_income',
        'relation',
        'images',
    ];

    protected $casts = [
        'monthly_income' => 'decimal:2',
        'images'         => 'array', // Laravel will auto-decode JSON
    ];

    public function contract()
    {
        return $this->belongsTo(InstallmentContract::class, 'contract_id');
    }
}
