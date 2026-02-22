<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'data'];

    protected $casts = [
        'data' => 'array', // Cast the `data` column as an array
    ];
}
