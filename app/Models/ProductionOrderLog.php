<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderLog extends Model
{
    /**
     * اسم الجدول
     *
     * @var string
     */
    protected $table = 'production_order_logs';

    /**
     * الحقول القابلة للملء
     *
     * @var array
     */
    protected $fillable = [
        'production_order_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
    ];

    /**
     * علاقات التحويل (casts)
     *
     * @var array
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * سجل مرتبط بأمر الإنتاج
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * المستخدم الذي قام بالتغيير
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * مُنشئ السجل من أمر إنتاج
     *
     * @param  \App\Models\ProductionOrder  $order
     * @param  int                          $userId
     * @param  string                       $action
     * @param  array                        $changes
     * @return static
     */
    public static function createFromOrder(ProductionOrder $order, int $userId, string $action, array $changes = []): self
    {
        return self::create([
            'production_order_id' => $order->id,
            'user_id'             => $userId,
            'action'              => $action,
            'old_values'          => $order->getOriginal(),
            'new_values'          => $changes,
        ]);
    }
}
