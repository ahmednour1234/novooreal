<?php

namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArchivedProductLog extends Model
{
    use HasFactory;
    /**
     * Get the user that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
 
       public function products()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

}
