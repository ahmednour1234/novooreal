@extends('layouts.admin.app')

@section('content')
<style>
  /* تصميم بسيط ونضيف */
  body{ background:#f6f8fb; }
  .container{ max-width: 1100px; }

  .card{
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,.05);
    overflow: hidden;
    background:#fff;
  }
  .card-header{ border-bottom: none; }
  .breadcrumb{ border:1px solid #e5e7eb; }

  .form-control{ border-radius:10px; height:44px; }
  textarea.form-control{ height:auto; min-height:110px; }

  .table thead th{
    position: sticky; top: 0; z-index: 2;
    background:#f8f9fa !important; font-weight:700;
  }
  .table tbody tr:hover{ background:#fafafa; }

  .sticky-actions{
    position: sticky; bottom:-1px; z-index: 5;
    background:#fff; border-top:1px solid #e5e7eb;
    padding:12px; display:flex; gap:10px; justify-content:flex-end;
  }
</style>

<div class="content container-fluid">
  {{-- Breadcrumb --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="#" class="text-primary">{{ \App\CPU\translate('مرتجع شراء') }}</a>
        </li>
      </ol>
    </nav>
  </div>

  {{-- ====== الخطوة 1: إدخال رقم الفاتورة ====== --}}
  @if(!session()->has('orderDetails'))
    <div class="row justify-content-center mt-5 pt-4">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card">
          <div class="card-header bg-secondary text-white">
            بدء عملية مرتجع الشراء
          </div>
          <div class="card-body">
            <form id="returnForm" method="POST" action="{{ route('admin.purchase_invoice.processReturn') }}">
              @csrf
              <label for="invoice_number" class="form-label font-weight-bold mb-2">رقم الفاتورة</label>
              <div class="input-group shadow-sm">
                <input type="text" class="form-control" name="invoice_number" id="invoice_number" required autofocus placeholder="اكتب رقم الفاتورة...">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary px-4">اعرض المنتجات</button>
                </div>
              </div>
              <small class="text-muted d-block mt-2">أدخل رقم الفاتورة المراد عمل مرتجع عليها ثم اضغط "اعرض المنتجات".</small>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ====== الخطوة 2: تفاصيل الفاتورة والمرتجع ====== --}}
  @if(session()->has('orderDetails'))
    @php
      $total_discount = 0;
      foreach(session('orderDetails.order_products') as $product) {
          $total_discount += $product->discount_on_product * $product->quantity;
      }
      $extraDiscount = session('extra_discount') ?? 0;
      $total_tax     = session('total_tax') ?? 0;
      $orderAmount   = (session('order_amount') ?? 0) + $extraDiscount + $total_discount - $total_tax;
      $orderAmount   = $orderAmount > 0 ? $orderAmount : 1;
      $discountRatio = ($extraDiscount / $orderAmount) * 100;

      $stockBatches  = collect(session('orderDetails.stock_batches') ?? []);
      $netInvoice    = (session('order_amount') ?? 0);
    @endphp

    {{-- بيانات المورد والفاتورة --}}
    <div class="card mt-4">
      <div class="card-header bg-secondary text-white">بيانات المورد والفاتورة</div>
      <div class="card-body">
        <div class="row gy-2">
          <div class="col-sm-6 col-lg-4"><strong>اسم المورد:</strong> {{ session('name') }}</div>
          <div class="col-sm-6 col-lg-4"><strong>الهاتف:</strong> {{ session('mobile') }}</div>
          <div class="col-sm-6 col-lg-4"><strong>السجل التجاري:</strong> {{ session('c_history') }}</div>
          <div class="col-sm-6 col-lg-4"><strong>الرقم الضريبي:</strong> {{ session('tax_number') }}</div>
          <div class="col-sm-6 col-lg-4"><strong>كاتب الفاتورة:</strong> {{ session('seller') }}</div>
          <div class="col-sm-6 col-lg-4"><strong>تاريخ الفاتورة:</strong> {{ session('created_at') }}</div>
          <div class="col-sm-6 col-lg-4"><strong>الفرع:</strong> {{ session('branch') }}</div>
        </div>
      </div>
    </div>

    {{-- ملخص الفاتورة --}}
    <div class="card mt-3">
      <div class="card-header bg-secondary text-white">ملخص الفاتورة</div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-7">
            <table class="table table-sm mb-0">
              <tbody>
                <tr>
                  <th class="w-50">الإجمالي قبل الخصم الإضافي</th>
                  <td>{{ number_format((session('order_amount') ?? 0) + $total_discount + $extraDiscount - $total_tax, 2) }}</td>
                </tr>
                <tr>
                  <th>خصم المنتجات الإجمالي</th>
                  <td>{{ number_format($total_discount, 2) }}</td>
                </tr>
                <tr>
                  <th>الخصم الإضافي</th>
                  <td>{{ number_format($extraDiscount, 2) }}</td>
                </tr>
                <tr>
                  <th>الضريبة</th>
                  <td>{{ number_format($total_tax, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-5 d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <div class="display-4 text-success font-weight-bold mb-0">
              {{ number_format($netInvoice, 2) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- تفاصيل المرتجع + ملاحظة ومرفق --}}
    <div class="card mt-4">
      <div class="card-header bg-secondary text-white">
        تفاصيل الفاتورة رقم: {{ session('orderDetails.order_id') }}
      </div>

      <div class="card-body p-0">
        <form id="returnInvoiceForm" method="POST" action="{{ route('admin.purchase_invoice.processConfirmedReturn') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="order_id" value="{{ session('orderDetails.order_id') }}">

          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <thead>
                <tr>
                  <th>المنتج</th>
                  <th>سعر الوحدة</th>
                  <th>خصم الوحدة</th>
                  <th>الخصم الإضافي/وحدة</th>
                  <th>الضريبة/وحدة</th>
                  <th>السعر النهائي/وحدة</th>
                  <th>الرصيد الحالي</th>
                  <th>الكمية بالفاتورة</th>
                  <th>إجمالي الصنف</th>
                  <th>الوحدة</th>
                  <th style="min-width:140px">كمية المرتجع</th>
                </tr>
              </thead>
              <tbody>
                @foreach(session('orderDetails.order_products') as $product)
                  @php
                    $discountToEachProduct = $product->price * ($discountRatio / 100);
                    $finalUnitPrice        = $product->price - $product->discount_on_product - $discountToEachProduct + $product->tax_amount;
                    $productFinalTotal     = $finalUnitPrice * $product->quantity;
                    $productDetails        = json_decode($product->product_details);
                    $unitValue             = $productDetails->unit_value ?? 1;
                    $computedPurchasePrice = $product->product->purchase_price;

                    $currentPrice = $product->unit == 0 ? ($product->price * $unitValue) : $product->price;

                    $currentStock = 0;
                    $batches = collect(session('orderDetails.stock_batches') ?? []);
                    if($batches->isNotEmpty()){
                        foreach ($batches as $batch) {
                            if ($batch->product_id == $product->product_id && $batch->product_code == $product->product_code) {
                                $currentStock += $batch->quantity;
                            }
                        }
                    }
                    if(floor($currentStock) != $currentStock) {
                        $currentStock = $currentStock * $unitValue;
                    }

                    $rowClass = ($computedPurchasePrice > $currentPrice) ? 'table-danger' : '';
                  @endphp

                  <tr class="{{ $rowClass }}" data-purchase-price="{{ $computedPurchasePrice }}" data-current-price="{{ $currentPrice }}">
                    <td class="font-weight-bold">
                      {{ $product->product->name }}
                      @if($rowClass)
                        <div class="small text-danger mt-1">⚠️ سعر الشراء الحالي أعلى من سعر المرتجع</div>
                      @endif
                    </td>

                    <td data-base-price="{{ $product->price }}">{{ number_format($product->price, 2) }}</td>
                    <td data-discount="{{ $product->discount_on_product }}">{{ number_format($product->discount_on_product, 2) }}</td>
                    <td data-extra-discount="{{ ($discountRatio/100) * $product->price }}">{{ number_format(($discountRatio/100) * $product->price, 2) }}</td>
                    <td data-tax="{{ $product->tax_amount }}">{{ number_format($product->tax_amount, 2) }}</td>
                    <td data-final-unit="{{ $finalUnitPrice }}">{{ number_format($finalUnitPrice, 2) }}</td>

                    <td>{{ number_format($currentStock, 2) }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ number_format($productFinalTotal, 2) }}</td>

                    <td style="min-width:120px">
                      @if($product->unit == 1)
                        <select name="return_unit[{{ $product->product_id }}]"
                                id="return_unit_{{ $product->product_id }}"
                                class="form-control"
                                onchange="updateHiddenUnit(this, '{{ $product->product_id }}'); setReturnQuantityMax(this, '{{ $product->product_id }}', {{ $unitValue }}, {{ $product->quantity }})">
                          <option value="1" selected>كبري</option>
                          <option value="0">صغري</option>
                        </select>
                        <input type="hidden" name="return_unit_hidden[{{ $product->product_id }}]" id="hidden_return_unit_{{ $product->product_id }}" value="1">
                      @else
                        <input type="hidden" name="return_unit[{{ $product->product_id }}]" value="{{ $product->unit }}">
                        <input type="hidden" name="return_unit_hidden[{{ $product->product_id }}]" value="{{ $product->unit }}">
                        <span class="text-muted">صغري</span>
                      @endif
                    </td>

                    <td>
                      <input type="number"
                             id="return_quantity_{{ $product->product_id }}"
                             name="return_quantities[{{ $product->product_id }}]"
                             class="form-control return-qty"
                             value="0" min="0" step="1"
                             data-final-unit-price="{{ $finalUnitPrice }}"
                             data-unit-value="{{ $unitValue }}"
                             data-return-unit="1"
                             oninput="updateHiddenQuantity(this); validateReturnQuantity(this); updateReturnSummary();"
                             onchange="updateReturnSummary();">
                      <input type="hidden"
                             name="return_quantities_hidden[{{ $product->product_id }}]"
                             id="hidden_return_quantity_{{ $product->product_id }}"
                             value="0">
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- ملاحظة ومرفق (اختياري) --}}
          <div class="p-3">
            <div class="row">
              <div class="col-md-8">
                <label for="return_note" class="form-label">ملاحظة (اختياري)</label>
                <textarea id="return_note" name="return_note" class="form-control" placeholder="اكتب أي ملاحظات بخصوص المرتجع..."></textarea>
              </div>
              <div class="col-md-4">
                <label for="return_attachment" class="form-label">إرفاق صورة (اختياري)</label>
                <input type="file" id="return_attachment" name="return_attachment" class="form-control" accept="image/*">
                <small class="text-muted d-block mt-1">يسمح بصور PNG/JPG.</small>
              </div>
            </div>
          </div>

          {{-- أزرار التنفيذ --}}
          <div class="sticky-actions">
            <button type="button" id="confirmBtn" class="btn btn-primary" onclick="confirmForm()">تمام</button>
            <button type="button" id="undoBtn" class="btn btn-danger" onclick="undoConfirm()" style="display:none;">تراجع</button>
            <button type="submit" id="submitReturn" class="btn btn-primary" disabled>تنفيذ المرتجع</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ملخص المرتجع --}}
    <div class="card mt-4" id="returnSummaryCard">
      <div class="card-header bg-secondary text-white">ملخص المرتجع</div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-7">
            <table class="table table-sm mb-0">
              <tbody>
                <tr>
                  <th class="w-50">إجمالي أسعار المرتجع</th>
                  <td id="returnTotalProduct">0.00</td>
                </tr>
                <tr>
                  <th>إجمالي خصم على المرتجع</th>
                  <td id="returnTotalDiscount">0.00</td>
                </tr>
                <tr>
                  <th>إجمالي الخصم الإضافي</th>
                  <td id="returnTotalExtraDiscount">0.00</td>
                </tr>
                <tr>
                  <th>إجمالي الضريبة على المرتجع</th>
                  <td id="returnTotalTax">0.00</td>
                </tr>
                <tr>
                  <th>الإجمالي النهائي للمرتجع</th>
                  <td id="returnTotalOverall">0.00</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-5 d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <div class="text-muted">تحقق من الأرقام قبل التنفيذ.</div>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>

