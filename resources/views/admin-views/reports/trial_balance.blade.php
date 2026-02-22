{{-- resources/views/admin-views/reports/trial_balance.blade.php --}}
@extends('layouts.admin.app')

@section('title', __('ميزان المراجعة'))

@section('content')
@php
    $hasFilter   = request()->filled('from_date') || request()->filled('to_date') || request()->filled('view_mode');
    $hasRows     = isset($rows) && count($rows ?? []) > 0;

    $showCode    = (bool)($filters['show_code'] ?? false);      // افتراضي false
    $withOpening = (bool)($filters['with_opening'] ?? true);
    $viewMode    = $filters['view_mode'] ?? 'summary';          // summary | details

    // ===== ترجمة أنواع الحسابات للعربي =====
    $typeMap = [
        'asset' => 'الأصول',
        'assets' => 'الأصول',
        'current_asset' => 'الأصول المتداولة',
        'non_current_asset' => 'الأصول غير المتداولة',
        'fixed_asset' => 'الأصول الثابتة',
        'contra_asset' => 'أصول مقابلة',

        'liability' => 'الخصوم',
        'liabilities' => 'الخصوم',
        'current_liability' => 'الالتزامات المتداولة',
        'non_current_liability' => 'الالتزامات غير المتداولة',
        'contra_liability' => 'التزامات مقابلة',

        'equity' => 'حقوق الملكية',
        'capital' => 'رأس المال',
        'retained_earnings' => 'الأرباح المبقاة',
        'contra_equity' => 'حقوق ملكية مقابلة',

        'revenue' => 'الإيرادات',
        'income' => 'الإيرادات',
        'sales' => 'المبيعات',
        'other_income' => 'إيرادات أخرى',

        'expense' => 'المصروفات',
        'expenses' => 'المصروفات',
        'operating_expense' => 'مصروفات تشغيلية',
        'other_expense' => 'مصروفات أخرى',

        'cogs' => 'تكلفة البضاعة المباعة',
        'cost_of_goods_sold' => 'تكلفة البضاعة المباعة',

        // شائعة في بعض الدليل المحاسبي
        'bank' => 'البنك',
        'cash' => 'الصندوق',
        'inventory' => 'المخزون',
        'accounts_receivable' => 'المدينون',
        'receivable' => 'المدينون',
        'accounts_payable' => 'الدائنون',
        'payable' => 'الدائنون',
        'depreciation' => 'مصروف إهلاك',
        'accumulated_depreciation' => 'مجمع الإهلاك',
        'prepaid_expense' => 'مصروفات مقدمة',
        'accrued_expense' => 'مصروفات مستحقة',
        'interest_income' => 'إيرادات فوائد',
        'interest_expense' => 'مصروف فوائد',
    ];

    $translateType = function($type) use ($typeMap) {
        $key = strtolower(trim((string)$type));
        $key = str_replace([' ', '-'], '_', $key);
        return $typeMap[$key] ?? ($type ? __($type) : '—');
    };
@endphp

