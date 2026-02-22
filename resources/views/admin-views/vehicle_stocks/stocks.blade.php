@extends('layouts.admin.app')

@section('title',\App\CPU\translate('product_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush

@section('content')
<style>
    .car{
        background-color: #001B63;
    }
    .color-one {
        background-color: #001B63;
}
</style>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="row align-items-center mb-3">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize"><i
                        class="tio-filter-list"></i> {{\App\CPU\translate('المنتجات الحالية اللي في سيارة')}}
                    <span class="badge badge-soft-dark ml-2">{{$stocks->count()}}</span>
                </h1>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th>{{\App\CPU\translate('المنتج')}}</th>
                                <th>{{\App\CPU\translate('كود المنتج')}}</th>
                                <th>{{\App\CPU\translate('رصيد أول فترة')}}</th>
                                <th>{{\App\CPU\translate('رصيد الحالي')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($stocks->get() as $key => $stock)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        {{ $stock->product->name }}
                                    </td>
                                       <td>
                                        {{ $stock->product->product_code }}
                                    </td>
                                    <td>
                                                                                    @php
                                                $mainStock = (float) ($stock['main_stock'] ?? 0);
                                                $unitValue = (float) ($stock->product->unit_value ?? 0);
                                                $isDecimal = strpos((string)$mainStock, '.') !== false;
                                                $result = $mainStock * $unitValue;
                                            @endphp

                                            @if ($isDecimal)
                                                {{ number_format($result, 2) }}
                                                {{ $stock->product->unit->subUnits->first()?->name ?? '' }}
                                            @else
                                                {{ $mainStock }}
                                                {{ $stock->product->unit->unit_type ?? '' }}
                                            @endif
                                    </td>
                                    <td>
                                            @php
                                                $currentStock = (float) ($stock['stock'] ?? 0);
                                                $result = $currentStock * $unitValue;
                                                $isDecimal = strpos((string)$currentStock, '.') !== false;
                                            @endphp

                                            @if ($isDecimal)
                                                {{ number_format($result, 2) }}
                                                {{ $stock->product->unit->subUnits->first()?->name ?? '' }}
                                            @else
                                                {{ $currentStock }}
                                                {{ $stock->product->unit->unit_type ?? '' }}
                                            @endif                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if($stocks->count() == 0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-cl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
    
    </div>

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="row align-items-center mb-3">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize"><i
                        class="tio-filter-list"></i> {{\App\CPU\translate('بيانات السيارة')}}
                </h1>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">

                    @php
                        $remain_stock = 0;
                        $total_stock = 0;
                        $total_cash = 0;
                        $refund_total = 0;
                        $total_credit = 0;
                        $order_count = 0;
                        $product_count = $stocks->count();
                        $remain_product_count = $remain_stocks->count();
                        $installment_total = 0;
                        
                        foreach ($stocks->get() as $st) {
                            $remain_stock += $st->stock;
                            $total_stock += $st->main_stock - $st->stock;
                        }
                        
                        foreach ($remain_stocks->get() as $st) {
                            $remain_stock += $st->stock;
                        }
                        
                                              
                        $total_cash = \App\Models\Transection::where('seller_id', $seller->id)->where('tran_type', 4)->where('cash',1)->where('active',1)->sum('amount');
                        $total_credit = \App\Models\Transection::where('seller_id', $seller->id)->where('tran_type', 4)->where('cash',2)->where('active',1)->sum('amount');
                        $refund_total = \App\Models\Transection::where('seller_id', $seller->id)->where('tran_type', 7)->where('active',1)->sum('amount');
                        $installment_total = \App\Models\Installment::where('seller_id', $seller->id)->sum('total_price');

                    @endphp
                    <div class=" col-sm-12 col-lg-4 mb-3 mt-3 mb-lg-5"><!-- Card -->
                        <a class="card card-hover-shadow h-100 color-one" href="#">
                            <div class="card-body">
                                <div class="flex-between align-items-center mb-1">
                                    <div>
                                        <h6 class="card-subtitle text-white">اسم المندوب: {{ $seller->f_name . ' ' . $seller->l_name }}</h6>
                                        <h6 class="card-subtitle text-white">كود المندوب: {{ $seller->mandob_code }}</h6>
                                        
                                        <span class="card-title text-white">
                                            كود السيارة: {{ \App\Models\Store::where('store_id', $seller->vehicle_code)->first()->store_code }}
                                        </span>
                                        <span class="card-title text-white">
                                            اسم السيارة: {{ \App\Models\Store::where('store_id', $seller->vehicle_code)->first()->store_name1 }}
                                        </span>
                                        
                                       <span class="card-title text-white">
                                            مبيعات كاش: {{ number_format($total_cash, 2) }}
                                        </span>
                                        <span class="card-title text-white">
                                            مبيعات أجلة: {{ number_format($total_credit, 2) }}
                                        </span>
                                        <span class="card-title text-white">
                                        مرتجعات: {{ number_format($refund_total, 2) }}
                                        </span>
                                        <span class="card-title text-white">
                                            تحصيلات: {{ number_format($installment_total, 2) }}
                                        </span>
                                        <span class="card-title text-white">
                                            عدد الفواتير: {{ $orders->count() }}
                                        </span>

                                    </div>
                                </div>
                                <!-- End Row -->
                            </div>
                        </a>
                        <!-- End Card -->
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src={{asset("public/assets/admin/js/global.js")}}></script>
@endpush
