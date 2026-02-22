{{-- resources/views/admin/quotations/create.blade.php --}}
@extends('layouts.admin.app')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
      rel="stylesheet"/>
    <style>
     
      .current-time { font-size: .9rem; opacity: .8; }
      .filter-form .form-label { font-weight: 500; }
      .table thead { background: #f5f7fa; }
      .table th, .table td { vertical-align: middle !important; }
      .table input, .table select {
        background: #fff; border: 1px solid #ced4da;
        border-radius: 4px; height: 38px;
        padding: .375rem .75rem; font-size: .9rem;
      }
      .is-invalid { border-color: #dc3545 !important; }
      .warning-text { color: #dc3545; font-size: .85rem; display: none; }
      .summary-card { background: #fff; padding: 1rem; border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      }
      .summary-row { display: flex; justify-content: space-between; margin-bottom: .5rem; }
      .final-total { border-top: 1px dashed #ced4da; padding-top: .75rem; margin-top: .5rem; font-weight: 700; }
      .invoice-discount { display: flex; gap: .5rem; margin-bottom: 1rem; }
      .invoice-discount select, .invoice-discount input { flex: 1; height: 38px; }
    </style>
    <style>
    .select2-container {
        width: 80% !important;
    }
    .select2-container--default .select2-selection--single {
        padding: 8px 8px;
        height: auto;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        padding: 6px 6px;
    }
       #product-rows td, #product-rows th {
        font-size: 11px; /* حجم خط أكبر */
        padding: 10px 8px; /* مساحة أكبر حول النص */
        vertical-align: middle; /* محاذاة وسطية */
    }

    #product-rows select,
    #product-rows input {
        width: 100%;
        font-size: 11px;
        padding: 6px 8px;
    }

    #product-rows .product-code {
        font-weight: bold;
        display: block;
        font-size: 8px;
        white-space: nowrap; /* ما يكسرش الكود */
    }
        /* عرض مخصص لكل عمود */
    #product-rows td:nth-child(1) { min-width: 300px; } /* المنتج */
    #product-rows td:nth-child(2) { min-width: 100px; } /* كود المنتج */
    #product-rows td:nth-child(3) { min-width: 80px; }  /* الوحدة */
    #product-rows td:nth-child(4),
    #product-rows td:nth-child(5),
    #product-rows td:nth-child(6),
    #product-rows td:nth-child(7) { min-width: 110px; }
    #product-rows td:nth-child(8) { min-width: 150px; } /* الضريبة */
    #product-rows td:nth-child(9),
    #product-rows td:nth-child(10),
    #product-rows td:nth-child(11) { min-width: 120px; }
    #product-rows td:last-child { width: 50px; text-align: center; }

</style>

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                    <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.costcenter.add') }}" class="text-primary">
                    {{ \App\CPU\translate('فاتورة مبيعات') }}
                </a>
            </li>
            
        </ol>
    </nav>
</div>
  <form id="quotation-form" action="{{ route('admin.sells.store') }}" method="POST">
    @csrf
    <input type="hidden" name="type" value="8">
    <input type="hidden" name="order_amount" id="order_amount" value="0">
    <input type="hidden" name="cash" value="2">
    <input type="hidden" name="extra_discount" id="extra_discount" value="0">
    <input type="hidden" name="order_type"  value="{{$orderType}}">

    <div class="card card-custom mb-4">
      <!--<div class="card-header-custom">-->
      <!--  <span>إنشاء فاتورة بيع</span>-->
      <!--  <span class="current-time" id="current-time"></span>-->
      <!--</div>-->
      <div class="card-body p-4">
        {{-- اختيار العميل --}}
<!-- 🔽 صف اختيار العميل وتاريخ الفاتورة -->
<div class="row filter-form mb-3">
    <!-- اختيار العميل -->
    <div class="col-md-5">
        <label class="form-label">اختر العميل:</label>
        <div class="input-group">
    <select id="supplier" name="customer_id" class="form-control select2" onchange="showSupplierDetails(this)">
            <option value="">-- اختر العميل --</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}"
                        data-name="{{ $c->name }}"
                        data-phone="{{ $c->mobile }}"
                        data-email="{{ $c->email }}"
                        data-history="{{ $c->c_history }}"
                        data-tax="{{ $c->tax_number }}"
                        data-address="{{ $c->address }}">
                    {{ $c->name }}
                </option>
            @endforeach
        </select>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>

    <!-- تاريخ الفاتورة -->
    <div class="col-md-5">
        <label class="form-label">تاريخ الفاتورة:</label>
        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
    </div>
</div>

<!-- ✅ مودال إضافة عميل جديد -->


        {{-- تفاصيل العميل --}}
        <div id="supplier-details" class="card mb-4" style="display:none;">
          <div class="card-body">
            <div class="row">
              <div class="col-md-4"><strong>اسم العميل:</strong> <span id="sup-name"></span></div>
              <div class="col-md-4"><strong>الهاتف:</strong> <span id="sup-phone"></span></div>
              <div class="col-md-4"><strong>البريد الإلكتروني:</strong> <span id="sup-email"></span></div>
              <div class="col-md-4"><strong>السجل التجاري:</strong> <span id="sup-history"></span></div>
              <div class="col-md-4"><strong>الرقم الضريبي:</strong> <span id="sup-tax"></span></div>
              <div class="col-md-4"><strong>العنوان:</strong> <span id="sup-address"></span></div>
            </div>
          </div>
        </div>

        {{-- إضافة منتجات --}}
        <div class="product-section mb-4">
          <h5 class="mb-3">إضافة منتجات</h5>
          <div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead>
    <tr>
      <th>المنتج</th>
      <th>الكود</th>

      @if($orderType == 'product')
        <th>الوحدة</th>
      @endif

      <th>الكمية</th>
      <th>السعر</th>
      <th>خصم منتج</th>
      <th>خصم إضافي</th>
      <th>نوع الضريبة</th>
      <th>قيمة الضريبة</th>
      <th>شامل</th>
      <th>الإجمالي</th>
      <th></th>
    </tr>
  </thead>

<tbody id="product-rows">
    <tr>
        <!-- ✅ اختيار المنتج -->
        <td>
            <select name="products[0][id]" class="form-control select2" onchange="setProductData(this)">
                <option value="">-- اختر المنتج --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}"
                        data-code="{{ $p->product_code }}"
                        data-unit-value="{{ $p->unit_value }}"
                        data-selling-price="{{ $p->selling_price }}"
                        data-discount="{{ $p->discount }}"
                        data-discount-type="{{ $p->discount_type }}">
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <!-- ✅ كود المنتج -->
        <td><span class="product-code"></span></td>

        <!-- ✅ اختيار الوحدة -->
        @if($orderType === 'product')
            <td>
                <select name="products[0][unit]" class="form-control" onchange="onUnitChange(this)">
                    <option value="0">صغرى</option>
                    <option value="1">كبرى</option>
                </select>
            </td>
        @endif

        <!-- ✅ الكمية -->
        <td>
            <input name="products[0][quantity]" type="number" class="form-control" min="1" value="1" onchange="calculateRowTotal(this)">
        </td>

        <!-- ✅ السعر -->
        <td>
            <input name="products[0][price]" type="number" class="form-control price-input" step="0.01" min="0" onchange="calculateRowTotal(this)">
            <small class="warning-text text-danger d-block" style="display:none;"></small>
        </td>

        <!-- ✅ خصم المنتج -->
        <td><input name="products[0][default_discount]" type="text" class="form-control" readonly></td>

        <!-- ✅ الخصم الإضافي -->
        <td><input name="products[0][extra_discount]" type="text" class="form-control" readonly></td>

        <!-- ✅ اختيار نوع الضريبة -->
        <td>
            @php
                $taxes = \App\Models\Taxe::all();
            @endphp

            <select name="products[0][tax_id]" class="form-control tax-select" onchange="onTaxChange(this)">
                <option value="">-- اختر الضريبة --</option>
                @foreach($taxes as $tax)
                    <option value="{{ $tax->id }}" data-amount="{{ $tax->amount }}">
                        {{ $tax->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <!-- ✅ قيمة الضريبة -->
        <td><input name="products[0][tax]" type="text" class="form-control tax-value-input" readonly></td>

        <!-- ✅ السعر شامل الضريبة -->
        <td><input name="products[0][price_incl_tax]" type="text" class="form-control" readonly></td>

        <!-- ✅ الإجمالي -->
        <td><input name="products[0][row_total]" type="text" class="form-control" readonly></td>

        <!-- ✅ زر الحذف -->
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button>
        </td>
    </tr>
</tbody>
</table>
          </div>
          <div class="text-end mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addRow()">أضف صف</button>
          </div>
        </div>

        {{-- خصم الفاتورة --}}
        <div class="invoice-discount mb-4">
          <select id="invoice_discount_type" class="form-control" onchange="updateSummary()">
            <option value="percent">خصم نسبة مئوية</option>
            <option value="fixed">خصم مبلغ ثابت</option>
          </select>
          <input id="invoice_discount_value" type="number" class="form-control" step="0.01" min="0" value="0" onchange="updateSummary()">
        </div>

        {{-- ملخص الفاتورة --}}
        <div class="row">
          <div class="col-lg-4 offset-lg-8">
            <div class="summary-card">
              <h4>ملخص الفاتورة</h4>
              <div class="summary-row"><span>قبل الخصم:</span><span id="subtotal">0.00</span></div>
              <div class="summary-row"><span>خصومات المنتجات:</span><span id="productDiscount">0.00</span></div>
              <div class="summary-row"><span>بعد خصم المنتجات:</span><span id="grandTotal">0.00</span></div>
              <div class="summary-row"><span>خصم الفاتورة:</span><span id="invoiceDiscountDisplay">0.00</span></div>
              <div class="summary-row"><span>ضريبة بعد الخصم:</span><span id="totalTax">0.00</span></div>
              <div class="summary-row final-total"><span>المجموع النهائي:</span><span id="finalTotal">0.00</span></div>

              <!-- زر حفظ وتنفيذ: submit ويحمل action=12 -->
              <button type="submit"  class="btn btn-primary w-100 mt-3">
                حفظ وتنفيذ
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </form>
</div>
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('admin.customer.store') }}" method="POST" id="addClientForm">
            @csrf
            <div class="modal-content shadow">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" style="color:white;" id="addClientModalLabel">
                     إضافة عميل جديد
                    </h5>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="ادخل اسم العميل" value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">رقم الجوال <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" name="mobile" class="form-control" placeholder="مثال: 05xxxxxxxx" value="{{ old('mobile') }}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="example@email.com" value="{{ old('email') }}">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary">
                     حفظ العميل
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
let isDirty = false;

