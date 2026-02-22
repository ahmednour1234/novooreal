<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Models\ZatcaEgsUnit;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;

class ZatcaSettingsController extends Controller
{
    public function index(): View|Factory|Application|RedirectResponse
    {
        $adminId = Auth::guard('admin')->id();
        $admin = DB::table('admins')->where('id', $adminId)->first();

        if (!$admin) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }

        $roleId = $admin->role_id;
        $role = DB::table('roles')->where('id', $roleId)->first();

        if (!$role) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }

        $decodedData = json_decode($role->data, true);
        if (is_string($decodedData)) {
            $decodedData = json_decode($decodedData, true);
        }

        if (!is_array($decodedData)) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }

        if (!in_array("settings.index", $decodedData)) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }

        $companySettings = CompanySetting::first();
        $egsUnits = ZatcaEgsUnit::with('branch')->orderBy('created_at', 'desc')->get();
        $branches = Branch::all();

        return view('admin-views.zatca-settings.index', compact('companySettings', 'egsUnits', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vat_tin' => 'required|string|max:15',
            'cr_number' => 'nullable|string|max:50',
            'company_name_ar' => 'required|string|max:255',
            'company_name_en' => 'required|string|max:255',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'environment' => 'required|in:simulation,production',
        ], [
            'vat_tin.required' => translate('VAT/TIN number is required'),
            'company_name_ar.required' => translate('Company name (Arabic) is required'),
            'company_name_en.required' => translate('Company name (English) is required'),
        ]);

        $companySettings = CompanySetting::first();
        if (!$companySettings) {
            $companySettings = new CompanySetting();
        }

        $companySettings->vat_tin = $request->vat_tin;
        $companySettings->cr_number = $request->cr_number;
        $companySettings->company_name_ar = $request->company_name_ar;
        $companySettings->company_name_en = $request->company_name_en;
        $companySettings->address_ar = $request->address_ar;
        $companySettings->address_en = $request->address_en;
        $companySettings->environment = $request->environment;
        $companySettings->save();

        Toastr::success(translate('Company settings saved successfully'));
        return redirect()->back();
    }

    public function storeEgsUnit(Request $request): RedirectResponse
    {
        $request->validate([
            'egs_id' => 'required|string|max:50|unique:zatca_egs_units,egs_id,' . ($request->id ?? ''),
            'name' => 'required|string|max:255',
            'type' => 'required|in:branch,cashier',
            'branch_id' => 'nullable|exists:branches,id',
        ], [
            'egs_id.required' => translate('EGS ID is required'),
            'egs_id.unique' => translate('EGS ID already exists'),
            'name.required' => translate('Name is required'),
        ]);

        if ($request->id) {
            $egsUnit = ZatcaEgsUnit::findOrFail($request->id);
        } else {
            $egsUnit = new ZatcaEgsUnit();
        }

        $egsUnit->egs_id = $request->egs_id;
        $egsUnit->name = $request->name;
        $egsUnit->type = $request->type;
        $egsUnit->branch_id = $request->branch_id;
        $egsUnit->save();

        Toastr::success(translate('EGS unit saved successfully'));
        return redirect()->back();
    }

    public function deleteEgsUnit($id): RedirectResponse
    {
        $egsUnit = ZatcaEgsUnit::findOrFail($id);
        
        if ($egsUnit->zatcaDocuments()->count() > 0) {
            Toastr::error(translate('Cannot delete EGS unit with existing documents'));
            return redirect()->back();
        }

        $egsUnit->delete();
        Toastr::success(translate('EGS unit deleted successfully'));
        return redirect()->back();
    }
}
