@extends('layouts.admin.app')

@section('title', \App\CPU\translate('عرض جميع الأصول'))

@push('css_or_js')

<style>
        :root {
            --primary-dark: black; /* اللون الأساسي الداكن */
            --accent: #ffffff;       /* اللون التبايني */
            --light-bg: #ffffff;
            --secondary-bg: #ffffff;
            --table-head-bg: var(--primary-dark);
        }
        /* تنسيق عام للصفحة */
        .page-header-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
        }
        /* تنسيق البطاقات */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        /* قسم التصفية */
        .filter-card {
            background-color: var(--light-bg);
            border: 1px solid var(--accent);
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .filter-card .form-label {
            font-weight: 300;
            margin-bottom: 0.4rem;
        }
        /* تنسيق الجدول */
        .table thead th {
            font-size: 0.9rem;
            font-weight: 300;
            text-align: center;
        }
        .table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            text-align: center;
            background: white;
        }
        .table tbody tr:hover {
        }
        /* قسم تبديل عرض الأعمدة */
        .columns-toggle {
            background-color: #ffffff;
            border-radius: 6px;
        }
        .columns-toggle label {
            font-weight: 300;
            margin-right: 0.8rem;
            font-size: 0.85rem;
        }
        /* زر الطباعة */
        .print-btn {
            background-color: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s, border-color 0.3s;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .print-btn:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        /* أنماط الطباعة (A4) */
        @media print {
            * {
                -webkit-print-color-adjust: exact;
            }
            body {
                margin: 10mm;
                width: 210mm;
                max-width: 210mm;
                font-size: 12px;
                background-color: #ffffff;
            }
            .no-print,
            .filter-card,
            .columns-toggle,
            .print-btn,
            .pagination {
                display: none !important;
            }
            .print-only {
                display: block !important;
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
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { page-break-inside: avoid; }
            h1 {
                text-align: center;
                color: var(--primary-dark);
            }
        }
        /* يمنع القائمة من أن تختفي داخل scroll */
.table-responsive {
    overflow: visible !important;
    position: relative;
}

    </style>@endpush
<!-- Bootstrap CSS (v5 مثال) -->


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
                    {{ \App\CPU\translate(' الأصول الثابتة') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>
    <!-- زر الطباعة (غير معروض عند الطباعة) -->
  
    
    <!-- قسم التصفية (غير معروض عند الطباعة) -->
    <div class="card filter-card no-print">
        <div class="card-body">
            <form action="{{ route('admin.depreciation.index') }}" method="GET">
                <div class="row">
                    <!-- تصفية حسب الفرع -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('الفرع') }}</label>
                        <select name="branch_id" class="form-control">
                            <option value="">{{ \App\CPU\translate('كل الفروع') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- تحديد نوع التاريخ للتصفية -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">{{ \App\CPU\translate('تحديد التاريخ') }}</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="filter_dates[]" value="purchase_date" id="filterPurchase"
                                {{ is_array(request('filter_dates')) && in_array('purchase_date', request('filter_dates')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="filterPurchase">{{ \App\CPU\translate('تاريخ الشراء') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="filter_dates[]" value="commencement_date" id="filterCommencement"
                                {{ is_array(request('filter_dates')) && in_array('commencement_date', request('filter_dates')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="filterCommencement">{{ \App\CPU\translate('تاريخ التشغيل') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="filter_dates[]" value="created_at" id="filterCreated"
                                {{ is_array(request('filter_dates')) && in_array('created_at', request('filter_dates')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="filterCreated">{{ \App\CPU\translate('تاريخ الإنشاء') }}</label>
                        </div>
                    </div>
                    <!-- نطاق التاريخ -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('من') }}</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('إلى') }}</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
       <div class="row text-start filter-btns mt-4">

    <div class="col-3">
        <button type="button"
                class="btn btn-primary btn-lg w-100 h-75 d-flex align-items-center justify-content-center"
                onclick="printTable()">
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
    <!-- نهاية قسم التصفية -->

    <!-- قسم تبديل عرض الأعمدة (غير معروض عند الطباعة) -->
    <div class="card mb-4 shadow-sm no-print">
        <div class="card-body columns-toggle">
            <label>
                <input type="checkbox" class="toggle-col" data-col="purchase_price-col"> {{ \App\CPU\translate('سعر الشراء') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="additional_costs-col"> {{ \App\CPU\translate('التكاليف الإضافية') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="total_cost-col"> {{ \App\CPU\translate('التكلفة الإجمالية') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="salvage_value-col"> {{ \App\CPU\translate('القيمة المتبقية') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="useful_life-col"> {{ \App\CPU\translate('العمر الافتراضي') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="depreciation_method-col"> {{ \App\CPU\translate('طريقة الاهلاك') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="accumulated_depreciation-col"> {{ \App\CPU\translate('الاستهلاك المتراكم') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="book_value-col"> {{ \App\CPU\translate('القيمة الدفترية') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="status-col"> {{ \App\CPU\translate('الحالة') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="purchase_date-col"> {{ \App\CPU\translate('تاريخ الشراء') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="commencement_date-col"> {{ \App\CPU\translate('تاريخ بدء التشغيل') }}
            </label>
            <label>
                <input type="checkbox" class="toggle-col" data-col="additional_details-col"> {{ \App\CPU\translate('تفاصيل إضافية') }}
            </label>
        </div>
    </div>
    
    <!-- المنطقة القابلة للطباعة -->
<div  id="product-table">
        <!-- قسم عرض الأصول -->
                <div class="table-responsive">
                    <table style="background-color: #EDF2F4; color:black;" id="excel-table" class="table  table-striped">
                        <thead >
                            <tr>
                                <th>{{ \App\CPU\translate('رقم') }}</th>
                                <th>{{ \App\CPU\translate('اسم الأصل') }}</th>
                                <th>{{ \App\CPU\translate('الكود') }}</th>
                                <th>{{ \App\CPU\translate('الفرع') }}</th>
                                <th class="purchase_price-col" style="display: none;">{{ \App\CPU\translate('سعر الشراء') }}</th>
                                <th class="additional_costs-col" style="display: none;">{{ \App\CPU\translate('التكاليف الإضافية') }}</th>
                                <th class="total_cost-col" style="display: none;">{{ \App\CPU\translate('التكلفة الإجمالية') }}</th>
                                <th class="salvage_value-col" style="display: none;">{{ \App\CPU\translate('القيمة المتبقية') }}</th>
                                <th class="useful_life-col" style="display: none;">{{ \App\CPU\translate('العمر الافتراضي') }}</th>
                                <th class="depreciation_method-col" style="display: none;">{{ \App\CPU\translate('طريقة الاهلاك') }}</th>
                                <th class="accumulated_depreciation-col" style="display: none;">{{ \App\CPU\translate('الاستهلاك المتراكم') }}</th>
                                <th class="book_value-col" style="display: none;">{{ \App\CPU\translate('القيمة الدفترية') }}</th>
                                <th class="status-col" style="display: none;">{{ \App\CPU\translate('الحالة') }}</th>
                                <th class="purchase_date-col" style="display: none;">{{ \App\CPU\translate('تاريخ الشراء') }}</th>
                                <th class="commencement_date-col" style="display: none;">{{ \App\CPU\translate('تاريخ بدء التشغيل') }}</th>
                                <th class="additional_details-col" style="display: none;">{{ \App\CPU\translate('تفاصيل إضافية') }}</th>
                                <th class="none">{{ \App\CPU\translate('الإجراءات') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assets as $asset)
                            <tr>
                                <td>{{ $asset->id }}</td>
                                <td>{{ $asset->asset_name }}</td>
                                <td>{{ $asset->code }}</td>
                                <td>{{ $asset->branch->name ?? '-' }}</td>
                                <td class="purchase_price-col" style="display: none;">{{ number_format($asset->purchase_price, 2) }}</td>
                                <td class="additional_costs-col" style="display: none;">{{ number_format($asset->additional_costs, 2) }}</td>
                                <td class="total_cost-col" style="display: none;">{{ number_format($asset->total_cost, 2) }}</td>
                                <td class="salvage_value-col" style="display: none;">{{ number_format($asset->salvage_value, 2) }}</td>
                                <td class="useful_life-col" style="display: none;">{{ $asset->useful_life }} {{ \App\CPU\translate('سنوات') }}</td>
                                <td class="depreciation_method-col" style="display: none;">
                                    @switch($asset->depreciation_method)
                                        @case('straight_line')
                                            {{ \App\CPU\translate('القسط الثابت') }}
                                            @break
                                        @case('declining_balance')
                                            {{ \App\CPU\translate('الرصيد المتناقص') }}
                                            @break
                                        @case('units_of_production')
                                            {{ \App\CPU\translate('الإنتاج/الاستخدام') }}
                                            @break
                                            
                                        @default
                                            {{ $asset->depreciation_method }}
                                    @endswitch
                                </td>
                                <td class="accumulated_depreciation-col" style="display: none;">{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                                <td class="book_value-col" style="display: none;">{{ number_format($asset->book_value, 2) }}</td>
                                <td class="status-col" style="display: none;">                @switch($asset->status)
                                @case('active')
                                    {{ \App\CPU\translate('نشط') }}
                                    @break
                                @case('maintenance')
                                    {{ \App\CPU\translate('تحت الصيانة') }}
                                    @break
                                @case('disposed')
                                    {{ \App\CPU\translate('تم التخلص منه') }}
                                    @break
                                       @case('sold')
                                    {{ \App\CPU\translate('تم  بيعه') }}
                                    @break
                                       @case('closed')
                                    {{ \App\CPU\translate('تم  اهلاك كامل') }}
                                    @break
                                @default
                                    {{ $asset->status }}
                            @endswitch</td>
                                <td class="purchase_date-col" style="display: none;">{{ $asset->purchase_date }}</td>
                                <td class="commencement_date-col" style="display: none;">{{ $asset->commencement_date }}</td>
                                <td class="additional_details-col" style="display: none;">
                                    @if(!empty($asset->additional_details))
                                        <a class="btn btn-sm btn-warning btn-extra" data-bs-toggle="collapse" href="#collapse{{ $asset->id }}" role="button" aria-expanded="false" aria-controls="collapse{{ $asset->id }}">
                                            {{ \App\CPU\translate('عرض التفاصيل') }}
                                        </a>
                                    @endif
                                </td>
    <td class="position-relative none" style="overflow: visible;">
    <div class="dropdown text-center">
        <button class="btn btn-sm btn-light" type="button" id="dropdownMenu{{ $asset->id }}" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-v"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow"
            aria-labelledby="dropdownMenu{{ $asset->id }}"
            style="position: absolute; z-index: 9999; top: 100%; left: auto; right: 0;">
            
            <li>
                <a class="dropdown-item" href="{{ route('admin.assets.show', [$asset->id]) }}">
                    {{ \App\CPU\translate('تفاصيل') }}
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('admin.assets.transactions', [$asset->id]) }}">
                    {{ \App\CPU\translate('قيود') }}
                </a>
            </li>
            @if($asset->status !== 'sold' && $asset->status !== 'closed')
                <li>
                    <a class="dropdown-item" href="{{ route('admin.disposal.sale.create', [$asset->id]) }}">
                        {{ \App\CPU\translate('بيع') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.disposal.complete.create', [$asset->id]) }}">
                        {{ \App\CPU\translate('اهلاك تام') }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</td>


                            </tr>
                            @if(!empty($asset->additional_details))
                            <tr class="collapse" id="collapse{{ $asset->id }}">
                                <td colspan="17">
                                    <div class="p-3 bg-light">
                                        <h5>{{ \App\CPU\translate('تفاصيل إضافية') }}</h5>
                                        <p>{{ $asset->additional_details }}</p>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {!! $assets->appends(request()->query())->links() !!}
           
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

<!-- Bootstrap JS (مطلوب لتفعيل dropdown) -->
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
                cells.forEach(function(cell) {
                    cell.style.display = '';
                });
            } else {
                cells.forEach(function(cell) {
                    cell.style.display = 'none';
                });
            }
        });
    });
    // عند تحميل الصفحة: إخفاء الأعمدة إذا كانت checkboxes غير محددة (افتراضيًا)
    document.querySelectorAll('.toggle-col').forEach(function(chk) {
        const colClass = chk.getAttribute('data-col');
        const cells = document.querySelectorAll(`.${colClass}`);
        if (!chk.checked) {
            cells.forEach(function(cell) {
                cell.style.display = 'none';
            });
        }
    });
});


</script>
<script>
    function printTable() {
        const tableContent = document.getElementById('product-table').innerHTML;


        const printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{{ \App\CPU\translate('تقرير الاصول الثابتة') }}</title>
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
                        padding: 8px;
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
                    
                    <h1 class="page-header-title">
{{ \App\CPU\translate('تقرير   الاصول الثابتة') }}</h1>
                
                ${tableContent}
                <hr>
                <script>
                    window.onload = function() {
                        window.print();
                        window.close();
                    };
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();
    }
</script>
