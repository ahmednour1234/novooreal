@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تفاصيل سجل الصيانة'))

@push('css_or_js')
    <style>
        :root {
            --blueblack: rgba(0,0,51,1);
            --yellow: rgba(248,190,28,1);
        }

        /* تنسيق عام */
        .page-header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--blueblack);
            text-align: center;
            margin-bottom: 1rem;
        }
        /* تنسيق البطاقة */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
        }
        .card-body {
            padding: 2rem;
            background-color: #fff;
        }
        /* تنسيق الجدول */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th,
        .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: middle;
            font-size: 0.95rem;
        }
        .info-table th {
            background-color: var(--blueblack);
            color: #fff;
            text-align: right;
            width: 35%;
        }
        .info-table td {
            text-align: right;
        }
        /* زر العودة */
        .back-btn {
            background-color: var(--blueblack);
            color: #fff;
            font-weight: 600;
            margin-top: 1.5rem;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .back-btn:hover {
            background-color: rgba(0,0,51,0.8);
            border-color: rgba(0,0,51,0.8);
        }
        /* زر الطباعة */
        .print-btn {
            background-color: var(--yellow);
            color: #fff;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s, border-color 0.3s;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .print-btn:hover {
            background-color: rgba(248,190,28,0.8);
            border-color: rgba(0,0,51,0.8);
        }
        /* تنسيق الطباعة */
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
            .no-print {
                display: none !important;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            .info-table, th, td {
                border: 1px solid #333 !important;
                padding: 6px !important;
                text-align: center !important;
            }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
            h1, h4 { text-align: center; color: var(--blueblack); }
        }
        .receipt-section {
    margin-bottom: 1.5rem;
}

.section-title {
    font-weight: bold;
    color: #000;
    font-size: 16px;
    margin-bottom: 10px;
}

.receipt-row {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.receipt-col {
    flex: 1;
    min-width: 250px;
    margin-bottom: 4px;
}

.label {
    font-weight: 600;
    color: #333;
}

.value {
    color: #555;
    margin-inline-start: 5px;
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
                                        <a href="{{ route('admin.maintenance_logs.index') }}" class="text-secondary">

                    {{ \App\CPU\translate('سجلات صيانة الأصول') }}
                    </a>
                </li>
            </ol>
        </nav>
    </div>    
    <!-- زر الطباعة (لا يظهر عند الطباعة) -->
  
    
    <div id="printableArea">
    <div class="card">
        <div class="card-body">

    

            {{-- 📌 بيانات الأصل --}}
            <div class="receipt-section">
                <div class="section-title">📌 {{ \App\CPU\translate('معلومات الأصل') }}</div>

                <div class="receipt-row">
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('الأصل') }}:</span>
                        <span class="value">{{ $maintenance->asset->asset_name ?? '-' }}</span>
                    </div>
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('الكود') }}:</span>
                        <span class="value">{{ $maintenance->asset->code ?? '-' }}</span>
                    </div>
                </div>

                <div class="receipt-row">
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('الفرع') }}:</span>
                        <span class="value">{{ $maintenance->branch->name ?? '-' }}</span>
                    </div>
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('تاريخ الصيانة') }}:</span>
                        <span class="value">{{ $maintenance->maintenance_date }}</span>
                    </div>
                </div>
            </div>

            <hr>

            {{-- 🛠️ بيانات الصيانة --}}
            <div class="receipt-section">
                <div class="section-title">🛠️ {{ \App\CPU\translate('تفاصيل الصيانة') }}</div>

                <div class="receipt-row">
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('نوع الصيانة') }}:</span>
                        <span class="value">
                            @if($maintenance->maintenance_type == 'preventive')
                                {{ \App\CPU\translate('وقائية') }}
                            @elseif($maintenance->maintenance_type == 'emergency')
                                {{ \App\CPU\translate('طارئة') }}
                            @else
                                {{ $maintenance->maintenance_type }}
                            @endif
                        </span>
                    </div>
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('التكلفة التقديرية') }}:</span>
                        <span class="value">{{ number_format($maintenance->estimated_cost, 2) }}</span>
                    </div>
                </div>

                <div class="receipt-row">
                    <div class="receipt-col w-100">
                        <span class="label">{{ \App\CPU\translate('الملاحظات') }}:</span>
                        <span class="value d-block mt-1">{{ $maintenance->notes ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <hr>

            {{-- 📊 معلومات الحالة --}}
            <div class="receipt-section">
                <div class="section-title">📊 {{ \App\CPU\translate('الحالة والمتابعة') }}</div>

                <div class="receipt-row">
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('الحالة') }}:</span>
                        <span class="value">
                            @if($maintenance->status == 'scheduled')
                                {{ \App\CPU\translate('مجدولة') }}
                            @elseif($maintenance->status == 'in progress')
                                {{ \App\CPU\translate('جاري التنفيذ') }}
                            @elseif($maintenance->status == 'completed')
                                {{ \App\CPU\translate('مكتملة') }}
                            @else
                                {{ ucfirst($maintenance->status) }}
                            @endif
                        </span>
                    </div>
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('تاريخ الإنشاء') }}:</span>
                        <span class="value">{{ $maintenance->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>

                <div class="receipt-row">
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('أنشئ بواسطة') }}:</span>
                        <span class="value">{{ $maintenance->add->email ?? '-' }}</span>
                    </div>
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('جاري التنفيذ بواسطة') }}:</span>
                        <span class="value">{{ $maintenance->approve->email ?? '-' }}</span>
                    </div>
                </div>

                <div class="receipt-row">
                    <div class="receipt-col">
                        <span class="label">{{ \App\CPU\translate('مكتمل بواسطة') }}:</span>
                        <span class="value">{{ $maintenance->done->email ?? '-' }}</span>
                    </div>
                </div>
            </div>

               <div class="d-flex justify-content-end mb-3 none">
    <button class="btn btn-primary" onclick="printDiv('printableArea')">
        {{ \App\CPU\translate('طباعة التقرير') }}
    </button>
</div>
        </div>
    </div>
</div>

</div>
@endsection

<script>
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
.receipt-section {
    margin-bottom: 1.5rem;
}

.section-title {
    font-weight: bold;
    color: #001B63;
    font-size: 16px;
    margin-bottom: 10px;
}

.receipt-row {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.receipt-col {
    flex: 1;
    min-width: 250px;
    margin-bottom: 4px;
}

.label {
    font-weight: 600;
    color: #333;
}

.value {
    color: #555;
    margin-inline-start: 5px;
}

                </style>
        </head>
        <body>
            <h1>{{ \App\CPU\translate('تقرير صيانة الأصول') }}</h1>
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
