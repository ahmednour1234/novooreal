<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the subunits associated with the unit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subUnits()
    {
        return $this->hasMany(SubUnit::class,'unit_id');
    }
}
