@extends('layouts.admin.app')

@section('title', \App\CPU\translate('قائمة سجلات الصيانة'))

@push('css_or_js')
    <style>
        :root {
            --primary-dark: #000; /* لون أزرق داكن */
            --accent: #fff;       /* لون أصفر */
            --light-bg: #fff;
            --secondary-bg: #fff;
            --table-head-bg: var(--primary-dark);
        }

        /* تنسيق عام للصفحة */
        .page-header-title {
            font-size: 2rem;
            font-weight: 300;
            color: var(--primary-dark);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        /* تنسيق البطاقات */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }
        .card-body {
            padding: 1.5rem;
            background-color: var(--light-bg);
        }

        /* تنسيق قسم التصفية */
        .filter-card {
            background-color: var(--light-bg);
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
        }
        .filter-card .form-label {
            font-weight: 300;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
        }
        .filter-card select,
        .filter-card input[type="date"] {
            border-radius: 4px;
        }
        .filter-btns .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
   
        .table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            text-align: center;
            word-wrap: break-word;
        }
        .table tbody tr:hover {
        }

        /* أزرار التحكم */
        .back-btn {
            background-color: var(--primary-dark);
            border: 1px solid var(--primary-dark);
            color: #fff;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: rgba(0,0,51,0.8);
            border-color: rgba(0,0,51,0.8);
        }
        .print-btn {
            background-color: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .print-btn:hover {
        }

        /* تنسيق الطباعة على ورقة A4 */
        @media print {
            * {
                -webkit-print-color-adjust: exact;
            }
            body {
                margin: 10mm;
                width: 210mm;
                max-width: 210mm;
                font-size: 7px;
                background-color: #fff;
            }
            .no-print, .filter-card, .print-btn, .filter-btns {
                display: none !important;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            .table, th, td {
                border: 1px solid #333 !important;
                padding: 6px !important;
                text-align: center !important;
            }
            thead {
                display: table-header-group;
            }
            tr { 
                page-break-inside: avoid; 
            }
            h1 {
                text-align: center;
                color: var(--primary-dark);
            }
        }
    </style>
@endpush

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
                <li class="breadcrumb-item active text-primary" aria-current="page">
                    {{ \App\CPU\translate('سجلات صيانة الأصول') }}
                </li>
            </ol>
        </nav>
    </div>
    
    <!-- زر الطباعة (لا يظهر في الطباعة) -->

    
    <!-- قسم التصفية (لا يظهر في الطباعة) -->
    <div class="card filter-card no-print">
        <div class="card-body">
            <form action="{{ route('admin.maintenance_logs.index') }}" method="GET">
                <div class="row">
                    <!-- فلتر حسب الفرع -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('الفرع') }}</label>
                        <select name="branch_id" class="form-control">
                            <option value="">{{ \App\CPU\translate('كل الفروع') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- فلتر حسب الأصل -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('الأصل') }}</label>
                        <select name="asset_id" class="form-control">
                            <option value="">{{ \App\CPU\translate('اختر الأصل') }}</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->asset_name }} ({{ $asset->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- نطاق تاريخ الصيانة -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('من') }}</label>
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('إلى') }}</label>
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                    </div>
                </div>
                <div class="row text-start filter-btns mt-4">

    <div class="col-3">
        <button type="button"
                class="btn btn-primary btn-lg w-100 h-75 d-flex align-items-center justify-content-center"
                onclick="printDiv('printableArea')">
            {{ \App\CPU\translate('طباعة') }}
        </button>
    </div>

    <div class="col-3">
        <button type="submit"
                class="btn btn-success btn-lg w-100 h-75 d-flex align-items-center justify-content-center">
            {{ \App\CPU\translate('بحث') }}
        </button>
    </div>

    <div class="col-3">
        <a onclick="exportTableToExcel('excel-table')"
           class="btn btn-info btn-lg w-100 h-75 d-flex align-items-center justify-content-center"
           data-toggle="tooltip" data-placement="top">
            {{ \App\CPU\translate('إصدار ملف أكسل') }}
        </a>
    </div>

    <div class="col-3">
        <a href="{{ route('admin.depreciation.index') }}"
           class="btn btn-danger btn-lg w-100 h-75 d-flex align-items-center justify-content-center">
            {{ \App\CPU\translate('الغاء') }}
        </a>
    </div>

