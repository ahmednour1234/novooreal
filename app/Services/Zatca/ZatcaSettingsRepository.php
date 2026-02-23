<?php

namespace App\Services\Zatca;

use App\Models\CompanySetting;
use App\Models\ZatcaEgsUnit;

class ZatcaSettingsRepository
{
    /**
     * Get company settings
     */
    public function getCompanySettings(): ?CompanySetting
    {
        return CompanySetting::getSettings();
    }

    /**
     * Update or create company settings
     */
    public function saveCompanySettings(array $data): CompanySetting
    {
        $settings = CompanySetting::first();
        if (!$settings) {
            $settings = new CompanySetting();
        }

        $settings->fill($data);
        $settings->save();

        return $settings;
    }

    /**
     * Get all EGS units
     */
    public function getEgsUnits()
    {
        return ZatcaEgsUnit::with('branch')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get EGS unit by ID
     */
    public function getEgsUnit(int $id): ?ZatcaEgsUnit
    {
        return ZatcaEgsUnit::with('branch')->find($id);
    }

    /**
     * Create or update EGS unit
     */
    public function saveEgsUnit(array $data, ?int $id = null): ZatcaEgsUnit
    {
        if ($id) {
            $egsUnit = ZatcaEgsUnit::findOrFail($id);
        } else {
            $egsUnit = new ZatcaEgsUnit();
        }

        $egsUnit->fill($data);
        $egsUnit->save();

        return $egsUnit->load('branch');
    }

    /**
     * Delete EGS unit
     */
    public function deleteEgsUnit(int $id): bool
    {
        $egsUnit = ZatcaEgsUnit::findOrFail($id);
        
        if ($egsUnit->zatcaDocuments()->count() > 0) {
            throw new \RuntimeException('Cannot delete EGS unit with existing documents');
        }

        return $egsUnit->delete();
    }
}
