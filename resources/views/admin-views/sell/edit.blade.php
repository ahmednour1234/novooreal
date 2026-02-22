{{-- resources/views/admin-views/sell/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', "تعديل مسودة فاتورة بيع #{$quotation->id}")

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<style>
  body { background: #eef2f7; }
  .container-main { max-width: 1200px; margin: 30px auto; }
  .card-custom { border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  .card-header-custom { background: #161853; color: #fff; padding: 1rem 1.5rem; font-size: 1.25rem; font-weight: 600; border-top-left-radius: 8px; border-top-right-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
  .current-time { font-size: .9rem; opacity: .8; }
  .filter-form .form-label { font-weight: 500; }
  .table thead { background: #f5f7fa; }
  .table th, .table td { vertical-align: middle !important; }
  .table input, .table select { background: #fff; border: 1px solid #ced4da; border-radius: 4px; height: 38px; padding: .375rem .75rem; font-size: .9rem; }
  .table input:disabled, .table select:disabled { background: #e9ecef; }
  .warning-text { color: #dc3545; font-size: .85rem; display: none; }
  .summary-card { background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
  .summary-row { display: flex; justify-content: space-between; margin-bottom: .5rem; }
  .final-total { border-top: 1px dashed #ced4da; padding-top: .75rem; margin-top: .5rem; font-weight: 700; }
  .invoice-discount { display: flex; gap: .5rem; margin-bottom: 1rem; }
  .invoice-discount select, .invoice-discount input { flex: 1; height: 38px; }
</style>
@endpush

@section('content')
@php
  $subtotal        = $quotation->details->sum(fn($d) => $d->price * $d->quantity);
  $productDiscount = $quotation->details->sum(fn($d) => $d->discount_on_product * $d->quantity);
@endphp

<div class="container-main">
  <h2 class="mb-4">تعديل مسودة فاتورة بيع #{{ $quotation->id }}</h2>

  <form id="quotation-form" action="{{ route('admin.sells.update', $quotation->id) }}" method="POST">
    @csrf @method('PUT')
    <input type="hidden" name="type" value="{{ $quotation->type }}">
    <input type="hidden" name="order_amount" id="order_amount" value="{{ $quotation->order_amount }}">
    <input type="hidden" name="cash" value="{{ $quotation->cash }}">
    <input type="hidden" name="extra_discount" id="extra_discount" value="{{ $quotation->extra_discount }}">

    <div class="card card-custom mb-4">
      <div class="card-header-custom">
        <span>تعديل فاتورة بيع</span>
        <span class="current-time" id="current-time"></span>
      </div>
      <div class="card-body p-4">
        {{-- اختيار العميل --}}
        <div class="row filter-form mb-3">
          <div class="col-md-5">
            <label class="form-label">اختر العميل:</label>
            <select id="supplier" name="customer_id" class="form-control select2" onchange="showSupplierDetails(this)">
              <option value="">-- اختر العميل --</option>
              @foreach($customers as $c)
                <option
                  value="{{ $c->id }}"
                  @if($c->id == $quotation->user_id) selected @endif
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
          </div>
          <div class="col-md-3">
            <label class="form-label">تاريخ الفاتورة:</label>
            <input type="date" name="date" class="form-control" value="{{ $quotation->date }}">
          </div>
          <div class="col-md-4 text-end">
            <button type="submit" name="action" value="0" class="btn btn-outline-primary mt-2">حفظ كمسودة</button>
          </div>
        </div>

        {{-- تفاصيل العميل --}}
        <div id="supplier-details" class="card mb-4" style="display: none;">
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

        {{-- خطوط المنتجات --}}
        <div class="product-section mb-4">
          <h5 class="mb-3">منتجات الفاتورة</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>المنتج</th><th>الكود</th><th>الوحدة</th><th>الكمية</th>
                  <th>السعر</th><th>خصم منتج</th><th>خصم إضافي</th>
                  <th>الضريبة</th><th>شامل</th><th>الإجمالي</th><th></th>
                </tr>
              </thead>
              <tbody id="product-rows">
                @foreach($quotation->details as $i => $d)
                @php
                  $baseAfterProd = $d->price - $d->discount_on_product;
                  $incl = $baseAfterProd + $d->tax_amount;
                  $rowTotal = $incl * $d->quantity;
                @endphp
                <tr class="product-row">
                  <td>
                    <select name="products[{{ $i }}][id]" class="form-control product-select select2">
                      <option value="">-- اختر --</option>
                      @foreach($products as $p)
                        <option
                          value="{{ $p->id }}"
                          @if($p->id == $d->product_id) selected @endif
                          data-code="{{ $p->product_code }}"
                          data-unit-value="{{ $p->unit_value }}"
                          data-selling-price="{{ $p->selling_price }}"
                          data-discount="{{ $p->discount }}"
                          data-discount-type="{{ $p->discount_type }}"
                          data-tax="{{ $p->taxe->amount ?? 0 }}">
                          {{ $p->name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td><span class="product-code">{{ $d->product->product_code }}</span></td>
                  <td>
                    <select name="products[{{ $i }}][unit]" class="form-control unit-select">
                      <option value="0" @if($d->unit==0) selected @endif>صغرى</option>
                      <option value="1" @if($d->unit==1) selected @endif>كبرى</option>
                    </select>
                  </td>
                  <td><input name="products[{{ $i }}][quantity]" type="number" class="form-control qty-input" min="1" value="{{ $d->quantity }}"></td>
                  <td>
                    <input name="products[{{ $i }}][price]" type="number" class="form-control price-input" step="0.01" min="0" value="{{ $d->price }}">
                    <span class="warning-text"></span>
                  </td>
                  <td><input name="products[{{ $i }}][default_discount]" type="text" class="form-control default-discount-input" readonly value="{{ $d->discount_on_product }}"></td>
                  <td><input name="products[{{ $i }}][extra_discount]" type="text" class="form-control extra-discount-input" readonly value="0"></td>
                  <td><input name="products[{{ $i }}][tax]" type="text" class="form-control tax-input" readonly value="{{ $d->tax_amount }}"></td>
                  <td><input name="products[{{ $i }}][price_incl_tax]" type="text" class="form-control incl-input" readonly value="{{ number_format($incl,2,'.','') }}"></td>
                  <td><input name="products[{{ $i }}][row_total]" type="text" class="form-control total-input" readonly value="{{ number_format($rowTotal,2,'.','') }}"></td>
                  <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-btn">×</button></td>
                </tr>
                @endforeach

                {{-- hidden template --}}
                <tr id="product-row-template" style="display: none;">
                  <td>
                    <select name="products[__INDEX__][id]" class="form-control product-select select2">
                      <option value="">-- اختر --</option>
                      @foreach($products as $p)
                        <option
                          value="{{ $p->id }}"
                          data-code="{{ $p->product_code }}"
                          data-unit-value="{{ $p->unit_value }}"
                          data-selling-price="{{ $p->selling_price }}"
                          data-discount="{{ $p->discount }}"
                          data-discount-type="{{ $p->discount_type }}"
                          data-tax="{{ $p->taxe->amount ?? 0 }}">
                          {{ $p->name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td><span class="product-code"></span></td>
                  <td>
                    <select name="products[__INDEX__][unit]" class="form-control unit-select">
                      <option value="0">صغرى</option>
                      <option value="1">كبرى</option>
                    </select>
                  </td>
                  <td><input name="products[__INDEX__][quantity]" type="number" class="form-control qty-input" min="1" value="1"></td>
                  <td>
                    <input name="products[__INDEX__][price]" type="number" class="form-control price-input" step="0.01" min="0">
                    <span class="warning-text"></span>
                  </td>
                  <td><input name="products[__INDEX__][default_discount]" type="text" class="form-control default-discount-input" readonly value="0"></td>
                  <td><input name="products[__INDEX__][extra_discount]" type="text" class="form-control extra-discount-input" readonly value="0"></td>
                  <td><input name="products[__INDEX__][tax]" type="text" class="form-control tax-input" readonly value="0"></td>
                  <td><input name="products[__INDEX__][price_incl_tax]" type="text" class="form-control incl-input" readonly value="0"></td>
                  <td><input name="products[__INDEX__][row_total]" type="text" class="form-control total-input" readonly value="0"></td>
                  <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-btn">×</button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="text-end mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm add-btn">أضف صف</button>
          </div>
        </div>

        {{-- خصم الفاتورة --}}
        <div class="invoice-discount mb-4">
          <select id="invoice_discount_type" class="form-control">
            <option value="percent" @if($quotation->extra_discount_percentage) selected @endif>خصم نسبة مئوية</option>
            <option value="fixed"   @if(!$quotation->extra_discount_percentage) selected @endif>خصم مبلغ ثابت</option>
          </select>
          <input id="invoice_discount_value" type="number" class="form-control" step="0.01" min="0" value="{{ $quotation->extra_discount }}">
        </div>

        {{-- ملخص الفاتورة --}}
        <div class="row">
          <div class="col-lg-4 offset-lg-8">
            <div class="summary-card">
              <h4>ملخص الفاتورة</h4>
              <div class="summary-row"><span>قبل الخصم:</span><span id="subtotal">{{ number_format($subtotal,2) }}</span></div>
              <div class="summary-row"><span>خصومات المنتجات:</span><span id="productDiscount">{{ number_format($productDiscount,2) }}</span></div>
              <div class="summary-row"><span>بعد خصم المنتجات:</span><span id="grandSubtotal">{{ number_format($subtotal - $productDiscount,2) }}</span></div>
              <div class="summary-row"><span>خصم الفاتورة:</span><span id="invoiceDiscountDisplay">{{ number_format($quotation->extra_discount,2) }}</span></div>
              <div class="summary-row"><span>ضريبة بعد الخصم:</span><span id="totalTax">{{ number_format($quotation->total_tax,2) }}</span></div>
              <div class="summary-row final-total"><span>المجموع النهائي:</span><span id="finalTotal">{{ number_format($quotation->order_amount,2) }}</span></div>
              <button type="submit" name="action" value="12" class="btn btn-primary w-100 mt-3">حفظ وتنفيذ</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </form>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(init);

function init(){
  $('.select2').select2({ width:'100%' });
  updateTime(); setInterval(updateTime,1000);
  bindEvents();
  calculateAll();
}

function updateTime(){
  $('#current-time').text(
    new Date().toLocaleString('ar-EG',{
      hour:'2-digit', minute:'2-digit', second:'2-digit',
      day:'2-digit', month:'2-digit', year:'numeric'
    })
  );
}

function showSupplierDetails(sel){
  if(!sel.value){ $('#supplier-details').hide(); return; }
  const o = sel.selectedOptions[0];
  $('#sup-name').text(o.dataset.name);
  $('#sup-phone').text(o.dataset.phone);
  $('#sup-email').text(o.dataset.email);
  $('#sup-history').text(o.dataset.history);
  $('#sup-tax').text(o.dataset.tax);
  $('#sup-address').text(o.dataset.address);
  $('#supplier-details').show();
}

function bindEvents(){
  // when product changes
  $('#product-rows').on('change', '.product-select', function(){
    handleProductChange($(this).closest('tr'));
  });
  // when unit, qty, price change
  $('#product-rows').on('change keyup', '.unit-select, .qty-input, .price-input', function(){
    calculateRow($(this).closest('tr'));
  });
  // remove row
  $('#product-rows').on('click', '.remove-btn', function(){
    if($('#product-rows tr:visible').length > 1) {
      $(this).closest('tr').remove();
      calculateAll();
    }
  });
  // invoice-level discount
  $('#invoice_discount_type, #invoice_discount_value').on('change keyup', calculateAll);
  // add new row
  $('.add-btn').on('click', addRow);
}

function handleProductChange(row){
  const sel = row.find('.product-select')[0];
  if(!sel.value) return;
  // enable & clear errors
  row.find('select, input').prop('disabled', false).removeClass('is-invalid');
  row.find('.warning-text').hide();
  // store row data
  const o = sel.selectedOptions[0];
  row.data({
    uv: +o.dataset.unitValue,
    sp: +o.dataset.sellingPrice,
    disc: +o.dataset.discount,
    discType: o.dataset.discountType,
    taxPct: +o.dataset.tax
  });
  // show code
  row.find('.product-code').text(o.dataset.code);
  // reset unit, qty, price
  row.find('.unit-select').val(0);
  row.find('.qty-input').val(1);
  row.find('.price-input').val((row.data('sp')/row.data('uv')).toFixed(2));
  // clear discounts/taxes/totals
  row.find('.default-discount-input, .extra-discount-input, .tax-input, .incl-input, .total-input')
     .val('0');
  calculateAll();
}

function calculateRow(row){
  const q = +row.find('.qty-input').val() || 0;
  let p   = +row.find('.price-input').val() || 0;
  const uv       = row.data('uv') || 1;
  const sp       = row.data('sp') || 0;
  const disc     = row.data('disc') || 0;
  const discType = row.data('discType') || 'fixed';
  const taxPct   = row.data('taxPct') || 0;

  // enforce min price
  const unit    = +row.find('.unit-select').val();
  const minPrice = unit===1 ? sp : (sp/uv);
  if(p < minPrice){
    row.find('.price-input').addClass('is-invalid');
    row.find('.warning-text').show().text(`الحد الأدنى للسعر هو ${minPrice.toFixed(2)}`);
    p = minPrice;
  } else {
    row.find('.price-input').removeClass('is-invalid');
    row.find('.warning-text').hide();
  }

  // calculate default discount
  const defDisc = discType==='percent' ? p*disc/100 : disc;
  row.find('.default-discount-input').val(defDisc.toFixed(2));

  // compute all invoice-level discount
  const baseAll = getBaseAfterProdAll();
  const invType = $('#invoice_discount_type').val();
  const invVal  = +$('#invoice_discount_value').val() || 0;
  let invDisc   = invType==='percent' ? baseAll*invVal/100 : invVal;
  invDisc = Math.min(invDisc, baseAll);
  $('#extra_discount').val(invDisc.toFixed(2));

  // share per-row
  const baseRow = (p - defDisc)*q;
  const share   = baseAll ? (baseRow/baseAll) : 0;
  const rowInv  = share * invDisc;
  const extraPerUnit = rowInv/q || 0;
  row.find('.extra-discount-input').val(extraPerUnit.toFixed(2));

  // tax & totals
  const taxable   = p - defDisc - extraPerUnit;
  const taxPerUnit= taxable * taxPct/100;
  const incl      = taxable + taxPerUnit;
  row.find('.tax-input').val(taxPerUnit.toFixed(2));
  row.find('.incl-input').val(incl.toFixed(2));
  row.find('.total-input').val((incl*q).toFixed(2));

  calculateAll();
}

function getBaseAfterProdAll(){
  let sum = 0;
  $('#product-rows tr').each(function(){
    const r = $(this);
    const q = +r.find('.qty-input').val() || 0;
    const p = +r.find('.price-input').val() || 0;
    const d = +r.find('.default-discount-input').val() || 0;
    sum += (p - d)*q;
  });
  return sum;
}

function calculateAll(){
  // subtotal & prod discounts
  let sub=0, prodD=0;
  $('#product-rows tr').each(function(){
    const r = $(this);
    const q = +r.find('.qty-input').val() || 0;
    const p = +r.find('.price-input').val() || 0;
    const d = +r.find('.default-discount-input').val() || 0;
    sub += p*q;
    prodD += d*q;
  });
  const grand = sub - prodD;
  $('#subtotal').text(sub.toFixed(2));
  $('#productDiscount').text(prodD.toFixed(2));
  $('#grandSubtotal').text(grand.toFixed(2));

  // invoice discount display
  $('#invoiceDiscountDisplay').text((+$('#extra_discount').val()||0).toFixed(2));

  // total tax & final total
  let totalTax=0, finalTotal=0;
  $('#product-rows tr').each(function(){
    const r = $(this);
    const q = +r.find('.qty-input').val()||0;
    const tax= +r.find('.tax-input').val()||0;
    const tot= +r.find('.total-input').val()||0;
    totalTax += tax*q;
    finalTotal += tot;
  });
  $('#totalTax').text(totalTax.toFixed(2));
  $('#finalTotal').text(finalTotal.toFixed(2));
  $('#order_amount').val(finalTotal.toFixed(2));
}

function addRow(){
  const idx = $('#product-rows tr').length;
  const $tpl = $('#product-row-template').clone()
                 .removeAttr('id').show();
  $tpl.find('select, input').each(function(){
    const $el = $(this), nm = $el.attr('name');
    if(nm){
      $el.attr('name', nm.replace('__INDEX__', idx));
      // clear values
      if(!$el.is('[readonly]')) $el.val('');
    }
  });
  $('#product-rows').append($tpl);
  $tpl.find('.select2').select2({ width:'100%' });
}
</script>
