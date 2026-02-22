<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="viewport" content="width=device-width">
    <!-- Title -->
    <title>Add to cart page</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="">
    <!-- Font -->
            <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/fonts/Bahij_TheSansArabic-Plain.ttf">

    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/google-fonts.css">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/vendor/icon-set/style.css">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/theme.minc619.css?v=1.0">
    @stack('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/pos.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/toastr.css">
</head>
@if(request()->route('type') != 1)
<style>
@font-face {
    font-family: 'Bahij';
    src: url("{{ asset('public/assets/admin/css/fonts/Bahij_TheSansArabic-Plain.ttf') }}") format('truetype');
    font-weight: normal;
    font-style: normal;
}
.direction-toggle {
    background: #161853;
    color: #ffffff;
    padding: 8px 0;
    -webkit-padding-end: 18px;
    padding-inline-end: 18px;
    -webkit-padding-start: 10px;
    padding-inline-start: 10px;
    cursor: pointer;
    position: fixed;
    top: 30%;
    border-radius: 5px;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all ease 0.3s;
    white-space: nowrap;
    inset-inline-start: 100%;
    transform: translateX(calc(-100% + 3px));
}


    body{
            font-family: 'Bahij', sans-serif;
    font-weight: 150;
    background:rgba(173, 216, 230, 0.2);

        color: black;
    }
</style>
<!-- تحسينات الـ CSS -->
<style>
    /* تنسيق التبويبات داخل Grid */
    .category-tab {
        display: block;
        text-align: center;
        padding: 10px;
        font-size: 14px;
        border-radius: 8px;
    }

    /* تكبير حجم الأيقونات */
    .tio-search {
        font-size: 18px;
    }

    /* ترتيب المحتوى ليتناسب مع التصميم */
    .card-title {
        font-weight: bold;
        font-size: 18px;
    }
</style>

<body class="footer-offset">
    <!-- Toggler -->
    <div class="direction-toggle">
        <i class="tio-settings"></i>
        <span></span>
    </div>
    <!-- Toggler -->
    {{--loader--}}
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div id="loading" class="d-none">
                    <div class="style-i1">
                        <img width="200" src="{{asset('public/assets/admin/img/loader.gif')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--loader--}}

    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper">
                <!-- Logo Div-->
                @php($shop_logo=\App\Models\BusinessSetting::where('key','shop_logo')->first()->value)
                <a class="navbar-brand pt-0 pb-0" href="{{route('admin.dashboard')}}" aria-label="Front">
                    <img class="navbar-brand-logo w-i1"
                        onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                        src="{{asset('storage/app/public/shop/'.$shop_logo)}}"
                        alt="Logo">
                </a>
            </div>

            <!-- Secondary Content -->
            <div class="navbar-nav-wrap-content-right">
                <!-- Navbar -->
                <ul class="navbar-nav align-items-center flex-row">
                    <li class="nav-item d-sm-inline-block">
                        <!-- short cut key -->
                        <div class="hs-unfold">
                            <a id="short-cut" class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                            data-toggle="modal" data-target="#short-cut-keys" title="{{\App\CPU\translate('مفاتيح للتسهيل')}}">
                                <i class="tio-keyboard"></i>

                            </a>
                        </div>
                        <!-- End short cut key -->
                    </li>
                    <li class="nav-item d-sm-inline-block">
                        <!-- Notification -->
                        <!--<div class="hs-unfold">-->
                            <!--<a data-toggle="tooltip" class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"-->
                            <!--href="{{route('admin.pos.orders')}}" target="_blank" title="{{\App\CPU\translate('قائمة الطلبات')}}">-->
                            <!--    <i class="tio-shopping-basket"></i>-->
                            <!--    {{--<span class="btn-status btn-sm-status btn-status-danger"></span>--}}-->
                            <!--</a>-->
                        <!--    <div class="tooltip bs-tooltip-top" role="tooltip">-->
                        <!--        <div class="arrow"></div>-->
                        <!--        <div class="tooltip-inner"></div>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!-- End Notification-->
                    </li>

                    <li class="nav-item">
                        <!-- Account -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;"
                            data-hs-unfold-options='{
                                        "target": "#accountNavbarDropdown",
                                        "type": "css-animation"
                                    }'>
                                <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img"
                                        onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                        src="{{asset('storage/app/public/admin')}}/{{auth('admin')->user()->image}}"
                                        alt="Image">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                            </a>

                            <div id="accountNavbarDropdown"
                                class="w-i2 hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img"
                                                onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                                src="{{asset('storage/app/public/admin')}}/{{auth('admin')->user()->image}}"
                                                alt="Owner image">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{auth('admin')->user()->f_name}}</span>
                                            <span class="card-text">{{auth('admin')->user()->email}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="javascript:" onclick="Swal.fire({
                                    title: 'Do you want to logout?',
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonColor: '#FC6A57',
                                    cancelButtonColor: '#363636',
                                    confirmButtonText: `Yes`,
                                    denyButtonText: `Don't Logout`,
                                    }).then((result) => {
                                    if (result.value) {
                                    location.href='{{route('admin.auth.logout')}}';
                                    } else{
                                    Swal.fire('Canceled', '', 'info')
                                    }
                                    })">
                                    <span class="text-truncate pr-2"
                                        title="Sign out">{{\App\CPU\translate('sign_out')}}</span>
                                </a>
                            </div>
                        </div>
                        <!-- End Account -->
                    </li>
                </ul>
                <!-- End Navbar -->
            </div>
            <!-- End Secondary Content -->
        </div>
    </header>

