<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCenter extends Model
{
    /**
     * اسم الجدول المرتبط بالنموذج.
     *
     * @var string
     */
    protected $table = 'work_centers';

    /**
     * الحقول القابلة للتعيين بشكل جماعي.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'capacity_per_day',
        'cost_per_hour',
        'branch_id'
    ];

    /**
     * التحويلات (Casts) للحقول.
     *
     * @var array
     */
    protected $casts = [
        'capacity_per_day' => 'float',
        'cost_per_hour'    => 'float',
    ];

    /**
     * الحصول على جميع خطوات التشغيل المرتبطة بهذا المركز.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function routingOperations(): HasMany
    {
        return $this->hasMany(RoutingOperation::class, 'work_center_id');
    }
     public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

}
