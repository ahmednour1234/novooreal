{{-- resources/views/admin-views/costcenter/reports/transactions.blade.php --}}
@extends('layouts.admin.app')

@section('title', __('حركات مراكز التكلفة'))

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

@php
    $hasSearch = request()->filled('from_date')
        || request()->filled('to_date')
        || request()->filled('reference')
        || request()->filled('desc_like')
        || request()->filled('writer_id')
        || request()->filled('account_id')
        || request()->filled('branch_id')
        || request()->filled('cost_center_id')
        || request()->boolean('with_branch_children')
        || request()->boolean('with_cc_children')
        || request()->boolean('include_null_cost_center');
@endphp

<style>
  :root{
    --line:#e9eef5; --soft:#fff; --bg:#f6f8ff; --ink:#0f172a;
    --muted:#667085; --chip:#f8fafc; --zebra:#fbfdff; --radius:14px;
  }
  .page-wrap{direction:rtl}
  .breadcrumb{border:1px solid var(--line); border-radius:10px}
  .filter-card{border:1px solid var(--line); border-radius:var(--radius); overflow:hidden}
  .filter-card .card-header{background:var(--bg); border-bottom:1px solid var(--line)}
  .select2-container{width:100%!important;min-width:0}
  .select2-container .select2-selection--single{height:38px}
  .select2-container .select2-selection__rendered{line-height:38px}
  .select2-container .select2-selection__arrow{height:38px}
  .btn-eq{min-width:122px}
  .btn{border-radius:10px}
  .btn + .btn{margin-inline-start:10px}
  .chip{display:inline-flex;align-items:center;gap:6px;background:var(--chip);border:1px solid #e5e7eb;border-radius:999px;padding:.28rem .65rem;font-size:.85rem;margin-inline-start:6px}
  thead th.sticky{position:sticky;top:0;background:var(--bg);z-index:2}
  .table thead th{white-space:nowrap;border-bottom:1px solid var(--line)}
  .table tbody tr:nth-child(even){background:var(--zebra)}
  .table tfoot td{background:#fafafa;border-top:2px solid var(--line)}
  .card.shadowed{box-shadow:0 12px 28px -14px rgba(2,32,71,.12)}
  .empty-state{border:1px dashed #d6dbe4;border-radius:14px;padding:28px;text-align:center;background:#fff}
  .table .text-trunc{max-width:360px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
  .toolbar .btn{padding-inline:14px}
  .toolbar .btn-group .btn + .btn{margin-inline-start:0}
  @media (max-width: 576px){
    .btn-eq{min-width:100%}
    .toolbar{gap:8px}
    .btn + .btn{margin-inline-start:6px}
  }
  @media print{
    /* كل شيء مخفي افتراضيًا أثناء الطباعة */
    body * { visibility: hidden !important; }
    /* نظهر الجدول فقط */
    #printOnlyTable, #printOnlyTable * { visibility: visible !important; }
    #printOnlyTable { position: absolute; inset: 0; margin: 0; }
    /* تحسين مظهر الجدول في الطباعة */
    table{ width:100%; border-collapse: collapse; font-size:12px }
    th, td{ border:1px solid #ccc; padding:6px; }
    .non-printable{display:none!important}
  }
</style>

<div class="content container-fluid page-wrap">

  {{-- 🔷 المسار الملاحي --}}
  <div class="mb-3 non-printable">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active text-primary" aria-current="page">
          {{ \App\CPU\translate('مراكز التكلفة') }}
        </li>
      </ol>
    </nav>
  </div>

  <!-- ====== Filter Card ====== -->
  <div class="card filter-card mb-3 non-printable shadowed">
   

    <div class="collapse show" id="filtersCollapse">
      <div class="card-body">
        <form method="get" id="filtersForm">
          <div class="row g-3">
            <!-- صف التواريخ -->
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label">{{ __('من تاريخ') }}</label>
              <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label">{{ __('إلى تاريخ') }}</label>
              <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <!-- مرجع + جزء من الوصف -->
   
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label">{{ __('جزء من الوصف') }}</label>
              <input type="text" name="desc_like" class="form-control" value="{{ request('desc_like') }}" placeholder="{{ __('جزء من الوصف') }}">
            </div>

            <!-- الحساب + الكاتب -->
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label">{{ __('الحساب') }}</label>
              <select name="account_id" class="form-select select2" data-placeholder="{{ __('كل الحسابات') }}">
                <option value="">{{ __('كل الحسابات') }}</option>
                @foreach(($filters['accounts'] ?? []) as $acc)
                  <option value="{{ $acc->id }}" {{ (string)$acc->id === request('account_id') ? 'selected' : '' }}>
                    {{ $acc->code ?? '' }} — {{ $acc->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label">{{ __('كاتب القيد') }}</label>
              <select name="writer_id" class="form-select select2" data-placeholder="{{ __('الكل') }}">
                <option value="">{{ __('الكل') }}</option>
                @foreach(($filters['writers'] ?? []) as $w)
                  <option value="{{ $w->id }}" {{ (string)$w->id === request('writer_id') ? 'selected' : '' }}>
                    {{ $w->name ?? ('#'.$w->id) }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- الفرع + يشمل الأبناء -->
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label">{{ __('الفرع') }}</label>
              <select name="branch_id" class="form-select select2" data-placeholder="{{ __('كل الفروع') }}">
                <option value="">{{ __('كل الفروع') }}</option>
                @foreach(($filters['branches'] ?? []) as $br)
                  <option value="{{ $br->id }}" {{ (string)$br->id === request('branch_id') ? 'selected' : '' }}>
                    {{ $br->name }}
                  </option>
                @endforeach
              </select>
            </div>
    

            <!-- مركز التكلفة + يشمل الأبناء + تضمين السطور بدون مركز -->
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label">{{ __('مركز التكلفة') }}</label>
              <select name="cost_center_id" class="form-select select2" data-placeholder="{{ __('كل المراكز') }}" required>
                <option value="">{{ __('كل المراكز') }}</option>
                @foreach(($filters['cost_centers'] ?? []) as $cc)
                  <option value="{{ $cc->id }}" {{ (string)$cc->id === request('cost_center_id') ? 'selected' : '' }}>
                    {{ $cc->code ?? '' }} — {{ $cc->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3 d-flex align-items-end">
              <div class="form-check me-4">
                <input class="form-check-input" type="checkbox" name="with_cc_children" value="1" id="withCcChildren" {{ request('with_cc_children')?'checked':'' }}>
                <label class="form-check-label" for="withCcChildren">{{ __('يشمل مراكز التكلفة الفرعية') }}</label>
              </div>
         
            </div>
          </div>

          <!-- أزرار أسفل الفلاتر -->
          <div class="toolbar justify-content-start mt-4">
            <button class="btn btn-primary btn-eq" type="submit">
               {{ __('بحث') }}
            </button>
            <a href="{{ request()->url() }}" class="btn btn-danger btn-eq">
         {{ __('إلغاء') }}
            </a>

            <!-- ✅ طباعة الجدول فقط -->
            <button type="button" class="btn btn-secondary btn-eq" onclick="printTable('reportTable')">
        {{ __('طباعة الجدول') }}
            </button>

            <!-- ✅ تصدير مباشر إلى Excel بدون Dropdown -->
            <button type="button" class="btn btn-info btn-eq"
                    onclick="exportTableToExcel('reportTable','cost-center-transactions')">
 {{ __('تصدير Excel') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ====== Results ====== -->
  @if($hasSearch)
    <div class="card shadowed">
      <div class="card-body table-responsive">
     

        <!-- ✅ عنصر منفصل لطباعة الجدول فقط -->
        <div id="printOnlyTable">
          <table class="table " id="reportTable" data-export-filename="cost-center-transactions">
            <thead>
              <tr>
                <th class="sticky">#</th>
                <th class="sticky">{{ __('التاريخ') }}</th>
                <th class="sticky">{{ __('المرجع') }}</th>
                <th class="sticky">{{ __('الفرع') }}</th>
                <th class="sticky">{{ __('الحساب') }}</th>
                <th class="sticky">{{ __('مركز التكلفة') }}</th>
                <th class="sticky text-end">{{ __('مدين') }}</th>
                <th class="sticky text-end">{{ __('دائن') }}</th>
                <th class="sticky text-end">{{ __('صافي') }}</th>
                <th class="sticky">{{ __('الوصف') }}</th>
                <th class="sticky non-printable">{{ __('إجراء') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rows as $r)
                <tr>
                  <td>{{ $rows->firstItem() + $loop->index }}</td>
                  <td>{{ $r->entry_date }}</td>
                  <td>{{ $r->reference }}</td>
                  <td>{{ $r->branch_name }}</td>
                  <td>{{ $r->account_name }}</td>
                  <td>{{ $r->cost_center_name }}</td>
                  <td class="text-end">{{ number_format($r->debit,2) }}</td>
                  <td class="text-end">{{ number_format($r->credit,2) }}</td>
                  <td class="text-end">{{ number_format($r->net_amount,2) }}</td>
                  <td class="text-trunc" title="{{ $r->description }}">{{ $r->description }}</td>
                  <td class="non-printable">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.journal-entries.show', $r->entry_id) }}" target="_blank">
                      {{ __('عرض القيد') }}
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="fw-bold">
                <td colspan="6" class="text-end">{{ __('الإجمالي:') }}</td>
                <td class="text-end">{{ number_format($totals['total_debit'],2) }}</td>
                <td class="text-end">{{ number_format($totals['total_credit'],2) }}</td>
                <td class="text-end">{{ number_format($totals['net_amount'],2) }}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="mt-3">
          {{ $rows->withQueryString()->links() }}
        </div>
      </div>
    </div>
  @else
    <div class="empty-state">
      <h6 class="mb-2">{{ __('لا توجد بيانات للعرض') }}</h6>
      <div class="text-muted">{{ __('ابدأ بتحديد أي فلتر في الأعلى ثم اضغط "بحث".') }}</div>
    </div>
  @endif

</div>
@endsection

<!-- ====== Scripts ====== -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
  // Init Select2
  document.addEventListener('DOMContentLoaded', function(){
    $('.select2').select2({
      width:'100%',
      allowClear:true,
      placeholder: function(){return $(this).data('placeholder') || '';}
    });
  });

  // ✅ طباعة الجدول فقط (بدون الصفحة)
  function printTable(tableId){
    // نعتمد على @media print لإظهار #printOnlyTable فقط
    window.print();
  }

  // ✅ تصدير إلى Excel بدون Dropdown (يحذف الأعمدة غير القابلة للطباعة)
  function exportTableToExcel(tableId, filename){
    const table = document.getElementById(tableId);
    if(!table) return;

    const clone = table.cloneNode(true);
    // إزالة الأعمدة غير القابلة للطباعة
    clone.querySelectorAll('.non-printable').forEach(el => el.remove());

    const html = `
      <html xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns="http://www.w3.org/TR/REC-html40">
        <head>
          <meta charset="UTF-8">
          <style>
            table{ border-collapse:collapse; direction:rtl; }
            th,td{ border:1px solid #ccc; padding:6px; font-family:Tahoma,Arial; font-size:12px; }
            th{ background:#f6f8ff; }
            tfoot td{ font-weight:bold; background:#fafafa; }
            .text-end{text-align:right}
          </style>
        </head>
        <body>
          ${clone.outerHTML}
        </body>
      </html>`;

    const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = (filename || 'export') + '.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
</script>