<main id="content" role="main" class="main pointer-event">
  <input type="hidden" id="barcodeInput" style="position:absolute; left:-9999px;" autofocus>
  
  <!-- Content Section -->
  <section class="section-content pt-5" style="background: linear-gradient(to right, #fdfbfb, #ebedee);">
    <div class="container-fluid">
      <!-- المنتجات والبحث -->
      <div class="row">
        <!-- قسم المنتجات -->
        <div class="col-12">
          <div class="card shadow-lg mb-4" style="border-radius: 10px; overflow: hidden;">
            <div class="card-header bg-gradient-primary text-white py-3">
              <h5 class="mb-0">{{ \App\CPU\translate('قسم المنتجات') }}</h5>
            </div>
            <div class="card-body">
              <!-- تبويبات الفئات -->
              <div class="row mb-3">
                <!-- تبويب "كل الأقسام" -->
                <div class="col-md-3 col-6 mb-2">
                  <a class="category-tab btn btn-primary w-100 text-white active" onclick="set_category_filter('')">
                    {{ \App\CPU\translate('كل الأقسام') }}
                  </a>
                </div>
                <!-- الأقسام الديناميكية -->
                @foreach ($categories as $item)
                  <div class="col-md-3 col-6 mb-2">
                    <a class="category-tab btn btn-primary w-100 text-white" 
                       onclick="set_category_filter('{{ $item['id'] }}')"
                       style="{{ $category == $item->id ? 'background-color: #d32f2f; border-color:#d32f2f;' : '' }}">
                      {{ $item->name }}
                    </a>
                  </div>
                @endforeach
              </div>
              
              <!-- صندوق البحث -->
              <div class="row mb-4">
                <div class="col-sm-6">
                  <form>
                    <div class="input-group input-group-merge">
                      <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0">
                          <i class="tio-search" style="color:#007bff;"></i>
                        </span>
                      </div>
                      <input id="search" autocomplete="off" type="text" name="search" class="form-control border-left-0"
                             placeholder="{{ \App\CPU\translate('search_by_code_or_name') }}" aria-label="Search here">
                    </div>
                    <div class="pos-search-card position-absolute w-100 z-index-1" style="top: 100%; left:0;">
                      <div id="search-box" class="card card-body search-result-box d--none"></div>
                    </div>
                  </form>
                </div>
              </div>
              
              <!-- عرض المنتجات -->
              <div class="card-body pt-2" id="items">
                <div class="row pos-item-wrap">
                  @foreach($products as $product)
                    @include('admin-views.pos._single_product', ['product' => $product])
                  @endforeach
                  @if(count($products) == 0)
                    <div class="col-12 text-center py-4">
                      <img class="mb-3 w-50" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="No Data">
                      <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
                    </div>
                  @endif
                </div>
                <!-- الترقيم -->
                <div class="table-responsive mt-4">
                  <div class="d-flex justify-content-end px-4">
                    {!! $products->withQueryString()->links() !!}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- End Products & Search -->
      
      @if(request()->route('type') == 1)
        <!-- For route type 1: display Customer Data and Billing side by side -->
        <div class="row">
          <!-- Customer Data Column -->
          <!-- Billing Section Column -->
          <div class="col-md-6">
            @php($customers = \App\Models\Customer::get())
            <div class="card shadow-lg billing-section-wrap mb-4" style="border-radius: 10px;">
              <div class="card-header bg-gradient-secondary text-white py-3">
                <h5 class="mb-0">{{ \App\CPU\translate('الفاتورة') }}</h5>
              </div>
              <div class="card-body">
                <div class="row mb-3 align-items-center">
                  <div class="col-md-8">
                    <select id="customer" name="customer_id" class="form-control js-data-example-ajax" onchange="customer_change(this.value);">
                      <option>{{ \App\CPU\translate('--اختار عميل--') }}</option>
                      <option value="0">{{ \App\CPU\translate('walking_customer') }}</option>
                    </select>
                  </div>
                  <div class="col-md-4 text-right">
                    <button class="btn btn-primary" id="add_new_customer" type="button" data-toggle="modal" data-target="#add-customer" title="Add Customer">
                      <i class="tio-add"></i> {{ \App\CPU\translate('العميل') }}
                    </button>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="d-block text-uppercase" style="font-size: 0.9rem;">
                    {{ \App\CPU\translate('العميل المختار') }}:
                    <span class="font-weight-bold" id="current_customer"></span>
                  </label>
                </div>
                <!-- عربة الفاتورة -->
                <div class="row mb-3 align-items-center">
                  <div class="col-md-8">
                    <select id="cart_id" name="cart_id" class="form-control js-select2-custom" onchange="cart_change(this.value);"></select>
                  </div>
                  <div class="col-md-4 text-right">
                    <a class="btn btn-danger" style="background-color: #c62828;" href="{{ route('admin.pos.clear-cart-ids') }}">
                      {{ \App\CPU\translate('تنضيف العربة') }}
                    </a>
                  </div>
                </div>
                <!-- Loader -->
                <div class="row">
                  <div class="col-12 text-center">
                    <div id="cartloader" class="d-none">
                      <img width="50" src="{{ asset('public/assets/admin/img/loader.gif') }}" alt="Loading...">
                    </div>
                  </div>
                </div>
                <!-- عرض العربة -->
                <div id="cart">
                  @include('admin-views.pos._cart', ['cart_id' => $cart_id, 'type' => $type])
                </div>
              </div>
            </div>
          </div>
        </div>
      @else
        <!-- For route type not equal to 1: stack Customer Data and Billing vertically -->
        <div class="row">
          <div class="col-12">
            <div class="card mb-4" id="customerDataCard" style="border-radius: 10px;">
              <div class="card-header bg-dark text-white">
                {{ \App\CPU\translate('بيانات العميل') }}
              </div>
              <div class="card-body" id="customerDetails">
                <!-- سيتم تعبئة بيانات العميل هنا -->
              </div>
            </div>
          </div>
        </div>
        <div class="row mt-5">
          <div class="col-12">
            @php($customers = \App\Models\Customer::get())
            <div class="card shadow-lg billing-section-wrap mb-4" style="border-radius: 10px;">
              <div class="card-header bg-gradient-secondary text-white py-3">
                <h5 class="mb-0">{{ \App\CPU\translate('الفاتورة') }}</h5>
              </div>
              <div class="card-body">
                <div class="row mb-3 align-items-center">
                  <div class="col-md-8">
                    <select id="customer" name="customer_id" class="form-control js-data-example-ajax" onchange="customer_change(this.value);">
                      <option>{{ \App\CPU\translate('--اختار عميل--') }}</option>
                      <option value="0">{{ \App\CPU\translate('walking_customer') }}</option>
                    </select>
                  </div>
                  <div class="col-md-4 text-right">
                    <button class="btn btn-primary" id="add_new_customer" type="button" data-toggle="modal" data-target="#add-customer" title="Add Customer">
                      <i class="tio-add"></i> {{ \App\CPU\translate('العميل') }}
                    </button>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="d-block text-uppercase" style="font-size: 0.9rem;">
                    {{ \App\CPU\translate('العميل المختار') }}:
                    <span class="font-weight-bold" id="current_customer"></span>
                  </label>
                </div>
                <!-- عربة الفاتورة -->
                <div class="row mb-3 align-items-center">
                  <div class="col-md-8">
                    <select id="cart_id" name="cart_id" class="form-control js-select2-custom" onchange="cart_change(this.value);"></select>
                  </div>
                  <div class="col-md-4 text-right">
                    <a class="btn btn-danger" style="background-color: #c62828;" href="{{ route('admin.pos.clear-cart-ids') }}">
                      {{ \App\CPU\translate('تنضيف العربة') }}
                    </a>
                  </div>
                </div>
                <!-- Loader -->
                <div class="row">
                  <div class="col-12 text-center">
                    <div id="cartloader" class="d-none">
                      <img width="50" src="{{ asset('public/assets/admin/img/loader.gif') }}" alt="Loading...">
                    </div>
                  </div>
                </div>
                <!-- عرض العربة -->
                <div id="cart">
                  @include('admin-views.pos._cart', ['cart_id' => $cart_id, 'type' => $type])
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif           
    </div>
  </section>
  
  <!-- مودال العرض السريع -->
  <div class="modal fade" id="quick-view" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content" id="quick-view-modal">
        <!-- سيتم تحميل المحتوى هنا -->
      </div>
    </div>
  </div>
  
  <!-- مودال طباعة الفاتورة -->
  @php($order = \App\Models\Order::find(session('اخر فاتورة مبيعات')))
  @if($order)
    @php(session(['last_order' => false]))
    <div class="modal fade" id="print-invoice" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 10px;">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">{{ \App\CPU\translate('اطبع فاتورة') }}</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body row font-i1">
            <div class="col-12 text-center mb-3">
              <input id="print_invoice" type="button" class="btn btn-primary non-printable"
                     onclick="printDiv('printableArea')"
                     value="Proceed, If thermal printer is ready."/>
              <a id="invoice_close" onclick="location.href='{{ url()->previous() }}'" class="btn btn-danger non-printable">
                {{ \App\CPU\translate('عودة') }}
              </a>
            </div>
            <hr class="non-printable">
            <div class="col-12" id="printableArea">
              @include('admin-views.pos.order.invoice')
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
</main>

