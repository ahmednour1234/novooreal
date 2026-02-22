<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUnit extends Model
{
    use HasFactory;
    public $timestamps = true;
    
public function unit()
{
    return $this->belongsTo(Unit::class, 'unit_id');
}

}
