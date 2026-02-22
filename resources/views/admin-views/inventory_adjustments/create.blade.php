{{-- resources/views/admin-views/inventory-adjustments/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('inventory_adjustments'))

@push('css_or_js')
<style>
  :root{
    --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --brand:#0d6efd;
    --ok:#16a34a; --bad:#dc2626; --warn:#f59e0b; --bg:#f8fafc;
    --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
  .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .page-head h1{font-size:1.25rem;margin:0;color:var(--ink);font-weight:800}
  .sub{color:var(--muted);font-size:.9rem}

  .toolbar{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar .btn{min-height:42px}

  .form-label{font-weight:700;color:#111827}
  .form-control, .form-select{min-height:42px}
  .select2-container{width:100%!important}
  .select2-selection--single{height:42px!important;display:flex;align-items:center;border:1px solid #ced4da!important;border-radius:.375rem!important}
  .select2-selection__rendered{line-height:40px!important;padding-right:8px!important}
  .select2-selection__arrow{height:40px!important}

  .table-wrap{overflow:auto}
  table.table thead th{position:sticky;top:0;z-index:5;background:#f3f6fb}
  table.table td, table.table th{vertical-align:middle}
  .table-hover tbody tr:hover{background:#f9fbff}

  .diff-chip{
    display:inline-flex;align-items:center;gap:6px;
    border-radius:999px;padding:.35rem .6rem;font-weight:700;font-size:.85rem
  }
  .diff-up{background:#ecfdf5;color:#065f46}    /* + */
  .diff-down{background:#fef2f2;color:#991b1b} /* - */
  .diff-zero{background:#f3f4f6;color:#374151} /* 0 */

  .hint{font-size:.8rem;color:var(--muted)}
  .kpi{display:flex;gap:12px;flex-wrap:wrap}
  .kpi .box{border:1px dashed var(--grid);border-radius:12px;padding:10px 14px;min-width:180px}
  .kpi .box h6{margin:0;color:#111827;font-weight:800}
  .kpi .box p{margin:4px 0 0;color:var(--muted);font-size:.9rem}

  @media (max-width:768px){
    .page-head h1{font-size:1.1rem}
  }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('إنشاء أمر تسوية مخزنية') }}</li>
      </ol>
    </nav>
  </div>

  @php
    $currentBranch = auth('admin')->user()->branch;
    $branchId = auth('admin')->user()->branch_id;
  @endphp

  {{-- ====== رأس الصفحة ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <div>
        <h1 class="mb-1">{{ \App\CPU\translate('أمر تسوية مخزنية (جديد)') }}</h1>
        <div class="sub">
          {{ \App\CPU\translate('الفرع') }}: <strong>{{ $currentBranch->name ?? '—' }}</strong>
        </div>
      </div>
      <div class="toolbar">
        <button form="adjustmentForm" type="submit" class="btn btn-primary">
          <i class="tio-save"></i> {{ \App\CPU\translate('حفظ أمر التسوية') }}
        </button>
      </div>
    </div>
  </div>

  {{-- ====== النموذج ====== --}}
  <div class="card-soft p-3">
    <form id="adjustmentForm" action="{{ route('admin.inventory_adjustments.store') }}" method="POST" onsubmit="return validateBeforeSubmit()">
      @csrf
      <input type="hidden" name="branch_id"  value="{{ $branchId }}">
      <input type="hidden" name="status"     value="pending">
      <input type="hidden" name="created_by" value="{{ auth('admin')->id() }}">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">{{ \App\CPU\translate('الفرع') }}</label>
          <input type="text" class="form-control" value="{{ $currentBranch->name ?? \App\CPU\translate('غير محدد') }}" readonly>
        </div>
        <div class="col-md-4">
          <label for="adjustment_date" class="form-label">{{ \App\CPU\translate('تاريخ التسوية') }}</label>
          <input type="date" name="adjustment_date" id="adjustment_date" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ \App\CPU\translate('الحالة') }}</label>
          <input type="text" class="form-control" value="{{ \App\CPU\translate('قيد الانتظار') }}" readonly>
        </div>
        <div class="col-12">
          <label for="notes" class="form-label">{{ \App\CPU\translate('ملاحظات') }}</label>
          <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="{{ \App\CPU\translate('أدخل ملاحظات قصيرة (اختياري)') }}"></textarea>
        </div>
      </div>

      <hr class="my-4">

      {{-- KPI سريعة --}}
      <div class="kpi mb-3">
        <div class="box">
          <h6 id="kpi-rows">0</h6>
          <p>{{ \App\CPU\translate('عدد البنود') }}</p>
        </div>
        <div class="box">
          <h6 id="kpi-up">0.00</h6>
          <p>{{ \App\CPU\translate('إجمالي الزيادة (+)') }}</p>
        </div>
        <div class="box">
          <h6 id="kpi-down">0.00</h6>
          <p>{{ \App\CPU\translate('إجمالي النقصان (−)') }}</p>
        </div>
      </div>

      <h5 class="mb-2">{{ \App\CPU\translate('بنود التسوية') }}</h5>
      <p class="hint mb-3">{{ \App\CPU\translate('اختر المنتج وسيتم ملء الكمية النظامية تلقائيًا، ثم أدخل الكمية الفعلية ليظهر الفرق.') }}</p>

      <div class="table-responsive table-wrap">
        <table class="table table-bordered table-hover align-middle" id="items_table">
          <thead>
            <tr>
              <th style="width:28%">{{ \App\CPU\translate('المنتج') }}</th>
              <th style="width:14%">{{ \App\CPU\translate('الكمية النظامية') }}</th>
              <th style="width:14%">{{ \App\CPU\translate('الكمية الجديدة') }}</th>
              <th style="width:14%">{{ \App\CPU\translate('الفرق') }}</th>
              <th style="width:20%">{{ \App\CPU\translate('السبب') }}</th>
              <th style="width:10%">{{ \App\CPU\translate('الإجراء') }}</th>
            </tr>
          </thead>
          <tbody>
            {{-- صف مبدئي --}}
            <tr>
              <td>
                <select name="items[0][product_id]" class="form-control product-select" required>
                  <option value="" data-available="0">{{ \App\CPU\translate('اختر المنتج') }}</option>
                  @foreach($products as $product)
                    <option value="{{ $product->id }}" data-available="{{ $product->available_quantity }}">
                      {{ $product->name }} — {{ \App\CPU\translate('متاح') }}: {{ number_format($product->available_quantity,2) }}
                    </option>
                  @endforeach
                </select>
                <div class="hint mt-1 available-note">{{ \App\CPU\translate('اختر منتجًا لعرض الكمية المتاحة') }}</div>
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][adjustment_amount]" class="form-control system-quantity" placeholder="{{ \App\CPU\translate('الكمية النظامية') }}" readonly>
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][new_system_quantity]" class="form-control new-quantity" placeholder="{{ \App\CPU\translate('الكمية الجديدة') }}" required>
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][difference]" class="form-control difference d-none" readonly>
                <span class="diff-chip diff-zero js-diff-chip">0.00</span>
              </td>
              <td>
                <input type="text" name="items[0][reason]" class="form-control" placeholder="{{ \App\CPU\translate('سبب التسوية (اختياري)') }}">
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-danger remove-row"><i class="tio-delete-outlined"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

<div class="d-flex flex-wrap mt-2">
  <button type="button" id="add_row" class="btn btn-secondary mr-2 mb-2">
    <i class="tio-add"></i> {{ \App\CPU\translate('إضافة بند') }}
  </button>
  <button type="button" id="clear_rows" class="btn btn-outline-danger mb-2">
    <i class="tio-clear"></i> {{ \App\CPU\translate('إزالة كل البنود') }}
  </button>
</div>


      <hr class="my-4">

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary px-4">
          <i class="tio-save"></i> {{ \App\CPU\translate('حفظ أمر التسوية') }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
(function($){
  'use strict';

  let rowIndex = 1;

  function initSelect2(scope){
    (scope || $(document)).find('.product-select').select2({
      placeholder: "{{ \App\CPU\translate('اختر المنتج') }}",
      width: '100%',
      dir: 'rtl',
      allowClear: true
    });
  }

  function updateSystemQuantity(selectElem){
    const $row   = $(selectElem).closest('tr');
    const $sys   = $row.find('.system-quantity');
    const $note  = $row.find('.available-note');
    const avail  = parseFloat(selectElem.options[selectElem.selectedIndex]?.getAttribute('data-available')) || 0;
    $sys.val(avail.toFixed(2));
    $note.text(`{{ \App\CPU\translate('المتاح حاليًا') }}: ${avail.toFixed(2)}`);
    updateDifferenceForRow($row);
  }

  function setChip($row, diff){
    const $chip = $row.find('.js-diff-chip');
    $chip.removeClass('diff-up diff-down diff-zero');
    if(diff > 0){ $chip.addClass('diff-up'); }
    else if(diff < 0){ $chip.addClass('diff-down'); }
    else { $chip.addClass('diff-zero'); }
    $chip.text(diff.toFixed(2));
    $row.find('.difference').val(diff.toFixed(2));
  }

  function updateDifferenceForRow($row){
    const sysQ = parseFloat($row.find('.system-quantity').val()) || 0;
    const newQ = parseFloat($row.find('.new-quantity').val()) || 0;
    const diff = newQ - sysQ;
    setChip($row, diff);
    refreshKPIs();
  }

  function refreshKPIs(){
    let rows = 0, up=0, down=0;
    $('#items_table tbody tr').each(function(){
      const val = parseFloat($(this).find('.difference').val()) || 0;
      rows++;
      if(val > 0) up   += val;
      if(val < 0) down += val; // down negative
    });
    $('#kpi-rows').text(rows);
    $('#kpi-up').text((+up).toFixed(2));
    $('#kpi-down').text((+down).toFixed(2));
  }

  function addRow(){
    const html = `
      <tr>
        <td>
          <select name="items[${rowIndex}][product_id]" class="form-control product-select" required>
            <option value="" data-available="0">{{ \App\CPU\translate('اختر المنتج') }}</option>
            @foreach($products as $product)
              <option value="{{ $product->id }}" data-available="{{ $product->available_quantity }}">
                {{ $product->name }} — {{ \App\CPU\translate('متاح') }}: {{ number_format($product->available_quantity,2) }}
              </option>
            @endforeach
          </select>
          <div class="hint mt-1 available-note">{{ \App\CPU\translate('اختر منتجًا لعرض الكمية المتاحة') }}</div>
        </td>
        <td>
          <input type="number" step="0.01" name="items[${rowIndex}][adjustment_amount]" class="form-control system-quantity" placeholder="{{ \App\CPU\translate('الكمية النظامية') }}" readonly>
        </td>
        <td>
          <input type="number" step="0.01" name="items[${rowIndex}][new_system_quantity]" class="form-control new-quantity" placeholder="{{ \App\CPU\translate('الكمية الجديدة') }}" required>
        </td>
        <td>
          <input type="number" step="0.01" name="items[${rowIndex}][difference]" class="form-control difference d-none" readonly>
          <span class="diff-chip diff-zero js-diff-chip">0.00</span>
        </td>
        <td>
          <input type="text" name="items[${rowIndex}][reason]" class="form-control" placeholder="{{ \App\CPU\translate('سبب التسوية (اختياري)') }}">
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-danger remove-row"><i class="tio-delete-outlined"></i></button>
        </td>
      </tr>`;
    $('#items_table tbody').append(html);
    initSelect2($('#items_table tbody tr:last'));
    rowIndex++;
    refreshKPIs();
  }

  function validateBeforeSubmit(){
    let ok = true;
    let msg = '';
    const dateVal = $('#adjustment_date').val();
    if(!dateVal){
      ok = false; msg = "{{ \App\CPU\translate('الرجاء اختيار تاريخ التسوية') }}";
    }
    if(ok){
      const rows = $('#items_table tbody tr');
      if(!rows.length){ ok=false; msg="{{ \App\CPU\translate('أضف بندًا واحدًا على الأقل') }}"; }
      rows.each(function(i, tr){
        const prod = $(tr).find('.product-select').val();
        const newQ = $(tr).find('.new-quantity').val();
        if(!prod || newQ === '' || isNaN(parseFloat(newQ))){
          ok=false; msg="{{ \App\CPU\translate('تحقق من اختيار المنتج وإدخال الكمية الجديدة لكل بند') }}";
          return false;
        }
      });
    }
    if(!ok){
      toastr.error(msg);
      return false;
    }
    return true;
  }

  // Init
  $(document).ready(function(){
    initSelect2();

    // change product
    $(document).on('change', '.product-select', function(){ updateSystemQuantity(this); });

    // typing new qty
    $(document).on('input', '.new-quantity', function(){ updateDifferenceForRow($(this).closest('tr')); });

    // add row
    $('#add_row').on('click', function(){
      addRow();
      toastr.success("{{ \App\CPU\translate('تمت إضافة بند جديد') }}");
    });

    // clear rows
    $('#clear_rows').on('click', function(){
      if(confirm("{{ \App\CPU\translate('سيتم حذف جميع البنود، هل أنت متأكد؟') }}")){
        $('#items_table tbody').html('');
        rowIndex = 0;
        addRow();
        toastr.info("{{ \App\CPU\translate('تمت إزالة البنود وإضافة صف جديد فارغ') }}");
      }
    });

    // remove single row
    $(document).on('click', '.remove-row', function(){
      const $tr = $(this).closest('tr');
      if($('#items_table tbody tr').length === 1){
        // لو آخر صف، نظّفه بدل ما نحذفه
        $tr.find('.product-select').val('').trigger('change');
        $tr.find('.system-quantity,.new-quantity,.difference').val('');
        $tr.find('.available-note').text("{{ \App\CPU\translate('اختر منتجًا لعرض الكمية المتاحة') }}");
        setChip($tr, 0);
      }else{
        $tr.remove();
      }
      refreshKPIs();
    });

    refreshKPIs();
  });

})(jQuery);
</script>
