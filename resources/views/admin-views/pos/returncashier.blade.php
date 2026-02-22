@extends('layouts.admin.app')
@section('content')
<div class="content container-fluid">
           <div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white  rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                    <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                </a>
            </li>
        
               <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate('المرتجعات') }}
                </a>
            </li>
        </ol>
    </nav>
</div>
<!-- زر بدء عملية المرتجع -->
  <!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#returnModal">-->
  <!--  بدء عملية مرتجع-->
  <!--</button>-->

  <!-- نافذة إدخال رقم الفاتورة -->
  <!--<div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">-->
  <!--  <div class="modal-dialog" role="document">-->
  <!--    <form id="returnForm" method="POST" action="{{ route('admin.pos.processReturn') }}">-->
  <!--      @csrf-->
  <!--      <div class="modal-content">-->
  <!--        <div class="modal-header bg-info text-white">-->
  <!--          <h5 class="modal-title" id="returnModalLabel">ادخل رقم الفاتورة للمرتجع</h5>-->
  <!--          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">-->
  <!--            <span aria-hidden="true">&times;</span>-->
  <!--          </button>-->
  <!--        </div>-->
  <!--        <div class="modal-body">-->
  <!--          <div class="form-group">-->
  <!--            <label for="invoice_number">رقم الفاتورة</label>-->
  <!--            <input type="text" class="form-control" name="invoice_number" id="invoice_number" required>-->
  <!--          </div>-->
  <!--        </div>-->
  <!--        <div class="modal-footer">-->
  <!--          <button type="submit" class="btn btn-info">اعرض المنتجات</button>-->
  <!--        </div>-->
  <!--      </div>-->
  <!--    </form>-->
  <!--  </div>-->
  <!--</div>-->

  @if(session()->has('orderDetails'))
    @php
      $total_discount = 0;
      foreach(session('orderDetails.order_products') as $product) {
          $total_discount += $product->discount_on_product * $product->quantity; 
      }
      $extraDiscount = session('extra_discount') ?? 0;
      $total_tax = session('total_tax') ?? 0;
      $orderAmount = (session('order_amount') ?? 0) + $extraDiscount + $total_discount - $total_tax;
      $orderAmount = $orderAmount > 0 ? $orderAmount : 1;
      $discountRatio = ($extraDiscount / $orderAmount) * 100;
    @endphp

    <!-- بطاقة بيانات العميل والفاتورة -->
<div class="row g-3 mt-4 align-items-stretch">
  <!-- بطاقة بيانات العميل والفاتورة -->
  <div class="col-12 col-lg-6 d-flex">
    <div class="card w-100 h-100">
      <div class="card-header bg-secondary text-white">بيانات العميل والفاتورة</div>
      <div class="card-body">
        <p><strong>اسم العميل:</strong> {{ session('name') }}</p>
        <p><strong>رقم الهاتف:</strong> {{ session('mobile') }}</p>
        <p><strong>السجل التجاري:</strong> {{ session('c_history') }}</p>
        <p><strong>الرقم الضريبي:</strong> {{ session('tax_number') }}</p>
        <p><strong>مديونية العميل:</strong> {{ session('credit') }}</p>
        <p><strong>اسم كاتب الفاتورة:</strong> {{ session('seller') }}</p>
        <p><strong>تاريخ إنشاء الفاتورة:</strong> {{ session('created_at') }}</p>
      </div>
    </div>
  </div>

  <!-- بطاقة ملخص الفاتورة النهائية -->
  <div class="col-12 col-lg-6 d-flex">
    <div class="card w-100 h-100">
      <div class="card-header bg-secondary text-white">ملخص الفاتورة النهائية</div>
      <div class="card-body">
        <p>
          <strong>الإجمالي قبل الخصم الإضافي:</strong>
          {{ number_format((session('order_amount') ?? 0) + $total_discount + $extraDiscount - $total_tax, 2) }}
        </p>
        <p><strong>خصم المنتجات الإجمالي:</strong> {{ number_format($total_discount, 2) }}</p>
        <p><strong>الخصم الإضافي:</strong> {{ number_format($extraDiscount, 2) }}</p>
        <p><strong>الضريبة:</strong> {{ number_format($total_tax, 2) }}</p>
        @php $netInvoice = (session('order_amount') ?? 0); @endphp
        <p><strong>الصافي:</strong> {{ number_format($netInvoice, 2) }}</p>
      </div>
    </div>
  </div>
