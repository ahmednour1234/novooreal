@extends('layouts.admin.app')

@section('content')
<div class="content container-fluid">
                 <div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                    <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                </a>
            </li>
 
                        <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                {{ \App\CPU\translate('إدارة الأدوار والصلاحيات') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>


<div class="d-flex justify-content-end">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addRoleModal">إضافة دور جديد</button>
        </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>اسم الدور</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $index => $role)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $role->name }}</td>
                     
                        <td>
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#editRoleModal{{ $role->id }}">تعديل</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- إضافة دور جديد -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.role.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إضافة دور جديد</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم الدور</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>الصلاحيات</label>

                        @php
                          $permissions = [
                           'الادوار' => [
        'role.index' => 'عرض',
        'role.store' => 'إضافة',
        'role.update' => 'تحديث',

    ],
     'مواعيد العمل' => [
        'shift.index' => 'عرض',
        'shift.store' => 'إضافة',
        'shift.update' => 'تحديث'
    ],
    'الفروع' => [
        'branch.index' => 'عرض',
        'branch.store' => 'إضافة',
        'branch.update' => 'تحديث'
    ],
    'الإعدادات' => [
        'settings.update' => 'تحديث',
        'settings.index' => 'عرض'
    ],
    'الأدمن' => [
        'admin.update' => 'تحديث',
        'admin.index' => 'عرض',
        'admin.store' => 'تخزين',
        'admin.destroy' => 'حذف'
    ],
    'قسم الموارد البشرية' => [
        'staff.update' => 'تحديث موظف',
        'staff.index' => 'عرض الموظفين',
        'staff.store' => 'اضافة موظف',
        'staff.destroy' => 'حذف موظف',
        'interview.store' => 'اضافة طلب توظيف',
        'interview.index' => 'عرض طلب توظيف',
        'interview.update' => 'تحديث طلب توظيف',
        'interview.destroy' => 'حذف طلب توظيف',
        'meeting.store' => 'اضافة مقابلة',
        'meeting.update' => 'تعديل مقابلة',
        'meeting.destroy' => 'حذف مقابلة',
        'meeting.index' => 'تاريخ المقابلات',
        'attendace.index' => 'سجلات حضور والانصراف',
        'staff.rate' => 'تقييم موظفين',
        'develop.store'=>'تطوير',
        'develop.update'=>'تعديل تطوير',
        'develop.destroy'=>'حذف تطوير',
        'develop.approve'=>'موفقة علي إجازة',
        'course.store'=>'كورسات ',
        'salary.index' => 'عرض الرواتب',
        'salary.store' => 'دفع مرتب',

    ],
     'المناديب' => [
        'seller.update' => 'تحديث',
        'seller.index' => 'عرض',
        'seller.store' => 'تخزين',
        'seller.map'=>'الخريطة',
        'seller.price.store'=>'انشاء اسعار خاصة',
        'seller.price.update'=>'تعديل اسعار خاصة',
        'seller.price.destroy'=>'حذف اسعار خاصة',
        'visit.store'=>'انشاء زيارات',
        'visit.index'=>'رؤية زيارات',
        'visit.destroy'=>'حذف زيارات',
    ],
     'الموردين' => [
        'supplier.update' => 'تحديث',
        'supplier.index' => 'عرض',
        'supplier.store' => 'تخزين',
        'supplier.active'=>'تفعيل',
        'supplier.destroy' => 'حذف',
        'supplier.show'=>'كشف حساب',
    ],
     'العملاء' => [
        'customer.update' => 'تحديث',
        'customer.index' => 'عرض',
        'customer.store' => 'تخزين',
        'customer.active'=>'تفعيل',
        'customer.destroy' => 'حذف',
        'customer.show'=>'كشف حساب',
         'customer.price.store'=>'انشاء اسعار خاصة',
        'customer.price.update'=>'تعديل اسعار خاصة',
        'customer.price.destroy'=>'حذف اسعار خاصة',
    ],
    'التخصصات' => [
        'category.update' => 'تحديث',
        'category.index' => 'عرض',
        'category.store' => 'تخزين',
        'category.active'=>'تفعيل',
        'category.destroy' => 'حذف',
    ],
    'المناطق' => [
        'region.update' => 'تحديث',
        'region.index' => 'عرض',
        'region.store' => 'تخزين',
        'region.destroy' => 'حذف',
    ],
    'التقارير' => [
        'report.productsales' => 'المنتجات المباعة',
        'report.productpurchases' => 'المشتريات',
        'report.pointsales' => 'نقاط البيع',
        'report.gain' => 'هامش الربح والخسارة',
        'report.tax' => 'الضرائب',
        'report.box' => 'الصندوق',
        'report.boxseller' => 'الصندوق اليومي',
        'report.mainstock' => 'المستودع الرئيسي',
        'report.allstock' => 'جميع المستودعات',
        'report.expire' => 'الصلاحية',
        'report.stocklimit' => 'النواقص',
        'report.unlike' => 'الركود',
    ],
    'المنتجات' => [
        'product.update' => 'تحديث',
        'product.index' => 'عرض',
        'product.store' => 'تخزين',
        'product.destroy' => 'حذف',
        'product.barcode' => 'باركود',
        'product.show' => 'تتبع',
        'product.expire' => 'اضافة هالك',
        'product.expire.show' => 'قائمة الهالك',
        'product.excel.import' => 'استيراد اكسل',
        'product.excel.export' => 'اصدار اكسل',

    ],
      'الضريبة' => [
        'tax.index' => 'عرض',
        'tax.store' => 'إضافة',
        'tax.update' => 'تحديث',
        'tax.active' => 'تفعيل'
    ],
     'وحدات القياس' => [
        'unit.update' => 'تحديث',
        'unit.index' => 'عرض',
        'unit.store' => 'تخزين',
        'unit.destroy' => 'حذف',
    ],
      'أمر توريد' => [
        'import41.index' => 'عرض',
        'import41.update' => 'موافقة',
        'import41.store' => 'رفض',
    ],
      'أمر مرتجع' => [
        'export71.index' => 'عرض',
    ],
     'أمر صرف' => [
        'export32.index' => 'عرض',
        'export32.store' => 'انشاء',
        'export32.update' => 'تحديث',
        'export32.destroy' => 'حذف',
        'export32.show' => 'قائمة المنتجات داخل المستودعات',
    ],
     'أمر تحويل بين الفروع' => [
        'transfer.view.all' => 'عرض',
'transfer.view'=>'طباعة',
        'transfer.create' => 'انشاء',
        'transfer.edit' => 'تحديث',
        'transfer.delete' => 'حذف',
        'transfer.accept' => 'موافقة علي تحويل',
    ],
    'التسويات' => [
        'InventoryAdjustment.view.all' => 'عرض',
'InventoryAdjustment.view'=>'طباعة',
        'InventoryAdjustment.create' => 'انشاء',
        'InventoryAdjustment.edit' => 'تحديث',
        'InventoryAdjustment.destroy' => 'حذف',
                'InventoryAdjustment.show' => 'عرض التسوية',
        'InventoryAdjustment.end' => 'انهاء تسوية',
        'InventoryAdjustment.accept' => 'اعتماد تسوية',
    ],
    'المخازن'=> [
        'store.index' => 'عرض',
        'store.store' => 'انشاء',
        'store.update' => 'تحديث',
        'store.destroy' => 'حذف',
    ],
     'الفواتير'=> [
        'transectionseller.index' => ' عرض تحويلات المناديب',
        'transectionseller.approve' => 'موافقة علي تحويلات المناديب',
        'order4.index' => 'المبيعات',
        'order7.index' => 'مرتجعات',
        'order12.index' => 'مشتريات',
        'order24.index' => 'مردودد مشتريات',
        'installment.index' => 'التحصيلات',
        'reservation.index' => 'الحجوزات',
        'stock.index' => 'الرحلات',
    ],
    'إنشاء مبيعات'=> [
        'pos4.index' => 'المبيعات',
        'pos7.index' => 'مرتجعات',
        'pos12.index' => 'مشتريات',
        'pos24.index' => 'مردودد مشتريات',
        'pos1.index' => 'كاشير',

    ],
      'الأِشعارات'=> [
        'notification.index' => 'الأشعارات',
        'notification4.index' => 'المبيعات',
        'notification7.index' => 'مرتجعات',
        'notification13.index' => 'تحصيلات',
        'notification500.index' => 'تحويلات',
        'notification41.index' => 'امر توريد',
        'notification71.index' => 'امر صرف',
    ],
      'قسم التقارير المالية'=> [
        'expense2.index' => 'تقرير  الأصول الثابتة',
        'expense100.index' => 'تقرير سندات الصرف',
        'expense200.index' => 'تقرير سندات القبض',
        'transfer.index' => 'تقرير قيود يدوية',
        'mizania.report' => 'تقرير ميزانية عمومية ',
        'tadfk.report' => 'تقرير التدفقات النقدية',
        'mizan.report' => 'تقرير ميزان المراجعة ',
        'kamtdakhl.report' => 'تقرير قائمة الدخل ',
        'yearscustomer.report' => 'تقرير أعمار ديون العملاء',
        'yearssupplier.report' => 'تقرير أعمار ديون الموردين',
        'costcenter.report' => 'تقرير مراكز التكلفة',
    ],
      'قسم الأصول الثابتة '=> [
        'assets.index' => 'جميع  الأصول الثابتة',
        'asset.koyod' => 'قيود الأصل الثابت',
        'asset.details' => 'تفاصيل الأصل الثابت',
        'expense2.store' => 'إضافة أصل ثابت',
        'asset.ehlak' => 'إهلاك أصل ثابت',
        'asset.addsayan' => 'إضافة صيانة أصل ثابت',
        'asset.showsayana' => 'عرض صيانة  أصل ثابت',
    ],
      
    'مراكز التكلفة' => [
        'costcenter.update' => 'تحديث',
        'costcenter.index' => 'عرض',
        'costcenter.store' => 'تخزين',
        'costcenter.active'=>'تفعيل',
        'costcenter.showindx' => 'مراكز فرعية',
    ],
     'شجرة الحسابات'=> [
        'account.index' => 'عرض',
        'account.store' => 'انشاء',
        'account.update' => 'تحديث',
        'account.listone' => 'عرض فرعية',
        'account.storeone' => 'اضافة فرعية',
    ],
      'القيود المحاسبية'=> [
        'transection.listkoyod' => 'عرض',
    ] ,
     'ارصدة إفتتاحية'=> [
        'start.index' => 'عرض',
        'start.store' => 'انشاء',
    ],
      'سندات الصرف'=> [
        'expense100.store' => 'انشاء',
    ],
      'سندات قيبض'=> [
        'expense200.store' => 'انشاء',
    ],
      'تحويل بين الحسابات'=> [
        'transfer.store' => 'انشاء',
    ],
      'كشف الحساب'=> [
        'transection.list' => 'عرض',
    ]
    ,
      'لوحة التحكم'=> [
        'dashboard.list' => 'عرض',
    ]
];

                        @endphp

                        @foreach($permissions as $category => $perms)
                            @php
                                $groupSlug = Str::slug($category);
                            @endphp
                            
                            <div class="mb-2">
                                <label class="font-weight-bold">{{ $category }}</label>
                                <input type="checkbox" class="check-all" data-group="{{ $groupSlug }}">
                            </div>

                            <div class="row">
                                @foreach($perms as $key => $label)
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}" 
                                                   class="form-check-input {{ $groupSlug }}">
                                            <label class="form-check-label">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">إضافة</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- تعديل دور -->
