@extends('layouts.admin.app')

@section('title', \App\CPU\translate('stock_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css"/>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="row align-items-center mb-3">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                    <i class="tio-filter-list"></i> {{ \App\CPU\translate('قائمة المنتجات داخل المستودعات') }}
                    <span class="badge badge-soft-dark ml-2">{{ $stocks->total() }}</span>
                </h1>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-12">
            <div class="col-sm-12 col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                   <div class="card-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="tio-search"></i>
                    </span>
                    <input type="search" name="search" class="form-control"
                           placeholder="{{ \App\CPU\translate('بحث بكود المستودع أو كود المندوب') }}"
                           value="{{ request()->search }}">
                    <button type="submit" class="btn btn-primary">
                        {{ \App\CPU\translate('بحث') }}
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-12 text-end">
            <button onclick="printTable()" class="btn btn-secondary no-print">
                <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
            </button>
        </div>
    </div>
</div>

                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="stock-table" class="table table-borderless table-thead-bordered table-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ \App\CPU\translate('#') }}</th>
                                    <th>{{ \App\CPU\translate('كود المخزن') }}</th>
                                    <th>{{ \App\CPU\translate('كود المندوب') }}</th>
                                    <th>{{ \App\CPU\translate('اسم المندوب') }}</th>
                                    <th>{{ \App\CPU\translate('اسم المنتج') }}</th>
                                    <th>{{ \App\CPU\translate('الكمية أول فترة') }}</th>
                                    <th>{{ \App\CPU\translate('الكمية المتاحة') }}</th>
                                   <th>{{ \App\CPU\translate('سعر البيع') }}</th>
                                   <th>{{ \App\CPU\translate('سعر الشراء') }}</th>

                                    <!--<th class="none">{{ \App\CPU\translate('اجراءات') }}</th>-->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $key => $stock)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $stock->seller->vehicle_code }}</td>
                                        <td>{{ $stock->seller->mandob_code }}</td>
                                        <td>{{ $stock->seller->f_name . ' ' . $stock->seller->l_name }}</td>
                                        <td>{{ $stock->product->name }}</td>
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
                                            @endif
                                        </td>
                                        <td>{{ $stock->product->selling_price }}</td>
                                        <td>{{ $stock->product->purchase_price }}</td>
                                        <!--<td  class="none">-->
                                            <!--<a href="{{ route('admin.stock.edit', [$stock['id']]) }}" class="btn btn-white">-->
                                            <!--    <i class="tio-edit"></i>-->
                                            <!--</a>-->
                                        <!--    <a href="javascript:"-->
                                        <!--       onclick="form_alert('stock-{{ $stock['id'] }}','هل انت متأكد من حذف المنتج من مخزن المندوب?')"-->
                                        <!--       class="btn btn-white">-->
                                        <!--        <i class="tio-delete"></i>-->
                                        <!--    </a>-->
                                        <!--    <form action="{{ route('admin.stock.delete', [$stock['id']]) }}"-->
                                        <!--          method="post" id="stock-{{ $stock['id'] }}">-->
                                        <!--        @csrf @method('delete')-->
                                        <!--    </form>-->
                                        <!--</td>-->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="text-center mt-3">
                            {!! $stocks->appends(request()->query())->links() !!}
                        </div>

                        @if($stocks->isEmpty())
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-cl" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}"
                                     alt="{{ \App\CPU\translate('Image Description') }}">
                                <p>{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
                            </div>
                        @endif
                    </div>
                    <!-- End Table -->

                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection
@php
    use App\Models\Seller;
    use App\Models\Product;

    $search = request()->get('search'); // Get the search query

    // Check if a product exists with the given product_code
    $product = Product::where('product_code', $search)->first();

    // If product exists, get the associated seller (assuming 'seller_id' exists in products table)
    $seller = $product ? Seller::find($product->seller_id) : Seller::where('mandob_code', $search)->first();

    // Set the seller's email if found, or fallback to null
    $sellerName = $seller ? $seller->f_name. $seller->l_name : null;
@endphp

<script>
    function printTable() {
        const tableContent = document.getElementById('stock-table').outerHTML;
        const sellerName = @json($sellerName ?? null); // Pass PHP variable to JS
        const productName = @json($product->name ?? null); // Pass product name to JS

        let headerTitle = "";

        if (sellerName) {
            headerTitle = `قائمة المنتجات داخل مستودع (${sellerName})`;
        } else if (productName) {
            headerTitle = `قائمة  داخل المستودعات (${productName})`;
        } else {
            headerTitle = "قائمة المنتجات داخل كل المستودعات";
        }

        const printWindow = window.open('', '', 'height=600,width=800');

        printWindow.document.write(`
            <html>
                <head>
                    <title>{{ \App\CPU\translate('طباعة') }}</title>
                    <style>
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
                            margin-top: 20px;
                            border: 1px solid #ddd;
                        }

                        th, td {
                            padding: 10px;
                            text-align: center;
                            font-size: 14px;
                            border: 1px solid #ddd;
                        }

                        th {
                            background-color: #f0f0f0;
                            color: #003366;
                            font-weight: bold;
                        }

                        .footer {
                            margin-top: 30px;
                            text-align: center;
                            font-size: 12px;
                            color: #888;
                        }

                        @media print {
                            body {
                                font-size: 12px;
                            }

                            @page {
                                margin: 10mm;
                            }

                            footer {
                                position: fixed;
                                bottom: 0;
                                left: 0;
                                width: 100%;
                                text-align: center;
                                padding: 10px;
                            }

                            table, th, td {
                                border: 1px solid #000;
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
                    <h1>${headerTitle}</h1>
                    ${tableContent}
                </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.print();
    }
</script>
@push('script_2')

@endpush
