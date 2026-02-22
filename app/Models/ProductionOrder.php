<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrder extends Model
{
    /**
     * اسم الجدول المرتبط
     *
     * @var string
     */
    protected $table = 'production_orders';

    /**
     * الحقول القابلة للملء جماعياً
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'bom_id',
        'routing_id',
        'branch_id',
        'issued_by',
        'quantity',
        'unit',
        'start_date',
        'end_date',
        'status',
        'produced_quantity',
        'cost_price'
    ];

    /**
     * التحويلات الخاصة بالحقول
     *
     * @var array
     */
    protected $casts = [
        'quantity'   => 'float',
        'produced_quantity'=>'float',
        'unit'       => 'boolean', // 0 = صغرى، 1 = كبرى
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * العلاقة إلى المنتج
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * العلاقة إلى قائمة المواد (BOM)
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    /**
     * العلاقة إلى مسار التشغيل (Routing)
     */
    public function routing(): BelongsTo
    {
        return $this->belongsTo(Routing::class, 'routing_id');
    }

    /**
     * العلاقة إلى الفرع
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * العلاقة إلى المستخدم الذي أصدر الأمر
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'issued_by');
    }

    /**
     * الصفة للحصول على تسمية الوحدة
     *
     * @return string
     */
    public function getUnitLabelAttribute(): string
    {
        return $this->unit
            ? 'كبرى'
            : 'صغرى';
    }
 public function batches()
{
    return $this->belongsToMany(StockBatch::class, 'production_order_batches')
                ->using(ProductionOrderBatch::class)
                ->withPivot(['reserved_quantity','actual_quantity','waste_quantity'])
                ->withTimestamps();
}
}
