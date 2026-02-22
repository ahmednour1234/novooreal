<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZatcaDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'egs_unit_id',
        'invoice_uuid',
        'invoice_number',
        'invoice_type',
        'xml_content',
        'signed_xml',
        'qr_code_tlv',
        'submission_status',
        'zatca_uuid',
        'zatca_long_id',
        'submitted_at',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function egsUnit()
    {
        return $this->belongsTo(ZatcaEgsUnit::class, 'egs_unit_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(ZatcaAuditLog::class, 'zatca_document_id');
    }

    public function scopePending($query)
    {
        return $query->where('submission_status', 'pending');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('submission_status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('submission_status', 'failed');
    }

    public function isPending(): bool
    {
        return $this->submission_status === 'pending';
    }

    public function isSuccessful(): bool
    {
        return $this->submission_status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->submission_status === 'failed';
    }
}