</div>

            </form>
        </div>
    </div>
    
    <!-- المنطقة القابلة للطباعة -->
    <div id="printableArea">

                <div class="table-responsive">
                    @if($maintenanceLogs->count() > 0)
                    <table class="table" id="excel-table">
                        <thead>
                            <tr>
                                <th>{{ \App\CPU\translate('#') }}</th>
                                <th>{{ \App\CPU\translate('الأصل') }}</th>
                                <th>{{ \App\CPU\translate('تاريخ الصيانة') }}</th>
                                <th>{{ \App\CPU\translate('نوع الصيانة') }}</th>
                                <th>{{ \App\CPU\translate('التكلفة التقديرية') }}</th>
                                <th>{{ \App\CPU\translate('الحالة') }}</th>
                                <th>{{ \App\CPU\translate('الفرع') }}</th>
                                <th>{{ \App\CPU\translate('إنشاء بواسطة') }}</th>
                                <th>{{ \App\CPU\translate('جاري التنفيذ بواسطة') }}</th>
                                <th>{{ \App\CPU\translate('مكتملة بواسطة') }}</th>
                                <th>{{ \App\CPU\translate('العمليات') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maintenanceLogs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->asset->asset_name ?? '-' }}</td>
                                <td>{{ $log->maintenance_date }}</td>
                                <td>
                                    @if($log->maintenance_type == 'preventive')
                                        {{ \App\CPU\translate('وقائية') }}
                                    @elseif($log->maintenance_type == 'emergency')
                                        {{ \App\CPU\translate('طارئة') }}
                                    @else
                                        {{ $log->maintenance_type }}
                                    @endif
                                </td>
                                <td>{{ number_format($log->estimated_cost, 2) }}</td>
                                <td>
                                    @if($log->status == 'scheduled')
                                        {{ \App\CPU\translate('مجدولة') }}
                                    @elseif($log->status == 'in progress')
                                        {{ \App\CPU\translate('جاري التنفيذ') }}
                                    @elseif($log->status == 'completed')
                                        {{ \App\CPU\translate('مكتملة') }}
                                    @else
                                        {{ ucfirst($log->status) }}
                                    @endif
                                </td>
                                <td>{{ $log->branch->name ?? '-' }}</td>
                                <td>{{ $log->add->email ?? '-' }}</td>
                                <td>{{ $log->approve->email ?? '-' }}</td>
                                <td>{{ $log->done->email ?? '-' }}</td>
<td class="position-relative" style="overflow: visible;">
    <div class="dropdown text-center">
        <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" id="dropdownMenu{{ $log->id }}" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-v"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow animated fadeIn"
            aria-labelledby="dropdownMenu{{ $asset->id }}"
            style="z-index: 1050;">

            {{-- تفاصيل --}}
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.maintenance_logs.show', $log->id) }}">
                    {{ \App\CPU\translate('تفاصيل') }}
                </a>
            </li>

            {{-- قيود --}}
            @if($log->status !== 'completed')

            <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.maintenance_logs.edit', $log->id) }}">
                    {{ \App\CPU\translate('تعديل') }}
                </a>
            </li>
            @endif

            {{-- حذف (فقط إذا الحالة مجدولة) --}}
            @if($log->status === 'scheduled')
                <li>
                    <form action="{{ route('admin.maintenance_logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد؟') }}')" style="display: flex; align-items: center;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger bg-transparent border-0">
                            {{ \App\CPU\translate('حذف') }}
                        </button>
                    </form>
                </li>
            @endif

        </ul>
    </div>
