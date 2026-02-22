{{-- resources/views/admin-views/costcenter/reports/totals.blade.php --}}
@extends('layouts.admin.app')

@section('title', __('إجماليات مراكز التكلفة'))

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

    $group = (string) request('group_by', 'none'); // account / cost_center / branch / none
    $groupLabels = [
        'none'        => __('الإجمالي العام'),
        'account'     => __('الحساب'),
        'cost_center' => __('مركز التكلفة'),
        'branch'      => __('الفرع'),
    ];
    $groupLabel = $groupLabels[$group] ?? __('الإجمالي العام');

    $from = request('from_date'); $to = request('to_date');

    // اسم ملف التصدير
    $exportBase = 'cost-center-totals';
    $exportName = $exportBase
        . ($group !== 'none' ? "-{$group}" : '-overall')
        . ($from ? "-from-{$from}" : '')
        . ($to ? "-to-{$to}" : '');
@endphp

<style>
  :root{
    --line:#e9eef5; --soft:#fff; --bg:#f6f8ff; --ink:#0f172a;
    --muted:#667085; --chip:#f8fafc; --zebra:#fbfdff; --radius:14px;
    --green:#16a34a; --red:#dc2626; --amber:#b45309;
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
  .chip{display:inline-flex;align-items:center;gap:6px;background:var(--chip);border:1px solid #e5e7eb;border-radius:999px;padding:.28rem .65rem;font-size:.85rem;margin-inline-end:6px}
  thead th.sticky{position:sticky;top:0;background:var(--bg);z-index:2}
  .table thead th{white-space:nowrap;border-bottom:1px solid var(--line)}
  .table tbody tr:nth-child(even){background:var(--zebra)}
  .table tfoot td{background:#fafafa;border-top:2px solid var(--line)}
  .card.shadowed{box-shadow:0 12px 28px -14px rgba(2,32,71,.12)}
  .empty-state{border:1px dashed #d6dbe4;border-radius:14px;padding:28px;text-align:center;background:#fff}
  .toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
  .toolbar .btn{padding-inline:14px}
  .kpi{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
  .kpi .item{border:1px solid var(--line);border-radius:12px;background:#fff;padding:12px}
  .kpi .label{color:var(--muted);font-size:.85rem}
  .kpi .value{font-weight:700;font-size:1.1rem}
  .value.pos{color:var(--green)} .value.neg{color:var(--red)} .value.warn{color:var(--amber)}

  @media (max-width: 992px){ .kpi{grid-template-columns:repeat(2,minmax(0,1fr))} }
  @media (max-width: 576px){
    .btn-eq{min-width:100%}
    .toolbar{gap:8px}
    .btn + .btn{margin-inline-start:6px}
    .kpi{grid-template-columns:repeat(1,minmax(0,1fr))}
  }

  /* طباعة الجدول فقط */
  @media print{
    body * { visibility: hidden !important; }
    #printOnlyTable, #printOnlyTable * { visibility: visible !important; }
    #printOnlyTable { position: absolute; inset: 0; margin: 0; }
    table{ width:100%; border-collapse: collapse; font-size:12px }
    th, td{ border:1px solid #ccc; padding:6px; }
    th{ background:#f6f8ff; }
    .text-end{text-align:right}
    .non-printable{display:none!important}
  }
</style>

<div class="content container-fluid page-wrap">

  {{-- المسار الملاحي --}}
  <div class="mb-3 non-printable">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active text-primary" aria-current="page">
          {{ \App\CPU\translate('إجماليات') }}
        </li>
      </ol>
    </nav>
  </div>

  <!-- Filter Card -->
  <div class="card filter-card mb-3 non-printable shadowed">
    <div class="card-body">
      <form method="get" id="filtersForm">
        <div class="row g-3">

          <!-- ✅ التجميع أولا -->
          <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">{{ __('تجميع حسب') }}</label>
            <select name="group_by" class="form-select">
              <option value="none"        {{ $group==='none'?'selected':'' }}>{{ __('الإجمالي العام') }}</option>
              <option value="cost_center" {{ $group==='cost_center'?'selected':'' }}>{{ __('مركز التكلفة') }}</option>
              <option value="account"     {{ $group==='account'?'selected':'' }}>{{ __('الحساب') }}</option>
              <option value="branch"      {{ $group==='branch'?'selected':'' }}>{{ __('الفرع') }}</option>
            </select>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">{{ __('من تاريخ') }}</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">{{ __('إلى تاريخ') }}</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>

          <!-- فلاتر اختيارية -->
          <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">{{ __('مركز التكلفة') }}</label>
            <select name="cost_center_id" class="form-select select2" data-placeholder="{{ __('كل المراكز') }}">
              <option value="">{{ __('كل المراكز') }}</option>
              @foreach(($filters['cost_centers'] ?? []) as $cc)
                <option value="{{ $cc->id }}" {{ (string)$cc->id === request('cost_center_id') ? 'selected' : '' }}>
                  {{ $cc->code ?? '' }} — {{ $cc->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">{{ __('الحساب') }}</label>
            <select name="account_id" class="form-select select2" data-placeholder="{{ __('كل الحسابات') }}">
              <option value="">{{ __('كل الحسابات') }}</option>
              @foreach(($filters['accounts'] ?? []) as $acc)
                <option value="{{ $acc->id }}" {{ (string)$acc->id === request('account_id') ? 'selected' : '' }}>
                  {{ $acc->code ?? '' }} — {{ $acc->account }}
                </option>
              @endforeach
            </select>
          </div>
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

          <!-- خيارات -->
          <div class="col-12 col-md-6 col-xl-3 d-flex align-items-end">
            <div class="form-check me-4">
              <input class="form-check-input" type="checkbox" name="with_cc_children" value="1" id="withCcChildren" {{ request('with_cc_children')?'checked':'' }}>
              <label class="form-check-label" for="withCcChildren">{{ __('يشمل مراكز التكلفة الفرعية') }}</label>
            </div>
          </div>
        </div>

        <!-- أزرار -->
        <div class="toolbar justify-content-end mt-4">
          <button class="btn btn-primary btn-eq" type="submit">{{ __('بحث') }}</button>
          <a href="{{ request()->url() }}" class="btn btn-danger btn-eq">{{ __('إلغاء') }}</a>
          <button type="button" class="btn btn-secondary btn-eq" onclick="printTable('totalsTable')">{{ __('طباعة الجدول') }}</button>
          <button type="button" class="btn btn-info btn-eq"
                  onclick="exportTableToExcel('totalsTable','{{ $exportName }}')">{{ __('تصدير Excel') }}</button>
        </div>
      </form>

      {{-- شِبّات الفلاتر المفعّلة --}}
      @if($hasSearch)
        <div class="mt-3 d-flex flex-wrap">
          <span class="chip">{{ __('تجميع:') }} {{ $groupLabel }}</span>
          @if($from)<span class="chip">{{ __('من:') }} {{ $from }}</span>@endif
          @if($to)<span class="chip">{{ __('إلى:') }} {{ $to }}</span>@endif
          @if(request('cost_center_id'))
            @php
              $sel = collect($filters['cost_centers'] ?? [])->firstWhere('id', (int)request('cost_center_id'));
            @endphp
            <span class="chip">{{ __('مركز:') }} {{ $sel->name ?? '#'.request('cost_center_id') }}</span>
          @endif
          @if(request('account_id'))
            @php
              $sel = collect($filters['accounts'] ?? [])->firstWhere('id', (int)request('account_id'));
            @endphp
            <span class="chip">{{ __('حساب:') }} {{ $sel->account ?? '#'.request('account_id') }}</span>
          @endif
          @if(request('branch_id'))
            @php
              $sel = collect($filters['branches'] ?? [])->firstWhere('id', (int)request('branch_id'));
            @endphp
            <span class="chip">{{ __('فرع:') }} {{ $sel->name ?? '#'.request('branch_id') }}</span>
          @endif
          @if(request('with_cc_children'))<span class="chip">{{ __('يشمل الفرعية') }}</span>@endif
        </div>
      @endif

    </div>
  </div>

  <!-- Results -->
  @if($hasSearch && $rows->count())
    {{-- KPI Summary --}}


    <div class="card shadowed">
      <div class="card-body table-responsive">

        <!-- منطقة طباعة الجدول فقط -->
        <div id="printOnlyTable">
          <table class="table table-sm align-middle" id="totalsTable" data-export-filename="{{ $exportName }}">
            <thead>
              <tr>
                <th class="sticky">#</th>
                <th class="sticky">{{ $group !== 'none' ? $groupLabel : __('الوصف') }}</th>
                <th class="sticky text-end">{{ __('إجمالي مدين') }}</th>
                <th class="sticky text-end">{{ __('إجمالي دائن') }}</th>
                <th class="sticky text-end">{{ __('الصافي') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rows as $r)
                @php
                  $rowNet = (float) ($r->net_amount ?? 0);
                @endphp
                <tr>
                  <td>{{ $rows->firstItem() + $loop->index }}</td>

                  @if($group === 'account')
                    <td>{{ $r->account_name ?? ('#'.$r->account_id) }}</td>
                  @elseif($group === 'cost_center')
                    <td>{{ $r->cost_center_name ?? ('#'.$r->cost_center_id) }}</td>
                  @elseif($group === 'branch')
                    <td>{{ $r->branch_name ?? ('#'.$r->branch_id) }}</td>
                  @else
                    <td>
                      <span class="badge bg-light text-dark" style="border:1px solid #e5e7eb">{{ __('الإجمالي العام') }}</span>
                    </td>
                  @endif

                  <td class="text-end">{{ number_format($r->total_debit,2) }}</td>
                  <td class="text-end">{{ number_format($r->total_credit,2) }}</td>
                  <td class="text-end {{ $rowNet>0?'text-success':($rowNet<0?'text-danger':'text-warning') }}">
                    {{ number_format($rowNet,2) }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="fw-bold">
                <td></td>
                <td class="text-end">{{ __('الإجمالي:') }}</td>
                <td class="text-end">{{ number_format($grand['total_debit'] ?? 0,2) }}</td>
                <td class="text-end">{{ number_format($grand['total_credit'] ?? 0,2) }}</td>
                <td class="text-end">{{ number_format($grand['net_amount'] ?? 0,2) }}</td>
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
      <div class="text-muted">{{ __('حدّد الفترة وأي فلاتر إضافية ثم اضغط "بحث".') }}</div>
    </div>
  @endif

</div>
@endsection

<!-- Scripts -->
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

  // طباعة الجدول فقط
  function printTable(){ window.print(); }

  // تصدير Excel (HTML) — يحافظ على RTL والحدود
  function exportTableToExcel(tableId, filename){
    const table = document.getElementById(tableId);
    if(!table) return;

    // خفّض أي عناصر غير مطلوبة في التصدير لو عندك
    const clone = table.cloneNode(true);

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
        <body>${clone.outerHTML}</body>
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
