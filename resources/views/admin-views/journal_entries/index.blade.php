@extends('layouts.admin.app')

@section('title', __('قيود اليومية'))

@section('content')
@php
    $hasSearch = request('from_date') || request('to_date') || request('branch_id') || request('account_id') || request('seller_id') || request('reference') || request('description')||request('id');
    $showAll   = (bool) request('show_all');
    $hasResultsView = $hasSearch || $showAll;
@endphp

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root{
        --c-line:#e9eef5; --c-bg:#f6f8ff; --c-soft:#fff; --rd:14px;
        --c-green:#16a34a; --c-red:#dc2626; --c-blue:#2563eb; --c-amber:#b45309;
    }
    .page-wrap{direction:rtl}
    .breadcrumb{border:1px solid var(--c-line)}
    .filter-card{border-radius:var(--rd); border:1px solid var(--c-line)}
    .card.shadowed{box-shadow:0 12px 28px -14px rgba(2,32,71,.15); border:1px solid var(--c-line); border-radius:var(--rd)}
    .card-header-lite{padding:10px 14px; background:linear-gradient(180deg,#fff,#f9fbff); border-bottom:1px solid var(--c-line); border-top-left-radius:var(--rd); border-top-right-radius:var(--rd)}
    .table thead th{position:sticky; top:0; background:var(--c-bg); z-index:2}
    table.table{border-color:var(--c-line)}
    table.table tbody tr{transition:background .2s ease}
    table.table tbody tr:nth-child(even){background:#fbfdff}
    table.table tbody tr:hover{background:#eef5ff}
    td,th{vertical-align:middle}

    .status-badge{font-size:.75rem; padding:.2rem .6rem; border-radius:999px; border:1px solid}
    .status-reversed{background:#fff1f2; color:#9f1239; border-color:#fecdd3}
    .status-reversal{background:#eef2ff; color:#3730a3; border-color:#c7d2fe}
    .status-normal{background:#f1f5f9; color:#334155; border-color:#e2e8f0}

    .toolbar{position:sticky; top:64px; z-index:6; background:#fff; border:1px solid var(--c-line); border-radius:12px; padding:10px 12px}

    .btn-icon{display:inline-flex; align-items:center; gap:6px; border-radius:10px; border:1px solid var(--c-line); background:#fff;}
    .btn-group .btn{border-radius:10px !important}
    .btn-group .btn + .btn{margin-inline-start:6px}

    .select2-container--default .select2-selection--single{
      height:38px;border:1px solid #ced4da;border-radius:.375rem
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height:36px;padding-right:8px
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
      height:36px
    }

    @media print{
        .non-printable{display:none !important}
        body{background:#fff}
        table{page-break-inside:auto}
        tr{page-break-inside:avoid; page-break-after:auto}
        @page{margin:12mm}
    }
</style>

<div class="container-fluid page-wrap">

    <div class="row align-items-center mb-3">
        <div class="col-sm">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                            <i class="tio-home-outlined"></i> {{ __('الرئيسية') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-primary">{{ __('قيود اليومية') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- فلاتر -->
    <div class="card filter-card shadow-sm mb-3 non-printable">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.journal-entries.index') }}" class="row g-3">

                <div class="col-md-2">
                    <label class="form-label">{{ __('من تاريخ') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('إلى تاريخ') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ __('الفرع') }}</label>
                    <select name="branch_id" id="branch_id" class="form-control select2">
                        <option value="">{{ __('الكل') }}</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id')==$b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ __('الحساب') }}</label>
                    <select name="account_id" id="account_id" class="form-control select2">
                        <option value="">{{ __('الكل') }}</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}" @selected(request('account_id')==$a->id)>
                                {{ $a->account }} @if($a->code) — {{ $a->code }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ __('المستخدم (المُنشئ)') }}</label>
                    <select name="seller_id" id="seller_id" class="form-control select2">
                        <option value="">{{ __('الكل') }}</option>
                        @foreach($sellers as $s)
                            <option value="{{ $s->id }}" @selected(request('seller_id')==$s->id)>
                                {{ $s->name }} @if($s->email) — {{ $s->email }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ __('رقم المرجع') }}</label>
                    <input type="text" name="reference" class="form-control" value="{{ request('reference') }}" placeholder="مثال: SALE-2025...">
                </div>
     <div class="col-md-3">
                    <label class="form-label">{{ __('رقم التسلسل') }}</label>
                    <input type="number" name="id" class="form-control" value="{{ request('id') }}" placeholder="1">
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ __('الوصف') }}</label>
                    <input type="text" name="description" class="form-control" value="{{ request('description') }}" placeholder="كلمة مفتاحية...">
                </div>

                <div class="col-12 d-flex flex-wrap mt-2" style="gap: 12px; padding: 6px;">
                    <button class="btn btn-primary" style="min-width: 140px;">
                        {{ \App\CPU\translate('تطبيق البحث') }}
                    </button>

                    <a href="{{ route('admin.journal-entries.index', array_merge(request()->all(), ['show_all' => 1])) }}" 
                       class="btn btn-outline-secondary"
                       style="min-width: 140px;"
                       title="عرض كل السندات بدون فلاتر">
                        <i class="tio-list"></i> {{ __('عرض الكل') }}
                    </a>

                    <a href="{{ route('admin.journal-entries.index') }}"
                       class="btn btn-danger"
                       style="min-width: 140px;">
                       <i class="tio-close"></i> {{ \App\CPU\translate('الغاء') }}
                    </a>

                </div>
            </form>
        </div>
    </div>

    @if($hasResultsView)
        <!-- شريط أدوات علوي -->
        <div class="toolbar non-printable mb-3 d-flex align-items-center justify-content-between">
            <div class="sticky-actions non-printable" style="padding: 8px;">
                <div class="d-flex align-items-start">
                    <button class="btn btn-sm btn-primary shadow" style="min-width: 120px;" onclick="printTableOnly('entriesTable')">
                        {{ \App\CPU\translate('طباعة') }}
                    </button>
                    <button class="btn btn-sm btn-info shadow" style="min-width: 140px; margin-right: 15px;" onclick="exportTableToExcel('entriesTable')">
                        {{ \App\CPU\translate('إصدار ملف أكسل') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- الجدول -->
        <div class="card shadowed">
            <div class="card-header-lite d-flex justify-content-between align-items-center">
                <div class="fw-bold">{{ __('قائمة القيود') }}</div>
                <div class="text-muted small">{{ now()->format('Y-m-d H:i') }}</div>
            </div>
            <div class="table-responsive">
                <table id="entriesTable" class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 110px">{{ __('التسلسل') }}</th>
                            <th style="min-width: 120px">{{ __('التاريخ') }}</th>
                            <th>{{ __('المرجع') }}</th>
                            <th>{{ __('الفرع') }}</th>
                            <th style="min-width: 320px">{{ __('أول حساب (عرض سريع)') }}</th>
                            <th>{{ __('المدين') }}</th>
                            <th>{{ __('الدائن') }}</th>
                            <th>{{ __('المستخدم') }}</th>
                            <th style="min-width: 100px">{{ __('الحالة') }}</th>
                            <th class="non-printable" style="min-width:210px">{{ __('إجراء') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $e)
                            @php
                                $first = $e->details->first();
                                $sumDebit = $e->details->sum('debit');
                                $sumCredit = $e->details->sum('credit');
                                $flag = ($e->reversal ?? 0); // 0:عادي، 1:قيد قابل للعكس، 2:تم عكسه
                                $isReversal = $flag === 1;
                                $isNormal   = $flag === 0;
                                $isReversed = $flag === 2;
                            @endphp
                            <tr id="row-{{ $e->id }}">
                                <td>{{ $e->id ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($e->entry_date ?? $e->head_date)->format('Y-m-d') }}</td>
                                <td class="fw-bold">{{ $e->reference ?? $e->head_ref }}</td>
                                <td>{{ $e->branch->name ?? '—' }}</td>
                                <td>
                                    @if($first)
                                        <div class="text-truncate" style="max-width: 320px" title="{{ $first->account->account ?? '' }}">
                                            <span class="text-muted">{{ $first->account->code ?? '' }}</span>
                                            — {{ $first->account->account ?? '' }}
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-success fw-bold">{{ number_format($sumDebit,2) }}</td>
                                <td class="text-danger fw-bold">{{ number_format($sumCredit,2) }}</td>
                                <td>{{ $e->seller->email ?? $e->writer->email ?? '—' }}</td>
                       <td>
  <span class="entry-status">
    @if($isReversed)
      <span class="status-badge status-reversed">{{ __('تم عكسه') }}</span>
    @elseif($isReversal)
      <span class="status-badge status-reversal">{{ __('قيد قابل للعكس') }}</span>
    @else
      <span class="status-badge status-locked" title="{{ __('غير قابل للعكس') }}">
        {{ __('لا يمكن عكسه') }}
      </span>
    @endif
  </span>
</td>

                                <td class="non-printable">
                                    <div class="btn-group" role="group" aria-label="Actions">
                                        <a href="{{ route('admin.journal-entries.show', $e->id) }}" 
                                           class="btn btn-outline-secondary"
                                           title="{{ __('عرض') }}">
                                            <i class="tio-visible"></i>
                                        </a>
@if( (int)($e->reserve ?? 0) !== 0 )

                                        <a href="{{ route('admin.journal-entries.edit', $e->id) }}"
                                           class="btn btn-outline-primary"
                                           title="{{ __('تعديل') }}">
                                            <i class="tio-edit"></i>
                                        </a>
@endif

                                    {{-- يظهر الزر فقط لو reserve != 0 --}}
@if( (int)($e->reserve ?? 0) !== 0 )
  <button type="button"
          class="btn btn-outline-danger"
          data-action="reverse"
          data-open-reverse="{{ $e->id }}"
          data-ref="{{ $e->reference ?? $e->head_ref }}"
          title="@if($isReversal) {{ __('عكس القيد') }} @elseif($isNormal) {{ __('غير مسموح: قيد عادي') }} @else {{ __('غير مسموح: تم عكسه') }} @endif"
          @if(!$isReversal) disabled @endif>
    <i class="tio-undo"></i>
  </button>
@endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">{{ __('لا توجد قيود مطابقة') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($entries, 'hasPages') && $entries->hasPages())
                <div class="card-footer bg-white">
                    {{ $entries->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- ============== مودال عكس القيد ============== -->
<div class="modal fade" id="reverseModal" tabindex="-1" aria-labelledby="reverseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="reverseModalLabel">{{ __('تأكيد عكس القيد') }}</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">
          {{ __('هل أنت متأكد من عكس القيد') }} <strong id="rev-ref"></strong>؟
        </p>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="1" id="hardRecalc">
          <label class="form-check-label" for="hardRecalc">
            {{ __('تحديث الأرصدة لأصلها (إعادة احتساب شاملة بعد العكس)') }}
          </label>
        </div>
        <small class="text-muted d-block mt-2">
          {{ __('سيتم إنشاء قيد عكسي لكل التفاصيل مع عكس جميع العمليات المرتبطة. اختيار إعادة الاحتساب قد يأخذ وقتًا أطول.') }}
        </small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
        <button type="button" class="btn btn-danger" id="confirmReverseBtn">{{ __('تأكيد العكس') }}</button>
      </div>
    </div>
  </div>
</div>

@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.full.min.js"></script>

<script>
(function () {
  "use strict";

  // ===== Helpers: Bootstrap version–agnostic modal controller =====
  function getModalController(modalEl){
    if (window.bootstrap && window.bootstrap.Modal) {
      const Modal = window.bootstrap.Modal;
      try {
        const inst = (typeof Modal.getInstance === 'function') ? Modal.getInstance(modalEl) : null;
        return inst || new Modal(modalEl);
      } catch(e) {
        return new Modal(modalEl);
      }
    }
    if (window.jQuery && typeof jQuery(modalEl).modal === 'function') {
      return {
        show(){ jQuery(modalEl).modal('show'); },
        hide(){ jQuery(modalEl).modal('hide'); }
      };
    }
    return {
      show(){
        modalEl.style.display='block';
        modalEl.classList.add('show');
        modalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
      },
      hide(){
        modalEl.style.display='none';
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden','true');
        document.body.classList.remove('modal-open');
      }
    };
  }

  // ===== Select2 =====
  document.addEventListener('DOMContentLoaded', function(){
    if (window.jQuery && jQuery.fn.select2) {
      jQuery('.select2').select2({ width: '100%' });
    }
  });

  // ===== Excel Export =====
  window.exportTableToExcel = function (tableId, filename = 'journal_entries.xlsx') {
    const table = document.getElementById(tableId);
    if (!table) return;
    const wb = XLSX.utils.table_to_book(table, { sheet: "Entries" });
    XLSX.writeFile(wb, filename);
  };

  // ===== Print Only Table =====
  window.printTableOnly = function (tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const html = `
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Print</title>
<style>
  body{direction:rtl;font-family:Tahoma,Arial,sans-serif;background:#fff;color:#111;padding:12px}
  table{width:100%;border-collapse:collapse;background:#fff}
  th,td{border:1px solid #e5e7eb;padding:6px 8px;text-align:center;font-size:8px}
  thead th{background:#f6f8ff;font-weight:700;font-size:12px}
  .text-success{color:#16a34a;font-weight:700}
  .text-danger{color:#dc2626;font-weight:700}
  @page{margin:12mm}
</style>
</head>
<body>
  ${table.outerHTML}
</body>
</html>`.trim();

    const win = window.open('', '_blank');
    if (!win) { alert('يرجى السماح بالنوافذ المنبثقة للطباعة'); return; }
    win.document.open();
    win.document.write(html);
    win.document.close();
    win.focus();
    win.print();
  };

  // ===== Utilities =====
  function firstValidationError(respJson){
    if (!respJson) return '';
    if (respJson.errors) {
      const firstKey = Object.keys(respJson.errors)[0];
      if (firstKey && Array.isArray(respJson.errors[firstKey]) && respJson.errors[firstKey][0]) {
        return respJson.errors[firstKey][0];
      }
    }
    return respJson.message || '';
  }

  // ===== State =====
  const state = { reverseTargetId: null };

  // ===== Open modal (data attribute) =====
  function openReverseModal(entryId, refText) {
    state.reverseTargetId = entryId;
    const refEl = document.getElementById('rev-ref');
    if (refEl) refEl.textContent = refText || ('#' + entryId);

    const modalEl = document.getElementById('reverseModal');
    const modal = getModalController(modalEl);
    modal.show();
  }

  // ===== POST reverse =====
  async function postReverse(entryId, hardRecalc) {
    const route = "{{ route('admin.journal-entries.reverse', ':id') }}".replace(':id', entryId);
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const formData = new FormData();
    // مهم: نرسل كل الأسماء المحتملة التي قد يتحقق منها الباك إند
    formData.append('journal_entry_id', entryId);
    formData.append('id', entryId);
    formData.append('entry_id', entryId);
    formData.append('hard_recalc', hardRecalc ? 1 : 0);

    const resp = await fetch(route, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: formData
    });

    let data;
    try {
      data = await resp.json();
    } catch (_) {
      const txt = await resp.text();
      throw new Error(txt || 'Invalid JSON response');
    }

    if (!resp.ok) {
      // لو فاليديشن 422
      if (resp.status === 422) {
        const msg = firstValidationError(data) || 'Validation error';
        throw new Error(msg);
      }
      throw new Error(data?.message || 'Request failed');
    }
    return data;
  }

  // ===== Delegated Events =====
  document.addEventListener('click', async function (e) {

    // (1) فتح المودال
    const opener = e.target.closest('[data-open-reverse]');
    if (opener) {
      e.preventDefault();
      const entryId = opener.getAttribute('data-open-reverse');
      const refText = opener.getAttribute('data-ref');
      openReverseModal(entryId, refText);
      return;
    }

    // (2) تأكيد العكس
    if (e.target.id === 'confirmReverseBtn') {
      e.preventDefault();
      if (!state.reverseTargetId) return;

      const btn = e.target;
      const modalEl = document.getElementById('reverseModal');
      const modal = getModalController(modalEl);
      const hardRecalc = !!document.getElementById('hardRecalc')?.checked;

      btn.disabled = true;
      const oldText = btn.textContent;
      btn.textContent = '{{ __("جارٍ التنفيذ...") }}';

      try {
        const res = await postReverse(state.reverseTargetId, hardRecalc);

        modal.hide();

        if (res && res.success) {
          const row = document.getElementById('row-' + state.reverseTargetId);
          if (row) {
            const statusWrap = row.querySelector('.entry-status');
            if (statusWrap) {
              statusWrap.innerHTML = `<span class="status-badge status-reversed">{{ __('تم عكسه') }}</span>`;
            }
            const reverseBtn = row.querySelector('[data-action="reverse"]');
            if (reverseBtn) reverseBtn.disabled = true;
          }
          alert('{{ __("تم عكس القيد بنجاح") }}');
        } else {
          alert((res && res.message) ? res.message : '{{ __("فشل في عكس القيد") }}');
        }
      } catch (err) {
        console.error(err);
        alert(err?.message || '{{ __("حدث خطأ أثناء العملية") }}');
      } finally {
        btn.disabled = false;
        btn.textContent = oldText;
      }
      return;
    }
  });

})();
</script>
