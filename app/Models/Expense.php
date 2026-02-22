<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    // Specify the table name (if not following Laravel's naming conventions)
    protected $table = 'expenses';

    // Define the fields that are mass assignable
    protected $fillable = [
        'account_id',
        'cost_center_id',  // you can also name this field as cost_center_id or cost_id based on your design
        'description',
        'amount',
        'date',
        'attachment',
        'seller_id',
        'branch_id'
    ];

    // Optionally, you can define casts for certain fields (for example, amount as a float)
    protected $casts = [
        'amount' => 'float',
        'date'   => 'date',
    ];
       public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
     public function costcenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
      public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
      public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