</div>


    <!-- بطاقة تفاصيل الفاتورة للمرتجع -->
    <div class="card mt-4">
      <div class="card-header bg-secondary text-white">تفاصيل الفاتورة رقم: {{ session('orderDetails.order_id') }}</div>
      <div class="card-body">
        <!-- فورم المرتجع -->
        <form id="returnInvoiceForm" method="POST" action="{{ route('admin.pos.processConfirmedReturnCashier') }}">
          @csrf
          <input type="hidden" name="order_id" value="{{ session('orderDetails.order_id') }}">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>اسم المنتج</th>
                <th>سعر الشراء / السعر الحالي</th>
                <th>سعر الوحدة (أساسي)</th>
                <th>خصم الوحدة</th>
                <th>الخصم الإضافي للمنتج</th>
                <th>الضريبة</th>
                <th>السعر النهائي للوحدة</th>
                <th>الكمية</th>
                <th>الإجمالي النهائي</th>
                <th>الوحدة</th>
                <th>كمية المرتجع</th>
              </tr>
            </thead>
            <tbody>
              @foreach(session('orderDetails.order_products') as $product)
                @php
                  $discountToEachProduct = $product->price * ($discountRatio / 100);
                  $finalUnitPrice = $product->price - $product->discount_on_product - $discountToEachProduct + $product->tax_amount;
                  $productFinalTotal = $finalUnitPrice * $product->quantity;
                  $productDetails = json_decode($product->product_details);
                  $unitValue = $productDetails->unit_value ?? 1;
                  $computedPurchasePrice = $product->product->purchase_price;
                  // السعر الحالي: إذا كانت الوحدة صغرى يتم ضرب سعر الوحدة في قيمة الوحدة
                  $currentPrice = $product->unit == 0 
                                  ? $product->price * $unitValue 
                                  : $product->price;
                  // تحديد الكلاس التحذيري إذا كان سعر الشراء أكبر من السعر الحالي
                  $rowClass = $computedPurchasePrice > $currentPrice ? 'table-danger' : '';
                @endphp
                <tr class="{{ $rowClass }}" data-purchase-price="{{ $computedPurchasePrice }}" data-current-price="{{ $currentPrice }}">
                  <td>{{ $product->product->name }}</td>
                  <td>
                    <span class="text-primary">شراء: {{ number_format($computedPurchasePrice, 2) }}</span>
                    <br>
                    <span class="text-success">حالي: {{ number_format($currentPrice, 2) }}</span>
                  </td>
                  <td data-base-price="{{ $product->price }}">{{ number_format($product->price, 3) }}</td>
                  <td data-discount="{{ $product->discount_on_product }}">{{ number_format($product->discount_on_product, 3) }}</td>
                  <td data-extra-discount="{{ ($discountRatio/100) * $product->price }}">{{ number_format(($discountRatio/100) * $product->price, 3) }}</td>
                  <td data-tax="{{ $product->tax_amount }}">{{ number_format($product->tax_amount, 3) }}</td>
                  <td data-final-unit="{{ $finalUnitPrice }}">{{ $finalUnitPrice }}</td>
                  <td>{{ $product->quantity }}</td>
                  <td>{{ $productFinalTotal }}</td>
                  <td>
                    @if($product->unit == 1)
                      <!-- إذا الوحدة كبري يمكن تغييرها إلى صغري -->
                      <select name="return_unit[{{ $product->product_id }}]" id="return_unit_{{ $product->product_id }}" class="form-control" 
                              onchange="updateHiddenUnit(this, '{{ $product->product_id }}'); setReturnQuantityMax(this, '{{ $product->product_id }}', {{ $unitValue }}, {{ $product->quantity }})">
                        <option value="1" selected>كبري</option>
                        <option value="0">صغري</option>
                      </select>
                      <!-- حقل مخفي لمزامنة قيمة الوحدة -->
                      <input type="hidden" name="return_unit_hidden[{{ $product->product_id }}]" id="hidden_return_unit_{{ $product->product_id }}" value="1">
                    @else
                      <input type="hidden" name="return_unit[{{ $product->product_id }}]" value="{{ $product->unit }}">
                      <input type="hidden" name="return_unit_hidden[{{ $product->product_id }}]" value="{{ $product->unit }}">
                      <span>صغري</span>
                    @endif
                  </td>
                  <td>
                    <!-- حقل كمية المرتجع مع استخدام product_id في المعرف -->
                    <input type="number" id="return_quantity_{{ $product->product_id }}" 
                           name="return_quantities[{{ $product->product_id }}]" 
                           class="form-control return-qty"
                           value="0" min="0" step="1"
                           data-final-unit-price="{{ $finalUnitPrice }}"
                           data-unit-value="{{ $unitValue }}"
                           data-return-unit="1"
                           oninput="updateHiddenQuantity(this); validateReturnQuantity(this); updateReturnSummary();"
                           onkeyup="updateReturnSummary();"
                           onchange="updateReturnSummary();">
                    <!-- حقل مخفي لمزامنة كمية المرتجع -->
                    <input type="hidden" name="return_quantities_hidden[{{ $product->product_id }}]" id="hidden_return_quantity_{{ $product->product_id }}" value="0">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <!-- أزرار التأكيد والتراجع -->
          <div class="mt-3">
            <button type="button" id="confirmBtn" class="btn btn-warning" onclick="confirmForm()">تمام</button>
            <button type="button" id="undoBtn" class="btn btn-secondary" onclick="undoConfirm()" style="display:none;">تراجع</button>
          </div>
          <div class="mt-3">
            <!-- زر تنفيذ المرتجع -->
            <button type="submit" id="submitReturn" class="btn btn-success" disabled>تنفيذ المرتجع</button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="warningModalLabel">تحذير</h5>
          </div>
          <div class="modal-body">
            سعر الشراء اتغير وبقي أكبر من سعر المنتج الذي سيتم إرجاعه!
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">حسناً</button>
          </div>
        </div>
      </div>
    </div>
    <!-- بطاقة ملخص المرتجع -->
    <div class="card mt-4" id="returnSummaryCard">
      <div class="card-header bg-secondary text-white">ملخص المرتجع</div>
      <div class="card-body">
        <p>إجمالي اسعار المرتجع: <span id="returnTotalProduct">0.00</span></p>
        <p>إجمالي خصم علي المرتجع: <span id="returnTotalDiscount">0.00</span></p>
        <p>إجمالي الخصم الإضافي: <span id="returnTotalExtraDiscount">0.00</span></p>
        <p>إجمالي الضريبة علي المرتجع: <span id="returnTotalTax">0.00</span></p>
        <p><strong>إجمالي المرتجع:</strong> <span id="returnTotalOverall">0.00</span></p>
      </div>
    </div>
  @endif
