<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
 use Illuminate\Support\Facades\Auth;


class BusinessSettingsController extends Controller
{
    public function __construct(
        private BusinessSetting $business_setting
    ){}

    /**
     * @return Application|Factory|View
     */


public function shop_index(): View|Factory|Application|RedirectResponse
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

    return view('admin-views.business-settings.shop-index');
}


    /**
     * @param Request $request
     * @return RedirectResponse
     */
public function shop_setup(Request $request): RedirectResponse
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

    if (!in_array("settings.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Check if at least one of shabaka, credit, or cash is set to 1
    if (!$request->hasAny(['shabaka', 'credit', 'cash']) || 
        ($request->shabaka != 1 && $request->credit != 1 && $request->cash != 1)) {
        Toastr::warning(translate('يجب علي الاقل ان تكون طريقة دفع واحدة مفعلة.'));
        return back();
    }

    if ($request->pagination_limit == 0) {
        Toastr::warning(translate('pagination_limit_is_required'));
        return back();
    }

    DB::table('business_settings')->updateOrInsert(['key' => 'shop_name'], [
        'value' => $request['shop_name']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'shop_email'], [
        'value' => $request['shop_email']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'shop_phone'], [
        'value' => $request['shop_phone']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'shop_address'], [
        'value' => $request['shop_address']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'pagination_limit'], [
        'value' => $request['pagination_limit']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'stock_limit'], [
        'value' => $request['stock_limit']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'currency'], [
        'value' => $request['currency']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'country'], [
        'value' => $request['country']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'footer_text'], [
        'value' => $request['footer_text']
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'kilometer'], [
        'value' => $request['kilometer']
    ]);

    $curr_logo = $this->business_setting->where(['key' => 'shop_logo'])->first();
    DB::table('business_settings')->updateOrInsert(['key' => 'shop_logo'], [
        'value' => $request->has('shop_logo') ? Helpers::update('shop/', $curr_logo->value, 'png', $request->file('shop_logo')) : $curr_logo->value
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'time_zone'], [
        'value' => $request['time_zone'],
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'vat_reg_no'], [
        'value' => $request['vat_reg_no'],
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'number_tax'], [
        'value' => $request['number_tax'],
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'color1'], [
        'value' => $request['color1'],
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'color2'], [
        'value' => $request['color2'],
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'cash'], [
        'value' => $request['cash'],
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'shabaka'], [
        'value' => $request['shabaka'],
    ]);

    DB::table('business_settings')->updateOrInsert(['key' => 'agel'], [
        'value' => $request['agel'],
    ]);
      DB::table('business_settings')->updateOrInsert(['key' => 'tax'], [
        'value' => $request['tax'],
    ]);


    Toastr::success(translate('تم التحديث بنجاح'));
    return back();
}


    /**
     * @return Application|Factory|View
     */
    public function shortcut_key(): View|Factory|Application
    {
        return view('admin-views.business-settings.shortcut-key-index');
    }
}
