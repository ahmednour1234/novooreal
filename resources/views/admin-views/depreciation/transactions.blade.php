@extends('layouts.admin.app')

@section('title',\App\CPU\translate('transection_list'))

@push('css_or_js')

@endpush

@section('content')
<style>
    /* Styling for the input fields and dropdown */




/* Styling for buttons */
.custom-btn {
    border-radius: 5px;
    padding: 10px;
    font-size: 14px;
    font-weight: bold;
    transition: all 0.3s ease;
}




.col-12 {
    margin-bottom: 10px;
}

.font-weight-bold {
    font-weight: 600;
    font-size: 16px;
    color: #333;
}

.input-label {
    display: block;
    margin-bottom: 5px;
}

.row {
    margin-bottom: 15px;
}

</style>
<style>
    .account-container {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .account-item {
        padding: 5px;
        background-color: #f8f9fa;
        border-radius: 5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .amount-container {
        display: flex;
        justify-content: space-between;
        gap: 5px;
        margin-bottom: 5px;
    }
    .amount-item {
        padding: 5px;
        background-color: #f8f9fa;
        border-radius: 5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .amount-item.text-danger {
        color: red;
    }
    .amount-item.text-success {
        color: green;
    }
    .amount-item {
        width: 100%;
    }
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f1f1;
    }
    .badge {
        font-size: 0.85rem;
        padding: 5px 10px;
    }
        .action-buttons {
 
    }

    .custom-btn {
        padding: 10px 50px;
        font-weight: bold;
    }
       .equal-button {
        flex: 1 1 0;
        min-width: 200px;
        margin: 5px;
    }
 .pb-2, .py-2 {
    padding-right: .5rem !important;
}
.table td, .table th {
    padding: 0.2rem;
    vertical-align: center;
    border-top: .02rem solid rgba(231, 234, 243, .7);
}
</style>

<div class="content container-fluid">
        <!-- Page Header -->
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
                    {{ \App\CPU\translate('الأصول الثابتة') }}
                </a>
            </li>
             <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate('القيود') }} : {{ $asset->asset_name }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>

        <!-- End Page Header -->
        <div class="row ">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
<form action="{{ url()->current() }}" method="GET">
    <div class="form-section">
<div class="row py-2 pr-1">

            <!-- الحساب -->
            <div class="form-group col-12 col-sm-6 col-md-3 ">
                <label class="input-label font-weight-bold">{{ \App\CPU\translate('الحساب') }}</label>
                <select name="account_id" class="form-control js-select2-custom custom-select">
                    <option value="">---{{ \App\CPU\translate('اختار') }}---</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account['id'] }}" {{ $acc_id == $account['id'] ? 'selected' : '' }}>
                            {{ $account['account'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- الفرع -->
            <div class="form-group col-12 col-sm-6 col-md-3">
                <label class="input-label font-weight-bold">{{ \App\CPU\translate('الفرع') }}</label>
                <select name="branch_id" class="form-control js-select2-custom custom-select">
                    <option value="">---{{ \App\CPU\translate('اختار') }}---</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch['id'] }}" {{ $branch_id == $branch['id'] ? 'selected' : '' }}>
                            {{ $branch['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

   
            <!-- التواريخ -->
            <div class="form-group col-12 col-sm-6 col-md-3">
                <label class="input-label font-weight-bold">{{ \App\CPU\translate('من تاريخ') }}</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>

            <div class="form-group col-12 col-sm-6 col-md-3">
                <label class="input-label font-weight-bold">{{ \App\CPU\translate('إلى تاريخ') }}</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
        </div>

        <!-- تحديد حالة الفلترة -->
        @php
            $chk = ($acc_id || $tran_type || $from || $to) ? 1 : 0;
        @endphp

        <!-- أزرار الإجراءات -->
       <div class="row action-buttons">
    <div class="col-12 d-flex flex-wrap justify-content-center">
          <button type="button" onclick="printTable()" class="btn btn-primary custom-btn equal-button"
                data-toggle="tooltip" data-placement="top"
                title="{{ $chk == 0 ? \App\CPU\translate('export_last_month_data') : '' }}">
            {{ \App\CPU\translate('طباعة') }}
        </button>
        <button type="submit" class="btn btn-success custom-btn equal-button">
            {{ \App\CPU\translate('بحث') }}
        </button>
 
     
        <a onclick="exportTableToExcel('excel-table')"
           class="btn btn-info custom-btn equal-button"
           data-toggle="tooltip" data-placement="top"
           title="{{ $chk == 0 ? \App\CPU\translate('export_last_month_data') : '' }}">
            {{ \App\CPU\translate('إصدار ملف أكسل') }}
        </a>

        <a href="{{ url()->current() }}" class="btn btn-danger custom-btn equal-button">
            {{ \App\CPU\translate('إلغاء') }}
        </a>

    </div>
</div>
    </div>
</form>

                    <!-- End Header -->

                    <!-- Table -->

                    @php
    $account = null;
    if (request()->has('account_id') && !is_null(request()->get('account_id'))) {
        $account = \App\Models\Account::find(request()->get('account_id'));
    }
@endphp

        <div class="table-responsive datatable-custom" id="product-table">
<table id="excel-table" class="table">
    <thead class="thead-light">
        <tr>
            <th rowspan="2">{{ \App\CPU\translate('رقم القيد') }}</th>
                        <th rowspan="2">{{ \App\CPU\translate('الوصف') }}</th>

            <th rowspan="2">{{ \App\CPU\translate('التاريخ') }}</th>
            <th rowspan="2">{{ \App\CPU\translate('النوع') }}</th>
            <th rowspan="2">{{ \App\CPU\translate('العملة') }}</th>
            <th rowspan="2">{{ \App\CPU\translate('المبلغ') }}</th>

            <th rowspan="2" class="none">{{ \App\CPU\translate('طباعة') }}</th>
            <th rowspan="2" class="none">{{ \App\CPU\translate('قيد عكسي') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($transections as $key => $transection)
            <tr>
                <td>{{ $transection->id }}</td>
                                              <td>{{ Str::limit($transection->description, 30) }}</td>

                <td>{{ $transection->date ?? $transection->created_at }}</td>

                <td>
                    @if ($transection->tran_type == 'Expense')
                        <span class="badge-koyod badge-danger">
                            {{ \App\CPU\translate('المصروفات') }}
                        </span>
                    @elseif($transection->tran_type == 200)
                        <span class="badge-koyod badge-success">
                            {{ \App\CPU\translate('سند قبض') }}
                        </span>
                    @elseif($transection->tran_type == 'Transfer')
                        <span class="badge-koyod badge-warning">
                            {{ \App\CPU\translate('قيود يدوية') }}
                        </span>
                    @elseif($transection->tran_type == 2)
                        <span class="badge-koyod badge-warning">
                            {{ \App\CPU\translate('أصل ثابت') }}
                        </span>
                    @elseif($transection->tran_type ==0 || $transection->tran_type ==1)
                        <span class="badge-koyod badge-warning">
                            {{ \App\CPU\translate('رصيد افتتاحي') }}
                        </span>
                    @elseif($transection->tran_type == 'Income')
                        <span class="badge-koyod badge-success">
                            {{ \App\CPU\translate('الدخل') }}
                        </span>
                    @elseif ($transection->tran_type == 4)
                        <span class="badge-koyod badge-danger">مبيعات</span>
                    @elseif ($transection->tran_type == 500)
                        <span class="badge-koyod badge-danger">تحويل مندوب</span>
                    @elseif($transection->tran_type == 7)
                        <span class="badge-koyod badge-info">مرتجع مبيعات</span>
                    @elseif($transection->tran_type == 12)
                        <span class="badge-koyod badge-warning">مشتريات</span>
                    @elseif($transection->tran_type == 24)
                        <span class="badge-koyod badge-success">مرتجع مشتريات</span>
                    @elseif($transection->tran_type == 13)
                        <span class="badge-koyod badge-soft-warning">سداد مشتريات</span>
                    @elseif($transection->tran_type == 26)
                        <span class="badge-koyod badge-soft-success">استلام نقدية</span>
                    @elseif($transection->tran_type == 30)
                        <span class="badge-koyod badge-soft-success">خصم مكتسب</span>
                             @elseif($transection->tran_type == 3)
                        <span class="badge-koyod badge-soft-success">تحويل مخزني</span>
                                 @elseif($transection->tran_type == 555)
                        <span class="badge-koyod badge-soft-success">امر صرف مخزني</span>
                            @elseif($transection->tran_type == 'Depreciation')
                        <span class="badge-koyod badge-soft-success">اهلاك اصل ثابت</span>
                                    @elseif($transection->tran_type == 'asset_sold')
                        <span class="badge-koyod badge-soft-success">بيع اصل ثابت</span>
                                @elseif($transection->tran_type == 'salary')
                        <span class="badge-koyod badge-soft-success">دفع مرتب</span>
                        
       @elseif($transection->tran_type == 'closed')
                        <span class="badge-koyod badge-soft-success">اهلاك كامل اصل ثابت</span>
                    @else                        <span class="badge-koyod badge-success">
                            {{ \App\CPU\translate('سند صرف') }}
                        </span>
                    @endif
                </td>
                <td>{{ \App\CPU\Helpers::currency_symbol() }}</td>
                <td>
                    <div class="account-container">
                        <div class="amount-item text-danger">
                            {{ number_format($transection->debit_account, 2) }}
                        </div>
                        <div class="amount-item text-success">
                            {{ number_format($transection->credit_account, 2) }}
                        </div>
                    </div>
                </td>
              
              
                <td class="none">
                    <button class="btn btn-sm btn-white" target="_blank" type="button" onclick="print_invoicea2('{{ $transection->id }}')">
                        <i class="tio-download"></i> {{\App\CPU\translate('الفاتورةA4')}}
                    </button>
                </td>
                <td class="none">
               @if($transection->is_reversal == 1)
    <span class="badge-koyod badge-danger">
        {{ \App\CPU\translate(' قيد معكوس') }}
    </span>
    @elseif($transection->is_reversal == 2)
     <span class="badge-koyod badge-danger">
        {{ \App\CPU\translate('قيد أصلي تم عكسه') }}
    </span>
@elseif(
    in_array($transection->tran_type, [2, 3, 4, 7, 12, 24, 555, 'asset_sold','salary'])
    || (($transection->asset->status ?? '') == 'closed')
)

    <span class="badge-koyod badge-warning">
        {{ \App\CPU\translate('هذا القيد لا يمكن تعديله') }}
    </span>
@else
    <button class="btn btn-sm btn-warning reverse-btn" data-transaction-id="{{ $transection->id }}">
        {{ \App\CPU\translate('عكس القيد') }}
    </button>
@endif


                </td>
            </tr>
            <tr class="border-top">
                <td colspan="14"></td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Modal تأكيد عملية القيد العكسي -->
<div class="modal fade none" id="reverseConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="reverseConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reverseConfirmationModalLabel">{{ \App\CPU\translate('تأكيد عكس القيد') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ \App\CPU\translate('اغلاق') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        {{ \App\CPU\translate('هل أنت متأكد من عكس هذا القيد؟') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ \App\CPU\translate('لا') }}</button>
        <button type="button" class="btn btn-primary" id="confirmReverse">{{ \App\CPU\translate('نعم') }}</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal إشعار بعد التنفيذ -->
<div class="modal fade none" id="reverseResultModal" tabindex="-1" role="dialog" aria-labelledby="reverseResultModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reverseResultModalLabel">{{ \App\CPU\translate('تنبيه') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ \App\CPU\translate('اغلاق') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="reverseResultMessage">
        <!-- سيتم ملء الرسالة هنا -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ \App\CPU\translate('اغلاق') }}</button>
      </div>
    </div>
  </div>
</div>





                        <div class="page-area none">
                            <table>
                                <tfoot class="border-top">
                                {!! $transections->links() !!}
                                </tfoot>
                            </table>
                        </div>
                        @if(count($transections)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 img-one-tranl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('image_description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها')}}</p>
                            </div>
                        @endif
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
            <div class="modal fade none" id="print-invoice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modal-content1">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('طباعة')}} {{\App\CPU\translate('إيصال')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span class="text-dark" aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row">
                    <div class="col-md-12">
                        <center>
                            <input type="button" class="mt-2 btn btn-primary non-printable"
                                   onclick="printDiv('printableArea')"
                                   value="{{\App\CPU\translate('لو متصل بالطابعة اطبع')}}."/>
                            <a href="{{url()->current()}}"
                               class="mt-2 btn btn-danger non-printable">{{\App\CPU\translate('عودة')}}</a>
                        </center>
                        <hr class="non-printable">
                    </div>
                    <div class="row m-auto" id="printableArea">

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@php
    $account = null;
    if (request()->has('account_id') && !is_null(request()->get('account_id'))) {
        $account = \App\Models\Account::find(request()->get('account_id'));
    }
@endphp
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

<script>
    function printDiv(divId) {
        var printContents = document.getElementById(divId).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;

        // Reload the page to restore functionality
        window.location.reload();
    }
</script>
<!-- jQuery & Bootstrap JS (تأكد من تضمين bootstrap.js) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function(){
        let selectedTransactionId = null;

        // عند الضغط على زر "عكس القيد"
        $('.reverse-btn').click(function(){
            selectedTransactionId = $(this).data('transaction-id');
            // عرض نافذة التأكيد
            $('#reverseConfirmationModal').modal('show');
        });

        // عند تأكيد العملية في النافذة
        $('#confirmReverse').click(function(){
            $('#reverseConfirmationModal').modal('hide');
            // إرسال طلب AJAX لتنفيذ القيد العكسي
            $.ajax({
                url: "{{ route('admin.account.reverseExpenseTransaction') }}",
                method: 'POST',
                data: {
                    transaction_id: selectedTransactionId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    // عند النجاح، نعرض رسالة تفيد بأن القيد العكسي لم يعد قابلًا للتعديل
                    $('#reverseResultMessage').html("{{ \App\CPU\translate('تم تنفيذ القيد العكسي، ولا يمكن إجراء عمليات عليه لاحقًا') }}");
                    $('#reverseResultModal').modal('show');
                    // يمكن تعطيل زر العكس للقيد المعالج
                    $('button.reverse-btn[data-transaction-id="' + selectedTransactionId + '"]').prop('disabled', true);
                },
error: function(xhr) {
    console.error(xhr.responseText);
    // عرض رسالة الخطأ المستلمة من السيرفر في النافذة
    $('#reverseResultMessage').html(xhr.responseText);
    $('#reverseResultModal').modal('show');
}
            });
        });
    });
</script>
<script>
    function printTable() {
        const tableContent = document.getElementById('product-table').innerHTML;

        // Pass supplier data from PHP to JavaScript
         const customerName = @json($account ? $account->account : 'جميع الحسابات');
    const fromDate = @json(request()->get('from'));
    const toDate = @json(request()->get('to'));

        const printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="ar">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>كشف حساب - ${customerName}</title>
                <style>
                    body {
                        font-family: 'Cairo', Arial, sans-serif;
                        margin: 0;
                        background-color: #f9f9f9;
                        color: #333;
                        direction: rtl;
                    }

                    h2, h3 {
                        text-align: center;
                        color: #003366;
                        font-weight: bold;
                    }

                    h2 {
                        font-size: 28px;
                        margin-bottom: 10px;
                    }

                    h3 {
                        font-size: 20px;
                        margin-bottom: 30px;
                    }
.none{
    display:none;
}
                    .header-section {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-bottom: 2px solid #003366;
                        padding-bottom: 10px;
                        margin-bottom: 30px;
                        flex-wrap: wrap; /* Ensures responsiveness */
                    }

                    .header-section .left,
                    .header-section .right,
                    .header-section .logo {
                        width: 32%; /* Ensure each section takes up 32% of the row */
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

                    .badge-warning {
                        background-color: #fbc02d;
                        color: #fff;
                        font-size: 16px;
                        padding: 8px 16px;
                        border-radius: 6px;
                        display: inline-block;
                        margin-bottom: 20px;
                    }

                    .d-flex {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }

                    .font-weight-bold {
                        font-weight: bold;
                        color: #003366;
                    }

                    .final-balance {
                        font-size: 22px;
                        font-weight: bold;
                        color: #388e3c;
                    }

                    .table-wrapper {
                        margin-top: 30px;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        background-color: #fff;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }

                    table, th, td {
                        border: 1px solid #ddd;
                    }

                    th, td {
                        padding: 12px;
                        text-align: center;
                        font-size: 13px;
                    }

                    th {
                        background-color: #f0f0f0;
                        color: #003366;
                    }

                    td {
                        color: #555;
                    }

                    .footer {
                        margin-top: 40px;
                        text-align: center;
                        font-size: 14px;
                        color: #888;
                    }

                    .signatures {
                        margin-top: 40px;
                        font-size: 16px;
                        text-align: center;
                    }

                    .signatures div {
                        margin-bottom: 20px;
                    }

                    .signatures span {
                        display: inline-block;
                        margin-right: 10px;
                    }

                    .signatures .row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 30px;
                    }

                    .signatures p {
                        font-size: 14px;
                        color: #555;
                    }

                    .shop-info {
                        text-align: center;
                        margin-bottom: 20px;
                    }

                    .shop-details {
                        margin: 0 auto;
                        width: 100%;
                        border-collapse: collapse;
                    }

                    .shop-details td {
                        padding: 10px;
                        text-align: left;
                        border: 1px solid #ddd;
                    }

                    .none {
                        display: none;
                    }

                    /* Print styles */
                    @media print {
                        body {
                            font-size: 12px;
                        }

                        @page {
                            margin: 10mm;
                        }

                        .header-section {
                            display: block;
                        }

                    .header-section {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-bottom: 2px solid #003366;
                        padding-bottom: 10px;
                        margin-bottom: 30px;
                        flex-wrap: wrap; /* Ensures responsiveness */
                    }

                    .header-section .left,
                    .header-section .right,
                    .header-section .logo {
                        width: 32%; /* Ensure each section takes up 32% of the row */
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
                        footer {
                            position: fixed;
                            bottom: 0;
                            left: 0;
                            width: 100%;
                            text-align: center;
                            font-size: 12px;
                            color: #555;
                            padding: 10px;
                        }

                        table {
                            border: 1px solid #000;
                        }

                        th, td {
                            border: 1px solid #000;
                            padding: 1px;
                        }

                        .badge-warning {
                            background-color: #fbc02d;
                        }

                        .signatures {
                            display: block;
                        }
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

                <h2> قيود يومية - ${customerName}</h2>
                <h3>من تاريخ ${fromDate} إلى تاريخ ${toDate}</h3>

                <div class="badge-warning">{{ \App\CPU\translate('اجمالي حساب') }}</div>

           @php
    // Initialize final balance
    $final_balance = 0;
    $final_total_out = 0;
    $final_total_in = 0;

    // Check if 'account_id' exists in the request
    if (request()->has('account_id') && !is_null(request()->get('account_id'))) {
        // Find the account by ID
        $account = \App\Models\Account::find(request()->get('account_id'));

        if ($account) {
            $final_balance = round($account->balance, 2);
            $final_total_in = round($account->total_in, 2);
            $final_total_out = round($account->total_out, 2);
        }
    } else {
        // Sum the respective columns in the $transections collection
        $final_total_in = round($transections->sum('total_in'), 2);
        $final_total_out = round($transections->sum('total_out'), 2);
        $final_balance = round($transections->sum('balance'), 2);
    }
@endphp

<div class="row">
    <div class="col-12 style-one-stl">
        <div class="d-flex">
            <span class="font-weight-bold">{{ \App\CPU\translate('مدين') }}:</span>
            <span>
                {{ number_format($final_total_in, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
            </span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 style-one-stl">
        <div class="d-flex">
            <span class="font-weight-bold">{{ \App\CPU\translate('دائن') }}:</span>
            <span>
                {{ number_format($final_total_out, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
            </span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 style-one-stl">
        <div class="d-flex">
            <span class="font-weight-bold">{{ \App\CPU\translate('الرصيد النهائي') }}:</span>
            <span>
                {{ number_format($final_balance, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
            </span>
        </div>
    </div>
</div>

                    </div>
                </div>

                <div class="table-wrapper">
                    ${tableContent}
                </div>

                <div class="final-balance-wrapper">
                    <span class="final-balance">{{ abs($final_balance) . ' ' . \App\CPU\Helpers::currency_symbol() }} 
                    ({{ $final_balance >= 0 ? 'دائن' : 'مدين' }})</span>
                </div>

                <div class="signatures">
                    <div class="row">
                        <span>المحاسب: ...................................</span>
                        <span>المراجع: ...................................</span>
                        <span>المدير العام: ...................................</span>
                    </div>
                    <div>نصادق علي صحة الرصيد المطلوب</div>
                    <div class="row">
                        <div class="col">
                            <div>الاسم: ...................................</div>
                            <div>التوقيع: ...................................</div>
                        </div>
                        <div class="col">
                            <div>التاريخ: ...................................</div>
                        </div>
                    </div>
                </div>

                <footer>
                    <div>© {{ date('Y') }} {{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}</div>
                </footer>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
</script>

 <script>
        $('#from_date,#to_date').change(function() {
            let fr = $('#from_date').val();
            let to = $('#to_date').val();
            if (fr != '' && to != '') {
                if (fr > to) {
                    $('#from_date').val('');
                    $('#to_date').val('');
                    toastr.error('Invalid date range!', Error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            }

        })
    </script>
          <script>
        "use strict";
        function print_invoicea2(order_id) {
            $.get({
                url: '{{url('/')}}/admin/account/invoice_expense/' + order_id,
                dataType: 'json',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    //console.log("success...")
                    $('#print-invoice').modal('show');
                    $('#printableArea').empty().html(data.view);
                },
                complete: function () {
                    $('#loading').hide();
                },
                error: function (error) {
                    console.log(error)
                }
            });
        }
    </script>

    <script src={{asset("public/assets/admin/js/transaction.js")}}></script>
