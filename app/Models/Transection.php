<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transection extends Model
{
    use HasFactory;

    // Specify the table name if it's different from the plural of the model name
    protected $table = 'transections'; // Adjust if necessary

    // Enable timestamps for this model
    public $timestamps = true;

    // Define fillable attributes to allow mass assignment
    protected $fillable = [
        'tran_type',
        'seller_id',
        'account_id',
        'amount',
        'description',
        'debit',
        'credit',
        'balance',
        'debit_account',
        'cost_id',
        'cost_id_to',
        'credit_account',
        'balance_account',
        'date',
        'customer_id',
        'supplier_id',
        'order_id',
        'cash',
        'branch_id',
        'asset_id'
    ];

    // Define the relationship with the Account model
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
     public function costcenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_id');
    }
       public function costcenter_to()
    {
        return $this->belongsTo(CostCenter::class, 'cost_id_to');
    }
     public function account_to()
    {
        return $this->belongsTo(Account::class, 'account_id_to');
    }
       public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
          public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
          public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
         public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
            public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
         public function taxe()
    {
        return $this->belongsTo(Taxe::class, 'tax_id');
    }
}
