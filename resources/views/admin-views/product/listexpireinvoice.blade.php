{{-- resources/views/admin/products/expire_list.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('product_list'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SheetJS for Excel export --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>

<style>

  .content.container-fluid { direction: rtl; }




  .header-title{
    font-size: 22px; font-weight: 800; margin: 0;
    display: flex; align-items: center; gap: 10px;
  }
  .header-sub{
    font-size: 13px; opacity: .9; margin-top: 6px;
  }
  .toolbar{
    display: flex; gap: 8px; flex-wrap: wrap;
  }

  /* ===== Buttons (Static) ===== */
  .btn{
    border: 0; border-radius: 10px;
    padding: 10px 14px; font-weight: 700; font-size: 14px;
    cursor: pointer; transition: .18s ease;
  }
  .btn i{ margin-left: 4px; }
  .btn:disabled{ opacity:.6; cursor:not-allowed; }
  .btn-primary{ background: var(--accent); color: #0B1B36; }
  .btn-primary:hover{ filter: brightness(.96); }
  .btn-outline{
    background: transparent; color: var(--white); border: 2px solid rgba(255,255,255,.5);
  }
  .btn-outline:hover{ background: rgba(255,255,255,.08); }
  .btn-success{ background: #16A34A; color: var(--white); }
  .btn-success:hover{ filter: brightness(.95); }

 



  /* ===== Table ===== */
  .table-wrap{
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 14px;
    overflow: hidden;
    margin-top: 14px;
  }
  table{ width: 100%; border-collapse: collapse; }
  thead th{
    color: var(--white);
    font-weight: 800;
    padding: 12px 10px;
    position: sticky; top: 0; z-index: 1;
  }
  tbody td{
    border-top: 1px solid var(--line);
    padding: 10px 10px;
    text-align: center;
    font-size: 14px;
    color: #0B1B36;
  }
  tbody tr:nth-child(even) td{ background: #FBFCFF; }
  tbody tr:hover td{ background: #F2F6FF; }
  tfoot th{
    padding: 12px 10px; text-align: center; font-weight: 800;
    border-top: 2px solid var(--line);
  }

  .badge-unit{
    display: inline-block; padding: 4px 8px; border-radius: 999px;
    background: rgba(149,187,72,.15);
    color: #2C4C20; font-weight: 800; font-size: 12px;
    border: 1px solid rgba(149,187,72,.35);
  }

  /* ===== Signatures ===== */
  .signatures{
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
  }
  .sig{
    padding: 12px 10px; border: 2px dashed var(--primary);
    border-radius: 12px; text-align: center; font-weight: 800; color: var(--primary);
  }
  .sig small{ display:block; font-weight: 700; color: #334155; margin-top: 6px; }

  /* ===== Utilities ===== */
  .no-print{ }
  @media (max-width: 991.98px){
    .filters-grid .col-3{ grid-column: span 6; }
    .filters-grid .col-6{ grid-column: span 12; }
    .header-title{ font-size: 20px; }
    thead th, tbody td{ font-size: 13px; }
  }
  @media print{
    .no-print{ display: none !important; }
    @page{ size: A4; margin: 12mm; }
    body{ direction: rtl; }
    thead th{
      background: #0f172a !important; color: #fff !important;
      -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    tbody tr:nth-child(even) td{ background: #fbfcff !important; }
  }
</style>
<style>
  .actions-row{ display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
  .actions-row .btn{ min-width:140px; }
</style>
<style>
  /* شبكة الفلاتر */
  .filters-grid{ display:grid; grid-template-columns:repeat(12,1fr); gap:12px; align-items:end; }
  .filters-grid .col-6{ grid-column: span 6; }
  .filters-grid .col-12{ grid-column: span 12; }

  /* صف الأزرار */
  .actions-row{ display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
  .actions-row .btn{ min-width:140px; }

  /* تجميع التاريخ (من/إلى) */
  .date-range{ display:flex; gap:8px; align-items:center; }
  .date-range .form-control{ flex:1; }
  .date-range .sep{ color:#64748b; font-weight:700; }

  /* استجابة للشاشات الصغيرة */
  @media (max-width: 991.98px){
    .filters-grid .col-6{ grid-column: span 12; }
    .date-range{ flex-direction:column; align-items:stretch; }
    .date-range .sep{ display:none; }
  }
</style>
<style>
  :root{
    --accent:#95BB48;    /* أخضر */
    --ink:#0B1B36;
    --muted:#F6F8FB;
    --line:#E5E7EB;
  }

  /* شبكة الفلاتر */
  .filters-grid{
    display:grid; grid-template-columns:repeat(12,1fr);
    gap:12px; align-items:end;
  }
  .filters-grid .col-6{ grid-column:span 6; }
  .filters-grid .col-12{ grid-column:span 12; }

  /* حقول الإدخال */
  .form-label{ font-weight:700; font-size:13px; color:var(--primary); margin-bottom:6px; }
  .form-control,.form-select{
    width:100%; background:var(--muted); border:1px solid var(--line);
    color:var(--ink); border-radius:10px; padding:9px 12px; font-size:14px;
  }

  /* الفترة (من/إلى) */
  .date-range{ display:flex; gap:8px; align-items:center; }
  .date-range .form-control{ flex:1; }
  .date-range .sep{ color:#64748b; font-weight:700; }

  /* صف الأزرار */
  .actions-row{ display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
  .actions-row .btn{ min-width:140px; border-radius:10px; font-weight:700; }

  /* ألوان الأزرار (ثابتة) */
  .btn-primary{ background:var(--primary); border-color:var(--primary); color:#fff; }
  .btn-primary:hover{ filter:brightness(.96); }

  .btn-secondary{ background:#E2E8F0; border-color:#E2E8F0; color:var(--ink); }
  .btn-secondary:hover{ filter:brightness(.97); }

  .btn-success{ background:var(--accent); border-color:var(--accent); color:#0B1B36; }
  .btn-success:hover{ filter:brightness(.96); }

  .btn-danger{ background:var(--danger); border-color:var(--danger); color:#fff; }
  .btn-danger:hover{ filter:brightness(.96); }

  .btn-info{ background:var(--info); border-color:var(--info); color:#fff; }
  .btn-info:hover{ filter:brightness(.96); }

  /* استجابة للموبايل */
  @media (max-width: 991.98px){
    .filters-grid .col-6{ grid-column:span 12; }
    .date-range{ flex-direction:column; align-items:stretch; }
    .date-range .sep{ display:none; }
    .actions-row .btn{ flex:1 1 48%; min-width:auto; }
  }
  .signatures{
      display: none;
  }
</style>

@endpush

@section('content')
@php
    // ===== Preload settings once =====
    $get = fn($key) => optional(\App\Models\BusinessSetting::where('key',$key)->first())->value;

    $shop = [
        'vat_reg_no'  => $get('vat_reg_no'),
        'number_tax'  => $get('number_tax'),
        'shop_email'  => $get('shop_email'),
        'shop_name'   => $get('shop_name'),
        'shop_address'=> $get('shop_address'),
        'shop_phone'  => $get('shop_phone'),
        'shop_logo'   => $get('shop_logo') ? asset('storage/app/public/shop/'.$get('shop_logo')) : null,
    ];
    $taxRate = (float) ($get('tax') ?? 0);

    $fromDate = request('from_date');
    $toDate   = request('to_date');
    $sellerId = request('seller_id');
@endphp

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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('منتجات هالك') }}</li>
      </ol>
    </nav>
  </div>
  <div class="page-frame">



    {{-- ===== Filters ===== --}}
<div class="card card-pad mb-3 no-print">
  <form method="GET" action="{{ route('admin.product.listexpireinvoice') }}">
    <div class="filters-grid">
      {{-- الصف الأول: الفترة + البائع --}}
      <div class="col-6">
        <label class="form-label d-block">الفترة</label>
        <div class="date-range">
          <input type="date" id="from_date" name="from_date" class="form-control" value="{{ $fromDate }}">
          <span class="sep">—</span>
          <input type="date" id="to_date" name="to_date" class="form-control" value="{{ $toDate }}">
        </div>
      </div>

      <div class="col-6">
        <label for="seller_id" class="form-label">البائع</label>
        <select id="seller_id" name="seller_id" class="form-select">
          <option value="">اختر البائع</option>
          @foreach($sellers as $seller)
            <option value="{{ $seller->id }}" {{ (string)$sellerId === (string)$seller->id ? 'selected' : '' }}>
              {{ $seller->email }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- الصف الثاني: الأزرار --}}
      <div class="col-12">
        <div class="actions-row">
          <button type="submit" class="btn btn-secondary">بحث</button>
          <a href="{{ route('admin.product.listexpireinvoice') }}" class="btn btn-danger">إلغاء</a>
          <button type="button" class="btn btn-primary" onclick="printTable()">طباعة الجدول</button>
          <button type="button" class="btn btn-info" onclick="printRows()">طباعة المحدد</button>
          <button type="button" class="btn btn-success" onclick="exportExcel(false)">Excel (الكل)</button>
        </div>
      </div>
    </div>
  </form>
</div>

    {{-- ===== Table ===== --}}
    <div class="table-wrap">
      <table id="expire-table" class="table">
        <thead>
          <tr>
            <th class="no-print" style="width:44px"><input type="checkbox" id="select-all" title="تحديد الكل"></th>
            <th style="width:60px">#</th>
            <th>اسم المنتج</th>
            <th>كود المنتج</th>
            <th>الكمية</th>
            <th>الوحدة</th>
            <th>القيمة شاملة ضريبة</th>
            <th>ملاحظة</th>
            <th>التاريخ</th>
          </tr>
        </thead>
        <tbody>
        @php
          $totalQuantityUnit0 = 0; // حبة
          $totalQuantityUnit1 = 0; // كرتون
          $totalPrice         = 0;
        @endphp

        @foreach($productsexpires as $key => $productexpire)
          @php
            $prod        = $productexpire->product;
            $prodName    = $prod->name ?? 'غير متوفر';
            $prodCode    = $prod->product_code ?? 'غير متوفر';
            $unitValue   = (float) ($prod->unit_value ?? 1);
            $quantityRaw = (float) $productexpire->quantity;
            $unitIsCarton= ((int) $productexpire->unit === 1);

            // إذا كانت الوحدة فرعية (حبة) نُظهر الكمية بالحبات = الكمية * قيمة الوحدة
            $quantityShown = $unitIsCarton ? $quantityRaw : ($quantityRaw * max($unitValue, 1));

            if($unitIsCarton){ $totalQuantityUnit1 += $quantityRaw; }
            else{ $totalQuantityUnit0 += $quantityShown; }

            $unitName = 'غير متوفر';
            if($unitIsCarton){
              $unitName = optional(optional($prod)->unit)->unit_type ?? 'غير متوفر';
            } else {
              $unitName = optional(optional(optional($prod)->unit)->subUnits->first())->name ?? 'غير متوفر';
            }

            $priceWT = ((float)$productexpire->price * $quantityRaw);
            $priceWT += ($priceWT * ($taxRate/100));
            $totalPrice += $priceWT;
          @endphp
          <tr>
            <td class="no-print"><input type="checkbox" class="row-check" aria-label="تحديد الصف"></td>
            <td>{{ $key + 1 }}</td>
            <td>{{ $prodName }}</td>
            <td>{{ $prodCode }}</td>
            <td>{{ number_format($quantityShown, 2) }}</td>
            <td><span class="badge-unit">{{ $unitName }}</span></td>
            <td>{{ number_format($priceWT, 2) }}</td>
            <td>{{ $productexpire->note ?? '—' }}</td>
            <td>{{ optional($productexpire->created_at)->format('Y-m-d') }}</td>
          </tr>
        @endforeach
        </tbody>

        <tfoot>
          <tr>
            <th class="no-print"></th>
            <th colspan="2">الإجمالي</th>
            <th>—</th>
            <th>
              حبة: {{ number_format($totalQuantityUnit0, 2) }}<br>
              كرتون: {{ number_format($totalQuantityUnit1, 2) }}
            </th>
            <th>—</th>
            <th>{{ number_format($totalPrice, 2) }}</th>
            <th colspan="2">—</th>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- ===== Signatures (print area) ===== --}}
    <div class="signatures mt-3">
      <div class="sig">توقيع المندوب<small>اسم وتوقيع</small></div>
      <div class="sig">توقيع المحاسب<small>اسم وتوقيع</small></div>
      <div class="sig">توقيع أمين المخازن<small>اسم وتوقيع</small></div>
    </div>

    {{-- ===== Hidden print header template ===== --}}
    <template id="print-header">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;border-bottom:2px solid #2C4470;padding-bottom:10px;margin-bottom:18px;flex-wrap:wrap;">
        <div style="min-width:220px">
          <div><strong>رقم السجل التجاري:</strong> {{ $shop['vat_reg_no'] }}</div>
          <div><strong>الرقم الضريبي:</strong> {{ $shop['number_tax'] }}</div>
          <div><strong>البريد الإلكتروني:</strong> {{ $shop['shop_email'] }}</div>
        </div>
        <div style="text-align:center;">
          @if($shop['shop_logo'])
            <img src="{{ $shop['shop_logo'] }}" alt="Logo" style="max-width:150px;height:auto;">
          @else
            <div style="font-weight:800;color:#2C4470;font-size:20px">{{ $shop['shop_name'] }}</div>
          @endif
        </div>
        <div style="min-width:220px">
          <div><strong>اسم المؤسسة:</strong> {{ $shop['shop_name'] }}</div>
          <div><strong>العنوان:</strong> {{ $shop['shop_address'] }}</div>
          <div><strong>رقم الجوال:</strong> {{ $shop['shop_phone'] }}</div>
        </div>
      </div>
    </template>

  </div>
</div>
@endsection

<script>
  // ===== Helpers =====
  function qs(sel, ctx=document){ return ctx.querySelector(sel); }
  function qsa(sel, ctx=document){ return Array.from(ctx.querySelectorAll(sel)); }

  function getQueryParameter(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }
  function getDateRangeText(){
    const from_date = getQueryParameter("from_date") || "غير محدد";
    const to_date   = getQueryParameter("to_date") || "غير محدد";
    return `من تاريخ: ${from_date} إلى تاريخ: ${to_date}`;
  }
  function openPrintWindow(title){
    const w = window.open('', '', 'width=900,height=700');
    w.document.write('<html><head><title>'+title+'</title>');
    w.document.write(`
      <style>
        @media print{
          body{ font-family: "Cairo", sans-serif; direction:rtl; text-align:right; }
          table{ border-collapse: collapse; width:100%; }
          th,td{ border:1px solid #ddd; padding:8px; text-align:center; font-size:13px; }
          thead th{ background:#2C4470; color:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
          tr:nth-child(even) td{ background:#fbfcff; }
        }
      </style>
    `);
    w.document.write('</head><body>');
    w.document.write(qs('#print-header').innerHTML);
    return w;
  }

  // ===== Remove checkbox column for a cloned table =====
  function removeNoPrintColumn(tableEl){
    const headerNoPrint = tableEl.querySelector('thead th.no-print');
    let idx = -1;
    if(headerNoPrint){
      idx = Array.from(headerNoPrint.parentNode.children).indexOf(headerNoPrint);
    }else{
      // fallback: assume first column is selection
      idx = 0;
    }
    if(idx >= 0){
      qsa('tr', tableEl).forEach(tr => tr.children[idx]?.remove());
    }
  }

  // ===== Print All =====
  function printTable() {
    const title = "قائمة أوامر الهالك";
    const range = getDateRangeText();
    const table = qs('#expire-table').cloneNode(true);
    removeNoPrintColumn(table);

    const w = openPrintWindow(title);
    w.document.write(`<h2 style="text-align:center;margin-bottom:0">${title}</h2>`);
    w.document.write(`<p style="text-align:center;margin:4px 0 10px;font-size:13px">${range}</p>`);
    w.document.body.appendChild(table);
    w.document.write(`
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:16px;padding-top:16px;border-top:2px dashed #2C4470;">
        <div style="text-align:center;font-weight:700;color:#2C4470">توقيع المندوب<div style="margin-top:8px;color:#333;font-weight:600">اسم وتوقيع</div></div>
        <div style="text-align:center;font-weight:700;color:#2C4470">توقيع المحاسب<div style="margin-top:8px;color:#333;font-weight:600">اسم وتوقيع</div></div>
        <div style="text-align:center;font-weight:700;color:#2C4470">توقيع أمين المخازن<div style="margin-top:8px;color:#333;font-weight:600">اسم وتوقيع</div></div>
      </div>
    `);
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    w.print();
  }

  // ===== Print Selected Rows =====
  function printRows() {
    const checks = qsa('#expire-table tbody .row-check:checked');
    if(!checks.length){ alert('يرجى تحديد سطور للطباعة'); return; }

    const title = "قائمة أوامر الهالك (سطور محددة)";
    const range = getDateRangeText();

    // Build new table with selected rows
    const table = document.createElement('table');
    table.innerHTML = `
      <thead>
        <tr>
          <th>#</th>
          <th>اسم المنتج</th>
          <th>كود المنتج</th>
          <th>الكمية</th>
          <th>الوحدة</th>
          <th>القيمة شاملة ضريبة</th>
          <th>ملاحظة</th>
          <th>التاريخ</th>
        </tr>
      </thead>
      <tbody></tbody>
    `;
    const tbody = table.querySelector('tbody');

    checks.forEach((chk, i)=>{
      const tr = chk.closest('tr');
      const row = document.createElement('tr');

      // cells: [checkbox, #, name, code, qty, unit, price, note, date]
      const tds = qsa('td', tr);
      const data = [
        (i+1),
        tds[2].textContent.trim(),
        tds[3].textContent.trim(),
        tds[4].textContent.trim(),
        tds[5].textContent.trim(),
        tds[6].textContent.trim(),
        tds[7].textContent.trim(),
        tds[8].textContent.trim()
      ];
      data.forEach(v=>{
        const td = document.createElement('td');
        td.textContent = v;
        td.style.textAlign = 'center';
        row.appendChild(td);
      });
      tbody.appendChild(row);
    });

    const w = openPrintWindow(title);
    w.document.write(`<h2 style="text-align:center;margin-bottom:0">${title}</h2>`);
    w.document.write(`<p style="text-align:center;margin:4px 0 10px;font-size:13px">${range}</p>`);
    w.document.body.appendChild(table);
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    w.print();
  }

  // ===== Select All Toggle + Indeterminate =====
  document.addEventListener('DOMContentLoaded', ()=>{
    const selAll = qs('#select-all');
    const rowChecks = () => qsa('#expire-table tbody .row-check');

    if(selAll){
      selAll.addEventListener('change', ()=>{
        rowChecks().forEach(c=> c.checked = selAll.checked);
        selAll.indeterminate = false;
      });
    }

    // Update header checkbox indeterminate state when rows change
    document.addEventListener('change', (e)=>{
      if(!e.target.classList.contains('row-check')) return;
      const checks = rowChecks();
      const checkedCount = checks.filter(c=> c.checked).length;
      if(checkedCount === 0){
        selAll.checked = false; selAll.indeterminate = false;
      } else if(checkedCount === checks.length){
        selAll.checked = true; selAll.indeterminate = false;
      } else {
        selAll.checked = false; selAll.indeterminate = true;
      }
    });
  });

  // ===== Excel Export (all or selected) =====
  function exportExcel(selectedOnly=false){
    const headers = ['#','اسم المنتج','كود المنتج','الكمية','الوحدة','القيمة شاملة ضريبة','ملاحظة','التاريخ'];
    const rows = [];

    if(selectedOnly){
      const checks = qsa('#expire-table tbody .row-check:checked');
      if(!checks.length){ alert('يرجى تحديد السطور أولاً'); return; }
      checks.forEach((chk, idx)=>{
        const tds = qsa('td', chk.closest('tr'));
        rows.push([
          idx + 1,
          tds[2].textContent.trim(),
          tds[3].textContent.trim(),
          Number(tds[4].textContent.replace(/,/g,'')) || tds[4].textContent.trim(),
          tds[5].textContent.trim(),
          Number(tds[6].textContent.replace(/,/g,'')) || tds[6].textContent.trim(),
          tds[7].textContent.trim(),
          tds[8].textContent.trim()
        ]);
      });
    }else{
      const trs = qsa('#expire-table tbody tr');
      trs.forEach((tr)=>{
        const tds = qsa('td', tr);
        rows.push([
          tds[1].textContent.trim(),
          tds[2].textContent.trim(),
          tds[3].textContent.trim(),
          Number(tds[4].textContent.replace(/,/g,'')) || tds[4].textContent.trim(),
          tds[5].textContent.trim(),
          Number(tds[6].textContent.replace(/,/g,'')) || tds[6].textContent.trim(),
          tds[7].textContent.trim(),
          tds[8].textContent.trim()
        ]);
      });
    }

    const wb = XLSX.utils.book_new();
    const wsData = [headers, ...rows];
    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // Column widths
    ws['!cols'] = [{wch:6},{wch:30},{wch:22},{wch:12},{wch:18},{wch:22},{wch:26},{wch:16}];

    XLSX.utils.book_append_sheet(wb, ws, 'Waste-List');

    const from_date = getQueryParameter("from_date") || 'NA';
    const to_date   = getQueryParameter("to_date") || 'NA';
    const fileName  = selectedOnly
      ? `waste-selected_${from_date}_to_${to_date}.xlsx`
      : `waste-all_${from_date}_to_${to_date}.xlsx`;

    XLSX.writeFile(wb, fileName);
  }
</script>
