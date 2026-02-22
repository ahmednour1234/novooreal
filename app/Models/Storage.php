<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    use HasFactory;
    public function parent()
    {
        return $this->belongsTo(CostCenter::class, 'parent_id');
    }

    // علاقة مراكز التكلفة الفرعية
    public function children()
    {
        return $this->hasMany(CostCenter::class, 'parent_id');
    }

}
