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
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header text-center">
            <h3 class="text-white">عرض قائمة الدخل</h3>
            <p>يمكنك الصندوق التاريخ أو النوع</p>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.taxe.listallbox') }}" class="row gy-3 no-print">
                <!--<div class="col-md-4">-->
                <!--    <label for="account_id" class="form-label">الحساب</label>-->
                <!--    <select name="account_id" id="account_id" class="form-control">-->
                <!--        <option value="">اختر الحساب</option>-->
                <!--        @foreach($accounts as $account)-->
                <!--            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>-->
                <!--                {{ $account->account }}-->
                <!--            </option>-->
                <!--        @endforeach-->
                <!--    </select>-->
                <!--</div>-->

                <!--<div class="col-md-4">-->
                <!--    <label for="tran_type" class="form-label">نوع المعاملة</label>-->
                <!--    <select name="tran_type" id="tran_type" class="form-control">-->
                <!--        <option value="">اختر النوع</option>-->
                <!--        <option value="12" {{ request('tran_type') == '12' ? 'selected' : '' }}>مشتريات</option>-->
                <!--        <option value="24" {{ request('tran_type') == '24' ? 'selected' : '' }}>مردود مشتريات</option>-->
                <!--        <option value="4" {{ request('tran_type') == '4' ? 'selected' : '' }}>مبيعات</option>-->
                <!--        <option value="7" {{ request('tran_type') == '7' ? 'selected' : '' }}>مردود مبيعات</option>-->
                <!--    </select>-->
                <!--</div>-->

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
                    <a href="{{ route('admin.taxe.listallbox') }}" class="btn btn-secondary">إعادة تعيين</a>
                    <button type="button" onclick="printReport()" class="btn btn-primary">طباعة التقرير</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-lg mt-4" id="table">
        <div class="card-header text-center">
            <h4 class="text-white">ملخص قائمة الدخل</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered" style="direction: rtl;">
                <thead>
                    <tr>
                        <th>البند</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
<tbody>
      <tr>
        <td>إجمالي المبيعات</td>
        <td class="add">{{ number_format($totalSales, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي  الخصم  المسموح به </td>
        <td class="subtract">{{ number_format($totalSalesdiscount, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي مردود المبيعات</td>
        <td class="subtract">{{ number_format($totalReSales, 2) }} ريال</td>
    </tr>
     
   <tr style="background-color: yellow;">
    <td>صافي  المبيعات شامل الضريبة</td>
    @php
    $finalAmount = ($totalSales ) - ($totalSalesdiscount + $totalReSales + $productExpireData);
@endphp

<!-- Render the calculated value in a table cell -->
<td class="add">{{ number_format($finalAmount, 2) }} ريال</td>

</tr>
  <tr>
    <td>مخزون أول المدة</td>
    <td class="none">{{ number_format($productsstart*1.15, 2) }} ريال</td>
</tr>
    <tr>
        <td>إجمالي المشتريات</td>
        <td class="add">{{ number_format($totalPurchases, 2) }} ريال</td>
    </tr>
        <tr>
        <td>إجمالي مردود المشتريات</td>
        <td class="subtract">{{ number_format($totalRePurchases, 2) }} ريال</td>
    </tr>
        <tr>
        <td>إجمالي  الخصم المكتسب</td>
        <td class="subtract">{{ number_format($finalPurchasediscount, 2) }} ريال</td>
    </tr>
<tr style="background-color: #ADD8E6;">
    <td>  تكلفة المشتريات شامل الضريبة</td>
    <td class="add">{{ number_format($netpurchase*1.15, 2) }} ريال</td>
</tr>
<tr style="background-color: lightgreen;">
    <td> تكلفة البضاعة المتاحة للبيع شامل الضريبة</td>
<td class="add">{{ number_format(($netpurchase * 1.15) + ($productsstart * 1.15), 2) }} ريال</td>
</tr>
<tr>
    <td>إجمالي قيمة المخزون أخر المدة</td>
    <td class="add">{{ number_format($productsnoww*1.15, 2) }} ريال</td>
</tr>
<tr>
    <td>تكلفة البضاعة المباعة</td>
<td class="add">
    {{ number_format((($netpurchase * 1.15) + ($productsstart * 1.15) - ($productsnoww * 1.15)), 2) }} ريال
</td>
</tr>
<tr style="background-color: black;">
    <td style="color: white;">مجمل الربح أو الخسارة</td>
<td class="add" style="color: white;">
    {{ number_format($finalAmount - (($netpurchase * 1.15) + ($productsstart * 1.15) - ($productsnoww * 1.15)), 2) }} ريال
</td>
</tr>
 <tr>
        <th colspan="2" style="background-color: red; color:white;"> مصروفات </th>
    </tr>
 <tr>
        
    <tr>
        <td>إجمالي  مصروفات الأصول الثابتة</td>
        <td class="add">{{ number_format($totalStillStart, 2) }} ريال</td>
    </tr>
    <tr>
        <td>إجمالي السلف</td>
        <td class="add">{{ number_format($totallaon, 2) }} ريال</td>
    </tr>
        <tr>
        <td>إجمالي الرواتب المسددة</td>
        <td class="add">{{ number_format($totalSalary, 2) }} ريال</td>
    </tr>
       <tr>
        <td>إجمالي  مصروفات  تشغيل</td>
        <td class="add">{{ number_format($expense, 2) }} ريال</td>
    </tr>
     <tr>
        <td>مصروف بضاعة تالفة</td>
        <td class="add">{{ number_format($productExpireData, 2) }} ريال</td>
    </tr>
     <tr style="background-color: red;">
        <td>صافي المصروفات</td>
        <td class="add">{{ number_format($totalexpenses+$productExpireData, 2) }} ريال</td>
    </tr>
     <tr style="background-color: black;">
        <td style="color:white;">صافي الربح بعد التشغيل</td>
      @php
    $calculatedValue = $finalAmount - (($netpurchase * 1.15) + ($productsstart * 1.15) - ($productsnoww * 1.15));
    $additionalExpenses = $totalexpenses + $productExpireData;
@endphp

@if($calculatedValue < 0)
    <td class="add" style="color:white;">
        {{ number_format($calculatedValue - $additionalExpenses, 2) }} ريال
    </td>
@else
    <td class="add" style="color:white;">
        {{ number_format($calculatedValue - $additionalExpenses, 2) }} ريال
    </td>
@endif

    </tr>
  <!--  <tr>-->
  <!--      <td>إجمالي الإيرادات الأخري</td>-->
  <!--      <td class="add">{{ number_format($totalIncome, 2) }} ريال</td>-->
  <!--  </tr>-->
  <!--<tr style="background-color: #ADD8E6;">-->
  <!--      <td>صافي الإيرادات </td>-->
  <!--      <td class="add">{{ number_format($totalIncome, 2) }} ريال</td>-->
  <!--  </tr>-->
<!--    <tr style="background-color: #4CAF50; color: white;">-->
<!--    <td>صافي الربح أو الخسارة النهائي</td>-->
<!--    <td class="add">{{ number_format(($calculatedValue+($calculatedValue - $additionalExpenses)) , 2) }} ريال</td>-->
<!--</tr>-->

  
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
                <title>تقرير قائمة الدخل</title>
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
                </style>
            </head>
            <body>
                <h4>تقرير قائمة الدخل</h4>
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
