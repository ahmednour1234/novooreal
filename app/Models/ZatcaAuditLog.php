<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZatcaAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'egs_unit_id',
        'order_id',
        'zatca_document_id',
        'request_data',
        'response_data',
        'status',
        'error_message',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function egsUnit()
    {
        return $this->belongsTo(ZatcaEgsUnit::class, 'egs_unit_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function zatcaDocument()
    {
        return $this->belongsTo(ZatcaDocument::class, 'zatca_document_id');
    }

    public static function log(string $action, array $data = []): self
    {
        return static::create([
            'action' => $action,
            'egs_unit_id' => $data['egs_unit_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'zatca_document_id' => $data['zatca_document_id'] ?? null,
            'request_data' => $data['request'] ?? null,
            'response_data' => $data['response'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'error_message' => $data['error'] ?? null,
        ]);
    }
}
