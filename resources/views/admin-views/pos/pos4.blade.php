@extends('layouts.admin.app')
@section('title','إنشاء فاتورة مبيعات')
@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
  <!-- إضافة CSS لمكتبة Select2 لتحسين القوائم القابلة للبحث -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<style>
  @font-face {
    font-family: 'Bahij';
    src: url("{{ asset('public/assets/admin/css/fonts/Bahij_TheSansArabic-Plain.ttf') }}") format('truetype');
    font-weight: normal;
    font-style: normal;
  }
  body {
    background: linear-gradient(135deg, #ece9e6, #ffffff);
    font-family: 'Bahij', sans-serif;
    color: #333;
    margin: 0;
    padding: 0;
  }
  header.navbar {
    /* Updated dark blue gradient */
    background: linear-gradient(135deg, #00008B, #191970);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  }
  header .navbar-brand span {
    font-size: 1.6rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: white;
  }
  /* Ensure all h1 elements are white */
  h1 {
    color: white;
  }
  /* زر تبديل الاتجاه */
  .direction-toggle {
    background: #161853;
    color: #fff;
    padding: 10px 15px;
    border-radius: 50px;
    cursor: pointer;
    position: fixed;
    top: 40%;
    right: 20px;
    z-index: 9999;
    transition: transform 0.3s, background 0.3s;
  }
  .direction-toggle:hover {
    transform: translateX(-8px);
    background: #0f1241;
  }
  /* تصميم الكروت */
  .card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    background: #fff;
  }
  .card-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    font-size: 1.3rem;
    padding: 15px 20px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
  }
  .card-header.bg-primary {
    background: linear-gradient(135deg, #00008B, #191970) !important;
  }
  .card-header.bg-info {
    background: linear-gradient(135deg, #00008B, #191970) !important;
  }
  .card-body {
    padding: 20px;
  }
  /* تنسيق الجداول */
  .table thead th {
    background-color: #f0f0f0;
    font-weight: bold;
    text-align: center;
    padding: 12px;
  }
  .table tbody td {
    text-align: center;
    vertical-align: middle;
    padding: 10px;
    transition: background 0.3s;
  }
  .table tbody tr:hover {
    background-color: #f9f9f9;
  }
  .table-responsive {
    font-size: 1.1rem;
  }
  /* ملخص الفاتورة */
  .total-summary p {
    font-size: 1.2rem;
    font-weight: bold;
    margin: 0;
  }
  /* تنسيق المودالات */
  .modal-header {
    background: #161853;
    color: #fff;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
  }
  .modal-title {
    font-size: 1.5rem;
  }
  .btn {
    transition: background 0.3s, transform 0.3s;
  }
  .btn:hover {
    transform: translateY(-2px);
  }
  /* تحسين عرض select العملاء */
  .input-group .input-group-text {
    background: #f0f0f0;
    border: 1px solid #ced4da;
  }
</style>

<div class="container my-4">
  <!-- زر تبديل الاتجاه -->
  <div class="direction-toggle">
    <i class="fas fa-cog"></i>
    <span>Toggle RTL</span>
  </div>

  <!-- Main Content -->
  <main class="my-4">
    <!-- Card 1: بيانات العميل والشركة -->
    <div class="card">
      <div class="card-header bg-primary text-white">
        بيانات العميل والشركة
      </div>
      <div class="card-body">
        <div class="row">
          <!-- اختيار العميل باستخدام Select2 -->
          <div class="col-md-6 mb-3">
            <label>اختر العميل:</label>
            <select class="form-control select2" id="customer">
              @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
              @endforeach
            </select>
          </div>
          <!-- اسم الشركة -->
          <div class="col-md-3 mb-3">
            <label>اسم الشركة:</label>
            <p id="companyName" class="mb-0">
              {{ \DB::table('business_settings')->where('key', 'shop_name')->value('value') }}
            </p>
          </div>
          <!-- اسم البائع -->
          <div class="col-md-3 mb-3">
            <label>اسم البائع:</label>
            <p id="sellerName" class="mb-0">
              {{ auth()->guard('admin')->user()->f_name . ' ' . auth()->guard('admin')->user()->l_name }}
            </p>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-md-4">
            <label>الوقت الحالي:</label>
            <p id="currentTime">{{ date('Y-m-d H:i:s') }}</p>
          </div>
          <!-- زر فتح مودال إضافة عميل -->
          <div class="col-md-4">
            <button class="btn btn-success" id="addCustomerBtn" data-toggle="modal" data-target="#addClientModal">إضافة عميل</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 1.1: بيانات العميل المُختار -->
    <div class="card d-none" id="customerDataCard">
      <div class="card-header bg-secondary text-white">
        بيانات العميل
      </div>
      <div class="card-body" id="customerDetails">
        <!-- سيتم تعبئة بيانات العميل هنا بعد الاختيار -->
      </div>
    </div>

    <!-- Card 2: تفاصيل الفاتورة -->
    <div class="card">
      <div class="card-header bg-info text-white">
        تفاصيل الفاتورة
      </div>
      <div class="card-body">
        <!-- جدول المنتجات -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="thead-light">
              <tr>
                <th>اسم المنتج</th>
                <th>سعر الوحدة</th>
                <th>السعر</th>
                <th>الضريبة</th>
                <th>الخصم</th>
                <th>السعر بعد الخصم والضريبة</th>
                <th>الكمية</th>
                <th>وحدة القياس</th>
                <th>السعر النهائي</th>
                <th>تعديل سعر البيع</th>
              </tr>
            </thead>
            <tbody id="invoiceItems">
              <tr>
                <td>
                  <select class="form-control product-select select2" id="productSelect">
                    @foreach($products as $product)
                      <option value="{{$product->id}}">{{$product->name}}</option>
                    @endforeach
                  </select>
                </td>
                <td><input type="number" class="form-control unit-price" value="50" readonly></td>
                <td><input type="number" class="form-control price" value="50" readonly></td>
                <td><input type="number" class="form-control tax" value="5" readonly></td>
                <td><input type="number" class="form-control discount" value="2" readonly></td>
                <td><input type="number" class="form-control price-after" value="53" readonly></td>
                <td><input type="number" name="quantity" class="form-control quantity" value="1"></td>
                <td>
                  <select name="unit" class="form-control unit-type select2">
                    <option value="0">صغري</option>
                    <option value="1">كبري</option>
                  </select>
                </td>
                <td><input type="number" class="form-control final-price" value="53" readonly></td>
                <td>
                  <input type="number" class="form-control sale-price" value="53" min="55">
                  <small class="text-danger">لا يقل عن سعر الشراء بعد الضريبة</small>
                </td>
              </tr>
              <!-- Loop over products in the session cart -->
              <?php
                $subtotal = 0;
                $discount_on_product = 0;
                $product_tax = 0;
              ?>
              @if (session()->has($cart_id) && count(session($cart_id)) > 0)
                  @foreach (session($cart_id) as $key => $cartItem)
                      @if (is_array($cartItem))
                          <?php
                              // إذا كانت السلة تحتوي على منتجات، يتم حساب الإجمالي الفرعي والخصم والضريبة لكل منتج
                              $product_subtotal = (float) $cartItem['price'] * (int) $cartItem['quantity'];
                              $discount_on_product += (float) $cartItem['discount'] * (int) $cartItem['quantity'];
                              $subtotal += $product_subtotal;
                              $product_tax += (float) $cartItem['tax'] * (int) $cartItem['quantity'];
                          ?>
                          <tr>
                              <td>{{ $cartItem['name'] }}</td>
                              <td>{{ number_format($cartItem['price'], 2) }}</td>
                              <td>{{ $cartItem['quantity'] }}</td>
                              <td>{{ number_format($product_subtotal, 2) }}</td>
                              <td>{{ number_format($cartItem['discount'], 2) }}</td>
                              <td>{{ number_format((float) $cartItem['discount'] * (int) $cartItem['quantity'], 2) }}</td>
                              <td>{{ number_format($cartItem['tax'], 2) }}</td>
                              <td>{{ number_format((float) $cartItem['tax'] * (int) $cartItem['quantity'], 2) }}</td>
                              <!-- You can add more columns here if needed -->
                          </tr>
                      @endif
                  @endforeach

                  <tr>
                      <td colspan="3"><strong>الإجمالي الفرعي</strong></td>
                      <td colspan="5">{{ number_format($subtotal, 2) }}</td>
                  </tr>
                  <tr>
                      <td colspan="3"><strong>إجمالي الخصم على المنتجات</strong></td>
                      <td colspan="5">{{ number_format($discount_on_product, 2) }}</td>
                  </tr>
                  <tr>
                      <td colspan="3"><strong>إجمالي الضريبة</strong></td>
                      <td colspan="5">{{ number_format($product_tax, 2) }}</td>
                  </tr>
              @endif
            </tbody>
          </table>
        </div>
        <!-- زر لإضافة صف جديد من المنتجات -->
        <div class="text-right mt-2">
          <button id="addRow" class="btn btn-info">إضافة صف منتج</button>
        </div>
        <!-- خصم إضافي -->
        <div class="row mt-2">
          <div class="col-md-12">
            <label>نوع الخصم الإضافي:</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="extraDiscountType" id="discountFixed" value="fixed" checked>
              <label class="form-check-label" for="discountFixed">رقمي</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="extraDiscountType" id="discountPercentage" value="percentage">
              <label class="form-check-label" for="discountPercentage">نسبة</label>
            </div>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-12">
            <label>قيمة الخصم الإضافي:</label>
            <input type="number" id="extraDiscountValue" class="form-control" placeholder="ادخل قيمة الخصم">
          </div>
        </div>
        <!-- ملخص الفاتورة النهائية -->
        <div class="row mt-3 total-summary">
          <div class="col-md-3">
            <label>الإجمالي النهائي:</label>
            <?php
                // يمكن احتساب الإجمالي النهائي هنا كما يناسبك (على سبيل المثال: الإجمالي الفرعي + الضريبة - الخصومات)
                $finalTotal = $subtotal + $product_tax - $discount_on_product;
            ?>
            <p>{{ number_format($finalTotal, 2) }}</p>
          </div>
        </div>
        <!-- خيارات الدفع -->
        <div class="row mt-3">
          <div class="col-md-12">
            <label>طريقة الدفع:</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="payment_method" id="paymentCash" value="cash" checked>
              <label class="form-check-label" for="paymentCash">كاش</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="payment_method" id="paymentCredit" value="credit">
              <label class="form-check-label" for="paymentCredit">أجل</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="payment_method" id="paymentNetwork" value="network">
              <label class="form-check-label" for="paymentNetwork">شبكة</label>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- زر حفظ الفاتورة -->
    <div class="text-center mb-4">
      <button class="btn btn-primary btn-lg">حفظ الفاتورة</button>
    </div>
    <!-- زر لإضافة منتج جديد (Modal) -->
    <div class="text-center mb-4">
      <button class="btn btn-warning" data-toggle="modal" data-target="#addProductModal">إضافة منتج جديد</button>
    </div>
  </main>
  
  <!-- مودال إضافة عميل جديد -->
  <div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title text-white" id="addClientModalLabel">إضافة عميل جديد</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="إغلاق">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.customer.store') }}" method="post" id="addClientForm">
            @csrf
            <input type="hidden" class="form-control" name="balance" value="0">
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <label class="input-label">{{ \App\CPU\translate('الاسم') }} <span class="input-label-secondary text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ \App\CPU\translate('customer_name') }}" required>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label class="input-label">{{ \App\CPU\translate('رقم الهاتف') }} <span class="input-label-secondary text-danger">*</span></label>
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
                  <label class="input-label">{{ \App\CPU\translate('المدينة') }} </label>
                  <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="{{ \App\CPU\translate('city') }}">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label class="input-label">{{ \App\CPU\translate('كود المدينة') }} </label>
                  <input type="text" name="zip_code" class="form-control" value="{{ old('zip_code') }}" placeholder="{{ \App\CPU\translate('zip_code') }}">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label class="input-label">{{ \App\CPU\translate('العنوان') }} </label>
                  <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="{{ \App\CPU\translate('address') }}">
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-end">
              <button type="submit" id="submit_new_customer" class="btn btn-primary">{{ \App\CPU\translate('حفظ') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <!-- مودال تفاصيل الدفع -->
  <div class="modal fade" id="paymentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title" id="paymentDetailsModalLabel">تفاصيل الدفع</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="إغلاق">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="paymentContent"></div>
        </div>
        <div class="modal-footer">
          <button type="button" id="savePaymentDetails" class="btn btn-primary">حفظ</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- مودال إضافة منتج جديد -->
  <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">  
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="addProductModalLabel">إضافة منتج جديد</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="إغلاق">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.product.store') }}" method="post" id="product_form" enctype="multipart/form-data">
            @csrf
            <div class="row pl-2">
              <!-- Product Name in Arabic -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="name">
                    {{ \App\CPU\translate('الاسم بالعربي') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ \App\CPU\translate('product_name_in_arabic') }}" required>
                </div>
              </div>
              <!-- Product Name in English -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="name_en">
                    {{ \App\CPU\translate('الاسم بالانجليزي') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="{{ \App\CPU\translate('product_name_in_english') }}" required>
                </div>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Product Code -->
              <div class="col-md-6">
                <div class="form-group">
                  <label class="input-label" for="product_code">
                    {{ \App\CPU\translate('انشاء كود اتوماتيك') }}
                    <span class="input-label-secondary">*</span>
                    <a class="style-one-pro" onclick="document.getElementById('generate_number').value = getRndInteger()">
                      {{ \App\CPU\translate('انشاء كود') }}
                    </a>
                  </label>
                  <input type="text" minlength="5" id="generate_number" name="product_code" class="form-control" value="{{ old('product_code') }}" placeholder="{{ \App\CPU\translate('product_code') }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="input-label" for="tax_id">{{ \App\CPU\translate('اختار الضريبة') }}</label>
                <select name="tax_id" id="tax_id" class="form-control js-select2-custom">
                  <option value="">{{ \App\CPU\translate('اختر') }}</option>
                  <!-- خيارات الضريبة يمكن إضافتها هنا -->
                </select>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Category -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="category_id">
                    {{ \App\CPU\translate('قسم') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <select name="category_id" class="form-control js-select2-custom" onchange="getRequest('{{ url('/') }}/admin/product/get-categories?parent_id='+this.value, 'sub-categories')" required>
                    <option value="">--- {{ \App\CPU\translate('اختار') }} ---</option>
                    <!-- يمكن جلب الأقسام ديناميكيًا -->
                  </select>
                </div>
              </div>
              <!-- Product Type -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="type">
                    {{ \App\CPU\translate('التصنيف') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <select name="type" id="type" class="form-control js-select2-custom">
                    <option value="imported">{{ \App\CPU\translate('مستورد') }}</option>
                    <option value="local">{{ \App\CPU\translate('محلي') }}</option>
                    <option value="assembled">{{ \App\CPU\translate('مجمع') }}</option>
                    <option value="company">{{ \App\CPU\translate('شركة') }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Quantity -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="quantity">
                    {{ \App\CPU\translate('الكمية') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" placeholder="{{ \App\CPU\translate('quantity') }}" required>
                </div>
              </div>
              <!-- Unit Type -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="unit_type">
                    {{ \App\CPU\translate('الوحدة') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <select name="unit_type" class="form-control js-select2-custom">
                    <option value="">--- {{ \App\CPU\translate('اختار') }} ---</option>
                    <!-- يمكن جلب الوحدات هنا -->
                  </select>
                </div>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Unit Value -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="unit_value">
                    {{ \App\CPU\translate('الوحدة كمية') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <input type="number" min="0" name="unit_value" id="unit_value_input" class="form-control" value="{{ old('unit_value') }}" placeholder="{{ \App\CPU\translate('unit_value') }}" oninput="roundToNearestStep(this, 0.01)">
                </div>
              </div>
              <!-- Selling Price -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="selling_price">
                    {{ \App\CPU\translate('سعر البيع') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price') }}" placeholder="{{ \App\CPU\translate('selling_price') }}" required>
                </div>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Purchase Price -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="purchase_price">
                    {{ \App\CPU\translate('سعر الشراء') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price') }}" placeholder="{{ \App\CPU\translate('purchase_price') }}" required>
                </div>
              </div>
              <!-- Discount Type -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="discount_type">
                    {{ \App\CPU\translate('نوع الخصم') }}
                  </label>
                  <select onchange="discount_option(this);" name="discount_type" class="form-control js-select2-custom">
                    <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>{{ \App\CPU\translate('نسبة') }}</option>
                    <option value="amount" {{ old('discount_type') == 'amount' ? 'selected' : '' }}>{{ \App\CPU\translate('رقم') }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Discount -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label id="percent" class="input-label">{{ \App\CPU\translate('نسبة الخصم') }} (%)</label>
                  <label id="amount" class="input-label d-none">{{ \App\CPU\translate('discount_amount') }}</label>
                  <input type="number" min="0" name="discount" class="form-control" value="{{ old('discount') }}" placeholder="{{ \App\CPU\translate('discount') }}">
                </div>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Supplier -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="supplier_id">
                    {{ \App\CPU\translate('اختار مورد') }}
                  </label>
                  <select class="form-control js-select2-custom" name="supplier_id" id="supplier_id">
                    <option value="">--- {{ \App\CPU\translate('اختار') }} ---</option>
                    <!-- يمكن جلب بيانات الموردين هنا -->
                  </select>
                </div>
              </div>
              <!-- Expiry Date -->
              <div class="col-12 col-sm-6">
                <div class="form-group">
                  <label class="input-label" for="expiry_date">
                    {{ \App\CPU\translate('تاريخ انتهاء الصلاحية') }}
                    <span class="input-label-secondary">*</span>
                  </label>
                  <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                </div>
              </div>
            </div>
            <div class="row pl-2">
              <!-- Product Image -->
              <div class="col-12 col-sm-12">
                <label>{{ \App\CPU\translate('صورة') }}</label>
                <div class="custom-file">
                  <input type="file" name="image" id="customFileEg1" class="custom-file-input" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                  <label class="custom-file-label" for="customFileEg1">{{ \App\CPU\translate('choose') }} {{ \App\CPU\translate('file') }}</label>
                </div>
                <div class="form-group my-4">
                  <center>
                    <img class="style-two-pro" id="viewer" src="{{ asset('public/assets/admin/img/400x400/img2.jpg') }}" alt="image"/>
                  </center>
                </div>
              </div>
            </div>
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary col-12">{{ \App\CPU\translate('حفظ') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
  // Define getRndInteger if not already defined
  function getRndInteger(min = 10000, max = 99999) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  $(document).ready(function(){
    $('.select2').select2({ width: '100%' });

    // Update current time
    function updateCurrentTime() {
      $('#currentTime').text(new Date().toLocaleString());
    }
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000);

    // Validate sale-price input
    $(document).on('change', '.sale-price', function() {
      var salePrice = parseFloat($(this).val());
      var minSalePrice = 55;
      if (salePrice < minSalePrice) {
        alert('يجب ألا يقل سعر البيع عن ' + minSalePrice);
        $(this).val(minSalePrice);
      }
    });

    // Customer select using Select2
    if ($('#customer').length) {
      $('#customer').on('select2:select', function (e) {
        var data = e.params.data;
        var url = "{{ route('admin.customer.details', ':id') }}".replace(':id', data.id);
        $.ajax({
          url: url,
          type: 'GET',
          dataType: 'json',
          success: function(response) {
            var detailsHtml = '<h1>' + response.name + '</h1>' +
                              '<p><strong>السجل التجاري:</strong> ' + response.c_history + '</p>' +
                              '<p><strong>الرقم الضريبي:</strong> ' + response.tax_number + '</p>' +
                              '<p><strong>رقم الجوال:</strong> ' + response.mobile + '</p>' +
                              '<p><strong>المديونية:</strong> ' + response.credit + '</p>';
            $('#customerDetails').html(detailsHtml);
            $('#customerDataCard').removeClass('d-none');
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
            alert('حدث خطأ أثناء جلب بيانات العميل');
          }
        });
      });
    }

    // When a product is selected, update the session with the selected product ID

    // Add new client form submission
    $('#addClientForm').on('submit', function(e){
      e.preventDefault();
      alert("تم حفظ بيانات العميل بنجاح!");
      $('#addClientModal').modal('hide');
    });

    // Calculate totals (if needed later)
    function calculateTotals() {
      var totalProductsPrice = 0, totalDiscount = 0, totalTax = 0;
      $('#invoiceItems tr').each(function(){
        var price = parseFloat($(this).find('.price').val()) || 0;
        var qty = parseFloat($(this).find('.quantity').val()) || 0;
        var discount = parseFloat($(this).find('.discount').val()) || 0;
        var tax = parseFloat($(this).find('.tax').val()) || 0;
        totalProductsPrice += price * qty;
        totalDiscount += discount * qty;
        totalTax += tax * qty;
      });
      $('#totalProductsPrice').text(totalProductsPrice.toFixed(2));
      $('#totalDiscount').text(totalDiscount.toFixed(2));
      $('#totalTax').text(totalTax.toFixed(2));
      $('#grandTotal').text((totalProductsPrice - totalDiscount + totalTax).toFixed(2));
    }

    // Payment method change
    $('input[name="payment_method"]').change(function() {
      var method = $(this).val();
      var content = '';
      if (method == 'credit') {
        content += '<div class="form-group"><label>تاريخ السداد:</label><input type="date" id="paymentDueDate" class="form-control"></div>';
      } else if (method == 'cash' || method == 'network') {
        content += '<div class="form-group"><label>رفع صورة (اختياري):</label><input type="file" id="paymentImage" class="form-control"></div>';
        content += '<div class="form-group"><label>مركز التكلفة (اختياري):</label><select id="costCenter" class="form-control"><option value="">-- اختر مركز التكلفة --</option><option value="1">مركز 1</option><option value="2">مركز 2</option></select></div>';
      }
      $('#paymentContent').html(content);
      $('#paymentDetailsModal').modal('show');
    });

    // Save payment details
    $('#savePaymentDetails').on('click', function(){
      $('#paymentDetailsModal').modal('hide');
    });

    // Toggle direction
    $(".direction-toggle").on("click", function () {
      let currentDir = $("html").attr("dir");
      if (currentDir === "rtl") {
        $("html").attr("dir", "ltr");
        $(this).find("span").text("Toggle RTL");
      } else {
        $("html").attr("dir", "rtl");
        $(this).find("span").text("Toggle LTR");
      }
    });

    // Add new product row
    $('#addRow').on('click', function(){
      var newRow = $('#invoiceItems tr:first').clone();
      newRow.find('input').each(function(){
        $(this).val('');
      });
      newRow.find('select.product-select').val(null).trigger('change');
      newRow.find('.quantity').val('1');
      $('#invoiceItems').append(newRow);
    });
  });

  // addToCart function for when a product is added to the cart
  function addToCart(form_id, type) {
    let productId = form_id;
    let productQty = $('#product_qty').val();

    // Get the selected unit (0 or 1)
    let selectedUnit = $('#unit_type').val();
    console.log('Selected Unit in addToCart:', selectedUnit);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.post({
        url: '{{ url('admin/pos/add-to-cart') }}/' + type,
        data: {
            _token: '{{ csrf_token() }}',
            id: productId,
            quantity: productQty,
            unit: selectedUnit,
        },
        beforeSend: function () {
            $('#cartloader').removeClass('d-none');
        },
        success: function (data) {
            if (data.qty == 0) {
                toastr.warning('{{ \App\CPU\translate('product_quantity_end!') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            } else {
                toastr.success('{{ \App\CPU\translate('تم اضافة المنتج للعربة!') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
            $('#cart').empty().html(data.view);
            if (data.user_type === 'sc') {
                customer_Balance_Append(data.user_id);
            }
            $('#search').val('').focus();
            $('#search-box').addClass('d-none');
        },
        complete: function () {
            $('#cartloader').addClass('d-none');
        }
    });
    $('#barcodeInput').on('input', function () {
      console.log('Scanned Barcode:', $(this).val()); 
    });
  }
</script>
