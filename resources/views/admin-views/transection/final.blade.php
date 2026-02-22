@extends('layouts.admin.app')

@section('title', \App\CPU\translate('transection_list'))

@push('css_or_js')
<style>
    .card-header {
        background-color: #001B63;
        color: white;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .form-control {
        border-radius: 5px;
    }

    h6 {
        font-weight: bold;
    }
    h3{
        color: white;
    }
    h4{
        color: white;
    }

    .print-area {
        display: none;
    }
    .add {
    color: green;
}

.subtract {
    color: red;
}

.result {
    color: blue;
}
.none{
    color:gray;
}


    @media print {
        .no-print {
            display: none;
        }

        .print-area {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            direction: rtl;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .add {
    color: green;
}

.subtract {
    color: red;
}

.result {
    color: blue;
}
.none{
    color:gray;
}

    }
</style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('عرض صندوق') }}</li>
      </ol>
    </nav>
  </div>    <div class="card shadow-lg">
      
        <div class="card-body">
            <form method="GET" action="{{ route('admin.taxe.listalltodaynew') }}" class="row gy-3 no-print">
              
                <div class="col-md-6">
                    <label for="from" class="form-label">من تاريخ</label>
                    <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-6">
                    <label for="to" class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-secondary">تصفية</button>
                    <a href="{{ route('admin.taxe.listalltodaynew') }}" class="btn btn-danger">إعادة تعيين</a>
                    <button type="button" onclick="printReport()" class="btn btn-primary">طباعة التقرير</button>

                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-lg mt-4" id="table">
     
        <div class="card-body">
<table class="table table-bordered" style="direction: rtl;">
    <thead>
        <tr>
            <th>البند</th>
            <th>نقدي</th>
            <th>شبكة</th>
            <th>أجل</th>
            <th>إجمالي</th>
        </tr>
    </thead>
    <tbody>
        <!-- Section: مبيعات -->
        <tr>
            <td>إجمالي المبيعات</td>
            <td>{{ number_format($totalSalesCash, 2) }} ريال</td>
            <td>{{ number_format($totalSalesShabaka, 2) }} ريال</td>
            <td>{{ number_format($totalSalesCredit, 2) }} ريال</td>
            <td>{{ number_format($totalSales, 2) }} ريال</td>
        </tr>

        <!-- Section: خصومات -->
        <tr>
            <td>إجمالي الخصومات</td>
            <td>{{ number_format($totalSalesDiscountCash, 2) }} ريال</td>
            <td>{{ number_format($totalSalesDiscountShabaka, 2) }} ريال</td>
            <td>{{ number_format($totalSalesDiscountCredit, 2) }} ريال</td>
            <td>{{ number_format($totalSalesDiscountCash + $totalSalesDiscountShabaka + $totalSalesDiscountCredit, 2) }} ريال</td>
        </tr>

        <!-- Section: صافي مبيعات -->
        <tr>
            <td>صافي المبيعات</td>
            <td>{{ number_format($totalsafysafycash, 2) }} ريال</td>
            <td>{{ number_format($totalSalesafyShabaka, 2) }} ريال</td>
            <td>{{ number_format($totalsafysafycredit, 2) }} ريال</td>
            <td>{{ number_format($totalsafysafycash + $totalSalesafyShabaka + $totalsafysafycredit, 2) }} ريال</td>
        </tr>

        <!-- Section: مردود مبيعات -->
        <tr>
            <td>إجمالي مردود المبيعات</td>
            <td colspan="4">{{ number_format($totalreSales, 2) }} ريال</td>
        </tr>

        <!-- Section: خصومات مردود مبيعات -->
        <tr>
            <td>إجمالي خصومات مردود المبيعات</td>
            <td colspan="4">{{ number_format($totalreSalesDiscount, 2) }} ريال</td>
        </tr>

        <!-- Section: صافي مردود مبيعات -->
        <tr>
            <td>إجمالي صافي مردود المبيعات</td>
            <td colspan="4">{{ number_format($totalreSales - $totalreSalesDiscount, 2) }} ريال</td>
        </tr>

        <!-- Section: صافي مبيعات -->
        <tr>
            <td>صافي المبيعات</td>
            <td colspan="4">{{ number_format($totalsafysafycash + $totalSalesafyShabaka + $totalsafysafycredit-$totalreSales + $totalreSalesDiscount, 2) }} ريال</td>
        </tr>

        <!-- Section: سندات القبض -->
        <tr>
            <td>إجمالي سندات القبض</td>
            <td colspan="4">{{ number_format($tax, 2) }} ريال</td>
        </tr>

        <!-- Section: المصروفات -->
        <tr>
            <td>إجمالي المصروفات</td>
            <td colspan="4">{{ number_format($taxexpense, 2) }} ريال</td>
        </tr>

        <!-- Section: المتبقي -->
        <tr>
            <td>صافي حركة الصندوق</td>
            <td colspan="4">{{ number_format($totalsafysafycash + $totalSalesafyShabaka + $totalsafysafycredit-$totalreSales + $totalreSalesDiscount-$taxexpense+$tax, 2) }} ريال</td>
        </tr>
    </tbody>
</table>
        </div>
    </div>
</div>
@endsection

<script>
    function printReport() {
        const fromDate = document.querySelector('#from').value || 'غير محدد';
        const toDate = document.querySelector('#to').value || 'غير محدد';
        const printContents = document.querySelector('#table').innerHTML;

        const printWindow = window.open('', '_blank');
        printWindow.document.open();
        printWindow.document.write(`
            <html>
            <head>
                <title>تقرير نقاط البيع</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; direction: rtl; }
                    h4, h6 { text-align: center; margin-bottom: 20px; }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    table th, table td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: center;
                    }
                    table th {
                        background-color: #007bff;
                        color: white;
                    }
                    table tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .signatures {
                        margin-top: 40px;
                        text-align: right;
                        font-size: 16px;
                    }
                    .signatures span {
                        display: inline-block;
                        width: 35%;
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
                <h4>تقرير الصندوق</h4>
                <h6>من تاريخ: ${fromDate} إلى تاريخ: ${toDate}</h6>
                ${printContents}
                <div class="signatures col">
                    <span>توقيع مدير الحسابات: ................................................</span>
                    <span>توقيع المدير: ......................................................</span>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
</script>
