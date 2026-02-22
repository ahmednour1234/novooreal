<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'vat_tin',
        'cr_number',
        'company_name_ar',
        'company_name_en',
        'address_ar',
        'address_en',
        'environment',
        'simulation_csid',
        'production_csid',
    ];

    protected $casts = [
        'environment' => 'string',
    ];

    public function getActiveCsid(): ?string
    {
        if ($this->isProduction()) {
            return $this->getProductionCsid();
        }
        return $this->getSimulationCsid();
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    public function isSimulation(): bool
    {
        return $this->environment === 'simulation';
    }

    public function getSimulationCsid(): ?string
    {
        return $this->simulation_csid ? Crypt::decryptString($this->simulation_csid) : null;
    }

    public function getProductionCsid(): ?string
    {
        return $this->production_csid ? Crypt::decryptString($this->production_csid) : null;
    }

    public function setSimulationCsidAttribute($value): void
    {
        $this->attributes['simulation_csid'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setProductionCsidAttribute($value): void
    {
        $this->attributes['production_csid'] = $value ? Crypt::encryptString($value) : null;
    }

    public static function getSettings(): ?self
    {
        return static::first();
    }
}
