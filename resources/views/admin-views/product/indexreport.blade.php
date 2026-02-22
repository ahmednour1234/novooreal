{{-- resources/views/admin/reports/products_sold.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تقرير المنتجات المباعة'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>

<style>
  /* تخطيط عام */
  .filters-card .form-label{ font-weight:600; color:#2c4470; }
  .filters-card .form-control,
  .filters-card .form-select{
    padding:.75rem 1rem; font-size:14px; border-radius:8px;
  }
  .filters-card .btn{
    min-height:44px; border-radius:10px; font-weight:600;
  }
  .filters-card .btn-outline{
    background:#fff; border:1px solid #dfe3ea; color:#2c4470;
  }
  .filters-card .btn-outline:hover{ background:#f6f8fb; }

  /* شبكة الفلاتر: 4 أعمدة على الشاشات الكبيرة، 2 على المتوسطة، 1 على الصغيرة */
  .filters-grid .col-item{ margin-bottom:14px; }
  @media (min-width: 1200px){
    .filters-grid .col-item{ width:25%; padding-inline:8px; float:right; }
  }
  @media (min-width: 768px) and (max-width: 1199.98px){
    .filters-grid .col-item{ width:50%; padding-inline:8px; float:right; }
  }
  @media (max-width: 767.98px){
    .filters-grid .col-item{ width:100%; padding-inline:0; float:none; }
  }

  .actions-row{ display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-start; }
  .table-wrap{ overflow:auto; }
  thead th{ white-space:nowrap; }

  /* تحسين الطباعة */
  @media print{
    .filters-card, .breadcrumb, .card-footer{ display:none !important; }
    table{ page-break-inside:auto; }
    tr{ page-break-inside:avoid; page-break-after:auto; }
  }
</style>
@endpush

@section('content')
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تقرير المنتجات المباعة') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== Filters Card ====== --}}
  <form method="GET" action="{{ route('admin.product.getreportProducts') }}" class="card shadow-sm mb-4 filters-card">
    <div class="card-header bg-white">
      <h5 class="mb-0">{{ \App\CPU\translate('الفلاتر') }}</h5>
    </div>

    <div class="card-body">
      <div class="filters-grid clearfix">

        {{-- الصف الأول: الاسم - الكود - البائع - المنطقة --}}
        <div class="col-item">
          <label class="form-label" for="product_name">اسم المنتج</label>
          <input type="text" name="product_name" id="product_name" class="form-control"
                 placeholder="اسم المنتج" value="{{ request('product_name') }}">
        </div>

        <div class="col-item">
          <label class="form-label" for="product_code">كود المنتج</label>
          <input type="text" name="product_code" id="product_code" class="form-control"
                 placeholder="كود المنتج" value="{{ request('product_code') }}">
        </div>

        <div class="col-item">
          <label class="form-label" for="seller_id">البائع</label>
          <select name="seller_id" id="seller_id" class="form-select">
            <option value="">جميع البائعين</option>
            @foreach ($sellers as $seller)
              <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                {{ $seller->email }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-item">
          <label class="form-label" for="region_id">المنطقة</label>
          <select name="region_id" id="region_id" class="form-select">
            <option value="">جميع المناطق</option>
            @foreach ($regions as $region)
              <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                {{ $region->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- الصف الثاني: نوع الحركة - حالة الدفع - تاريخ البدء - تاريخ النهاية --}}
        <div class="col-item">
          <label class="form-label" for="order_type">نوع الحركة</label>
          <select name="order_type" id="order_type" class="form-select">
            <option value="">جميع الأنواع</option>
            @foreach ([4 => 'مبيعات', 7 => 'مرتجع مبيعات', 12 => 'مشتريات', 24 => 'مرتجع مشتريات'] as $key => $label)
              <option value="{{ $key }}" {{ request('order_type') == $key ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-item">
          <label class="form-label" for="payment_status">حالة الدفع</label>
          <select name="payment_status" id="payment_status" class="form-select">
            <option value="">حالة الدفع</option>
            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>تم التحصيل</option>
            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>لم يتم التحصيل</option>
          </select>
        </div>

        <div class="col-item">
          <label class="form-label" for="start_date">تاريخ البدء</label>
          <input type="date" name="start_date" id="start_date" class="form-control"
                 value="{{ request('start_date') }}" placeholder="تاريخ البدء">
        </div>

        <div class="col-item">
          <label class="form-label" for="end_date">تاريخ النهاية</label>
          <input type="date" name="end_date" id="end_date" class="form-control"
                 value="{{ request('end_date') }}" placeholder="تاريخ النهاية">
        </div>
      </div>
    </div>

    <div class="card-footer bg-white">
      <div class="actions-row">
        <button type="submit" class="btn btn-primary">
          <i class="tio-search"></i> بحث
        </button>
        <a href="{{ route('admin.product.getreportProducts') }}" class="btn btn-outline">
          مسح الفلاتر
        </a>
        <button type="button" class="btn btn-primary" onclick="printReport()">
          طباعة التقرير
        </button>
        <button type="button" class="btn btn-success" onclick="exportExcel()">
          تصدير Excel
        </button>
      </div>
    </div>
  </form>

  {{-- ====== Report Table ====== --}}
  <div class="card">
    <div class="table-wrap">
      <table class="table table-striped table-hover mb-0" id="report-table" dir="rtl">
        <thead>
          <tr>
            <th>معرّف المنتج</th>
            <th>اسم المنتج</th>
            <th>كود المنتج</th>
            <th>سعر البيع</th>
            <th>الكمية</th>
            <th>وحدة القياس</th>
            <th>البائع</th>
            <th>العميل</th>
            <th>المنطقة</th>
            <th>النوع</th>
            <th>المبلغ المحصل</th>
            <th>رقم الفاتورة</th>
            <th>تاريخ البيع</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $productDetails)
            @foreach($productDetails as $product)
              <tr>
                <td>{{ $product['product_id'] }}</td>
                <td>{{ $product['product_name'] }}</td>
                <td>{{ $product['product_code'] }}</td>
                <td>{{ $product['selling_price'] }}</td>
                <td>{{ $product['quantity'] }}</td>
                <td>{{ $product['unit'] }}</td>
                <td>{{ $product['seller'] }}</td>
                <td>{{ $product['customer'] ?? 'غير متوفر' }}</td>
                <td>{{ $product['region'] ?? 'غير متوفر' }}</td>
                <td>
                  @if($product['order_type'] == 4)
                    مبيعات
                  @elseif($product['order_type'] == 7)
                    مرتجع مبيعات
                  @elseif($product['order_type'] == 12)
                    مشتريات
                  @elseif($product['order_type'] == 24)
                    مرتجع مشتريات
                  @else
                    غير معروف
                  @endif
                </td>
                <td>{{ $product['transaction_reference'] }}</td>
                <td>{{ $product['order_id'] }}</td>
                <td>{{ $product['created_at'] }}</td>
              </tr>
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
        <div class="col-sm-auto">
          <div class="d-flex justify-content-center justify-content-sm-end">
            {!! $orderDetails->appends(request()->query())->links() !!}
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
<script>
  // ===== Helpers =====
  function qs(s, ctx=document){ return ctx.querySelector(s); }
  function qsa(s, ctx=document){ return Array.from(ctx.querySelectorAll(s)); }

  function getSelectText(id){
    const el = qs('#'+id);
    if(!el) return '';
    return el.value ? el.options[el.selectedIndex].text.trim() : '';
  }

  function buildFiltersSummary(){
    const parts = [];
    const pn = qs('#product_name')?.value?.trim();
    const pc = qs('#product_code')?.value?.trim();
    const seller = getSelectText('seller_id');
    const region = getSelectText('region_id');
    const otype  = getSelectText('order_type');
    const pstat  = getSelectText('payment_status');
    const sd = qs('#start_date')?.value || '';
    const ed = qs('#end_date')?.value || '';

    if(pn) parts.push(`المنتج: ${pn}`);
    if(pc) parts.push(`الكود: ${pc}`);
    if(seller) parts.push(`البائع: ${seller}`);
    if(region) parts.push(`المنطقة: ${region}`);
    if(otype) parts.push(`النوع: ${otype}`);
    if(pstat) parts.push(`الدفع: ${pstat}`);
    if(sd || ed) parts.push(`الفترة: ${sd || '—'} → ${ed || '—'}`);

    return parts.join(' | ');
  }

  // ===== Print =====
  function printReport(){
    const title = 'تقرير المنتجات المباعة';
    const summary = buildFiltersSummary();
    const table = qs('#report-table').cloneNode(true);

    const w = window.open('', '', 'width=1000,height=800');
    w.document.write('<html><head><title>'+title+'</title>');
    w.document.write(`
      <style>
        @page{ size:A4; margin:12mm; }
        body{ direction:rtl; font-family:"Cairo", sans-serif; color:#0b1b36; }
        h2{ text-align:center; margin:0 0 8px; }
        p{ text-align:center; margin:0 0 12px; font-size:13px; }
        table{ width:100%; border-collapse:collapse; }
        th,td{ border:1px solid #ddd; padding:8px; text-align:center; font-size:12px; }
        thead th{
          background:#2C4470; color:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact;
        }
        tbody tr:nth-child(even) td{ background:#fbfcff; }
      </style>
    `);
    w.document.write('</head><body>');
    w.document.write('<h2>'+title+'</h2>');
    if(summary){ w.document.write('<p>'+summary+'</p>'); }
    w.document.body.appendChild(table);
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    w.print();
  }

  // ===== Excel Export (current page rows) =====
  function exportExcel(){
    const table = qs('#report-table');
    const headers = qsa('thead th', table).map(th => th.textContent.trim());
    const rows = qsa('tbody tr', table).map(tr =>
      qsa('td', tr).map(td => td.textContent.trim())
    );

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);

    // column widths
    ws['!cols'] = [
      {wch:12},{wch:26},{wch:16},{wch:12},{wch:10},{wch:14},
      {wch:18},{wch:20},{wch:14},{wch:14},{wch:16},{wch:14},{wch:16}
    ];

    XLSX.utils.book_append_sheet(wb, ws, 'Products Report');

    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth()+1).padStart(2,'0');
    const d = String(now.getDate()).padStart(2,'0');
    const hh = String(now.getHours()).padStart(2,'0');
    const mm = String(now.getMinutes()).padStart(2,'0');

    XLSX.writeFile(wb, `products_report_${y}-${m}-${d}_${hh}-${mm}.xlsx`);
  }
</script>
