<?php

namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductLog extends Model
{
    use HasFactory;
    /**
     * Get the user that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
   protected $fillable = [
        'seller_id','product_id','branch_id','quantity'
      
    ];
       public function products()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

}
