@extends('layouts.admin.app')
@section('title','Stock History')
@push('css_or_js')
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
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
                    <h1 class="page-header-title text-capitalize">{{\App\CPU\translate('رحلات')}} {{\App\CPU\translate('المناديب')}}
                        <span
                            class="badge badge-soft-dark ml-2">{{$orders->total()}}</span></h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row justify-content-between align-items-center flex-grow-1">
                    <div class="col-sm-8 col-md-6 col-lg-12 mb-lg-12">
<form action="{{ url()->current() }}" method="GET">
    <div class="row align-items-center">
        <!-- Search by Delegate Name -->
        <div class="col-md-3 mb-3">
            <div class="input-group input-group-lg">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <i class="tio-search"></i>
                    </div>
                </div>
                <input type="search" name="search" class="form-control" placeholder="{{ \App\CPU\translate('بحث باسم المندوب') }}" aria-label="Search" value="{{ $search }}">
            </div>
        </div>

        <!-- Search by From Date -->
        <div class="col-md-3 mb-3">
            <div class="input-group input-group-lg">
                <input type="date" name="from_date" class="form-control" placeholder="{{ \App\CPU\translate('من التاريخ') }}" value="{{ $fromDate }}" aria-label="From Date">
            </div>
        </div>

        <!-- Search by To Date -->
        <div class="col-md-3 mb-3">
            <div class="input-group input-group-lg">
                <input type="date" name="to_date" class="form-control" placeholder="{{ \App\CPU\translate('إلى التاريخ') }}" value="{{ $toDate }}" aria-label="To Date">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="col-md-3 mb-3 text-md-right text-center">
            <button type="submit" class="btn btn-primary btn-lg px-4 py-3">
                {{ \App\CPU\translate('بحث') }}
            </button>
        </div>
    </div>
</form>

                    </div>

                    <div class="col-lg-6"></div>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->
            
            <!-- Table -->
            <div class="table-responsive ">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                >
                    <thead class="thead-light">
                    <tr>
                        <th class="">
                            {{\App\CPU\translate('#')}}
                        </th>
                        <th class="table-column-pl-0">{{\App\CPU\translate('رقم الرحلة')}}</th>
                        <th>{{\App\CPU\translate('اسم المندوب')}}</th>
                        <th>{{\App\CPU\translate('كود المستودع')}}</th>
                        <th>{{\App\CPU\translate('تاريخ انتهاء الرحلة')}}</th>
                        <th>{{\App\CPU\translate('طباعة')}}</th>
                        <th>{{\App\CPU\translate('A4طباعة')}}</th>

                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($orders as $key=>$order)
                        <tr class="status-{{$order['order_status']}} class-all">
                            <td class="">
                                {{$key+$orders->firstItem()}}
                            </td>
                            <td class="table-column-pl-0">
                                <a class="text-primary" href="#" onclick="print_invoice('{{$order->id}}')">{{$order['id']}}</a>
                            </td>
                            <td>
                                  {{ optional($order->seller)->f_name }} {{ optional($order->seller)->l_name }}
                            </td>
                            <td>
                                {{ optional(\App\Models\Store::where('store_id', $order->seller->vehicle_code)->first())->store_code }}
                            </td>
                            
                                 <td>{{date('d M Y',strtotime($order['created_at']))}}</td>
                            <td>
                                <button class="btn btn-sm btn-white" target="_blank" type="button"
                                        onclick="print_invoice('{{$order->id}}')"><i
                                        class="tio-download"></i> {{\App\CPU\translate('اطبع')}}</button>
                            </td>
                              <td>
                                <button class="btn btn-sm btn-white" target="_blank" type="button"
                                        onclick="print_invoicea2('{{$order->id}}')"><i
                                        class="tio-download"></i> {{\App\CPU\translate('اطبع')}}</button>
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
                            {!! $orders->links() !!}
                        </div>
                    </div>
                </div>
                <!-- End Pagination -->
            </div>
            @if(count($orders)==0)
                <div class="text-center p-4">
                    <img class="mb-3 img-one-ol" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg"
                         alt="Image Description">
                    <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها')}}</p>
                </div>
        @endif
        <!-- End Footer -->
        </div>
        <!-- End Card -->
    </div>

<div class="modal fade col-md-12" id="print-invoice" tabindex="-1">
    <div class="modal-dialog modal-lg"> <!-- Use modal-lg for a larger modal -->
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
                    <!-- Content for printing will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

    <script>
        "use strict";
        function print_invoice(order_id) {
            $.get({
                url: '{{url('/')}}/admin/pos/stocks/invoice/' + order_id,
                dataType: 'json',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    console.log(data)
                    //console.log("success...")
                    $('#print-invoice').modal('show');
                    $('#printableArea').empty().html(data.view);
                },
                error: function (error) {
                    console.log(error)
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }
    </script>
     <script>
        "use strict";
        function print_invoicea2(order_id) {
            $.get({
                url: '{{url('/')}}/admin/pos/stocks/invoicea2/' + order_id,
                dataType: 'json',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    console.log(data)
                    //console.log("success...")
                    $('#print-invoice').modal('show');
                    $('#printableArea').empty().html(data.view);
                },
                error: function (error) {
                    console.log(error)
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }
    </script>

    <script src={{asset("public/assets/admin/js/global.js")}}></script>
