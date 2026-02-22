{{-- resources/views/admin/reports/products_purchase.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تقرير مشتريات المنتجات'))

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
<form method="GET" action="{{ route('admin.product.getreportProductsPurchase') }}" class=" p-3 border rounded bg-light" dir="rtl">
  <div class="row g-3 align-items-end">
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
      <select name="supplier_id" class="form-control">
        <option value="" {{ request('supplier_id') ? '' : 'selected' }}>كل الموردين</option>
        @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
            {{ $supplier->name }}
          </option>
        @endforeach
      </select>
    </div>

    <!-- صف الأزرار -->
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



      <div class="table-responsive datatable-custom" dir="rtl">
        {{-- معلومات المورّد أعلاه --}}
        @php
          use App\Models\Supplier;
          use Illuminate\Support\Facades\Request;

          $sellerId = Request::get('supplier_id');
          $seller = $sellerId ? Supplier::where('id', $sellerId)->first() : null;
          $sellerName = $seller ? $seller->name : 'كل الموردين';
        @endphp

        <table class="tsupplier">
          <tr>
            <td colspan="3" style="text-align:center;font-weight:bold;">اسم المورد</td>
            <td colspan="3" style="text-align:center;font-weight:bold;">{{ $sellerName }}</td>
          </tr>
        </table>

        {{-- جدول البيانات الرئيسي (سيتم تصديره إلى إكسل) --}}
        <table class="table" id="purchase-table">
          <thead >
            <tr>
              <th>معرّف المنتج</th>
              <th>اسم المنتج</th>
              <th>كود المنتج</th>
              <th>الكمية</th>
              <th>وحدة القياس</th>
              <th>سعر الشراء</th>
              <th>الخصم</th>
              <th>الضريبة</th>
              <th>إجمالي القيمة بعد الخصم والضريبة</th>
            </tr>
          </thead>

          @php
            $taxSetting = \App\Models\BusinessSetting::where('key','tax')->first();
            $taxRate = $taxSetting ? $taxSetting->value : 0;
          @endphp

          <tbody>
            @foreach ($products as $product)
              <tr>
                <td>{{ $product['product_id'] }}</td>
                <td>{{ $product['product_name'] }}</td>
                <td>{{ $product['product_code'] }}</td>
                <td>{{ $product['quantity'] }}</td>
                <td>{{ $product['unit'] }}</td>
                <td>{{ number_format($product['selling_price'], 2) }}</td>
                <td>{{ number_format($product['discount'], 2) }}</td>
                <td>{{ number_format($product['tax_amount'], 2) }}</td>
                <td>
                  {{ number_format(
                    (($product['selling_price'] + $product['tax_amount'] - $product['discount']) * $product['quantity']),
                  2) }}
                </td>
              </tr>
            @endforeach
          </tbody>

          <tfoot>
            <tr>
              <td colspan="3">الإجمالي</td>
              @php
                $productsByUnit = $products->groupBy('unit');
              @endphp
              <td colspan="2">
                @foreach ($productsByUnit as $unit => $groupedProducts)
                  @php $totalQuantity = $groupedProducts->sum('quantity'); @endphp
                  @if($unit == 'كرتونة')
                    <span>{{ $totalQuantity }} كرتونة</span>
                  @elseif($unit == 'حبة')
                    <span>{{ $totalQuantity }} حبة</span>
                  @else
                    <span>{{ $totalQuantity }} {{ $unit }}</span>
                  @endif
                  @if(!$loop->last) &nbsp;|&nbsp; @endif
                @endforeach
              </td>

              <td>{{ number_format($products->sum('selling_price'), 2) }}</td>
              <td>{{ number_format($products->sum('discount'), 2) }}</td>
              <td>{{ number_format($products->sum(fn($p)=>$p['tax_amount']), 2) }}</td>
              <td>
                {{ number_format($products->sum(fn($p)=>
                  ($p['selling_price'] + $p['tax_amount'] - $p['discount']) * $p['quantity']
                ), 2) }}
              </td>
            </tr>
          </tfoot>
        </table>


    </div>
  </div>
@endsection

  {{-- مكتبة التصدير إلى إكسل --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>

  <script>
    // ====== تصدير إلى إكسل من الجدول الرئيسي ======
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

    // ====== طباعة المحتوى ======
    function printTable() {
      var printWindow = window.open('', '', 'height=600,width=800');
      var tableElement = document.querySelector('.datatable-custom');
      if (!tableElement) { alert("Table element not found!"); return; }
      var tableContent = tableElement.innerHTML;

      printWindow.document.write('<html><head><title>تقرير مشتريات المنتجات</title>');
      printWindow.document.write('<style>');
      printWindow.document.write(`
        body{ direction:rtl; font-family:'Cairo', Arial, sans-serif; margin:0; padding:20px; background:#fff; color:#000; }
        .header-section{ display:flex; align-items:center; justify-content:space-between; margin-bottom:30px; border-bottom:2px solid #000; padding-bottom:10px; }
        .header-section .left,.header-section .right{ width:30%; font-size:14px; }
        .header-section p{ margin:5px 0; line-height:1.4; font-size:16px; color:#000; }
        .logo{ text-align:center; width:30%; }
        .logo img{ max-width:150px; height:auto; }
        h2{ text-align:center; color:#000; margin-bottom:20px; font-size:24px; font-weight:bold; }
        table{ width:100%; border-collapse:collapse; margin:20px 0; background:#fff; }
        th,td{ border:1px solid #000; padding:10px; text-align:center; font-size:14px; color:#000; }
        th{ background:#000; color:#fff; font-weight:bold; }
        td{ background:#fff; }
        td img, td button{ display:none; }
        .datatable-custom .pagination{ display:none !important; }
        #printButton{ display:none; }
        @media print{
          body{ font-size:12px; }
          @page{ margin:10mm; }
          table{ border:1px solid #000; }
          th,td{ border:1px solid #000; padding:8px; }
          .header-section{ display:flex !important; }
        }
      `);
      printWindow.document.write('</style></head><body>');

      // Header Info (سيرفر سايد داخل الجافاسكربت)
      printWindow.document.write('<div class="header-section">');

      printWindow.document.write('<div class="right">');
      printWindow.document.write('<p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where(["key"=>"vat_reg_no"])->first()->value }}</p>');
      printWindow.document.write('<p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where(["key"=>"number_tax"])->first()->value }}</p>');
      printWindow.document.write('<p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where(["key"=>"shop_email"])->first()->value }}</p>');
      printWindow.document.write('</div>');

      let logoUrl = "{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}";
      printWindow.document.write('<div class="logo">');
      printWindow.document.write('<img src="'+logoUrl+'" alt="{{ \App\CPU\translate("شعار المتجر") }}">');
      printWindow.document.write('</div>');

      printWindow.document.write('<div class="left">');
      printWindow.document.write('<p><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where(["key"=>"shop_name"])->first()->value }}</p>');
      printWindow.document.write('<p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where(["key"=>"shop_address"])->first()->value }}</p>');
      printWindow.document.write('<p><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where(["key"=>"shop_phone"])->first()->value }}</p>');
      printWindow.document.write('</div>');

      printWindow.document.write('</div>');

      // عنوان التقرير والمدى الزمني
      printWindow.document.write('<h2>تقرير المشتريات من تاريخ ' + "{{ request('start_date') }}" + ' إلى تاريخ ' + "{{ request('end_date') }}" + '</h2>');

      // محتوى الجدول
      printWindow.document.write(tableContent);
      printWindow.document.write('</body></html>');

      printWindow.document.close();
      printWindow.print();
    }

    // أكواد مساعدة موجودة سابقاً (أبقيناها كما هي)
    function update_customer_balance_cl(customerId) {
      document.getElementById('customer_id') && (document.getElementById('customer_id').value = customerId);
    }
    function update_customer_credit_cl(customerId) {
      document.getElementById('customer_credit_id') && (document.getElementById('customer_credit_id').value = customerId);
    }
    document.addEventListener('DOMContentLoaded', function () {
      const accountSelect = document.getElementById('account_id');
      const balanceDisplay = document.getElementById('account_balance');
      if(!accountSelect || !balanceDisplay) return;
      accountSelect.addEventListener('change', function () {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const balance = selectedOption.getAttribute('data-balance');
        balanceDisplay.textContent = balance ? balance : '0';
      });
      if (accountSelect.value) {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const balance = selectedOption.getAttribute('data-balance');
        balanceDisplay.textContent = balance ? balance : '0';
      }
    });
  </script>
