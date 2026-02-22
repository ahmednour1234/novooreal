<?php
// app/Models/ProductionOrderExecution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderExecution extends Model
{
    protected $fillable = [
        'production_order_id',
        'branch_id',
        'start_time',
        'end_time',
        'actual_hours',
        'total_reserved_quantity',
        'total_consumed_quantity',
        'waste_quantity',
        'produced_quantity',
        'unit_cost',
        'total_cost',
        'additional_costs',
        'additional_cost_total',
        'executed_by',
    ];

    // علاقة بعنصر التنفيذ
    public function items()
    {
        return $this->hasMany(ProductionOrderExecutionItem::class, 'execution_id');
    }

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
