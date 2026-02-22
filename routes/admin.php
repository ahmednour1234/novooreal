<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StoresController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\StorageSellerController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\DevelopSellerController;
use App\Http\Controllers\Admin\CourseSellerController;
use App\Http\Controllers\Admin\TransactionSellerController;
use App\Http\Controllers\Admin\TransectionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ChatbotController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\BalanceSheetController;
use App\Http\Controllers\Admin\OperatingActivitiesController;
use App\Http\Controllers\Admin\IncomeStatementController;
use App\Http\Controllers\Admin\AgeingReceivablesController;
use App\Http\Controllers\Admin\PurchaseInvoiceController;
use App\Http\Controllers\Admin\MaintenanceLogController;
use App\Http\Controllers\Admin\AssetDisposalController;
use App\Http\Controllers\Admin\JobApplicantController;
use App\Http\Controllers\Admin\InterviewEvaluationController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\POSSessionController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\InstallmentContractController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\SellController;
use App\Http\Controllers\Admin\BillOfMaterialController;
use App\Http\Controllers\Admin\BOMComponentController;
use App\Http\Controllers\Admin\WorkCenterController;
use App\Http\Controllers\Admin\RoutingController;
use App\Http\Controllers\Admin\RoutingOperationController;
use App\Http\Controllers\Admin\ProductionOrderController;
use App\Http\Controllers\Admin\GuarantorController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\CostCenterReportsController;
use App\Http\Controllers\Admin\ReportsController;




Route::post('/pos/return/forget-session', [POSController::class, 'forgetReturnSession'])
    ->name('pos.return.forget');
    Route::get('real/invoicea2/{id}', [\App\Http\Controllers\Admin\POSController::class, 'generate_invoicereal'])->name('qrcode.order');
Route::post('/chatbot', [ChatbotController::class, 'chat'])->withoutMiddleware(['admin', 'auth']);
Route::post('/chatbot/suggestions', [ChatbotController::class, 'getSuggestions'])->withoutMiddleware(['admin', 'auth']);
Route::get('/admin/account/get-accounts/{storage_id}', [AccountController::class, 'getAccounts']);
Route::get('/admin/account/get-sub-accounts/{account_id}', [AccountController::class, 'getSubAccounts']);
Route::get('/admin/storage/get-sub-items/{type}/{id}', [AccountController::class, 'getSubItems']);
Route::get('/chart-of-accounts/fetch', [AccountController::class, 'getAccountsByTypeOrParent'])->name('chart.accounts.fetch');
Route::get('/admin/customer/details/{id}', [CustomerController::class, 'getDetails'])->name('admin.customer.details');



