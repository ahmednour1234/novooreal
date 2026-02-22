<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routing extends Model
{
    /**
     * اسم الجدول المرتبط
     *
     * @var string
     */
    protected $table = 'routings';

    /**
     * الحقول القابلة للملء الجماعي
     *
     * @var array
     */
    protected $fillable = [
        'bom_id',
        'name',
        'description',
        'effective_date',
    ];

    /**
     * التحويلات (Casts) للحقل effective_date
     *
     * @var array
     */
    protected $casts = [
        'effective_date' => 'date',
    ];

    /**
     * علاقة الـRouting برأس الـBOM
     *
     * @return BelongsTo
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    /**
     * علاقة الـRouting بعدة خطوات تشغيل
     *
     * @return HasMany
     */
    public function operations(): HasMany
    {
        return $this->hasMany(RoutingOperation::class, 'routing_id');
    }
}