function updateTime() {
  $('#current-time').text(
    new Date().toLocaleString('ar-EG', {
      hour: '2-digit', minute: '2-digit', second: '2-digit',
      day: '2-digit', month: '2-digit', year: 'numeric'
    })
  );
}

function showSupplierDetails(sel) {
  if (!sel.value) return $('#supplier-details').hide();
  const o = sel.selectedOptions[0];
  $('#sup-name').text(o.dataset.name);
  $('#sup-phone').text(o.dataset.phone);
  $('#sup-email').text(o.dataset.email);
  $('#sup-history').text(o.dataset.history);
  $('#sup-tax').text(o.dataset.tax);
  $('#sup-address').text(o.dataset.address);
  $('#supplier-details').show();
}

function setProductData(sel) {
  const o = sel.selectedOptions[0], row = $(sel).closest('tr');
  row.data({
    uv: +o.dataset.unitValue || 1,
    sp: +o.dataset.sellingPrice || 0,
    disc: +o.dataset.discount || 0,
    type: o.dataset.discountType || 'percent',
  });

  row.find('.product-code').text(o.dataset.code);
  const basePrice = row.data('sp') / row.data('uv');
  row.find('.price-input').val(basePrice.toFixed(2)).removeClass('is-invalid');
  row.find('.warning-text').hide();

  calculateRowTotal(sel);
}