<!-- JS Front -->
<script src="{{asset('public/assets/admin')}}/js/vendor.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/theme.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/sweet_alert.js"></script>
<script src="{{asset('public/assets/admin')}}/js/toastr.js"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        "use strict";
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
<script>
    function customer_change(customerId) {
    if (customerId != 0) {
        // AJAX request to fetch customer data by ID
        fetch(`customer/getCustomerData/${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Customer not found!');
                } else {
                    // Populate the modal with the customer data
                    document.getElementById('customer_name').textContent = data.name || '-';
                    document.getElementById('customer_email').textContent = data.email || '-';
                    document.getElementById('customer_phone').textContent = data.phone || '-';
                    
                    // Show the modal
                    var myModal = new bootstrap.Modal(document.getElementById('customerModal'));
                    myModal.show();
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

// Optional: If you want to open the modal via the button directly (outside of selection)
document.getElementById('show_customer_change').addEventListener('click', function() {
    var customerId = document.getElementById('customer').value;
    customer_change(customerId);  // Trigger the function on button click
});

</script>
<script>
    $(document).on('change', '.unit_type', function () {
        // Get the selected value and product ID
        let unit = $(this).val();
        let productId = $(this).data('product-id');

        // Print the product_id and unit to the console
        console.log('Selected product ID:', productId);
        console.log('Selected unit:', unit);

        // Make an AJAX request to update the unit
        $.ajax({
url: `{{ route('admin.pos.update.unit', ['type' => request()->route('type')]) }}`, // Pass the route parameter 'type' dynamically
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}', // Include CSRF token
                product_id: productId,
                unit: unit,
            },
            beforeSend: function () {
                console.log('Updating unit...');
            },
            success: function (response) {
                console.log('Unit updated successfully', response);

                // Example of updating a part of the page with the new data
                // Assuming the response contains the updated product details

                // Update the product unit or other details on the page
                // This will depend on the structure of your HTML and response data
                $('#product-unit-' + productId).text(response.updatedUnit); // Example: update unit in a specific element
                $('#product-price-' + productId).text(response.updatedPrice); // Example: update price
                // You can add more updates based on the response data
            },
            error: function (xhr, status, error) {
                console.error('Failed to update unit:', error);
            }
        });
    });
</script>


<script>
    $(document).on('ready', function () {
        "use strict";
        
        $('.js-hs-unfold-invoker').each(function () {
            new HSUnfold($(this)).init();
        });
        
        $('#type').val('{{ request()->route('type') }}'); // Set initial value from the route

        $.ajax({
url: `{{ url('admin/pos/get-cart-ids') }}/${$('#type').val()}`,
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#loading').removeClass('d-none');
            },
            success: function (data) {
                populateDropdown('#cart_id', data.cart_nam, data.current_user);
                $('#current_customer').text(data.current_customer);
                $('#cart').empty().html(data.view);

                if (data.user_type === 'sc') {
                    customer_Balance_Append(data.user_id);
                }
            },
            error: function (xhr, status, error) {
                console.error(`Error: ${error}`);
                alert('Failed to load cart data. Please try again.');
            },
            complete: function () {
                $('#loading').addClass('d-none');
            },
        });

        function populateDropdown(elementId, items, selectedItem) {
            let output = '';
            items.forEach(item => {
                output += `<option value="${item}" ${item === selectedItem ? 'selected' : ''}>${item}</option>`;
            });
            $(elementId).html(output);
        }
    });
</script>


<script>
    $(document).on('ready', function(){

        $(".direction-toggle").on("click", function () {
            setDirection(localStorage.getItem("direction"));
        });

        function setDirection(direction) {
            if (direction == "rtl") {
                localStorage.setItem("direction", "ltr");
                $("html").attr('dir', 'ltr');
            $(".direction-toggle").find('span').text('Toggle RTL')
            } else {
                localStorage.setItem("direction", "rtl");
                $("html").attr('dir', 'rtl');
            $(".direction-toggle").find('span').text('Toggle LTR')
            }
        }

        if (localStorage.getItem("direction") == "rtl") {
            $("html").attr('dir', "rtl");
            $(".direction-toggle").find('span').text('Toggle LTR')
        } else {
            $("html").attr('dir', "ltr");
            $(".direction-toggle").find('span').text('Toggle RTL')
        }

    })
</script>
<!-- JS Plugins Init. -->
<script src="{{asset('public/assets/admin')}}/js/pos.js"></script>

<script>
    "use strict";

    // Ensure the hidden input has the correct value on page load
    $(document).ready(function() {
        $('#type').val('{{ request()->route('type') }}'); // Set initial value from the route
    });

function payment_option(val) {
    let selectedPaymentType = $(val).val(); // Get the selected payment type (0, 1, or 2)

    // Update the hidden input field for `type` with the selected value
    $('#payment_type').val(selectedPaymentType);
    $('#type').val('{{ request()->route('type') }}'); // Set initial value from the route

    let customerId = $('#customer').val(); // Get the customer ID (if applicable)

    // Handle different payment option behaviors based on the selection
    if (selectedPaymentType == 0) {
        // Hide everything
        $("#collected_cash").addClass('d-none');
        $("#returned_amount").addClass('d-none');
        $("#transaction_ref").addClass('d-none');
        $("#date").addClass('d-none');
        $("#balance").addClass('d-none');
        $("#remaining_balance").addClass('d-none');
        $('#cash_amount').attr('required', false);
    } else if (selectedPaymentType == 1) {
        // Cash
        $('#type').val('{{ request()->route('type') }}'); // Set initial value from the route
        $("#collected_cash").removeClass('d-none');
        $("#returned_amount").removeClass('d-none');
        $("#transaction_ref").addClass('d-none');
        $("#date").addClass('d-none');
        $("#balance").addClass('d-none');
        $("#remaining_balance").addClass('d-none');
        $('#cash_amount').attr('required', true);
    } else if (selectedPaymentType == 2) {
        // Installment
        $('#type').val('{{ request()->route('type') }}'); // Set initial value from the route
        $("#collected_cash").addClass('d-none');
        $("#returned_amount").addClass('d-none');
        $("#balance").addClass('d-none');
        $("#remaining_balance").addClass('d-none');
        $("#transaction_ref").removeClass('d-none');
        $("#date").removeClass('d-none');
        $('#cash_amount').attr('required', false);
    }
}
</script>
<script>    "use strict";    function customer_change(val) {
        //let  cart_id = $('#cart_id').val();
        $.post({
                url: '{{route('admin.pos.remove-coupon')}}',
                data: {
                    _token: '{{csrf_token()}}',
                    //cart_id:cart_id,
                    user_id:val
                },
                beforeSend: function () {
                    $('#loading').removeClass('d-none');
                },
                success: function (data) {
                    console.log(data);
                    var output = '';
                    for(var i=0; i<data.cart_nam.length; i++) {
                        output += `<option value="${data.cart_nam[i]}" ${data.current_user==data.cart_nam[i]?'selected':''}>${data.cart_nam[i]}</option>`;
                    }
                    $('#cart_id').html(output);
                    $('#current_customer').text(data.current_customer);
                    $('#cart').empty().html(data.view);
                    customer_Balance_Append(val);
                },
                complete: function () {
                    $('#loading').addClass('d-none');
                }
            });
    }
</script>

<script>
    "use strict";
    function cart_change(val)
    {
        let  cart_id = val;
        let url = "{{route('admin.pos.change-cart')}}"+'/?cart_id='+val;
        document.location.href=url;
    }
</script>

<script>
    "use strict";
function extra_discount() {
    let discount = $('#dis_amount').val();
    let type = $('#type_ext_dis').val();

    if (discount) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.post({
            url: '{{ route('admin.pos.discount') }}',
            data: {
                _token: '{{csrf_token()}}',
                discount: discount,
                type: type
            },
            beforeSend: function () {
                $('#loading').removeClass('d-none');
            },
            success: function (data) {
                if (data.extra_discount === 'success') {
                    toastr.success('{{ \App\CPU\translate('الخصم اضافة بنجاح') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                } else if (data.extra_discount === 'empty') {
                    toastr.warning('{{ \App\CPU\translate('العربة فارغة') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                } else {
                    toastr.warning('{{ \App\CPU\translate('الخصم اكبر من الفاتورة') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }

                $('.modal-backdrop').addClass('d-none');
                $('#cart').empty().html(data.view);

                if (data.user_type === 'sc') {
                    console.log('after add');
                    customer_Balance_Append(data.user_id);
                }
                $('#search').focus();
            },
            complete: function () {
                $('.modal-backdrop').addClass('d-none');
                $(".footer-offset").removeClass("modal-open");
                $('#loading').addClass('d-none');
            },
            error: function (xhr) {
                console.error("Error:", xhr.responseJSON.message);
                toastr.error('{{ \App\CPU\translate('حدث خطأ في العملية') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });
    }
}
</script>
<script>
    "use strict";
 function coupon_discount() {
    // Set the initial value for 'type' input from the route
    $('#type').val('{{ request()->route('type') }}');

    let coupon_code = $('#coupon_code').val();
    let type = $('#type').val(); // Get the current type value

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.post({
        url: '{{ route('admin.pos.coupon-discount', ['type' => '__TYPE__']) }}'.replace('__TYPE__', type),
        data: {
            _token: '{{ csrf_token() }}',
            coupon_code: coupon_code
        },
        beforeSend: function () {
            $('#loading').removeClass('d-none');
        },
        success: function (data) {
            console.log(data);

            if (data.coupon === 'success') {
                toastr.success('{{ \App\CPU\translate('coupon_added_successfully') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            } else if (data.coupon === 'amount_low') {
                toastr.warning('{{ \App\CPU\translate('this_discount_is_not_applied_for_this_amount') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            } else if (data.coupon === 'cart_empty') {
                toastr.warning('{{ \App\CPU\translate('your_cart_is_empty') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            } else {
                // Update the type field and show success message
                $('#type').val('{{ request()->route('type') }}');
                toastr.success('{{ \App\CPU\translate('تم تحديث الفاتورة بنجاح') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
                console.log($('#type').val());
            }

            // Update cart view
            $('#cart').empty().html(data.view);

            // Update balance if user type is 'sc'
            if (data.user_type === 'sc') {
                console.log('after add');
                customer_Balance_Append(data.user_id);
            }

            // Set focus back to search
            $('#search').focus();
        },
        complete: function () {
            $('.modal-backdrop').addClass('d-none');
            $(".footer-offset").removeClass("modal-open");
            $('#loading').addClass('d-none');
        }
    });
}

</script>
<script>
    "use strict";
    $(document).on('ready', function () {
        @if($order)
        $('#print-invoice').modal('show');
        @endif
    });

    function set_category_filter(id) {
        var nurl = new URL('{!!url()->full()!!}');
        nurl.searchParams.set('category_id', id);
        location.href = nurl;
    }

    $('#search-form').on('submit', function (e) {
        e.preventDefault();
        var keyword = $('#datatableSearch').val();
        var nurl = new URL('{!!url()->full()!!}');
        nurl.searchParams.set('keyword', keyword);
        location.href = nurl;
    });

    function quickView(product_id) {
        //console.log(product_id);
        $.ajax({
            url: '{{route('admin.pos.quick-view')}}',
            type: 'GET',
            data: {
                product_id: product_id
            },
            dataType: 'json', // added data type
            beforeSend: function () {
                $('#loading').removeClass('d-none');
                //console.log("loding");
            },
            success: function (data) {
                //console.log("success...");
                //console.log(data);

                // $("#quick-view").removeClass('fade');
                // $("#quick-view").addClass('show');

                $('#quick-view').modal('show');
                $('#quick-view-modal').empty().html(data.view);
            },
            complete: function () {
                $('#loading').addClass('d-none');
            },
        });
    }

$('.unit_type').on('change', function() {
    var selectedUnit = this.value;  // Get the selected value (0 or 1)
    console.log('Selected Unit:', selectedUnit);
});


function addToCart(form_id, type) {
    let productId = form_id;
    let productQty = $('#product_qty').val();

    // Get the selected unit after the change event happens
    let selectedUnit = $('#unit_type').val();  // Get the selected unit value (0 or 1)

    console.log('Selected Unit in addToCart:', selectedUnit); // Log the selected unit to make sure it's correctly fetched

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.post({
        url: '{{ url('admin/pos/add-to-cart') }}/' + type,  // Pass the type dynamically
        data: {
            _token: '{{ csrf_token() }}',
            id: productId,
            quantity: productQty,
            unit: selectedUnit,  // Send the selected unit value
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

            // Update the cart view after product is added
            $('#cart').empty().html(data.view);

            // If the user type is 'sc', call a function to update the balance
            if (data.user_type === 'sc') {
                customer_Balance_Append(data.user_id);
            }

            // Reset the search box after adding the product
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

    function removeFromCart(key) {
        // let  user_id = $('#customer').val();
        // let  cart_id = $('#cart_id').val();
        $.post('{{ route('admin.pos.remove-from-cart') }}', {_token: '{{ csrf_token() }}', key: key}, function (data) {

                $('#cart').empty().html(data.view);
                if(data.user_type === 'sc')
                {
                    console.log('after add');
                    customer_Balance_Append(data.user_id);
                }
                toastr.info('{{\App\CPU\translate('تمت ازالة المنتج من الفاتورة')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            $('#search').focus();

        });
    }

    function emptyCart() {
        Swal.fire({
            title: '{{\App\CPU\translate('Are_you_sure?')}}',
            text: '{{\App\CPU\translate('انت تريد ازالة كل المنتجات من الفاتورة!!')}}',
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#161853',
            cancelButtonText: 'No',
            confirmButtonText: 'Yes',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                // let  user_id = $('#customer').val();
                // let  cart_id = $('#cart_id').val();
                $.post('{{ route('admin.pos.emptyCart') }}', {_token: '{{ csrf_token() }}'}, function (data) {
                    $('#cart').empty().html(data.view);
                    $('#search').focus();
                    if(data.user_type === 'sc')
                    {
                        customer_Balance_Append(data.user_id);
                    }
                    toastr.info('{{\App\CPU\translate('تمت حذف منتج من الفاتورة')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                });
            }
        })

    }

    function updateCart() {
        $.post('<?php echo e(route('admin.pos.cart_items')); ?>', {_token: '<?php echo e(csrf_token()); ?>'}, function (data) {
            $('#cart').empty().html(data);

        });
    }

function updateQuantity(id, qty) {
    let selectedUnit = $('#unit_type').val(); // التقاط قيمة الوحدة المختارة
    let type = '{{ request("type") }}'; // جلب type من الـ Route

    console.log("Selected Unit:", selectedUnit);
    console.log("Type from Route:", type);

    if (!type) {
        console.error("Error: Type is not defined from Route!");
        return;
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') }
    });

    let updateUrl = '{{ route("admin.pos.updateQuantity", ["type" => "__type__"]) }}'.replace('__type__', type);

    $.post({
        url: updateUrl,
        data: {
            _token: '{{ csrf_token() }}',
            key: id,
            quantity: qty,
            unit: selectedUnit
        },
        beforeSend: function() {
            $('#loading').removeClass('d-none'); // إظهار اللودر
        },
        success: function(data) {
            if (data.error) {
                toastr.error(data.error, { CloseButton: true, ProgressBar: true });
            } else {
                $('#cart .cart-items').html(data.view);

                if (data.user_type === 'sc') {
                    customer_Balance_Append(data.user_id);
                }

                setTimeout(function() {
                    location.reload();
                }, 5000);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error:", error);
            console.error("Response Text:", xhr.responseText);
            console.error("Status Code:", xhr.status);

            if (xhr.status === 500) {
                toastr.error("خطأ داخلي في الخادم! " + xhr.responseText, { CloseButton: true, ProgressBar: true });
            } else {
                toastr.error("حدث خطأ أثناء تحديث الكمية! " + xhr.statusText, { CloseButton: true, ProgressBar: true });
            }
        },
        complete: function() {
            $('#loading').addClass('d-none'); // إخفاء اللودر
        }
    });
}

    // INITIALIZATION OF SELECT2
    // =======================================================
    $('.js-select2-custom').each(function () {
        var select2 = $.HSCore.components.HSSelect2.init($(this));
    });

$('.js-data-example-ajax').select2({
    ajax: {
        url: function () {
            // Append 'type' dynamically to the route URL
            let type = $('#type').val() || '{{ request()->route("type") }}';
            return '{{ route("admin.pos.customers", ":type") }}'.replace(':type', type);
        },
        data: function (params) {
            return {
                q: params.term, // Search term
                page: params.page // Pagination page number
            };
        },
        processResults: function (data) {
            return {
                results: data
            };
        },
        __port: function (params, success, failure) {
            var $request = $.ajax(params);

            $request.then(success);
            $request.fail(failure);

            return $request;
        }
    }
});

// Set the initial value of '#type' from the route
$(document).ready(function () {
    $('#type').val('{{ request()->route('type') }}');
});

    jQuery(".search-bar-input").on('keyup',function () {
        //$('#search-box').removeClass('d-none');
        $(".search-card").removeClass('d-none').show();
        let name = $(".search-bar-input").val();
        //console.log(name);
        if (name.length >0) {
            $('#search-box').removeClass('d-none').show();
            $.get({
                url: '{{route('admin.pos.search-products')}}',
                dataType: 'json',
                data: {
                    name: name
                },
                beforeSend: function () {
                    $('#loading').removeClass('d-none');
                },
                success: function (data) {
                    //console.log(data.count);
                    if (data.count == 0) {
                        $('#search-box').addClass('d-none');
                    }
                    $('.search-result-box').empty().html(data.result);
                },
                complete: function () {
                    $('#loading').addClass('d-none');
                },
            });
        } else {
            $('.search-result-box').empty();
            $('#search-box').addClass('d-none');
        }
    });

jQuery(document).ready(function () {
    // Set initial value of 'type' from the route
    $('#type').val('{{ request()->route('type') }}');

    // Search bar input with a delay
    jQuery(".search-bar-input").on('keyup', delay(function () {
        // Show the search card
        $(".search-card").removeClass('d-none').show();

        // Get the input value
        let name = $(".search-bar-input").val();
// Get the 'type' value from the route parameter rendered by Blade


        // Check if the input has valid content
        if (name.length > 0 || isNaN(name)) {
            $.get({
                url: '{{ route("admin.pos.search-by-add", ["type" => request()->route("type")]) }}',
                dataType: 'json',
                data: { name: name },
                success: function (data) {
                    if (data.count === 1) {
                        $('#search').attr("disabled", true);
                        addToCart(data.id); // Function to add the item to the cart
                        $('#search').attr("disabled", false);

                        // Update the search results box
                        $('.search-result-box').empty().html(data.result);

                        // Clear input and hide search box
                        $('#search').val('');
                        $('#search-box').addClass('d-none');
                    } else {
                        // Display multiple results if count > 1
                        $('.search-result-box').empty().html(data.result);
                    }
                },
                error: function () {
                    console.error("Error occurred while fetching search results.");
                },
            });
        } else {
            // Clear the search results box if input is empty
            $('.search-result-box').empty();
        }
    }, 1000)); // Delay of 1000ms
});

// Delay function to throttle search input
function delay(callback, ms) {
    let timer = 0;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
}

</script>
<script>
window.csrfToken = "{{ csrf_token() }}";
window.type = "{{ request()->route('type') }}";
</script>

<script>
window.scanBarcode = function (barcode) {
    console.log('Executing scanBarcode with:', barcode); // تأكيد تشغيل الدالة

    $.post({
        url: '/admin/pos/add-to-cart/barcode/' + window.type, // استخدم القيمة من Blade
        data: {
            _token: window.csrfToken,
            barcode: barcode
        },
        beforeSend: function () {
            $('#cartloader').removeClass('d-none');
        },
        success: function (data) {
            if (data.qty === 0) {
                toastr.warning('🚨 المنتج غير متوفر!');
            } else {
                toastr.success('✅ تم إضافة المنتج للعربة!');
            }
            $('#cart').empty().html(data.view);
        },
        complete: function () {
            $('#cartloader').addClass('d-none');
        }
    });
};

$(document).ready(function () {
    let barcode = '';

    $('#barcodeInput').focus();

    $('#barcodeInput').on('input', function () {
        barcode = $(this).val().trim();
        console.log('Scanned Barcode:', barcode);

        if (barcode.length > 0) {
            if (typeof window.scanBarcode === 'function') {
                window.scanBarcode(barcode);
            } else {
                console.error("❌ Error: scanBarcode is not defined!");
            }
            $(this).val('');
        }
    });

    $(document).on('click', function () {
        $('#barcodeInput').focus();
    });
});
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

</script>

@stack('script_2')
@endif
</body>
</html>
