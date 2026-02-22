@extends('layouts.admin.app')

@section('title', \App\CPU\translate('supplier_details'))

@push('css_or_js')
@endpush

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div>
                <h1 class="page-header-title">{{ $supplier->name }}</h1>
            </div>
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('admin.supplier.view', [$supplier['id']]) }}">{{ \App\CPU\translate('تفاصيل المورد') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('admin.supplier.products', [$supplier['id']]) }}">{{ \App\CPU\translate('قائمة المنتجلت') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active"
                            href="{{ route('admin.supplier.transaction-list', [$supplier['id']]) }}">{{ \App\CPU\translate('كشاف حساب المورد') }}</a>
                    </li>
                </ul>

            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-7 mt-2">
                <div class="card">

              <div class="card-body">
    <div class="row">
        <!-- Title for Total Supplier Account -->
        <span class="font-one-stl">{{ \App\CPU\translate('اجمالي حساب المورد') }}</span>
        
        <!-- Supplier Due Amount (مدين) -->
        <div class="col-12 style-one-stl mt-2">
            <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">{{ \App\CPU\translate('دائن') }}:</span>
                <span>
                    {{ $supplier->due_amount   ? $supplier->due_amount . ' ' . \App\CPU\Helpers::currency_symbol() : '0 ' . \App\CPU\Helpers::currency_symbol() }}
                </span>
            </div>
        </div>

        <!-- Supplier Credit Amount (دائن) -->
        <div class="col-12 style-one-stl mt-2">
            <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">{{ \App\CPU\translate('مدين') }}:</span>
                <span>
                    {{ $supplier->credit+$supplier->discount . ' ' . \App\CPU\Helpers::currency_symbol()  }}
                </span>
            </div>
        </div>
        

        <!-- Final Balance (Total = Due - Credit) -->
        <div class="col-12 style-one-stl mt-2">
            <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">{{ \App\CPU\translate('الرصيد النهائي') }}:</span>
                @php
                    $final_balance = $supplier->due_amount - $supplier->credit-$supplier->discount;
                @endphp
                <span>
                    {{ abs($final_balance) . ' ' . \App\CPU\Helpers::currency_symbol() }} 
                    ({{ $final_balance >= 0 ? 'دائن' : 'مدين' }})
                </span>
            </div>
        </div>
    </div>
</div>

                </div>
            </div>
            <!--<div class="col-12 col-md-5 mt-2">-->
            <!--    <div class="card">-->
            <!--        <div class="card-body">-->
            <!--            <div class="row">-->
            <!--                <div class="col-12 mb-1">-->
            <!--                    <a class="col-12 btn btn-info" onclick="add_new_purchase({{ $supplier->id }});"-->
            <!--                        data-toggle="modal"-->
            <!--                        data-target="#add-new-purchase">{{ \App\CPU\translate('اضافة حساب جديد') }}</a>-->
            <!--                </div>-->
            <!--                <div class="col-12">-->
            <!--                    <a class="col-12 btn btn-success" onclick="payment_due({{ $supplier->id }});"-->
            <!--                        data-toggle="modal" data-target="#payment-due">{{ \App\CPU\translate('دفع دفعة للمورد') }}</a>-->
            <!--                </div>-->

            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
        </div>

    </div>
    <div class="content container-fluid">
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-12 col-lg-5 mt-2 mb-lg-0">
                                <h3>{{ \App\CPU\translate('كشف الحساب') }}
                                    <span class="badge badge-soft-dark ml-2">{{ $transections->total() }}</span>
                                </h3>
                            </div>
                            <div class="col-12  mt-2">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="row">
                                        <div class="col-12 col-md-5">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ \App\CPU\translate('من تاريخ') }}
                                                </label>
                                                <input id="start_date" type="date" name="from" class="form-control"
                                                    value="{{ $from }}" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ \App\CPU\translate('الي تاريخ') }} </label>
                                                <input id="end_date" type="date" name="to" class="form-control"
                                                    value="{{ $to }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2 mt-md-5">
                                            <button href="" class="btn btn-success">
                                                {{ \App\CPU\translate('بحث') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

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
            <th class="none">{{ \App\CPU\translate('الحساب') }}</th>
            <th>{{ \App\CPU\translate('العملة') }}</th>
            <th>{{ \App\CPU\translate('المشتريات نقدا') }}</th>
                        <th>{{ \App\CPU\translate('مدين') }}</th>

            <th>{{ \App\CPU\translate('دائن') }}</th>
            <th>{{ \App\CPU\translate('الرصيد') }}</th>
            <th class="none">{{ \App\CPU\translate('صورة') }}</th>
                        <th class="none">{{ \App\CPU\translate('طباعة') }}</th>

        </tr>
    </thead>
    <tbody>
        @foreach($transections as $key => $transaction)
            <tr>
                <td>{{ $transections->firstItem() + $key }}</td>
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
                <td class="none">{{ $accountsupplier->account }}</td>
                <td>{{ \App\CPU\Helpers::currency_symbol() }}</td>
       @if(!in_array($transaction->tran_type, [4, 7,12,24]))
      <td>0.00</td>
@else
    <td>{{ number_format($transaction->amount, 2) }}</td>
@endif            
@if($supplier->account_id==$transaction->account_id)
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
                                    {!! $transections->links() !!}
                                </tfoot>
                            </table>
            
                        </div>
                        @if(count($transections)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-tl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها')}}</p>
                            </div>
                        @endif
                        <!-- End Footer -->
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
    <div class="modal fade" id="add-new-purchase" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ \App\CPU\translate('اضافة حساب جديد') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.supplier.add-new-purchase') }}" method="post" class="row">
                        @csrf
                        <input type="hidden" id="supplier_id" name="supplier_id">
                        <div class="form-group col-sm-6">
                            <label for="">{{ \App\CPU\translate('المبلغ الحساب') }}</label>
                            <input id="purchased_amount" type="number" step=".01" min="0"
                                class="form-control" name="purchased_amount" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="">{{ \App\CPU\translate('المبلغ الذي سيتم دفعه') }}</label>
                            <input id="paid_amount" onkeyup="due_calculate();" type="number" step=".01"
                                min="0" class="form-control" name="paid_amount" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="">{{ \App\CPU\translate('المتبقي') }}</label>
                            <input id="due_amount" type="number" step=".01" min="0" class="form-control"
                                name="due_amount" required readonly>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{ \App\CPU\translate('الحساب الذي سيتم الدفع من خلاله') }} </label>
                                <select id="payment_account_id" name="payment_account_id" class="form-control" required>
                                    <option value="">---{{ \App\CPU\translate('select') }}---</option>
                                    @foreach ($accounts as $account)
                                            <option value="{{ $account['id'] }}" class="account">
                                                {{ $account['account'] }} </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary"
                                type="submit">{{ \App\CPU\translate('submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="payment-due" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ \App\CPU\translate('دفع دفعة للمورد') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.supplier.pay-due') }}" method="post" class="row">
                        @csrf
                        <input type="hidden" id="due_pay_supplier_id" name="supplier_id">
                        <div class="form-group col-sm-6">
                            <label for="">{{ \App\CPU\translate('اجمالي حساب المورد') }}</label>
                            <input id="total_due_amount" type="number" step=".01" min="0"
                                class="form-control" name="total_due_amount" value="{{ $supplier->due_amount }}"
                                required readonly>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="">{{ \App\CPU\translate('المبلغ المدفوع') }}</label>
                            <input id="pay_amount" onkeyup="due_remain();" type="number" step=".01" min="0.1"
                                max="{{ $supplier->due_amount }}" class="form-control" name="pay_amount" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="">{{ \App\CPU\translate('المبلغ المتبقي') }}</label>
                            <input id="remaining_due_amount" type="number" step=".01" min="0"
                                class="form-control" name="remaining_due_amount" required readonly>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{ \App\CPU\translate('الحساب الذي سيتم الدفع من خلاله') }} </label>
                                <select id="payment_account_id" name="payment_account_id" class="form-control" required>
                                    <option value="">---{{ \App\CPU\translate('اختار') }}---</option>
                                    @foreach ($accounts as $account)
                                            <option value="{{ $account['id'] }}" class="account">
                                                {{ $account['account'] }} </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary"
                                type="submit">{{ \App\CPU\translate('حفظ') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
            <div class="modal fade" id="print-invoice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modal-content1">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('طباعة')}} {{\App\CPU\translate('فاتورة')}}</h5>
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
                            <a href="{{url()->previous()}}"
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
<script>
    function printTable() {
        const tableContent = document.getElementById('product-table').innerHTML;

        // Pass supplier data from PHP to JavaScript
        const customerName = @json($supplier->name);
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

                <h2>كشف حساب - ${customerName}</h2>
                <h3>من تاريخ ${fromDate} إلى تاريخ ${toDate}</h3>

                <div class="badge-warning">{{ \App\CPU\translate('اجمالي حساب عميل') }}</div>

                <div class="row">
                    <div class="col-12 style-one-stl">
                        <div class="d-flex">
                            <span class="font-weight-bold">{{ \App\CPU\translate('دائن') }}:</span>
                            <span>{{ $supplier->due_amount   ? $supplier->due_amount . ' ' . \App\CPU\Helpers::currency_symbol() : '0 ' . \App\CPU\Helpers::currency_symbol() }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 style-one-stl">
                        <div class="d-flex">
                            <span class="font-weight-bold">{{ \App\CPU\translate('مدين') }}:</span>
                            <span>{{ $supplier->credit+$supplier->discount . ' ' . \App\CPU\Helpers::currency_symbol() }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 style-one-stl">
                        <div class="d-flex">
                            <span class="font-weight-bold">{{ \App\CPU\translate('الرصيد النهائي') }}:</span>
                               @php
                    $final_balance = $supplier->due_amount - $supplier->credit-$supplier->discount;
                @endphp
                            <span class="final-balance">
                                {{ abs($final_balance) . ' ' . \App\CPU\Helpers::currency_symbol() }} 
                                ({{ $final_balance >= 0 ? 'دائن' : 'مدين' }})
                            </span>
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
        "use strict";
        function print_invoicea2(order_id) {
            $.get({
                url: '{{url('/')}}/admin/supplier/invoice_expense/' + order_id,
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
