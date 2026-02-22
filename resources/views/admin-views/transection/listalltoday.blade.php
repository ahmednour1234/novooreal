@extends('layouts.admin.app')

@section('title', \App\CPU\translate('transection_list'))

@push('css_or_js')
<style>
   .card-header {
        background-color: #001B63;
        color: white;
    }
    h3{
        color: white;
    }
  h4{
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
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('عرض الصندوق') }}</li>
      </ol>
    </nav>
  </div>
    <div class="card shadow-lg">
    
        <div class="card-body">
            <form method="GET" action="{{ route('admin.taxe.listalltoday') }}" class="row gy-3 no-print">
                <div class="col-md-4">
                    <label for="account_id" class="form-label">الحساب</label>
                    <select name="account_id" id="account_id" class="form-control">
                        <option value="">اختر الحساب</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tran_type" class="form-label">نوع المعاملة</label>
                    <select name="tran_type" id="tran_type" class="form-control">
                        <option value="">اختر النوع</option>
                        <option value="12" {{ request('tran_type') == '12' ? 'selected' : '' }}>مشتريات</option>
                        <option value="24" {{ request('tran_type') == '24' ? 'selected' : '' }}>مردود مشتريات</option>
                        <option value="4" {{ request('tran_type') == '4' ? 'selected' : '' }}>مبيعات</option>
                        <option value="7" {{ request('tran_type') == '7' ? 'selected' : '' }}>مردود مبيعات</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="from" class="form-label">من تاريخ</label>
                    <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-6">
                    <label for="to" class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-success">تصفية</button>
                    <a href="{{ route('admin.taxe.listalltoday') }}" class="btn btn-secondary">إعادة تعيين</a>
                    <button type="button" onclick="printReport()" class="btn btn-primary">طباعة التقرير</button>

                </div>
            </form>
        </div>
    </div>


            <table class="table table-bordered" style="direction: rtl;">
                <thead>
                    <tr>
                        <th>البند</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
<tbody>
    <tr>
        <td>إجمالي المشتريات</td>
        <td class="none">{{ number_format($totalPurchases, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي المبالغ المدفوعة في الشراء</td>
        <td class="subtract">{{ number_format($totalDonePurchases, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي مردود المشتريات</td>
        <td class="none">{{ number_format($totalRePurchases, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي المبالغ مردود المشتريات</td>
        <td class="add">{{ number_format($totalDoneRePurchases, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي المبيعات</td>
        <td class="none">{{ number_format($totalSales, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي مبالغ المحصلة من المبيعات</td>
        <td class="add">{{ number_format($totalDoneSales, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي مردود المبيعات</td>
        <td class="none">{{ number_format($totalReSales, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي مبالغ المدفوعة من مردود المبيعات</td>
        <td class="add">{{ number_format($totalDoneReSales, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي الدخل</td>
        <td class="add">{{ number_format($totalIncome, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي سندات القبض</td>
        <td class="add">{{ number_format($totalBalance, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي المصروفات</td>
        <td class="subtract">{{ number_format($totalExpense, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي سندات الصرف</td>
        <td class="subtract">{{ number_format($totalCredit, 2) }} ريال</td>
    </tr>
    <tr>
        <td>الرصيد الافتتاحي</td>
        <td class="subtract">{{ number_format($totalStart, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي الأصول الثابتة</td>
        <td class="subtract">{{ number_format($totalStillStart, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي السلف</td>
        <td class="subtract">{{ number_format($totallaon, 2) }} ريال</td>
    </tr>
        <tr>
        <td>إجمالي الرواتب المسددة</td>
        <td class="subtract">{{ number_format($totalSalary, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي المتبقي</td>
        <td class="result">{{ number_format($tax, 2) }} ريال</td>
    </tr>
</tbody>
            </table>
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
                <title>تقرير الصندوق</title>
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