@foreach($roles as $role)
<div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.role.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">تعديل الدور</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم الدور</label>
                        <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label>الصلاحيات</label>

                        @php
                            $storedPermissions = json_decode($role->data, true) ?? [];
                          $permissions = [
                                   'الادوار' => [
        'role.index' => 'عرض',
        'role.store' => 'إضافة',
        'role.update' => 'تحديث',

    ],
     'مواعيد العمل' => [
        'shift.index' => 'عرض',
        'shift.store' => 'إضافة',
        'shift.update' => 'تحديث'
    ],
    'الفروع' => [
        'branch.index' => 'عرض',
        'branch.store' => 'إضافة',
        'branch.update' => 'تحديث'
    ],
    'الإعدادات' => [
        'settings.update' => 'تحديث',
        'settings.index' => 'عرض'
    ],
    'الأدمن' => [
        'admin.update' => 'تحديث',
        'admin.index' => 'عرض',
        'admin.store' => 'تخزين',
        'admin.destroy' => 'حذف'
    ],
    'قسم الموارد البشرية' => [
        'staff.update' => 'تحديث موظف',
        'staff.index' => 'عرض الموظفين',
        'staff.store' => 'اضافة موظف',
        'staff.destroy' => 'حذف موظف',
        'interview.store' => 'اضافة طلب توظيف',
        'interview.index' => 'عرض طلب توظيف',
        'interview.update' => 'تحديث طلب توظيف',
        'interview.destroy' => 'حذف طلب توظيف',
        'meeting.store' => 'اضافة مقابلة',
        'meeting.update' => 'تعديل مقابلة',
        'meeting.destroy' => 'حذف مقابلة',
        'meeting.index' => 'تاريخ المقابلات',
        'attendace.index' => 'سجلات حضور والانصراف',
        'staff.rate' => 'تقييم موظفين',
        'develop.store'=>'تطوير',
        'develop.update'=>'تعديل تطوير',
        'develop.destroy'=>'حذف تطوير',
        'develop.approve'=>'موفقة علي إجازة',
        'course.store'=>'كورسات ',
        'salary.index' => 'عرض الرواتب',
        'salary.store' => 'دفع مرتب',

    ],
     'المناديب' => [
        'seller.update' => 'تحديث',
        'seller.index' => 'عرض',
        'seller.store' => 'تخزين',
        'seller.map'=>'الخريطة',
        'seller.price.store'=>'انشاء اسعار خاصة',
        'seller.price.update'=>'تعديل اسعار خاصة',
        'seller.price.destroy'=>'حذف اسعار خاصة',
        'visit.store'=>'انشاء زيارات',
        'visit.index'=>'رؤية زيارات',
        'visit.destroy'=>'حذف زيارات',
    ],
        'الموردين' => [
        'supplier.update' => 'تحديث',
        'supplier.index' => 'عرض',
        'supplier.store' => 'تخزين',
        'supplier.destroy' => 'حذف',
        'supplier.active'=>'تفعيل',
        'supplier.show'=>'كشف حساب',
    ],
     'العملاء' => [
        'customer.update' => 'تحديث',
        'customer.index' => 'عرض',
        'customer.store' => 'تخزين',
        'customer.active'=>'تفعيل',
        'customer.destroy' => 'حذف',
        'customer.show'=>'كشف حساب',
         'customer.price.store'=>'انشاء اسعار خاصة',
        'customer.price.update'=>'تعديل اسعار خاصة',
        'customer.price.destroy'=>'حذف اسعار خاصة',
    ],
     'التخصصات' => [
        'category.update' => 'تحديث',
        'category.index' => 'عرض',
        'category.store' => 'تخزين',
        'category.active'=>'تفعيل',
        'category.destroy' => 'حذف',
    ],
    'المناطق' => [
        'region.update' => 'تحديث',
        'region.index' => 'عرض',
        'region.store' => 'تخزين',
        'region.destroy' => 'حذف',
    ],
    'التقارير' => [
        'report.productsales' => 'المنتجات المباعة',
        'report.productpurchases' => 'المشتريات',
        'report.pointsales' => 'نقاط البيع',
        'report.gain' => 'هامش الربح والخسارة',
        'report.tax' => 'الضرائب',
        'report.box' => 'الصندوق',
        'report.boxseller' => 'الصندوق اليومي',
        'report.mainstock' => 'المستودع الرئيسي',
        'report.allstock' => 'جميع المستودعات',
        'report.expire' => 'الصلاحية',
        'report.stocklimit' => 'النواقص',
        'report.unlike' => 'الركود',
    ],
    'المنتجات' => [
        'product.update' => 'تحديث',
        'product.index' => 'عرض',
        'product.store' => 'تخزين',
        'product.destroy' => 'حذف',
        'product.barcode' => 'باركود',
        'product.show' => 'تتبع',
        'product.expire' => 'اضافة هالك',
        'product.expire.show' => 'قائمة الهالك',
        'product.excel.import' => 'استيراد اكسل',
        'product.excel.export' => 'اصدار اكسل',

    ],
      'الضريبة' => [
        'tax.index' => 'عرض',
        'tax.store' => 'إضافة',
        'tax.update' => 'تحديث',
        'tax.active' => 'تفعيل'
    ],
    'وحدات القياس' => [
        'unit.update' => 'تحديث',
        'unit.index' => 'عرض',
        'unit.store' => 'تخزين',
        'unit.destroy' => 'حذف',
    ],
      'أمر توريد' => [
        'import41.index' => 'عرض',
        'import41.update' => 'موافقة',
        'import41.store' => 'رفض',
    ],
     'أمر مرتجع' => [
        'export71.index' => 'عرض',
    ],
     'أمر تحويل بين الفروع' => [
        'transfer.view.all' => 'عرض',
'transfer.view'=>'طباعة',
        'transfer.create' => 'انشاء',
        'transfer.edit' => 'تحديث',
        'transfer.delete' => 'حذف',
        'transfer.accept' => 'موافقة علي تحويل',
    ],
    'التسويات' => [
        'InventoryAdjustment.view.all' => 'عرض',
'InventoryAdjustment.view'=>'طباعة',
        'InventoryAdjustment.create' => 'انشاء',
        'InventoryAdjustment.edit' => 'تحديث',
        'InventoryAdjustment.destroy' => 'حذف',
                'InventoryAdjustment.show' => 'عرض التسوية',
        'InventoryAdjustment.end' => 'انهاء تسوية',
        'InventoryAdjustment.accept' => 'اعتماد تسوية',
    ],
      'أمر صرف' => [
        'export32.index' => 'عرض',
         'export32.store' => 'انشاء',
        'export32.update' => 'تحديث',
        'export32.destroy' => 'حذف',
        'export32.show' => 'قائمة المنتجات داخل المستودعات',


    ],
    'المخازن'=> [
        'store.index' => 'عرض',
         'store.store' => 'انشاء',
        'store.update' => 'تحديث',
        'store.destroy' => 'حذف',
    ],
     'الفواتير'=> [
        'transectionseller.index' => ' عرض تحويلات المناديب',
        'transectionseller.approve' => 'موافقة علي تحويلات المناديب',
        'order4.index' => 'المبيعات',
        'order7.index' => 'مرتجعات',
        'order12.index' => 'مشتريات',
        'order24.index' => 'مردودد مشتريات',
        'installment.index' => 'التحصيلات',
        'reservation.index' => 'الحجوزات',
        'stock.index' => 'الرحلات',
    ],
    'إنشاء مبيعات'=> [
        'pos4.index' => 'المبيعات',
        'pos7.index' => 'مرتجعات',
        'pos12.index' => 'مشتريات',
        'pos24.index' => 'مردودد مشتريات',
        'pos1.index' => 'كاشير',

    ],
      'الأِشعارات'=> [
        'notification.index' => 'الأشعارات',
        'notification4.index' => 'المبيعات',
        'notification7.index' => 'مرتجعات',
        'notification13.index' => 'تحصيلات',
        'notification500.index' => 'تحويلات',
        'notification41.index' => 'امر توريد',
        'notification71.index' => 'امر صرف',
    ],
      'قسم التقارير المالية'=> [
        'expense2.index' => 'تقرير  الأصول الثابتة',
        'expense100.index' => 'تقرير سندات الصرف',
        'expense200.index' => 'تقرير سندات القبض',
        'transfer.index' => 'تقرير قيود يدوية',
        'mizania.report' => 'تقرير ميزانية عمومية ',
        'tadfk.report' => 'تقرير التدفقات النقدية',
        'mizan.report' => 'تقرير ميزان المراجعة ',
        'kamtdakhl.report' => 'تقرير قائمة الدخل ',
        'yearscustomer.report' => 'تقرير أعمار ديون العملاء',
        'yearssupplier.report' => 'تقرير أعمار ديون الموردين',
        'costcenter.report' => 'تقرير مراكز التكلفة',
    ],
      'قسم الأصول الثابتة '=> [
        'assets.index' => 'جميع  الأصول الثابتة',
        'asset.koyod' => 'قيود الأصل الثابت',
        'asset.details' => 'تفاصيل الأصل الثابت',
        'expense2.store' => 'إضافة أصل ثابت',
        'asset.ehlak' => 'إهلاك أصل ثابت',
        'asset.addsayan' => 'إضافة صيانة أصل ثابت',
        'asset.showsayana' => 'عرض صيانة  أصل ثابت',
        'asset.ehlaktam' => 'إهلاك أصل تام',
        'asset.sale' => 'بيع أصل ثابت',
    ],
    
    'مراكز التكلفة' => [
        'costcenter.update' => 'تحديث',
        'costcenter.index' => 'عرض',
        'costcenter.store' => 'تخزين',
        'costcenter.active'=>'تفعيل',
        'costcenter.showindx' => 'مراكز فرعية',
    ],
     'شجرة الحسابات'=> [
        'account.index' => 'عرض',
        'account.store' => 'انشاء',
        'account.update' => 'تحديث',
        'account.listone' => 'عرض فرعية',
        'account.storeone' => 'اضافة فرعية',
    ],
      'القيود المحاسبية'=> [
        'transection.listkoyod' => 'عرض',
    ] ,
     'ارصدة إفتتاحية'=> [
        'start.index' => 'عرض',
        'start.store' => 'انشاء',
    ],
      'سندات الصرف'=> [
        'expense100.store' => 'انشاء',
    ],
      'سندات قيبض'=> [
        'expense200.store' => 'انشاء',
    ],
      'تحويل بين الحسابات'=> [
        'transfer.store' => 'انشاء',
    ],
      'كشف الحساب'=> [
        'transection.list' => 'عرض',
    ]
    ,
      'لوحة التحكم'=> [
        'dashboard.list' => 'عرض',
    ]
    
    
    
];

                        @endphp

                        @foreach($permissions as $category => $perms)
                            @php
                                $groupSlug = Str::slug($category);
                                $allChecked = count(array_intersect(array_keys($perms), $storedPermissions)) === count($perms);
                            @endphp
                            
                            <div class="mb-2">
                                <label class="font-weight-bold">{{ $category }}</label>
                                <input type="checkbox" class="check-all" data-group="{{ $groupSlug }}" {{ $allChecked ? 'checked' : '' }}>
                            </div>

                            <div class="row">
                                @foreach($perms as $key => $label)
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}" 
                                                   class="form-check-input {{ $groupSlug }}"
                                                   {{ in_array($key, $storedPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">تحديث</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endforeach
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function () {
        function updateCheckAll(group) {
            let checkboxes = document.querySelectorAll("." + group);
            let checkAllBox = document.querySelector("[data-group='" + group + "']");
            checkAllBox.checked = [...checkboxes].every(checkbox => checkbox.checked);
        }

        document.querySelectorAll(".check-all").forEach(function (checkAllBox) {
            let group = checkAllBox.getAttribute("data-group");
            
            // تحقق عند التحميل
            updateCheckAll(group);

            // عند تغيير تحديد "تحديد الكل"
            checkAllBox.addEventListener("change", function () {
                document.querySelectorAll("." + group).forEach(function (checkbox) {
                    checkbox.checked = checkAllBox.checked;
                });
            });
        });

        // عند تغيير أي خيار، تحقق مما إذا كان "تحديد الكل" يحتاج إلى التحديث
        document.querySelectorAll("input[type='checkbox']").forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                let groups = this.classList;
                groups.forEach(group => {
                    if (group !== "form-check-input") {
                        updateCheckAll(group);
                    }
                });
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        function updateCheckAll(group) {
            let checkboxes = document.querySelectorAll("." + group);
            let checkAllBox = document.querySelector("[data-group='" + group + "']");
            checkAllBox.checked = [...checkboxes].every(checkbox => checkbox.checked);
        }

        document.querySelectorAll(".check-all").forEach(function (checkAllBox) {
            let group = checkAllBox.getAttribute("data-group");

            // عند تغيير "تحديد الكل"
            checkAllBox.addEventListener("change", function () {
                document.querySelectorAll("." + group).forEach(function (checkbox) {
                    checkbox.checked = checkAllBox.checked;
                });
            });
        });

        // عند تغيير أي خيار، تحقق مما إذا كان "تحديد الكل" يحتاج إلى التحديث
        document.querySelectorAll("input[type='checkbox']").forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                let groups = this.classList;
                groups.forEach(group => {
                    if (group !== "form-check-input") {
                        updateCheckAll(group);
                    }
                });
            });
        });
    });
</script>
