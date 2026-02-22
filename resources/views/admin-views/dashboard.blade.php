@extends('layouts.admin.app')

@section('title', \App\CPU\translate('dashboard'))

@section('content')

<style>
    .card-title-header {
        background: linear-gradient(90deg, #3c4b96 0%, #3c4b96 100%);
        color: #fff;
        padding: 10px 15px;
        border-radius: 0.5rem 0.5rem 0 0;
        font-weight: bold;
        font-size: 1.2rem;
        margin-bottom: 0;
    }

    .img-one-dash {
        width: 100px;
        opacity: 0.6;
    }

    .trip-card {
        position: relative;
        overflow: hidden;
        border: none;
        border-radius: .75rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .trip-card:hover {
        transform: translateY(-5px);
    }

    .trip-card .card-header {
        background: linear-gradient(90deg, #3c4b96 0%, #3c4b96 100%);
        color: #fff;
        border-top-left-radius: .75rem;
        border-top-right-radius: .75rem;
        font-weight: bold;
    }

    .trip-card .card-body {
        background-color: #fff;
    }

    .stat-box {
        background: #f9f9f9;
        border-radius: .5rem;
        padding: 10px;
        transition: background-color 0.3s ease;
    }

    .stat-box:hover {
        background-color: #ffeede;
    }

    .stat-box strong {
        display: block;
        font-size: 1.4rem;
        color: #333;
    }

    @keyframes drive {
        0% {
            right: -100px;
        }

        100% {
            right: calc(100% + 100px);
        }
    }

    .animated-car {
        position: absolute;
        bottom: 280px;
        width: 60px;
        opacity: 0.8;
        animation: drive 6s linear infinite;
    }
</style>

<div class="content container">
    <div class="mb-3">
        @include('admin-views.partials._dashboard-balance-stats', ['account' => $account])
    </div>

    {{-- أدلة محاسبية ونواقص --}}
    <div class="row gx-2 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-title-header d-flex justify-content-between align-items-center">
                    <span>{{ \App\CPU\translate('أدلة محاسبية') }}</span>
                    <a href="{{ route('admin.storage.indextree') }}" class="text-white">{{ \App\CPU\translate('رؤية المزيد') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم الحساب</th>
                                    <th>الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($accounts as $key => $account)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a class="text-primary" href="#">{{ $account->account }}</a></td>
                                        <td>{{ $account->balance . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center p-4">
                                            <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" class="img-one-dash">
                                            <p>لاتوجد بيانات للعرض</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- النواقص --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-title-header d-flex justify-content-between align-items-center">
                    <span>{{ \App\CPU\translate('النواقص') }}</span>
                    <a href="{{ route('admin.stock.stock-limit') }}" class="text-white">{{ \App\CPU\translate('مشاهدة الكل') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>الكمية المتاحة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $key => $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a class="text-primary" href="{{ route('admin.stock.stock-limit') }}">{{ Str::limit($product->name, 40) }}</a></td>
                                        <td>{{ $product->quantity }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center p-4">
                                            <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" class="img-one-dash">
                                            <p>لاتوجد بيانات للعرض</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- المنتجات الأكثر مبيعًا ومرتجعًا --}}
    <div class="row gx-2 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-title-header d-flex justify-content-between align-items-center">
                    <span>{{ \App\CPU\translate('المنتجات الأكثر مبيعاً') }}</span>
                    <a href="{{ route('admin.product.list') }}" class="text-white">{{ \App\CPU\translate('رؤية المزيد') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم المنتج</th>
                                    <th>كود المنتج</th>
                                    <th>عدد مرات البيع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productmoreselles as $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a class="text-primary" href="{{ route('admin.product.listProductsByOrderType', ['product_id' => $product->id]) }}">{{ $product->name }}</a></td>
                                        <td>{{ $product->product_code }}</td>
                                        <td>{{ $product->order_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4">
                                            <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" class="img-one-dash">
                                            <p>لاتوجد بيانات للعرض</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- الأكثر مرتجعاً --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-title-header d-flex justify-content-between align-items-center">
                    <span>{{ \App\CPU\translate('المنتجات الأكثر مرتجعاً') }}</span>
                    <a href="{{ route('admin.stock.stock-limit') }}" class="text-white">{{ \App\CPU\translate('مشاهدة الكل') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم المنتج</th>
                                    <th>كود المنتج</th>
                                    <th>عدد المرتجعات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productmorerefunds as $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a class="text-primary" href="{{ route('admin.product.listProductsByOrderType', ['product_id' => $product->id]) }}">{{ $product->name }}</a></td>
                                        <td>{{ $product->product_code }}</td>
                                        <td>{{ $product->refund_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4">
                                            <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" class="img-one-dash">
                                            <p>لاتوجد بيانات للعرض</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الرحلات الحالية --}}
{{--    <div class="card mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <h4 class="card-header-title">
                        <i class="tio-chart-bar-4"></i>
                        {{ \App\CPU\translate('الرحلات الحالية') }}
                    </h4>
                </div>
            </div>
            <div class="row g-3">
                @foreach($sellers as $seller)
                    @php
                        $stocks = \App\Models\Stock::where('seller_id', $seller->id)->whereRaw('main_stock != stock');
                        $remain_stocks = \App\Models\Stock::where('seller_id', $seller->id)->whereRaw('main_stock = stock');
                        if ($stocks->count() + $remain_stocks->count() == 0) continue;
                        $remain_stock = $stocks->sum('stock') + $remain_stocks->sum('stock');
                        $total_cash = \App\Models\CurrentOrder::where('owner_id', $seller->id)->where('type',4)->where('cash',1)->sum('order_amount');
                        $total_credit = \App\Models\CurrentOrder::where('owner_id', $seller->id)->where('type',4)->where('cash',2)->sum('order_amount');
                        $refund_total = \App\Models\CurrentOrder::where('owner_id', $seller->id)->where('type',7)->sum('order_amount');
                        $installment_total = \App\Models\Installment::where('seller_id', $seller->id)->sum('total_price');
                        $orders_count = \App\Models\CurrentOrder::where('owner_id', $seller->id)->count();
                    @endphp
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <a href="{{ route('admin.stock.products', $seller->id) }}" class="card trip-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-white">{{ $seller->f_name }} {{ $seller->l_name }} <small>#{{ $seller->mandob_code }}</small></h6>
                                <img src="https://www.freeiconspng.com/uploads/truck-icon-png-14.png" alt="car" class="animated-car">
                            </div>
                            <div class="card-body py-4">
                                <div class="row text-center gy-3">
                                    <div class="col-6"><div class="stat-box"><strong>{{ number_format($remain_stock) }}</strong><small>رصيد متبقي</small></div></div>
                                    <div class="col-6"><div class="stat-box"><strong>{{ number_format($total_cash, 2) }}</strong><small>مبيعات نقدي</small></div></div>
                                    <div class="col-6"><div class="stat-box"><strong>{{ number_format($total_credit, 2) }}</strong><small>مبيعات أجل</small></div></div>
                                    <div class="col-6"><div class="stat-box"><strong>{{ number_format($refund_total, 2) }}</strong><small>مرتجعات</small></div></div>
                                    <div class="col-6"><div class="stat-box"><strong>{{ number_format($installment_total, 2) }}</strong><small>تحصيلات</small></div></div>
                                    <div class="col-6"><div class="stat-box"><strong>{{ $orders_count }}</strong><small>عدد الفواتير</small></div></div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        --}}
    </div>
</div>


@endsection

    <script src="{{ asset('public/assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('public/assets/admin') }}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script
        src="{{ asset('public/assets/admin') }}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js">
    </script>

    <script>
        "use strict";

        function account_stats_update(type) {
            //console.log(type)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.account-status') }}',
                data: {
                    statistics_type: type
                },
                beforeSend: function() {
                    $('#loading').show()
                },
                success: function(data) {
                    $('#account_stats').html(data.view)
                },
                complete: function() {
                    $('#loading').hide()
                }
            });
        }
    </script>

    <script src={{ asset('public/assets/admin/js/global.js') }}></script>
