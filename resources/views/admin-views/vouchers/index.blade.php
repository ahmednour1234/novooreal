<!-- resources/views/vouchers/index.blade.php -->
@extends('layouts.admin.app')

@php
    // استنتاج النوع من الطلب أو من المتغير القادم من الكنترولر
    $currentType = request('type', isset($type) ? $type : 'payment'); // 'payment' | 'receipt'
    $isReceipt   = ($currentType === 'receipt');

    $pageTitle = \App\CPU\translate($isReceipt ? 'قائمة سندات القبض' : 'قائمة سندات الصرف');
@endphp

@section('title', $pageTitle)

@section('content')
@php
    use App\Models\BusinessSetting;

    // هل في أي فلاتر؟ أو تم طلب عرض الكل؟
    $hasSearch = request()->hasAny(['from_date','to_date','created_by','description','voucher_number']) || request('show') === 'all';

    // إجمالي المبالغ في الصفحة الحالية
    $pageTotal = isset($vouchers) ? $vouchers->sum('amount') : 0;

    // إعدادات المتجر (مرة واحدة)
    $settings = BusinessSetting::whereIn('key', [
        'shop_name','shop_address','shop_phone','shop_email','number_tax','vat_reg_no','shop_logo'
    ])->pluck('value','key');

    $shopName   = $settings['shop_name']   ?? '';
    $shopAddr   = $settings['shop_address']?? '';
    $shopPhone  = $settings['shop_phone']  ?? '';
    $shopEmail  = $settings['shop_email']  ?? '';
    $taxNumber  = $settings['number_tax']  ?? '';
    $vatRegNo   = $settings['vat_reg_no']  ?? '';
    $shopLogo   = $settings['shop_logo']   ?? '';
@endphp