</div>
@endsection

<!-- JavaScript: AJAX والدوال المساعدة -->
<script>
  // دالة مزامنة قيمة اختيار الوحدة مع الحقل المخفي
  function updateHiddenUnit(selectElem, productId) {
    document.getElementById('hidden_return_unit_' + productId).value = selectElem.value;
  }

  // دالة مزامنة كمية المرتجع مع الحقل المخفي باستخدام product_id
  function updateHiddenQuantity(inputElem) {
    var productId = inputElem.name.match(/\[(.*?)\]/)[1];
    document.getElementById('hidden_return_quantity_' + productId).value = inputElem.value;
  }

  $(document).ready(function() {
    // تحديث الملخص عند تحميل الصفحة
    updateReturnSummary();

    $('#returnInvoiceForm').on('submit', function(e) {
      e.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: formData,
        success: function(response) {
          toastr.success(response.message || 'تم تنفيذ المرتجع بنجاح!');
        },
        error: function(xhr) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'حدث خطأ أثناء تنفيذ المرتجع!';
          toastr.error(errorMessage);
        }
      });
    });
  });

  $(document).ready(function() {
    updateReturnSummary();

    // فحص أسعار الشراء مقابل السعر الحالي لكل صف وعرض مودال التحذير إذا لزم الأمر
    $('tr[data-purchase-price]').each(function(){
      var purchasePrice = parseFloat($(this).data('purchase-price'));
      var currentPrice = parseFloat($(this).data('current-price'));
      if (purchasePrice > currentPrice) {
        $('#warningModal').modal('show');
        return false;
      }
    });

    // استدعاء التحديث عند تغيير الكمية أو الوحدة
    $('select[name^="return_unit"]').on('change', function(){
      updateReturnSummary();
    });
    $('.return-qty').on('input change', function(){
      updateReturnSummary();
    });
  });

  // تغيير حد الكمية بناءً على الوحدة المختارة
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
      qtyInput.value = "";
      qtyInput.setAttribute('max', originalQuantity);
      updateHiddenQuantity(qtyInput);
    }
    updateReturnSummary();
  }

  // التحقق من عدم تجاوز الكمية للحد الأقصى
  function validateReturnQuantity(inputElem) {
    var maxAllowed = parseFloat(inputElem.getAttribute('max'));
    var val = parseFloat(inputElem.value);
    if (val > maxAllowed) {
      inputElem.value = maxAllowed;
      updateHiddenQuantity(inputElem);
    }
    updateReturnSummary();
  }

  // دالة تحديث ملخص المرتجع
  function updateReturnSummary() {
    var totalProduct = 0, totalDiscount = 0, totalExtraDiscount = 0, totalTax = 0, totalOverall = 0;
    var inputs = document.querySelectorAll('.return-qty');
    inputs.forEach(function(input) {
      var qty = parseFloat(input.value) || 0;
      var unitValue = parseFloat(input.getAttribute('data-unit-value')) || 1;
      var productPrice = parseFloat(input.closest('tr').querySelector('[data-base-price]').getAttribute('data-base-price')) || 0;
      var discountPerUnit = parseFloat(input.closest('tr').querySelector('[data-discount]').getAttribute('data-discount')) || 0;
      var extraDiscountPerUnit = parseFloat(input.closest('tr').querySelector('[data-extra-discount]').getAttribute('data-extra-discount')) || 0;
      var taxPerUnit = parseFloat(input.closest('tr').querySelector('[data-tax]').getAttribute('data-tax')) || 0;
      var returnUnit = input.dataset.returnUnit; // "1" لكبرى، "0" لصغرى
      
      if (returnUnit === "0") {
        productPrice = productPrice / unitValue;
        discountPerUnit = discountPerUnit / unitValue;
        extraDiscountPerUnit = extraDiscountPerUnit / unitValue;
        taxPerUnit = taxPerUnit / unitValue;
      }
      
      var effectiveFinalUnit = productPrice - discountPerUnit - extraDiscountPerUnit + taxPerUnit;
      
      totalProduct += qty * productPrice;
      totalDiscount += qty * discountPerUnit;
      totalExtraDiscount += qty * extraDiscountPerUnit;
      totalTax += qty * taxPerUnit;
      totalOverall += qty * effectiveFinalUnit;
    });
    
    document.getElementById('returnTotalProduct').textContent = totalProduct.toFixed(2);
    document.getElementById('returnTotalDiscount').textContent = totalDiscount.toFixed(2);
    document.getElementById('returnTotalExtraDiscount').textContent = totalExtraDiscount.toFixed(2);
    document.getElementById('returnTotalTax').textContent = totalTax.toFixed(2);
    document.getElementById('returnTotalOverall').textContent = totalOverall.toFixed(2);
  }

  // دوال التأكيد والتراجع لتعطيل/إعادة تفعيل الحقول المرئية
  function confirmForm() {
    var formElements = document.querySelectorAll('#returnInvoiceForm input[type="number"], #returnInvoiceForm select');
    formElements.forEach(function(elem) {
      elem.disabled = true;
    });
    document.getElementById('confirmBtn').style.display = 'none';
    document.getElementById('undoBtn').style.display = 'inline-block';
    document.getElementById('submitReturn').disabled = false;
  }

  function undoConfirm() {
    var formElements = document.querySelectorAll('#returnInvoiceForm input[type="number"], #returnInvoiceForm select');
    formElements.forEach(function(elem) {
      elem.disabled = false;
    });
    document.getElementById('undoBtn').style.display = 'none';
    document.getElementById('confirmBtn').style.display = 'inline-block';
    document.getElementById('submitReturn').disabled = true;
  }
  
</script>