{{-- تحذير اختلاف السعر --}}
<div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="warningModalLabel">تحذير</h5>
      </div>
      <div class="modal-body">
        سعر الشراء أصبح أكبر من سعر المنتج الذي سيتم إرجاعه. يُفضل مراجعة الأسعار قبل المتابعة.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">حسناً</button>
      </div>
    </div>
  </div>
</div>
@endsection

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
  // مزامنة الحقول المخفية
  function updateHiddenUnit(selectElem, productId) {
    document.getElementById('hidden_return_unit_' + productId).value = selectElem.value;
  }
  function updateHiddenQuantity(inputElem) {
    var productId = inputElem.name.match(/\[(.*?)\]/)[1];
    document.getElementById('hidden_return_quantity_' + productId).value = inputElem.value;
  }

  // أقصى كمية حسب الوحدة
  function setReturnQuantityMax(selectElem, productId, unitValue, originalQuantity) {
    var qtyInput = document.getElementById("return_quantity_" + productId);
    if (selectElem.value == "0") {
      qtyInput.dataset.returnUnit = "0"; // صغرى
      var newMax = originalQuantity * unitValue;
      qtyInput.setAttribute('max', newMax);
      if (parseFloat(qtyInput.value) > newMax) {
        qtyInput.value = newMax;
        updateHiddenQuantity(qtyInput);
      }
    } else {
      qtyInput.dataset.returnUnit = "1"; // كبرى
      qtyInput.setAttribute('max', originalQuantity);
      if (parseFloat(qtyInput.value) > originalQuantity) {
        qtyInput.value = originalQuantity;
      }
      updateHiddenQuantity(qtyInput);
    }
    updateReturnSummary();
  }

  function validateReturnQuantity(inputElem) {
    var maxAllowed = parseFloat(inputElem.getAttribute('max'));
    var val = parseFloat(inputElem.value) || 0;
    if (val > maxAllowed) {
      inputElem.value = maxAllowed;
      updateHiddenQuantity(inputElem);
    }
  }

  // ملخص المرتجع
  function updateReturnSummary() {
    var totalProduct = 0, totalDiscount = 0, totalExtraDiscount = 0, totalTax = 0, totalOverall = 0;
    var inputs = document.querySelectorAll('.return-qty');

    inputs.forEach(function(input) {
      var qty = parseFloat(input.value) || 0;
      var unitValue = parseFloat(input.getAttribute('data-unit-value')) || 1;
      var row = input.closest('tr');

      var productPrice = parseFloat(row.querySelector('[data-base-price]').getAttribute('data-base-price')) || 0;
      var discountPerUnit = parseFloat(row.querySelector('[data-discount]').getAttribute('data-discount')) || 0;
      var extraDiscountPerUnit = parseFloat(row.querySelector('[data-extra-discount]').getAttribute('data-extra-discount')) || 0;
      var taxPerUnit = parseFloat(row.querySelector('[data-tax]').getAttribute('data-tax')) || 0;

      var returnUnit = input.dataset.returnUnit; // "1": كبري, "0": صغري
      if (returnUnit === "0") {
        productPrice          = productPrice / unitValue;
        discountPerUnit       = discountPerUnit / unitValue;
        extraDiscountPerUnit  = extraDiscountPerUnit / unitValue;
        taxPerUnit            = taxPerUnit / unitValue;
      }

      var effectiveFinalUnit = productPrice - discountPerUnit - extraDiscountPerUnit + taxPerUnit;

      totalProduct       += qty * productPrice;
      totalDiscount      += qty * discountPerUnit;
      totalExtraDiscount += qty * extraDiscountPerUnit;
      totalTax           += qty * taxPerUnit;
      totalOverall       += qty * effectiveFinalUnit;
    });

    $('#returnTotalProduct').text(totalProduct.toFixed(2));
    $('#returnTotalDiscount').text(totalDiscount.toFixed(2));
    $('#returnTotalExtraDiscount').text(totalExtraDiscount.toFixed(2));
    $('#returnTotalTax').text(totalTax.toFixed(2));
    $('#returnTotalOverall').text(totalOverall.toFixed(2));
  }

  // تأكيد/تراجع
  function confirmForm() {
    var formElements = document.querySelectorAll('#returnInvoiceForm input[type="number"], #returnInvoiceForm select');
    formElements.forEach(function(elem) { elem.disabled = true; });
    $('#confirmBtn').hide();
    $('#undoBtn').show();
    $('#submitReturn').prop('disabled', false);
  }
  function undoConfirm() {
    var formElements = document.querySelectorAll('#returnInvoiceForm input[type="number"], #returnInvoiceForm select');
    formElements.forEach(function(elem) { elem.disabled = false; });
    $('#undoBtn').hide();
    $('#confirmBtn').show();
    $('#submitReturn').prop('disabled', true);
  }

  // إرسال Ajax (يدعم المرفقات)
  $(document).ready(function() {
    updateReturnSummary();

    $('#returnInvoiceForm').on('submit', function(e) {
      e.preventDefault();
      var formEl = this;
      var fd = new FormData(formEl);
      $.ajax({
        url: $(formEl).attr('action'),
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(response) {
          if(window.toastr){
            toastr.success(response.message || 'تم تنفيذ المرتجع بنجاح!');
          } else {
            alert(response.message || 'تم تنفيذ المرتجع بنجاح!');
          }
          // location.reload(); // إن حبيت تحدث الصفحة بعد التنفيذ
        },
        error: function(xhr) {
          var errorMessage = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'حدث خطأ أثناء تنفيذ المرتجع!';
          if(window.toastr){
            toastr.error(errorMessage);
          } else {
            alert(errorMessage);
          }
        }
      });
    });

    // تحذير اختلاف السعر
    let warned = false;
    $('tr[data-purchase-price]').each(function(){
      var purchasePrice = parseFloat($(this).data('purchase-price'));
      var currentPrice  = parseFloat($(this).data('current-price'));
      if (!warned && purchasePrice > currentPrice) {
        $('#warningModal').modal('show');
        warned = true;
      }
    });

    // تحديث الملخص مع أي تغيير
    $('select[name^="return_unit"]').on('change', updateReturnSummary);
    $('.return-qty').on('input change', updateReturnSummary);
  });
</script>
