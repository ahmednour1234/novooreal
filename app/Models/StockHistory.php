<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;
    public $timestamps = true; // Ensures updated_at is included in your queries
  protected $fillable = [
        'seller_id','product_id','order_id'
      
    ];
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