Route::group(['namespace'=>'Admin', 'as' => 'admin.', 'prefix'=>'admin'] ,function(){
    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function(){
        Route::get('login', 'LoginController@login')->name('login');

        Route::post('login', 'LoginController@submit');
        Route::get('logout', 'LoginController@logout')->name('logout');
    });

    Route::group(['middleware' => ['admin']], function(){
Route::get('/', function () {
    return view('admin-views.welcome');
})->name('welcome');
Route::get('/vouchers/{type}', [ExpenseController::class, 'getVouchers'])->name('vouchers.index');

Route::get('/vouchers/show/{voucher_number}', [ExpenseController::class, 'showVouchers'])
    ->name('vouchers.show');
    Route::resource('journal-entries', \App\Http\Controllers\Admin\JournalEntryController::class)
        ->only(['index','show','edit','update']);
        Route::post('journal-entries/{id}/reverse', [\App\Http\Controllers\Admin\JournalEntryController::class, 'reverseJournalEntry'])
            ->name('journal-entries.reverse');

Route::prefix('/reportss/costcenters')->name('costcenters.')->group(function () {
    Route::get('transactions', [CostCenterReportsController::class, 'transactions'])->name('transactions');
    Route::get('totals',       [CostCenterReportsController::class, 'totals'])->name('totals');
});

Route::prefix('/boms')->name('boms.')->group(function () {
    // عرض قائمة الـBOMs
    Route::get('/', [BillOfMaterialController::class, 'index'])->name('index');
    // نموذج إضافة الـBOM
    Route::get('create', [BillOfMaterialController::class, 'create'])->name('create');
    // حفظ الـBOM
    Route::post('/', [BillOfMaterialController::class, 'store'])->name('store');
    // نموذج تعديل الـBOM
    Route::get('{id}/edit', [BillOfMaterialController::class, 'edit'])->name('edit');
    // تحديث الـBOM
    Route::put('{id}', [BillOfMaterialController::class, 'update'])->name('update');
    // حذف/تعطيل الـBOM
    Route::delete('{id}', [BillOfMaterialController::class, 'destroy'])->name('destroy');
});
Route::prefix('/bomcomponents')->name('bomcomponents.')->group(function () {
    // عرض قائمة الـBOMs
    Route::get('/', [BOMComponentController::class, 'index'])->name('index');
    // نموذج إضافة الـBOM
    Route::get('create', [BOMComponentController::class, 'create'])->name('create');
    // حفظ الـBOM
    Route::post('/', [BOMComponentController::class, 'store'])->name('store');
    // نموذج تعديل الـBOM
    Route::get('{id}/edit', [BOMComponentController::class, 'edit'])->name('edit');
    // تحديث الـBOM
    Route::put('{id}', [BOMComponentController::class, 'update'])->name('update');
    // حذف/تعطيل الـBOM
    Route::delete('{id}', [BOMComponentController::class, 'destroy'])->name('destroy');
});
Route::prefix('/workcenters')->name('work-centers.')->group(function () {
    // عرض قائمة الـBOMs
    Route::get('/', [WorkCenterController::class, 'index'])->name('index');
    // نموذج إضافة الـBOM
    Route::get('create', [WorkCenterController::class, 'create'])->name('create');
    // حفظ الـBOM
    Route::post('/', [WorkCenterController::class, 'store'])->name('store');
    // نموذج تعديل الـBOM
    Route::get('{id}/edit', [WorkCenterController::class, 'edit'])->name('edit');
    // تحديث الـBOM
    Route::put('{id}', [WorkCenterController::class, 'update'])->name('update');
    // حذف/تعطيل الـBOM
    Route::delete('{id}', [WorkCenterController::class, 'destroy'])->name('destroy');
});
Route::prefix('/routings')->name('routings.')->group(function () {
    // عرض قائمة الـBOMs
    Route::get('/', [RoutingController::class, 'index'])->name('index');
    // نموذج إضافة الـBOM
    Route::get('create', [RoutingController::class, 'create'])->name('create');
    // حفظ الـBOM
    Route::post('/', [RoutingController::class, 'store'])->name('store');
    // نموذج تعديل الـBOM
    Route::get('{id}/edit', [RoutingController::class, 'edit'])->name('edit');
    // تحديث الـBOM
    Route::put('{id}', [RoutingController::class, 'update'])->name('update');
    // حذف/تعطيل الـBOM
    Route::delete('{id}', [RoutingController::class, 'destroy'])->name('destroy');
});
Route::prefix('/routing-operations')->name('routing-operations.')->group(function () {
    // عرض قائمة الـBOMs
    Route::get('/', [RoutingOperationController::class, 'index'])->name('index');
    // نموذج إضافة الـBOM
    Route::get('create', [RoutingOperationController::class, 'create'])->name('create');
    // حفظ الـBOM
    Route::post('/', [RoutingOperationController::class, 'store'])->name('store');
    // نموذج تعديل الـBOM
    Route::get('{id}/edit', [RoutingOperationController::class, 'edit'])->name('edit');
    // تحديث الـBOM
    Route::put('{id}', [RoutingOperationController::class, 'update'])->name('update');
    // حذف/تعطيل الـBOM
    Route::delete('{id}', [RoutingOperationController::class, 'destroy'])->name('destroy');
});
Route::prefix('/production-orders')->name('production-orders.')->group(function () {
    // عرض قائمة الـBOMs
    Route::get('/', [ProductionOrderController::class, 'index'])->name('index');
    // نموذج إضافة الـBOM
    Route::get('create', [ProductionOrderController::class, 'create'])->name('create');
    // حفظ الـBOM
    Route::post('/', [ProductionOrderController::class, 'store'])->name('store');
    // نموذج تعديل الـBOM
    Route::get('{id}/edit', [ProductionOrderController::class, 'edit'])->name('edit');
    // تحديث الـBOM
   Route::get('{id}/show', [ProductionOrderController::class, 'show'])->name('show');
   Route::get('/invoice/{id}', [ProductionOrderController::class, 'show_invoice'])->name('show_invoice');

    Route::put('{id}', [ProductionOrderController::class, 'update'])->name('update');
     Route::post('cancel/{id}', [ProductionOrderController::class, 'cancel'])->name('cancel');
  Route::post('startProduction/{id}', [ProductionOrderController::class, 'startProduction'])->name('startProduction');
Route::get('/{order}/finalize', [ProductionOrderController::class, 'showCompleteForm'])
    ->name('finalize.form');
    Route::post('/{order}/finalize', [ProductionOrderController::class, 'finalize'])
    ->name('finalize');
    // حذف/تعطيل الـBOM
    Route::delete('{id}', [ProductionOrderController::class, 'destroy'])->name('destroy');
});
Route::prefix('/guarantors')->name('guarantors.')->group(function () {
    // عرض قائمة الـBOMs
    Route::get('/', [GuarantorController::class, 'index'])->name('index');
    // نموذج إضافة الـBOM
    Route::get('create', [GuarantorController::class, 'create'])->name('create');
    // حفظ الـBOM
    Route::post('/', [GuarantorController::class, 'store'])->name('store');
    // نموذج تعديل الـBOM
    Route::get('{id}/edit', [GuarantorController::class, 'edit'])->name('edit');
    // تحديث الـBOM
   Route::get('{id}/show', [GuarantorController::class, 'show'])->name('show');

    Route::put('{id}', [GuarantorController::class, 'update'])->name('update');
    // حذف/تعطيل الـBOM
    Route::delete('{id}', [GuarantorController::class, 'destroy'])->name('destroy');
});
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index'); // إن أردت عرض القائمة
        Route::get('create', [ClientController::class, 'create'])->name('create');
        Route::post('store', [ClientController::class, 'store'])->name('store');
        Route::get('{client}', [ClientController::class, 'show'])->name('show');
        Route::get('{client}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('{client}', [ClientController::class, 'update'])->name('update');
        Route::patch('{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('toggleStatus');
    });
     Route::prefix('contracts')
         ->name('contracts.')
         ->group(function () {

        // قائمة العقود
        Route::get('/', [ContractController::class, 'index'])
             ->name('index');

        // نموذج إنشاء عقد جديد
        Route::get('create', [ContractController::class, 'create'])
             ->name('create');

        // حفظ العقد الجديد
        Route::post('/', [ContractController::class, 'store'])
             ->name('store');

        // عرض تفاصيل عقد
        Route::get('{contract}', [ContractController::class, 'show'])
             ->name('show');

        // نموذج تعديل عقد
        Route::get('{contract}/edit', [ContractController::class, 'edit'])
             ->name('edit');

        // تحديث العقد
        Route::put('{contract}', [ContractController::class, 'update'])
             ->name('update');

        // تفعيل/إلغاء تفعيل العقد
        Route::patch('{contract}/toggle-status', [ContractController::class, 'toggleStatus'])
             ->name('toggleStatus');
    });
Route::post('/pos/session/open', [POSSessionController::class, 'openSession'])->name('pos.session.open');
Route::post('/admin/pos/session/open', [POSSessionController::class, 'openSession'])->name('pos.session.open');
Route::get('/admin/pos/session/current', [POSSessionController::class, 'getCurrentSession'])->name('pos.session.current');
Route::post('/admin/pos/session/close', [POSSessionController::class, 'closeSession'])->name('pos.session.close');
Route::get('/admin/pos/session/invoices', [POSSessionController::class, 'getSessionInvoices'])
    ->name('pos.session.invoices');
    Route::get('/session/all', [POSSessionController::class, 'index'])
    ->name('pos.session.index');
    Route::get('/admin/products/details/pos/{id}', [POSSessionController::class, 'ajaxDetails'])->name('pos.session.product');
            Route::get('session/orders',  [POSSessionController::class, 'order_list'])->name('session.orders');
            Route::get('session/returns',  [POSSessionController::class, 'returns_list'])->name('session.returns');

// web.php

Route::get('/reports/balance-sheet', [BalanceSheetController::class, 'index'])->name('balance-sheet');
Route::get('/reports/indexOperating', [OperatingActivitiesController::class, 'indexOperating'])->name('indexOperating');
Route::get('/reports/indexTrialBalance', [ReportsController::class, 'trialBalance'])->name('indexTrialBalance');
Route::get('/reports/IncomeStatement', [IncomeStatementController::class, 'index'])->name('IncomeStatement');
Route::get('/reports/ageing-receivables', [AgeingReceivablesController::class, 'index'])->name('AgeingReceivables');
Route::get('/reports/ageing-receivables-suppliers', [AgeingReceivablesController::class, 'suppliersIndex'])->name('suppliersIndex');
Route::get('/reports/expense-cost-centers', [AgeingReceivablesController::class, 'expenseCostCentersReport'])->name('expenseCostCentersReport');
Route::get('/reports/showCostCenterReport/{id}', [AgeingReceivablesController::class, 'showCostCenterReport'])->name('showCostCenterReport');
    Route::get('purchase_invoice/create', [PurchaseInvoiceController::class, 'create'])
         ->name('purchase_invoice.create');
    Route::post('purchase_invoice/add_to_cart', [PurchaseInvoiceController::class, 'addToCart'])
         ->name('purchase_invoice.add_to_cart');
         Route::post('admin/purchase_invoice/update', [PurchaseInvoiceController::class, 'update'])
    ->name('purchase_invoice.update');
    Route::get('admin/purchase_invoice/refresh', [PurchaseInvoiceController::class, 'refreshInvoice'])
    ->name('purchase_invoice.refresh');
    Route::post('admin/purchase_invoice/cancel', [PurchaseInvoiceController::class, 'cancelInvoice'])
    ->name('purchase_invoice.cancel');
Route::post('admin/purchase_invoice/execute', [PurchaseInvoiceController::class, 'execute'])->name('purchase_invoice.execute');
  Route::post('admin/purchase_invoice/processReturn', [PurchaseInvoiceController::class, 'processReturn'])
    ->name('purchase_invoice.processReturn');
    Route::post('admin/purchase_invoice/processConfirmedReturn', [PurchaseInvoiceController::class, 'processConfirmedReturn'])
    ->name('purchase_invoice.processConfirmedReturn');
        Route::get('/dashboard', 'DashboardController@dashboard')->name('dashboard');
        Route::post('account-status','DashboardController@account_stats')->name('account-status');
        Route::get('settings', 'SystemController@settings')->name('settings');
        Route::post('settings', 'SystemController@settings_update');
        Route::get('settings-password', 'SystemController@settings')->name('settings.password');
        Route::post('settings-password', 'SystemController@settings_password_update')->name('settings-password');
   Route::get('/stores', [StoresController::class, 'index'])->name('stores.index');
    Route::get('/stores/create', [StoresController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoresController::class, 'store'])->name('stores.store');
Route::get('/stores/{store_id}/edit', [StoresController::class, 'edit'])->name('stores.edit');
    Route::post('/stores/{store_id}/update', [StoresController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{store_id}', [StoresController::class, 'destroy'])->name('stores.destroy');
            Route::prefix('installments')->name('installments.')->group(function () {
        // عرض قائمة العقود
        Route::get('/', [InstallmentContractController::class, 'index'])->name('index');
Route::get('/{id}', [InstallmentContractController::class, 'show'])->name('show');
    Route::post('installments/{contract}/pay', [InstallmentContractController::class, 'payInstallment'])
        ->name('pay');
        // تحميل PDF لعقد تقسيط
        Route::get('/pdf/{id}', [InstallmentContractController::class, 'downloadPDF'])->name('pdf');
            });
        
        Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
            Route::get('add', 'CategoryController@index')->name('add');
            Route::get('add-sub-category', 'CategoryController@sub_index')->name('add-sub-category');
        Route::get('add-special-category', 'CategoryController@indexspecial')->name('indexspecial');
            //Route::get('add-sub-sub-category', 'CategoryController@sub_sub_index')->name('add-sub-sub-category');
            Route::post('store', 'CategoryController@store')->name('store');
            Route::get('edit/{id}', 'CategoryController@edit')->name('edit');
            Route::get('sub-edit/{id}', 'CategoryController@edit_sub')->name('sub-edit');
            Route::post('update/{id}', 'CategoryController@update')->name('update');
            Route::post('update-sub/{id}', 'CategoryController@update_sub')->name('update-sub');
            Route::post('store', 'CategoryController@store')->name('store');
            Route::get('status/{id}/{status}', 'CategoryController@status')->name('status');
            Route::delete('delete/{id}', 'CategoryController@delete')->name('delete');
            //Route::post('search', 'CategoryController@search')->name('search');
        });
           Route::group(['prefix' => 'costcenter', 'as' => 'costcenter.'], function () {
            Route::get('add', 'CostCenterController@index')->name('add');
                        Route::get('show/{id}', 'CostCenterController@show')->name('show');
            Route::post('store', 'CostCenterController@store')->name('store');
            Route::get('edit/{id}', 'CostCenterController@edit')->name('edit');
            Route::post('update/{id}', 'CostCenterController@update')->name('update');
            Route::post('store', 'CostCenterController@store')->name('store');
            Route::get('status/{id}/{status}', 'CostCenterController@status')->name('status');
                 Route::get('/fetch', 'CostCenterController@fetch')->name('fetch');
            Route::get('/search', 'CostCenterController@search')->name('search');
                        Route::get('/statement', 'CostCenterController@statement')->name('statement');

        });
              Route::group(['prefix' => 'branch', 'as' => 'branch.'], function () {
            Route::get('add', 'BranchController@index')->name('add');
            Route::get('add-sub-category', 'BranchController@sub_index')->name('add-sub-category');
        Route::get('add-special-category', 'BranchController@indexspecial')->name('indexspecial');
            //Route::get('add-sub-sub-category', 'CategoryController@sub_sub_index')->name('add-sub-sub-category');
            Route::post('store', 'BranchController@store')->name('store');
            Route::get('edit/{id}', 'BranchController@edit')->name('edit');
            Route::get('sub-edit/{id}', 'BranchController@edit_sub')->name('sub-edit');
            Route::post('update/{id}', 'BranchController@update')->name('update');
            Route::post('update-sub/{id}', 'BranchController@update_sub')->name('update-sub');
            Route::post('store', 'BranchController@store')->name('store');
            Route::get('status/{id}/{status}', 'BranchController@status')->name('status');
            Route::delete('delete/{id}', 'BranchController@delete')->name('delete');
            //Route::post('search', 'CategoryController@search')->name('search');
        });
           Route::group(['prefix' => 'shift', 'as' => 'shift.'], function () {
            Route::get('add', 'ShiftController@add')->name('add');
                        Route::get('list', 'ShiftController@index')->name('list');

            Route::post('store', 'ShiftController@store')->name('store');
            Route::get('edit/{id}', 'ShiftController@edit')->name('edit');
            Route::post('update/{id}', 'ShiftController@update')->name('update');
            Route::post('store', 'ShiftController@store')->name('store');
            Route::get('status/{id}/{status}', 'ShiftController@status')->name('status');
            Route::delete('delete/{id}', 'ShiftController@delete')->name('delete');
        });
//transferproduct
        Route::group(['prefix' => 'transfer', 'as' => 'transfer.'], function () {
    // عرض قائمة التحويلات
    Route::get('/', 'TransferProductController@index')->name('index');

    // عرض نموذج إنشاء تحويل جديد
    Route::get('/create', 'TransferProductController@create')->name('create');

    // حفظ تحويل جديد (أو مسودة)
    Route::post('/', 'TransferProductController@store')->name('store');

    // عرض تفاصيل تحويل واحد
    Route::get('/{id}', 'TransferProductController@show')->name('show');

    // عرض نموذج تعديل تحويل (في حالة عدم الموافقة)
    Route::get('/{id}/edit', 'TransferProductController@edit')->name('edit');

    // تحديث تحويل موجود
    Route::put('/{id}', 'TransferProductController@update')->name('update');

    // حذف تحويل إذا لم تتم الموافقة عليه
    Route::delete('/{id}', 'TransferProductController@destroy')->name('destroy');

    // الموافقة على تحويل (قبول الطلب)
    Route::post('/{id}/accept', 'TransferProductController@accept')->name('accept');
});
        Route::group(['prefix' => 'stockbatch', 'as' => 'stockbatch.'], function () {
    // عرض قائمة التحويلات
    Route::get('/', 'StockBatchController@index')->name('index');
    // عرض تفاصيل تحويل واحد
    Route::get('/{id}', 'StockBatchController@show')->name('show');


});

Route::prefix('/quotations')
    ->name('quotations.')
    ->group(function () {
        // 1. صفحة إنشاء عرض السعر
        Route::get('/create', [QuotationController::class, 'create'])
            ->name('create');
      Route::get('/create_type', [QuotationController::class, 'create_type'])
            ->name('create_type');
        // 2. تنفيذ وحفظ عرض السعر
        Route::post('/execute', [QuotationController::class, 'execute'])
            ->name('execute');
             Route::post('/store', [QuotationController::class, 'store'])
            ->name('store');

          Route::post('/executequotation/{id}', [QuotationController::class, 'executequotaiton'])
            ->name('executequotation');
            
          Route::post('/executequotation_service/{id}', [QuotationController::class, 'executequotaiton'])
            ->name('executequotation_service');
        // 3. عرض قائمة كل عروض الأسعار
        Route::get('/', [QuotationController::class, 'index'])
            ->name('index');

        // 4. عرض تفاصيل عرض سعر واحد
        Route::get('/{id}', [QuotationController::class, 'show'])
            ->name('show')
            ->whereNumber('id');
                Route::get('/edit/{id}', [QuotationController::class, 'edit'])
            ->name('edit')
            ->whereNumber('id');
                    Route::get('/drafts', [QuotationController::class, 'drafts'])
            ->name('drafts');

        // 5. إرسال العرض كـ PDF عبر بريد أو SMS
        Route::post('/{id}/send-pdf', [QuotationController::class, 'sendPdf'])
            ->name('sendPdf')
            ->whereNumber('id');

        // 6. رد العميل على العرض (قبول/رفض)
        Route::post('/{id}/respond', [QuotationController::class, 'respond'])
            ->name('respond')
            ->whereNumber('id');
                    Route::put('/{id}/update', [QuotationController::class, 'update'])
            ->name('update')
            ->whereNumber('id');
                    Route::delete('/{id}/destroy', [QuotationController::class, 'destroy'])
            ->name('destroy')
            ->whereNumber('id');
    });
    Route::prefix('/sells')
    ->name('sells.')
    ->group(function () {
        // 1. صفحة إنشاء عرض السعر
        Route::get('/create', [SellController::class, 'create'])
            ->name('create');
    Route::get('/create_type', [SellController::class, 'create_type'])
            ->name('create_type');
        // 2. تنفيذ وحفظ عرض السعر
        Route::post('/execute', [SellController::class, 'execute'])
            ->name('execute');
             Route::post('/store', [SellController::class, 'store'])
            ->name('store');

          Route::post('/executesell/{id}', [SellController::class, 'executequotaiton'])
            ->name('executequotation');
        // 3. عرض قائمة كل عروض الأسعار
        Route::get('/', [SellController::class, 'index'])
            ->name('index');

        // 4. عرض تفاصيل عرض سعر واحد
        Route::get('/{id}', [SellController::class, 'show'])
            ->name('show')
            ->whereNumber('id');
                Route::get('/edit/{id}', [SellController::class, 'edit'])
            ->name('edit')
            ->whereNumber('id');
                    Route::get('/drafts', [SellController::class, 'drafts'])
            ->name('drafts');

        // 5. إرسال العرض كـ PDF عبر بريد أو SMS
        Route::post('/{id}/send-pdf', [SellController::class, 'sendPdf'])
            ->name('sendPdf')
            ->whereNumber('id');

        // 6. رد العميل على العرض (قبول/رفض)
        Route::post('/{id}/respond', [SellController::class, 'respond'])
            ->name('respond')
            ->whereNumber('id');
                    Route::put('/{id}/update', [SellController::class, 'update'])
            ->name('update')
            ->whereNumber('id');
                    Route::delete('/{id}/destroy', [SellController::class, 'destroy'])
            ->name('destroy')
            ->whereNumber('id');
    });
    Route::get('/getprice', 'TransferProductController@getPrice')->name('getPrice');


        Route::group(['prefix' => 'brand', 'as' => 'brand.'], function () {
            Route::get('add', 'BrandController@index')->name('add');
            Route::post('store','BrandController@store')->name('store');
            Route::get('edit/{id}', 'BrandController@edit')->name('edit');
            Route::post('update/{id}', 'BrandController@update')->name('update');
            Route::delete('delete/{id}', 'BrandController@delete')->name('delete');
        });
            Route::group(['prefix' => 'roles', 'as' => 'role.'], function () {
            Route::get('', 'RoleController@index')->name('index');
            Route::post('store','RoleController@store')->name('store');
            Route::Put('update/{id}', 'RoleController@update')->name('update');
            Route::delete('delete/{id}', 'RoleController@delete')->name('delete');
        });
        //unit
        Route::group(['prefix' => 'unit', 'as' => 'unit.'], function () {
            Route::get('index/{units}', 'UnitController@index')->name('index');
            Route::post('store/{units}', 'UnitController@store')->name('store');
            Route::get('edit/{id}/{type}', 'UnitController@edit')->name('edit');
            Route::post('update/{id}/{type}', 'UnitController@update')->name('update');
             Route::delete('delete/{id}/{units}', 'UnitController@delete')->name('delete');
        });

        Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
            Route::get('add/{type}', 'ProductController@index')->name('add');
            Route::get('getreportProducts', 'ProductController@getreportProducts')->name('getreportProducts');
                        Route::get('getreportProductsPurchase', 'ProductController@getreportProductsPurchase')->name('getreportProductsPurchase');
                                                Route::get('getreportProductsSales/{type}', 'ProductController@getreportProductsSales')->name('getreportProductsSales');
                                                Route::get('getreportMainStock', 'ProductController@getreportProductsMainStock')->name('getreportMainStock');
                                                Route::get('getReportProductsAllStock', 'ProductController@getReportProductsAllStock')->name('getReportProductsAllStock');

            Route::post('store', 'ProductController@store')->name('store');
                        Route::post('storeservice', 'ProductController@storeservice')->name('storeservice');

                        Route::get('product_type', 'ProductController@product_type')->name('product_type');
           Route::get('addexpire', 'ProductController@indexexpire')->name('addexpire');
            Route::post('storeexpire', 'ProductController@storeexpire')->name('storeexpire');
            Route::get('list', 'ProductController@list')->name('list');
            Route::get('listreportexpire', 'ProductController@listreportexpire')->name('listreportexpire');
                Route::get('listexpireinvoice', 'ProductController@listexpireinvoice')->name('listexpireinvoice');
            Route::get('listProductsByOrderType', 'ProductController@listProductsByOrderType')->name('listProductsByOrderType');
            Route::get('edit/{id}', 'ProductController@edit')->name('edit');
            Route::post('update/{id}', 'ProductController@update')->name('update');
            Route::delete('delete/{id}', 'ProductController@delete')->name('delete');
            Route::get('barcode-generate/{id}', 'ProductController@barcode_generate')->name('barcode-generate');
            Route::get('barcode/{id}', 'ProductController@barcode')->name('barcode');
            Route::get('bulk-import', 'ProductController@bulk_import_index')->name('bulk-import');
            Route::post('bulk-import', 'ProductController@bulk_import_data');
            Route::get('bulk-export', 'ProductController@bulk_export_data')->name('bulk-export');

            //ajax request
            Route::get('get-categories', 'ProductController@get_categories')->name('get-categories');
            Route::get('remove-image/{id}/{name}', 'ProductController@remove_image')->name('remove-image');
        });

Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
            Route::get('/pos/{type}', 'POSController@index')->name('index');
            Route::get('quick-view', 'POSController@quick_view')->name('quick-view');
            Route::post('variant_price', 'POSController@variant_price')->name('variant_price');
            Route::post('processReturn', 'POSController@processReturn')->name('processReturn');
                        Route::post('processReturn_service', 'POSController@processConfirmedReturn_service')->name('processReturn_service');
                        Route::get('processReturncashier', 'POSController@processReturncashier')->name('processReturncashier');

            Route::post('add-to-cart/{type}', 'POSController@addToCart')->name('add-to-cart');
            Route::post('add-to-cart/barcode/{type}', 'POSController@addToCartByBarcode')->name('addToCartByBarcode');

            Route::post('remove-from-cart', 'POSController@removeFromCart')->name('remove-from-cart');
            Route::post('cart-items', 'POSController@cart_items')->name('cart_items');
            Route::post('update-quantity/{type}', 'POSController@updateQuantity')->name('updateQuantity');
            Route::post('empty-cart', 'POSController@emptyCart')->name('emptyCart');
            Route::post('tax', 'POSController@update_tax')->name('tax');
            Route::post('discount', 'POSController@update_discount')->name('discount');
            Route::get('customers/{type}', 'POSController@get_customers')->name('customers');
            
            Route::get('customer-balance', 'POSController@customer_balance')->name('customer-balance');
            Route::post('order', 'POSController@place_order')->name('order');
            Route::post('storeplaceorder', 'POSController@storeplaceorder')->name('storeplaceorder');
            Route::get('orders', 'POSController@order_list')->name('orders');
            Route::get('order-details/{id}', 'POSController@order_details')->name('order-details');
            Route::get('refunds', 'POSController@refund_list')->name('refunds');
            Route::get('sample', 'POSController@sample_list')->name('sample');
            Route::get('donations', 'POSController@donation_list')->name('donations');
            Route::get('installments', 'POSController@installment_list')->name('installments');
            Route::get('stocks', 'StockController@history')->name('stocks');
            Route::post('reserveProduct', 'POSController@reserveProduct')->name('reserveProduct');
            // Route::get('installment', 'POSController@installment_list')->name('installment');
           Route::get('reservations/{type}/{active}','POSController@reservation_list')->name('reservations');
            Route::get('reservations_notification/{type}/{active}', 'POSController@reservation_list_notification')->name('reservation_list_notification');
            Route::get('invoice/{id}', 'POSController@generate_invoice');
            Route::get('invoicea2/{id}', 'POSController@generate_invoicea2');
            Route::get('sample/invoice/{id}', 'POSController@sample_generate_invoice');
            Route::get('sample/invoicea2/{id}', 'POSController@sample_generate_invoicea2');
            Route::get('donation/invoice/{id}', 'POSController@donation_generate_invoice');
                        Route::post('processConfirmedReturn', 'POSController@processConfirmedReturn')->name('processConfirmedReturn');
                                                Route::post('processConfirmedReturnCashier', 'POSController@processConfirmedReturnCashier')->name('processConfirmedReturnCashier');

            Route::post('deactivateReservedProductsByReservationId/{id}', 'POSController@deactivateReservedProductsByReservationId')->name('deactivateReservedProductsByReservationId');
            Route::get('we/reservations/invoice/{id}', 'POSController@generate_reservation_invoice')->name('generate_reservation_invoice');
            Route::get('we/reservations/invoicea2/{id}', 'POSController@generate_reservation_invoicea2')->name('generate_reservation_invoicea2');
            Route::get('requestsewq/invoice/{id}', 'POSController@generate_reservation_invoice_notification')->name('generate_reservation_invoice_notification');
            Route::get('reservationsnotification/invoice/{id}', 'POSController@generate_reservation_notification_invoice');
            Route::get('installments/invoice/{id}', 'POSController@generate_installments_invoice');
            Route::get('stocks/invoice/{id}', 'POSController@generate_stocks_invoice');
            Route::get('stocks/invoicea2/{id}', 'POSController@generate_stocks_invoicea2');
            Route::get('search-products','POSController@search_product')->name('search-products');
            Route::get('search-by-add','POSController@search_by_add_product')->name('search-by-add');

            Route::post('coupon-discount/{type}', 'POSController@coupon_discount')->name('coupon-discount');
            Route::post('remove-coupon','POSController@remove_coupon')->name('remove-coupon');
            Route::get('change-cart','POSController@change_cart')->name('change-cart');
            Route::get('new-cart-id','POSController@new_cart_id')->name('new-cart-id');
            Route::get('clear-cart-ids','POSController@clear_cart_ids')->name('clear-cart-ids');
            Route::get('get-cart-ids/{type}','POSController@get_cart_ids')->name('get-cart-ids');
            Route::post('/update-unit/{type}','POSController@updateUnit')->name('update.unit');
        });

        Route::group(['prefix' => 'vehicle-stock', 'as' => 'stock.'], function () {
            Route::get('/', 'StockController@index')->name('index');
            Route::get('products/{seller_id}', 'StockController@stock_products')->name('products');
            Route::get('vehicles', 'StockController@vehicles')->name('vehicles');
            Route::get('vehicles/products/{seller_id}', 'StockController@vehicle_products')->name('vehicles.products');
            Route::get('create', 'StockController@create')->name('create');
            Route::post('store', 'StockController@store')->name('store');
            Route::get('edit/{id}', 'StockController@edit')->name('edit');
            Route::post('update/{id}', 'StockController@update')->name('update');
            Route::delete('delete/{id}', 'StockController@delete')->name('delete');
        });

 Route::group(['prefix' => 'visitors', 'as' => 'visitor.'], function () {
            Route::get('/', 'VisitorController@index')->name('index');
            Route::get('/showResultVisitors/{seller_id}', 'VisitorController@showResultVisitors')->name('showResultVisitors');
            Route::get('visitor/{seller_id}', 'VisitorController@stock_products')->name('products');
            Route::get('visitor', 'VisitorController@vehicles')->name('vehicles');
            Route::get('visitor/products/{seller_id}', 'VisitorController@vehicle_products')->name('vehicles.products');
            Route::get('create', 'VisitorController@create')->name('create');
            Route::post('store', 'VisitorController@store')->name('store');
            Route::get('edit/{id}', 'VisitorController@edit')->name('edit');
            Route::post('update/{id}', 'VisitorController@update')->name('update');
            Route::delete('delete/{id}', 'VisitorController@delete')->name('delete');
            Route::get('admin/visitors/export','VisitorController@export')->name('export');

        });

        // account
        Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
            Route::get('add','AccountController@add')->name('add');
           
                Route::delete('destroy/{id}','AccountController@destroy')->name('destroy');


            Route::get('addone/{id}','AccountController@addone')->name('addone');
           Route::get('download','AccountController@download')->name('download');
            Route::post('store', 'AccountController@store')->name('store');
            Route::get('list', 'AccountController@list')->name('list');
            Route::get('listone/{id}', 'AccountController@listone')->name('listone');
            Route::get('view/{id}', 'AccountController@view')->name('view');
            Route::get('edit/{id}', 'AccountController@edit')->name('edit');
            Route::post('update/{id}', 'AccountController@update')->name('update');

            //expense
            Route::get('add-expense/{type}','ExpenseController@add')->name('add-expense');
                        Route::get('list-expense/{type}','ExpenseController@list')->name('list-expense');
                                                Route::get('getAccountsByType','ExpenseController@getAccountsByType')->name('getAccountsByType');

                                    Route::get('add-expense','ExpenseController@addExpense')->name('addExpense');
                        Route::get('list-expense','ExpenseController@listExpense')->name('listExpense');
                                                Route::post('store-expense','ExpenseController@storeExpense')->name('storeExpense');
                                                                                                Route::post('reverseExpenseTransaction','ExpenseController@reverseExpenseTransaction')->name('reverseExpenseTransaction');

                                                                                                Route::post('storesandExpense','ExpenseController@storesandExpense')->name('storesandExpense');
                                                Route::get('getOrdersByAccount','ExpenseController@getOrdersByAccount')->name('getOrdersByAccount');

                        Route::get('report-expense','ExpenseController@indexreport')->name('report-expense');

                        Route::get('invoice_expense/{id}','ExpenseController@generate_expense_invoice')->name('generate_expense_invoice');
            Route::get('download-expense/{type}','ExpenseController@download')->name('download-expense');
            Route::post('store-expense/{type}', 'ExpenseController@store')->name('store-expense');

            //income
            Route::get('add-income', 'IncomeController@add')->name('add-income');
            Route::post('store-income', 'IncomeController@store')->name('store-income');
            //transfer
            Route::get('add-transfer', 'TransferController@add')->name('add-transfer');
                        Route::get('list-transfer', 'TransferController@list')->name('list-transfer');
            Route::post('store-transfer', 'TransferController@store')->name('store-transfer');
            //transection
            Route::get('list-transection', 'TransectionController@list')->name('list-transection');
            Route::get('listkoyod-transection', 'TransectionController@listkoyod')->name('listkoyod-transection');
            Route::get('transection-export', 'TransectionController@export')->name('transection-export');

            //payable
            Route::get('add-payable', 'PayableController@add')->name('add-payable');
            Route::get('download/a', 'PayableController@download')->name('download-payable');
            Route::post('store-payable', 'PayableController@store')->name('store-payable');
            Route::post('payable-transfer','PayableController@transfer')->name('payable-transfer');

            //receivable
            Route::get('add-receivable', 'ReceivableController@add')->name('add-receivable');
            Route::post('store-receivable', 'ReceivableController@store')->name('store-receivable');
            Route::post('receivable-transfer','ReceivableController@transfer')->name('receivable-transfer');
        });
        //تسوية
   Route::group(['prefix' => 'inventory_adjustments', 'as' => 'inventory_adjustments.'], function() {
    Route::get('/', 'InventoryAdjustmentController@index')
         ->name('index');

    Route::get('/create', 'InventoryAdjustmentController@create')
         ->name('create');

    Route::post('/', 'InventoryAdjustmentController@store')
         ->name('store');

    Route::get('/{id}', 'InventoryAdjustmentController@show')
         ->name('show');

    Route::post('approve/{id}', 'InventoryAdjustmentController@approve')
         ->name('approve');
         
    Route::post('complete/{id}', 'InventoryAdjustmentController@complete')
         ->name('complete');
    Route::get('edit/{id}', 'InventoryAdjustmentController@edit')
         ->name('edit');

    Route::put('/{id}', 'InventoryAdjustmentController@update')
         ->name('update');

    Route::delete('/{id}', 'InventoryAdjustmentController@destroy')
         ->name('destroy');
});
        //customer
        Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
            Route::get('add','CustomerController@index')->name('add');
            Route::post('store', 'CustomerController@store')->name('store');
                                                            Route::get('invoice_expense/{id}','CustomerController@generate_expense_invoice')->name('generate_expense_invoice');

            Route::get('status/{id}/{status}', 'CustomerController@status')->name('status');
            Route::get('getCustomerData/{id}', 'CustomerController@getCustomerData')->name('getCustomerData');
            Route::get('list', 'CustomerController@list')->name('list');
            Route::any('prices/{id}', 'CustomerController@prices')->name('prices');
            Route::any('prices/edit/{customer_id}/{price_id}', 'CustomerController@edit_price')->name('prices.edit');
            Route::delete('prices/delete/{id}', 'CustomerController@delete_price')->name('prices.delete');
            Route::get('view/{id}', 'CustomerController@view')->name('view');
            Route::get('edit/{id}', 'CustomerController@edit')->name('edit');
            Route::post('update/{id}', 'CustomerController@update')->name('update');
            Route::delete('delete/{id}', 'CustomerController@delete')->name('delete');
            Route::post('update-balance','CustomerController@update_balance')->name('update-balance');
            Route::post('update-credit','CustomerController@update_credit')->name('update-credit');
                        Route::post('extra_discount','CustomerController@extra_discount')->name('extra_discount');

            Route::get('transaction-list/{id}', 'CustomerController@transaction_list')->name('transaction-list');
            Route::get('/admin/customers/export', 'CustomerController@export')->name('export');

        });

        //seller
        Route::group(['prefix' => 'seller', 'as' => 'seller.'], function () {
            Route::get('add','SellerController@index')->name('add');
            Route::post('store', 'SellerController@store')->name('store');
            Route::get('list', 'SellerController@list')->name('list');
            Route::any('prices/{id}', 'SellerController@prices')->name('prices');
            Route::any('prices/edit/{seller_id}/{price_id}', 'SellerController@edit_price')->name('prices.edit');
            Route::delete('prices/delete/{id}', 'SellerController@delete_price')->name('prices.delete');
            Route::get('edit/{id}', 'SellerController@edit')->name('edit');
            Route::post('update/{id}', 'SellerController@update')->name('update');
            Route::post('update-balance','SellerController@update_balance')->name('update-balance');
            Route::post('update-credit','SellerController@update_credit')->name('update-credit');
            Route::post('update-loan','SellerController@update_loan')->name('update-loan');
            Route::delete('delete/{id}', 'SellerController@delete')->name('delete');
        });
         Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
            Route::get('add','StaffController@index')->name('add');
            Route::post('store', 'StaffController@store')->name('store');
            Route::get('list', 'StaffController@list')->name('list');
            Route::get('edit/{id}', 'StaffController@edit')->name('edit');
            Route::post('update/{id}', 'StaffController@update')->name('update');
            Route::post('update-balance','StaffController@update_balance')->name('update-balance');
            Route::post('update-credit','StaffController@update_credit')->name('update-credit');
            Route::post('update-loan','StaffController@update_loan')->name('update-loan');
            Route::delete('delete/{id}', 'StaffController@delete')->name('delete');
        });
        //seller
        Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
            Route::get('add','AdminController@index')->name('add');
            Route::get('showmap','AdminController@showmap')->name('showmap');
            Route::post('store', 'AdminController@store')->name('store');
            Route::get('list', 'AdminController@list')->name('list');
            Route::get('edit/{id}', 'AdminController@edit')->name('edit');
            Route::post('update/{id}', 'AdminController@update')->name('update');
            Route::delete('delete/{id}', 'AdminController@delete')->name('delete');
            
        });

        //supplier
        Route::group(['prefix' => 'supplier', 'as' => 'supplier.'], function () {
            Route::get('add','SupplierController@index')->name('add');
            Route::post('store', 'SupplierController@store')->name('store');
            Route::get('list', 'SupplierController@list')->name('list');
            Route::get('status/{id}/{status}', 'SupplierController@status')->name('status');
            Route::get('view/{id}', 'SupplierController@view')->name('view');
                                    Route::get('invoice_expense/{id}','SupplierController@generate_expense_invoice')->name('generate_expense_invoice');

            Route::get('edit/{id}', 'SupplierController@edit')->name('edit');
            Route::post('update/{id}', 'SupplierController@update')->name('update');
            Route::delete('delete/{id}', 'SupplierController@delete')->name('delete');
            Route::get('products/{id}', 'SupplierController@product_list')->name('products');
            Route::get('transaction-list/{id}', 'SupplierController@transaction_list')->name('transaction-list');
            Route::post('update-balance','SupplierController@update_balance')->name('update-balance');
            Route::post('update-credit','SupplierController@update_credit')->name('update-credit');
            Route::post('extra_discount','SupplierController@extra_discount')->name('extra_discount');
            Route::post('add-new-purchase','SupplierController@add_new_purchase')->name('add-new-purchase');
            Route::post('pay-due','SupplierController@pay_due')->name('pay-due');
        });
        //stock limit
        Route::group(['prefix' => 'stock', 'as' => 'stock.'], function () {
            Route::get('stock-limit', 'StocklimitController@stock_limit')->name('stock-limit');
            Route::post('update-quantity', 'StocklimitController@update_quantity')->name('update-quantity');
        });
        //business settings
        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
            Route::get('shop-setup', 'BusinessSettingsController@shop_index')->name('shop-setup');
            Route::post('update-setup', 'BusinessSettingsController@shop_setup')->name('update-setup');
            Route::get('shortcut-keys', 'BusinessSettingsController@shortcut_key')->name('shortcut-keys');
        });

        //coupon
        Route::group(['prefix' => 'coupon', 'as' => 'coupon.'], function () {
            Route::get('add-new', 'CouponController@add_new')->name('add-new');
            Route::post('store', 'CouponController@store')->name('store');
            Route::get('edit/{id}', 'CouponController@edit')->name('edit');
            Route::post('update/{id}', 'CouponController@update')->name('update');
            Route::get('status/{id}/{status}', 'CouponController@status')->name('status');
            Route::delete('delete/{id}', 'CouponController@delete')->name('delete');
        });
        
        Route::group(['prefix' => 'regions', 'as' => 'regions.'], function() {
            Route::get('list', 'DashboardController@regionList')->name('list');
            Route::post('store', 'DashboardController@regionStore')->name('store');
            Route::get('edit/{id}', 'DashboardController@regionEdit')->name('edit');
            Route::post('update/{id}', 'DashboardController@regionUpdate')->name('update');
            Route::delete('delete/{id}', 'DashboardController@regionDelete')->name('delete');
        });
    Route::group(['prefix' => 'storages', 'as' => 'storage.','namespace' => 'App\Http\Controllers\Admin'], function() {
    Route::get('list', [StorageController::class, 'index'])->name('list');
    
        Route::get('indextree', [StorageController::class, 'indextree'])->name('indextree');
    Route::get('create', [StorageController::class, 'create'])->name('create');
    Route::post('store', [StorageController::class, 'store'])->name('store');
    Route::get('edit/{id}', [StorageController::class, 'edit'])->name('edit');
    Route::put('update/{id}', [StorageController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [StorageController::class, 'delete'])->name('delete');
});
    Route::group(['prefix' => 'tax', 'as' => 'taxe.'], function() {
    Route::get('list', [TaxController::class, 'index'])->name('list');
    Route::get('list/tax', [TransectionController::class, 'listall'])->name('listall');
    Route::get('list/box', [TransectionController::class, 'listalltoday'])->name('listalltoday');
        Route::get('list/listalltodaybyseller', [TransectionController::class, 'listalltodaybyseller'])->name('listalltodaybyseller');
    Route::get('list/today', [TransectionController::class, 'listallbox'])->name('listallbox');
        Route::get('listalltodaynew', [TransectionController::class, 'listalltodaynew'])->name('listalltodaynew');

    Route::get('create', [TaxController::class, 'create'])->name('create');
    Route::post('store', [TaxController::class, 'store'])->name('store');
    Route::get('edit/{id}', [TaxController::class, 'edit'])->name('edit');
    Route::post('update/{id}', [TaxController::class, 'update'])->name('update');
    Route::get('status/{id}/{status}',[TaxController::class, 'status'])->name('status');
    Route::delete('delete/{id}', [TaxController::class, 'delete'])->name('delete');
});
  Route::group(['prefix' => 'storagesseller', 'as' => 'storageseller.','namespace' => 'App\Http\Controllers\Admin'], function() {
    Route::get('list', [StorageSellerController::class, 'index'])->name('list');
    Route::get('create', [StorageSellerController::class, 'create'])->name('create');
    Route::post('store', [StorageSellerController::class, 'store'])->name('store');
    Route::get('edit/{id}', [StorageSellerController::class, 'edit'])->name('edit');
Route::put('admin/storage-sellers/update/{id}', [StorageSellerController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [StorageSellerController::class, 'delete'])->name('delete');
});
Route::get('/admin/notifications/{id}/{type}', [NotificationController::class, 'showItemById'])->name('admin.notifications.show');
Route::get('/admin/notifications', [NotificationController::class, 'listItems'])->name('admin.notifications.listItems');

        //order notification
        Route::middleware('auth:admin')->group(function () {
                    Route::get('/ordernotification', 'OrderNotificationController@index')->name('ordernotification.index');
                    Route::get('/ordernotification/{order_id}', 'OrderNotificationController@show')->name('ordernotification.show');
        Route::post('/ordernotification/store', 'OrderNotificationController@placeOrder')->name('ordernotification.placeOrder');
        Route::get('/admin/orders/search', 'OrderNotificationController@search')->name('orders.search');
                    Route::get('/productsunlike', 'OrderNotificationController@Productunlike')->name('ordernotification.Productunlike');


});

Route::prefix('admin/salaries')->group(function () {
    Route::get('/', [SalaryController::class, 'index'])->name('salaries.index');
        Route::get('/showsalary_summary/{sellerId}/{month}', [SalaryController::class, 'showSalarySummary'])->name('salaries.showsalary_summary');

    Route::get('/create', [SalaryController::class, 'create'])->name('salaries.create');
        Route::get('/createrating', [SalaryController::class, 'createrating'])->name('salaries.createrating');
    Route::post('/', [SalaryController::class, 'store'])->name('salaries.store');
        Route::post('/rating', [SalaryController::class, 'storerating'])->name('salaries.storerating');

    Route::get('/{id}', [SalaryController::class, 'show'])->name('salaries.show');
    Route::get('admin/salary/show/{id}', [SalaryController::class, 'showsalary'])->name('salaries.showsalary');
    Route::get('/{id}/edit', [SalaryController::class, 'edit'])->name('salaries.edit');
});
Route::prefix('admin/developsellers')->group(function () {
    Route::get('/{type}', [DevelopSellerController::class, 'index'])->name('developsellers.index');
    Route::get('/create/{type}', [DevelopSellerController::class, 'create'])->name('developsellers.create');
    Route::post('/', [DevelopSellerController::class, 'store'])->name('developsellers.store');
    Route::get('/{id}/edit', [DevelopSellerController::class, 'edit'])->name('developsellers.edit');
    Route::put('/{id}', [DevelopSellerController::class, 'update'])->name('developsellers.update');
        Route::put('status/{id}', [DevelopSellerController::class, 'status'])->name('developsellers.status');
Route::put('admin/developsellers/status/{id}', [DevelopSellerController::class, 'status'])->name('developsellers.status');

        Route::delete('/{id}', [DevelopSellerController::class, 'destroy'])->name('developsellers.destroy');

});
Route::prefix('admin/TransactionSeller')->group(function () {
    Route::get('/', [TransactionSellerController::class, 'index'])->name('TransactionSeller.index');
        Route::put('status/{id}', [TransactionSellerController::class, 'status'])->name('TransactionSeller.status');

}); 
                 Route::resource('coursesellers', CourseSellerController::class)->except(['show']);
// عرض صفحة نموذج الاهلاك
Route::get('depreciation', [\App\Http\Controllers\Admin\DepreciationController::class, 'showDepreciationPage'])
    ->name('depreciation.show');

// معالجة نموذج الاهلاك
Route::post('admin/depreciation/depreciate', [\App\Http\Controllers\Admin\DepreciationController::class, 'depreciateAsset'])
    ->name('depreciation.depreciate');

    });
    Route::get('admin/depreciation/asset-details', [\App\Http\Controllers\Admin\DepreciationController::class, 'getAssetDetails'])
    ->name('depreciation.getAssetDetails');
