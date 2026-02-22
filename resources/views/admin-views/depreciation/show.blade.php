@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تفاصيل الأصل'))

@push('css_or_js')
    <style>
        :root {
            --blueblack:black;   /* لون أزرق داكن يميل إلى الأسود */
            --yellow: rgba(248,190,28,1);    /* لون أصفر */
        }
        /* تنسيق عام للصفحة */
        .page-header-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--blueblack);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        /* تنسيق البطاقة */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        .card-body {
            padding: 2rem;
            background-color: #fff;
        }
        /* زر العودة */
        .back-btn {
            margin-top: 1.5rem;
            background-color: var(--blueblack);
            border: 1px solid var(--blueblack);
            color: #fff;
            font-weight: 600;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .back-btn:hover {
            background-color: rgba(0,0,51,0.8);
            border-color: rgba(0,0,51,0.8);
            color: #fff;
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
        /* تنسيق الجدول */
        .table-bordered {
            border: 1px solid #ddd;
        }
        .table-bordered th,
        .table-bordered td {
            vertical-align: middle;
            font-size: 0.85rem;
            padding: 0.6rem;
            text-align: center;
            word-wrap: break-word;
        }
      th {
            background-color:#EDF2F4;
            color: #000;
            text-align: center;
        }
        .table tbody td {
            text-align: center;
        }
     
        /* زر الطباعة - لا يظهر عند الطباعة */
        .no-print {
            display: block;
        }
        /* عند الطباعة، تأكد من ظهور كل المحتوى */
        @media print {
            * {
                -webkit-print-color-adjust: exact;
            }
            body {
                margin: 0;
                padding: 0;
                width: 210mm;
                font-size: 12px;
                background-color: #ffffff;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .card, .card-body {
                box-shadow: none !important;
                border: none !important;
                margin: 0;
                padding: 0;
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
            h1, h4 {
                text-align: center;
                color: var(--blueblack);
            }
        }
            .section-title {
        font-size: 20px;
        font-weight: bold;
        color: #000;
        margin-bottom: 5px;
        padding-bottom: 5px;
    }

    .receipt-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: px;
    }

    .receipt-col {
        flex: 0 0 50%;
        max-width: 50%;
        box-sizing: border-box;
        padding: 4px 8px;
    }

    .receipt-col.full {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .label {
        font-weight: bold;
        color: #000;
        min-width: 140px;
        display: inline-block;
    }

    .value {
        color: #000;
    }

    hr {
        margin: 10px 0;
    }

    .receipt-section {
        margin-bottom: 10px;
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
            <li class="breadcrumb-item">
                <a href="{{ route('admin.depreciation.index') }}" class="text-primary">
                    {{ \App\CPU\translate(' الأصول الثابتة') }}
                </a>
            </li>
                <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate('تفاصيل الأصل') }} : {{ $asset->asset_name }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>
    
    <!-- زر الطباعة (غير معروض عند الطباعة) -->

    
    <div id="printableArea">
        <div class="card">
            <div class="card-body">
        

<div class="receipt-section">
    <div class="section-title">📌 {{ \App\CPU\translate('بيانات الأصل') }}</div>
    <div class="receipt-row">
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('الرقم') }}:</span> <span class="value">{{ $asset->id }}</span></div>
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('اسم الأصل') }}:</span> <span class="value">{{ $asset->asset_name }}</span></div>
    </div>
    <div class="receipt-row">
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('الكود') }}:</span> <span class="value">{{ $asset->code }}</span></div>
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('الفرع') }}:</span> <span class="value">{{ $asset->branch->name ?? '-' }}</span></div>
    </div>
    <div class="receipt-row">
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('تاريخ الشراء') }}:</span> <span class="value">{{ $asset->purchase_date }}</span></div>
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('تاريخ بدء التشغيل') }}:</span> <span class="value">{{ $asset->commencement_date }}</span></div>
    </div>
</div>

<hr>

<div class="receipt-section">
    <div class="section-title">💰 {{ \App\CPU\translate('البيانات المالية') }}</div>
    <div class="receipt-row">
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('سعر الشراء') }}:</span> <span class="value">{{ number_format($asset->purchase_price, 2) }}</span></div>
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('التكاليف الإضافية') }}:</span> <span class="value">{{ number_format($asset->additional_costs, 2) }}</span></div>
    </div>
    <div class="receipt-row">
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('التكلفة الإجمالية') }}:</span> <span class="value">{{ number_format($asset->total_cost, 2) }}</span></div>
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('القيمة المتبقية') }}:</span> <span class="value">{{ number_format($asset->salvage_value, 2) }}</span></div>
    </div>
</div>

<hr>

<div class="receipt-section">
    <div class="section-title">📉 {{ \App\CPU\translate('الاستهلاك والحالة') }}</div>
    <div class="receipt-row">
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('العمر الافتراضي') }}:</span> <span class="value">{{ $asset->useful_life }} {{ \App\CPU\translate('سنوات') }}</span></div>
        <div class="receipt-col">
            <span class="label">{{ \App\CPU\translate('طريقة الاهلاك') }}:</span>
            <span class="value">
                @switch($asset->depreciation_method)
                    @case('straight_line') {{ \App\CPU\translate('القسط الثابت') }} @break
                    @case('declining_balance') {{ \App\CPU\translate('الرصيد المتناقص') }} @break
                    @case('units_of_production') {{ \App\CPU\translate('الإنتاج/الاستخدام') }} @break
                    @default {{ $asset->depreciation_method }}
                @endswitch
            </span>
        </div>
    </div>
    <div class="receipt-row">
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('الاستهلاك المتراكم') }}:</span> <span class="value">{{ number_format($asset->accumulated_depreciation, 2) }}</span></div>
        <div class="receipt-col"><span class="label">{{ \App\CPU\translate('القيمة الدفترية') }}:</span> <span class="value">{{ number_format($asset->book_value, 2) }}</span></div>
    </div>
    <div class="receipt-row">
        <div class="receipt-col full">
            <span class="label">{{ \App\CPU\translate('الحالة') }}:</span>
            <span class="value">
                @switch($asset->status)
                    @case('active') {{ \App\CPU\translate('نشط') }} @break
                    @case('maintenance') {{ \App\CPU\translate('تحت الصيانة') }} @break
                    @case('disposed') {{ \App\CPU\translate('تم التخلص منه') }} @break
                    @case('sold') {{ \App\CPU\translate('تم بيعه') }} @break
                    @case('closed') {{ \App\CPU\translate('تم اهلاك كامل') }} @break
                    @default {{ $asset->status }}
                @endswitch
            </span>
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
document.addEventListener('DOMContentLoaded', function() {
    window.printDiv = function(divId) {
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
                        margin: 20px;
                    }
                     body {
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
                     .section-title {
        font-size: 20px;
        font-weight: bold;
        color: #000;
        margin-bottom: 15px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 5px;
    }

    .receipt-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .receipt-col {
        flex: 0 0 50%;
        max-width: 50%;
        box-sizing: border-box;
        padding: 4px 8px;
    }

    .receipt-col.full {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .label {
        font-weight: bold;
        color: #000;
        min-width: 140px;
        display: inline-block;
    }

    .value {
        color: #2d3436;
    }

    hr {
        border-top: 1px solid #ccc;
        margin: 25px 0;
    }

    .receipt-section {
        margin-bottom: 20px;
    }
    .none{
    display:none;
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

                ${content}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
});
</script>
