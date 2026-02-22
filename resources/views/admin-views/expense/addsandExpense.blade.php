@extends('layouts.admin.app')

@section('title', \App\CPU\translate('اضافة سند صرف جديد'))

@push('css_or_js')
<style>
    .form-section{margin-bottom:2rem;padding:2rem;background:#fff;border:1px solid #eaeaea;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.1)}
    .section-title{font-size:1.8rem;font-weight:300;margin-bottom:1.5rem;padding-bottom:.5rem;border-bottom:3px solid #003f88;text-transform:uppercase;color:#001B63}
    label{font-weight:300;margin-bottom:.5rem;color:#333}
    .required::after{content:" *";color:#dc3545}
    .form-control{border:1px solid #ced4da;border-radius:4px;padding:.5rem;width:100%;font-size:.8rem;transition:border-color .3s ease}
    .form-control:focus{border-color:#003f88;box-shadow:0 0 0 .2rem rgba(0,27,99,.15)}
    #multipleForm .form-section td .form-control{width:300px}
    .toggle-btns{margin-bottom:1.5rem;text-align:center}
    .toggle-btns button{width:18%;margin:0 1%;border-radius:4px;padding:.55rem;font-size:1.1rem}
    .toggle-btns button.btn-outline-primary{background:#fff;border:1px solid #003f88;color:#003f88}
    #normalForm .receipt-header{text-align:center;margin-bottom:1rem;font-size:1.4rem;font-weight:bold;color:#003f88;padding-bottom:.5rem}
    #multipleForm .form-section table{width:100%;border-collapse:collapse;margin-bottom:1.5rem}
    #multipleForm .form-section td,#multipleForm .form-section th{border:1px solid #ddd;padding:1rem 1.5rem;text-align:center;white-space:nowrap}
    .table-header{background:#003f88;color:#fff;font-weight:bold;text-transform:uppercase}
    #multipleForm .form-section tbody tr:hover{background:#ffffff}
    #overallTotals p{font-size:1.1rem;font-weight:300;margin:.5rem 0;color:#000}
    .tax-header{display:table-cell}
    .hint-required{font-size:.75rem;color:#b91c1c;display:none}
    .is-required{border-color:#ef4444!important}
</style>
@endpush

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />

@section('content')
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
                <li class="breadcrumb-item">
                    <a href="#" class="text-primary">
                        {{ \App\CPU\translate('إضافة سند صرف جديد') }}
                    </a>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Toggle Buttons -->
    <div class="toggle-btns d-flex justify-content-start mb-3">
        <button type="button" id="btnNormalVoucher" class="btn btn-sm btn-primary active me-2">
            {{ \App\CPU\translate('سند صرف عادي') }}
        </button>
        <button type="button" id="btnMultipleVoucher" class="btn btn-sm btn-outline-primary">
            {{ \App\CPU\translate('سند صرف متعدد') }}
        </button>
    </div>

    <!-- Normal Expense Form -->
    <div id="normalForm">
        <form id="normalVoucherForm" action="{{ route('admin.account.storesandExpense') }}" method="post" enctype="multipart/form-data" class="form-section">
            @csrf
            <input type="hidden" name="type" value="100">

            <div class="receipt-header">{{ \App\CPU\translate('إيصال صرف') }}</div>

            {{-- رقم السند - اسم المستفيد - طريقة الدفع - رقم الشيك --}}
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="required">{{ \App\CPU\translate('رقم السند') }}</label>
                    <input type="text" name="voucher_number" id="voucher_number_normal" class="form-control" placeholder="{{ \App\CPU\translate('ادخل رقم السند أو اتركه فارغ للتلقائي') }}">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="required">{{ \App\CPU\translate('اسم المستفيد') }}</label>
                    <input type="text" name="payee_name" id="payee_name_normal" class="form-control" placeholder="{{ \App\CPU\translate('ادخل اسم المستفيد') }}" required>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="required">{{ \App\CPU\translate('طريقة الدفع') }}</label>
                    <select name="payment_method" id="payment_method_normal" class="form-control js-select2-custom" required>
                        <option value="cash">{{ \App\CPU\translate('نقدًا') }}</option>
                        <option value="bank_transfer">{{ \App\CPU\translate('تحويل بنكي') }}</option>
                        <option value="cheque">{{ \App\CPU\translate('شيك') }}</option>
                        <option value="other">{{ \App\CPU\translate('أخرى') }}</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2" id="cheque_wrapper_normal" style="display:none;">
                    <label class="required">{{ \App\CPU\translate('رقم الشيك') }}</label>
                    <input type="text" name="cheque_number" id="cheque_number_normal" class="form-control" placeholder="{{ \App\CPU\translate('ادخل رقم الشيك') }}">
                </div>
            </div>

            <!-- Priority Fields -->
            <div class="row">
                <div class="col-md-4 mb-1">
                    <label class="required" for="accountSelectFrom">{{ \App\CPU\translate('الحساب المدين') }}</label>
                    <select id="accountSelectFrom" name="account_id_to" class="form-control js-select2-custom" required>
                        <option value="">{{ \App\CPU\translate('اختار الحساب المدين') }}</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account['id'] }}"
                                    data-cost-center="{{ $account['cost_center'] ?? 0 }}">
                                {{ $account['account'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-1">
                    <label class="required" for="accountSelectToNormal">{{ \App\CPU\translate('الحساب الدائن') }}</label>
                    <select id="accountSelectToNormal" name="account_id" class="form-control js-select2-custom" required>
                        <option value="">{{ \App\CPU\translate('اختار الحساب الدائن') }}</option>
                        @foreach ($accounts_to as $account_to)
                            <option value="{{ $account_to['id'] }}"
                                    data-cost-center="{{ $account_to['cost_center'] ?? 0 }}">
                                {{ $account_to['account'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-1">
                    <label for="costSelect">{{ \App\CPU\translate('من مركز التكلفة') }}</label>
                    <select name="cost_id" id="costSelect" class="form-control js-select2-custom">
                        <option value="">{{ \App\CPU\translate('اختار مركز التكلفة') }}</option>
                        @foreach ($costcenters as $costcenter)
                            <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
                        @endforeach
                    </select>
                    <small id="costSelectHint" class="hint-required">{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}</small>
                </div>
            </div>

            <!-- Secondary Fields -->
            <div class="row">
                <div class="col-md-4 mb-1">
                    <label class="required" for="date_normal">{{ \App\CPU\translate('التاريخ') }}</label>
                    <input type="date" name="date" id="date_normal" class="form-control" required>
                </div>
                <div class="col-md-4 mb-1">
                    <label class="required" for="normal_amount">{{ \App\CPU\translate('المبلغ') }}</label>
                    <input type="number" step="0.01" min="1" name="amount" id="normal_amount" class="form-control amount-input" placeholder="{{ \App\CPU\translate('ادخل المبلغ') }}" required>
                </div>
                <div class="col-md-4 mb-1">
                    <label class="required" for="description_normal">{{ \App\CPU\translate('الوصف') }}</label>
                    <input type="text" name="description" id="description_normal" class="form-control" placeholder="{{ \App\CPU\translate('ادخل الوصف بوضوح') }}" required>
                </div>
            </div>

            <!-- Normal Tax Invoice Options -->
            <div class="form-group">
                <label class="form-check-label ps-5" for="normalIsTax">
                    <input type="checkbox" name="is_tax_invoice" id="normalIsTax">
                    {{ \App\CPU\translate('فاتورة ضريبية') }}
                </label>
            </div>

            <div id="normalTaxFields" style="display:none;">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="required" for="normalTaxType">{{ \App\CPU\translate('نوع الضريبة') }}</label>
                        <select name="tax_id" id="normalTaxType" class="form-control js-select2-custom">
                            <option value="">{{ \App\CPU\translate('اختار نوع الضريبة') }}</option>
                            @foreach ($taxes as $tax)
                                <option value="{{ $tax['id'] }}" data-rate="{{ $tax['amount'] }}">{{ $tax['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="required" for="normalTaxNumber">{{ \App\CPU\translate('رقم الضريبي') }}</label>
                        <input type="text" name="tax_number" id="normalTaxNumber" class="form-control" placeholder="{{ \App\CPU\translate('ادخل رقم الضريبي') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>{{ \App\CPU\translate('المبلغ بدون ضريبة') }}</label>
                        <input type="text" id="normalAmountWithoutTax" class="form-control" disabled>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>{{ \App\CPU\translate('الضريبة') }}</label>
                        <input type="text" id="normalTaxAmount" class="form-control" disabled>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>{{ \App\CPU\translate('المبلغ مع ضريبة') }}</label>
                        <input type="text" id="normalTotalWithTax" class="form-control" disabled>
                    </div>
                </div>
            </div>

            <!-- Receipt Image (Normal) -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="img_normal">{{ \App\CPU\translate('تحميل صورة إيصال') }}</label>
                    <input type="file" name="img" id="img_normal" class="form-control" accept="image/*,application/pdf">
                </div>
            </div>

            <!-- Save + Fetch -->
            <div class="row justify-content-end">
                <div class="col-md-3 text-end mb-2">
                    <button id="save-button-normal" class="btn btn-primary w-100 py-3" type="submit">
                        <span class="button-text">{{ \App\CPU\translate('حفظ') }}</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="col-md-3 text-end mb-2">
                    <button id="fetchOrders" class="btn btn-info w-100 py-3" type="button">
                        {{ \App\CPU\translate('جلب الفواتير') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Multiple Expense Form -->
    <div id="multipleForm">
        <form id="multipleVoucherForm" action="{{ route('admin.account.storesandExpense') }}" method="post" enctype="multipart/form-data" class="form-section">
            @csrf
            <input type="hidden" name="type" value="100">

            {{-- أعلى: اختيار الحساب الدائن فقط --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="required" for="accountSelectToMultiple">{{ \App\CPU\translate('اختر الحساب الدائن') }}</label>
                    <select id="accountSelectToMultiple" name="account_id_multiple" class="form-control js-select2-custom" required>
                        <option value="">{{ \App\CPU\translate('اختار الحساب الدائن') }}</option>
                        @foreach ($accounts_to as $account_to)
                            <option value="{{ $account_to['id'] }}"
                                    data-cost-center="{{ $account_to['cost_center'] ?? 0 }}">
                                {{ $account_to['account'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="table table-bordered">
                    <thead>
                    <tr class="table-header" style="background-color:#EDF2F4;color:black;">
                        <th>{{ \App\CPU\translate('رقم السند') }}</th>
                        <th>{{ \App\CPU\translate('اسم المستفيد') }}</th>
                        <th>{{ \App\CPU\translate('طريقة الدفع') }}</th>
                        <th>{{ \App\CPU\translate('رقم الشيك') }}</th>
                        <th>{{ \App\CPU\translate('اسم الحساب (مدين)') }}</th>
                        <th>{{ \App\CPU\translate('مع ضريبة') }}</th>
                        <th class="tax-header">{{ \App\CPU\translate('نوع الضريبة') }}</th>
                        <th class="tax-header">{{ \App\CPU\translate('الرقم الضريبي') }}</th>
                        <th>{{ \App\CPU\translate('مركز التكلفة') }}</th>
                        <th>{{ \App\CPU\translate('التاريخ') }}</th>
                        <th>{{ \App\CPU\translate('المبلغ') }}</th>
                        <th>{{ \App\CPU\translate('بدون ضريبة') }}</th>
                        <th>{{ \App\CPU\translate('الضريبة') }}</th>
                        <th>{{ \App\CPU\translate('الإجمالي') }}</th>
                        <th>{{ \App\CPU\translate('إيصال') }}</th>
                        <th>{{ \App\CPU\translate('الوصف') }}</th>
                        <th>{{ \App\CPU\translate('حذف') }}</th>
                    </tr>
                    </thead>

                    <tbody id="multipleRowsContainer">
                    <tr class="multiple-row" data-row="0">
                        <td><input type="text" name="multiple[0][voucher_number]" class="form-control" placeholder="{{ \App\CPU\translate('اتركه فارغ للتلقائي') }}"></td>
                        <td><input type="text" name="multiple[0][payee_name]" class="form-control" placeholder="{{ \App\CPU\translate('اسم المستفيد') }}" required></td>
                        <td style="min-width:160px;">
                            <select name="multiple[0][payment_method]" class="form-control payment-method js-select2-custom" required>
                                <option value="cash">{{ \App\CPU\translate('نقدًا') }}</option>
                                <option value="bank_transfer">{{ \App\CPU\translate('تحويل بنكي') }}</option>
                                <option value="cheque">{{ \App\CPU\translate('شيك') }}</option>
                                <option value="other">{{ \App\CPU\translate('أخرى') }}</option>
                            </select>
                        </td>
                        <td><input type="text" name="multiple[0][cheque_number]" class="form-control cheque-number" placeholder="{{ \App\CPU\translate('ادخل رقم الشيك') }}" style="display:none;"></td>
                        <td>
                            <select name="multiple[0][account_id_to]" class="form-control js-select2-custom account-debit-select" required>
                                <option value="">{{ \App\CPU\translate('اختار الحساب') }}</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account['id'] }}" data-cost-center="{{ $account['cost_center'] ?? 0 }}">
                                        {{ $account['account'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <label class="m-0">
                                <input type="checkbox" name="multiple[0][has_tax]" class="has-tax-checkbox"> {{ \App\CPU\translate('مع ضريبة؟') }}
                            </label>
                        </td>
                        <td class="tax-header">
                            <select name="multiple[0][tax_type]" class="form-control js-select2-custom tax-container" style="display:none;">
                                <option value="">{{ \App\CPU\translate('اختار نوع الضريبة') }}</option>
                                @foreach ($taxes as $taxe)
                                    <option value="{{ $taxe['id'] }}" data-rate="{{ $taxe['amount'] }}">{{ $taxe['name'] }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="tax-header"><input type="text" name="multiple[0][tax_number]" class="form-control tax-number-input" placeholder="{{ \App\CPU\translate('الرقم الضريبي') }}" style="display:none;"></td>
                        <td>
                            <select name="multiple[0][cost_id]" class="form-control js-select2-custom cost-select-row">
                                <option value="">{{ \App\CPU\translate('اختار مركز تكلفة') }}</option>
                                @foreach ($costcenters as $costcenter)
                                    <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
                                @endforeach
                            </select>
                            <small class="hint-required row-cost-hint" style="display:none">{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}</small>
                        </td>
                        <td><input type="date" name="multiple[0][date]" class="form-control" required></td>
                        <td><input type="number" step="0.01" min="0" name="multiple[0][amount]" class="form-control amount-input" placeholder="{{ \App\CPU\translate('ادخل المبلغ') }}" required></td>
                        <td><input type="text" class="form-control amount-without-tax" value="0.00" readonly></td>
                        <td><input type="text" class="form-control tax-amount" value="0.00" readonly></td>
                        <td><input type="text" class="form-control total-with-tax" value="0.00" readonly></td>
                        <td><input type="file" name="multiple[0][img]" class="form-control" accept="image/*,application/pdf"></td>
                        <td><input type="text" name="multiple[0][description]" class="form-control" placeholder="{{ \App\CPU\translate('ادخل الوصف بوضوح') }}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm delete-row">{{ \App\CPU\translate('حذف') }}</button></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3 mt-3 text-start">
                <button type="button" id="addRow" class="btn btn-sm btn-secondary">
                    {{ \App\CPU\translate('أضف صف جديد') }}
                </button>
            </div>

            <div id="overallTotals" class="mt-4" style="text-align:right;color:black;">
                <p>{{ \App\CPU\translate('الإجمالي بدون ضريبة') }}: <span id="overallWithoutTax">0.00</span></p>
                <p>{{ \App\CPU\translate('إجمالي الضريبة') }}: <span id="overallTax">0.00</span></p>
                <p>{{ \App\CPU\translate('الإجمالي مع ضريبة') }}: <span id="overallWithTax">0.00</span></p>
            </div>

            <div class="row mt-4">
                <div class="col-12 d-flex justify-content-end">
                    <button id="save-button-multiple" class="btn btn-sm btn-primary py-3 px-5" type="submit">
                        <span class="button-text">{{ \App\CPU\translate('حفظ') }}</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="ordersResult" class="mt-4"></div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

@push('script')
<script>
$(function () {
    /* ========== أدوات عامة ========== */
    const $normalForm   = $('#normalForm');
    const $multipleForm = $('#multipleForm');

    $('.js-select2-custom').select2({ placeholder: "{{ \App\CPU\translate('اختار') }}", width:'100%' });

    // تبويب عادي/متعدد
    $('#btnNormalVoucher').on('click', function () {
        $('#btnNormalVoucher, #btnMultipleVoucher').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
        $normalForm.show(); $multipleForm.hide();
    });
    $('#btnMultipleVoucher').on('click', function () {
        $('#btnNormalVoucher, #btnMultipleVoucher').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
        $normalForm.hide(); $multipleForm.show();
    });
    $normalForm.show(); $multipleForm.hide();

    /* === Helpers === */
    const requiresCC = (opt) => parseInt($(opt).data('cost-center')) === 1;
    const setReq = ($el, isReq, $hint=null) => {
        $el.prop('required', isReq);
        if (isReq && !$el.val()) $el.addClass('is-required'); else $el.removeClass('is-required');
        if ($hint) $hint.toggle(isReq);
    };

    /* ========== عادي: شيك/ضريبة ========== */
    function toggleChequeNormal(){
        const isCheque = $('#payment_method_normal').val()==='cheque';
        $('#cheque_wrapper_normal').toggle(isCheque);
        $('#cheque_number_normal').prop('required', isCheque);
        if(!isCheque) $('#cheque_number_normal').val('');
    }
    $('#payment_method_normal').on('change', toggleChequeNormal); toggleChequeNormal();

    $('#normalIsTax').on('change', function(){
        if(this.checked){ $('#normalTaxFields').show(); recalcNormalForm(); }
        else{ $('#normalTaxFields').hide(); $('#normalAmountWithoutTax,#normalTaxAmount,#normalTotalWithTax').val(''); }
    });
    $('#normal_amount, #normalTaxType').on('input change', function(){ if($('#normalIsTax').is(':checked')) recalcNormalForm(); });
    function recalcNormalForm(){
        let amount = parseFloat($('#normal_amount').val())||0;
        let taxRate = parseFloat($('#normalTaxType option:selected').data('rate'))||0;
        let taxAmount = amount*taxRate/100;
        $('#normalAmountWithoutTax').val(amount.toFixed(2));
        $('#normalTaxAmount').val(taxAmount.toFixed(2));
        $('#normalTotalWithTax').val((amount+taxAmount).toFixed(2));
    }

    /* ========== عادي: إجبار مركز التكلفة بحسب الحساب المختار ========== */
    function updateCostCenterRequirementNormal(){
        const fromNeeds = requiresCC($('#accountSelectFrom option:selected'));
        const toNeeds   = requiresCC($('#accountSelectToNormal option:selected'));
        const needCC    = fromNeeds || toNeeds; // أي منهما يطلب مركز تكلفة
        setReq($('#costSelect'), needCC, $('#costSelectHint'));
    }
    $('#accountSelectFrom, #accountSelectToNormal').on('change', updateCostCenterRequirementNormal);
    updateCostCenterRequirementNormal();

    /* ========== متعدد: منطق الصفوف ========== */
    const $rowsContainer = $('#multipleRowsContainer');

    function num(v){ v=parseFloat(v); return isNaN(v)?0:v; }

    function attachRowEvents($row){
        // طريقة الدفع → رقم الشيك
        $row.find('.payment-method').on('change', function(){
            const isCheque = $(this).val()==='cheque';
            const $cheque  = $row.find('.cheque-number');
            $cheque.toggle(isCheque).prop('required', isCheque);
            if(!isCheque) $cheque.val('');
        }).trigger('change');

        // ضريبة: إظهار/إخفاء عناصر الضريبة
        function syncTax(){
            const hasTax = $row.find('.has-tax-checkbox').is(':checked');
            $row.find('.tax-container, .tax-number-input').toggle(hasTax);
            recalcRow($row); recalcOverallTotals();
        }
        $row.find('.has-tax-checkbox').on('change', syncTax);
        $row.find('.tax-container').on('change', function(){ recalcRow($row); recalcOverallTotals(); });
        $row.find('.amount-input').on('input', function(){ recalcRow($row); recalcOverallTotals(); });

        // مركز التكلفة الإجباري حسب الحساب المدين في الصف
        const $acct = $row.find('.account-debit-select');
        const $cc   = $row.find('.cost-select-row');
        const $hint = $row.find('.row-cost-hint');

        function syncRowCC(){
            const need = requiresCC($acct.find('option:selected'));
            setReq($cc, need, $hint);
        }
        $acct.on('change', syncRowCC);
        syncRowCC();

        if ($.fn.select2) {
            $row.find('.js-select2-custom').select2({ width:'100%' });
        }

        syncTax(); recalcRow($row);
    }

    function recalcRow($row){
        let amount = num($row.find('.amount-input').val());
        let hasTax = $row.find('.has-tax-checkbox').is(':checked');
        let rate   = hasTax ? num($row.find('.tax-container option:selected').data('rate')) : 0;
        let tax    = amount * rate / 100;
        let total  = amount + tax;
        $row.find('.amount-without-tax').val(amount.toFixed(2));
        $row.find('.tax-amount').val(tax.toFixed(2));
        $row.find('.total-with-tax').val(total.toFixed(2));
    }

    function recalcOverallTotals(){
        let w=0,t=0,all=0;
        $rowsContainer.find('tr.multiple-row').each(function(){
            w   += num($(this).find('.amount-without-tax').val());
            t   += num($(this).find('.tax-amount').val());
            all += num($(this).find('.total-with-tax').val());
        });
        $('#overallWithoutTax').text(w.toFixed(2));
        $('#overallTax').text(t.toFixed(2));
        $('#overallWithTax').text(all.toFixed(2));
    }

    // أول صف
    attachRowEvents($rowsContainer.find('tr.multiple-row').first());

    // إضافة صف
    let rowIndex = 1;
    $('#addRow').on('click', function(){
        const tpl = `
        <tr class="multiple-row" data-row="${rowIndex}">
            <td><input type="text" name="multiple[${rowIndex}][voucher_number]" class="form-control" placeholder="{{ \App\CPU\translate('اتركه فارغ للتلقائي') }}"></td>
            <td><input type="text" name="multiple[${rowIndex}][payee_name]" class="form-control" placeholder="{{ \App\CPU\translate('اسم المستفيد') }}" required></td>
            <td>
                <select name="multiple[${rowIndex}][payment_method]" class="form-control payment-method js-select2-custom" required>
                    <option value="cash">{{ \App\CPU\translate('نقدًا') }}</option>
                    <option value="bank_transfer">{{ \App\CPU\translate('تحويل بنكي') }}</option>
                    <option value="cheque">{{ \App\CPU\translate('شيك') }}</option>
                    <option value="other">{{ \App\CPU\translate('أخرى') }}</option>
                </select>
            </td>
            <td><input type="text" name="multiple[${rowIndex}][cheque_number]" class="form-control cheque-number" placeholder="{{ \App\CPU\translate('ادخل رقم الشيك') }}" style="display:none;"></td>
            <td>
                <select name="multiple[${rowIndex}][account_id_to]" class="form-control js-select2-custom account-debit-select" required>
                    <option value="">{{ \App\CPU\translate('اختار الحساب') }}</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account['id'] }}" data-cost-center="{{ $account['cost_center'] ?? 0 }}">
                            {{ $account['account'] }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td><label class="m-0"><input type="checkbox" name="multiple[${rowIndex}][has_tax]" class="has-tax-checkbox"> {{ \App\CPU\translate('مع ضريبة؟') }}</label></td>
            <td class="tax-header">
                <select name="multiple[${rowIndex}][tax_type]" class="form-control js-select2-custom tax-container" style="display:none;">
                    <option value="">{{ \App\CPU\translate('اختار نوع الضريبة') }}</option>
                    @foreach ($taxes as $taxe)
                        <option value="{{ $taxe['id'] }}" data-rate="{{ $taxe['amount'] }}">{{ $taxe['name'] }}</option>
                    @endforeach
                </select>
            </td>
            <td class="tax-header"><input type="text" name="multiple[${rowIndex}][tax_number]" class="form-control tax-number-input" placeholder="{{ \App\CPU\translate('الرقم الضريبي') }}" style="display:none;"></td>
            <td>
                <select name="multiple[${rowIndex}][cost_id]" class="form-control js-select2-custom cost-select-row">
                    <option value="">{{ \App\CPU\translate('اختار مركز تكلفة') }}</option>
                    @foreach ($costcenters as $costcenter)
                        <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
                    @endforeach
                </select>
                <small class="hint-required row-cost-hint" style="display:none">{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}</small>
            </td>
            <td><input type="date" name="multiple[${rowIndex}][date]" class="form-control" required></td>
            <td><input type="number" step="0.01" min="0" name="multiple[${rowIndex}][amount]" class="form-control amount-input" placeholder="{{ \App\CPU\translate('ادخل المبلغ') }}" required></td>
            <td><input type="text" class="form-control amount-without-tax" value="0.00" readonly></td>
            <td><input type="text" class="form-control tax-amount" value="0.00" readonly></td>
            <td><input type="text" class="form-control total-with-tax" value="0.00" readonly></td>
            <td><input type="file" name="multiple[${rowIndex}][img]" class="form-control" accept="image/*,application/pdf"></td>
            <td><input type="text" name="multiple[${rowIndex}][description]" class="form-control" placeholder="{{ \App\CPU\translate('ادخل الوصف بوضوح') }}"></td>
            <td><button type="button" class="btn btn-danger btn-sm delete-row">{{ \App\CPU\translate('حذف') }}</button></td>
        </tr>`;
        const $row = $(tpl).appendTo($rowsContainer);
        attachRowEvents($row);
        recalcOverallTotals();
        rowIndex++;
    });

    // حذف صف
    $(document).on('click','.delete-row',function(){
        const $rows = $rowsContainer.find('.multiple-row');
        if($rows.length>1){
            $(this).closest('tr').remove();
            recalcOverallTotals();
        }
    });

    /* ========== جلب الفواتير (عادي) ========== */
    $('#fetchOrders').on('click', function() {
        const accountId = $('#accountSelectFrom').val();
        if (!accountId) { alert('{{ \App\CPU\translate("يرجى اختيار حساب") }}'); return; }
        fetch("{{ route('admin.account.getOrdersByAccount') }}?account_id=" + accountId)
            .then(r => r.json())
            .then(data => {
                let html = "<h3>{{ \App\CPU\translate('الطلبات') }}:</h3>";
                if (data.success && data.orders.length > 0) {
                    html += "<table class='table table-bordered'>";
                    html += '<tr style="background-color:#EDF2F4;color:black;">';
                    html += "<td>{{ \App\CPU\translate('فاتورة البيع') }}</td>";
                    html += "<td>{{ \App\CPU\translate('فاتورة المرتجع') }}</td>";
                    html += "<td>{{ \App\CPU\translate('المبلغ المتبقي') }}</td>";
                    html += "</tr>";
                    data.orders.forEach(function(order) {
                        const saleAmount   = parseFloat(order.transaction_reference)||0;
                        const paidAmount   = parseFloat(order.order_amount)||0;
                        const returnAmount = order.return_order ? parseFloat(order.return_order.order_amount)||0 : 0;
                        const diff = paidAmount - saleAmount - returnAmount;
                        html += "<tr>";
                        html += "<td><strong>" + order.id + "</strong><br><small>{{ \App\CPU\translate('تاريخ البيع') }}: " + (order.created_at || '-') + "</small><br><small>{{ \App\CPU\translate('المبلغ') }}: " + paidAmount.toFixed(2) + "</small><br><small>{{ \App\CPU\translate('المدفوع') }}: " + saleAmount.toFixed(2) + "</small></td>";
                        html += "<td>";
                        if (order.return_order) {
                            html += "<strong>" + order.return_order.id + "</strong><br><small>{{ \App\CPU\translate('تاريخ المرتجع') }}: " + (order.return_order.created_at || '-') + "</small><br><small>{{ \App\CPU\translate('المبلغ') }}: " + returnAmount.toFixed(2) + "</small>";
                        } else { html += "-"; }
                        html += "</td>";
                        html += "<td style='color:black;font-weight:bold;'>" + diff.toFixed(2) + "</td>";
                        html += "</tr>";
                    });
                    html += "</table>";
                } else {
                    html += "<p>{{ \App\CPU\translate('لم يتم العثور على طلبات للحساب المحدد.') }}</p>";
                }
                $('#ordersResult').html(html);
            })
            .catch(_ => $('#ordersResult').html("<p>{{ \App\CPU\translate('حدث خطأ أثناء جلب البيانات.') }}</p>"));
    });

    /* ========== منع الإرسال إذا لزم مركز تكلفة ولم يُختر ========== */
    $('#normalVoucherForm').on('submit', function(e){
        // تحقق من شرط مركز التكلفة للنموذج العادي
        const fromNeeds = requiresCC($('#accountSelectFrom option:selected'));
        const toNeeds   = requiresCC($('#accountSelectToNormal option:selected'));
        const needCC    = fromNeeds || toNeeds;
        const $cc       = $('#costSelect');
        setReq($cc, needCC, $('#costSelectHint'));

        if (needCC && !$cc.val()){
            e.preventDefault();
            alert('{{ \App\CPU\translate("هذا الحساب يتطلب اختيار مركز تكلفة") }}');
            $cc.select2('open');
            return false;
        }

        // Spinner
        const $btn = $('#save-button-normal');
        $btn.prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');
    });

    $('#multipleVoucherForm').on('submit', function(e){
        let invalid = false, firstBad = null;

        $('#multipleRowsContainer tr.multiple-row').each(function(){
            const $row = $(this);
            const need = requiresCC($row.find('.account-debit-select option:selected'));
            const $cc  = $row.find('.cost-select-row');
            setReq($cc, need, $row.find('.row-cost-hint'));

            if (need && !$cc.val()){
                invalid = true;
                if (!firstBad) firstBad = $cc;
            }
        });

        if (invalid){
            e.preventDefault();
            alert('{{ \App\CPU\translate("هناك صف/صفوف تتطلب اختيار مركز تكلفة قبل الحفظ.") }}');
            if (firstBad){
                // افتح Select2 إن وجد
                try { firstBad.select2('open'); } catch(_) { firstBad.focus(); }
            }
            return false;
        }

        const $btn = $('#save-button-multiple');
        $btn.prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');
    });

    // تعطيل زر الحفظ بعد الضغط (للاستخدام اليدوي إن لزم)
    window.disableButton = function(e){
        const $btn = $(e.currentTarget);
        $btn.prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');
    };
});
</script>
@endpush
