<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $table = 'maintenance_logs';

    /**
     * الحقول التي يمكن تعبئتها بواسطة الـ Mass Assignment.
     *
     * @var array
     */
    protected $fillable = [
        'asset_id',
        'maintenance_date',
        'maintenance_type',
        'estimated_cost',
        'notes',
        'status',
        'branch_id',
        'added_by',
        'approved_by',
        'done_by'
    ];

    /**
     * علاقة الصيانة مع الأصل.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
     public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    } public function add()
    {
        return $this->belongsTo(Seller::class,'added_by');
    } public function approve()
    {
        return $this->belongsTo(Seller::class,'approved_by');
    }
    public function done()
    {
        return $this->belongsTo(Seller::class,'done_by');
    }
}
