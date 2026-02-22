@extends('layouts.admin.app')

@section('title', \App\CPU\translate('supplier_list'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  .toolbar-wrap{display:flex;gap:8px;align-items:center;justify-content:space-between;flex-wrap:wrap}
  .btn-light{background:#fff;border:1px solid #e5e7eb}
  .table tfoot th,.table tfoot td{border-top:2px solid #e5e7eb;font-weight:700;background:#fafafa}
  .table-footer{border-top:1px solid #e5e7eb;background:#fcfcfd}
  @media print{
    body{direction:rtl}
    .no-print{display:none !important}
    .print-container{padding:16px;font-family:Tahoma, Arial}
    .print-title{font-size:20px;font-weight:700;margin-bottom:6px;text-align:center}
    .print-sub{font-size:12px;color:#555;text-align:center;margin-bottom:16px}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #000;padding:6px;text-align:center}
    tfoot th, tfoot td{font-weight:700}
    .signatures{display:flex;justify-content:space-between;margin-top:40px}
    .sig-box{width:45%;text-align:center}
    .sig-line{margin-top:40px;border-top:1px solid #000;padding-top:6px}
  }
</style>
@endpush

@section('content')
<div class="content container-fluid">
  {{-- Breadcrumb --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="#" class="text-primary">{{ \App\CPU\translate('قائمة الموردين') }}</a>
        </li>
      </ol>
    </nav>
  </div>

  @php
    // نجمع كل account_id المعروضة في هذه الصفحة ثم نجيب مجاميع المدين/الدائن بضربة واحدة
    /** @var \Illuminate\Pagination\LengthAwarePaginator $suppliers */
    $accountIds = $suppliers->pluck('account_id')->filter()->unique()->values();
    $txAggMap = collect();
    if ($accountIds->isNotEmpty()) {
        $txAggMap = \App\Models\Transection::whereIn('account_id', $accountIds)
            ->selectRaw('account_id, COALESCE(SUM(debit),0) AS dsum, COALESCE(SUM(credit),0) AS csum')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');
    }
    $currency = \App\CPU\Helpers::currency_symbol();
    $pageTotalDebit = 0.0;
    $pageTotalCredit = 0.0;
    $pageTotalBalance = 0.0;
  @endphp

  <div class="row gx-2 gx-lg-3">
    <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
      <div class="card">
        {{-- Header --}}
        <div class="card-header">
          <div class="toolbar-wrap">
            <div style="flex:1 1 420px;max-width:680px">
              <form action="{{ url()->current() }}" method="GET">
                <div class="input-group input-group-merge input-group-flush">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="tio-search"></i>
                    </div>
                  </div>
                  <input id="datatableSearch_" type="search" name="search" class="form-control"
                         placeholder="{{ \App\CPU\translate('بحث برقم الهاتف او اسم المورد') }}"
                         aria-label="Search" value="{{ $search }}">
                  <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('بحث') }}</button>
                </div>
              </form>
            </div>

            <div class="no-print" style="display:flex;gap:8px">
              <button type="button" id="btnExport" class="btn btn-light">
                <i class="tio-download-to"></i> {{ \App\CPU\translate('تصدير إكسل') }}
              </button>
              <button type="button" id="btnPrint" class="btn btn-primary">
                <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
              </button>
            </div>
          </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive datatable-custom">
          <table id="suppliersTable" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
            <thead class="thead-light">
              <tr>
                <th>{{ \App\CPU\translate('#') }}</th>
                <th>{{ \App\CPU\translate('الاسم') }}</th>
                <th class="hide-div-sl">{{ \App\CPU\translate('الايميل') }}</th>
                <th class="hide-div-sl">{{ \App\CPU\translate('رقم الهاتف') }}</th>
                <th class="hide-div-sl">{{ \App\CPU\translate('رقم الضريبي') }}</th>
                <th>{{ \App\CPU\translate('المنتجات ') }}</th>
                <th>{{ \App\CPU\translate('حالة التعامل ') }}</th>
                <th class="text-center">{{ \App\CPU\translate('مدين') }}</th>
                <th class="text-center">{{ \App\CPU\translate('دائن') }}</th>
                <th class="text-center">{{ \App\CPU\translate('الرصيد') }}</th>
                <th class="no-print">{{ \App\CPU\translate('الاجراءات') }}</th>
              </tr>
            </thead>

            <tbody id="set-rows">
              @foreach($suppliers as $key => $supplier)
                @php
                  $accId = $supplier->account_id;
                  $agg = $accId ? ($txAggMap[$accId] ?? null) : null;
                  $sumDebit  = (float)data_get($agg, 'dsum', 0);
                  $sumCredit = (float)data_get($agg, 'csum', 0);
                  $balance   = $sumCredit - $sumDebit; // موجب = دائن، سالب = مدين

                  $pageTotalDebit   += $sumDebit;
                  $pageTotalCredit  += $sumCredit;
                  $pageTotalBalance += $balance;
                @endphp

                <tr>
                  <td>{{ $suppliers->firstItem() + $key }}</td>

                  <td>
                    <a class="text-primary" href="{{ route('admin.supplier.view',[$supplier['id']]) }}">
                      {{ $supplier->name }}
                    </a>
                  </td>

                  <td class="hide-div-sl">
                    @if($supplier['email'])
                      <a class="text-dark" href="mailto:{{ $supplier['email'] }}">{{ $supplier['email'] }}</a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>

                  <td class="hide-div-sl">
                    @if($supplier->mobile)
                      <a href="tel:{{ $supplier->mobile }}">{{ $supplier->mobile }}</a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>

                  <td class="hide-div-sl">
                    <span>{{ $supplier->tax_number ?: '—' }}</span>
                  </td>

                  <td>
                    <a data-toggle="tooltip" href="{{ route('admin.supplier.products',[$supplier['id']]) }}"
                       title="{{ \App\CPU\translate('product_view') }}">
                      {{ $supplier->products->count() }}
                    </a>
                  </td>

                  <td>
                    <label class="toggle-switch toggle-switch-sm">
                      <input type="checkbox" class="toggle-switch-input"
                             onclick="location.href='{{ route('admin.supplier.status', [$supplier['id'], $supplier->active ? 1 : 0]) }}'"
                             {{ $supplier->active ? 'checked' : '' }}>
                      <span class="toggle-switch-label">
                        <span class="toggle-switch-indicator"></span>
                      </span>
                    </label>
                  </td>

                  <td class="text-center">{{ number_format($sumDebit, 2, '.', ',') }} {{ $currency }}</td>
                  <td class="text-center">{{ number_format($sumCredit, 2, '.', ',') }} {{ $currency }}</td>
                  <td class="text-center">
                    {{ number_format($balance, 2, '.', ',') }} {{ $currency }}
                    <small class="d-block text-muted">({{ $balance >= 0 ? 'دائن' : 'مدين' }})</small>
                  </td>

                  <td class="no-print">
                    <a class="btn btn-white mr-1" href="{{ route('admin.supplier.view',[$supplier['id']]) }}"><span class="tio-visible"></span></a>
                    <a class="btn btn-white mr-1" href="{{ route('admin.supplier.edit',[$supplier['id']]) }}"><span class="tio-edit"></span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>

            <tfoot>
              <tr>
                <th colspan="7" class="text-right">{{ \App\CPU\translate('الإجمالي (لهذه الصفحة)') }}</th>
                <th class="text-center">{{ number_format($pageTotalDebit, 2, '.', ',') }} {{ $currency }}</th>
                <th class="text-center">{{ number_format($pageTotalCredit, 2, '.', ',') }} {{ $currency }}</th>
                <th class="text-center">{{ number_format($pageTotalBalance, 2, '.', ',') }} {{ $currency }}</th>
                <th class="no-print">—</th>
              </tr>
            </tfoot>
          </table>
        </div>

        {{-- Footer أسفل الجدول --}}
        <div class="table-footer px-3 py-2 d-flex justify-content-between align-items-center">
          <div class="small text-muted">
            {{ \App\CPU\translate('عدد النتائج في الصفحة') }}: {{ $suppliers->count() }}
          </div>
          <div>
            {!! $suppliers->links() !!}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<script>
  // كل الكود يتنفذ بعد جاهزية الـ DOM لتجنب null addEventListener
  document.addEventListener('DOMContentLoaded', function(){
    const suppliersTable = document.getElementById('suppliersTable');
    const exportBtn = document.getElementById('btnExport');
    const printBtn  = document.getElementById('btnPrint');

    // لو الصفحة لا تحتوي على العناصر لأي سبب، نخرج بهدوء
    if (!suppliersTable) return;

    // ===== Helper: تحويل جدول إلى CSV بترميز UTF-8 مع BOM ليدعم العربية في Excel
    function tableToCSV(tableEl) {
      const rows = Array.from(tableEl.querySelectorAll('tr'));
      const csv = [];
      for (const row of rows) {
        const cells = Array.from(row.querySelectorAll('th,td')).filter(td => !td.classList.contains('no-print'));
        const line = cells.map(cell => {
          const text = cell.innerText.replace(/\(\s*دائن\s*\)|\(\s*مدين\s*\)/g,'').trim();
          const safe = (text.includes(',') || text.includes('"') || text.includes('\n'))
            ? '"' + text.replace(/"/g, '""') + '"'
            : text;
          return safe;
        }).join(',');
        csv.push(line);
      }
      const csvContent = "\ufeff" + csv.join('\n');
      return new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    }

    if (exportBtn) {
      exportBtn.addEventListener('click', function(){
        const blob = tableToCSV(suppliersTable);
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        const d    = new Date();
        const ts   = d.getFullYear().toString() + ('0'+(d.getMonth()+1)).slice(-2) + ('0'+d.getDate()).slice(-2);
        a.href = url;
        a.download = 'suppliers_report_' + ts + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
      });
    }

    if (printBtn) {
      printBtn.addEventListener('click', function(){
        const clone = suppliersTable.cloneNode(true);

        // إزالة عمود الإجراءات من النسخة المطبوعة
        clone.querySelectorAll('.no-print').forEach(el => el.remove());

        const win = window.open('', '_blank', 'width=900,height=700');
        const dateStr = new Date().toLocaleString('ar-EG');

        win.document.open();
        win.document.write(`
          <html dir="rtl" lang="ar">
            <head>
              <title>{{ \App\CPU\translate('قائمة الموردين') }}</title>
              <style>
                body{direction:rtl;font-family:Tahoma, Arial;padding:16px}
                .print-title{font-size:20px;font-weight:700;margin-bottom:6px;text-align:center}
                .print-sub{font-size:12px;color:#555;text-align:center;margin-bottom:16px}
                table{width:100%;border-collapse:collapse}
                th,td{border:1px solid #000;padding:6px;text-align:center}
                tfoot th, tfoot td{font-weight:700}
                .signatures{display:flex;justify-content:space-between;margin-top:40px}
                .sig-box{width:45%;text-align:center}
                .sig-line{margin-top:40px;border-top:1px solid #000;padding-top:6px}
              </style>
            </head>
            <body>
              <div class="print-title">{{ \App\CPU\translate('قائمة الموردين') }}</div>
              <div class="print-sub">{{ \App\CPU\translate('تاريخ الطباعة') }}: ${dateStr}</div>
              ${clone.outerHTML}
              <div class="signatures">
                <div class="sig-box">
                  <div class="sig-line">{{ \App\CPU\translate('توقيع المراجع') }}</div>
                </div>
                <div class="sig-box">
                  <div class="sig-line">{{ \App\CPU\translate('توقيع المدير') }}</div>
                </div>
              </div>
            </body>
          </html>
        `);
        win.document.close();
      });
    }
  });
</script>
