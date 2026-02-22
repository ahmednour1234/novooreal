{{-- resources/views/admin-views/stockbatch/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('دفعات المخزون'))

@push('css_or_js')
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --brand:#0d6efd;
      --bg:#f8fafc; --ok:#16a34a; --warn:#f59e0b; --bad:#dc2626;
      --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
    }
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{font-size:1.25rem;margin:0;color:var(--ink);font-weight:800}
    .toolbar{display:flex;gap:8px;flex-wrap:wrap}
    .toolbar .btn{min-height:42px}

    .filter-card{margin-bottom:16px}
    .filter-card .form-label{font-weight:700;color:#111827}
    .filter-card .form-control,.filter-card select{min-height:42px}

    .table-wrap{overflow:auto}
    table.table thead th{position:sticky;top:0;z-index:5;background:#f3f6fb}
    table.table td, table.table th{vertical-align:middle}
    .table-hover tbody tr:hover{background:#f9fbff}

    tfoot td{background:#fafafa;font-weight:800}

    /* أعمدة لا تُصدَّر / لا تُطبع */
    .no-export, .no-print-col{}

    /* الطباعة */
    @media print {
      body * { visibility: hidden; }
      .print-scope, .print-scope * { visibility: visible; }
      .print-scope { position:absolute; left:0; top:0; width:100%; }
      .no-print-col{ display:none !important; }
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('دفعات المخزون') }}</li>
      </ol>
    </nav>
  </div>

  @php
    $branchName = request('branch_id')
      ? optional(\App\Models\Branch::find(request('branch_id')))->name
      : \App\CPU\translate('كل الفروع');
  @endphp

  {{-- ====== رأس الصفحة + أدوات ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1 class="mb-0">{{ \App\CPU\translate('ملخص دفعات المخزون') }} — <span class="text-muted" style="font-weight:600">{{ $branchName }}</span></h1>
      <div class="toolbar">
        <button id="printTableBtn" class="btn btn-outline-primary" type="button" onclick="printTable()">
          <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
        </button>
        <button id="exportExcelBtn" class="btn btn-success" type="button" onclick="exportTableToExcel('stockBatchesTable')">
          <i class="tio-file-text-outlined"></i> {{ \App\CPU\translate('تصدير Excel') }}
        </button>
      </div>
    </div>
  </div>

  {{-- ====== فورم الفرع (قُرِّبت المسافات) ====== --}}
  <div class="card-soft p-2 filter-card">
    <form method="GET" action="{{ route('admin.stockbatch.index') }}" class="row align-items-end gx-2 gy-1">
      <div class="col-sm-6 col-md-5 col-lg-4">
        <label for="branch_id" class="form-label mb-1">{{ \App\CPU\translate('اختر الفرع') }}</label>
        <select name="branch_id" id="branch_id" class="form-select">
          <option value="">{{ \App\CPU\translate('كل الفروع') }}</option>
          @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-auto">
        <button type="submit" class="btn btn-primary">
          <i class="tio-search"></i> {{ \App\CPU\translate('تطبيق') }}
        </button>
      </div>
    </form>
  </div>

  {{-- ====== الجدول ====== --}}
  <div class="card-soft p-3">
    <div class="table-wrap print-scope">
      <table id="stockBatchesTable" class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>{{ \App\CPU\translate('كود المنتج') }}</th>
            <th>{{ \App\CPU\translate('اسم المنتج') }}</th>
            <th>{{ \App\CPU\translate('اسم الفرع') }}</th>
            <th>{{ \App\CPU\translate('إجمالي الكمية') }}</th>
            <th>{{ \App\CPU\translate('الكمية مع الوحدة') }}</th>
            <th>{{ \App\CPU\translate('الإجمالي (السعر × الكمية)') }}</th>
            <th class="no-export no-print-col" data-no-export="1" style="width:140px">{{ \App\CPU\translate('تفاصيل') }}</th>
          </tr>
        </thead>
        <tbody>
          @php
            $totalAllQuantity = 0;
            $totalAllPrice    = 0;
          @endphp

          @forelse($products as $row)
            @php
              $totalAllQuantity += (float) $row->total_quantity;
              $totalAllPrice    += (float) $row->total_price;

              $productModel = \App\Models\Product::find($row->product_id);

              $isInteger = floor($row->total_quantity) == $row->total_quantity;
              if ($isInteger) {
                  $displayQuantity = (float) $row->total_quantity;
                  $unitLabel = $productModel->unit->unit_type ?? '';
              } else {
                  $displayQuantity = (float) $row->total_quantity * (float) ($productModel->unit_value ?? 1);
                  $unitLabel = $productModel->unit->subUnits->first()->name ?? '';
              }
            @endphp
            <tr>
              <td>{{ $row->product_code }}</td>
              <td>{{ $row->product_name }}</td>
              <td>{{ $branchName }}</td>
              <td>{{ number_format($row->total_quantity, 2) }}</td>
              <td>{{ number_format($displayQuantity, 2) }} {{ $unitLabel }}</td>
              <td>{{ number_format($row->total_price, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
              <td class="no-export no-print-col" data-no-export="1">
                <button class="btn btn-sm btn-white" type="button" onclick="viewInvoice('{{ $row->product_id }}')">
                  <i class="tio-download"></i> {{ \App\CPU\translate('عرض الفاتورة') }}
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted">{{ \App\CPU\translate('لا توجد دفعات مخزون متاحة.') }}</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="text-center">{{ \App\CPU\translate('الإجمالي') }}</td>
            <td>{{ number_format($totalAllQuantity, 2) }}</td>
            <td>—</td>
            <td>{{ number_format($totalAllPrice, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
            <td class="no-export no-print-col" data-no-export="1"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

</div>

{{-- ====== Modal طباعة الفاتورة ====== --}}
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content card-soft">
      <div class="modal-header">
        <h5 class="modal-title" id="invoiceModalLabel">{{ \App\CPU\translate('طباعة الفاتورة') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ \App\CPU\translate('إغلاق') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="printableInvoiceArea">
        {{-- سيتم تحميل المحتوى عبر AJAX --}}
      </div>
      <div class="modal-footer">
        <button type="button" onclick="printDiv('printableInvoiceArea')" class="btn btn-primary">
          <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
        </button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ \App\CPU\translate('إغلاق') }}</button>
      </div>
    </div>
  </div>
</div>
@endsection

{{-- SheetJS + jQuery (لو مش موجودين بالـ layout) --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
      window.exportTableToExcel = function (tableId, filename = 'stockBatchesTable.xlsx') {
    const table = document.getElementById(tableId);
    if (!table) return;
    const wb = XLSX.utils.table_to_book(table, { sheet: "Entries" });
    XLSX.writeFile(wb, filename);
  };

  // ====== طباعة الجدول بالكامل ======
  function printTable(){
    const tableNode = document.getElementById('stockBatchesTable').cloneNode(true);
    // إزالة أعمدة غير قابلة للطباعة
    tableNode.querySelectorAll('.no-print-col').forEach(el => el.remove());

    const win = window.open('', '', 'width=1000,height=900');
    win.document.write(`
      <html dir="rtl">
      <head>
        <title>{{ \App\CPU\translate('طباعة الجدول') }}</title>
        <style>
          body{font-family: Tahoma, Arial, sans-serif; margin:16px;}
          table{width:100%; border-collapse:collapse;}
          th, td{border:1px solid #333; padding:8px; text-align:center; font-size:13px}
          th{background:#f2f2f2}
          h2{margin:0 0 12px; font-size:18px}
        </style>
      </head>
      <body>
        <h2>{{ \App\CPU\translate('ملخص دفعات المخزون') }} — {{ $branchName }}</h2>
    `);
    win.document.body.appendChild(tableNode);
    win.document.write(`
        <script>window.onload=function(){window.print(); window.close();};<\/script>
      </body></html>
    `);
    win.document.close();
  }

  // ====== تحويل جدول إلى مصفوفة صفوف (مع استبعاد أعمدة no-export) ======
  function tableToAoA(table){
    const aoa = [];
    const skipIdx = new Set();

    // رؤوس الأعمدة
    const ths = table.querySelectorAll('thead tr th');
    const headRow = [];
    ths.forEach((th, i) => {
      const noExp = th.classList.contains('no-export') || th.classList.contains('no-print-col') || th.dataset.noExport === '1';
      if(noExp) skipIdx.add(i);
      headRow.push(th.innerText.trim());
    });
    // استبعد الأعمدة المحظورة من الرؤوس
    aoa.push(headRow.filter((_, i)=>!skipIdx.has(i)));

    // جسم الجدول
    table.querySelectorAll('tbody tr').forEach(tr => {
      const row = [];
      tr.querySelectorAll('td').forEach((td, i) => {
        if(!skipIdx.has(i)){
          // لو بداخل الخلية زرار أو HTML، ناخد النص فقط
          row.push(td.innerText.replace(/\s+\n/g,' ').trim());
        }
      });
      // تجاهل الصفوف الفارغة بالكامل
      if(row.some(cell => cell !== '')) aoa.push(row);
    });

    // الفوتر (لو موجود)
    const tf = table.querySelector('tfoot tr');
    if(tf){
      const foot = [];
      tf.querySelectorAll('td,th').forEach((cell, i) => {
        if(!skipIdx.has(i)) foot.push(cell.innerText.trim());
      });
      if(foot.length) aoa.push(foot);
    }

    return aoa;
  }

  // ====== تنزيل CSV بديل لو XLSX غير متاح ======
  function downloadCSV(aoa, filename){
    const csv = aoa.map(row =>
      row.map(cell => {
        const c = (cell ?? '').toString().replace(/"/g,'""');
        return `"${c}"`;
      }).join(',')
    ).join('\n');

    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename.endsWith('.csv') ? filename : (filename + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // ====== تصدير إلى Excel (مع خطة بديلة CSV) ======
  document.getElementById('exportExcelBtn').addEventListener('click', function(){
    const table = document.getElementById('stockBatchesTable');
    const aoa   = tableToAoA(table);

    const today = new Date();
    const y = today.getFullYear();
    const m = String(today.getMonth()+1).padStart(2,'0');
    const d = String(today.getDate()).padStart(2,'0');
    const baseName = `stock_batches_${y}${m}${d}`;

    if (window.XLSX && XLSX.utils && XLSX.writeFile) {
      try{
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(aoa);
        XLSX.utils.book_append_sheet(wb, ws, "StockBatches");
        XLSX.writeFile(wb, `${baseName}.xlsx`);
        return;
      }catch(e){
        console.error('XLSX error:', e);
      }
    }
    // خطة بديلة CSV
    downloadCSV(aoa, `${baseName}.csv`);
  });

  // ====== عرض فاتورة المنتج في مودال + طباعة ======
  function viewInvoice(productId){
    const branchId = document.getElementById('branch_id') ? document.getElementById('branch_id').value : '';
    $.get({
      url: '{{ url("/") }}/admin/stockbatch/' + productId,
      data: { branch_id: branchId },
      dataType: 'json',
      success: function(resp){
        if(resp && resp.success){
          $('#printableInvoiceArea').html(resp.view);
          $('#invoiceModal').modal('show');
        }else{
          alert("{{ \App\CPU\translate('فشل تحميل الفاتورة') }}");
        }
      },
      error: function(){
        alert("{{ \App\CPU\translate('حدث خطأ أثناء جلب بيانات الفاتورة') }}");
      }
    });
  }
  window.viewInvoice = viewInvoice; // إتاحته للأزرار

  // طباعة محتوى عنصر
  function printDiv(divId){
    const content = document.getElementById(divId).innerHTML;
    const win = window.open('', '', 'width=900,height=800');
    win.document.write(`
      <html dir="rtl">
        <head>
          <title>{{ \App\CPU\translate('طباعة') }}</title>
          <style>
            body{font-family: Tahoma, Arial, sans-serif; margin:16px;}
            table{width:100%; border-collapse:collapse;}
            th, td{border:1px solid #333; padding:8px; text-align:center; font-size:13px}
            th{background:#f2f2f2}
          </style>
        </head>
        <body>${content}
          <script>window.onload=function(){window.print(); window.close();};<\/script>
        </body>
      </html>
    `);
    win.document.close();
  }
  window.printDiv = printDiv; // للمودال
</script>
