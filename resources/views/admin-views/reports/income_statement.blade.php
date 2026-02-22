@extends('layouts.admin.app')

@section('content')
<style>
    /* Global Styles */
   
    .report-title h2,
    .report-title h4 {
        margin: 0;
        padding: 0;
    }
    .report-title {
        margin-bottom: 30px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 5px 8px rgba(0,0,0,0.1);
        border-left: 5px solid #007bff;
    }
    .header-section {
        margin-bottom: 30px;
        padding: 25px;
        background-color: #ffffff;
        border-radius: 10px;
        border-bottom: 5px solid #343a40;
        box-shadow: 0 4px 6px rgba(0,0,0,0.08);
    }
    .header-section .info {
        font-size: 15px;
        line-height: 1.7;
    }
    .header-section .info p {
        margin: 8px 0;
    }
    .logo-img {
        max-height: 100px;
        margin: 0 auto;
        display: block;
    }
    /* Filter Form */
    .filter-form .form-group {
        margin-bottom: 20px;
    }
    .filter-form label {
        margin-right: 8px;
        font-weight: 600;
    }
    .filter-form .btn-primary {
        margin-top: 10px;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .filter-form .btn-primary:hover {
        background-color: #0056b3;
        transform: scale(1.02);
    }
    /* Table Styles */
    table {
        width: 100%;
        margin-bottom: 30px;
        background-color: #ffffff;
        border-radius: 10px;
        overflow: hidden;
    }
    table, th, td {
    }
    th, td {
        padding: 15px 25px;
        text-align: right;
    }
    thead {
        background-color: #f0f4f8;
    }
    tfoot {
        background-color: #f8f9fa;
    }
    .table-title {
        margin: 25px 0 15px;
        font-size: 20px;
        font-weight: 600;
    }
    .toggle-btn {
        font-size: 14px;
        padding: 10px 15px;
        cursor: pointer;
        border: none;
        background-color: #007bff;
        color: #fff;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .toggle-btn:hover {
        background-color: #0056b3;
        transform: scale(1.02);
    }
    /* No-print & Print-only */
    .no-print {
        display: block;
    }
    .print-only {
        display: none;
    }
    /* Print Specific Styles */
    @media print {
        * {
            -webkit-print-color-adjust: exact;
        }
        .no-print { display: none; }
        .print-only { display: block !important; }
        body {
            font-size: 15px;
            margin: 10mm;
            background-color: #ffffff;
        }
        table, th, td {
            padding: 12px !important;
        }
          table {
            padding-top: ;-top:25px ;
        }
        h2, h3 {
            text-align: center;
            margin-top: 10mm;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #003366;
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
            line-height: 1.7;
            font-size: 16px;
        }
        .logo-img {
            max-width: 150px;
            height: auto;
        }
        .filter-form, .toggle-btn, .btn {
            display: none;
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
                    {{ \App\CPU\translate('تقرير قائمة الدخل') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>

    <!-- نموذج تحديد الفترة -->
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
                        <div class="info">
                            <p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where("key", "vat_reg_no")->first()->value ?? '' }}</p>
                            <p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where("key", "number_tax")->first()->value ?? '' }}</p>
                            <p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_email")->first()->value ?? '' }}</p>
                        </div>
                    </td>
                    <td style="width:33%; text-align: center; border: none;">
                        <img class="logo-img" src="{{ asset('storage/app/public/shop/' . (\App\Models\BusinessSetting::where("key", "shop_logo")->first()->value ?? '')) }}" style="max-width:200px;max-height:200px;" alt="شعار المؤسسة">
                    </td>
                    <td style="width:33%; text-align: right; border: none;">
                        <div class="info">
                            <p><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_name")->first()->value ?? '' }}</p>
                            <p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_address")->first()->value ?? '' }}</p>
                            <p><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_phone")->first()->value ?? '' }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
<div id="excel-table">
        <table id="excel-table" class="table">
            <!-- قسم الإيرادات -->
            <thead>
                <tr class="bg-info text-white">
                    <th colspan="3">الإيرادات</th>
                </tr>
            
            </thead>
            <tbody>
                @foreach($revenuesData as $revenue)
                    <tr>
                        <td>{{ $revenue['account']->account }}</td>
                        <td>{{ number_format($revenue['lastBalance'], 2) }}</td>
                        <td class="none">
                            @if($revenue['account']->childrenn->count() > 0)
<button class="btn btn-sm btn-white shadow-none none"
        data-toggle="collapse"
        data-target="#revenue-details-{{ $revenue['account']->id }}"
        title="عرض التفاصيل">
    <i class="tio-chevron-down text-dark"></i>
</button>
                            @else
-                            @endif
                        </td>
                    </tr>
                    @if($revenue['account']->childrenn->count() > 0)
                        <tr class="collapse" id="revenue-details-{{ $revenue['account']->id }}">
                            <td colspan="3">
                                <table class="table table-sm ">
                                    <thead>
                                        <tr>
                                            <th>اسم الحساب الفرعي</th>
                                            <th>الرصيد</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($revenue['account']->childrenn as $child)
                                            @php
                                                $childDescendantIds = App\Models\Account::where('parent_id', $child->id)->pluck('id')->toArray();
                                                $childAccountIds = array_merge([$child->id], $childDescendantIds);
                                                $childLastTransaction = App\Models\Transection::where(function($query) use ($childAccountIds) {
                                                    $query->whereIn('account_id', $childAccountIds)
                                                          ->orWhereIn('account_id_to', $childAccountIds);
                                                })
                                                ->where('created_at', '<=', $endDate)
                                                ->orderBy('created_at', 'desc')
                                                ->orderBy('id', 'desc')
                                                ->first();
                                                $childLastBalance = $childLastTransaction 
                                                    ? (in_array($childLastTransaction->account_id, $childAccountIds)
                                                        ? $childLastTransaction->balance
                                                        : $childLastTransaction->balance_account)
                                                    : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $child->account }}</td>
                                                <td>{{ number_format($childLastBalance, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr class="font-weight-bold">
                    <td>إجمالي الإيرادات</td>
                    <td>{{ number_format($totalRevenues, 2) }}</td>
                    <td class="no-print"></td>
                </tr>
            </tbody>
            </table>
                    <table id="excel-table" class="table">

            <!-- قسم تكلفة البضاعة المباعة -->
            <thead>
                <tr class="bg-warning text-dark">
                    <th colspan="3">تكلفة البضاعة المباعة</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $cogsAccount->account }}</td>
                    <td>{{ number_format($cogsLastBalance, 2) }}</td>
                    <td class="no-print">
                        @if($cogsAccount->childrenn->count() > 0)
<button class="btn btn-sm btn-white shadow-none"
        data-toggle="collapse"
        data-target="#cogs-details-{{ $cogsAccount->id }}"
        title="عرض التفاصيل">
    <i class="tio-chevron-down text-dark"></i>
</button>
                        @else
-
@endif
                    </td>
                </tr>
                @if($cogsAccount->childrenn->count() > 0)
                    <tr class="collapse" id="cogs-details-{{ $cogsAccount->id }}">
                        <td colspan="3">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>اسم الحساب الفرعي</th>
                                        <th>الرصيد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cogsAccount->childrenn as $child)
                                        @php
                                            $childDescendantIds = App\Models\Account::where('parent_id', $child->id)->pluck('id')->toArray();
                                            $childAccountIds = array_merge([$child->id], $childDescendantIds);
                                            $childLastTransaction = App\Models\Transection::where(function($query) use ($childAccountIds) {
                                                $query->whereIn('account_id', $childAccountIds)
                                                      ->orWhereIn('account_id_to', $childAccountIds);
                                            })
                                            ->where('created_at', '<=', $endDate)
                                            ->orderBy('created_at', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->first();
                                            $childLastBalance = $childLastTransaction 
                                                ? (in_array($childLastTransaction->account_id, $childAccountIds)
                                                    ? $childLastTransaction->balance
                                                    : $childLastTransaction->balance_account)
                                                : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $child->account }}</td>
                                            <td>{{ number_format($childLastBalance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif
            </tbody>
            
            <!-- صف مجمل الربح (الإيرادات - COGS) -->
            <tbody>
                <tr class="bg-light font-weight-bold">
                    <td>إجمالي الأرباح</td>
                    <td>{{ number_format($grossProfit, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
            </table>

                    <table id="excel-table" class="table">

            <!-- قسم المصروفات التشغيلية: حساب الأب (id=44) مع تفاصيل الأبناء -->
            <thead>
                <tr class="bg-danger text-white">
                    <th colspan="3">المصروفات التشغيلية</th>
                </tr>
           
            </thead>
            <tbody>
                <tr>
                    <td>{{ $opexAccount->account }}</td>
                    <td>{{ number_format($totalOpex, 2) }}</td>
                    <td>
                        @if($opexAccount->childrenn->count() > 0)
<button class="btn btn-sm btn-white shadow-none"
        data-toggle="collapse"
        data-target="#opex-details-{{ $opexAccount->id }}"
        title="عرض التفاصيل">
    <i class="tio-chevron-down text-dark"></i>
</button>
                        @else
-
@endif
                    </td>
                </tr>
                @if($opexAccount->childrenn->count() > 0)
                    <tr class="collapse" id="opex-details-{{ $opexAccount->id }}">
                        <td colspan="3">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>اسم الحساب الفرعي</th>
                                        <th>الرصيد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($opexAccount->childrenn as $child)
                                        @if(in_array($child->id, $cogsAccountIds))
                                            @continue
                                        @endif
                                        @php
                                            $childDescendantIds = App\Models\Account::where('parent_id', $child->id)->pluck('id')->toArray();
                                            $childAccountIds = array_merge([$child->id], $childDescendantIds);
                                            $childLastTransaction = App\Models\Transection::where(function($query) use ($childAccountIds) {
                                                $query->whereIn('account_id', $childAccountIds)
                                                      ->orWhereIn('account_id_to', $childAccountIds);
                                            })
                                            ->where('created_at', '<=', $endDate)
                                            ->orderBy('created_at', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->first();
                                            $childLastBalance = $childLastTransaction 
                                                ? (in_array($childLastTransaction->account_id, $childAccountIds)
                                                    ? $childLastTransaction->balance
                                                    : $childLastTransaction->balance_account)
                                                : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $child->account }}</td>
                                            <td>{{ number_format($childLastBalance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif
            </tbody>
            <tbody>
                <tr class="bg-light font-weight-bold">
                    <td>الدخل قبل المصروفات غير التشغيلية</td>
                    <td>{{ number_format($incomeBeforeNonOpEx, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>  
            </table>
                    <table id="excel-table" class="table">

            <thead>
                <tr class="bg-secondary text-white">
                    <th colspan="3">المصروفات غير التشغيلية</th>
                </tr>
            
            </thead>
            <tbody>
                <tr>
                    <td>{{ $nonOpExAccount->account }}</td>
                    <td>{{ number_format($totalNonOpEx, 2) }}</td>
                    <td class="none">
                        @if($nonOpExAccount->childrenn->count() > 0)
<button class="btn btn-sm btn-white shadow-none"
        data-toggle="collapse"
        data-target="#nonOpEx-details-{{ $nonOpExAccount->id }}"
        title="عرض التفاصيل">
    <i class="tio-chevron-down text-dark"></i>
</button>
                        @else
-                        @endif
                    </td>
                </tr>
                @if($nonOpExAccount->childrenn->count() > 0)
                    <tr class="collapse" id="nonOpEx-details-{{ $nonOpExAccount->id }}">
                        <td colspan="3">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>اسم الحساب الفرعي</th>
                                        <th>الرصيد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nonOpExAccount->childrenn as $child)
                                        @php
                                            $childDescendantIds = App\Models\Account::where('parent_id', $child->id)->pluck('id')->toArray();
                                            $childAccountIds = array_merge([$child->id], $childDescendantIds);
                                            $childLastTransaction = App\Models\Transection::where(function($query) use ($childAccountIds) {
                                                $query->whereIn('account_id', $childAccountIds)
                                                      ->orWhereIn('account_id_to', $childAccountIds);
                                            })
                                            ->where('created_at', '<=', $endDate)
                                            ->orderBy('created_at', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->first();
                                            $childLastBalance = $childLastTransaction 
                                                ? (in_array($childLastTransaction->account_id, $childAccountIds)
                                                    ? $childLastTransaction->balance
                                                    : $childLastTransaction->balance_account)
                                                : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $child->account }}</td>
                                            <td>{{ number_format($childLastBalance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif
            </tbody>
            
            <!-- صف الدخل بعد المصروفات غير التشغيلية -->
                <tr class="bg-light font-weight-bold">
                    <td>الدخل بعد المصروفات غير التشغيلية</td>
                    <td>{{ number_format($incomeAfterNonOpEx, 2) }}</td>
                    <td></td>
                </tr>
                
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