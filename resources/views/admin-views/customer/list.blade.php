{{-- resources/views/admin/customers/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('قائمة العملاء'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>

{{-- Select2 CSS (CDN) --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet"/>

<style>
  .no-underline, .no-underline:hover, .no-underline:focus, .no-underline:active { text-decoration: none !important; }

  /* Toolbar spacing */
  .toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; }
  .toolbar .btn { min-width: 130px; }

  /* Bigger Select (height & font) */
  .select2-container .select2-selection--single {
    height: 48px; line-height: 48px; padding: 8px 12px;
    border: 1px solid #ced4da; border-radius: .5rem;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px; font-size: 15px;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 46px;
  }

  /* Table footer styling */
  table.table tfoot th, table.table tfoot td {
    background: #f7f7f7; font-weight: 600; border-top: 2px solid #e9ecef;
  }

  /* Hide columns tagged as .none in print only */
  @media print {
    .none { display:none !important; }
  }
</style>
@endpush

@section('content')
@php
  use Illuminate\Support\Facades\DB;

  // لو عندك مصفوفة $sellers عبارة عن IDs فقط، نحمل الإيميلات مرة واحدة بدلاً من find داخل الحلقة
  $sellerModels = collect();
  if(isset($sellers) && count($sellers)) {
      $sellerModels = \App\Models\Seller::whereIn('id', $sellers)->get(['id','email'])->keyBy('id');
  }

  // إجماليات حركة العملاء في الصفحة الحالية فقط (كما كان)
  $pageAccountIds = $customers->map(fn($c) => $c->account_id_to ?? $c->account_id ?? null)
                              ->filter()->unique()->values();

  // NOTE: لو اسم جدولك transactions غيّر السطر التالي
  $txRows = DB::table('transections')
      ->select('account_id', DB::raw('SUM(debit) AS debit_sum'), DB::raw('SUM(credit) AS credit_sum'))
      ->whereIn('account_id', $pageAccountIds)
      ->groupBy('account_id')
      ->get();

  $txByAccount = $txRows->keyBy('account_id');

  $pageDebitTotal  = 0.0;
  $pageCreditTotal = 0.0;
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
        <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('قائمة العملاء') }}
        </li>
        
      </ol>
    </nav>
  </div>

  <div class="row gx-2 gx-lg-3">
    <div class="col-12">
      <div class="card">

        {{-- ====== Header / Filters ====== --}}
        <div class="card-header">
          <form action="{{ url()->current() }}" method="GET">
            <div class="row gy-2 gx-2 align-items-end">
              {{-- Search --}}
              <div class="col-lg-5 col-md-6">
                <label class="form-label mb-1">{{ \App\CPU\translate('بحث باسم اوكود العميل') }}</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="tio-search"></i></span>
                  <input id="datatableSearch_" type="search" name="search" class="form-control"
                        placeholder="{{ \App\CPU\translate('بحث') }}"
                        value="{{ request('search','') }}">
                </div>
              </div>

              {{-- Seller (أعرضها أعرض) --}}
              <div class="col-lg-5 col-md-6">
                <label class="form-label mb-1">{{ \App\CPU\translate('اختر بائع') }}</label>
                <select name="seller" id="seller" class="form-control select2"
                        data-placeholder="{{ \App\CPU\translate('اختر بائع') }}">
                  <option value="">{{ \App\CPU\translate('الكل') }}</option>
                  @foreach($sellers as $sellerId)
                    @php $sellerModel = $sellerModels[$sellerId] ?? null; @endphp
                    @if($sellerModel)
                      <option value="{{ $sellerId }}" {{ request('seller') == $sellerId ? 'selected' : '' }}>
                        {{ $sellerModel->email }}
                      </option>
                    @endif
                  @endforeach
                </select>
              </div>

              {{-- أزرار --}}
              <div class="col-12">
                <div class="toolbar mt-2">
                  <button type="submit" class="btn btn-secondary">
                    {{ \App\CPU\translate('بحث') }}
                  </button>

                  <a href="{{ url()->current() }}" class="btn btn-danger" id="btnReset">
                    {{ \App\CPU\translate('الغاء') }}
                  </a>

                  {{-- تصدير صفحة (DOM) --}}
                  <button type="button" class="btn btn-info" id="btnExportPage">
                    {{ \App\CPU\translate('تصدير') }} (CSV - {{ \App\CPU\translate('هذه الصفحة') }})
                  </button>

          
                  <button type="button" class="btn btn-primary" id="btnPrint">
                    {{ \App\CPU\translate('طباعة') }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
        {{-- ====== End Header ====== --}}

        <div class="table-responsive datatable-custom" id="print-area">
          <table class="table" id="customers-table">
            <thead>
            <tr>
              <th>{{ \App\CPU\translate('#') }}</th>
              <th>{{ \App\CPU\translate('الاسم') }}</th>
              <th>{{ \App\CPU\translate('عدد مرات البيع') }}</th>
              <th>{{ \App\CPU\translate('عدد مرات التحصيل') }}</th>
              <th >{{ \App\CPU\translate('رقم الهاتف') }}</th>
              <th class="text-center">{{ \App\CPU\translate('دائن') }}</th>
              <th class="text-center">{{ \App\CPU\translate('مدين') }}</th>
              <th class="text-center">{{ \App\CPU\translate('الرصيد') }}</th>
              <th class="none">{{ \App\CPU\translate('التفعيل') }}</th>
              <th class="none">{{ \App\CPU\translate('الاجراءات') }}</th>
            </tr>
            </thead>

            <tbody id="set-rows">
            @foreach($customers as $key => $customer)
              @php
                $accId  = $customer->account_id_to ?? $customer->account_id ?? null;
                $sums   = $accId ? ($txByAccount[$accId] ?? null) : null;
                $debit  = $sums->debit_sum  ?? 0;
                $credit = $sums->credit_sum ?? 0;

                $pageDebitTotal  += $debit;
                $pageCreditTotal += $credit;

                $net = $debit - $credit; // مدين - دائن
              @endphp
              <tr>
                <td>{{ $customers->firstItem() + $key }}</td>
                <td>
                  <a class="text-primary no-underline" href="{{ route('admin.customer.view', [$customer['id']]) }}">
                    {{ $customer->name ?? '' }}
                  </a>
                </td>
                <td>{{ $customer->orders->count() }}</td>
                <td>{{ $customer->installments->count() }}</td>
                <td >{{ $customer->mobile }}</td>

                {{-- دائن = credit --}}
                <td class="text-center">{{ number_format($credit, 2) }}</td>
                {{-- مدين = debit --}}
                <td class="text-center">{{ number_format($debit, 2) }}</td>

                {{-- الرصيد --}}
                <td class="text-center">
                  {{ number_format(abs($net), 2) }}
                  {{ $net >= 0 ? \App\CPU\translate('مدين') : \App\CPU\translate('دائن') }}
                </td>

                <td class="none">
                  <label class="toggle-switch toggle-switch-sm">
                    <input type="checkbox" class="toggle-switch-input"
                           onclick="location.href='{{ route('admin.customer.status', [$customer['id'], $customer->active ? 1 : 0]) }}'"
                           {{ $customer->active ? 'checked' : '' }}>
                    <span class="toggle-switch-label">
                      <span class="toggle-switch-indicator"></span>
                    </span>
                  </label>
                </td>

                <td class="none">
                  @if ($customer->id != 0)
                    <a class="btn btn-white mr-1" href="{{ route('admin.customer.prices', [$customer['id']]) }}"><span class="tio-money"></span></a>
                    <a class="btn btn-white mr-1" href="{{ route('admin.customer.view', [$customer['id']]) }}"><span class="tio-visible"></span></a>
                    <a class="btn btn-white mr-1" href="{{ route('admin.customer.edit', [$customer['id']]) }}"><span class="tio-edit"></span></a>
                  @else
                    <a class="btn btn-white mr-1" href="{{ route('admin.customer.view', [$customer['id']]) }}"><span class="tio-visible"></span></a>
                  @endif
                </td>
              </tr>
            @endforeach
            </tbody>

            @php
              $pageNet = $pageDebitTotal - $pageCreditTotal;
              $totalTransactions = $customers->sum(fn($c) => $c->orders->count() + $c->installments->count());
            @endphp

            <tfoot class="bg-light">
              <tr>
                <td></td>
                <td class="text-right">{{ \App\CPU\translate('الإجمالي') }}</td>
                <td></td>
                <td class="text-center">{{ $totalTransactions }}</td>
                <td class="none"></td>
                <td class="text-center">{{ number_format($pageCreditTotal, 2) }}</td>
                <td class="text-center">{{ number_format($pageDebitTotal, 2) }}</td>
                <td class="text-center">
                  {{ number_format(abs($pageNet), 2) }}
                  {{ $pageNet >= 0 ? \App\CPU\translate('مدين') : \App\CPU\translate('دائن') }}
                </td>
                <td class="none"></td>
                <td class="none"></td>
              </tr>
            </tfoot>
          </table>

          @if($customers->count() === 0)
            <div class="text-center p-4">
              <img class="mb-3 w-one-cl" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="{{ \App\CPU\translate('Image Description') }}">
              <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
            </div>
          @endif
        </div>

        {{-- Pagination (يحافظ على الفلاتر) --}}
        <div class="page-area">
          {!! $customers->appends(request()->query())->links() !!}
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

<script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
{{-- Select2 JS (CDN) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.full.min.js"></script>

<script>
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    // Select2
    $('.select2').select2({ width: '100%', allowClear: true, placeholder: $('.select2').data('placeholder') || '' });

    // تصدير صفحة واحدة (DOM)
    const btnExportPage = document.getElementById('btnExportPage');
    if(btnExportPage){
      btnExportPage.addEventListener('click', function(){
        exportTableToCSV('customers_page_{{ now()->format("Ymd_His") }}.csv');
      });
    }

    // طباعة
    const btnPrint = document.getElementById('btnPrint');
    if(btnPrint){ btnPrint.addEventListener('click', printTable); }
  });

  // ===== تصدير CSV من الجدول (DOM) =====
  function exportTableToCSV(filename){
    const table = document.getElementById('customers-table');
    if(!table) return;

    const rows = [];
    const skipIdx = new Set();

    // Header: خُد الأعمدة التي ليست .none
    const ths = table.querySelectorAll('thead tr th');
    const header = [];
    ths.forEach((th, i) => {
      if(th.classList.contains('none')){
        skipIdx.add(i);
      } else {
        header.push(cleanCell(th.innerText));
      }
    });
    rows.push(header.join(','));

    // Body
    table.querySelectorAll('tbody tr').forEach(tr => {
      const tds = tr.querySelectorAll('td');
      const row = [];
      tds.forEach((td, i) => {
        if(skipIdx.has(i) || td.classList.contains('none')) return;
        row.push(cleanCell(td.innerText));
      });
      if(row.length) rows.push(row.join(','));
    });

    // Footer (الإجماليات)
    const tfoot = table.querySelector('tfoot');
    if(tfoot){
      tfoot.querySelectorAll('tr').forEach(tr => {
        const cells = Array.from(tr.children);
        const row = [];
        cells.forEach((cell, i) => {
          if(skipIdx.has(i) || cell.classList?.contains('none')) return;
          row.push(cleanCell(cell.innerText));
        });
        if(row.length) rows.push(row.join(','));
      });
    }

    const csv = '\uFEFF' + rows.join('\n'); // BOM للعربي
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    setTimeout(() => {
      URL.revokeObjectURL(link.href);
      document.body.removeChild(link);
    }, 0);

    function cleanCell(text){
      let t = (text || '').toString().replace(/\s+/g, ' ').trim();
      if(t.includes(',') || t.includes('"')) t = '"' + t.replace(/"/g, '""') + '"';
      return t;
    }
  }

  // ===== الطباعة =====
  function printTable(){
    const areaHtml = document.getElementById('print-area').innerHTML;

    const w = window.open('', '', 'height=800,width=1000');
    w.document.write('<html dir="rtl"><head><title>{{ \App\CPU\translate('تقرير مديونية العملاء') }}</title>');
    w.document.write('<style>');
    w.document.write(`
      @page { size: A4; margin: 12mm; }
      body { font-family: 'Cairo', Arial, sans-serif; color:#000; background:#fff; }
      .header-section{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:2px solid #000;padding-bottom:10px}
      .header-section .left,.header-section .right{width:30%;font-size:13px}
      .logo{width:30%;text-align:center}
      .logo img{max-width:150px;height:auto}
      h2{text-align:center;margin:10px 0 6px;font-size:20px}
      table{width:100%;border-collapse:collapse;background:#fff}
      th,td{border:1px solid #000;padding:6px;text-align:center;font-size:12px}
      th{background:#000;color:#fff}
      .none{display:none !important}
    `);
    w.document.write('</style></head><body>');

    // Header with shop info
    w.document.write('<div class="header-section">');
    w.document.write('<div class="right">');
    w.document.write('<p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where(["key" => "vat_reg_no"])->value("value") }}</p>');
    w.document.write('<p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where(["key" => "number_tax"])->value("value") }}</p>');
    w.document.write('<p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_email"])->value("value") }}</p>');
    w.document.write('</div>');

    let logoUrl = "{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->value('value')) }}";
    w.document.write('<div class="logo"><img src="'+logoUrl+'" alt="{{ \App\CPU\translate('شعار المتجر') }}"></div>');

    w.document.write('<div class="left">');
    w.document.write('<p><strong>اسم المتجر:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_name"])->value("value") }}</p>');
    w.document.write('<p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_address"])->value("value") }}</p>');
    w.document.write('<p><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_phone"])->value("value") }}</p>');
    w.document.write('</div>');
    w.document.write('</div>');

    w.document.write('<h2>{{ \App\CPU\translate('تقرير مديونية العملاء') }}</h2>');
    w.document.write(areaHtml);

    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    w.print();
    w.close();
  }

  // جعل الدوال متاحة عالميًا عند الحاجة
  window.printTable = printTable;
})();
</script>
@push('script_2')
@endpush
