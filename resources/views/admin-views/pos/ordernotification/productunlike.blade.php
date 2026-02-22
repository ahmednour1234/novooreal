@extends('layouts.admin.app')

@section('title', \App\CPU\translate('product_list_Unlike'))

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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('كشف الركود ') }}</li>
      </ol>
    </nav>
  </div>
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

  
        <div class="row">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
<table class="table" id="productsTable">
    <thead>
        <tr>
            <th>{{ \App\CPU\translate('اسم الصنف') }}</th>
            <th>{{ \App\CPU\translate('كود الصنف') }}</th>
            <th>{{ \App\CPU\translate('الكمية') }}</th>
            <th>{{ \App\CPU\translate('تاريخ اخر مرة بيع') }}</th>
            <th>{{ \App\CPU\translate('مدة الركود') }}</th>
        </tr>
    </thead>
    <tbody id="set-rows">
        @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->product_code }}</td>
<td>
    @php
        // Check if the quantity is a decimal number (contains a dot)
        $isDecimal = is_float($product['quantity']) || strpos((string)$product['quantity'], '.') !== false;

        // Calculate the result of quantity * unit_value if quantity is decimal
        $result = $product['quantity'] * $product['unit_value'];
    @endphp

    @if ($isDecimal)
        <!-- If the quantity is a decimal, display the result of the multiplication and the subunit -->
        {{ number_format($result, 2) }} <!-- Display the result with 2 decimal places -->
        {{ $product->unit->subUnits->first()?->name ?? '' }}
    @else
        <!-- If the quantity is not a decimal, display the quantity with the unit name -->
        {{ $product['quantity'] ?? 0 }}
        {{ $product->unit->unit_type ?? '' }}
    @endif
</td>            <td>
    {{ $product->last_sale_date ? \Carbon\Carbon::parse($product->last_sale_date)->format('Y-m-d') : 'N/A' }}
</td>
<td>
    {{ $product->stagnation_period ? $product->stagnation_period . ' ' . \App\CPU\translate('days') : 'N/A' }}
</td>

            </tr>
        @endforeach
    </tbody>
</table>

                        <div class="page-area">
                            <table>
                                <tfoot class="border-top">
                                </tfoot>
                            </table>
                 
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
    <div class="modal fade" id="update-quantity" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ \App\CPU\translate('update_product_quantity') }} <br>
                        <span class="text-danger">({{ \App\CPU\translate('to_decrease_product_quantity_use_minus_before_number._Ex: -10') }})</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- Your form elements for updating product quantity go here -->
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
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