Route::get('assets', [\App\Http\Controllers\Admin\DepreciationController::class, 'index'])
    ->name('depreciation.index');
Route::get('assets/{id}', [\App\Http\Controllers\Admin\DepreciationController::class, 'show'])
    ->name('assets.show');
Route::get('assets/{id}/transactions', [\App\Http\Controllers\Admin\DepreciationController::class, 'assetTransactions'])
    ->name('assets.transactions');
Route::post('assets/{id}/reverseDepreciation', [\App\Http\Controllers\Admin\DepreciationController::class, 'reverseDepreciation'])
    ->name('assets.reverseDepreciation');
    // تسوية المخازن
// في ملف routes/web.php

Route::prefix('/maintenance_logs')->name('maintenance_logs.')->group(function () {
    // عرض قائمة سجلات الصيانة
    Route::get('/', [MaintenanceLogController::class, 'index'])->name('index');

    // عرض نموذج إضافة سجل صيانة جديد
    Route::get('/create', [MaintenanceLogController::class, 'create'])->name('create');

    // تخزين سجل صيانة جديد
    Route::post('/store', [MaintenanceLogController::class, 'store'])->name('store');

    // عرض تفاصيل سجل صيانة معين
    Route::get('/{id}', [MaintenanceLogController::class, 'show'])->name('show');

    // عرض نموذج تعديل سجل صيانة
    Route::get('/{id}/edit', [MaintenanceLogController::class, 'edit'])->name('edit');

    // تحديث سجل صيانة معين
    Route::put('/{id}', [MaintenanceLogController::class, 'update'])->name('update');

    // حذف سجل صيانة معين
    Route::delete('/{id}', [MaintenanceLogController::class, 'destroy'])->name('destroy');
});