function onUnitChange(sel) {
  const row = $(sel).closest('tr');
  const { uv, sp } = row.data();
  const isLargeUnit = +$(sel).val() === 1;
  const newPrice = isLargeUnit ? sp : sp / uv;

  row.find('.price-input').val(newPrice.toFixed(2)).removeClass('is-invalid');
  row.find('.warning-text').hide();
  calculateRowTotal(sel);
}

function onTaxChange(sel) {
  const row = $(sel).closest('tr');
  const option = sel.selectedOptions[0];
  const taxAmount = parseFloat(option?.dataset.amount || 0);
  row.data('taxPct', taxAmount);

  calculateRowTotal(sel);
}

function calculateRowTotal(input) {
  const row = $(input).closest('tr');
  const q = +row.find('[name$="[quantity]"]').val() || 0;
  let p = +row.find('.price-input').val() || 0;

  const { uv = 1, sp = 0, disc = 0, type = 'percent' } = row.data();

  const unitEl = row.find('[name$="[unit]"]');
  const unit = unitEl.length ? +unitEl.val() : 1;
  const minBase = unit === 1 ? sp : sp / uv;

  // تحقق من الحد الأدنى
  if (p < minBase) {
    p = minBase;
    row.find('.price-input').addClass('is-invalid');
    row.find('.warning-text').text(`الحد الأدنى للسعر هو ${minBase.toFixed(2)}`).show();
  } else {
    row.find('.price-input').removeClass('is-invalid');
    row.find('.warning-text').hide();
  }

  const defaultDiscount = type === 'percent' ? (p * disc / 100) : disc;
  const priceAfterDiscount = p - defaultDiscount;

  const taxOption = row.find('.tax-select')[0]?.selectedOptions[0];
  const taxRate = parseFloat(taxOption?.dataset.amount || 0);
  const taxVal = priceAfterDiscount * taxRate / 100;

  const priceInclTax = priceAfterDiscount + taxVal;
  const totalRow = priceInclTax * q;

  row.find('[name$="[default_discount]"]').val(defaultDiscount.toFixed(2));
  row.find('[name$="[tax]"]').val(taxVal.toFixed(2));
  row.find('[name$="[price_incl_tax]"]').val(priceInclTax.toFixed(2));
  row.find('[name$="[row_total]"]').val(totalRow.toFixed(2));

  updateSummary();
}

