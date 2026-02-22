@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_expense'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
<style>
    .form-section{margin-bottom:1.5rem;padding:1.5rem;background:#fff;border-radius:8px}
    .form-section .section-title{font-size:1.2rem;font-weight:300;margin-bottom:1rem;border-bottom:2px solid #001B63;padding-bottom:.5rem}
    label{font-weight:300;margin-bottom:.3rem;display:block}
    .required::after{content:" *";color:red}
    .form-control{border:1px solid #ced4da;border-radius:4px;padding:.5rem .75rem;transition:border-color .3s}
    .form-control:focus{border-color:#001B63}
    .btn-primary{background:#001B63;border-color:#001B63;font-weight:700}
    .btn-primary:hover{background:#001a70;border-color:#001a70}
    .card{border:none;box-shadow:0 2px 6px rgba(0,0,0,.1);border-radius:8px;overflow:hidden;margin-bottom:1.5rem}
    .card-body{padding:1.5rem;background:#fff}
    .toggle-btns{margin-bottom:1rem}
    .toggle-btns button{width:32%;margin-inline-end:2%}
    .toggle-btns button:last-child{margin-inline-end:0}
    .row{display:flex;flex-wrap:wrap;margin-inline-end:0;margin-inline-start:0}

    /* تنبيهات مركز التكلفة */
    .hint-required{font-size:.8rem;color:#b91c1c;display:none;margin-top:.25rem}
    .is-required{border-color:#ef4444!important; box-shadow:0 0 0 .08rem rgba(239,68,68,.25)}
</style>
@endpush

@section('content')
@php
    $isReceipt = in_array((string)$type, ['100','200']);
    $shopNameFromSettings = \App\Models\BusinessSetting::where('key','shop_name')->value('value');
@endphp

<div class="content container-fluid">
    <!-- Header -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                        <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                    </a>
                </li>
                @if($isReceipt)
                    <li class="breadcrumb-item">
                        <a href="#" class="text-primary">{{ \App\CPU\translate('إضافة سند قبض جديد') }}</a>
                    </li>
                @else
                    <li class="breadcrumb-item">
                        <a href="#" class="text-primary">{{ \App\CPU\translate('إضافة  أصل ثابت') }}</a>
                    </li>
                @endif
            </ol>
        </nav>
    </div>
    <!-- End Header -->

    <div class="row gx-2 gx-lg-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form id="expenseForm" action="{{ route('admin.account.store-expense', [$type]) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="entry_type" id="entryTypeInput">

                        @if($type == 2)
                            <!-- أزرار التبديل لنوع القيد جوّه أصل ثابت -->
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block mb-2">{{ \App\CPU\translate('نوع القيد') }}</label>
                                <div class="d-flex flex-wrap">
                                    <button type="button" id="btnPurchaseInvoice" class="btn btn-outline-primary btn-sm" style="min-width: 120px; margin-inline-end: 10px;">
                                        {{ \App\CPU\translate('قيد أصل ثابت') }}
                                    </button>
                                    <button type="button" id="btnFixedAsset" class="btn btn-outline-primary btn-sm" style="min-width: 120px; margin-inline-end: 10px;">
                                        {{ \App\CPU\translate('أصل ثابت') }}
                                    </button>
                                    <button type="button" id="btnBoth" class="btn btn-outline-primary btn-sm" style="min-width: 120px;">
                                        {{ \App\CPU\translate('كلاهما') }}
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- قسم فاتورة الشراء / سند -->
                        <div id="purchaseInvoiceSection" style="display: {{ $type == 2 ? 'none' : 'block' }};">
                            <div class="form-section">
                                <h4 class="section-title">{{ \App\CPU\translate('بيانات السند') }}</h4>

                                <!-- صف: اسم المستفيد + طريقة الدفع -->
                                <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <label class="required">{{ \App\CPU\translate('اسم المستفيد') }}</label>
                                        <input type="text" name="payee_name" class="form-control"
                                               value="{{ $isReceipt ? ($shopNameFromSettings ?? '') : old('payee_name') }}"
                                               placeholder="{{ \App\CPU\translate('ادخل اسم المستفيد') }}">
                                    </div>

                                    <div class="col-md-6 mb-1">
                                        <label class="required">{{ \App\CPU\translate('طريقة الدفع/القبض') }}</label>
                                        <select name="payment_method" id="paymentMethodPurchase" class="form-control">
                                            <option value="cash">{{ \App\CPU\translate('نقدًا') }}</option>
                                            <option value="bank">{{ \App\CPU\translate('تحويل بنكي') }}</option>
                                            <option value="check">{{ \App\CPU\translate('شيك') }}</option>
                                            <option value="card">{{ \App\CPU\translate('بطاقة') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- صف: رقم السند + رقم الشيك -->
                                <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <label class="required">{{ \App\CPU\translate('رقم السند') }}</label>
                                        <input type="text" name="voucher_number" class="form-control" placeholder="{{ \App\CPU\translate('ادخل رقم السند') }}">
                                    </div>

                                    <div class="col-md-6 mb-1" id="chequeNumberPurchaseWrap" style="display:none;">
                                        <label class="required">{{ \App\CPU\translate('رقم الشيك') }}</label>
                                        <input type="text" name="cheque_number" class="form-control" placeholder="{{ \App\CPU\translate('ادخل رقم الشيك') }}">
                                    </div>
                                </div>

                                <!-- الحسابات -->
                                <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <div class="form-group">
                                            <label class="required">{{ \App\CPU\translate('الحساب المدين') }}</label>
                                            <select id="accountSelectTo" name="account_id_to" class="form-control js-select2-custom">
                                                <option value="">{{ \App\CPU\translate('اختار الحساب المحول له') }}</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account['id'] }}"
                                                            data-cost-center="{{ $account['cost_center'] ?? 0 }}">
                                                        {{ $account['account'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <div class="form-group">
                                            <label class="required">{{ \App\CPU\translate('الحساب الدائن') }}</label>
                                            <select id="accountSelect" name="account_id" class="form-control js-select2-custom">
                                                <option value="">{{ \App\CPU\translate('اختار الحساب') }}</option>
                                                @foreach ($accounts_to as $account)
                                                    <option value="{{ $account->id }}"
                                                            data-cost-center="{{ $account->cost_center ?? 0 }}">
                                                        {{ $account->account }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- مراكز التكلفة + المبلغ -->
                                <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <div class="form-group">
                                            <label>{{ \App\CPU\translate('مركز التكلفة') }}</label>
                                            <select name="cost_id" id="costSelect" class="form-control js-select2-custom">
                                                <option value="">{{ \App\CPU\translate('اختار مركز التكلفة') }}</option>
                                                @foreach ($costcenters as $costcenter)
                                                    <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <small id="costSelectHint" class="hint-required">{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <div class="form-group">
                                            <label class="required">{{ \App\CPU\translate('المبلغ') }}</label>
                                            <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="{{ \App\CPU\translate('ادخل المبلغ') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- الوصف + التاريخ -->
                                <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <div class="form-group">
                                            <label class="required">{{ \App\CPU\translate('الوصف') }}</label>
                                            <input type="text" name="description" class="form-control" placeholder="{{ \App\CPU\translate('ادخل الوصف') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <div class="form-group">
                                            <label class="required">{{ \App\CPU\translate('التاريخ') }}</label>
                                            <input type="date" name="date" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <!-- صورة إيصال -->
                                <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <div class="form-group">
                                            <label for="img">{{ \App\CPU\translate('تحميل صورة الإيصال') }}</label>
                                            <input type="file" name="img" id="img" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                </div>

                                @if($type!=2)
                                <!-- زر الحفظ لقسم السند لما مش أصل ثابت -->
                                <div class="row justify-content-end ps-4">
                                    <div class="col-md-3 text-end mb-2">
                                        <button id="save-button-normal" class="btn btn-primary w-100 py-3" type="submit">
                                            <span class="button-text">{{ \App\CPU\translate('حفظ') }}</span>
                                            <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>

                                    @if($isReceipt)
                                    <div class="col-md-3 text-end mb-2">
                                        <button id="fetchOrders" class="btn btn-info w-100 py-3" type="button">
                                            {{ \App\CPU\translate('جلب الفواتير') }}
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        @if($type == 2)
                        <!-- قسم بيانات الأصل الثابت -->
                        <div id="fixedAssetSection" style="display: {{ $type == 2 ? 'none' : 'block' }};">
                            <div class="form-section">
                                <h4 class="section-title">{{ \App\CPU\translate('بيانات الأصل الثابت') }}</h4>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="required">{{ \App\CPU\translate('اسم الأصل') }}</label>
                                        <input type="text" name="asset_name" class="form-control" placeholder="{{ \App\CPU\translate('ادخل اسم الأصل') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="required">{{ \App\CPU\translate('كود الاصل') }}</label>
                                        <input type="text" name="code" class="form-control" placeholder="{{ \App\CPU\translate('ادخل كود الأصل') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="required">{{ \App\CPU\translate('سعر الشراء الأساسي') }}</label>
                                        <input type="number" step="0.01" name="purchase_price" class="form-control" placeholder="{{ \App\CPU\translate('ادخل سعر الشراء الأساسي') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>{{ \App\CPU\translate('التكاليف الإضافية (شحن، تركيب، ...)') }}</label>
                                        <input type="number" step="0.01" name="additional_costs" class="form-control" placeholder="{{ \App\CPU\translate('ادخل التكاليف الإضافية') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>{{ \App\CPU\translate('القيمة المتبقية') }}</label>
                                        <input type="number" step="0.01" name="salvage_value" class="form-control" placeholder="{{ \App\CPU\translate('ادخل القيمة المتبقية') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="required">{{ \App\CPU\translate('العمر الافتراضي (بالسنوات)') }}</label>
                                        <input type="number" name="useful_life" class="form-control" placeholder="{{ \App\CPU\translate('ادخل العمر الافتراضي') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">{{ \App\CPU\translate('تاريخ بدء الاستخدام') }}</label>
                                        <input type="date" name="commencement_date" class="form-control">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="required">{{ \App\CPU\translate('طريقة الاهلاك') }}</label>
                                        <select name="depreciation_method" id="depreciation_method" class="form-control">
                                            <option value="">{{ \App\CPU\translate('-- اختر طريقة الاهلاك --') }}</option>
                                            <option value="straight_line">{{ \App\CPU\translate('القسط الثابت') }}</option>
                                            <option value="declining_balance">{{ \App\CPU\translate('الرصيد المتناقص') }}</option>
                                            <option value="units_of_production">{{ \App\CPU\translate('الإنتاج/الاستخدام') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="depreciation_rate_group" style="display:none;">
                                        <label>{{ \App\CPU\translate('معدل الاهلاك (مثلاً 200 أو 150)') }}</label>
                                        <input type="number" step="0.01" name="depreciation_rate" class="form-control" placeholder="{{ \App\CPU\translate('ادخل معدل الاهلاك') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>{{ \App\CPU\translate('رقم الفاتورة') }}</label>
                                        <input type="text" name="invoice_number" class="form-control" placeholder="{{ \App\CPU\translate('ادخل رقم الفاتورة') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">{{ \App\CPU\translate('تاريخ الشراء') }}</label>
                                        <input type="date" name="purchase_date" class="form-control">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>{{ \App\CPU\translate('الموقع / القسم') }}</label>
                                        <input type="text" name="location" class="form-control" placeholder="{{ \App\CPU\translate('مثلاً: فرع القاهرة أو قسم الصيانة') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">{{ \App\CPU\translate('حالة الأصل') }}</label>
                                        <select name="status" class="form-control" required>
                                            <option value="active">{{ \App\CPU\translate('نشط') }}</option>
                                            <option value="maintenance">{{ \App\CPU\translate('تحت الصيانة') }}</option>
                                            <option value="disposed">{{ \App\CPU\translate('تم التخلص منه') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="assetImg">{{ \App\CPU\translate('صورة الأصل (اختياري)') }}</label>
                                        <input type="file" name="asset_img" id="assetImg" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">{{ \App\CPU\translate('الفرع') }}</label>
                                        <select name="branch_id" class="form-control">
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($type!=200)
                        <div class="row justify-content-end ps-4">
                            <div class="col-md-3 text-end mb-2">
                                <button id="save-button" class="btn btn-primary w-100 py-3" type="submit">
                                    <span class="button-text">{{ \App\CPU\translate('حفظ') }}</span>
                                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>

                    <div id="ordersResult" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


<script>
    // تعطيل زر الحفظ أثناء الإرسال (للزر id="save-button")
    function disableButton(event) {
        event.preventDefault();
        const button = document.getElementById('save-button');
        if(!button) return;
        button.disabled = true;
        button.querySelector('.button-text')?.classList.add('d-none');
        button.querySelector('.spinner-border')?.classList.remove('d-none');
        button.closest('form').submit();
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== منطق "رقم الشيك" حسب طريقة الدفع =====
    const pmPurchase = document.getElementById('paymentMethodPurchase');
    const chequeWrapPurchase = document.getElementById('chequeNumberPurchaseWrap');
    if(pmPurchase && chequeWrapPurchase){
        const toggleChequePurchase = () => {
            chequeWrapPurchase.style.display = (pmPurchase.value === 'check') ? 'block' : 'none';
        };
        pmPurchase.addEventListener('change', toggleChequePurchase);
        toggleChequePurchase();
    }

    const pmAsset = document.getElementById('paymentMethodAsset');
    const chequeWrapAsset = document.getElementById('chequeNumberAssetWrap');
    if(pmAsset && chequeWrapAsset){
        const toggleChequeAsset = () => {
            chequeWrapAsset.style.display = (pmAsset.value === 'check') ? 'block' : 'none';
        };
        pmAsset.addEventListener('change', toggleChequeAsset);
        toggleChequeAsset();
    }

    // ===== طريقة الاهلاك =====
    const depreciationMethod = document.getElementById('depreciation_method');
    const rateGroup = document.getElementById('depreciation_rate_group');
    if(depreciationMethod && rateGroup){
        depreciationMethod.addEventListener('change', function() {
            rateGroup.style.display = (this.value === 'declining_balance') ? 'block' : 'none';
        });
    }

    // ===== أزرار التبديل في type=2 =====
    @if($type == 2)
        const btnPurchaseInvoice = document.getElementById('btnPurchaseInvoice');
        const btnFixedAsset = document.getElementById('btnFixedAsset');
        const btnBoth = document.getElementById('btnBoth');
        const purchaseSection = document.getElementById('purchaseInvoiceSection');
        const fixedAssetSection = document.getElementById('fixedAssetSection');

        const setBtns = (activeBtn) => {
            [btnPurchaseInvoice, btnFixedAsset, btnBoth].forEach(b => {
                if(!b) return;
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline-primary');
            });
            if(activeBtn){
                activeBtn.classList.remove('btn-outline-primary');
                activeBtn.classList.add('btn-primary');
            }
        };

        btnPurchaseInvoice?.addEventListener('click', function() {
            if(purchaseSection) purchaseSection.style.display = 'block';
            if(fixedAssetSection) fixedAssetSection.style.display = 'none';
            document.getElementById('entryTypeInput').value = 'purchase_only';
            setBtns(this);
        });

        btnFixedAsset?.addEventListener('click', function() {
            if(purchaseSection) purchaseSection.style.display = 'none';
            if(fixedAssetSection) fixedAssetSection.style.display = 'block';
            document.getElementById('entryTypeInput').value = 'asset_only';
            setBtns(this);
        });

        btnBoth?.addEventListener('click', function() {
            if(purchaseSection) purchaseSection.style.display = 'block';
            if(fixedAssetSection) fixedAssetSection.style.display = 'block';
            document.getElementById('entryTypeInput').value = 'both';
            setBtns(this);
        });

        // الضغط التلقائي على الزر الأول
        btnPurchaseInvoice?.click();
    @endif

    // ===== إجبار اختيار مركز تكلفة لو الحساب يتطلب ذلك =====
    const accountSelectTo   = document.getElementById('accountSelectTo');   // المدين
    const accountSelectFrom = document.getElementById('accountSelect');     // الدائن
    const costSelect        = document.getElementById('costSelect');
    const costHint          = document.getElementById('costSelectHint');
    const form              = document.getElementById('expenseForm');

    const requiresCC = (selectEl) => {
        if(!selectEl) return false;
        const opt = selectEl.options[selectEl.selectedIndex];
        const val = opt?.dataset?.costCenter ?? 0;
        return parseInt(val) === 1;
    };

    const setRequired = (el, required, hintEl) => {
        if(!el) return;
        el.required = required;
        if (required && !el.value) {
            el.classList.add('is-required');
            if(hintEl) hintEl.style.display = 'inline';
        } else {
            el.classList.remove('is-required');
            if(hintEl) hintEl.style.display = 'none';
        }
    };

    const updateCostCenterRequirement = () => {
        const needCC = (requiresCC(accountSelectTo) || requiresCC(accountSelectFrom));
        setRequired(costSelect, needCC, costHint);
    };

    accountSelectTo?.addEventListener('change', updateCostCenterRequirement);
    accountSelectFrom?.addEventListener('change', updateCostCenterRequirement);
    updateCostCenterRequirement(); // init

    form?.addEventListener('submit', function(e){
        const needCC = (requiresCC(accountSelectTo) || requiresCC(accountSelectFrom));
        setRequired(costSelect, needCC, costHint);
        if (needCC && !costSelect.value) {
            e.preventDefault();
            alert('{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}');
            costSelect.focus();
            return false;
        }

        // Spinner للزر العام
        const btn = document.getElementById('save-button') || document.getElementById('save-button-normal');
        if(btn){
            btn.disabled = true;
            btn.querySelector('.button-text')?.classList.add('d-none');
            btn.querySelector('.spinner-border')?.classList.remove('d-none');
        }
    });

    // ===== جلب الفواتير (للإيصالات فقط) =====
    const fetchBtn = document.getElementById('fetchOrders');
    if(fetchBtn){
        fetchBtn.addEventListener('click', function(){
            const expenseType = "{{ $type }}";
            let accountId = '';
            if(expenseType === '100'){
                accountId = document.getElementById('accountSelectTo')?.value || '';
            } else if(expenseType === '200'){
                accountId = document.getElementById('accountSelect')?.value || '';
            }
            if(accountId === ''){
                alert('{{ \App\CPU\translate('يرجى اختيار حساب') }}');
                return;
            }

            fetch("{{ route('admin.account.getOrdersByAccount') }}?account_id=" + accountId)
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('ordersResult');
                    resultDiv.innerHTML = "";
                    if(data.success){
                        if(data.orders.length > 0){
                            let html = '<h3 style="padding:10px;">{{ \App\CPU\translate('الطلبات') }}:</h3>';
                            html += "<table class='table table-bordered'>";
                            html += '<thead><tr style="background-color:#EDF2F4;color:black;">';
                            html += "<th>{{ \App\CPU\translate('فاتورة البيع') }}</th>";
                            html += "<th>{{ \App\CPU\translate('فاتورة المرتجع') }}</th>";
                            html += "<th>{{ \App\CPU\translate('المبلغ المتبقي') }}</th>";
                            html += "</tr></thead><tbody>";

                            data.orders.forEach(function(order){
                                const saleAmount   = parseFloat(order.transaction_reference) || 0;
                                const paidAmount   = parseFloat(order.order_amount) || 0;
                                const returnAmount = order.return_order ? parseFloat(order.return_order.order_amount) || 0 : 0;
                                const returnPaid   = order.return_order ? parseFloat(order.return_order.transaction_reference) || 0 : 0;
                                const diff         = paidAmount - saleAmount - returnAmount;

                                html += "<tr style='border-bottom:1px solid #ddd;'>";
                                html += "<td>";
                                html += "<strong>" + order.id + "</strong><br>";
                                html += "<small>{{ \App\CPU\translate('تاريخ البيع') }}: " + (order.created_at ?? '-') + "</small><br>";
                                html += "<small>{{ \App\CPU\translate('المبلغ') }}: " + paidAmount.toFixed(2) + "</small><br>";
                                html += "<small>{{ \App\CPU\translate('المدفوع') }}: " + saleAmount.toFixed(2) + "</small>";
                                html += "</td>";

                                html += "<td>";
                                if(order.return_order){
                                    html += "<strong>" + order.return_order.id + "</strong><br>";
                                    html += "<small>{{ \App\CPU\translate('تاريخ المرتجع') }}: " + (order.return_order.created_at ?? '-') + "</small><br>";
                                    html += "<small>{{ \App\CPU\translate('المبلغ') }}: " + returnAmount.toFixed(2) + "</small><br>";
                                    html += "<small>{{ \App\CPU\translate('المدفوع') }}: " + returnPaid.toFixed(2) + "</small>";
                                } else {
                                    html += "-";
                                }
                                html += "</td>";

                                html += "<td style='color:black;font-weight:bold;'>" + diff.toFixed(2) + "</td>";
                                html += "</tr>";
                            });

                            html += "</tbody></table>";
                            resultDiv.innerHTML = html;
                        } else {
                            resultDiv.innerHTML = "<p>{{ \App\CPU\translate('لم يتم العثور على طلبات للحساب المحدد.') }}</p>";
                        }
                    } else {
                        resultDiv.innerHTML = "<p>" + (data.message ?? '{{ \App\CPU\translate('حدث خطأ أثناء جلب البيانات.') }}') + "</p>";
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('ordersResult').innerHTML = "<p>{{ \App\CPU\translate('حدث خطأ أثناء جلب البيانات.') }}</p>";
                });
        });
    }
});
</script>
