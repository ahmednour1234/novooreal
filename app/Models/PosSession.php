<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    protected $table = 'pos_sessions';

    protected $fillable = [
        'user_id',
        'branch_id',
        'start_time',
        'end_time',
        'total_cash',
        'total_card',
        'total_discount',
        'total_amount_returns',
        'total_returns',
        'total_orders',
        'status',
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // العلاقات

    public function admin()
    {
        return $this->belongsTo(Admin::class,'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'session_id');
    }

    // مدة الجلسة بالدقائق
    public function getDurationAttribute()
    {
        if (!$this->end_time) {
            return now()->diffInMinutes($this->start_time);
        }
        return $this->start_time->diffInMinutes($this->end_time);
    }
}