function updateSummary() {
  let subtotal = 0, productDisc = 0, baseAfterProd = 0;
  const rowsData = [];

  $('#product-rows tr').each(function () {
    const r = $(this);
    const q = +r.find('[name$="[quantity]"]').val() || 0;
    const p = +r.find('.price-input').val() || 0;
    const d = +r.find('[name$="[default_discount]"]').val() || 0;

    const taxOption = r.find('.tax-select')[0]?.selectedOptions[0];
    const taxPct = parseFloat(taxOption?.dataset.amount || 0);

    const base = (p - d) * q;

    subtotal += p * q;
    productDisc += d * q;
    baseAfterProd += base;

    rowsData.push({ row: r, q, p, d, base, taxPct });
  });

  const invType = $('#invoice_discount_type').val();
  const invVal = +$('#invoice_discount_value').val() || 0;

  let invoiceDiscount = invType === 'percent' ? (baseAfterProd * invVal / 100) : invVal;
  invoiceDiscount = Math.min(invoiceDiscount, baseAfterProd);
  $('#extra_discount').val(invoiceDiscount.toFixed(2));

  let totalTax = 0, totalAfterDiscount = 0;

  rowsData.forEach(obj => {
    const share = baseAfterProd ? (obj.base / baseAfterProd) : 0;
    const rowInvDiscount = share * invoiceDiscount;
    const unitInvDisc = obj.q ? rowInvDiscount / obj.q : 0;

    const basePerUnit = (obj.p - obj.d) - unitInvDisc;
    const taxPerUnit = basePerUnit * obj.taxPct / 100;
    const finalPrice = basePerUnit + taxPerUnit;

    obj.row.find('[name$="[extra_discount]"]').val(rowInvDiscount.toFixed(2));
    obj.row.find('[name$="[tax]"]').val(taxPerUnit.toFixed(2));
    obj.row.find('[name$="[price_incl_tax]"]').val(finalPrice.toFixed(2));
    obj.row.find('[name$="[row_total]"]').val((finalPrice * obj.q).toFixed(2));

    totalTax += taxPerUnit * obj.q;
    totalAfterDiscount += basePerUnit * obj.q;
  });

  const grandTotal = totalAfterDiscount + totalTax;

  $('#subtotal').text(subtotal.toFixed(2));
  $('#productDiscount').text(productDisc.toFixed(2));
  $('#grandTotal').text(baseAfterProd.toFixed(2));
  $('#invoiceDiscountDisplay').text(invoiceDiscount.toFixed(2));
  $('#totalTax').text(totalTax.toFixed(2));
  $('#finalTotal').text(grandTotal.toFixed(2));
  $('#order_amount').val(grandTotal.toFixed(2));
}

function addRow() {
  const $tbody = $('#product-rows');
  const $first = $tbody.find('tr').first();
  const newRow = $first.clone();
  const idx = $tbody.find('tr').length;

  newRow.find('select, input').each(function () {
    const $el = $(this);
    const name = $el.attr('name');
    if (name) $el.attr('name', name.replace(/\[\d+\]/, `[${idx}]`));
    $el.val('').removeClass('is-invalid');
  });

  newRow.find('.product-code').text('');
  newRow.find('.warning-text').hide();

  // إعادة تهيئة select2
  newRow.find('select.select2').next('.select2-container').remove();
  $tbody.append(newRow);
  newRow.find('select.select2').select2({ width: '100%' });

  updateSummary();
}

function removeRow(btn) {
  const rows = $('#product-rows tr');
  if (rows.length > 1) {
    $(btn).closest('tr').remove();
    updateSummary();
  }
}
</script>
<script>
    $(document).ready(function() {
        $('#supplier').select2({
            placeholder: "-- اختر العميل --",
            allowClear: true,
            width: '100',
            language: {
                noResults: function() {
                    return "لا توجد نتائج";
                }
            }
        });
    });
</script>

