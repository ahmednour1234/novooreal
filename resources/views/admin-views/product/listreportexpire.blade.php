{{-- resources/views/admin/products/listreportexpire.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تقرير المنتجات منتهية/قريبة الصلاحية'))

@push('css_or_js')
<style>
  :root{
    --card-bg: #ffffff;
    --brand: #001B63;
    --brand-2: #0d6efd;
    --muted: #6c757d;
    --success: #1f9d55;
    --warning: #e3a008;
    --danger: #e3342f;
    --soft: #f8f9fb;
    --border: #e9ecef;
  }

  .card{
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,.04);
  }



  .toolbar .form-control,
  .toolbar .btn{
    height: 44px;
    border-radius: 10px !important;
  }

  .toolbar .btn{
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-weight: 600;
  }

  .badge-chip{
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.35rem .6rem; border-radius:999px; font-size:.8rem; font-weight:700;
  }
  .chip-expired{ background:#fdeeee; color:var(--danger); border:1px solid #f8cccc; }
  .chip-soon{ background:#fff6e6; color:#b87400; border:1px solid #ffe3b5; }
  .chip-ok{ background:#eaf8ef; color:#228b4e; border:1px solid #c9eed7; }

  .table{ margin: 0; }
  .table thead th{
    position: sticky; top: 0; z-index: 2;
    background: #f5f7fb; border-bottom: 2px solid var(--border) !important;
    font-weight: 700; color:#334155;
  }
  .table tbody tr:hover{ background: #fcfdff; }
  .table td, .table th{ vertical-align: middle; }

  .summary{
    display:flex; flex-wrap:wrap; gap:.5rem 1rem; align-items:center;
    background: var(--soft); border-top:1px solid var(--border);
    padding: .75rem 1rem; border-bottom-left-radius:16px; border-bottom-right-radius:16px;
  }
  .summary .item{ color:#334155; font-size:.95rem; }
  .summary .item b{ color:#111827; }

  .legend{ display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
  .legend .legend-item{ display:flex; align-items:center; gap:.4rem; font-size:.85rem; color:#f1f5f9; opacity:.95; }

  .no-print {}
  @media print {
    .no-print, .no-print * { display: none !important; }
    .card, .card-header{ box-shadow: none !important; border: 0 !important; }
    .table thead th{ position: static; }
    body{ background: #fff; }
  }
</style>
@endpush

@section('content')
@php
    // نجمع إجمالي الكمية في الصفحة الحالية (آمن لأي نوع عناصر)
    $pageQtySum = 0;
@endphp

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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تقرير المنتجات منتهية/قريبة الصلاحية')}}</li>
      </ol>
    </nav>
  </div>
  <div class="row">
    <div class="col-12 mb-3 mb-lg-4">
      <div class="card">

        {{-- ===== Header / Toolbar ===== --}}
        <div class="card-header no-print">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
     

            <div class="toolbar w-100 mt-2">
              <form id="filterForm" action="{{ url()->current() }}" method="GET" class="row w-100 g-2">
                {{-- Search --}}
                <div class="col-12 col-md-6">
                  <div class="input-group input-group-merge">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="tio-search"></i></span>
                    </div>
                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                           placeholder="{{ \App\CPU\translate('بحث باسم او كود المنتج') }}"
                           aria-label="{{ \App\CPU\translate('Search') }}"
                           value="{{ request('search','') }}">
                    <button type="submit" class="btn btn-primary">
                      <i class="tio-search"></i> {{ \App\CPU\translate('بحث') }}
                    </button>
                  </div>
                </div>

                {{-- Sort --}}
                <div class="col-12 col-md-3">
                  @php $sort = request('sort_orderQty','default'); @endphp
                  <select id="sort_orderQty" name="sort_orderQty" class="form-control">
                    <option value="default" {{ $sort == 'default' ? 'selected' : '' }}>
                      {{ \App\CPU\translate('افتراضي') }}
                    </option>
                    <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>
                      {{ \App\CPU\translate('بالاسم من الاقل للاعلي') }}
                    </option>
                    <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>
                      {{ \App\CPU\translate('بالاسم من الاعلي للاقل') }}
                    </option>
                    <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>
                      {{ \App\CPU\translate('بالسعر من الاقل للاعلي') }}
                    </option>
                    <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>
                      {{ \App\CPU\translate('بالسعر من الاعلي للاقل') }}
                    </option>
                    <option value="expire_date_asc" {{ $sort == 'expire_date_asc' ? 'selected' : '' }}>
                      {{ \App\CPU\translate('تاريخ الصلاحية من الاقل للاعلي') }}
                    </option>
                    <option value="expire_date_desc" {{ $sort == 'expire_date_desc' ? 'selected' : '' }}>
                      {{ \App\CPU\translate('تاريخ الصلاحية من الاعلي للاقل') }}
                    </option>
                  </select>
                </div>

                {{-- Actions --}}
                <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
                  <button type="button" id="btnPrintTable" class="btn btn-outline-secondary no-print w-50">
                    <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
                  </button>

                  <button type="button" id="btnExportCsv" class="btn btn-success no-print w-50"
                          data-filename="products-expire-{{ now()->format('Ymd_His') }}.csv">
                    <i class="tio-file-outlined"></i> {{ \App\CPU\translate('تصدير CSV') }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        {{-- ===== End Header ===== --}}

        {{-- ===== Table ===== --}}
        <div class="table-responsive" id="product-table">
          <table class="table table-hover table-thead-bordered table-nowrap table-align-middle card-table" id="productsTable">
            <thead>
            <tr>
              <th style="width:80px">#</th>
              <th>{{ \App\CPU\translate('الكود') }}</th>
              <th>{{ \App\CPU\translate('الصلاحية') }}</th>
              <th>{{ \App\CPU\translate('الكمية') }}</th>
              <th>{{ \App\CPU\translate('الاسم') }}</th>
              <th>{{ \App\CPU\translate('المخزن') }}</th>
              <th>{{ \App\CPU\translate('السعر') }}</th>
              <th class="text-center">{{ \App\CPU\translate('الحالة') }}</th>
            </tr>
            </thead>

            <tbody id="set-rows">
            @forelse($products as $product)
              @php
                $expRaw   = $product['expiry_date'] ?? null;
                $expTxt   = $expRaw ? \Carbon\Carbon::parse($expRaw)->format('d-m-Y') : '--';
                $exp      = $expRaw ? \Carbon\Carbon::parse($expRaw) : null;
                $isPast   = $exp ? $exp->isPast() : false;
                $daysDiff = $exp ? now()->diffInDays($exp, false) : null; // سالب لو منتهي
                $status   = $isPast ? 'expired' : (($daysDiff !== null && $daysDiff <= 30) ? 'soon' : 'ok');
                $qtyVal   = (float)($product['quantity'] ?? 0);
                $pageQtySum += $qtyVal;
                $storeName = optional(optional($product->stock)->store)->store_name1 ?? '—';
              @endphp

              <tr>
                <td>{{ $product['id'] }}</td>
                <td class="text-monospace">{{ $product['product_code'] ?? '—' }}</td>
                <td>{{ $expTxt }}</td>
                <td>{{ rtrim(rtrim(number_format($qtyVal, 2, '.', ''), '0'), '.') }}</td>
                <td>
                  <div class="fw-600">{{ $product['name'] }}</div>
                </td>
                <td>{{ $storeName }}</td>
                <td>{{ number_format((float)($product['selling_price'] ?? 0), 2) }}</td>
                <td class="text-center">
                  @if($status === 'expired')
                    <span class="badge-chip chip-expired">{{ \App\CPU\translate('منتهي') }}</span>
                  @elseif($status === 'soon')
                    <span class="badge-chip chip-soon">{{ \App\CPU\translate('قريب الانتهاء') }}</span>
                  @else
                    <span class="badge-chip chip-ok">{{ \App\CPU\translate('سليم') }}</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  {{ \App\CPU\translate('لا توجد بيانات') }}
                </td>
              </tr>
            @endforelse
            </tbody>
          </table>
        </div>
        {{-- ===== End Table ===== --}}

        {{-- ===== Summary & Pagination ===== --}}
        <div class="summary no-print">
          <div class="item">{{ \App\CPU\translate('إجمالي المعروض في الصفحة') }}: <b>{{ $products->count() }}</b></div>
          <div class="item">{{ \App\CPU\translate('إجمالي الكمية (صفحة)') }}: <b>{{ rtrim(rtrim(number_format($pageQtySum, 2, '.', ''), '0'), '.') }}</b></div>
          <div class="ms-auto">
            {!! $products->appends(request()->query())->links() !!}
          </div>
        </div>
        {{-- ===== End Summary ===== --}}
      </div>
    </div>
  </div>
</div>
@endsection

<script>
(function(){
  // attach listener safely
  function on(id, event, handler){
    var el = document.getElementById(id);
    if(el) el.addEventListener(event, handler);
  }

  document.addEventListener('DOMContentLoaded', function(){

    // تغيير الترتيب => ارسل الفورم
    on('sort_orderQty', 'change', function(){
      var f = document.getElementById('filterForm');
      if(f) f.submit();
    });

    // ==== طباعة الجدول فقط ====
    on('btnPrintTable', 'click', function(){
      var table = document.getElementById('productsTable');
      if(!table) return;

      var title = @json(\App\CPU\translate('تقرير المنتجات منتهية/قريبة الصلاحية'));
      var now   = new Date();
      var stamp = now.toLocaleString('ar-EG');

      var win = window.open('', '_blank', 'width=1200,height=800');
      win.document.write(
        '<!doctype html>' +
        '<html lang="ar" dir="rtl">' +
        '<head>' +
          '<meta charset="utf-8">' +
          '<title>'+ title +'</title>' +
          '<style>' +
            'body{font-family:sans-serif;padding:20px;color:#111827;}' +
            'h2{margin:0 0 12px;font-size:20px;}' +
            'small{color:#6b7280;}' +
            'table{width:100%;border-collapse:collapse;direction:rtl;margin-top:8px;}' +
            'thead{background:#f5f7fb;}' +
            'th,td{border:1px solid #e5e7eb;padding:.55rem .6rem;text-align:right;font-size:13px;}' +
          '</style>' +
        '</head>' +
        '<body>' +
          '<h2>'+ title +'</h2>' +
          '<small>'+ stamp +'</small>' +
          table.outerHTML +
          '<script>window.onload=function(){window.print();setTimeout(function(){window.close()},300)}<\/script>' +
        '</body></html>'
      );
      win.document.close();
      win.focus();
    });

    // ==== تصدير CSV (UTF-8 + BOM) من الجدول الظاهر ====
    on('btnExportCsv', 'click', function(e){
      var btn   = e.currentTarget;
      var fname = (btn && btn.dataset) ? (btn.dataset.filename || 'export.csv') : 'export.csv';
      var table = document.getElementById('productsTable');
      if(!table) return;

      var rows    = Array.prototype.slice.call(table.querySelectorAll('tr'));
      var csvRows = [];

      rows.forEach(function(tr){
        var cells = Array.prototype.slice.call(tr.querySelectorAll('th,td')).map(function(td){
          var text = (td.innerText || '').replace(/\r?\n|\r/g, ' ').trim();
          // اضمن تحويل الفواصل والأقواس
          if (/[",\n]/.test(text)) text = '"' + text.replace(/"/g, '""') + '"';
          return text;
        });
        csvRows.push(cells.join(','));
      });

      var csvContent = '\uFEFF' + csvRows.join('\n'); // BOM للعربية
      var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      var url  = window.URL.createObjectURL(blob);
      var a    = document.createElement('a');
      a.style.display = 'none';
      a.href = url;
      a.download = fname;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      a.remove();
    });

  });
})();
</script>
