<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class ZatcaEgsUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'egs_id',
        'name',
        'type',
        'branch_id',
        'private_key_path',
        'public_key_path',
        'certificate_path',
        'csr_path',
        'compliance_csid',
        'production_csid',
        'status',
        'onboarded_at',
    ];

    protected $casts = [
        'onboarded_at' => 'datetime',
    ];

    public function zatcaDocuments()
    {
        return $this->hasMany(ZatcaDocument::class, 'egs_unit_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(ZatcaAuditLog::class, 'egs_unit_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function getKeyPath(): ?string
    {
        return $this->private_key_path;
    }

    public function getCertificatePath(): ?string
    {
        return $this->certificate_path;
    }

    public function isOnboarded(): bool
    {
        return $this->status === 'active' && !empty($this->onboarded_at);
    }

    public function getActiveCsid(): ?string
    {
        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            return null;
        }

        if ($companySettings->isProduction()) {
            return $this->getProductionCsid();
        }
        return $this->getComplianceCsid();
    }

    public function getComplianceCsid(): ?string
    {
        return $this->compliance_csid ? Crypt::decryptString($this->compliance_csid) : null;
    }

    public function getProductionCsid(): ?string
    {
        return $this->production_csid ? Crypt::decryptString($this->production_csid) : null;
    }

    public function setComplianceCsidAttribute($value): void
    {
        $this->attributes['compliance_csid'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setProductionCsidAttribute($value): void
    {
        $this->attributes['production_csid'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getPrivateKeyContent(): ?string
    {
        if (!$this->private_key_path) {
            return null;
        }

        $disk = config('zatca.storage_disk', 'local');
        return Storage::disk($disk)->get($this->private_key_path);
    }

    public function getPublicKeyContent(): ?string
    {
        if (!$this->public_key_path) {
            return null;
        }

        $disk = config('zatca.storage_disk', 'local');
        return Storage::disk($disk)->get($this->public_key_path);
    }

    public function getCertificateContent(): ?string
    {
        if (!$this->certificate_path) {
            return null;
        }

        $disk = config('zatca.storage_disk', 'local');
        return Storage::disk($disk)->get($this->certificate_path);
    }
}
