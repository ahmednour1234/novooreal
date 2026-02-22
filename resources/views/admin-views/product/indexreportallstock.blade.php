@extends('layouts.admin.app')

@section('content')
<style>
    /* Custom table borders */
    table {
        border: 2px solid black;
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 1px solid black; /* Border for each cell */
        padding: 8px;
        text-align: left;
    }
 
</style>

    <div class="container my-4">
        <h1 class="mb-4">تقرير مستودع المناديب  المنتجات</h1>

        <!-- Search Form -->
<form method="GET" action="{{ route('admin.product.getReportProductsAllStock') }}" class="mb-4 p-3 border rounded bg-light">
    <div class="row g-3 align-items-center">
        <div class="col-md-3">
            <input type="text" name="product_name" class="form-control" placeholder="اسم المنتج" value="{{ request('product_name') }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="product_code" class="form-control" placeholder="كود المنتج" value="{{ request('product_code') }}">
        </div>
        
        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="تاريخ البدء">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="تاريخ النهاية">
        </div>
         <div class="col-md-3">
            <select name="seller_id" class="form-control">
                <option value="" disabled {{ request('seller_id') ? '' : 'selected' }}>اختر المندوب</option>
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                        {{ $seller->email }}
                    </option>
                @endforeach
            </select>
        </div>



        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">بحث</button>
        </div>
    </div>
</form>


<div class="card-body">
    <!-- Total Sales -->


    <button class="btn btn-primary col-12" onclick="printTable()">تقرير</button>
<div class="table-responsive datatable-custom">

    <!-- Products Table -->

    <!-- Products Table -->
    @php
        use App\Models\Seller;
        use Illuminate\Support\Facades\Request;

        // Retrieve the seller ID from the query string
        $sellerId = Request::get('seller_id');

        // Fetch the seller details if the seller ID exists
        $seller = $sellerId ? Seller::where('id', $sellerId)->first() : null;

        // Determine the name of the seller or set to a default
        $sellerName = $seller ? $seller->email : 'كل المناديب';
    @endphp
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">
                اسم المندوب
            </td>
            <td colspan="3" style="text-align: center; font-weight: bold;">
 {{$sellerName}}
