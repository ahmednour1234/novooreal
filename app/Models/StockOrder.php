<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOrder extends Model
{
    use HasFactory;
        public $timestamps = true; // Ensures updated_at is included in your queries


    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
    
    public function details()
    {
        return $this->hasMany(StockHistory::class, 'order_id');
    }
}
