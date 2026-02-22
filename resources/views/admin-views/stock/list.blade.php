@extends('layouts.admin.app')

@section('title', \App\CPU\translate('stock_limit_products_list'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  /* ===== ألوان عامة خفيفة ومتناسقة ===== */
  :root{
    --brand:#001B63;       /* لون العناوين */
    --accent:#10b981;      /* زرار نجاح */
    --muted:#6b7280;       /* نصوص ثانوية */
    --danger:#ef4444;      /* نواقص حرجة */
    --warn:#f59e0b;        /* نواقص متوسطة */
    --bg:#f5f7fb;          /* خلفية عامة */
    --card:#ffffff;        /* خلفية الكارد */
    --border:#e5e7eb;      /* حدود خفيفة */
  }

  body { background: var(--bg); }

  /* ===== شريط العنوان/الأكشنز ===== */
  .page-actions{
    display:flex; gap:.5rem; align-items:center; justify-content:flex-end;
  }

  /* ===== الكارد ===== */
  .card{
    border: 1px solid var(--border);
    box-shadow: 0 4px 18px rgba(0,0,0,.04);
    border-radius: 16px;
    overflow: hidden;
  }
  .card-header{
    background: linear-gradient(135deg, var(--brand) 0%, #03308d 100%);
    color:#fff; border-bottom:none; padding:1rem 1.25rem;
  }
  .card-header h3{
    font-size:1.15rem; margin:0; display:flex; align-items:center; gap:.5rem;
  }
  .card-header .result-count{
    background: rgba(255,255,255,.15);
    padding:.15rem .5rem; border-radius:999px; font-size:.85rem;
  }

  /* ===== نموذج البحث ===== */
  .search-box .form-control{
    border-radius: 10px 0 0 10px; border: none; box-shadow: inset 0 0 0 1px rgba(255,255,255,.25);
  }
  .search-box .btn{
    border-radius: 0 10px 10px 0; border: none;
  }

  /* ===== الجدول ===== */
  .table-wrapper{ position: relative; }
  .table{
    margin:0; background: var(--card);
  }
  thead th{
    position: sticky; top: 0; z-index: 2;
    background:#f9fafb; color:#111827; font-weight:700; border-bottom:1px solid var(--border);
  }
  tbody td, thead th{ vertical-align: middle; }
  tbody tr:hover{ background:#fafcff; }
  .tbl-img{
    width: 56px; height:56px; object-fit:cover; border-radius:10px;
    border:1px solid var(--border); background:#fff;
  }
  .code-badge{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    background:#eef2ff; color:#3730a3; padding:.15rem .45rem; border-radius:.35rem; font-size:.8rem;
  }
  .price{ white-space:nowrap; }

  /* إبراز حالة المخزون (تقديرية حسب الكمية) */
  tr.low-crit td{ box-shadow: inset 4px 0 0 var(--danger); }
  tr.low-warn td{ box-shadow: inset 4px 0 0 var(--warn); }

  /* ===== حالة لا يوجد بيانات ===== */
  .empty-state{
    text-align:center; padding:2.5rem 1rem; color: var(--muted);
  }
  .empty-state img{ width:160px; max-width:50%; opacity:.9; }

  /* ===== الطباعة ===== */
  @media print{
    .no-print{ display:none !important; }
    body{ background:#fff; }
    .card{ box-shadow:none; border:none; }
    thead th{ background:#eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .print-header{
      display:block; margin-bottom:1rem; text-align:center; border-bottom:1px solid #ccc; padding-bottom:.5rem;
    }
  }

  /* ===== موبايل ===== */
  @media (max-width: 767.98px){
    .page-actions{ justify-content: stretch; }
    .page-actions .btn{ flex:1 1 50%; }
    /* اخفاء عمود الصورة على الشاشات الصغيرة لتوفير المساحة */
    .col-img{ display:none; }
    thead th, tbody td{ font-size:.92rem; }
  }
</style>
@endpush

@section('content')
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('كشف نواقص المنتجات') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== Page Header / Actions ====== --}}
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3 no-print">

    <div class="page-actions col-12 col-md-6 col-lg-4">
      <button type="button" id="btnPrintTable" class="btn btn-outline-secondary w-50">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button
        type="button"
        id="btnExportCsv"
        class="btn btn-success w-50"
        data-filename="stock-limit-{{ now()->format('Ymd_His') }}.csv">
        <i class="tio-file-outlined"></i> {{ \App\CPU\translate('تصدير CSV') }}
      </button>
    </div>
  </div>

  {{-- ====== Card ====== --}}


    {{-- ====== Table ====== --}}
    <div class="table-responsive table-wrapper">
      <table class="table" id="productsTable">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>{{ \App\CPU\translate('الاسم') }}</th>
            <th class="col-img">{{ \App\CPU\translate('الصورة') }}</th>
            <th>{{ \App\CPU\translate('اسم المورد') }}</th>
            <th>{{ \App\CPU\translate('كود الصنف') }}</th>
            <th>{{ \App\CPU\translate('سعر الشراء') }}</th>
            <th>{{ \App\CPU\translate('سعر البيع ') }}</th>
            <th>{{ \App\CPU\translate('رصيد المخزن') }}</th>
            <th>{{ \App\CPU\translate('عدد مرات البيع') }}</th>
          </tr>
        </thead>
        <tbody id="set-rows">
        @forelse($products as $key => $product)
          @php
            $rowNumber = $products->firstItem() + $key;
            $qty = (float) ($product['quantity'] ?? 0);
            $rowClass = $qty <= 0 ? 'low-crit' : ($qty <= 3 ? 'low-warn' : '');
          @endphp
          <tr class="{{ $rowClass }}">
            <td>{{ $rowNumber }}</td>

            <td>
              <div class="fw-600 text-body" title="{{ $product['name'] }}">
                {{ \Illuminate\Support\Str::limit($product['name'], 40) }}
              </div>
            </td>

            <td class="col-img">
              <img class="tbl-img"
                   src="{{ asset('storage/app/public/product') }}/{{ $product['image'] }}"
                   onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'"
                   alt="{{ $product['name'] }}">
            </td>

            <td>
              @if($product->supplier)
                <div class="fw-600">{{ $product->supplier->name }}</div>
                <div class="text-muted small">{{ $product->supplier->mobile }}</div>
              @else
                <span class="text-muted">{{ \App\CPU\translate('لايوجد') }}</span>
              @endif
            </td>

            <td><span class="code-badge">{{ $product['product_code'] }}</span></td>

            <td class="price">
              {{ number_format((float)$product['purchase_price'], 2) }}
              {{ \App\CPU\Helpers::currency_symbol() }}
            </td>

            <td class="price">
              {{ number_format((float)$product['selling_price'], 2) }}
              {{ \App\CPU\Helpers::currency_symbol() }}
            </td>

            <td>
              <strong>{{ $qty }}</strong>
              @if($qty <= 0)
                <span class="badge bg-danger ms-1">{{ \App\CPU\translate('نفد') }}</span>
              @elseif($qty <= 3)
                <span class="badge bg-warning ms-1">{{ \App\CPU\translate('منخفض') }}</span>
              @endif
            </td>

            <td>
              <span class="badge bg-light text-dark">
                {{ (int) ($product->order_count ?? 0) }}
              </span>
            </td>
          </tr>
        @empty
          {{-- سيتم إظهار حالة فارغة بالأسفل أيضاً --}}
        @endforelse
        </tbody>
      </table>
    </div>

    {{-- ====== Pagination & Empty State ====== --}}
    @if($products->count())
      <div class="d-flex flex-wrap align-items-center justify-content-between px-3 py-2 border-top">
        <div class="small text-muted">
          {{ \App\CPU\translate('عرض') }}
          {{ $products->firstItem() }}–{{ $products->lastItem() }}
          {{ \App\CPU\translate('من') }}
          {{ number_format($products->total()) }}
        </div>
        <div class="page-area mb-0">
          {!! $products->links() !!}
        </div>
      </div>
    @else
      <div class="empty-state">
        <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="No data">
        <p class="mb-0 mt-3">{{ \App\CPU\translate('لا توجد بيانات لعرضها') }}</p>
      </div>
    @endif

  </div>{{-- /card --}}

  {{-- هيدر للطباعة فقط --}}
  <div class="print-header d-none">
    <h3>{{ \App\CPU\translate('كشف نواقص المنتجات') }}</h3>
    <div class="small">{{ \App\CPU\translate('تاريخ الطباعة') }}: {{ now()->format('Y-m-d H:i') }}</div>
  </div>

</div>
@endsection

{{-- ====== Scripts ====== --}}
<script>
  (function(){
    const $ = (s, r=document)=> r.querySelector(s);
    const $$ = (s, r=document)=> Array.from(r.querySelectorAll(s));

    // طباعة الجدول
    const printBtn = $('#btnPrintTable');
    if (printBtn){
      printBtn.addEventListener('click', () => {
        window.print();
      });
    }

    // تصدير CSV مع BOM ليتعرف عليه Excel عربى
    const exportBtn = $('#btnExportCsv');
    if (exportBtn){
      exportBtn.addEventListener('click', () => {
        const filename = exportBtn.getAttribute('data-filename') || 'export.csv';
        const table = $('#productsTable');
        if(!table) return;

        const rows = $$('tr', table);
        const csv = rows.map(tr => {
          const cells = [...tr.children].map(td => {
            // تخطي الصور، وخذ النص فقط
            const text = (td.innerText || '').trim().replace(/\s+/g,' ');
            // لف بقوسين مزدوجين مع هروب القوسين داخل النص
            return `"${text.replace(/"/g,'""')}"`;
          });
          return cells.join(',');
        }).join('\r\n');

        const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        URL.revokeObjectURL(url);
        link.remove();
      });
    }
  })();
</script>