</td>
        </tr>
    </table>
  <table class="table table-striped">
        <thead>
            <tr>
                <th>اسم المنتج</th>
                <th>كود المنتج</th>
                <th>سعر الشراء</th>
                <th>رصيد أول الفترة</th>
                <th>الكمية المنصرفة</th>
                <th>الكمية المباعة</th>
                <th>رصيد آخر الفترة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productsFromOrders as $product)
                @php
                    // هنا نفترض أن المنتج يحتوي على سعر الشراء عبر علاقة معينة أو عن طريق الـ ID.
                    $productDetails = App\Models\Product::find($product['product_id']);
                @endphp

                <tr>
                    <td>{{ $productDetails ? $productDetails->name : 'غير موجود' }}</td>
                    <td>{{ $productDetails ? $productDetails->product_code : 'غير موجود' }}</td>
                    <td>{{ $productDetails ? number_format($productDetails->purchase_price*1.15, 2) : 'غير موجود' }}</td>
                    <td>{{ number_format($product['previous_stock'], 2) }}</td>
                    <td>{{ number_format($product['reserved_stock'], 2) }}</td>
                    <td>{{ number_format($product['sold_quantity'], 2) }}</td>
                    <td>{{ number_format($product['final_stock'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div class="card-footer">
        <!-- Pagination -->
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm-auto">
                <!-- Pagination Controls (if needed) -->
            </div>
        </div>
        <!-- End Pagination -->
    </div>
</div>
@endsection
@push('script_2')
    <script src={{asset("public/assets/admin/js/global.js")}}>
    </script>
    <!-- jQuery -->

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>     
    function update_customer_balance_cl(customerId) {
    document.getElementById('customer_id').value = customerId; // For balance modal
}

function update_customer_credit_cl(customerId) {
    document.getElementById('customer_credit_id').value = customerId; // For credit modal
}

    
    document.addEventListener('DOMContentLoaded', function () {
    const accountSelect = document.getElementById('account_id');
    const balanceDisplay = document.getElementById('account_balance');

    accountSelect.addEventListener('change', function () {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const balance = selectedOption.getAttribute('data-balance');
        balanceDisplay.textContent = balance ? balance : '0';
    });

    // Initialize the balance display for the default selected option
    if (accountSelect.value) {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const balance = selectedOption.getAttribute('data-balance');
        balanceDisplay.textContent = balance ? balance : '0';
    }
});

</script>
@endpush
<script>
    function printTable() {
        var printWindow = window.open('', '', 'height=600,width=800');
        
        // Find the table element
        var tableElement = document.querySelector('.datatable-custom');
        
        // Check if the table element exists
        if (!tableElement) {
            alert("Table element not found!");
            return;
        }

        var tableContent = tableElement.innerHTML;

        // Add styles for the printed page
        printWindow.document.write('<style>');
        printWindow.document.write(`
            body {
                direction: rtl;
                font-family: 'Cairo', Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background: #fff;
                color: #000;
            }
            .header-section {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 30px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            .header-section .left, .header-section .right {
                width: 30%;
                font-size: 14px;
            }
            .header-section p {
                margin: 5px 0;
                line-height: 1.4;
                font-size: 16px;
                color: #000;
            }
            .logo {
                text-align: center;
                width: 30%;
            }
            .logo img {
                max-width: 150px;
                height: auto;
            }
            h2 {
                text-align: center;
                color: #000;
                margin-bottom: 20px;
                font-size: 24px;
                font-weight: bold;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                background: #fff;
            }
            th, td {
                border: 1px solid #000;
                padding: 10px;
                text-align: center;
                font-size: 14px;
                color: #000;
            }
            th {
                background: #000;
                color: #fff;
                font-weight: bold;
            }
            td {
                background: #fff;
            }
            td img, td button {
                display: none; /* Hide images and buttons */
            }

            /* Hide pagination elements during print */
            .datatable-custom .pagination {
                display: none !important; /* Hide pagination for print */
            }

            /* Hide print button during print */
            #printButton {
                display: none;
            }

            /* Print-specific styles */
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
                    font-size: 12px;
                    color: #555;
                    padding: 10px;
                }
                table {
                    border: 1px solid #000;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 8px;
                }
                /* Ensure header section is visible during print */
                .header-section {
                    display: flex !important;
                }
            }
        `);
        printWindow.document.write('</style></head><body>');

        // Add Header Information
        printWindow.document.write('<div class="header-section">');
        
        printWindow.document.write('<div class="right">');
        printWindow.document.write('<p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where(["key" => "vat_reg_no"])->first()->value }}</p>');
        printWindow.document.write('<p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where(["key" => "number_tax"])->first()->value }}</p>');
        printWindow.document.write('<p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_email"])->first()->value }}</p>');
        printWindow.document.write('</div>');
        
        let logoUrl = "{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}";
        printWindow.document.write('<div class="logo">');
        printWindow.document.write('<img src="' + logoUrl + '" alt="{{ \App\CPU\translate("شعار المتجر") }}">');
        printWindow.document.write('</div>');
        
        printWindow.document.write('<div class="left">');
        printWindow.document.write('<p><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_name"])->first()->value }}</p>');
        printWindow.document.write('<p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_address"])->first()->value }}</p>');
        printWindow.document.write('<p><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_phone"])->first()->value }}</p>');
        printWindow.document.write('</div>');
        
        printWindow.document.write('</div>');
        
        // Add Title and Date Range Information
        printWindow.document.write('<h2>تقرير مستودعات من تاريخ ' + "{{ request('start_date') }}" + ' إلى تاريخ ' + "{{ request('end_date') }}" + '</h2>');

        // Print the table content
        printWindow.document.write(tableContent);
        printWindow.document.write('</body></html>');
        
        // Close the document and print
        printWindow.document.close();
        printWindow.print();
    }
</script>
