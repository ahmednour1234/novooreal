@extends('layouts.admin.app')

@section('content')
<style>
    /* Global Styles */
    .balance-table {
        width: 100%;
        border-collapse: collapse;
    }
    .balance-table th,
    .balance-table td {
        padding: 12px 8px;
        text-align: right;
        font-size: 14px;
    }
   

    .header-controls {
        margin-bottom: 20px;
    }
    .date-range-inputs input {
        max-width: 150px;
    }
    .print-button {
        background-color: #28a745;
        border-color: #28a745;
        color: #fff;
    }
    .print-button:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
    
    @media print {
        .no-print { display: none; }
        body { direction: rtl; font-family: Arial, sans-serif; }
    }
        /* Elements visible on screen only */
    .no-print {
        display: block;
    }
    /* Elements visible on print only */
    .print-only {
        display: none;
    }
    /* Print specific styles */
    @media print {
        .no-print {
            display: none;
        }
        .print-only {
            display: block !important;
        }
           .noprint{
            display: none;
        }
        body {
            direction: rtl;
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            margin: 20px;
        }
        table {
            width: 100%;
        }
        table, th, td {
        }
        th, td {
            padding: 10px;
            text-align: right;
        }
        h2, h3 {
            text-align: center;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #003366;
            padding-bottom: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .header-section .left,
        .header-section .right,
        .header-section .logo {
            width: 32%;
            text-align: center;
        }
        .header-section p {
            margin: 5px 0;
            line-height: 1.6;
            font-size: 16px;
        }
        .logo-img {
            max-width: 150px;
            height: auto;
        }
        .noprint{
            display: none;
        }
    }
      .equal-btn {
            min-width: 160px;
            margin: 5px;
        }
</style>

<div class="container my-4">
            <div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                    <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('admin.expenseCostCentersReport')}}" class="text-primary">
                    {{ \App\CPU\translate('الميزانية العمومية') }}
                </a>
            </li>
             
        </ol>
    </nav>
</div>
               <div class="card p-4 shadow-sm mb-4 no-print" style="background: #fff;">
    <form method="GET"action="{{ url()->current() }}">
        {{-- 🔹 التاريخ من وإلى --}}
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-6">
                <label for="start_date" class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                        value="{{ request('start_date') }}" required>
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
                <input type="date" name="end_date" id="end_date" class="form-control"
                       value="{{ request('end_date') }}" required>
            </div>
        </div>

        {{-- 🔹 الأزرار الموحدة --}}
<div class="row mt-4">
    <div class="col-md-3 mb-2">
        <button type="button" onclick="printDiv('printableArea')" class="btn btn-primary w-100">
            {{ \App\CPU\translate('طباعة') }}
        </button>
    </div>
    <div class="col-md-3 mb-2">
        <button type="submit" class="btn btn-success w-100">
            {{ \App\CPU\translate('بحث') }}
        </button>
    </div>
    <div class="col-md-3 mb-2">
        <a onclick="exportTableToExcel('excel-table')" class="btn btn-info w-100">
            {{ \App\CPU\translate('إصدار ملف أكسل') }}
        </a>
    </div>
    <div class="col-md-3 mb-2">
        <a href="{{ url()->current() }}" class="btn btn-danger w-100">
            {{ \App\CPU\translate('إلغاء') }}
        </a>
    </div>
</div>
    </form>
</div>

@php
    // دالة تجميع رصيد شجري
    function aggBalance($acc) {
        $sum = $acc->aggregated_balance; // أو $acc->balance إذا هذا هو الحقل الصحيح
        foreach ($acc->children ?? [] as $child) {
            $sum += aggBalance($child);
        }
        return $sum;
    }

    // نجمع الحسابات حسب النوع
    $grouped = $balanceSheet->groupBy('account_type');

    // قائمة الأنواع بالعربي
    $names = [
        'asset'     => 'أصول',
        'liability' => 'الالتزامات',
        'equity'    => 'حقوق الملكية',
    ];

    // نهيئ مصفوفة الإجماليات
    $totals = [
        'asset'     => 0,
        'liability' => 0,
        'equity'    => 0,
    ];

    // نحسب الإجماليات بأمان بالاعتماد على get مع default
    foreach (array_keys($totals) as $type) {
        /** @var \Illuminate\Support\Collection $section */
        $section = $grouped->get($type, collect());
        $totals[$type] = $section->sum(fn($sec) => aggBalance($sec));
    }

    // نقرأ المتغيرات للاستخدام في الـ Blade
    $assetsTotal             = $totals['asset'];
    $liabilitiesTotal        = $totals['liability'];
    $equityTotal             = $totals['equity'];
    $equityLiabilitiesSum    = $liabilitiesTotal + $equityTotal;
@endphp


    <div id="printableArea">
   

@php
    use App\Models\BusinessSetting;
    function settings($key) {
        return optional(BusinessSetting::where('key', $key)->first())->value ?? '';
    }