</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="text-center">{{ \App\CPU\translate('لا توجد سجلات صيانة مسجلة.') }}</p>
                    @endif
                </div>
                <div class="d-flex justify-content-center">
                    {!! $maintenanceLogs->appends(request()->query())->links() !!}
                </div>
          
    </div>
</div>
@endsection
<!-- ✅ مكتبة xlsx -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- ✅ كود التصدير -->
<script>
    function exportTableToExcel(tableId, filename = 'transactions.xlsx') {
        let table = document.getElementById(tableId);
        let workbook = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
        XLSX.writeFile(workbook, filename);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // التعامل مع تبديل عرض الأعمدة بواسطة checkboxes
    const toggleCols = document.querySelectorAll('.toggle-col');
    toggleCols.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const colClass = this.getAttribute('data-col');
            const cells = document.querySelectorAll(`.${colClass}`);
            if (this.checked) {
                cells.forEach(cell => cell.style.display = '');
            } else {
                cells.forEach(cell => cell.style.display = 'none');
            }
        });
    });
    document.querySelectorAll('.toggle-col').forEach(function(chk) {
        const colClass = chk.getAttribute('data-col');
        const cells = document.querySelectorAll(`.${colClass}`);
        if (!chk.checked) {
            cells.forEach(cell => cell.style.display = 'none');
        }
    });
});

function printDiv(divId) {
    var content = document.getElementById(divId).innerHTML;
    var printWindow = window.open('', '_blank', 'width=800,height=1056');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>{{ \App\CPU\translate('تقرير الأصول') }}</title>
       <style>
                
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                     body {
                            font-family: 'Cairo', Arial, sans-serif;
                            margin: 0;
                            background-color: #f9f9f9;
                            color: #333;
                            direction: rtl;
                        }

                        h1 {
                            text-align: center;
                            color: #003366;
                            font-weight: bold;
                            font-size: 28px;
                            margin-bottom: 20px;
                        }

                        .header-section {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            border-bottom: 2px solid #003366;
                            padding: 10px 0;
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

                        .logo img {
                            max-width: 150px;
                            height: auto;
                        }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    table th, table td {
                        border: 1px solid #ddd;
                        padding: 4px;
                        text-align: left;
                    }
                    table th {
                        background-color: #f2f2f2;
                        font-weight: bold;
                    }
                    .row {
                        display: flex;
                        flex-wrap: wrap;
                        margin-bottom: 10px;
                    }
                    .col-md-3 {
                        flex: 0 0 25%;
                        max-width: 25%;
                        padding: 5px;
                        box-sizing: border-box;
                    }
                    .none{
                        display:none;
                    }
                    strong {
                        font-weight: bold;
                    }
                    input[type="search"][aria-controls="DataTables_Table_0"] {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_0"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_1"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_2"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_3"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_4"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_5"]) {
    display: none;
}
#DataTables_Table_0_info{
        display: none;

}
#DataTables_Table_1_info{
            display: none;

}
#DataTables_Table_2_info{
            display: none;

}
#DataTables_Table_3_info{
            display: none;

}
#DataTables_Table_4_info{
            display: none;

}
#DataTables_Table_5_info{
            display: none;

}
#links{
    display: block;
}
                </style>
        </head>
        <body>
            <h1>{{ \App\CPU\translate('تقرير صيلنة الأصول') }}</h1>
            <div class="header-section">
                        <div class="left">
                            <p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where(["key" => "vat_reg_no"])->first()->value }}</p>
                            <p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where(["key" => "number_tax"])->first()->value }}</p>
                            <p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_email"])->first()->value }}</p>
                        </div>
                        <div class="logo">
                            <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}" alt="شعار المتجر">
                        </div>
                        <div class="right">
                            <p><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_name"])->first()->value }}</p>
                            <p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_address"])->first()->value }}</p>
                            <p><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_phone"])->first()->value }}</p>
                        </div>
                    </div>
                    
            ${content}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}
</script>
