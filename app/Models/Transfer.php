<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    // اسم الجدول
    protected $table = 'transfers';

    // الحقول القابلة للتعبئة
    protected $fillable = [
        'transfer_number',
        'source_branch_id',
        'destination_branch_id',
        'account_id',
        'account_id_to',
        'total_amount',
        'created_by',
        'approved_by',
        'status',
        'notes'
    ];

    /**
     * العلاقة مع تفاصيل الأصناف المحولة
     */
    public function items()
    {
        return $this->hasMany(TransferItem::class, 'transfer_id');
    }

    /**
     * العلاقة مع الحساب المصدر
     */
    public function sourceAccount()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * العلاقة مع الحساب الوجهة
     */
    public function destinationAccount()
    {
        return $this->belongsTo(Account::class, 'account_id_to');
    }

    /**
     * العلاقة مع الفرع المصدر
     */
    public function sourceBranch()
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    /**
     * العلاقة مع الفرع الوجهة
     */
    public function destinationBranch()
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ الطلب
     */
    public function createdBy()
    {
        return $this->belongsTo(Seller::class, 'created_by');
    }

    /**
     * العلاقة مع المستخدم الذي وافق على الطلب
     */
    public function approvedBy()
    {
        return $this->belongsTo(Seller::class, 'approved_by');
    }
}
