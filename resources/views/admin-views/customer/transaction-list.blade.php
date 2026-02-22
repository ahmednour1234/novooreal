@extends('layouts.admin.app')

@section('title',\App\CPU\translate('customer_details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush

@section('content')
<div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-print-none pb-2">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="page-header">
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs page-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.customer.view',[$customer['id']]) }}">{{\App\CPU\translate('حجم تعامل عميل')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" href="{{ route('admin.customer.transaction-list',[$customer['id']]) }}">{{\App\CPU\translate('كشف حساب العميل')}}</a>
                                </li>

                            </ul>

                        </div>
                    </div>
                    <div class="d-sm-flex align-items-sm-center">
                        <h4 class="page-header-title">{{\App\CPU\translate('العميل')}} {{\App\CPU\translate('id')}}
                            #{{$customer['id']}}</h4>
                        <span class="ml-2 ml-sm-3">
                        <i class="tio-date-range">
                        </i> {{\App\CPU\translate('joined_at')}} : {{date('d M Y H:i:s',strtotime($customer['created_at']))}}
                        </span>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row" id="">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header">
                        <div class="w-100">
                            <h3>{{\App\CPU\translate('كشف حساب العميل')}}
                                <span class="badge badge-soft-dark ml-2">{{$transactions->total()}}</span>
                            </h3>
<form action="{{ url()->current() }}" method="GET">
    <div class="row">
        <!-- Account Filter -->

        <!-- Transaction Type Filter -->
        <div class="col-lg-3 mb-3 mb-lg-0"> 
            <div class="form-group">
                <label class="input-label" for="tran_type">{{ \App\CPU\translate('نوع') }}</label>
                <select id="tran_type" name="tran_type" class="form-control js-select2-custom">
                    <option value="">---{{ \App\CPU\translate('select') }}---</option>
                    <option value="4" {{ $tran_type == 4 ? 'selected' : '' }}>{{ \App\CPU\translate('مبيعات') }}</option>
                    <option value="7" {{ $tran_type == 7 ? 'selected' : '' }}>{{ \App\CPU\translate('مردود مبيعات') }}</option>
                    <option value="100" {{ $tran_type == 100 ? 'selected' : '' }}>{{ \App\CPU\translate('سند صرف') }}</option>
                    <option value="200" {{ $tran_type == 200 ? 'selected' : '' }}>{{ \App\CPU\translate('سند قبض') }}</option>
                    <option value="1" {{ $tran_type == 1 ? 'selected' : '' }}>{{ \App\CPU\translate('رصيد افتتاحي') }}</option>
                </select>
            </div>
        </div>
        
        <!-- From Date Filter -->
        <div class="col-lg-3 mb-3 mb-lg-0">
            <div class="form-group">
                <label class="input-label" for="from_date">{{ \App\CPU\translate('من تاريخ') }}</label>
                <input type="date" id="from_date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
        </div>
        
        <!-- To Date Filter -->
        <div class="col-lg-3 mb-3 mb-lg-0">
            <div class="form-group">
                <label class="input-label" for="to_date">{{ \App\CPU\translate('إلى تاريخ') }}</label>
                <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="text-center col-12 col-sm-2 mt-sm-5">
            <button class="btn btn-success col-12">{{ \App\CPU\translate('بحث') }}</button>
        </div>
    </div>
</form>
                        </div>
                    </div>
                    <!-- Table -->
                      <button class="btn btn-primary final none col-12" onclick="printTable()">
        {{ \App\CPU\translate('طباعة') }}
    </button>
                    <div class="table-responsive datatable-custom" id="product-table">
                   <table
    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light">
        <tr>
            <th>{{ \App\CPU\translate('#') }}</th>
            <th>{{ \App\CPU\translate('تاريخ المستند') }}</th>
            <th>{{ \App\CPU\translate('رقم المستند') }}</th>
            <th>{{ \App\CPU\translate('نوع المستند') }}</th>
            <th>{{ \App\CPU\translate('البيان') }}</th>
            <th class="none">{{ \App\CPU\translate('الكاتب') }}</th>
            <th  class="none">{{ \App\CPU\translate('الحساب') }}</th>
            <th>{{ \App\CPU\translate('العملة') }}</th>
            <th>{{ \App\CPU\translate('الفواتير نقدا وشبكة') }}</th>
                        <th>{{ \App\CPU\translate('مدين') }}</th>

            <th>{{ \App\CPU\translate('دائن') }}</th>
            <th>{{ \App\CPU\translate('الرصيد') }}</th>
            <th class="none">{{ \App\CPU\translate('صورة') }}</th>
                        <th class="none">{{ \App\CPU\translate('طباعة') }}</th>

        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $key => $transaction)
            <tr>
                <td>{{ $transactions->firstItem() + $key }}</td>
                <td>{{ $transaction->date }}</td>
                <td>{{ $transaction->id }}</td>
                <td>
                
                    @if ($transaction->tran_type == 4)
                        <span class="badge badge-danger">مبيعات</span>
          @elseif ($transaction->tran_type == 0 || $transaction->tran_type == 1)
                        <span class="badge badge-info">رصيد افتتاحي</span
                    @elseif ($transaction->tran_type == 7)

                        <span class="badge badge-info">مرتجع مبيعات</span>
                    @elseif ($transaction->tran_type == 12)
                        <span class="badge badge-warning">مشتريات</span>
                    @elseif ($transaction->tran_type == 24)
                        <span class="badge badge-success">مرتجع مشتريات</span>
                    @elseif ($transaction->tran_type == 100)
                        <span class="badge badge-soft-warning">سند صرف</span>
                    @elseif ($transaction->tran_type == 200)
                        <span class="badge badge-soft-success">سند قبض</span>
                    @elseif ($transaction->tran_type == 100)
                        <span class="badge badge-soft-success">سند صرف</span>
                                     @elseif($transaction->tran_type == 30)
        <span class="badge badge-soft-success">خصم مكتسب</span>
                    @endif
                </td>
                <td>{{ $transaction->description }}</td>
                <td class="none">{{ $transaction->seller->email ?? '' }}</td>
                <td class="none">{{ $accountcustomer->account }}</td>
                <td>{{ \App\CPU\Helpers::currency_symbol() }}</td>
              @if(!in_array($transaction->tran_type, [4, 7]))
      <td>0.00</td>
@else
    <td>{{ number_format($transaction->amount, 2) }}</td>
@endif
@if($customer->account_id==$transaction->account_id)
<td>{{ number_format($transaction->credit, 2) }}</td>
<td>{{ number_format($transaction->debit, 2) }}</td>
<td>{{ number_format($transaction->balance, 2) }}</td>
@else
<td>{{ number_format($transaction->credit_account, 2) }}</td>
<td>{{ number_format($transaction->debit_account, 2) }}</td>
<td>{{ number_format($transaction->balance_account, 2) }}</td>
@endif
                <td class="none">
                    <img class="navbar-brand-logo" id="imog"
                         src="{{ asset('storage/app/public/' . $transaction->img) }}" alt="Logo">
                </td>
                    <td class="none">
                    <button class="btn btn-sm btn-white" target="_blank" type="button" onclick="print_invoicea2('{{ $transaction->id }}')">
                        <i class="tio-download"></i> {{\App\CPU\translate('الفاتورةA4')}}
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

                        <!-- Footer -->
                        <div class="page-area none">
                            <table>
                                <tfoot class="border-top links" id="links">
                                    {!! $transactions->links() !!}
                                </tfoot>
                            </table>
  
                        </div>
                        @if(count($transactions)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-tl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها')}}</p>
                            </div>
                        @endif
                        <!-- End Footer -->
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">{{\App\CPU\translate('العميل')}}</h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    @if($customer)
<div class="card-body">
    <div class="media align-items-center" href="javascript:">
        <div class="avatar avatar-circle mr-3">
            <img
                class="avatar-img"
                onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                src="{{asset('storage/app/public/customer/'.$customer->image)}}"
                alt="{{\App\CPU\translate('image_description')}}">
        </div>
        <div class="media-body">
            <span class="text-body text-hover-primary">{{$customer['name']}}</span>
        </div>
    </div>

    <hr>

    <div class="media align-items-center" href="javascript:">
        <div class="icon icon-soft-info icon-circle mr-3">
            <i class="tio-shopping-basket-outlined"></i>
        </div>
        <div class="media-body">
            <span class="text-body text-hover-primary">{{ $transactions->count() }} {{\App\CPU\translate('عدد العمليات')}}</span>
        </div>
    </div>
    
<div class="media align-items-center mt-1" href="javascript:">
    <div class="icon icon-soft-info icon-circle mr-3">
        <i class="tio-money"></i>
    </div>
<div class="media-body">
    @php
        // Calculate total balance and credit for the customer
        $totalBalance = $customer->balance+$customer->discount; // Assuming balance is a single value
        $totalCredit = $customer->credit;   // Assuming credit is a single value

        $netAmount = $totalBalance - $totalCredit; // Calculate net amount
    @endphp

    <!-- Display total balance with appropriate label for creditor/debtor -->
    <span class="text-body text-hover-primary">
        {{ number_format(abs($totalBalance), 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
        {{ $totalBalance >= 0 ? \App\CPU\translate('دائن') : \App\CPU\translate('مدين') }}
    </span>

    <span class="text-body text-hover-primary"> - </span>

    <!-- Display total credit with appropriate label for creditor/debtor -->
    <span class="text-body text-hover-primary">
        {{ number_format(abs($totalCredit), 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
        {{ $totalCredit >= 0 ? \App\CPU\translate('مدين') : \App\CPU\translate('دائن') }}
    </span>

    <span class="text-body text-hover-primary"> = </span>

    <!-- Display net amount with conditional label for net debtor/creditor -->
    <span class="text-body text-hover-primary">
        {{ number_format(abs($netAmount), 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
        {{ $netAmount >= 0 ? \App\CPU\translate('صافي دائن') : \App\CPU\translate('صافي مدين') }}
    </span>
</div>
</div>

    @if($customer->id != 0)
        <hr>

        <div class="d-flex justify-content-between align-items-center">
            <h5>{{\App\CPU\translate('معلومات التواصل')}}</h5>
        </div>

        <ul class="list-unstyled list-unstyled-py-2">
            <li>
                <i class="tio-android-phone-vs mr-2"></i>
                {{$customer['mobile']}}
            </li>
            @if ($customer['email'])
                <li>
                    <i class="tio-online mr-2"></i>
                    {{$customer['email']}}
                </li>
            @endif
        </ul>

        <hr>

        <div class="d-flex justify-content-between align-items-center">
            <h5>{{\App\CPU\translate('العنوان')}}</h5>
        </div>
        <ul class="list-unstyled list-unstyled-py-2">
            <li>{{\App\CPU\translate('المقاطعة')}}: {{$customer['state']}}</li>
            <li>{{\App\CPU\translate('المدينة')}}: {{$customer['city']}}</li>
            <li>{{\App\CPU\translate('كود المدينة')}}: {{$customer['zip_code']}}</li>
            <li>{{\App\CPU\translate('العنوان')}}: {{$customer['address']}}</li>
        </ul>


        </div>
    @endif
</div>
                @endif
                <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- End Row -->
    </div>
<div class="modal fade" id="print-invoice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('طباعة')}} {{\App\CPU\translate('الفواتير')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row font-one-tl">
                    <div class="col-md-12">
                        <center>
                            <input type="button" class="btn btn-primary non-printable" onclick="printDiv('printableArea')"
                                value="{{\App\CPU\translate('Proceed, If thermal printer is ready')}}."/>
                            <a href="{{url()->previous()}}" class="btn btn-danger non-printable">{{\App\CPU\translate('عودة')}}</a>
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
<script>
    function printTable() {
        const tableContent = document.getElementById('product-table').innerHTML;

        // Pass supplier data from PHP to JavaScript
        const customerName = @json($customer->name);
        const fromDate = @json(request()->get('from_date'));
        const toDate = @json(request()->get('to_date'));

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
                            font-size: 15px;
                        }

                        @page {
                            margin: 5mm;
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
                                                        font-size: 15px;

                        }

                        th, td {
                            border: 1px solid #000;
                            padding: 1px;
                                                        font-size: 15px;

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
    display: none;
}
.none{
    display:none;
}             </style>
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

                <h2>كشف حساب - ${customerName}</h2>
                <h3>من تاريخ ${fromDate} إلى تاريخ ${toDate}</h3>

                <div class="badge-warning">{{ \App\CPU\translate('اجمالي حساب عميل') }}</div>

                <div class="row">
                    <div class="col-12 style-one-stl">
                        <div class="d-flex">
                            <span class="font-weight-bold">{{ \App\CPU\translate('دائن') }}:</span>
<span>
    {{ number_format($customer->balance + $customer->discount, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 style-one-stl">
                        <div class="d-flex">
                            <span class="font-weight-bold">{{ \App\CPU\translate('مدين') }}:</span>
<span>
    {{ $customer->credit ? number_format($customer->credit, 2) . ' ' . \App\CPU\Helpers::currency_symbol() : '0.00 ' . \App\CPU\Helpers::currency_symbol() }}
</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 style-one-stl">
                        <div class="d-flex">
                            <span class="font-weight-bold">{{ \App\CPU\translate('الرصيد النهائي') }}:</span>
                          @php
    $final_balance = round($customer->balance + $customer->discount - $customer->credit, 2);
@endphp

                            <span class="final-balance">
                                {{ abs($final_balance) . ' ' . \App\CPU\Helpers::currency_symbol() }} 
                                ({{ $final_balance >= 0 ? 'دائن' : 'مدين' }})
                            </span>
                        </div>
                    </div>
                </div>

                <div class="">
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
        "use strict";
        function print_invoicea2(order_id) {
            $.get({
                url: '{{url('/')}}/admin/customer/invoice_expense/' + order_id,
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

    <script src={{asset("public/assets/admin/js/global.js")}}></script>
