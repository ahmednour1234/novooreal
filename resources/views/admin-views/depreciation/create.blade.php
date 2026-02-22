@extends('layouts.admin.app')

@section('title', \App\CPU\translate('اهلاك أصل ثابت'))

@push('css_or_js')
    <style>
        .form-section {
            margin: 0.2rem 0;
            padding: 0.5rem;
            border-radius: 10px;
            background-color: #fff;
        }
        label { font-weight: 300; }
        .asset-details {
            margin-top: 0rem;
            padding: 0.8rem;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .asset-details h5 {
            margin-bottom: 1rem;
            font-weight: 300;
            border-bottom: 0.5px solid #ddd;
            padding-bottom: 0.4rem;
            color: #000;
        }
        .asset-details .row .col-md-6 { margin-bottom: 0.6rem; }
        .info-box {
            background-color: #e8f5e9;
            padding: 0.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        /* تنبيه مركز التكلفة */
        .hint-required{font-size:.8rem;color:#b91c1c;display:none;margin-top:.25rem}
        .is-required{border-color:#ef4444!important; box-shadow:0 0 0 .08rem rgba(239,68,68,.25)}
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
                <li class="breadcrumb-item active text-primary" aria-current="page">
                    {{ \App\CPU\translate('إهلاك أصل') }}
                </li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="depreciationForm" action="{{ route('admin.depreciation.depreciate') }}" method="post" enctype="multipart/form-data">
                @csrf

                {{-- بيانات الأصل --}}
                <div class="form-section">
                    <h4 class="mb-3">{{ \App\CPU\translate('بيانات الأصل') }}</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="required">{{ \App\CPU\translate('اختر الأصل') }}</label>
                            <select name="asset_id" id="asset_id" class="form-control" required>
                                <option value="">{{ \App\CPU\translate('اختر الأصل') }}</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">
                                        {{ $asset->asset_name }} - {{ $asset->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="required">{{ \App\CPU\translate('تاريخ العملية') }}</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                    </div>

                    {{-- تفاصيل الأصل --}}
                    <div class="asset-details">
                        <h5>{{ \App\CPU\translate('تفاصيل الأصل') }}</h5>
                        <div class="row">
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('اسم الأصل') }}:</strong> <span id="detail_asset_name">--</span></div>
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('الكود') }}:</strong> <span id="detail_code">--</span></div>
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('طريقة الاهلاك') }}:</strong> <span id="detail_depreciation_method">--</span></div>
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('سعر الشراء') }}:</strong> <span id="detail_purchase_price">--</span></div>
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('التكاليف الإضافية') }}:</strong> <span id="detail_additional_costs">--</span></div>
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('القيمة المتبقية') }}:</strong> <span id="detail_salvage_value">--</span></div>
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('العمر الافتراضي') }}:</strong> <span id="detail_useful_life">--</span> {{ \App\CPU\translate('بالسنوات') }}</div>
                            <div class="col-md-6"><strong>{{ \App\CPU\translate('القيمة الدفترية') }}:</strong> <span id="detail_book_value">--</span></div>
                        </div>
                    </div>
                </div>

                {{-- عدد الوحدات (يظهر فقط عند النوع المناسب) --}}
                <div class="form-section" id="unitsField" style="display: none;">
                    <h4 class="mb-3">{{ \App\CPU\translate('تفاصيل الاهلاك') }}</h4>
                    <div class="mb-3">
                        <label class="required">{{ \App\CPU\translate('عدد الوحدات المنتجة خلال الفترة') }}</label>
                        <input type="number" name="produced_units" step="1" min="0" class="form-control" placeholder="{{ \App\CPU\translate('أدخل عدد الوحدات') }}">
                    </div>
                </div>

                {{-- سند صرف الاهلاك --}}
                <div class="form-section">
                    <h4 class="mb-3">{{ \App\CPU\translate('سند صرف الاهلاك') }}</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="required">{{ \App\CPU\translate('الحساب المدين') }}</label>
                            <select name="account_id_to" id="debit_account" class="form-control js-select2-custom" required>
                                <option value="">{{ \App\CPU\translate('اختار الحساب المحول له') }}</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account['id'] }}"
                                        data-cost-center="{{ $account['cost_center'] ?? 0 }}">
                                        {{ $account['account'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="required">{{ \App\CPU\translate('الحساب الدائن') }}</label>
                            <select name="account_id" id="credit_account" class="form-control js-select2-custom" required>
                                <option value="">{{ \App\CPU\translate('اختار الحساب') }}</option>
                                @foreach ($accounts_to as $account)
                                    <option value="{{ $account->id }}"
                                        data-cost-center="{{ $account->cost_center ?? 0 }}">
                                        {{ $account->account }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('مركز التكلفة') }}</label>
                            <select name="cost_id" id="cost_id" class="form-control js-select2-custom">
                                <option value="">{{ \App\CPU\translate('اختر مركز التكلفة') }}</option>
                                @foreach ($costcenters as $costcenter)
                                    <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
                                @endforeach
                            </select>
                            <small id="costHint" class="hint-required">{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('تحميل صورة الإيصال') }}</label>
                            <input type="file" name="voucher_img" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                {{-- زر الإرسال --}}
                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-primary px-5">
                        {{ \App\CPU\translate('تسجيل الاهلاك') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === تحميل تفاصيل الأصل المختار ===
    const assetSelect = document.getElementById('asset_id');
    const unitsField = document.getElementById('unitsField');

    const detailAssetName = document.getElementById('detail_asset_name');
    const detailCode = document.getElementById('detail_code');
    const detailDepMethod = document.getElementById('detail_depreciation_method');
    const detailPurchasePrice = document.getElementById('detail_purchase_price');
    const detailAdditionalCosts = document.getElementById('detail_additional_costs');
    const detailSalvage = document.getElementById('detail_salvage_value');
    const detailUsefulLife = document.getElementById('detail_useful_life');
    const detailBookValue = document.getElementById('detail_book_value');

    if (assetSelect) {
        assetSelect.addEventListener('change', function () {
            const assetId = this.value;
            if (assetId === '') return;

            fetch("{{ route('admin.depreciation.getAssetDetails') }}?asset_id=" + assetId)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const a = data.asset;
                        detailAssetName.innerText = a.asset_name;
                        detailCode.innerText = a.code;

                        let depMethodText = a.depreciation_method;
                        switch (a.depreciation_method) {
                            case "straight_line":
                                depMethodText = "{{ \App\CPU\translate('القسط الثابت') }}"; break;
                            case "declining_balance":
                                depMethodText = "{{ \App\CPU\translate('الرصيد المتناقص') }}"; break;
                            case "units_of_production":
                                depMethodText = "{{ \App\CPU\translate('الإنتاج/الاستخدام') }}"; break;
                        }

                        detailDepMethod.innerText = depMethodText;
                        detailPurchasePrice.innerText = a.purchase_price;
                        detailAdditionalCosts.innerText = a.additional_costs ?? '0';
                        detailSalvage.innerText = a.salvage_value;
                        detailUsefulLife.innerText = a.useful_life;
                        detailBookValue.innerText = a.book_value;

                        // إظهار حقل الوحدات فقط لطريقة الإنتاج/الاستخدام
                        unitsField.style.display = (a.depreciation_method === 'units_of_production') ? 'block' : 'none';
                    }
                });
        });
    }

    // === إلزام مركز التكلفة إذا كان الحساب يتطلب ذلك (cost_center = 1) ===
    const debitSelect  = document.getElementById('debit_account');   // الحساب المدين
    const creditSelect = document.getElementById('credit_account');  // الحساب الدائن
    const costSelect   = document.getElementById('cost_id');         // مركز التكلفة
    const costHint     = document.getElementById('costHint');
    const form         = document.getElementById('depreciationForm');

    const requiresCC = (selectEl) => {
        if(!selectEl) return false;
        const opt = selectEl.selectedOptions ? selectEl.selectedOptions[0] : null;
        const val = opt && opt.dataset ? opt.dataset.costCenter : 0;
        return parseInt(val || 0) === 1;
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
        const need = (requiresCC(debitSelect) || requiresCC(creditSelect));
        setRequired(costSelect, need, costHint);
    };

    debitSelect?.addEventListener('change', updateCostCenterRequirement);
    creditSelect?.addEventListener('change', updateCostCenterRequirement);
    updateCostCenterRequirement(); // init

    form?.addEventListener('submit', function(e){
        const need = (requiresCC(debitSelect) || requiresCC(creditSelect));
        setRequired(costSelect, need, costHint);
        if (need && !costSelect.value) {
            e.preventDefault();
            alert('{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}');
            costSelect.focus();
            return false;
        }
    });
});
</script>