@endphp

        <!-- Print-Only Business Header -->
        <div class="header-section print-only">
            <table class="w-100 border-none">
                <tr>
                    <td class="text-start border-none" style="width:33%;">
                        <p><strong>السجل التجاري:</strong> {{ settings('vat_reg_no') }}</p>
                        <p><strong>الرقم الضريبي:</strong> {{ settings('number_tax') }}</p>
                        <p><strong>البريد الإلكتروني:</strong> {{ settings('shop_email') }}</p>
                    </td>
                    <td class="text-center border-none" style="width:33%;">
                        <img src="{{ asset('storage/shop/' . settings('shop_logo')) }}" style="height:60px;" alt="شعار المؤسسة">
                    </td>
                    <td class="text-end border-none" style="width:33%;">
                        <p><strong>اسم المؤسسة:</strong> {{ settings('shop_name') }}</p>
                        <p><strong>العنوان:</strong> {{ settings('shop_address') }}</p>
                        <p><strong>الهاتف:</strong> {{ settings('shop_phone') }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Print-Only Report Header -->
        <div class="print-only text-center mb-4" style="font-size:24px; font-weight:bold;">
            الميزانية العمومية :            <small class="text-muted">من: {{ request('date_from') ?? '----' }} إلى: {{ request('date_to') ?? '----' }}</small>

        </div>

<table class="table balance-table"id="excel-table">
    <thead>
        <tr>
            <th>نوع الحساب</th>
            <th>الحساب</th>
            <th>الرصيد</th>
            <th class="no-print">الإجراء</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grouped as $type => $sections)
            <tr class="table-secondary">
                <td colspan="4"><strong>{{ $names[$type] ?? $type }}</strong></td>
            </tr>
            @foreach($sections as $sec)
                @php $bal = aggBalance($sec); @endphp
                <tr>
                    <td>{{ $names[$type] ?? $type }}</td>
                    <td>{{ $sec->account }}</td>
                    <td>{{ number_format($bal) }}</td>
                    <td class="no-print">
                        @if(count($sec->children ?? []) > 0)
                            <button class="btn btn-sm btn-white toggle-table btn-white" data-target="child-table-{{ $sec->id }}">
                                <i class="tio-chevron-down text-dark"></i> 
                            </button>
                        @endif
                    </td>
                </tr>

                @if(count($sec->children ?? []) > 0)
                    <tr id="child-table-{{ $sec->id }}" class="child-table-row" style="display:none;">
                        <td colspan="4">
                            <table class="table  table-sm mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>الحساب الفرعي</th>
                                        <th>الرصيد</th>
                                        <th class="no-print">الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sec->children as $child)
                                        @php $cbal = aggBalance($child); @endphp
                                        <tr>
                                            <td class="ps-3">{{ $child->account }}</td>
                                            <td>{{ number_format($cbal) }}</td>
                                            <td class="no-print">
                                                @if(count($child->children ?? []) > 0)
                                                    <button class="btn btn-sm btn-white  toggle-table" data-target="grandchild-table-{{ $child->id }}">
                                                        <i class="tio-chevron-down text-dark"></i>  
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

                                        @if(count($child->children ?? []) > 0)
                                            <tr id="grandchild-table-{{ $child->id }}" class="child-table-row" style="display:none;">
                                                <td colspan="3">
                                                    <table class="table table-sm mb-0">
                                                        <thead class="bg-white">
                                                            <tr>
                                                                <th>الحساب الفرعي الثاني</th>
                                                                <th>الرصيد</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($child->children as $gc)
                                                                <tr>
                                                                    <td class="ps-5">{{ $gc->account }}</td>
                                                                    <td>{{ number_format(aggBalance($gc)) }}</td>
                                                                    <td></td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endforeach
    </tbody>

    <tfoot>
        <tr class="table-light">
            <td colspan="2"><strong>الإجمالي الكلي للأصول</strong></td>
            <td><strong>{{ number_format($assetsTotal) }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
            @if($assetsTotal != $equityLiabilitiesSum)
    <div class="alert  d-flex align-items-center">
        <strong class="text-danger mr-2">❗️</strong>
        <span>
            <strong>خطأ في التقرير:</strong>
            مجموع الأصول ({{ number_format($assetsTotal) }}) لا يساوي مجموع الالتزامات وحقوق الملكية ({{ number_format($equityLiabilitiesSum) }}).
        </span>
    </div>
@endif
    </div>
    
    </div>
    </div>
</div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- ✅ كود التصدير -->
<script>
    function exportTableToExcel(tableId, filename = 'transactions.xlsx') {
        let table = document.getElementById(tableId);
        let workbook = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
        XLSX.writeFile(workbook, filename);
    }
</script>
<script>
    function printDiv(divId) {
    var content = document.getElementById(divId).innerHTML;
    var printWindow = window.open('', '', 'height=700,width=900');
    printWindow.document.write('<html><head><title>طباعة التقرير</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('table, th, td { border: 0.2px solid #333; }');
    printWindow.document.write('th, td { padding: 10px; text-align: right; }');
    printWindow.document.write('h2, h3 { text-align: center; margin-top: 20px; }');
        printWindow.document.write('.noprint { display: none; }');
    printWindow.document.write('.header-section { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #003366; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

   
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-table').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = document.getElementById(this.dataset.target);
            if (target) {
                target.style.display = (target.style.display === 'none' || target.style.display === '') ? 'table-row' : 'none';
            }
        });
    });
});
</script>

