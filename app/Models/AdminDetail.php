<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDetail extends Model
{
    // تحديد اسم الجدول
    protected $table = 'admin_details';

    // تحديد الحقول القابلة للتعبئة (Mass Assignment)
    protected $fillable = [
        'admin_id',
        'full_name',
        'email',
        'phone',
        'department',
        'job_title',
        'hire_date',
        'qualifications',
        'contract_details',
    ];

    // إذا كنت تريد تعطيل timestamps، يمكنك ضبط التالي:
    // public $timestamps = false;

    // يمكنك أيضًا تحديد الأنواع (Castings) لبعض الحقول مثلاً:
    protected $casts = [
        'hire_date' => 'date',
    ];

    // علاقة مع نموذج المشرف Admin إن وُجد (اختياري)
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