<style>
    .page-wrapper{direction: rtl}
    .card{border-radius:12px}
    .card-header.bg-light{background:#f7f7f9!important}
    .sticky-actions{position:sticky; top:0; z-index:5; background:#fff; padding:10px 0}
    .table thead th{position:sticky; top:0; background:#f0f4ff; z-index:2}
    .badge-filter{font-size:12px}
    .amt-payment{color:#ef4444;font-weight:700}  /* أحمر للصرف */
    .amt-receipt{color:#16a34a;font-weight:700}  /* أخضر للقبض */
    @media print{
        .non-printable{display:none!important}
        body{direction: rtl}
    }
</style>

<div class="container-fluid page-wrapper">

    <!-- 🧭 Breadcrumb -->
    <div class="row align-items-center mb-3">
        <div class="col-sm">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">
                        {{ \App\CPU\translate($isReceipt ? 'سندات القبض' : 'سندات الصرف') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 🔍 فلتر البحث -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('admin.vouchers.index', ['type' => $currentType]) }}"
                  class="row g-3 non-printable"
                  id="filtersForm">
                <input type="hidden" name="type" value="{{ $currentType }}"/>

                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" id="from_date">
                </div>

                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" id="to_date">
                </div>

                <div class="col-md-3">
                    <label class="form-label">البريد الإلكتروني للكاتب</label>
                    <input type="email" name="created_by" class="form-control" placeholder="example@email.com" value="{{ request('created_by') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">الوصف</label>
                    <input type="text" name="description" class="form-control" placeholder="بحث في الوصف..." value="{{ request('description') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">رقم السند</label>
                    <input type="text" name="voucher_number" class="form-control" placeholder="مثال: 10023" value="{{ request('voucher_number') }}">
                </div>

                <div class="col-12 d-flex flex-wrap mt-2" style="gap: 15px; padding: 8px;">
                    <button class="btn btn-primary" style="min-width: 140px;">
                        {{ \App\CPU\translate('تطبيق البحث') }}
                    </button>

                    <a href="{{ route('admin.vouchers.index', array_merge(request()->except('page'), ['type' => $currentType, 'show' => 'all'])) }}"
                       class="btn btn-secondary"
                       style="min-width: 140px;"
                       title="عرض كل السندات بدون فلاتر">
                        عرض الكل
                    </a>

                    <a href="{{ route('admin.vouchers.index', ['type' => $currentType]) }}"
                       class="btn btn-danger border"
                       style="min-width: 140px;">
                        {{ \App\CPU\translate('الغاء') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($hasSearch)
        <!-- 🎛 أزرار الإجراءات أعلى الجدول -->
        <div class="sticky-actions non-printable mb-2" style="padding: 12px;">
            <div class="d-flex align-items-start">
                <button class="btn btn-sm btn-primary shadow" style="min-width: 120px;" onclick="printAllTable()">
                    {{ \App\CPU\translate('طباعة') }}
                </button>
                <button class="btn btn-sm btn-info shadow" style="min-width: 120px; margin-right: 15px;" onclick="exportTableToExcel('expenseTable')">
                    {{ \App\CPU\translate('إصدار ملف أكسل') }}
                </button>
            </div>
        </div>

        <!-- 📄 جدول البيانات -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="expenseTable" class="table align-middle mb-0">
                        <thead>
                        <tr>
                                                        <th>رقم التسلسل</th>

                            <th>رقم السند</th>
                            <th>التاريخ</th>
                            <th>الحساب الدائن</th>
                            <th>الحساب المدين</th>
                            <th>المبلغ</th>
                            <th>{{ $isReceipt ? 'طريقة القبض' : 'طريقة الدفع' }}</th>
                            <th>الوصف</th>
                            <th>الكاتب</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($vouchers as $voucher)
                            @php
                                $pm = strtolower($voucher->payment_method ?? '');
                                $pmText = match($pm){
                                    'cash' => 'نقدًا',
                                    'bank' => 'تحويل بنكي',
                                    'check','cheque' => 'شيك',
                                    'card' => 'بطاقة',
                                    default => $voucher->payment_method
                                };
                            @endphp
                            <tr>
                                   <td>
                                         <a href="{{ route('admin.vouchers.show', $voucher->id) }}"
                                       class="text-decoration-none fw-bold">
                                        {{ $voucher->id }}
                                                                            </a>

                                    
                                </td>
                                <td>
                                    <a href="{{ route('admin.vouchers.show', $voucher->id) }}"
                                       class="text-decoration-none fw-bold">
                                        {{ $voucher->voucher_number }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($voucher->date)->format('Y-m-d') }}</td>
                                <td>{{ $voucher->creditAccount->account ?? '-' }}</td>
                                <td>{{ $voucher->debitAccount->account ?? '-' }}</td>
                                <td class="{{ $isReceipt ? 'amt-receipt' : 'amt-payment' }}">
                                    {{ number_format($voucher->amount, 2) }}
                                </td>
                                <td>{{ $pmText }}</td>
                                <td class="text-truncate" style="max-width: 420px" title="{{ $voucher->description }}">{{ $voucher->description }}</td>
                                <td>{{ $voucher->creator->email ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                        </tbody>

                        @if(($vouchers->count() ?? 0) > 0)
                            <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">إجمالي المبالغ</th>
                                <th class="{{ $isReceipt ? 'amt-receipt' : 'amt-payment' }}">
                                    {{ number_format($pageTotal, 2) }}
                                </th>
                                <th colspan="3"></th>
                            </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if($vouchers->hasPages())
                <div class="card-footer">
                    {{ $vouchers->appends(array_merge(request()->except('page'), ['type' => $currentType]))->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

<!-- ✅ مكتبة xlsx -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>

<script>
    // تأكيد نطاق التاريخ (Client-side)
    document.addEventListener('DOMContentLoaded', function () {
        const from = document.getElementById('from_date');
        const to   = document.getElementById('to_date');

        function validateRange() {
            if (from.value && to.value && from.value > to.value) {
                toastr?.error('تاريخ البداية يجب أن يكون أقل من تاريخ النهاية', 'خطأ', { CloseButton:true, ProgressBar:true });
                to.value = '';
            }
        }
        from?.addEventListener('change', validateRange);
        to?.addEventListener('change', validateRange);
    });

    // ✅ تصدير الجدول إلى Excel
    function exportTableToExcel(tableId, filename) {
        const table = document.getElementById(tableId);
        if(!table){ return; }
        const currentType = new URLSearchParams(window.location.search).get('type') || '{{ $currentType }}';
        const safeName = currentType === 'receipt' ? 'vouchers_receipt' : 'vouchers_payment';
        const wb = XLSX.utils.table_to_book(table, {sheet: "Vouchers"});
        XLSX.writeFile(wb, (filename || (safeName + '.xlsx')));
    }

    // ✅ طباعة أنيقة مع رأس معلومات المتجر
    function printAllTable() {
        const table = document.getElementById('expenseTable');
        if(!table){ return; }

        const win = window.open('', '_blank');
        win.document.write('<html><head><title>Print</title>');

        win.document.write(`<style>
            body{direction:rtl;font-family:'Cairo',Arial,sans-serif;background:#f4f6fa;color:#333;padding:24px}
            .header{display:flex;gap:16px;align-items:center;justify-content:space-between;border-bottom:2px solid #e5e7ef;padding-bottom:12px;margin-bottom:16px}
            .header .col{width:33%}
            .logo{text-align:center}
            .logo img{max-width:140px;height:auto}
            h2{text-align:center;margin:14px 0 6px 0}
            .muted{text-align:center;color:#666;margin-bottom:10px}
            table{width:100%;border-collapse:collapse;background:#fff}
            th,td{border:1px solid #e6e9f2;padding:10px 12px;text-align:center;font-size:13px}
            thead th{background:#eef3ff}
            tfoot th{background:#f7fafc}
            @page{margin:10mm}
            @media print{.non-printable{display:none!important}}
        </style>`);

        win.document.write('</head><body>');

        const now = new Date().toLocaleString('ar-EG', { hour12:false });
        const logoUrl = `{{ asset('storage/app/public/shop/' . $shopLogo) }}`;
        const title = `{{ $isReceipt ? 'تقرير سندات القبض' : 'تقرير سندات الصرف' }}`;

        win.document.write(`
            <div class="header">
                <div class="col">
                    <div><strong>رقم السجل التجاري:</strong> {{ $vatRegNo }}</div>
                    <div><strong>الرقم الضريبي:</strong> {{ $taxNumber }}</div>
                    <div><strong>البريد الإلكتروني:</strong> {{ $shopEmail }}</div>
                </div>
                <div class="logo">
                    <img src="${logoUrl}" alt="Logo">
                </div>
                <div class="col" style="text-align:left">
                    <div><strong>اسم المتجر:</strong> {{ $shopName }}</div>
                    <div><strong>العنوان:</strong> {{ $shopAddr }}</div>
                    <div><strong>رقم الجوال:</strong> {{ $shopPhone }}</div>
                </div>
            </div>

            <h2>${title}</h2>
            <div class="muted"><strong>تاريخ الطباعة:</strong> ${now}</div>
        `);

        win.document.write(table.outerHTML);

        win.document.write('</body></html>');
        win.document.close();
        win.focus();
        win.print();
    }
</script>
