<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'company_name',
        'contact_person',
        'notes',
        'account_id',
    ];

    /**
     * علاقة العميل بالحساب المحاسبي
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * علاقة العميل بالعقود
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
