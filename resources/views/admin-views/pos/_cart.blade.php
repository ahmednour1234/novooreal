@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
@endpush
<style>
    .btn-primary {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.bg-danger {
    background-color: #EE6055 !important;
}
.bg-success {
    background-color: #60D394 !important;
}
.toggle-switch-input:checked + .toggle-switch-label {
    background-color: #60D394;
}
.h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    color: #1e2022;
    font-size:18px;
}
h1{
    color: #1e2022;
    font-size:18px;
}
.btn-outline-info {
    color: #677788;
    border-color: #677788;
}
.btn-outline-info:hover {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-outline-success {
    color: #677788;
    border-color: #677788;
}
.btn-outline-success:hover {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-success {
    color: #fff;
    background-color: #708D81;
    border-color: #708D81;
}
.btn-danger {
    color: #fff;
    background-color: #BF0603;
    border-color: #BF0603;
}
.btn-info {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.btn-info:hover {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.main .content {
    padding-top: 0.3rem;
    padding-bottom: 1.75rem;
}
.badge {
    background-color: #708D81;
    color: white;
}
.text-danger {
    color: #BF0603 !important;
}
.text-success {
    color: #003f88 !important;
}
.btn-outline-primary:not(:disabled):not(.disabled).active, .btn-outline-primary:not(:disabled):not(.disabled):active, .show > .btn-outline-primary.dropdown-toggle {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-primary:not(:disabled):not(.disabled).active, .btn-primary:not(:disabled):not(.disabled):active, .show > .btn-primary.dropdown-toggle {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.btn-info:not(:disabled):not(.disabled).active, .btn-info:not(:disabled):not(.disabled):active, .show > .btn-info.dropdown-toggle {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D
}

.btn-info:not(:disabled):not(.disabled).active:focus, .btn-info:not(:disabled):not(.disabled):active:focus, .show > .btn-info.dropdown-toggle:focus {
    box-shadow: #F4D58D;
}


.btn-info.disabled, .btn-info:disabled {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.btn-primary:not(:disabled):not(.disabled).active, .btn-primary:not(:disabled):not(.disabled):active, .show > .btn-primary.dropdown-toggle {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-danger.disabled, .btn-danger:disabled {
    color: #fff;
    background-color: #BF0603;
    border-color: #BF0603;
}
.btn-danger:hover {
    color: #fff;
    background-color: #BF0603;
    border-color: #BF0603;
}
.btn.disabled, .btn:disabled {
    opacity: .4;
}
.table td, .table th {
    vertical-align: center;
    font-size:0.8rem ;
}
 .table th {
    vertical-align: center;
    font-size:0.8rem ;
    background-color: #EDF2F4;
    color: black;
}
.bg-secondary {
    background-color:#71869d !important;
}
</style>
<div class="card-body pt-0">
  <div class="table-responsive pos-cart-table border rounded shadow-sm">
    <table class="table table-align-middle mb-0">
      <thead class="thead-light text-muted">
        <tr>
          <th>{{ \App\CPU\translate('المنتج') }}</th>
          
          <th>{{ \App\CPU\translate('الكمية') }}</th>
                            
          <th>{{ \App\CPU\translate('السعر') }}</th>
                   
          <th>{{ \App\CPU\translate('السعر شامل الضريبة') }}</th>
          <th>{{ \App\CPU\translate('حذف') }}</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $subtotal = 0;
          $tax = 0;
          $ext_discount = 0;
          $ext_discount_type = 'amount';
          $discount_on_product = 0;
          $product_tax = 0;
          $coupon_discount = 0;
          $total_tax_amount = 0;
        ?>

        @if (session()->has($cart_id) && count(session($cart_id)) > 0)
          <?php
            $cart = session()->get($cart_id);
            if (isset($cart['tax'])) {
              $tax = $cart['tax'];
            }
            if (isset($cart['ext_discount'])) {
              $ext_discount = $cart['ext_discount'];
              $ext_discount_type = $cart['ext_discount_type'];
            }
            if (isset($cart['coupon_discount'])) {
              $coupon_discount = $cart['coupon_discount'];
            }
          ?>
          @foreach (session($cart_id) as $key => $cartItem)
            @if (is_array($cartItem))
              <?php
                $product_subtotal = (float)$cartItem['price'] * (int)$cartItem['quantity'];
                $discount_on_product += (float)$cartItem['discount'] * (int)$cartItem['quantity'];
                $subtotal += $product_subtotal;
                $product_tax += (float)$cartItem['tax'] * (int)$cartItem['quantity'];
              ?>
              <tr>
                <td class="media align-items-center">
                  <img class="avatar avatar-sm mr-2 rounded" 
                       src="{{ asset('storage/app/public/product') }}/{{ $cartItem['image'] }}"
                       onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'" 
                       alt="{{ $cartItem['name'] }} image">
                  <div class="media-body">
                    <h6 class="mb-0">{{ Str::limit($cartItem['name'], 10) }}</h6>
                  </div>
                </td>

                <td>
                  <input type="number" data-key="{{ $key }}" class="form-control text-center qty-width" value="{{ $cartItem['quantity'] }}" min="0" step="0.1" onkeyup="updateQuantity('{{ $cartItem['id'] }}', this.value)">
                </td>

                <td>
                  <div>{{ number_format($cartItem['price'],2)  . ' ' . \App\CPU\Helpers::currency_symbol() }}</div>
                </td>

                            
                <td>
                  <div>{{ number_format(($product_subtotal - ($cartItem['discount']*$cartItem['quantity']) + ($cartItem['tax']*$cartItem['quantity'])),2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</div>
                </td>
                <td>
                  <a href="javascript:removeFromCart({{ $cartItem['id'] }})" class="btn btn-sm btn-outline-danger">
                    <i class="tio-delete"></i>
                  </a>
                </td>
              </tr>
            @endif
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
</div>

@php
  $total = $subtotal - $discount_on_product;
  $discount_amount = $ext_discount;
  $total -= $discount_amount;
  $total_tax_amount += $product_tax;
  $taxSetting = \App\Models\BusinessSetting::where('key', 'tax')->first();
  $taxRate = $taxSetting ? $taxSetting->value : 0;
@endphp

<div class="box p-3 bg-white rounded shadow-sm">
  <dl class="row">
    <dt class="col-6">{{ \App\CPU\translate('اجمالي الفاتورة') }} :</dt>
    <dd class="col-6 text-right">{{ $subtotal . ' ' . \App\CPU\Helpers::currency_symbol() }}</dd>

    @if(request()->route('type') == 12 || request()->route('type') == 24)
      <dt class="col-6">{{ \App\CPU\translate('خصم المنتجات') }} :</dt>
      <dd class="col-6 text-right">0.0</dd>
    @else
      <dt class="col-6">{{ \App\CPU\translate('خصم المنتجات') }} :</dt>
      <dd class="col-6 text-right">{{ round($discount_on_product, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</dd>
    @endif
                                               
                                               
    <dt class="col-6">{{ \App\CPU\translate('الضريبة') }} :</dt>
    <dd class="col-6 text-right">{{ round($total_tax_amount, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</dd>

    <dt class="col-6">{{ \App\CPU\translate('الصافي') }} :</dt>
    <dd class="col-6 text-right h4 font-weight-bold">
      <span id="total_price">{{ round($total + $total_tax_amount - $coupon_discount, 2) }}</span>
      {{ \App\CPU\Helpers::currency_symbol() }}
    </dd>
  </dl>
  <div class="row g-2">
    <div class="col-6 mt-2">
      <a href="javascript:emptyCart()" class="btn btn-danger btn-block">
        <i class="fa fa-times-circle"></i>
        {{ \App\CPU\translate('الغاء الفاتورة') }}
      </a>
    </div>
            <input type="hidden" name="tax"  id="tax" value="{{ $total_tax_amount }}">
          <input type="hidden" name="extra_discount" id="extra_discount"  value="{{$discount_amount}}">
          <input type="hidden" name="order_amount"  id="order_amount"   value="{{ $total + $total_tax_amount - $coupon_discount }}">
                    <input type="hidden" name="total_product_discount" id="total_product_discount" value="{{ $discount_on_product }}">
      <div class="col-6 mt-2">
        <button onclick="submit_order_quackic();" type="button" class="btn btn-primary btn-block">
          <i class="fa fa-shopping-bag"></i>
          {{ \App\CPU\translate('تنفيذ سريع') }}
        </button>
      </div>
   
  </div>
</div>

<!-- Modal: Add New Customer -->
<div class="modal fade" id="add-customer" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">{{ \App\CPU\translate('اضافة عميل جديد') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('admin.customer.store') }}" method="post" id="product_form">
          @csrf
          <input type="hidden" class="form-control" name="balance" value="0">
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label class="input-label">{{ \App\CPU\translate('الاسم') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ \App\CPU\translate('customer_name') }}" required>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label class="input-label">{{ \App\CPU\translate('رقم الهاتف') }} <span class="text-danger">*</span></label>
                <input type="text" id="mobile" name="mobile" class="form-control" value="{{ old('mobile') }}" placeholder="{{ \App\CPU\translate('mobile_no') }}" required>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label class="input-label">{{ \App\CPU\translate('الايميل') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{ \App\CPU\translate('Ex_:_ex@example.com') }}">
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label class="input-label">{{ \App\CPU\translate('المنطقة') }}</label>
                <input type="text" name="state" class="form-control" value="{{ old('state') }}" placeholder="{{ \App\CPU\translate('state') }}">
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label class="input-label">{{ \App\CPU\translate('المدينة') }}</label>
                <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="{{ \App\CPU\translate('city') }}">
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label class="input-label">{{ \App\CPU\translate('كود المدينة') }}</label>
                <input type="text" name="zip_code" class="form-control" value="{{ old('zip_code') }}" placeholder="{{ \App\CPU\translate('zip_code') }}">
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label class="input-label">{{ \App\CPU\translate('العنوان') }}</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="{{ \App\CPU\translate('address') }}">
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" id="submit_new_customer" class="btn btn-primary">{{ \App\CPU\translate('submit') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add Extra Discount -->
<div class="modal fade" id="add-discount" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">{{ \App\CPU\translate('خصم اضافي') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="form-group col-sm-6">
            <label>{{ \App\CPU\translate('الخصم') }}</label>
            <input type="number" id="dis_amount" class="form-control" name="discount" step="0.01" min="0">
          </div>
          <div class="form-group col-sm-6">
            <label>{{ \App\CPU\translate('type') }}</label>
            <select name="type" id="type_ext_dis" class="form-control" onchange="limit(this);">
              <option value="amount" {{ $ext_discount_type == 'amount' ? 'selected' : '' }}>
                {{ \App\CPU\translate('كمية') }} ({{ \App\CPU\Helpers::currency_symbol() }})
              </option>
              <option value="percent" {{ $ext_discount_type == 'percent' ? 'selected' : '' }}>
                {{ \App\CPU\translate('نسبة') }} (%)
              </option>
            </select>
          </div>
        </div>
        <div class="d-flex justify-content-end">
          <button class="btn btn-sm btn-primary" onclick="extra_discount();" type="submit">
            {{ \App\CPU\translate('اضفة الخصم') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add Coupon Discount -->
<div class="modal fade" id="add-coupon-discount" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">{{ \App\CPU\translate('كود خصم') }}</h5>
        <button id="coupon_close" type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>{{ \App\CPU\translate('كود خصم') }}</label>
          <input type="text" id="coupon_code" class="form-control" name="coupon_code">
        </div>
        <div class="d-flex justify-content-end">
          <button class="btn btn-sm btn-primary" type="submit" onclick="coupon_discount();">
            {{ \App\CPU\translate('حفظ الكود') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Edit Tax -->
<div class="modal fade" id="add-tax" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title">{{ \App\CPU\translate('تعديل الضريبة') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('admin.pos.tax') }}" method="POST" class="row">
          @csrf
          <div class="form-group col-12">
            <label>{{ \App\CPU\translate('الضريبة') }} (%)</label>
            <input type="number" class="form-control" name="tax" min="0">
          </div>
          <div class="form-group col-12">
            <button class="btn btn-sm btn-primary" type="submit">{{ \App\CPU\translate('حفظ') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">{{ \App\CPU\translate('دفع') }}</h5>
        <button id="payment_close" type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <span class="ml-auto font-weight-bold">{{ \App\CPU\translate('الصافي') }}</span>
        <h4 class="mb-0 ml-2" id="total_balance">
          <span>=</span>
          {{ round($total + $total_tax_amount - $coupon_discount, 2) }} {{ \App\CPU\Helpers::currency_symbol() }}
        </h4>
      </div>
      @php
$accounts = \App\Models\Account::where(function($query) {
    // نختار الحسابات الأصلية أو اللي رقمها 8 أو 14 أو اللي parent_id تبعهم أحد هذين الحسابين
    $query->whereIn('id', [8,14])
          ->orWhereIn('parent_id', [8,14]);
})->doesntHave('children') // نتأكد أنه ليس له أولاد
  ->orderBy('id')
  ->get();


          $costcenters = \App\Models\CostCenter::where('active',1)->doesntHave('children')->get();
      @endphp
      <div class="modal-body">
        <form action="{{ route('admin.pos.order') }}" id="order_place"  method="post" enctype="multipart/form-data">
          @csrf
          <!-- Hidden route type -->
          <input type="hidden" id="type" name="type" value="{{ request()->route('type') }}">
          
          <div class="form-group">
            <label>{{ \App\CPU\translate('طريقة الدفع') }}</label>
            <select onchange="payment_option(this);" name="cash" id="payment_pp" class="form-control select2" required>
              <option value="">{{ \App\CPU\translate('أختر') }}</option>
              <option value="1">كاش</option>
              <option value="2">أجل</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="account_select">{{ \App\CPU\translate('اختار الحساب') }}</label>
            <select name="payment_id" id="account_select" class="form-control select2" required>
              @foreach ($accounts as $account)
                <option value="{{ $account['id'] }}">{{ $account['account'] }}</option>
              @endforeach
            </select>
          </div>
          
          <div class="form-group">
            <label for="cost_center_select">{{ \App\CPU\translate('اختار مركز التكلفة') }}</label>
            <select name="cost_id" id="cost_center_select" class="form-control select2" required>
              @foreach ($costcenters as $costcenter)
                <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
              @endforeach
            </select>
          </div>
          
          <div class="form-group">
            <label for="img">{{ \App\CPU\translate('تحميل صورة') }}</label>
            <input type="file" name="img" id="img" class="form-control">
          </div>
          
          <div class="form-group" id="collected_cash">
            <label>{{ \App\CPU\translate('اجمالي المدفوع') }} ({{ \App\CPU\Helpers::currency_symbol() }})</label>
            <input type="number" id="cash_amount" onkeyup="price_calculation();" class="form-control" name="collected_cash" step="0.01" required>
          </div>
          
          <div class="form-group" id="returned_amount">
            <label>{{ \App\CPU\translate('اجمالي المتبقي') }} ({{ \App\CPU\Helpers::currency_symbol() }})</label>
            <input type="number" id="returned" class="form-control" name="returned_amount" readonly>
          </div>
          
          <div class="form-group" id="date">
            <label>{{ \App\CPU\translate('تاريخ السداد') }}</label>
            <input type="date" id="date" class="form-control" name="date">
          </div>
                    <input type="hidden" name="subtotal" id="subtotal" value="{{ $subtotal }}">

          <input type="hidden" name="tax"  id="tax" value="{{ $total_tax_amount }}">
          <input type="hidden" name="extra_discount" id="extra_discount"  value="{{$discount_amount}}">
          <input type="hidden" name="order_amount"  id="order_amount"   value="{{ $total + $total_tax_amount - $coupon_discount }}">
                    <input type="hidden" name="total_product_discount" id="total_product_discount" value="{{ $discount_on_product }}">

          <div class="d-flex justify-content-end">
            <button class="btn btn-sm btn-primary" id="order_complete" type="submit">
              {{ \App\CPU\translate('تنفيذ') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Short Cut Keys -->
<div class="modal fade" id="short-cut-keys" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title">{{ \App\CPU\translate('short_cut_keys') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <span>{{ \App\CPU\translate('to_click_order') }} : alt + O</span><br>
        <span>{{ \App\CPU\translate('to_click_payment_submit') }} : alt + S</span><br>
        <span>{{ \App\CPU\translate('to_close_payment_submit') }} : alt + Z</span><br>
        <span>{{ \App\CPU\translate('to_click_cancel_cart_item_all') }} : alt + C</span><br>
        <span>{{ \App\CPU\translate('to_click_add_new_customer') }} : alt + A</span><br>
        <span>{{ \App\CPU\translate('to_submit_add_new_customer_form') }} : alt + N</span><br>
        <span>{{ \App\CPU\translate('to_click_short_cut_keys') }} : alt + K</span><br>
        <span>{{ \App\CPU\translate('to_print_invoice') }} : alt + P</span><br>
        <span>{{ \App\CPU\translate('to_cancel_invoice') }} : alt + B</span><br>
        <span>{{ \App\CPU\translate('to_focus_search_input') }} : alt + Q</span><br>
        <span>{{ \App\CPU\translate('to_click_extra_discount') }} : alt + E</span><br>
        <span>{{ \App\CPU\translate('to_click_coupon_discount') }} : alt + D</span><br>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Print Invoice -->
<div class="modal fade col-md-12" id="print-invoice" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded modal-content1">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">{{ \App\CPU\translate('طباعة') }} {{ \App\CPU\translate('الفاتورة') }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span class="text-dark" aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body row">
        <div class="col-md-12 text-center">
          <input type="button" class="mt-2 btn btn-primary non-printable" onclick="printDiv('printableArea')" value="{{ \App\CPU\translate('لو متصل بالطابعة اطبع') }}."/>
          <a href="{{ url()->previous() }}" class="mt-2 btn btn-danger non-printable">{{ \App\CPU\translate('عودة') }}</a>
        </div>
        <hr class="non-printable">
        <div class="row m-auto" id="printableArea">
          <!-- Printable content will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade col-md-12" id="print-invoice" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content1">
            <div class="modal-header">
                <h5 class="modal-title">{{ \App\CPU\translate('طباعة الفاتورة') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-dark" aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row">
                <div class="col-md-12 text-center">
                    <input type="button" class="mt-2 btn btn-primary non-printable"
                           onclick="printDiv('printableArea')"
                           value="{{ \App\CPU\translate('لو متصل بالطابعة اطبع') }}" />
                    <a href="{{ url()->previous() }}" class="mt-2 btn btn-danger non-printable">
                        {{ \App\CPU\translate('عودة') }}
                    </a>
                    <hr class="non-printable">
                </div>
                <div class="row m-auto" id="printableArea">
                    <!-- سيُملأ هنا محتوى الفاتورة -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- A hidden button for printing invoice -->
<button class="btn btn-sm btn-white d-none" target="_blank" type="button" onclick="print_invoice()">
  <i class="tio-download"></i> {{ \App\CPU\translate('الفاتورة') }}
</button>

<script>
    // 0. نخزن رقم الطلب الأخير
    let lastOrderId = null;

    // 1. دالة تعبئة حقول الطلب
    function populateOrderForm() {
        const ppInput = document.getElementById("payment_pp");
        if (ppInput) ppInput.value = "1";

        const accountSelect = document.getElementById("account_select");
        if (accountSelect) accountSelect.value = "1";

        // جلب القيم من الخادم
        const total    = {{ round($total + $total_tax_amount - $coupon_discount, 2) }};
        const discount = {{ round($discount_amount, 2) }};

        // مراجع لعناصر الإدخال
        const cashInput        = document.getElementById("cash_amount");
        const orderAmountInput = document.getElementById("order_amount");
        const extraDiscount    = document.getElementById("extra_discount");
        const txRefInput       = document.getElementById("transaction_ref_input");

        // تحديث القيم مع التحقق من وجود العنصر
        if (cashInput)        cashInput.value        = total;
        if (orderAmountInput) orderAmountInput.value = total;
        if (extraDiscount)    extraDiscount.value    = discount;
        if (txRefInput)       txRefInput.value       = "Auto-Generated";

        // لوج للقيم بعد التحديث
        console.log("بعد التعبئة:", {
            total,
            discount,
            cash_amount: cashInput ? cashInput.value : null,
            order_amount: orderAmountInput ? orderAmountInput.value : null,
            extra_discount: extraDiscount ? extraDiscount.value : null,
            transaction_ref: txRefInput ? txRefInput.value : null,
            account_id: accountSelect ? accountSelect.value : null
        });
    }

    // 2. دالة الإرسال الرئيسية
    async function submit_order_quackic() {
        populateOrderForm();
        const form = document.getElementById("order_place");

        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept":       "application/json"
                },
                body: new FormData(form),
            });

            const text = await response.text();
            if (!response.ok) {
                alert(`❌ خطأ HTTP ${response.status} ${response.statusText}:\n\n${text}`);
                return;
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch {
                alert(`❌ استجابة غير صالحة JSON:\n\n${text}`);
                return;
            }

            if (data.success) {
                lastOrderId = data.order_id;
                print_invoice(data.order_id);
            } else {
                alert(`❌ خطأ من السيرفر:\n\n${JSON.stringify(data, null, 2)}`);
            }

        } catch (err) {
            alert(`❌ خطأ في الطلب:\n\n${err.message}`);
        }
    }

    // 3. دالة جلب وعرض الفاتورة
    function print_invoice(order_id) {
        $.get({
            url: '{{ url("/") }}/admin/pos/invoice/' + order_id,
            dataType: 'json',
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (data) {
                if (data.view) {
                    $('#printableArea').html(data.view);
                    $('#print-invoice').modal('show');
                } else {
                    alert('❌ لم يتم إرجاع محتوى الفاتورة من السيرفر.');
                }
            },
            error: function (xhr) {
                const statusLine = `HTTP ${xhr.status} ${xhr.statusText}`;
                const bodyText   = xhr.responseText || '(لا يوجد محتوى)';
                alert(`❌ خطأ في جلب الفاتورة:\n${statusLine}\n\n${bodyText}`);
                console.error('print_invoice Error:', statusLine, bodyText);
            },
            complete: function () {
                $('#loading').hide();
            }
        });
    }

    // 4. دالة printDiv للطباعة الفعلية
    function printDiv(divId) {
        const printContents = document.getElementById(divId).innerHTML;
        const original      = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = original;
        toastr.success("✅ تم تنفيذ الطلب بنجاح!");
        setTimeout(() => location.reload(), 2000);
    }

    // 5. ربط اختصار Alt+2
    document.addEventListener("keydown", function(event) {
        if (event.altKey && event.key === "2") {
            event.preventDefault();
            if (lastOrderId) {
                print_invoice(lastOrderId);
            } else {
                submit_order_quackic();
            }
        }
    });
</script>

<!-- مودال الطباعة كما في الـ Blade -->
<script>
  $(document).ready(function() {
    $('#account_select').select2({
      placeholder: '{{ \App\CPU\translate('اختر الحساب') }}',
      allowClear: true,
      width: '100%'
    });
    $('#cost_center_select').select2({
      placeholder: '{{ \App\CPU\translate('اختر مركز التكلفة') }}',
      allowClear: true,
      width: '100%'
    });
  });
</script>
