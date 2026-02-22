<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductExpire extends Model
{
    use HasFactory;
        public $timestamps = true; // Ensures updated_at is included in your queries

    /**
     * Get the user that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
      public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
