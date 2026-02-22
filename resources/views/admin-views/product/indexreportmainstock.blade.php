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
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تقرير مشتريات المنتجات') }}</li>
      </ol>
    </nav>
  </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.product.getreportMainStock') }}" class="mb-4 p-3 border rounded bg-light">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <input type="text" name="product_name" class="form-control" placeholder="اسم المنتج" value="{{ request('product_name') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="product_code" class="form-control" placeholder="كود المنتج" value="{{ request('product_code') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="تاريخ البدء" min="2025-01-28">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="تاريخ النهاية">
            </div>
            <div class="col-md-3">
                <select name="branch_id" class="form-control">
                    <option value="">اختر المستودع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
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
        <div class="table-responsive datatable-custom">

            <!-- Header Table with Branch Name -->
            @php
                // إذا تم اختيار فرع، نحاول الحصول على اسمه، وإلا نعرض المستودع الرئيسي
                $selectedBranch = request('branch_id') ? \App\Models\Branch::find(request('branch_id')) : null;
                $branchName = $selectedBranch ? $selectedBranch->name : 'المستودع الرئيسي';
            @endphp
            <table border="1" style="width: 100%; border-collapse: collapse;" class="tsupplier">
                <tr>
                    <td colspan="3" style="text-align: center; font-weight: bold;">اسم المستودع</td>
                    <td colspan="3" style="text-align: center; font-weight: bold;">{{ $branchName }}</td>
                </tr>
            </table>

            <!-- Products Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>معرّف المنتج</th>
                        <th>اسم المنتج</th>
                        <th>كود المنتج</th>
                        <th>وحدة الفياس</th>
                        <th>رصيد أول فترة</th>
                        <th>وحدة الفياس</th>
                        <th>الكمية الوارد</th>
                        <th>وحدة الفياس</th>
                        <th>الكمية المنصرفة</th>
                        <th>وحدة الفياس</th>
                        <th>رصيد أخر فترة</th>
                        <th>قيمة اخر فترة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($finalProducts as $product)
                        <tr>
                            <td class="border border-gray-300 px-4 py-2">{{ $product['product_id'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $product['product_name'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $product['product_code'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ is_float($product['start']) ? ($product['subunit']) : ($product['unit']) }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                @php
                                    $valuestart = is_float($product['start']) ? ($product['start'] * $product['unit_value']) : $product['start'];
                                    $threshold = 1e-10;
                                    $formattedValuestart = (abs($valuestart) < $threshold) ? 0 : (strpos((string)$valuestart, 'e') !== false ? sprintf('%.10f', $valuestart) : $valuestart);
                                @endphp
                                {{ $formattedValuestart }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ is_float($product['purchased'] + $product['delegate_returned'] + $product['sale_returned']) ? ($product['subunit']) : ($product['unit']) }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                @php
                                    $valuew = is_float($product['purchased'] + $product['delegate_returned'] + $product['sale_returned'])
                                        ? ($product['purchased'] + $product['delegate_returned'] + $product['sale_returned']) * $product['unit_value']
                                        : ($product['purchased'] + $product['delegate_returned'] + $product['sale_returned']);
                                    $valuew = (float)$valuew;
                                    $threshold = 1e-10;
                                    $formattedValuew = (abs($valuew) < $threshold) ? 0 : (strpos((string)$valuew, 'e') !== false ? sprintf('%.10f', $valuew) : number_format($valuew, 2));
                                @endphp
                                {{ $formattedValuew }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ is_float($product['issued'] + $product['damaged'] + $product['purchase_returned'] + $product['sold']) ? ($product['subunit']) : ($product['unit']) }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ is_float($product['issued'] + $product['damaged'] + $product['purchase_returned'] + $product['sold']) ? number_format(($product['issued'] + $product['damaged'] + $product['purchase_returned'] + $product['sold']) * $product['unit_value'], 2) : ($product['issued'] + $product['damaged'] + $product['purchase_returned'] + $product['sold']) }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ is_float($product['now']) ? ($product['subunit']) : ($product['unit']) }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                @php
                                    $value = is_float($product['now']) ? $product['now'] * $product['unit_value'] : $product['now'];
                                    $value = (float)$value;
                                    $threshold = 1e-10;
                                    $formattedValue = (abs($value) < $threshold) ? 0 : (strpos((string)$value, 'e') !== false ? sprintf('%.10f', $value) : $value);
                                    $taxSetting = \App\Models\BusinessSetting::where('key', 'tax')->first();
                                    $taxRate = $taxSetting ? $taxSetting->value : 0;
                                @endphp
                                {{ $formattedValue }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                @if($product['unitPrice'] == 0 && is_float($product['now']))
                                    {{ number_format(($product['purchase_price'] * $formattedValue) + ($formattedValue * $product['tax_purchase']), 2) }}
                                @elseif($product['unitPrice'] == 1 && is_float($product['now']))
                                    {{ number_format((($product['purchase_price'] + ($formattedValue * $product['tax_purchase'])) / $product['unit_value']) * $formattedValue, 2) }}
                                @elseif($product['unitPrice'] == 0 && !is_float($product['now']))
                                    {{ number_format($product['purchase_price'] + ($formattedValue * $product['tax_purchase'] * $product['unit_value'] * $formattedValue), 2) }}
                                @else
                                    {{ number_format($product['purchase_price'] + ($formattedValue * $product['tax_purchase'] * $formattedValue), 2) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="border border-gray-300 px-4 py-2 text-center" colspan="13">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    @php
                        $totalStart = 0;
                        $totalPurchased = 0;
                        $totalIssued = 0;
                        $totalNow = 0;
                        $totalValue = 0;
                        $totaltaxpurchase = 0;

                        foreach ($finalProducts as $product) {
                            $totalStart += is_float($product['start']) ? ($product['start'] * $product['unit_value']) : $product['start'];
                            $totalPurchased += is_float($product['purchased'] + $product['delegate_returned'] + $product['sale_returned'])
                                ? ($product['purchased'] + $product['delegate_returned'] + $product['sale_returned']) * $product['unit_value']
                                : ($product['purchased'] + $product['delegate_returned'] + $product['sale_returned']);
                            $totalIssued += is_float($product['issued'] + $product['damaged'] + $product['purchase_returned'] + $product['sold'])
                                ? ($product['issued'] + $product['damaged'] + $product['purchase_returned'] + $product['sold']) * $product['unit_value']
                                : ($product['issued'] + $product['damaged'] + $product['purchase_returned'] + $product['sold']);
                            $totalNow += is_float($product['now']) ? ($product['now'] * $product['unit_value']) : $product['now'];
                            $totaltaxpurchase += $product['tax_purchase'] * $product['now'];
                            $value = is_float($product['now']) ? $product['now'] * $product['unit_value'] : $product['now'];
                            $totalValue += ($product['unitPrice'] == 0)
                                ? ($product['purchase_price'] * $value + $totaltaxpurchase)
                                : (($product['purchase_price'] + ($totaltaxpurchase / $product['unit_value'])) * $value);
                        }
                    @endphp
                    <tr>
                        <td colspan="4"><strong>الإجمالي</strong></td>
                        <td>{{ number_format($totalStart, 2) }}</td>
                        <td></td>
                        <td>{{ number_format($totalPurchased, 2) }}</td>
                        <td></td>
                        <td>{{ number_format($totalIssued, 2) }}</td>
                        <td></td>
                        <td>{{ number_format($totalNow, 2) }}</td>
                        <td>{{ number_format($totalValue, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
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
        if (!tableElement) {
            alert("Table element not found!");
            return;
        }
        var tableContent = tableElement.innerHTML;
        
        printWindow.document.write('<style>');
        printWindow.document.write(`
            body {
                direction: rtl;
                font-family: 'Cairo', Arial, sans-serif;
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
                background: #fff;
            }
            th, td {
                border: 1px solid #000;
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
                display: none;
            }
            .datatable-custom .pagination {
                display: none !important;
            }
            #printButton {
                display: none;
            }
            @media print {
                body {
                    font-size: 12px;
                }
                @page {}
                footer {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    text-align: center;
                    font-size: 12px;
                    color: #555;
                }
                table {
                    border: 1px solid #000;
                }
                th, td {
                    border: 1px solid #000;
                }
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
        
        // Add Title and Date Range Information along with branch name
        printWindow.document.write('<h2>تقرير المخزن (' + "{{ $branchName }}" + ') من تاريخ ' + "{{ request('start_date') }}" + ' إلى تاريخ ' + "{{ request('end_date') }}" + '</h2>');
        
        // Print the table content
        printWindow.document.write(tableContent);
        printWindow.document.write('</body></html>');
        
        printWindow.document.close();
        printWindow.print();
    }
</script>
