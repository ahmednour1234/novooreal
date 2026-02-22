@extends('layouts.admin.app')

@section('title', \App\CPU\translate('customer_details'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
<style>
  .no-underline, .no-underline:hover, .no-underline:focus, .no-underline:active { text-decoration: none !important; }
  .toolbar { display:flex; flex-wrap:wrap; gap:.5rem; }
  .toolbar .btn { min-width: 120px; }
  .tab-pane { padding: 1rem 0; }
  .table thead { background:#f5f7fa; }
  .table th, .table td { vertical-align: middle !important; }
  @media print {
    .d-print-none { display: none !important; }
    .no-print { display: none !important; }
  }
</style>
@endpush

@section('content')
@php
    // أي تبويب نشط؟
    $activeTab = request('active_tab', 'tab-sales'); // افتراضي: مبيعات
@endphp

<div class="content container-fluid">
  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3 d-print-none">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="#" class="text-primary">{{ \App\CPU\translate('ملف العميل') }}</a>
        </li>
         <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('رقم العميل') }} #{{ $customer->id }}
        </li>
      </ol>
    </nav>
  </div>

  {{-- ====== عنوان وبيانات سريعة ====== --}}


  {{-- ====== Tabs ====== --}}
  <div class="page-header d-print-none">
    <div class="js-nav-scroller hs-nav-scroller-horizontal">
      <ul class="nav nav-tabs page-header-tabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-receipts' ? 'active' : '' }}" data-toggle="tab" href="#tab-receipts" role="tab">
            {{ \App\CPU\translate('سندات قبض') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-payments' ? 'active' : '' }}" data-toggle="tab" href="#tab-payments" role="tab">
            {{ \App\CPU\translate('سندات صرف') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-sales' ? 'active' : '' }}" data-toggle="tab" href="#tab-sales" role="tab">
            {{ \App\CPU\translate('مبيعات') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-returns' ? 'active' : '' }}" data-toggle="tab" href="#tab-returns" role="tab">
            {{ \App\CPU\translate('مرتجعات') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-statement' ? 'active' : '' }}" data-toggle="tab" href="#tab-statement" role="tab">
            {{ \App\CPU\translate('كشف حساب') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-basic' ? 'active' : '' }}" data-toggle="tab" href="#tab-basic" role="tab">
            {{ \App\CPU\translate('المعلومات الأساسية') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-quotes' ? 'active' : '' }}" data-toggle="tab" href="#tab-quotes" role="tab">
            {{ \App\CPU\translate('عروض الأسعار') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activeTab==='tab-sellers' ? 'active' : '' }}" data-toggle="tab" href="#tab-sellers" role="tab">
            {{ \App\CPU\translate('المناديب') }}
          </a>
        </li>
      </ul>
    </div>
  </div>

  {{-- ====== Tab Content ====== --}}
  <div class="tab-content">
    <div class="tab-pane fade {{ $activeTab==='tab-receipts' ? 'show active' : '' }}" id="tab-receipts" role="tabpanel">
      @include('admin-views.customer.partials._receipts', ['customer' => $customer])
    </div>
    <div class="tab-pane fade {{ $activeTab==='tab-payments' ? 'show active' : '' }}" id="tab-payments" role="tabpanel">
      @include('admin-views.customer.partials._payments', ['customer' => $customer])
    </div>
    <div class="tab-pane fade {{ $activeTab==='tab-sales' ? 'show active' : '' }}" id="tab-sales" role="tabpanel">
      @include('admin-views.customer.partials._sales', ['customer' => $customer])
    </div>
    <div class="tab-pane fade {{ $activeTab==='tab-returns' ? 'show active' : '' }}" id="tab-returns" role="tabpanel">
      @include('admin-views.customer.partials._returns', ['customer' => $customer])
    </div>
    <div class="tab-pane fade {{ $activeTab==='tab-statement' ? 'show active' : '' }}" id="tab-statement" role="tabpanel">
      @include('admin-views.customer.partials._statement', ['customer' => $customer])
    </div>
    <div class="tab-pane fade {{ $activeTab==='tab-basic' ? 'show active' : '' }}" id="tab-basic" role="tabpanel">
      @include('admin-views.customer.partials._basic', ['customer' => $customer])
    </div>
    <div class="tab-pane fade {{ $activeTab==='tab-quotes' ? 'show active' : '' }}" id="tab-quotes" role="tabpanel">
      @include('admin-views.customer.partials._quotations', ['customer' => $customer])
    </div>
    <div class="tab-pane fade {{ $activeTab==='tab-sellers' ? 'show active' : '' }}" id="tab-sellers" role="tabpanel">
      @include('admin-views.customer.partials._sellers', ['customer' => $customer])
    </div>
  </div>
</div>

{{-- ====== طباعة وتصدير (عام لكل الجداول) ====== --}}
@endsection
<script>
  function activateTab(tabId){
    const url = new URL(window.location.href);
    url.searchParams.set('active_tab', tabId);
    window.history.replaceState({}, '', url.toString());
    $('.nav-link[href="#'+tabId+'"]').tab('show');
  }

  function printTableById(tableId, title=''){
    const table = document.getElementById(tableId);
    if(!table){ return alert('لا يوجد جدول للطباعة'); }
    const win = window.open('', '_blank', 'width=900,height=700');
    win.document.write(`
      <html dir="rtl" lang="ar">
      <head>
        <meta charset="UTF-8" />
        <title>${title || document.title}</title>
        <style>
          body{font-family: 'Cairo', Arial, Tahoma; padding:20px; color:#333;}
          h2{ text-align:center; margin-bottom:15px; }
          table{ width:100%; border-collapse: collapse; }
          th,td{ border:1px solid #ddd; padding:8px; text-align:center; }
          thead{ background:#f0f0f0; }
        </style>
      </head>
      <body>
        <h2>${title}</h2>
        ${table.outerHTML}
      </body>
      </html>
    `);
    win.document.close();
    win.focus();
    win.print();
    win.close();
  }

  function exportTableToCSV(tableId, filename='export.csv'){
    const table = document.getElementById(tableId);
    if(!table){ return alert('لا يوجد جدول للتصدير'); }
    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach(row=>{
      const cols = row.querySelectorAll('th,td');
      const rowData = [];
      cols.forEach(col=>{
        let text = (col.innerText || '').replace(/\n/g,' ').replace(/"/g,'""');
        rowData.push(`"${text}"`);
      });
      csv.push(rowData.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url  = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.display='none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }

  function clearTabFilters(tabKey){
    const url = new URL(window.location.href);
    // نحافظ فقط على active_tab
    [...url.searchParams.keys()].forEach(k=>{
      if(k !== 'active_tab') url.searchParams.delete(k);
    });
    url.searchParams.set('active_tab', tabKey);
    window.location.href = url.toString();
  }
</script>
