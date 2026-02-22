<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BOMComponent
 *
 * يمثل سطرًا بمكون واحد (مادة خام أو وسيط) ضمن وصفة تصنيع (BOM).
 */
class BOMComponent extends Model
{
    protected $table = 'bom_components';

    protected $fillable = [
        'bom_id',
        'component_product_id',
        'quantity',
        'unit',
    ];

    /**
     * رأس الـBOM الذي ينتمي إليه هذا المكون.
     */
    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    /**
     * الصنف (مادة خام أو وسيط) المستخدم كمكون.
     */
    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }


}
