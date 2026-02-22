@extends('layouts.admin.app')

@section('content')
<style>
  /* حدود الجدول */

  thead th{ color:#fff; }
  tbody tr:hover{ background:#fafafa; }

  /* أزرار الإجراءات */
  .actions-row{ display:flex; gap:10px; flex-wrap:wrap; }
  .actions-row .btn{ min-width:140px; }
.tsupplier{
    display: none;
}
  /* تحسين المظهر العام */
  .page-title{ margin-bottom: 0; }
</style>
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تقرير هامش الربح والخسارة للمنتجات') }}</li>
      </ol>
    </nav>
  </div> 
  

        <!-- Search Form -->
<form method="GET" action="{{ url()->current() }}" class="mb-4 p-3 border rounded bg-light">
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

       <div class="col-12"><hr class="my-2"></div>

    <div class="col-6 col-lg-2">
      <button type="submit" class="btn btn-secondary w-100">بحث</button>
    </div>
    <div class="col-6 col-lg-2">
      <a href="{{ route('admin.product.getreportProductsPurchase') }}" class="btn btn-danger w-100">مسح الفلاتر</a>
    </div>
    <div class="col-6 col-lg-2">
      <button type="button" id="printButton" class="btn btn-primary w-100" onclick="printTable()">طباعة التقرير</button>
    </div>
    <div class="col-6 col-lg-2">
      <button type="button" class="btn btn-info w-100" onclick="exportExcel()">تصدير Excel</button>
    </div>
    </div>
</form>


<div class="card-body">
    <!-- Total Sales -->
 

<div class="table-responsive datatable-custom">

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
 
    <table border="1" style="width: 100%; border-collapse: collapse;" class="tsupplier">
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">
                اسم المندوب
            </td>
            <td colspan="3" style="text-align: center; font-weight: bold;">
                {{$sellerName }}
            </td>
        </tr>
    </table>

<table class="table">
    <thead>
        <tr>
            <th>معرّف المنتج</th>
            <th>اسم المنتج</th>
            <th>كود المنتج</th>
            <th>الكمية</th>
            <th>وحدة القياس</th>
                <th>سعر البيع</th>
                <th>اجمالي سعر البيع</th>
                
                            @if($type == 1)

                <th>سعر الشراء</th>
                <th>اجمالي سعر الشراء</th>
                <th>هامش الربح او الخسارة</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php
            // Initialize totals for footer calculations
            $totalSellingPrice = 0;
            $totalPurchasePrice = 0;
            $totalProfit = 0;
        @endphp

        @foreach ($products as $product)
            @php
            $taxSetting = \App\Models\BusinessSetting::where('key', 'tax')->first();
$taxRate = $taxSetting ? $taxSetting->value : 0; // Default to 0 if not found
                // Per-row calculations
                $rowTotalSellingPrice = $product['total_selling_price']  * $product['last_quantity'];
                $rowTotalPurchasePrice = ($product['total_purchase_price']+$product['taxpurchase'])* $product['last_quantity'];
                $rowProfit = $rowTotalSellingPrice-$rowTotalPurchasePrice;

                // Accumulate totals
                $totalSellingPrice += $rowTotalSellingPrice;
                $totalPurchasePrice += $rowTotalPurchasePrice;
                $totalProfit += $rowProfit;
            @endphp

            <tr>
                <td>{{ $product['product_id'] }}</td>
                <td>{{ $product['product_name'] }}</td>
                <td>{{ $product['product_code'] }}</td>
                <td>{{ number_format($product['last_quantity'], 2) }}</td>
                <td>حبة</td>
                    <td>{{ number_format($product['selling_price'] , 2) }}</td>
                    <td>{{ number_format($rowTotalSellingPrice, 2) }}</td>
                                    @if($type == 1)

                    <td>{{ number_format($product['purchase_price'], 2) }}</td>
                    <td>{{ number_format($rowTotalPurchasePrice, 2) }}</td>
                    <td>{{ number_format($rowTotalSellingPrice-$rowTotalPurchasePrice, 2) }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">الإجمالي</td>
            <td>{{ number_format($products->sum('last_quantity'), 2) }}</td>
            <td></td>
                <td></td>
                <td>{{ number_format($totalSellingPrice, 2) }}</td>
                <td></td>
                @if($type == 1)
                <td>{{ number_format($totalPurchasePrice, 2) }}</td>
                <td>{{ number_format($totalProfit, 2) }}</td>
            @endif
        </tr>
    </tfoot>
</table>
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
        function exportExcel(){
      const table = document.querySelector('#purchase-table');
      if(!table){ alert('لا يوجد جدول لتصديره'); return; }

      const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
      const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
        Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim())
      );

      // إضافة صف الإجماليات من الـ tfoot (اختياري)
      const tfootRow = table.querySelector('tfoot tr');
      if(tfootRow){
        const footCells = Array.from(tfootRow.querySelectorAll('td')).map(td => td.textContent.trim());
        rows.push(footCells);
      }

      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);

      // عرض الأعمدة
      ws['!cols'] = [
        {wch:12},{wch:28},{wch:18},{wch:12},{wch:14},
        {wch:14},{wch:12},{wch:12},{wch:26}
      ];

      XLSX.utils.book_append_sheet(wb, ws, 'Purchases Report');

      const now = new Date();
      const y = now.getFullYear();
      const m = String(now.getMonth()+1).padStart(2,'0');
      const d = String(now.getDate()).padStart(2,'0');
      const hh = String(now.getHours()).padStart(2,'0');
      const mm = String(now.getMinutes()).padStart(2,'0');

      XLSX.writeFile(wb, `purchases_report_${y}-${m}-${d}_${hh}-${mm}.xlsx`);
    }
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
        printWindow.document.write('<h2>تقرير هامش الربح والخسارة من تاريخ ' + "{{ request('start_date') }}" + ' إلى تاريخ ' + "{{ request('end_date') }}" + '</h2>');

        // Print the table content
        printWindow.document.write(tableContent);
        printWindow.document.write('</body></html>');
        
        // Close the document and print
        printWindow.document.close();
        printWindow.print();
    }
</script>
