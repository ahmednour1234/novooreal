@extends('layouts.admin.app')

@section('content')
@php
    // Fallback: لو الكنترولر ما مررش $cashAccounts
    if (!isset($cashAccounts)) {
        $cashAccounts = \App\Models\Account::query()
            ->whereIn('parent_id', [8, 14])
            ->orWhereIn('id', [8, 14])   // يسمح بإظهار الأب نفسه
            ->orderBy('account')
            ->get(['id','account','parent_id']);
    }
@endphp

<style>
  #submitReturn[disabled]{pointer-events:none;opacity:.6;cursor:not-allowed}
</style>

<div class="container">
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="#" class="text-primary">{{ \App\CPU\translate('المرتجعات') }}</a>
        </li>
      </ol>
    </nav>
  </div>

  {{-- صندوق إدخال رقم الفاتورة --}}
  @if(!session()->has('orderDetails'))
    <div class="row justify-content-center mt-5 pt-5">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card mb-4 shadow-sm">
          <div class="card-header text-black text-center">ادخل رقم الفاتورة للمرتجع</div>
          <div class="card-body">
            <form method="POST" autocomplete="off"
                  action="{{ session('order_type') === 'service' ? route('admin.pos.processReturn') : route('admin.pos.processReturn') }}">
              @csrf
              <label for="invoice_number" class="form-label">رقم الفاتورة</label>
              <div class="input-group">
                <input type="text" class="form-control" name="invoice_number" id="invoice_number" required autofocus placeholder="اكتب رقم الفاتورة...">
                <button type="submit" class="btn btn-primary">اعرض المنتجات</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endif

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
        $isService = (session('order_type') ?? '') == "service";
        $netInvoice = (session('order_amount') ?? 0);
    @endphp
<form action="{{ route('pos.return.forget') }}" method="POST" class="d-inline">
  @csrf
  <button type="submit" class="btn btn-outline-danger">
    مسح بيانات الإرجاع
  </button>
</form>

@if(session('success'))
  <div class="alert alert-success mt-2">{{ session('success') }}</div>
