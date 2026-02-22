@extends('layouts.admin.app')

@section('content')
<style>
    body {
        direction: rtl;
        background-color: #f8f9fa;
        color: #343a40;
    }
    .container {
        max-width: 1200px;
    }
    .report-title h2,
    .report-title h4 {
        margin: 0;
        padding: 0;
    }
    .report-title {
        margin-bottom: 30px;
        padding: 10px 20px;
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .header-section {
        margin-bottom: 30px;
        padding: 15px 20px;
        background-color: #fff;
        border-radius: 6px;
    }
    .header-section .info p {
        margin: 4px 0;
    }
    .logo-img {
        max-height: 80px;
    }
    .filter-form .form-group {
        margin-bottom: 0;
    }
    .filter-form label {
        margin-right: 5px;
        font-weight: bold;
    }
    .filter-form .btn-primary {
        margin-top: 10px;
    }
    table {
        width: 100%;
        margin-bottom: 30px;
        background-color: #fff;
    }

    th, td {
        padding: 12px 15px;
        text-align: right;
    }
    thead {
        background-color: #e9ecef;
    }
    tfoot {
        background-color: #f1f3f5;
    }
    .table-title {
        margin: 20px 0 10px;
        font-size: 18px;
        font-weight: bold;
    }
            .no-print { display: block; }
        .print-only { display: none; }
    .toggle-btn {
        font-size: 14px;
        padding: 5px 10px;
        cursor: pointer;
        border: none;
        background-color: #007bff;
        color: #fff;
        border-radius: 4px;
    }
    @media print {
        .no-print { display: none; }
        .print-only { display: block !important; }
        table, th, td {
            border: 1px solid #000;
        }
    }
     td {
                padding-right: 12px;

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
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate(' تقرير التدفقات النقدية ') }}
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


    <div id="printableArea">
                    <div class="header-section print-only">
                <table style="width:100%; border: none;">
                    <tr>
                        <td style="width:33%; text-align: left; border: none;">
                            <p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where("key", "vat_reg_no")->first()->value ?? '' }}</p>
                            <p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where("key", "number_tax")->first()->value ?? '' }}</p>
                            <p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_email")->first()->value ?? '' }}</p>
                        </td>
                        <td style="width:33%; text-align: center; border: none;">
                            <img class="logo-img" src="{{ asset('storage/app/public/shop/' . (\App\Models\BusinessSetting::where("key", "shop_logo")->first()->value ?? '')) }}" alt="شعار المتجر" style="max-width:150px;">
                        </td>
                        <td style="width:33%; text-align: right; border: none;">
                            <p><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_name")->first()->value ?? '' }}</p>
                            <p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_address")->first()->value ?? '' }}</p>
                            <p><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_phone")->first()->value ?? '' }}</p>
                        </td>
                    </tr>
                </table>
                <h2 style="margin-top: 20px;">تقرير التدفقات النقدية </h2>
                @if(isset($startDate) && isset($endDate))
                    <div class="date-range">
                        التقرير من تاريخ: <strong>{{ $startDate }}</strong> إلى تاريخ: <strong>{{ $endDate }}</strong>
                    </div>
                @endif
            </div>
        @php
            function renderAccountRow($account, $level = 0, $parentId = null) {
                $padding =  10;
                $rowClass = $parentId ? "child-of-{$parentId}" : "";
                $rowStyle = $parentId ? "display:none;" : "";
                echo "<tr class='{$rowClass}' style='{$rowStyle}'>";
                echo "<td style='padding-right: {$padding}px;'>" . $account->account;
               
                echo "</td><td>" . number_format($account->aggregated_balance) . "</td></tr>";

                if (!empty($account->children)) {
                    foreach ($account->children as $child) {
                        renderAccountRow($child, $level + 1, $account->id);
                    }
                }
            }
        @endphp
<div id="excel-table">
       @include('admin-views.reports.cashflow.section', [
    'id' => 'operating',
    'title' => 'الأنشطة التشغيلية',
    'total' => number_format($netOperating),
    'partial' => 'admin-views.reports.cashflow.operating'
])

@include('admin-views.reports.cashflow.section', [
    'id' => 'financing',
    'title' => 'الأنشطة التمويلية',
    'total' => number_format($netFinancing),
    'partial' => 'admin-views.reports.cashflow.financing'
])

@include('admin-views.reports.cashflow.section', [
    'id' => 'investment',
    'title' => 'الأنشطة الاستثمارية',
    'total' => number_format($netInvestment),
    'partial' => 'admin-views.reports.cashflow.investment'
])


        <div class="mb-4">

            <table class="table">
                <tbody>
                    <tr>
                        <td><strong>إجمالي التدفقات النقدية</strong></td>
                        <td>{{ number_format($netOperating + $netFinancing + $netInvestment) }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    
</div>
@endsection
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
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
    printWindow.document.write('table, th, td { border: 1px solid #333; }');
    printWindow.document.write('th, td { padding: 10px; text-align: right; }');
    printWindow.document.write('h2, h3 { text-align: center; margin-top: 20px; }');
   printWindow.document.write('.none { display: none; }');
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
    function toggleRow(btn) {
        var currentRow = btn.closest('tr');
        var currentLevel = parseInt(currentRow.getAttribute('data-level'));
        var nextRow = currentRow.nextElementSibling;
        while (nextRow && parseInt(nextRow.getAttribute('data-level')) > currentLevel) {
            nextRow.style.display = (nextRow.style.display === 'none') ? 'table-row' : 'none';
            nextRow = nextRow.nextElementSibling;
        }
        btn.textContent = (btn.textContent.trim() === 'مزيد') ? 'إخفاء' : 'مزيد';
    }
</script>
