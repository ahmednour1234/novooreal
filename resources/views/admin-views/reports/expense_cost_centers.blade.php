@extends('layouts.admin.app')

@section('content')
    <style>

        h2 {
            text-align: center;
            color: #001B63;
            margin-bottom: 20px;
            font-size: 28px;
        }
        .date-range {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }
        .form-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-container form {
            display: inline-block;
            background: #f7faff;
            padding: 20px 30px;
            border-radius: 10px;
            border: 1px solid #c3d0e8;
        }
        .form-container label {
            margin-right: 10px;
            font-weight: 600;
            color: #333;
        }
        .form-container input[type="date"] {
            padding: 8px 12px;
            margin-right: 20px;
            border: 1px solid #bfcadf;
            border-radius: 4px;
            outline: none;
        }
        .form-container button,
        .form-container a.report-btn {
            background: #001B63;
            color: #fff;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .form-container button:hover,
        .form-container a.report-btn:hover {
            background: #003388;
        }
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            margin-bottom: 40px;
        }
        table, th, td {
        }
        table th, table td {
            padding: 14px 20px;
            text-align: start;
        }
        table th {
            color: #fff;
            font-size: 16px;
        }
        table tr:nth-child(even) {
        }
        table tr:hover {
        }
        .toggle-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .no-print { display: block; }
        .print-only { display: none; }
        @media print {
            .no-print { display: none; }
            .print-only { display: block !important; }
            body {
                direction: rtl;
                font-family: 'Arial', sans-serif;
                font-size: 14px;
                margin: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid #333;
            }
            th, td {
                padding: 10px;
                text-align: right;
            }
            h2, h3 {
                text-align: center;
            }
            .header-section {
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #003366;
            }
        }
          .equal-btn {
            min-width: 160px;
            margin: 5px;
        }
    </style>

    <div class="container">
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
                    {{ \App\CPU\translate('مراكز التكلفة') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>
        <!-- عرض الفترة إذا كانت متوفرة -->
        @if(isset($startDate) && isset($endDate))
            <div class="date-range">
                التقرير من تاريخ: <strong>{{ $startDate }}</strong> إلى تاريخ: <strong>{{ $endDate }}</strong>
            </div>
        @endif

     <div class="card p-4 shadow-sm mb-4 no-print" style="background: #fff;">
    <form method="GET"action="{{ url()->current() }}">
        {{-- 🔹 التاريخ من وإلى --}}
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-6">
                <label for="start_date" class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                       value="{{ $startDate }}" required>
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
                <input type="date" name="end_date" id="end_date" class="form-control"
                       value="{{ $endDate }}" required>
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
            <!-- ترويسة النسخة المطبوعة -->
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
                <h2 style="margin-top: 20px;">تقرير مراكز تكلفة</h2>
                @if(isset($startDate) && isset($endDate))
                    <div class="date-range">
                        التقرير من تاريخ: <strong>{{ $startDate }}</strong> إلى تاريخ: <strong>{{ $endDate }}</strong>
                    </div>
                @endif
            </div>

            <div class="table-responsive">
<table class="table" id="excel-table">
    <thead>
        <tr>
                        <th>#</th>

            <th>اسم مركز التكلفة</th>
            <th>الإجمالي</th>
            <th class="none text-start">الإجراءات</th>
        </tr>
    </thead>
    <tbody>
     @php
function renderTree($nodes, $level = 0, &$index = 1) {
    $html = '';
    foreach ($nodes as $node) {
        $rowStyle = ($level > 0) ? 'style="display:none;"' : '';
        $padding = $level * 20;
        $name = isset($node['center_name']) ? $node['center_name'] : (isset($node['account_name']) ? $node['account_name'] : '');
        if ($level > 0) {
            $name .= ' (حساب فرعي)';
        }

        $total = number_format($node['total_expense'] ?? $node['expense'], 2);
        $hasDetails = isset($node['children']) && count($node['children']) > 0;

        $html .= '<tr data-level="' . $level . '" ' . $rowStyle . '>';
        $html .= '<td>' . $index . '</td>'; // ✅ رقم الصف
        $html .= '<td style="padding-left:' . $padding . 'px;">' . $name . '</td>';
        $html .= '<td>' . $total . '</td>';
        $html .= '<td class="none text-start">';

        if (isset($node['center_name']) && isset($node['id'])) {
            $html .= '
                <a class="btn btn-sm btn-white shadow me-1" href="' . url("admin/reports/showCostCenterReport/" . $node['id']) . '" title="عرض تقرير">
                    <i class="tio-visible"></i>
                </a>';
        }

        if ($hasDetails) {
            $html .= '
                <a class="btn btn-sm btn-white shadow" href="javascript:void(0)" onclick="toggleRow(this)" title="خيارات">
                    <i class="tio-more-vertical text-success"></i>
                </a>';
        } elseif (!isset($node['center_name'])) {
            $html .= 'لا يوجد';
        }

        $html .= '</td>';
        $html .= '</tr>';

        $index++;

        if ($hasDetails) {
            $html .= renderTree($node['children'], $level + 1, $index);
        }
    }
    return $html;
}

$startIndex = 1;
echo renderTree($report, 0, $startIndex);
@endphp

    </tbody>
</table>
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
function toggleRow(btn) {
    var currentRow = btn.closest('tr');
    var currentLevel = parseInt(currentRow.getAttribute('data-level'));
    var nextRow = currentRow.nextElementSibling;
    while (nextRow && parseInt(nextRow.getAttribute('data-level')) > currentLevel) {
        nextRow.style.display = (nextRow.style.display === 'none') ? 'table-row' : 'none';
        nextRow = nextRow.nextElementSibling;
    }
}
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
</script>