@endif

    {{-- فورم واحد يحتوي كل شيء --}}
    <form id="returnInvoiceForm" method="POST" action="{{ route('admin.pos.processConfirmedReturn') }}" enctype="multipart/form-data" autocomplete="off">
      @csrf
      <input type="hidden" name="order_id" value="{{ session('orderDetails.order_id') }}">

      <div class="row mt-4">
        {{-- بيانات العميل --}}
        <div class="col-md-6">
          <div class="card h-100">
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

        {{-- ملخص + نوع المرتجع + حساب الكاش (كلهم داخل نفس الفورم) --}}
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header bg-secondary text-white">ملخص الفاتورة النهائية</div>
            <div class="card-body">
              <p><strong>الإجمالي قبل الخصم الإضافي:</strong>
                {{ number_format(($netInvoice) + $total_discount + $extraDiscount - $total_tax, 2) }}</p>
              <p><strong>خصم المنتجات الإجمالي:</strong> {{ number_format($total_discount, 2) }}</p>
              <p><strong>الخصم الإضافي:</strong> {{ number_format($extraDiscount, 2) }}</p>
              <p><strong>الضريبة:</strong> {{ number_format($total_tax, 2) }}</p>
              <p><strong>الصافي:</strong> {{ number_format($netInvoice, 2) }}</p>
              <hr>

              <div class="mb-2">
                <label class="form-label d-block mb-1"><strong>نوع المرتجع</strong></label>
                <div class="d-flex align-items-center" style="gap:12px">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="return_type" id="return_type_cash" value="cash" checked>
                    <label class="form-check-label" for="return_type_cash">كاش</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="return_type" id="return_type_credit" value="credit">
                    <label class="form-check-label" for="return_type_credit">أجل</label>
                  </div>
                </div>
              </div>

              <div id="cashAccountWrap" class="mb-2">
                <label for="cash_account_id" class="form-label"><strong>اختر الحساب (للكاش فقط)</strong></label>
                {{-- أهم نقطة: عندنا name="cash_account_id" والـ select جوّا نفس الفورم --}}
                <select id="cash_account_id" name="cash_account_id" class="form-control">
                  <option value="">-- اختر حساب --</option>
                  @foreach($cashAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->account }}</option>
                  @endforeach
                </select>
                <small class="text-muted">من حسابات الأب 8 أو 14 (ويظهر الأب نفسه عند الحاجة).</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- تفاصيل الفاتورة --}}
      <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
          تفاصيل الفاتورة رقم: {{ session('orderDetails.order_id') }}
        </div>
        <div class="card-body">
          <div class="table-responsive">
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
                  @if(!$isService)
                    <th>الوحدة</th>
                  @endif
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
                  $currentPrice = $product->unit == 0 ? $product->price * $unitValue : $product->price;
                  $rowClass = $computedPurchasePrice > $currentPrice ? 'table-danger' : '';
                @endphp
                <tr class="{{ $rowClass }}" data-purchase-price="{{ $computedPurchasePrice }}" data-current-price="{{ $currentPrice }}">
                  <td>{{ $product->product->name }}</td>
                  <td>
                    <span class="text-primary">شراء: {{ number_format($computedPurchasePrice, 2) }}</span><br>
                    <span class="text-success">حالي: {{ number_format($currentPrice, 2) }}</span>
                  </td>
                  <td data-base-price="{{ $product->price }}">{{ number_format($product->price, 3) }}</td>
                  <td data-discount="{{ $product->discount_on_product }}">{{ number_format($product->discount_on_product, 3) }}</td>
                  <td data-extra-discount="{{ ($discountRatio/100) * $product->price }}">{{ number_format(($discountRatio/100) * $product->price, 3) }}</td>
                  <td data-tax="{{ $product->tax_amount }}">{{ number_format($product->tax_amount, 3) }}</td>
                  <td data-final-unit="{{ $finalUnitPrice }}">{{ $finalUnitPrice }}</td>
                  <td>{{ $product->quantity }}</td>
                  <td>{{ $productFinalTotal }}</td>

                  @if(!$isService)
                    <td>
                      @if($product->unit == 1)
                        <select name="return_unit[{{ $product->product_id }}]" id="return_unit_{{ $product->product_id }}" class="form-control"
                                onchange="updateHiddenUnit(this, '{{ $product->product_id }}'); setReturnQuantityMax(this, '{{ $product->product_id }}', {{ $unitValue }}, {{ $product->quantity }})">
                          <option value="1" selected>كبري</option>
                          <option value="0">صغري</option>
                        </select>
                        <input type="hidden" name="return_unit_hidden[{{ $product->product_id }}]" id="hidden_return_unit_{{ $product->product_id }}" value="1">
                      @else
                        <input type="hidden" name="return_unit[{{ $product->product_id }}]" value="{{ $product->unit }}">
                        <input type="hidden" name="return_unit_hidden[{{ $product->product_id }}]" value="{{ $product->unit }}">
                        <span>صغري</span>
                      @endif
                    </td>
                  @endif

                  <td>
                    <input type="number" id="return_quantity_{{ $product->product_id }}"
                           name="return_quantities[{{ $product->product_id }}]"
                           class="form-control return-qty"
                           value="0" min="0" step="1"
                           data-final-unit-price="{{ $finalUnitPrice }}"
                           data-unit-value="{{ $unitValue }}"
                           data-return-unit="1"
                           oninput="updateHiddenQuantity(this); validateReturnQuantity(this); updateReturnSummary();"
                           onchange="updateReturnSummary();">
                    <input type="hidden" name="return_quantities_hidden[{{ $product->product_id }}]" id="hidden_return_quantity_{{ $product->product_id }}" value="0">
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>

          {{-- ملاحظات وصورة --}}
          <div class="row g-3">
            <div class="col-12 col-md-8">
              <label for="return_note" class="form-label">ملاحظات على المرتجع (اختياري)</label>
              <textarea name="note" id="return_note" class="form-control" rows="3" placeholder="اكتب أي ملاحظات مهمة..."></textarea>
            </div>
            <div class="col-12 col-md-4">
              <label for="return_attachment" class="form-label">صورة مرفقة (اختياري)</label>
              <input type="file" name="attachment" id="return_attachment" class="form-control" accept="image/*" onchange="previewAttachment(this)">
              <small class="text-muted d-block mt-1">يسمح برفع صورة واحدة (PNG/JPG/JPEG).</small>
              <div id="attachment_preview" class="mt-2" style="display:none">
                <img src="" alt="Preview" class="img-thumbnail" style="max-height:160px">
              </div>
            </div>
          </div>

          {{-- الأزرار --}}
          <div class="mt-3">
            <div class="action-bar d-flex align-items-center" style="gap:8px">
              <button type="button" id="confirmBtn" class="btn btn-secondary btn-eq" onclick="onConfirmClick()">تأكيد</button>
              <button type="button" id="undoBtn" class="btn btn-danger" onclick="undoConfirm()" style="display:none;">تراجع</button>
              <button type="submit" id="submitReturn" class="btn btn-primary btn-eq" disabled>تنفيذ المرتجع</button>
            </div>
          </div>
        </div>
      </div>

      {{-- بطاقة ملخص المرتجع --}}
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
    </form>
  @endif
