@extends('layouts.admin.app')
@section('title','installments List')
@push('css_or_js')
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
    <!-- Include select2 CSS -->


@endpush

@section('content')
<style>
    .table td, .table th {
    border-bottom: 1px solid #ddd; /* Add a border between rows */
}

tfoot td {
    border-top: 2px solid #000; /* Optional: add a thicker line between the body and the footer */
}

</style>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="">
            <div class="row align-items-center mb-3">
                <div class="col-sm">
                    <h1 class="page-header-title text-capitalize">{{\App\CPU\translate('فاتورة')}} {{\App\CPU\translate('تحصيلات')}}
                        <span
                            class="badge badge-soft-dark ml-2">{{$installments->total()}}</span></h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row justify-content-between align-items-center flex-grow-1">
                    <div class="col-sm-8 col-md-6 col-lg-6 mb-3 mb-lg-0">


            <form action="{{ url()->current() }}" method="GET" class="p-4 border rounded bg-white shadow-sm w-100">
                <!-- First Row -->
                <div class="form-row mb-4">
                    <!-- Search by Order ID -->
                    <div class="col-md-6">
                        <label class="font-weight-bold text-secondary">{{ \App\CPU\translate('بحث') }}</label>
                        <div class="input-group">
                            <input type="search" name="search" class="form-control" placeholder="{{ \App\CPU\translate('أدخل رقم الفاتورة ') }}" aria-label="Search" value="{{ $search }}">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter by Region -->
                    <div class="col-md-6">
                        <label class="font-weight-bold text-secondary">{{ \App\CPU\translate('المنطقة') }}</label>
                        <select name="region_id" class="form-control custom-select select2">
                            <option value="">{{ \App\CPU\translate('اختر المنطقة') }}</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ $regionId == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Second Row -->
                <div class="form-row mb-4">
                    <!-- Filter by Seller -->
                    <div class="col-md-6">
                        <label class="font-weight-bold text-secondary">{{ \App\CPU\translate('المندوب') }}</label>
                        <select name="seller_id" class="form-control custom-select select2">
                            <option value="">{{ \App\CPU\translate('اختر المندوب') }}</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ $seller->id == $seller_id ? 'selected' : '' }}>
                                    {{ $seller->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter by Customer -->
                    <div class="col-md-6">
                        <label class="font-weight-bold text-secondary">{{ \App\CPU\translate('العميل') }}</label>
                        <select name="customer_id" class="form-control custom-select select2">
                            <option value="">{{ \App\CPU\translate('اختر العميل') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $customer->id == $customer_id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                                         <div class="col-md-12 mt-4">
                        <label class="font-weight-bold text-secondary">{{ \App\CPU\translate('الفرع') }}</label>
                        <select name="branch_id" class="form-control custom-select select2">
                            <option value="">{{ \App\CPU\translate('اختر الفرع') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch->id == $branch_id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <!-- Date Range -->
                <div class="form-row mb-4">
                    <!-- From Date -->
                    <div class="col-md-6">
                        <label class="font-weight-bold text-secondary">{{ \App\CPU\translate('من تاريخ') }}</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}" aria-label="From Date">
                    </div>

                    <!-- To Date -->
                    <div class="col-md-6">
                        <label class="font-weight-bold text-secondary">{{ \App\CPU\translate('إلى تاريخ') }}</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}" aria-label="To Date">
                    </div>
                </div>

                <!-- Buttons Row -->
                <div class="text-center d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary btn-lg w-45">
                        {{ \App\CPU\translate('تطبيق الفلاتر') }}
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg w-45" onclick="printTable()">
                        {{ \App\CPU\translate('طباعة') }}
                    </button>
                </div>
                
            </form>

<!-- Initialize select2 -->

                    </div>

                    <div class="col-lg-6"></div>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->
    <div class="row mb-3">

   
    
    </div>

            <!-- Table -->
            <div class="table-responsive "id="product-table">
                <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">
                اسم المندوب
            </td>
            <td colspan="3" style="text-align: center; font-weight: bold;">
 {{$sellerw->f_name?? ''}}
</td>
        </tr>
          <tr>
                        <td colspan="3" style="text-align: center; font-weight: bold;">
                            اسم الفرع
                        </td>
                        <td colspan="3" style="text-align: center; font-weight: bold;">
                            {{$branchw->name ?? ''}}
                        </td>
                    </tr>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">
                اسم العميل
            </td>
            <td colspan="3" style="text-align: center; font-weight: bold;">
 {{$customerw->name ?? ''}}
</td>
        </tr>
          <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">
                 تاريخ 
            </td>
            <td colspan="3" style="text-align: center; font-weight: bold;">
 {{$fromDate}} -  {{$toDate}}

</td>

        </tr>
         <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">
                 اجمالي المبالغ المحصلة 
            </td>
            <td colspan="3" style="text-align: center; font-weight: bold;">
 {{ number_format($totalAmount, 2) }}
</td>

        </tr>
    </table>
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                >
                    <thead class="thead-light">
                    <tr>
                        <th class="">
                            {{\App\CPU\translate('#')}}
                        </th>
                        <th>{{\App\CPU\translate('اسم المندوب')}}</th>
                        <th>{{\App\CPU\translate('اسم العميل')}}</th>
                        <th>{{\App\CPU\translate('المنطقة')}}</th>
                        <th>{{\App\CPU\translate('المبلغ')}}</th>
                        <th>{{\App\CPU\translate('الملاحظة')}}</th>
                         <th>{{\App\CPU\translate('التاريخ')}}</th>
        <th >{{\App\CPU\translate('رقم التحصيل')}}</th>
                <th class="none">{{\App\CPU\translate('صورة')}}</th>
                        <th class="none">{{\App\CPU\translate('إجراءات')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($installments as $key=>$installment)
                        <tr class="status-{{$installment['order_status']}} class-all">
                            <td class="">
                                {{$key+$installments->firstItem()}}
                            </td>
                           <td>{{ optional($installment->seller)->f_name }} {{ optional($installment->seller)->l_name }}</td>
                    <td>{{ optional($installment->customer)->name }}</td>
                                        <td>{{ $installment->customer->regions->name ??''  }}</td>
                            <td>{{ number_format($installment->total_price, 2) }}</td>
                                                <td>{{ $installment->note }}</td>
                              <td>{{date('d M Y',strtotime($installment['created_at']))}}</td>
                                                                              <td>{{ $installment->id }}</td>
                                  <td class="none">
    <img src="{{ asset('storage/app/public/'.$installment['img']) }}" alt="Image Description" style="max-width: 50px; max-height: 50px;">
</td>
                            <td class="none">
                                <button class="btn btn-sm btn-white" target="_blank" type="button"
                                        onclick="print_invoice('{{$installment->id}}')"><i
                                        class="tio-download"></i> {{\App\CPU\translate('طباعة')}}</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
         
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer">
                <!-- Pagination -->
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm-auto">
                        <div class="d-flex justify-content-center justify-content-sm-end">
                            <!-- Pagination -->
                            {!! $installments->links() !!}
                        </div>
                    </div>
                </div>
                <!-- End Pagination -->
            </div>
            @if(count($installments)==0)
                <div class="text-center p-4">
                    <img class="mb-3 img-one-ol" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg"
                         alt="Image Description">
                    <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                </div>
        @endif
        <!-- End Footer -->
        </div>
        <!-- End Card -->
    </div>

    <div class="modal fade" id="print-invoice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modal-content1">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('طباعة')}} {{\App\CPU\translate('الفاتورة')}}</h5>
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


        const printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{{ \App\CPU\translate('تقرير التحصيلات') }}</title>
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
                    
                <h2>{{ \App\CPU\translate('تقرير   التحصيلات') }}</h2>
                
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

<!-- Include jQuery and select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: 'اختر...',
        allowClear: true
    });
});
</script>
    <script>
        "use strict";
        function print_invoice(order_id) {
            $.get({
                url: '{{url('admin/pos/installments/invoice')}}/' + order_id,
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
                    console.log(error.responseText);
                },
            });
        }
    </script>

    <script src={{asset("public/assets/admin/js/global.js")}}></script>
