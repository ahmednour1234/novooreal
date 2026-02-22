{{-- resources/views/admin-views/inventory_adjustments/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('edit_inventory_adjustment'))

@push('css_or_js')
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --brand:#001B63;
      --bg:#f8fafc; --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .card-head{background:var(--brand);color:#fff;border-top-left-radius:var(--rd);border-top-right-radius:var(--rd)}
    .card-head .title{font-size:1.1rem;font-weight:800;margin:0}
    .subtle{color:var(--muted);font-size:.92rem}

    /* أزرار بأيقونات + مسافات RTL/LTR أنيقة */
    .btn-row{display:flex;flex-wrap:wrap;align-items:center}
    .btn-row > *{margin:0 .5rem .5rem 0}
    [dir="rtl"] .btn-row > *{margin:0 0 .5rem .5rem}

    .select2-container{width:100%!important}
    .select2-selection--single{
      height:42px!important;border:1px solid #ced4da!important;border-radius:.375rem!important;display:flex;align-items:center
    }
    .select2-selection__rendered{line-height:40px!important;padding-right:8px!important}
    .select2-selection__arrow{height:40px!important}

    .form-label{font-weight:700;color:#111827}
    .form-control{min-height:42px}

    .table-wrap{overflow:auto}
    table.table thead th{background:#f3f6fb;position:sticky;top:0;z-index:5}
    table.table td, table.table th{vertical-align:middle}
    .table-hover tbody tr:hover{background:#f9fbff}

    .help{font-size:.84rem;color:var(--muted)}
    .badge-soft{background:#eef2ff;color:#3730a3;border-radius:999px;padding:.2rem .5rem;font-weight:700}

    /* صف الأوامر السفلي ثابت داخل البطاقة */
    .card-actions-sticky{
      position:sticky;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--grid);
      padding:12px;border-bottom-left-radius:var(--rd);border-bottom-right-radius:var(--rd)
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
        <li class="breadcrumb-item">
          <a href="{{ route('admin.inventory_adjustments.index') }}" class="text-secondary">
            {{ \App\CPU\translate('اوامر جرد') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تعديل امر تسوية') }}</li>
      </ol>
    </nav>
  </div>

  <div class="card card-soft">

    <div class="card-body">
      <form action="{{ route('admin.inventory_adjustments.update', $adjustment->id) }}" method="POST" id="adjForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="branch_id" value="{{ $adjustment->branch_id }}">
        <input type="hidden" name="status" value="{{ $adjustment->status }}">
        <input type="hidden" name="created_by" value="{{ $adjustment->created_by }}">

        {{-- الحقول الأساسية --}}
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ \App\CPU\translate('فرع') }}</label>
            <input type="text" class="form-control" value="{{ $adjustment->branch->name ?? '—' }}" readonly>
          </div>
          <div class="col-md-6">
            <label for="adjustment_date" class="form-label">{{ \App\CPU\translate('تاريخ الجرد') }}</label>
            <input type="date" name="adjustment_date" id="adjustment_date" class="form-control"
                   value="{{ $adjustment->adjustment_date }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ \App\CPU\translate('حالة') }}</label>
            <input type="text" class="form-control"
              value="@if($adjustment->status=='pending') {{ \App\CPU\translate('قيد الانتظار') }}
                     @elseif($adjustment->status=='approved') {{ \App\CPU\translate('معتمد') }}
                     @elseif($adjustment->status=='rejected') {{ \App\CPU\translate('مرفوض') }}
                     @else {{ $adjustment->status }} @endif" readonly>
          </div>
          <div class="col-md-6">
            <label for="notes" class="form-label">{{ \App\CPU\translate('ملاحظة') }}</label>
            <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="{{ \App\CPU\translate('أدخل الملاحظات إن وجدت') }}">{{ $adjustment->notes }}</textarea>
          </div>
        </div>

        <hr class="my-4">

        {{-- بنود التسوية --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h6 class="m-0">{{ \App\CPU\translate('بنود التسوية') }}</h6>
          <div class="btn-row">
            <button type="button" id="add_row" class="btn btn-secondary">
              <i class="tio-add"></i> {{ \App\CPU\translate('إضافة بند') }}
            </button>
            <button type="button" id="clear_rows" class="btn btn-outline-danger">
              <i class="tio-clear"></i> {{ \App\CPU\translate('إزالة كل البنود') }}
            </button>
          </div>
        </div>
        <div class="help mb-2">{{ \App\CPU\translate('اختر المنتج، سنملأ الكمية النظامية تلقائيًا، ثم أدخل الكمية الجديدة ليتم حساب الفرق.') }}</div>

        <div class="table-wrap">
          <table class="table table-bordered table-hover align-middle" id="items_table">
            <thead>
              <tr>
                <th style="min-width:260px">{{ \App\CPU\translate('المنتج') }}</th>
                <th style="width:160px">{{ \App\CPU\translate('الكمية النظامية') }}</th>
                <th style="width:160px">{{ \App\CPU\translate('الكمية الجديدة') }}</th>
                <th style="width:140px">{{ \App\CPU\translate('الفرق') }}</th>
                <th>{{ \App\CPU\translate('السبب') }}</th>
                <th style="width:90px">{{ \App\CPU\translate('الإجراء') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($adjustment->items as $index => $item)
                <tr>
                  <td>
                    <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                      <option value="" data-available="0">{{ \App\CPU\translate('اختر المنتج') }}</option>
                      @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                data-available="{{ $product->available_quantity }}"
                                {{ $product->id == $item->product_id ? 'selected' : '' }}>
                          {{ $product->name }} — <span>{{ \App\CPU\translate('المتوفر') }}: {{ number_format($product->available_quantity,2) }}</span>
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="number" step="0.01" name="items[{{ $index }}][adjustment_amount]"
                           class="form-control system-quantity" placeholder="{{ \App\CPU\translate('الكمية النظامية') }}"
                           value="{{ $item->adjustment_amount }}" readonly>
                  </td>
                  <td>
                    <input type="number" step="0.01" name="items[{{ $index }}][new_system_quantity]"
                           class="form-control new-quantity" placeholder="{{ \App\CPU\translate('الكمية الجديدة') }}"
                           value="{{ $item->new_system_quantity }}" required>
                  </td>
                  <td>
                    <input type="number" step="0.01" name="items[{{ $index }}][difference]"
                           class="form-control difference" placeholder="{{ \App\CPU\translate('الفرق') }}"
                           value="{{ $item->difference }}" readonly>
                  </td>
                  <td>
                    <input type="text" name="items[{{ $index }}][reason]" class="form-control"
                           placeholder="{{ \App\CPU\translate('السبب') }}" value="{{ $item->reason }}">
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-white btn-sm remove-row" title="{{ \App\CPU\translate('حذف السطر') }}">
                      <i class="tio-delete-outlined"></i>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="6" class="text-end">
                  <span class="help">{{ \App\CPU\translate('إجمالي البنود') }}: <strong id="items_count">{{ count($adjustment->items) }}</strong></span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        {{-- أزرار الحفظ --}}
        <div class="card-actions-sticky">
          <div class="btn-row">
            <button type="submit" class="btn btn-primary">
              <i class="tio-save"></i> {{ \App\CPU\translate('تحديث أمر التسوية') }}
            </button>
            <a href="{{ route('admin.inventory_adjustments.index') }}" class="btn btn-outline-secondary">
              <i class="tio-rotate-left"></i> {{ \App\CPU\translate('رجوع للقائمة') }}
            </a>
          </div>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  {{-- Bootstrap JS لو مش متحمّل في الـ layout --}}
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  <script>
    (function($){
      'use strict';

      // مؤشر الصف التالي
      let rowIndex = {{ count($adjustment->items) }};

      // تهيئة Select2
      function initSelect2(scope){
        (scope || $(document)).find('.product-select').select2({
          placeholder: "{{ \App\CPU\translate('اختر المنتج') }}",
          width: '100%'
        });
      }

      // تعيين الكمية النظامية من data-available + تحديث الفرق
      function updateSystemQuantity(selectEl){
        const available = selectEl.options[selectEl.selectedIndex]?.getAttribute('data-available') || 0;
        const $row = $(selectEl).closest('tr');
        $row.find('.system-quantity').val(parseFloat(available).toFixed(2));
        updateDifference($row);
      }

      // حساب الفرق
      function updateDifference($row){
        const systemQty = parseFloat($row.find('.system-quantity').val()) || 0;
        const newQty    = parseFloat($row.find('.new-quantity').val()) || 0;
        $row.find('.difference').val((newQty - systemQty).toFixed(2));
      }

      // تحديث عداد البنود
      function refreshCount(){
        $('#items_count').text($('#items_table tbody tr').length);
      }

      // إضافة صف جديد
      function addRow(){
        const html = `
          <tr>
            <td>
              <select name="items[${rowIndex}][product_id]" class="form-control product-select" required>
                <option value="" data-available="0">{{ \App\CPU\translate('اختر المنتج') }}</option>
                @foreach($products as $product)
                  <option value="{{ $product->id }}" data-available="{{ $product->available_quantity }}">
                    {{ $product->name }} — {{ \App\CPU\translate('المتوفر') }}: {{ number_format($product->available_quantity,2) }}
                  </option>
                @endforeach
              </select>
            </td>
            <td>
              <input type="number" step="0.01" name="items[${rowIndex}][adjustment_amount]" class="form-control system-quantity" placeholder="{{ \App\CPU\translate('الكمية النظامية') }}" readonly>
            </td>
            <td>
              <input type="number" step="0.01" name="items[${rowIndex}][new_system_quantity]" class="form-control new-quantity" placeholder="{{ \App\CPU\translate('الكمية الجديدة') }}" required>
            </td>
            <td>
              <input type="number" step="0.01" name="items[${rowIndex}][difference]" class="form-control difference" placeholder="{{ \App\CPU\translate('الفرق') }}" readonly>
            </td>
            <td>
              <input type="text" name="items[${rowIndex}][reason]" class="form-control" placeholder="{{ \App\CPU\translate('السبب') }}">
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-white btn-sm remove-row" title="{{ \App\CPU\translate('حذف السطر') }}">
                <i class="tio-delete-outlined"></i>
              </button>
            </td>
          </tr>
        `;
        $('#items_table tbody').append(html);
        rowIndex++;
        initSelect2($('#items_table tbody tr:last'));
        refreshCount();
      }

      // إزالة كل البنود
      function clearRows(){
        if(confirm("{{ \App\CPU\translate('هل تريد إزالة جميع البنود؟') }}")){
          $('#items_table tbody').empty();
          refreshCount();
        }
      }

      // أحداث
      $(document).on('change', '.product-select', function(){ updateSystemQuantity(this); });
      $(document).on('input', '.new-quantity', function(){ updateDifference($(this).closest('tr')); });
      $(document).on('click', '.remove-row', function(){
        $(this).closest('tr').remove();
        refreshCount();
      });

      $('#add_row').on('click', addRow);
      $('#clear_rows').on('click', clearRows);

      // تهيئة أولية
      initSelect2();
      // لو فيه صفوف محمّلة مسبقًا، نضمن تعبئة الكمية النظامية
      $('.product-select').each(function(){ updateSystemQuantity(this); });

    })(jQuery);
  </script>