</div>
@endsection

{{-- JavaScript --}}
<script>
  function hasBootstrap5(){return !!(window.bootstrap && typeof window.bootstrap.Modal==='function');}
  function showModalById(id){var el=document.getElementById(id);if(!el)return;if(hasBootstrap5()){window.bootstrap.Modal.getOrCreateInstance(el,{backdrop:'static'}).show();}else if(window.jQuery){jQuery(el).modal({backdrop:'static',show:true});}}
  function hideModalById(id){var el=document.getElementById(id);if(!el)return;if(hasBootstrap5()){window.bootstrap.Modal.getOrCreateInstance(el).hide();}else if(window.jQuery){jQuery(el).modal('hide');}}

  function updateHiddenUnit(sel,pid){var el=document.getElementById('hidden_return_unit_'+pid);if(el)el.value=sel.value;}
  function updateHiddenQuantity(inp){var m=inp.name.match(/\[(.*?)\]/);if(!m)return;var el=document.getElementById('hidden_return_quantity_'+m[1]);if(el)el.value=inp.value;}
  function previewAttachment(input){var wrap=document.getElementById('attachment_preview');if(!wrap)return;var img=wrap.querySelector('img');if(input.files&&input.files[0]){img.src=URL.createObjectURL(input.files[0]);wrap.style.display='block';}else{img.src='';wrap.style.display='none';}}

  function updateReturnSummary(){
    var totalProduct=0,totalDiscount=0,totalExtraDiscount=0,totalTax=0,totalOverall=0;
    document.querySelectorAll('.return-qty').forEach(function(input){
      var qty=parseFloat(input.value)||0;
      var unitValue=parseFloat(input.getAttribute('data-unit-value'))||1;
      var tr=input.closest('tr');if(!tr)return;
      var productPrice=parseFloat(tr.querySelector('[data-base-price]').getAttribute('data-base-price'))||0;
      var discountPU=parseFloat(tr.querySelector('[data-discount]').getAttribute('data-discount'))||0;
      var extraDiscPU=parseFloat(tr.querySelector('[data-extra-discount]').getAttribute('data-extra-discount'))||0;
      var taxPU=parseFloat(tr.querySelector('[data-tax]').getAttribute('data-tax'))||0;
      var returnUnit=input.dataset.returnUnit;
      if(returnUnit==="0"){productPrice/=unitValue;discountPU/=unitValue;extraDiscPU/=unitValue;taxPU/=unitValue;}
      var effUnit=productPrice-discountPU-extraDiscPU+taxPU;
      totalProduct+=qty*productPrice;
      totalDiscount+=qty*discountPU;
      totalExtraDiscount+=qty*extraDiscPU;
      totalTax+=qty*taxPU;
      totalOverall+=qty*effUnit;
    });
    document.getElementById('returnTotalProduct').textContent=totalProduct.toFixed(2);
    document.getElementById('returnTotalDiscount').textContent=totalDiscount.toFixed(2);
    document.getElementById('returnTotalExtraDiscount').textContent=totalExtraDiscount.toFixed(2);
    document.getElementById('returnTotalTax').textContent=totalTax.toFixed(2);
    document.getElementById('returnTotalOverall').textContent=totalOverall.toFixed(2);
  }

  function setReturnQuantityMax(selectElem,productId,unitValue,originalQuantity){
    var qty=document.getElementById('return_quantity_'+productId);if(!qty)return;
    if(selectElem.value=='0'){qty.dataset.returnUnit='0';var newMax=originalQuantity*unitValue;qty.setAttribute('max',newMax);if((parseFloat(qty.value)||0)>newMax){qty.value=newMax;updateHiddenQuantity(qty);}}
    else{qty.dataset.returnUnit='1';qty.setAttribute('max',originalQuantity);if((parseFloat(qty.value)||0)>originalQuantity){qty.value=originalQuantity;}updateHiddenQuantity(qty);}
    updateReturnSummary();
  }
  function validateReturnQuantity(input){
    var max=parseFloat(input.getAttribute('max'));var val=parseFloat(input.value);
    if(!isNaN(max)&&!isNaN(val)&&val>max){input.value=max;updateHiddenQuantity(input);}updateReturnSummary();
  }

  function lockProductInputs(lock){
    document.querySelectorAll('#returnInvoiceForm input[type="number"].return-qty, #returnInvoiceForm select[name^="return_unit"]').forEach(function(el){el.disabled=!!lock;});
  }
  function onConfirmClick(){
    lockProductInputs(true);
    document.getElementById('confirmBtn').style.display='none';
    document.getElementById('undoBtn').style.display='inline-block';
    document.getElementById('submitReturn').disabled=false;
  }
  function undoConfirm(){
    lockProductInputs(false);
    document.getElementById('undoBtn').style.display='none';
    document.getElementById('confirmBtn').style.display='inline-block';
    document.getElementById('submitReturn').disabled=true;
  }

  function handleReturnTypeChange(){
    var isCash=document.getElementById('return_type_cash').checked;
    var wrap=document.getElementById('cashAccountWrap');
    var select=document.getElementById('cash_account_id');
    if(isCash){wrap.style.display='block';select.disabled=false;}
    else{wrap.style.display='none';select.value='';select.disabled=true;}
  }

  (function init(){
    updateReturnSummary();
    handleReturnTypeChange();

    // تحذير اختلاف الأسعار
    var rows=document.querySelectorAll('tr[data-purchase-price]');
    for(var i=0;i<rows.length;i++){
      var pp=parseFloat(rows[i].getAttribute('data-purchase-price'));
      var cp=parseFloat(rows[i].getAttribute('data-current-price'));
      if(pp>cp){showModalById('warningModal');break;}
    }

    document.querySelectorAll('select[name^="return_unit"]').forEach(function(sel){sel.addEventListener('change',updateReturnSummary);});
    document.querySelectorAll('.return-qty').forEach(function(inp){
      inp.addEventListener('input',updateReturnSummary);
      inp.addEventListener('change',updateReturnSummary);
    });

    document.querySelectorAll('input[name="return_type"]').forEach(function(r){r.addEventListener('change',handleReturnTypeChange);});

    // منع الإرسال لو كاش بدون حساب
    document.getElementById('returnInvoiceForm').addEventListener('submit',function(e){
      var isCash=document.getElementById('return_type_cash').checked;
      var acc=document.getElementById('cash_account_id').value;
      if(isCash && !acc){
        e.preventDefault();
        if(window.toastr) toastr.warning('برجاء اختيار حساب الكاش أولاً.');
      }
    });
  })();
</script>