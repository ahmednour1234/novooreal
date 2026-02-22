<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingOperation extends Model
{
    protected $table = 'routing_operations';

    protected $fillable = [
        'routing_id',
        'work_center_id',
        'sequence',
        'setup_time',
        'run_time',
    ];

    /**
     * المسار الذي تنتمي إليه هذه الخطوة.
     */
    public function routing(): BelongsTo
    {
        return $this->belongsTo(Routing::class, 'routing_id');
    }

    /**
     * مركز العمل الذي تُنفَّذ فيه هذه الخطوة.
     */
    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }
}
