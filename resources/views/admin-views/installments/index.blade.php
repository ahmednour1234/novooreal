@extends('layouts.admin.app')

@section('title', 'عقود الأقساط')

@push('css_or_js')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  :root{
    --ink:#0f172a; --muted:#6b7280; --brand:#0b3d91; --accent:#f59e0b;
    --grid:#e5e7eb; --paper:#ffffff; --zebra:#fbfdff; --rd:14px;
  }
  .select2-container .select2-selection--single {
    height: 38px; padding: 6px 12px; border: 1px solid #ced4da; border-radius: .375rem;
  }
  .content.container-fluid{max-width: 98%}

  /* Breadcrumb */
  .breadcrumb{direction:rtl}

  /* Filter Card */
  .filter-card{ background:var(--paper); border:1px solid var(--grid); border-radius:var(--rd); box-shadow:0 10px 26px -14px rgba(2,32,71,.18) }
  .filter-head{ padding:12px 16px; border-bottom:1px solid var(--grid); display:flex; align-items:center; justify-content:space-between; gap:10px }
  .filter-head h4{ margin:0; font-size:16px; font-weight:800; color:var(--ink) }
  .filter-body{ padding:14px 16px }
  .filter-grid{
    display:grid; gap:12px; grid-template-columns: repeat(12, 1fr); align-items:end;
  }
  .fg-3{ grid-column: span 3 }
  .fg-4{ grid-column: span 4 }
  .fg-6{ grid-column: span 6 }
  .fg-12{ grid-column: 1 / -1 }

  @media (max-width:1200px){
    .fg-3,.fg-4,.fg-6{ grid-column: span 6 }
  }
  @media (max-width:768px){
    .filter-grid{ grid-template-columns: repeat(2, 1fr) }
    .fg-3,.fg-4,.fg-6,.fg-12{ grid-column: 1 / -1 }
  }
  .filter-body label{ font-weight:700; color:var(--muted); margin-bottom:6px }

  /* Filter actions: 4 أزرار جنب بعض */
  .filter-actions{
    display:flex; gap:.5rem; flex-wrap:wrap; justify-content:flex-start;
  }
  .filter-actions .btn{
    min-width: 160px; font-weight:800; border-radius:10px; padding:10px 12px;
  }

  /* Table Card */
  .card{ border-radius:var(--rd); border:1px solid var(--grid) }
  .card-header{ background:#fff; border-bottom:1px solid var(--grid) }
  .card-body{ padding:14px }
  .table-responsive{ overflow:auto }
 
  .table-canceled{ background-color: rgba(248, 190, 28, 0.18) !important; }

  /* Hide actions column in print */
  @media print{
    .no-print{ display:none !important }
    .col-actions{ display:none !important }
  }
</style>
@endpush

@section('content')
<div class="content container-fluid">

  <!-- Breadcrumb -->
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="#" class="text-primary">
            {{ \App\CPU\translate('عقود الأقساط') }}
          </a>
        </li>
      </ol>
    </nav>
  </div>

  <!-- Filter Card -->
  <form method="GET" class="filter-card mb-3" id="filtersForm">

    <div class="filter-body">
      <div class="filter-grid">
        <div class="fg-3">
          <label>من تاريخ:</label>
          <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="fg-3">
          <label>إلى تاريخ:</label>
          <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="fg-6">
          <label>العميل:</label>
          <select name="customer_id" class="form-select select2">
            <option value="">-- الكل --</option>
            @foreach($customers as $cust)
              <option value="{{ $cust->id }}" @selected(request('customer_id') == $cust->id)>{{ $cust->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Actions: 4 أزرار جنب بعض -->
        <div class="fg-12 filter-actions">
          <button type="submit" class="btn btn-secondary">
            بحث
          </button>

          <button type="button" class="btn btn-success" onclick="exportContractsExcel()">
            تصدير Excel
          </button>

          <button type="button" class="btn btn-primary" onclick="printContractsTable()">
            طباعة الجدول
          </button>

          <a href="{{ route('admin.installments.index') }}" class="btn btn-danger">
            إلغاء
          </a>
        </div>
      </div>
    </div>
  </form>

  <!-- Table Card -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">{{ \App\CPU\translate('عقود الأقساط') }}</h5>
      <small class="text-muted">{{ \App\CPU\translate('إجمالي السجلات') }}: {{ $contracts->total() }}</small>
    </div>

    <div class="card-body">
      <div class="table-responsive shadow-sm rounded" id="contracts-table">
        <table id="contractsTable" class="table">
          <thead>
            <tr>
              <th>رقم العقد</th>
              <th>رقم الفاتورة</th>
              <th>العميل</th>
              <th>كاتب العقد</th>
              <th>نسبة الفائدة</th>
              <th>المدة (شهور)</th>
              <th>المدفوع</th>
                            <th>تاريخ الأنشاء</th>

              <th>الحالة</th>
              <th class="col-actions">إجراءات</th>
            </tr>
          </thead>
          <tbody class="text-center align-middle">
            @forelse($contracts as $contract)
              <tr class="@if($contract->status==='cancelled') table-canceled @endif">
                <td>#{{ $contract->id }}</td>
                <td>{{ $contract->order_id }}</td>
                <td>{{ $contract->customer->name ?? '-' }}</td>
                <td>{{ $contract->order->seller->email ?? '-' }}</td>
                <td>{{ $contract->interest_percent }}%</td>
                <td>{{ $contract->duration_months }}</td>
                <td>{{ $contract->scheduledInstallments()->where('status', 'paid')->count() }}</td>
<td>
  @if($contract->created_at)
    <span title="{{ $contract->created_at->timezone('Africa/Cairo')->toDayDateTimeString() }}">
      {{ $contract->created_at->timezone('Africa/Cairo')->translatedFormat('d M Y، h:mm a') }}
    </span>
    <small class="text-muted d-block">
      {{ $contract->created_at->diffForHumans() }}
    </small>
  @else
    —
  @endif
</td>

                <td>
                  @if($contract->status === 'active')
                    <span class="badge bg-success">مفعل</span>
                  @elseif($contract->status === 'cancelled')
                    <span class="badge bg-danger">ملغي</span>
                  @else
                    <span class="badge bg-secondary">{{ $contract->status }}</span>
                  @endif
                </td>

                <td class="col-actions">
                  <a href="{{ route('admin.installments.show', $contract->id) }}" class="btn btn-sm btn-outline-info">عرض</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted">لا توجد عقود</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $contracts->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Scripts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script><script>
  $(function () {
    $('.select2').select2({
      placeholder: 'اختر العميل',
      allowClear: true,
      dir: 'rtl',
      width: '100%'
    });
  });

  // ======== طباعة الجدول ========
  function printContractsTable(){
    const tableWrap = document.getElementById('contracts-table');
    if(!tableWrap){ alert('لا يوجد جدول لطباعته.'); return; }

    // ملخص الفلاتر
    const from = (document.querySelector('input[name="date_from"]')?.value || '').trim();
    const to   = (document.querySelector('input[name="date_to"]')?.value || '').trim();
    const custText = (function(){
      const sel = document.querySelector('select[name="customer_id"]');
      if(!sel) return '';
      const opt = sel.options[sel.selectedIndex];
      return opt && opt.value ? opt.text : '';
    })();

    const summary = `
      <div style="margin:8px 0 12px; border:1px dashed #cbd5e1; border-radius:10px; padding:10px; background:#fcfdff; font-family:'Cairo', Arial, sans-serif;">
        <strong>ملخص الفلاتر:</strong>
        <div style="margin-top:6px; display:flex; gap:14px; flex-wrap:wrap;">
          <span><strong>من:</strong> ${from || '—'}</span>
          <span><strong>إلى:</strong> ${to || '—'}</span>
          <span><strong>العميل:</strong> ${custText || 'الكل'}</span>
        </div>
      </div>
    `;

    const win = window.open('', '_blank', 'width=1000,height=1200');
    win.document.write(`
      <!DOCTYPE html>
      <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>طباعة - عقود الأقساط</title>
        <style>
          @page{ size:A4; margin:12mm }
          body{ font-family:'Cairo', Arial, sans-serif; color:#0f172a }
          h2{ text-align:center; margin:0 0 10px; color:#0b3d91 }
          table{ width:100%; border-collapse:collapse; font-size:13px }
          thead th{ background:#fff7e8; border-bottom:2px solid #e5e7eb; padding:8px; text-align:right }
          td{ border-bottom:1px solid #e5e7eb; padding:8px; text-align:right; vertical-align:middle }
          .col-actions{ display:none }
        </style>
      </head>
      <body onload="window.print(); window.onafterprint = () => window.close();">
        <h2>عقود الأقساط</h2>
        ${summary}
        ${tableWrap.innerHTML}
      </body>
      </html>
    `);
    win.document.close();
  }

  // ======== تصدير Excel (بدون مكتبات) ========
  function exportContractsExcel(){
    const srcTable = document.getElementById('contractsTable');
    if(!srcTable){ alert('لا يوجد جدول للتصدير.'); return; }

    // انسخ الجدول واحذف عمود الإجراءات وأي أزرار
    const table = srcTable.cloneNode(true);

    // احذف عمود الإجراءات عبر الكلاس
    const actionsHeader = table.querySelector('th.col-actions');
    if(actionsHeader){
      const idx = Array.from(actionsHeader.parentElement.children).indexOf(actionsHeader);
      // احذف من كل صف
      table.querySelectorAll('tr').forEach(tr=>{
        if(tr.children[idx]) tr.removeChild(tr.children[idx]);
      });
    }
    // أزل الأزرار إن وجدت
    table.querySelectorAll('button, a.btn').forEach(el => el.remove());

    // اسم الملف
    const from = (document.querySelector('input[name="date_from"]')?.value || '').trim();
    const to   = (document.querySelector('input[name="date_to"]')?.value || '').trim();
    const pad  = n => String(n).padStart(2,'0');
    const now  = new Date();
    const stamp = `${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}`;
    const range = (from || to) ? `_${from || 'start'}-to-${to || 'end'}` : '';
    const filename = `contracts${range}_${stamp}.xls`;

    // HTML متوافق مع Excel
    const html = `
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<style>
  table{border-collapse:collapse; width:100%}
  th,td{border:1px solid #999; padding:6px 8px; text-align:right}
  thead th{background:#f2f2f2}
</style>
</head>
<body>
  ${table.outerHTML}
</body>
</html>`.trim();

    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }
</script>