Route::prefix('assets/disposal')->name('disposal.')->group(function () {
    // بيع الأصل
    Route::get('/{asset_id}/sale', [AssetDisposalController::class, 'createSale'])->name('sale.create');
    Route::post('/{asset_id}/sale', [AssetDisposalController::class, 'storeSale'])->name('sale.store');

    // إغلاق الأصل بالإهلاك التام
    Route::get('/{asset_id}/complete', [AssetDisposalController::class, 'createCompleteDepreciation'])->name('complete.create'); // تستطيع تغيير الاسم
    Route::post('/{asset_id}/complete', [AssetDisposalController::class, 'storeCompleteDepreciation'])->name('complete.store');

    // التخلص عبر الإهداء أو النفايات
    Route::get('/{asset_id}/donation', [AssetDisposalController::class, 'createDonation'])->name('donation.create');
    Route::post('/{asset_id}/donation', [AssetDisposalController::class, 'storeDonation'])->name('donation.store');
});

Route::prefix('/job_applicants')->name('job_applicants.')->group(function () {
    // List all job applicants
    Route::get('/', [JobApplicantController::class, 'index'])->name('index');
    
    // Show the form to create a new applicant
    Route::get('/create', [JobApplicantController::class, 'create'])->name('create');
    
    // Store a new applicant
    Route::post('/store', [JobApplicantController::class, 'store'])->name('store');
    
    // Show the form to edit an applicant by ID
    Route::get('/edit/{id}', [JobApplicantController::class, 'edit'])->name('edit');
    
    // Update an applicant by ID
    Route::put('/update/{id}', [JobApplicantController::class, 'update'])->name('update');
    
    // Delete an applicant by ID
    Route::delete('/destroy/{id}', [JobApplicantController::class, 'destroy'])->name('destroy');
    Route::get('{id}/interviews', [JobApplicantController::class, 'showInterviews'])->name('interviews');

});

