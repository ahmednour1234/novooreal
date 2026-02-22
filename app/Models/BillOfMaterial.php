<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BillOfMaterial
 *
 * Represents the BOM header for a manufacturable product.
 */
class BillOfMaterial extends Model
{
    protected $table = 'bills_of_materials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'version',
        'description',
    ];

    /**
     * Get the product that this BOM belongs to.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the components for this BOM.
     *
     * @return HasMany
     */
    public function components(): HasMany
    {
        return $this->hasMany(BOMComponent::class, 'bom_id');
    }
}