<style>
  :root{
    --bg:#ffffff; --ink:#0f172a; --muted:#64748b;
    --grid:#e9eef5; --head:#f6f8ff; --zebra:#fbfcff;
    --brand: var(--bs-primary, #0d6efd);
    --ok:#16a34a; --warn:#f59e0b; --bad:#ef4444;
    --rd:12px; --shadow:0 12px 24px -18px rgba(2,32,71,.12);
    --ctl-h:36px; --fs:13px;
  }
  .page-wrap{direction:rtl}
  .container{max-width:98%}

  .toolbar{
    position:sticky; top:64px; z-index:10;
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
    padding:10px; margin-bottom:10px; border-radius:var(--rd);
    background:#fff; border:1px solid var(--grid); box-shadow:var(--shadow);
  }
  .toolbar h5{margin:0; font-weight:800; color:var(--ink); display:flex; align-items:center; gap:6px}
  .toolbar h5::before{content:"📊"; font-size:18px}
  .pill{background:#f1f5f9; border:1px solid var(--grid); padding:4px 8px; border-radius:999px; font-size:12px; color:#334155}
  .toolbar .right{margin-inline-start:auto; display:flex; gap:8px; flex-wrap:wrap}

  .filter-card{border:1px solid var(--grid); border-radius:var(--rd); background:var(--bg); box-shadow:var(--shadow)}
  .filter-card .card-header{background:var(--head); border-bottom:1px solid var(--grid); padding:8px 10px; font-weight:700; color:#0f172a; font-size:14px}

  .ctl,.form-control,.form-select{height:var(--ctl-h)!important; font-size:var(--fs)}
  .input-group-text{height:var(--ctl-h); display:flex; align-items:center; background:#f8fafc; border-color:var(--grid); padding:0 8px}

  .segmented{border:1px solid var(--grid); border-radius:999px; padding:2px; gap:2px; background:#fff; height:var(--ctl-h); display:inline-flex; align-items:center}
  .segmented .seg-input{display:none}
  .segmented .seg-btn{user-select:none; cursor:pointer; border:0; border-radius:999px; font-weight:700; color:#334155; background:transparent; height:calc(var(--ctl-h) - 6px); padding:0 12px; display:flex; align-items:center; justify-content:center; transition:.15s ease; font-size:13px}
  .segmented .seg-input:checked + .seg-btn{background:var(--brand); color:#fff}

  .switch-sm .form-check-input{width:2.2rem; height:1.1rem}
  .switch-sm .form-check-label{font-size:13px}
  .switch-sm{display:flex; align-items:center; gap:6px}
  .form-check-input:checked{background-color:var(--brand); border-color:var(--brand)}

  .btn-eq{height:var(--ctl-h); font-size:var(--fs); min-width:130px; padding-inline:14px}

  .table-wrap{border:1px solid var(--grid); border-radius:10px; overflow:auto; background:var(--bg); box-shadow:var(--shadow)}
  table.table{margin:0; min-width:980px}
  thead th{position:sticky; top:0; z-index:3; background:var(--head); text-align:center; font-weight:800; color:#1f2937}
  .table-striped>tbody>tr:nth-of-type(odd)>*{background:var(--zebra)}
  tbody tr:hover>*{background:#f8fafc}
  tfoot th{background:#fafafa; font-weight:800}

  .num{text-align:end}
  .nowrap{white-space:nowrap}
  .w-code{width:120px}
  .w-name{min-width:280px}

  th.sticky, td.sticky{position:sticky; left:0; z-index:2; background:inherit}
  th.sticky-2, td.sticky-2{position:sticky; left:120px; z-index:2; background:inherit}
  thead th.sticky, thead th.sticky-2{z-index:4}

  /* إخفاء الكود فقط في وضع التفاصيل */
  .table-wrap.hide-code td:nth-child(1),
  .table-wrap.hide-code th:nth-child(1){display:none}
  .table-wrap.hide-code th.sticky-2, .table-wrap.hide-code td.sticky-2{left:0}

  @media (max-width: 992px){ .toolbar .right{width:100%; justify-content:flex-start} }

  @media print{
    .toolbar, .filter-card, nav[aria-label="breadcrumb"]{display:none!important}
    .table-wrap{box-shadow:none; border:none}
    @page{ size:A4 landscape; margin:10mm 8mm }
  }
</style>

<div class="container page-wrap">
  <div class="toolbar">
    <h5>{{ __('ميزان المراجعة') }}</h5>
    <div class="mini-sum d-flex flex-wrap" style="gap:6px">
      <span class="pill">{{ __('من') }}: <strong>{{ $filters['from_date'] ?? '—' }}</strong></span>
      <span class="pill">{{ __('إلى') }}: <strong>{{ $filters['to_date'] ?? '—' }}</strong></span>
      <span class="pill">{{ __('الوضع') }}: <strong>{{ $viewMode === 'summary' ? __('ملخص (حسب النوع)') : __('تفاصيل') }}</strong></span>
      @if($viewMode === 'details')
        <span class="pill">{{ __('المستوى') }}: <strong>{{ $filters['level'] ?? 2 }}</strong></span>
        <span class="pill">{{ __('إظهار الكود') }}: <strong>{{ $showCode ? __('نعم') : __('لا') }}</strong></span>
      @endif
      <span class="pill">{{ __('رصيد افتتاحي') }}: <strong>{{ $withOpening ? __('مُحتسب') : __('غير مُحتسب') }}</strong></span>
    </div>

    @if($hasFilter)
      <div class="right">
        <button type="button" class="btn btn-primary btn-eq" onclick="printReport()" {{ $hasRows ? '' : 'disabled' }}>{{ __('طباعة') }}</button>
        <button type="button" class="btn btn-outline-primary btn-eq" onclick="exportTableToExcel()" {{ $hasRows ? '' : 'disabled' }}>{{ __('تصدير Excel') }}</button>
      </div>
    @endif
  </div>

  <div class="card filter-card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap:2">
        <strong>🔎 {{ __('بحث وفلاتر') }}</strong>
        <div class="d-none d-md-flex align-items-center gap-2 ms-2">
          <span class="badge bg-light text-dark border">{{ __('من') }}: {{ $filters['from_date'] ?? '—' }}</span>
          <span class="badge bg-light text-dark border">{{ __('إلى') }}: {{ $filters['to_date'] ?? '—' }}</span>
        </div>
      </div>
    </div>

    <form method="get" action="{{ route('admin.indexTrialBalance') }}">
      <div class="p-3">
        <div class="row g-2 align-items-end">
          <div class="col-12 col-md-4">
            <label class="form-label mb-1">{{ __('نطاق التاريخ') }}</label>
            <div class="input-group">
              <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control ctl">
              <span class="input-group-text">—</span>
              <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control ctl">
            </div>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label mb-1">{{ __('طريقة العرض') }}</label>
            <div class="segmented" role="tablist" aria-label="View mode">
              <input id="vm-summary" type="radio" name="view_mode" value="summary" class="seg-input" {{ ($viewMode ?? 'summary') === 'summary' ? 'checked' : '' }}>
              <label for="vm-summary" class="seg-btn">{{ __('ملخص') }}</label>

              <input id="vm-details" type="radio" name="view_mode" value="details" class="seg-input" {{ ($viewMode ?? 'summary') === 'details' ? 'checked' : '' }}>
              <label for="vm-details" class="seg-btn">{{ __('تفاصيل') }}</label>
            </div>
          </div>

          <div class="col-6 col-md-2">
            <label class="form-label mb-1">{{ __('مستوى الحساب') }}</label>
            <select name="level" class="form-select ctl" {{ $viewMode === 'summary' ? 'disabled' : '' }}>
              <optgroup label="{{ __('المستوى الحالي') }}: {{ $filters['level'] ?? 2 }}">
                @for($i=1; $i<=($maxLevel ?? 6); $i++)
                  <option value="{{ $i }}" {{ ($filters['level'] ?? 2)==$i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </optgroup>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label mb-1 d-block">{{ __('خيارات العرض') }}</label>
            <div class="d-flex flex-wrap" style="gap:12px">
              <div class="form-check form-switch switch-sm">
                <input class="form-check-input" type="checkbox" id="show_code" name="show_code" value="1" {{ $showCode ? 'checked' : '' }} {{ $viewMode === 'summary' ? 'disabled' : '' }}>
                <label class="form-check-label" for="show_code">{{ __('إظهار الكود') }}</label>
              </div>
              <div class="form-check form-switch switch-sm">
                <input class="form-check-input" type="checkbox" id="with_opening" name="with_opening" value="1" {{ $withOpening ? 'checked' : '' }}>
                <label class="form-check-label" for="with_opening">{{ __('رصيد افتتاحي') }}</label>
              </div>
              <div class="form-check form-switch switch-sm">
                <input class="form-check-input" type="checkbox" id="show_zero" name="show_zero" value="1" {{ ($filters['show_zero'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="show_zero">{{ __('عرض الصفري') }}</label>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6 text-start text-md-end">
            <div class="d-flex gap-2 justify-content-start justify-content-md-end">
              <button class="btn btn-primary btn-eq">{{ __('عرض النتائج') }}</button>
              <a href="{{ route('admin.indexTrialBalance') }}" class="btn btn-light border btn-eq">{{ __('إعادة الضبط') }}</a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  @if(!$hasFilter)
    <div class="text-center p-4" style="border:1px dashed var(--grid); border-radius:10px; background:#fcfcff">
      <div style="font-size:38px; line-height:1">🧭</div>
      <h6 class="fw-bold mt-2 mb-1">{{ __('ابدأ بتطبيق الفلاتر') }}</h6>
      <p class="text-muted mb-0">{{ __('لن يتم عرض الجدول إلا بعد اختيار نطاق التاريخ أو أي فلتر آخر.') }}</p>
    </div>
  @else
    {{-- في وضع التفاصيل فقط نطبّق hide-code --}}
    <div class="table-wrap {{ $viewMode === 'summary' ? '' : ($showCode ? '' : 'hide-code') }}">
      <table id="trialTable" class="table align-middle table-striped mb-0">
        <thead>
          @if($viewMode === 'summary')
            <tr>
              <th rowspan="2" class="w-name sticky">{{ __('النوع') }}</th>
              @if($withOpening)
                <th colspan="2">{{ __('افتتاحي') }}</th>
              @endif
              <th colspan="2">{{ __('خلال الفترة') }}</th>
              <th colspan="2">{{ __('ختامي') }}</th>
            </tr>
            <tr>
              @if($withOpening)
                <th class="num">{{ __('مدين') }}</th>
                <th class="num">{{ __('دائن') }}</th>
              @endif
              <th class="num">{{ __('مدين') }}</th>
              <th class="num">{{ __('دائن') }}</th>
              <th class="num">{{ __('مدين') }}</th>
              <th class="num">{{ __('دائن') }}</th>
            </tr>
          @else
            <tr>
              <th rowspan="2" class="w-code sticky">{{ __('الكود') }}</th>
              <th rowspan="2" class="w-name sticky-2">{{ __('الحساب') }}</th>
              @if($withOpening)
                <th colspan="2">{{ __('افتتاحي') }}</th>
              @endif
              <th colspan="2">{{ __('خلال الفترة') }}</th>
              <th colspan="2">{{ __('ختامي') }}</th>
            </tr>
            <tr>
              @if($withOpening)
                <th class="num">{{ __('مدين') }}</th>
                <th class="num">{{ __('دائن') }}</th>
              @endif
              <th class="num">{{ __('مدين') }}</th>
              <th class="num">{{ __('دائن') }}</th>
              <th class="num">{{ __('مدين') }}</th>
              <th class="num">{{ __('دائن') }}</th>
            </tr>
          @endif
        </thead>

        <tbody id="tbBody">
          @forelse($rows as $r)
            @if($viewMode === 'summary')
              @php $typeLabel = $translateType($r->account_type ?? $r->account_name ?? '—'); @endphp
              <tr class="lvl-1">
                <td class="sticky name">{{ $typeLabel }}</td>
                @if($withOpening)
                  <td class="num">{{ number_format($r->opening_debit, 2) }}</td>
                  <td class="num">{{ number_format($r->opening_credit, 2) }}</td>
                @endif
                <td class="num">{{ number_format($r->period_debit, 2) }}</td>
                <td class="num">{{ number_format($r->period_credit, 2) }}</td>
                <td class="num">{{ number_format($r->ending_debit, 2) }}</td>
                <td class="num">{{ number_format($r->ending_credit, 2) }}</td>
              </tr>
            @else
              <tr class="lvl-{{ $r->level }}">
                <td class="nowrap sticky">{{ $r->code }}</td>
                <td class="sticky-2 name">{{ $r->account_name }}</td>
                @if($withOpening)
                  <td class="num">{{ number_format($r->opening_debit, 2) }}</td>
                  <td class="num">{{ number_format($r->opening_credit, 2) }}</td>
                @endif
                <td class="num">{{ number_format($r->period_debit, 2) }}</td>
                <td class="num">{{ number_format($r->period_credit, 2) }}</td>
                <td class="num">{{ number_format($r->ending_debit, 2) }}</td>
                <td class="num">{{ number_format($r->ending_credit, 2) }}</td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="{{ $viewMode === 'summary' ? ($withOpening ? 7 : 5) : ($withOpening ? 8 : 6) }}" class="text-center text-muted py-4">
                {{ __('لا توجد بيانات ضمن الفلاتر الحالية') }}
              </td>
            </tr>
          @endforelse
        </tbody>

        <tfoot>
          @if($viewMode === 'summary')
            <tr>
              <th class="sticky">{{ __('الإجمالي') }}</th>
              @if($withOpening)
                <th class="num">{{ number_format($totals['opening_debit'], 2) }}</th>
                <th class="num">{{ number_format($totals['opening_credit'], 2) }}</th>
              @endif
              <th class="num">{{ number_format($totals['period_debit'], 2) }}</th>
              <th class="num">{{ number_format($totals['period_credit'], 2) }}</th>
              <th class="num">{{ number_format($totals['ending_debit'], 2) }}</th>
              <th class="num">{{ number_format($totals['ending_credit'], 2) }}</th>
            </tr>
            <tr>
              <th class="sticky">{{ __('فرق التوازن (يُفترض 0)') }}</th>
              @if($withOpening)
                <th class="num text-{{ ($diff['opening'] ?? 0) == 0 ? 'success' : 'warning' }}">{{ number_format($diff['opening'], 2) }}</th>
                <th></th>
              @endif
              <th class="num text-{{ ($diff['period'] ?? 0) == 0 ? 'success' : 'warning' }}">{{ number_format($diff['period'], 2) }}</th>
              <th></th>
              <th class="num text-{{ ($diff['ending'] ?? 0) == 0 ? 'success' : 'warning' }}">{{ number_format($diff['ending'], 2) }}</th>
              <th></th>
            </tr>
          @else
            <tr>
              <th colspan="2" class="sticky">{{ __('الإجمالي') }}</th>
              @if($withOpening)
                <th class="num">{{ number_format($totals['opening_debit'], 2) }}</th>
                <th class="num">{{ number_format($totals['opening_credit'], 2) }}</th>
              @endif
              <th class="num">{{ number_format($totals['period_debit'], 2) }}</th>
              <th class="num">{{ number_format($totals['period_credit'], 2) }}</th>
              <th class="num">{{ number_format($totals['ending_debit'], 2) }}</th>
              <th class="num">{{ number_format($totals['ending_credit'], 2) }}</th>
            </tr>
            <tr>
              <th colspan="2" class="sticky">{{ __('فرق التوازن (يُفترض 0)') }}</th>
              @if($withOpening)
                <th class="num text-{{ ($diff['opening'] ?? 0) == 0 ? 'success' : 'warning' }}">{{ number_format($diff['opening'], 2) }}</th>
                <th></th>
              @endif
              <th class="num text-{{ ($diff['period'] ?? 0) == 0 ? 'success' : 'warning' }}">{{ number_format($diff['period'], 2) }}</th>
              <th></th>
              <th class="num text-{{ ($diff['ending'] ?? 0) == 0 ? 'success' : 'warning' }}">{{ number_format($diff['ending'], 2) }}</th>
              <th></th>
            </tr>
          @endif
        </tfoot>
      </table>
    </div>
  @endif
</div>
@endsection

{{-- ===== Scripts ===== --}}
<script>
  const COMPANY    = @json(config('app.name'));
  const FROM       = @json($filters['from_date'] ?? '');
  const TO         = @json($filters['to_date'] ?? '');
  const SHOW_CODE  = @json($showCode);
  const IS_SUMMARY = @json(($viewMode ?? 'summary') === 'summary');
</script>

@verbatim
<script>
  function printReport(){
    const table = document.getElementById('trialTable');
    if(!table) return;

    const cloned = table.cloneNode(true);
    cloned.querySelectorAll('.sticky,.sticky-2').forEach(el => { el.style.position='static'; el.style.left='auto'; });

    const fmt = new Date().toLocaleString('ar-EG');

    // لا تُخفِ العمود الأول في summary (لأنه "النوع")
    const hideCodeCss = (!SHOW_CODE && !IS_SUMMARY) ? `
      table tr > *:nth-child(1){display:none !important}
    ` : '';

    const html = `<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>ميزان المراجعة</title>
<style>
@page{ size:A4 landscape; margin:10mm 8mm }
body{ font-family:"Tahoma","Arial",sans-serif; color:#0f172a }
.head{display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px}
.brand{font-weight:800; font-size:18px}
.meta{font-size:12px; color:#555; line-height:1.5}
table{width:100%; border-collapse:collapse; table-layout:fixed; font-size:12px}
thead{display:table-header-group} tfoot{display:table-footer-group}
th,td{border:1px solid #e5e7eb; padding:5px 7px} th{background:#f6f8ff; text-align:center}
thead tr:nth-child(2) th{background:#eef2ff} td.num{text-align:end}
.footer{margin-top:6px; font-size:11px; color:#666; display:flex; justify-content:space-between}
${hideCodeCss}
</style></head><body>
<div class="head">
  <div class="brand">${COMPANY||'Accounting System'}</div>
  <div class="meta">
    <div>ميزان المراجعة</div>
    <div>الفترة: ${FROM||'—'} → ${TO||'—'}</div>
    <div>تاريخ الطباعة: ${fmt}</div>
  </div>
</div>
${cloned.outerHTML}
<div class="footer"><span>مطبوع تلقائياً من النظام</span><span>${COMPANY||''}</span></div>
</body></html>`;

    const w = window.open('', '_blank');
    w.document.open(); w.document.write(html); w.document.close();
    w.onload = ()=> w.print();
  }

  function exportTableToExcel(){
    const table = document.getElementById('trialTable');
    if(!table) return;

    const head = table.tHead ? table.tHead.outerHTML : '';
    const body = table.tBodies[0] ? '<tbody>'+table.tBodies[0].innerHTML+'</tbody>' : '';
    const foot = table.tFoot ? table.tFoot.outerHTML : '';

    const hideCodeCss = (!SHOW_CODE && !IS_SUMMARY) ? `
      table tr > *:nth-child(1){display:none !important}
    ` : '';

    const workbookHtml = `
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40" dir="rtl" lang="ar">
<head><meta charset="UTF-8">
<!--[if gte mso 9]><xml>
 <x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
  <x:Name>Trial Balance</x:Name>
  <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
 </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
</xml><![endif]-->
<style>
table{border-collapse:collapse}
th,td{border:1px solid #cccccc; padding:4px 6px; mso-number-format:"General"}
th{text-align:center; background:#eef2ff; font-weight:bold}
td.num{text-align:right; mso-number-format:"0.00"}
${hideCodeCss}
</style></head><body>
<table border="1">${head}${body}${foot}</table>
</body></html>`;

    const blob = new Blob([workbookHtml], { type:'application/vnd.ms-excel;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url;
    a.download = `trial_balance_${(new Date()).toISOString().slice(0,10)}.xls`;
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }
</script>
@endverbatim
