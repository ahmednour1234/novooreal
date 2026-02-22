@extends('layouts.admin.app')

@section('title', 'قائمة الضامنين')

@push('css_or_js')
<style>
  .toolbar { display:flex; gap:.5rem; align-items:center; }
  .toolbar .btn { padding:.25rem .6rem; }
  .table thead th { background:#f5f7fa; border-bottom:1px solid #e9edf5 !important; }
  .table tbody tr:hover { background:#fcfdff; }
  @media print {
    .d-print-none { display: none !important; }
    .table td, .table th { padding:.45rem .5rem; }
  }
</style>
@endpush

@section('content')
<div class="content container-fluid">
  <!-- Breadcrumb -->
  <div class="mb-3 d-print-none">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('قائمة الضمناء') }}
        </li>
      </ol>
    </nav>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <!-- أدوات الأعلى -->
      <div class="row align-items-center mb-3 d-print-none">
        <div class="col-md-6 mb-2 mb-md-0">
          <input id="searchGuarantor" type="text" class="form-control"
                 placeholder="🔍 ابحث بالاسم أو الرقم القومي أو الجوال…">
        </div>
        <div class="col-md-6">
          <div class="toolbar justify-content-md-end">
            <button type="button" class="btn btn-outline-secondary"
                    onclick="printTableById('guarantorsTable','قائمة الضامنين')">
              <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
            </button>
            <button type="button" class="btn btn-outline-primary"
                    onclick="exportTableToCSV('guarantorsTable','guarantors.csv')">
              <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
            </button>
          </div>
        </div>
      </div>

      <!-- الجدول -->
      <div class="table-responsive">
        <table id="guarantorsTable" class="table align-middle">
          <thead>
            <tr>
              <th style="width:60px;">#</th>
              <th>الاسم</th>
              <th>الرقم القومي</th>
              <th>الجوال</th>
              <th style="width:100px;">جديد</th>
              <th class="text-center" style="width:160px;">إجراءات</th>
            </tr>
          </thead>
          <tbody>
            @forelse($guarantors as $g)
              <tr>
                <td>{{ $loop->iteration + ($guarantors->currentPage() - 1) * $guarantors->perPage() }}</td>
                <td class="text-nowrap">{{ $g->name }}</td>
                <td class="text-nowrap">{{ $g->national_id }}</td>
                <td class="text-nowrap">{{ $g->phone }}</td>
                <td>
                  @if($g->created_at && $g->created_at->isToday())
                    <span class="badge badge-soft-success">{{ \App\CPU\translate('جديد') }}</span>
                  @endif
                </td>
                <td class="text-center d-print-none">
                  <a href="{{ route('admin.guarantors.show', $g->id) }}"
                     class="btn btn-sm btn-outline-primary me-1" title="{{ \App\CPU\translate('عرض') }}">
                    <i class="tio-visible"></i>
                  </a>
                  <a href="{{ route('admin.guarantors.edit', $g->id) }}"
                     class="btn btn-sm btn-outline-secondary me-1" title="{{ \App\CPU\translate('تعديل') }}">
                    <i class="tio-edit"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4">{{ \App\CPU\translate('لا يوجد ضامنين') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 d-flex justify-content-center d-print-none">
        {{ $guarantors->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
<script>
  // ====== بحث فوري داخل الجدول (يؤثر على الطباعة والتصدير) ======
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchGuarantor');
    const table = document.getElementById('guarantorsTable');
    if (!searchInput || !table) return;

    const rows = Array.from(table.tBodies[0].rows);

    searchInput.addEventListener('input', function() {
      const term = this.value.trim().toLowerCase();
      rows.forEach(row => {
        const name  = row.cells[1]?.textContent.trim().toLowerCase() || '';
        const nid   = row.cells[2]?.textContent.trim().toLowerCase() || '';
        const phone = row.cells[3]?.textContent.trim().toLowerCase() || '';
        const match = name.includes(term) || nid.includes(term) || phone.includes(term);
        row.style.display = match ? '' : 'none';
      });
    });
  });

  // ====== طباعة جدول ======
  function printTableById(tableId, title=''){
    const table = document.getElementById(tableId);
    if(!table){ return alert('لا يوجد جدول للطباعة'); }
    // انسخ فقط الصفوف الظاهرة
    const clone = table.cloneNode(true);
    Array.from(clone.tBodies[0].rows).forEach(r => { if (r.style.display === 'none') r.remove(); });
    // أخفِ عمود الإجراءات في الطباعة
    const lastTh = clone.tHead?.rows[0]?.lastElementChild; if(lastTh) lastTh.remove();
    Array.from(clone.tBodies[0].rows).forEach(r => r.lastElementChild?.remove());

    const w = window.open('', '_blank', 'width=900,height=700');
    w.document.write(`
      <html dir="rtl" lang="ar">
        <head>
          <meta charset="UTF-8" />
          <title>${title || document.title}</title>
          <style>
            body{font-family: 'Cairo', Arial, Tahoma; color:#333; padding:20px;}
            h2{text-align:center; margin-bottom:16px;}
            table{width:100%; border-collapse:collapse;}
            th,td{border:1px solid #ddd; padding:.5rem; text-align:center;}
            thead{background:#f5f7fa;}
          </style>
        </head>
        <body>
          <h2>${title}</h2>
          ${clone.outerHTML}
        </body>
      </html>
    `);
    w.document.close(); w.focus(); w.print(); w.close();
  }

  // ====== تصدير CSV (Excel) للصفوف الظاهرة فقط ======
  function exportTableToCSV(tableId, filename='export.csv'){
    const table = document.getElementById(tableId);
    if(!table){ return alert('لا يوجد جدول للتصدير'); }
    const lines = [];
    const sep = ',';

    // رؤوس الأعمدة (بدون عمود الإجراءات)
    const headers = Array.from(table.tHead.rows[0].cells).map((th,i,arr)=>{
      if(i === arr.length-1) return null; // اخر عمود للإجراءات
      return '"' + (th.innerText||'').replace(/"/g,'""') + '"';
    }).filter(Boolean);
    lines.push(headers.join(sep));

    // الصفوف الظاهرة
    Array.from(table.tBodies[0].rows).forEach(row=>{
      if(row.style.display === 'none') return;
      const cells = Array.from(row.cells).map((td,i,arr)=>{
        if(i === arr.length-1) return null; // استبعاد عمود الإجراءات
        const txt = (td.innerText||'').replace(/\s+/g,' ').trim().replace(/"/g,'""');
        return `"${txt}"`;
      }).filter(Boolean);
      lines.push(cells.join(sep));
    });

    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
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
</script>

@push('script_2')
@endpush
