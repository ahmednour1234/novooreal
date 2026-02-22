@extends('layouts.admin.app')

@section('title', \App\CPU\translate('إنشاء تحويل جديد'))

@push('css_or_js')
    <!-- Custom / Select2 / Toastr -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root{
            --ink:#0f172a; --muted:#667085; --grid:#e9eef5; --brand:#0d6efd;
            --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
        }
        .card-soft{border:1px solid var(--grid); border-radius:var(--rd); box-shadow:var(--shadow); background:#fff}
        .section-title{font-weight:800; font-size:18px; margin:6px 0 14px; display:flex; align-items:center; gap:10px}
        .section-title .dot{width:10px; height:10px; border-radius:50%; background:var(--brand)}
        .help{font-size:12px; color:var(--muted)}
        .btn-min{min-height:42px}
        .select2-container{width:100%!important}
        .select2-selection--single{
            height:38px!important; border:1px solid #ced4da!important; border-radius:.375rem!important; display:flex; align-items:center
        }
        .select2-selection__rendered{line-height:36px!important; padding-right:8px!important}
        .select2-selection__arrow{height:36px!important}

        table.table thead th{position:sticky; top:0; z-index:5; background:#f8fafc}
        table.table td, table.table th{vertical-align:middle}

        .toolbar{display:flex; flex-wrap:wrap; gap:8px}
        .summary-bar{display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between}

        .dataTables_wrapper .dataTables_filter input{display:none}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تحويل مخزني  جديد') }}</li>
      </ol>
    </nav>
  </div>

  <form action="{{ route('admin.transfer.store') }}" method="POST" id="transferForm">
    @csrf

    <!-- بيانات التحويل -->
    <div class="card card-soft mb-4">
      <div class="card-body">
        <div class="section-title mb-3"><span class="dot"></span>{{ \App\CPU\translate('بيانات التحويل') }}</div>

        <div class="row g-3">
          <div class="col-md-4">
            <label for="transfer_number" class="form-label fw-bold">{{ \App\CPU\translate('رقم التحويل') }}</label>
            <input type="text" name="transfer_number" id="transfer_number" class="form-control" required>
            <div class="help mt-1">{{ \App\CPU\translate('اكتب رقم مميز أو اتركه حسب النظام لديك') }}</div>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">{{ \App\CPU\translate('فرع المصدر') }}</label>
            <input type="text" class="form-control" value="{{ auth('admin')->user()->branch->name }}" readonly>
            <input type="hidden" name="source_branch_id" id="source_branch_id" value="{{ auth('admin')->user()->branch_id }}">
          </div>

          <div class="col-md-4">
            <label for="destination_branch_id" class="form-label fw-bold">{{ \App\CPU\translate('الفرع الوجهة') }}</label>
            <select name="destination_branch_id" id="destination_branch_id" class="form-control select2" required>
              <option value="">{{ \App\CPU\translate('اختر الفرع') }}</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $branch->id == auth('admin')->user()->branch_id ? 'disabled' : '' }}>
                  {{ $branch->name }} {{ $branch->id == auth('admin')->user()->branch_id ? '— '.__('المصدر') : '' }}
                </option>
              @endforeach
            </select>
            <div class="help mt-1">{{ \App\CPU\translate('لا يمكن اختيار فرع المصدر كوجهة') }}</div>
          </div>

          <div class="col-12">
            <label for="notes" class="form-label fw-bold">{{ \App\CPU\translate('ملاحظات') }}</label>
            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="{{ \App\CPU\translate('اكتب أي ملاحظات على التحويل (اختياري)') }}"></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- تفاصيل الأصناف -->
    <div class="card card-soft">
      <div class="card-body">
        <div class="section-title mb-3 d-flex justify-content-between align-items-center">
          <div><span class="dot"></span>{{ \App\CPU\translate('تفاصيل الأصناف المحولة') }}</div>
          <div class="toolbar">
            <button type="button" id="addRow" class="btn btn-secondary btn-min">
              <i class="tio-add-circle"></i> {{ \App\CPU\translate('أضف صف جديد') }}
            </button>
            <button type="button" id="clearRows" class="btn btn-outline-danger btn-min">
              <i class="tio-clear"></i> {{ \App\CPU\translate('مسح كل الصفوف') }}
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered align-middle" id="itemsTable">
            <thead>
              <tr>
                <th style="width:28%">{{ \App\CPU\translate('المنتج') }}</th>
                <th style="width:14%">{{ \App\CPU\translate('الكمية') }}</th>
                <th style="width:14%">{{ \App\CPU\translate('الوحدة') }}</th>
                <th style="width:18%">{{ \App\CPU\translate('سعر الوحدة') }}</th>
                <th style="width:18%">{{ \App\CPU\translate('التكلفة الإجمالية') }}</th>
                <th style="width:8%">{{ \App\CPU\translate('إجراء') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr class="item-row">
                <td>
                  <select name="items[0][product_id]" class="form-control select2 product-select" required>
                    <option value="">{{ \App\CPU\translate('اختر المنتج') }}</option>
                    @foreach($products as $product)
                      <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <input type="number" step="0.01" name="items[0][quantity]" class="form-control quantity-input" required>
                </td>
                <td>
                  <select name="items[0][unit]" class="form-control select2 unit-select" required>
                    <option value="كبري">{{ \App\CPU\translate('كبري') }}</option>
                    <option value="صغري">{{ \App\CPU\translate('صغري') }}</option>
                  </select>
                </td>
                <td>
                  <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control unit-cost-input" readonly value="0.00">
                </td>
                <td>
                  <input type="number" step="0.01" name="items[0][total_cost]" class="form-control total-cost-input" readonly value="0.00">
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-light border remove-row" title="{{ \App\CPU\translate('حذف') }}">
                    <i class="tio-delete-outlined"></i>
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="6" class="bg-light">
                  <div class="summary-bar">
                    <div class="help"><i class="tio-info-outined"></i> {{ \App\CPU\translate('التكلفة تُحسب من رصيد المخزون بأسلوب FIFO بحسب الكمية والوحدة') }}</div>
                    <div class="d-flex align-items-center gap-3">
                      <div>
                        <label class="form-label fw-bold m-0">{{ \App\CPU\translate('عدد الأصناف') }}</label>
                        <span class="badge bg-secondary ms-2" id="item_count">1</span>
                      </div>
                      <div>
                        <label for="calculated_total" class="form-label fw-bold m-0">{{ \App\CPU\translate('إجمالي قيمة التحويل') }}</label>
                        <input type="number" step="0.01" id="calculated_total" name="total_amount" class="form-control d-inline-block" style="width:180px" readonly value="0.00">
                      </div>
                    </div>
                  </div>
                </th>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="d-flex justify-content-between mt-3">
          <a href="{{ route('admin.product.list') }}" class="btn btn-outline-secondary btn-min">
            <i class="tio-chevron-left"></i> {{ \App\CPU\translate('رجوع') }}
          </a>
          <button type="submit" class="btn btn-primary btn-min">
            <i class="tio-save"></i> {{ \App\CPU\translate('حفظ التحويل') }}
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

{{-- قالب صف جديد (نصّي آمن) --}}
<script type="text/template" id="rowTemplate">
  <tr class="item-row">
    <td>
      <select name="items[__INDEX__][product_id]" class="form-control select2 product-select" required>
        <option value="">{{ \App\CPU\translate('اختر المنتج') }}</option>
        @foreach($products as $product)
          <option value="{{ $product->id }}">{{ $product->name }}</option>
        @endforeach
      </select>
    </td>
    <td><input type="number" step="0.01" name="items[__INDEX__][quantity]" class="form-control quantity-input" required></td>
    <td>
      <select name="items[__INDEX__][unit]" class="form-control select2 unit-select" required>
        <option value="كبري">{{ \App\CPU\translate('كبري') }}</option>
        <option value="صغري">{{ \App\CPU\translate('صغري') }}</option>
      </select>
    </td>
    <td><input type="number" step="0.01" name="items[__INDEX__][unit_cost]" class="form-control unit-cost-input" readonly value="0.00"></td>
    <td><input type="number" step="0.01" name="items[__INDEX__][total_cost]" class="form-control total-cost-input" readonly value="0.00"></td>
    <td class="text-center">
      <button type="button" class="btn btn-light border remove-row" title="{{ \App\CPU\translate('حذف') }}">
        <i class="tio-delete-outlined"></i>
      </button>
    </td>
  </tr>
</script>
@endsection

<!-- jQuery / Select2 / Toastr -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const $ = window.jQuery;
  if(!window.jQuery){
    console.error('jQuery is required for Select2.');
    return;
  }

  let rowCount = 1;

  function initSelect2(ctx){
    (ctx ? $(ctx) : $(document)).find('.select2').select2({
      placeholder: "{{ \App\CPU\translate('اختر') }}",
      width: '100%',
      dir: 'rtl',
      allowClear: true
    });
  }
  initSelect2();

  function updateItemCount(){
    document.getElementById('item_count').innerText =
      document.querySelectorAll('#itemsTable tbody tr').length;
  }

  function recalcTotal(){
    let total = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(function(tr){
      const v = parseFloat(tr.querySelector('.total-cost-input').value) || 0;
      total += v;
    });
    document.getElementById('calculated_total').value = total.toFixed(2);
    updateItemCount();
  }

  function addRow(){
    const tpl = document.getElementById('rowTemplate').innerHTML.replace(/__INDEX__/g, rowCount);
    const tbody = document.querySelector('#itemsTable tbody');
    tbody.insertAdjacentHTML('beforeend', tpl);
    const newRow = tbody.lastElementChild;
    initSelect2(newRow);
    rowCount++;
    updateItemCount();
  }

  // ربط زر إضافة الصف (Vanilla JS لضمان العمل حتى لو تعارضت مكتبات)
  document.getElementById('addRow').addEventListener('click', addRow);

  // مسح كل الصفوف
  document.getElementById('clearRows').addEventListener('click', function(){
    const tbody = document.querySelector('#itemsTable tbody');
    tbody.innerHTML = '';
    rowCount = 0;
    addRow();
    recalcTotal();
  });

  // حذف صف
  document.addEventListener('click', function(e){
    if(e.target.closest('.remove-row')){
      const tr = e.target.closest('tr');
      tr.parentNode.removeChild(tr);
      if(document.querySelectorAll('#itemsTable tbody tr').length === 0){
        addRow();
      }
      recalcTotal();
    }
  });

  // عند تغيير المنتج/الكمية/الوحدة: حساب التكلفة AJAX
  function debounce(fn, delay){ let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),delay); }; }
  const recalcRow = debounce(function($row){
    const productId = $row.find('.product-select').val();
    const quantity  = parseFloat($row.find('.quantity-input').val()) || 0;
    const unit      = $row.find('.unit-select').val();

    if(!productId || quantity <= 0){
      $row.find('.total-cost-input').val('0.00');
      $row.find('.unit-cost-input').val('0.00');
      recalcTotal();
      return;
    }

    $.ajax({
      url: "{{ route('admin.getPrice') }}",
      method: "GET",
      data: { product_id: productId, quantity: quantity, unit: unit },
      success: function(resp){
        if(resp.error){
          toastr.error(resp.error);
          $row.find('.total-cost-input').val('0.00');
          $row.find('.unit-cost-input').val('0.00');
          recalcTotal();
          return;
        }
        if (quantity > (resp.total_available_quantity || 0)) {
          toastr.error("{{ \App\CPU\translate('الكمية المدخلة تتعدى الكمية المتاحة بالمخزن') }}: " + (resp.total_available_quantity || 0));
          $row.find('.total-cost-input').val('0.00');
          $row.find('.unit-cost-input').val('0.00');
          recalcTotal();
          return;
        }
        const totalCost = parseFloat(resp.total_cost);
        if(isNaN(totalCost) || totalCost <= 0){
          toastr.error("{{ \App\CPU\translate('لم يتم إيجاد السعر لهذا المنتج') }}");
          $row.find('.total-cost-input').val('0.00');
          $row.find('.unit-cost-input').val('0.00');
          recalcTotal();
          return;
        }
        const unitCost = totalCost / quantity;
        $row.find('.total-cost-input').val(totalCost.toFixed(2));
        $row.find('.unit-cost-input').val(unitCost.toFixed(2));
        recalcTotal();
      },
      error: function(xhr){
        console.error(xhr.responseText);
        toastr.error("{{ \App\CPU\translate('حدث خطأ أثناء جلب السعر') }}");
      }
    });
  }, 250);

  // تغييرات ديناميكية (jQuery delegation)
  $(document).on('change', '.product-select', function(){
    const $row = $(this).closest('tr');
    $row.find('.quantity-input').val(1);
    $row.find('.unit-select').val('كبري').trigger('change');
    recalcRow($row);
  });

  $(document).on('input change', '.quantity-input, .unit-select', function(){
    const $row = $(this).closest('tr');
    recalcRow($row);
  });

  // تحقق قبل الإرسال
  document.getElementById('transferForm').addEventListener('submit', function(e){
    const src = document.getElementById('source_branch_id').value;
    const dst = document.getElementById('destination_branch_id').value;

    if(!dst){
      toastr.error("{{ \App\CPU\translate('اختر الفرع الوجهة') }}");
      e.preventDefault(); return;
    }
    if(src === dst){
      toastr.error("{{ \App\CPU\translate('لا يمكن تحويل لنفس فرع المصدر') }}");
      e.preventDefault(); return;
    }

    let valid = false;
    document.querySelectorAll('#itemsTable tbody tr').forEach(function(tr){
      const pid = tr.querySelector('.product-select').value;
      const qty = parseFloat(tr.querySelector('.quantity-input').value) || 0;
      const tot = parseFloat(tr.querySelector('.total-cost-input').value) || 0;
      if(pid && qty > 0 && tot > 0){ valid = true; }
    });
    if(!valid){
      toastr.error("{{ \App\CPU\translate('يجب إدخال صنف واحد على الأقل بكمية وتكلفة صحيحة') }}");
      e.preventDefault(); return;
    }
  });

});
</script>
