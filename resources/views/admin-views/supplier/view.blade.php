@extends('layouts.admin.app')

@section('title', \App\CPU\translate('supplier_details'))

@push('css_or_js')
<style>
  .tab-toolbar{display:flex;gap:8px;align-items:center;justify-content:space-between;flex-wrap:wrap;margin-bottom:10px}
  .btn-light{background:#fff;border:1px solid #e5e7eb}
  .table tfoot th,.table tfoot td{border-top:2px solid #e5e7eb;font-weight:700;background:#fafafa}
  .table-footer{border-top:1px solid #e5e7eb;background:#fcfcfd}
  .avatar-img{width:56px;height:56px;border-radius:50%;object-fit:cover}
  .small-muted{font-size:.875rem;color:#6b7280}
  .sticky-head{position:sticky;top:0;background:#fff;z-index:2}
  @media print{
    .no-print{display:none!important}
    body{direction:rtl}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #000;padding:6px;text-align:center}
    .signatures{display:flex;justify-content:space-between;margin-top:40px}
    .sig-box{width:45%;text-align:center}
    .sig-line{margin-top:40px;border-top:1px solid #000;padding-top:6px}
  }
</style>
@endpush

@section('content')
<div class="content container-fluid">
      <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
{{ $supplier->name }}        </li>
      </ol>
    </nav>
  </div>
  {{-- Header --}}


  {{-- Tabs --}}
  @php
    $activeTab  = request('tab', 'statement'); // statement|purchases|returns|vouchers|journal|details
    $search     = trim(request('search', ''));
    $start_date = request('start_date');
    $end_date   = request('end_date');
    $accId      = $supplier->account_id;
    $currency   = \App\CPU\Helpers::currency_symbol();
  @endphp

  <ul class="nav nav-tabs page-header-tabs sticky-head" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $activeTab==='statement'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tab'=>'statement','page'=>1]) }}">{{ \App\CPU\translate('كشف حساب') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ $activeTab==='purchases'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tab'=>'purchases','page'=>1]) }}">{{ \App\CPU\translate('فواتير مشتريات') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ $activeTab==='returns'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tab'=>'returns','page'=>1]) }}">{{ \App\CPU\translate('فواتير مرتجع مشتريات') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ $activeTab==='vouchers'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tab'=>'vouchers','page'=>1]) }}">{{ \App\CPU\translate('السندات (قبض/صرف)') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ $activeTab==='journal'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tab'=>'journal','page'=>1]) }}">{{ \App\CPU\translate('القيود اليومية') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ $activeTab==='details'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tab'=>'details']) }}">{{ \App\CPU\translate('التفاصيل') }}</a></li>
  </ul>

  <div class="card mt-3">
    <div class="card-body">
      @include('admin-views.supplier.tabs._filters', [
        'activeTab' => $activeTab,
        'search'    => $search,
        'start_date'=> $start_date,
        'end_date'  => $end_date
      ])

      {{-- Tabs content (نحمّل بيانات التبويب النشط فقط لتحسين الأداء) --}}
      @if($activeTab==='statement')
        @include('admin-views.supplier.tabs.statement', compact('supplier','accId','search','start_date','end_date','currency'))
      @elseif($activeTab==='purchases')
        @include('admin-views.supplier.tabs.purchases', compact('supplier','search','start_date','end_date','currency'))
      @elseif($activeTab==='returns')
        @include('admin-views.supplier.tabs.returns', compact('supplier','search','start_date','end_date','currency'))
      @elseif($activeTab==='vouchers')
        @include('admin-views.supplier.tabs.vouchers', compact('supplier','accId','search','start_date','end_date','currency'))
      @elseif($activeTab==='journal')
        @include('admin-views.supplier.tabs.journal', compact('supplier','accId','search','start_date','end_date','currency'))
      @else
        @include('admin-views.supplier.tabs.details', compact('supplier','currency'))
      @endif
    </div>
  </div>
</div>
@endsection

<script>
  // === Export visible table to CSV (UTF-8 BOM for Arabic)
  function exportTableCSV(tableId, filenameBase){
    const table = document.getElementById(tableId);
    if(!table){ alert('No table to export'); return; }
    const rows = Array.from(table.querySelectorAll('tr'));
    const csv = [];
    rows.forEach(row=>{
      const cells = Array.from(row.querySelectorAll('th,td')).filter(td=>!td.classList.contains('no-print'));
      const line = cells.map(td=>{
        const text = td.innerText.replace(/\(\s*دائن\s*\)|\(\s*مدين\s*\)/g,'').trim();
        return (text.includes(',')||text.includes('"')||text.includes('\n')) ? `"${text.replace(/"/g,'""')}"` : text;
      }).join(',');
      csv.push(line);
    });
    const blob = new Blob(["\ufeff"+csv.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    const d    = new Date();
    const ts   = d.getFullYear()+('-0'+(d.getMonth()+1)).slice(-2)+('-0'+d.getDate()).slice(-2);
    a.href=url; a.download = filenameBase+'_'+ts+'.csv';
    document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
  }

  // === Print helper
  function printTable(tableId, title){
    const table = document.getElementById(tableId);
    if(!table){ alert('No table to print'); return; }
    const clone = table.cloneNode(true);
    clone.querySelectorAll('.no-print').forEach(el=>el.remove());

    const win = window.open('', '_blank', 'width=900,height=700');
    const dateStr = new Date().toLocaleString('ar-EG');

    win.document.open();
    win.document.write(`
      <html dir="rtl" lang="ar">
        <head>
          <title>${title}</title>
          <style>
            body{direction:rtl;font-family:Tahoma, Arial;padding:16px}
            .print-title{font-size:20px;font-weight:700;margin-bottom:6px;text-align:center}
            .print-sub{font-size:12px;color:#555;text-align:center;margin-bottom:16px}
            table{width:100%;border-collapse:collapse}
            th,td{border:1px solid #000;padding:6px;text-align:center}
            tfoot th, tfoot td{font-weight:700}
            .signatures{display:flex;justify-content:space-between;margin-top:40px}
            .sig-box{width:45%;text-align:center}
            .sig-line{margin-top:40px;border-top:1px solid #000;padding-top:6px}
          </style>
        </head>
        <body>
          <div class="print-title">${title}</div>
          <div class="print-sub">{{ \App\CPU\translate('تاريخ الطباعة') }}: ${dateStr}</div>
          ${clone.outerHTML}
          <div class="signatures">
            <div class="sig-box"><div class="sig-line">{{ \App\CPU\translate('توقيع المراجع') }}</div></div>
            <div class="sig-box"><div class="sig-line">{{ \App\CPU\translate('توقيع المدير') }}</div></div>
          </div>
          <script>window.print();<\/script>
        </body>
      </html>
    `);
    win.document.close();
  }
</script>