Route::prefix('/')->name('interview_evaluations.')->group(function () {
    // Other routes...

    // Interview evaluations for a specific applicant
    Route::post('/{applicantId}/interview_evaluations/store', [InterviewEvaluationController::class, 'store'])
         ->name('store');
    Route::get('/interview-evaluations/edit/{id}', [InterviewEvaluationController::class, 'edit'])
         ->name('edit');
             Route::get('/interview-evaluations/create/{id}', [InterviewEvaluationController::class, 'create'])
         ->name('create');
    Route::put('/interview-evaluations/update/{id}', [InterviewEvaluationController::class, 'update'])
         ->name('update');
    Route::delete('/interview-evaluations/destroy/{id}', [InterviewEvaluationController::class, 'destroy'])
         ->name('destroy');
});
// routes/web.php
Route::get('/account/statement', [\App\Http\Controllers\Admin\AccountStatementController::class, 'statement'])
    ->name('account.statement');

Route::prefix('/attendance')->name('attendance.')->group(function () {

    
    // Route to show attendance records, with filtering by date and employee, and summary data
    Route::get('/', [AttendanceController::class, 'showAttendances'])
         ->name('index');
});

});
Route::get('/admin/chart/accounts/search', [AccountController::class, 'search'])->name('chart.accounts.search');
