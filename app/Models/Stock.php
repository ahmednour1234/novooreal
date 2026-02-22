<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
  protected $fillable = [
        'tran_type', // Add tran_type here
        'product_id',
        'quantity',
        'seller_id',
        'store_id',
        'stock',
        'branch_id',
        'main_stock',
        'price'
        // Add other fillable attributes as needed
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
// في نموذج Stock (Stock.php)
public function unit()
{
    return $this->belongsTo(Unit::class,'product_id');
}

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
    
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
        public $timestamps = true; // Ensures updated_at is included in your queries
   public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function getTotalStockValue(): float
{
    $total = 0;
    $tiers = json_decode($this->price, true);
    if (is_array($tiers)) {
        foreach ($tiers as $tier) {
            $total += ($tier['quantity'] ?? 0) * ($tier['price'] ?? 0);
        }
    }
    return $total;
}

}
