@extends('layouts.admin.app')

@section('title', \App\CPU\translate('dashboard'))

@section('content')
<style>
:root {
    --dash-primary: #00296B;
    --dash-accent: #00509d;
    --dash-gold: #F8C01C;
    --dash-card-bg: #fff;
    --dash-shadow: 0 4px 24px rgba(0,41,107,.08);
    --dash-radius: 16px;
    --dash-radius-sm: 10px;
}
.dashboard-wrap { background: linear-gradient(180deg, #f0f4fc 0%, #fff 120px); min-height: 100vh; padding-bottom: 2rem; }
.dash-card {
    border: none;
    border-radius: var(--dash-radius);
    box-shadow: var(--dash-shadow);
    transition: transform .25s ease, box-shadow .25s ease;
    overflow: hidden;
    border-right: 4px solid var(--dash-accent);
}
.dash-card:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,41,107,.12); }
.dash-card-header {
    background: linear-gradient(135deg, var(--dash-primary) 0%, var(--dash-accent) 100%);
    color: #fff;
    padding: 14px 20px;
    font-weight: 700;
    font-size: 1.05rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}
.dash-card-header .dash-card-title { display: flex; align-items: center; gap: 10px; }
.dash-card-header .dash-card-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.dash-card-header a { color: rgba(255,255,255,.95); text-decoration: none; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 4px; }
.dash-card-header a:hover { color: var(--dash-gold); }
.dash-card .card-body { padding: 1.25rem; background: var(--dash-card-bg); }
.dash-table thead th {
    color: #5c6370;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: .02em;
    border-bottom: 2px solid #eef1f6;
    padding: 12px 10px;
}
.dash-table tbody tr { transition: background .2s; }
.dash-table tbody tr:hover { background: #f8fafc; }
.dash-table tbody td { padding: 12px 10px; vertical-align: middle; }
.img-one-dash { width: 80px; opacity: 0.5; }
.section-title { font-size: 1.25rem; font-weight: 700; color: var(--dash-primary); margin-bottom: 1.25rem; }
</style>

<div class="content container dashboard-wrap">
    <div class="mb-4">
        @include('admin-views.partials._dashboard-balance-stats', ['account' => $account])
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card dash-card">
                <div class="dash-card-header">
                    <span class="dash-card-title">
                        <span class="dash-card-icon"><i class="tio-account-circle"></i></span>
                        {{ \App\CPU\translate('أدلة محاسبية') }}
                    </span>
                    <a href="{{ route('admin.storage.indextree') }}"><i class="tio-arrow-forward"></i> {{ \App\CPU\translate('رؤية المزيد') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-nowrap table-align-middle dash-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الحساب</th>
                                    <th>الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($accounts as $key => $acc)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a class="text-primary" href="#">{{ $acc->account }}</a></td>
                                        <td>{{ $acc->balance . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center p-4">
                                            <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" class="img-one-dash">
                                            <p class="mb-0 text-muted">لاتوجد بيانات للعرض</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dash-card">
                <div class="dash-card-header">
                    <span class="dash-card-title">
                        <span class="dash-card-icon"><i class="tio-archive"></i></span>
                        {{ \App\CPU\translate('النواقص') }}
                    </span>
                    <a href="{{ route('admin.stock.stock-limit') }}"><i class="tio-arrow-forward"></i> {{ \App\CPU\translate('مشاهدة الكل') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-nowrap table-align-middle dash-table">
                            <thead>
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
                                            <p class="mb-0 text-muted">لاتوجد بيانات للعرض</p>
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

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card dash-card">
                <div class="dash-card-header">
                    <span class="dash-card-title">
                        <span class="dash-card-icon"><i class="tio-trending-up"></i></span>
                        {{ \App\CPU\translate('المنتجات الأكثر مبيعاً') }}
                    </span>
                    <a href="{{ route('admin.product.list') }}"><i class="tio-arrow-forward"></i> {{ \App\CPU\translate('رؤية المزيد') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-nowrap table-align-middle dash-table">
                            <thead>
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
                                            <p class="mb-0 text-muted">لاتوجد بيانات للعرض</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dash-card">
                <div class="dash-card-header">
                    <span class="dash-card-title">
                        <span class="dash-card-icon"><i class="tio-undo"></i></span>
                        {{ \App\CPU\translate('المنتجات الأكثر مرتجعاً') }}
                    </span>
                    <a href="{{ route('admin.stock.stock-limit') }}"><i class="tio-arrow-forward"></i> {{ \App\CPU\translate('مشاهدة الكل') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-nowrap table-align-middle dash-table">
                            <thead>
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
                                            <p class="mb-0 text-muted">لاتوجد بيانات للعرض</p>
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
