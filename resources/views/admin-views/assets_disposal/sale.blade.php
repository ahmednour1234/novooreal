@extends('layouts.admin.app')

@section('title', \App\CPU\translate('بيع الأصل'))

@push('css_or_js')
    <style>
        /* Title styling */
        .form-title {
            font-size: 2.5rem;
            text-align: center;
            color: #001B63;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        /* Enhanced Card Design */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .card-body { padding: 2rem; }

        /* Form Elements */
        .form-label { font-weight: 600; margin-bottom: 0.5rem; }
        .form-control { border-radius: 0.25rem; box-shadow: none; border-color: #ced4da; }
        .form-control:focus { border-color: #001B63; box-shadow: 0 0 0 0.2rem rgba(0, 27, 99, 0.25); }

        /* Buttons */
        .btn-primary { background-color: #001B63; border: none; padding: 0.6rem 1.5rem; font-weight: 600; }
        .btn-secondary { background-color: #6c757d; border: none; padding: 0.6rem 1.5rem; font-weight: 600; }

        .form-label.required::after { content: " *"; color: red; }

        /* Cost center enforcement hint */
        .hint-required{font-size:.85rem;color:#b91c1c;display:none;margin-top:.25rem}
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
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.depreciation.index') }}" class="text-primary">
                        {{ \App\CPU\translate(' الأصول الثابتة') }}
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" class="text-primary">
                        {{ \App\CPU\translate('بيع الأصل') }} : {{ $asset->asset_name }}
                    </a>
                </li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="assetSaleForm" action="{{ route('admin.disposal.sale.store', $asset->id) }}" method="POST">
                @csrf

                <!-- اسم الأصل -->
                <div class="mb-3">
                    <label class="form-label">{{ \App\CPU\translate('اسم الأصل') }}</label>
                    <input type="text" class="form-control" value="{{ $asset->asset_name }} ({{ $asset->code }})" disabled>
                </div>

                <!-- الحسابات -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="accountSelectTo">{{ \App\CPU\translate('الحساب المدين') }}</label>
                        <select id="accountSelectTo" name="account_id_to" class="form-control js-select2-custom" required>
                            <option value="">{{ \App\CPU\translate('اختار الحساب المحول له') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account['id'] }}"
                                        data-cost-center="{{ $account['cost_center'] ?? 0 }}">
                                    {{ $account['account'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="accountSelect">{{ \App\CPU\translate('الحساب الدائن') }}</label>
                        <select id="accountSelect" name="account_id" class="form-control js-select2-custom" required>
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

                <!-- مركز التكلفة وتاريخ البيع -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="costSelect">{{ \App\CPU\translate('مركز التكلفة') }}</label>
                        <select name="cost_id" id="costSelect" class="form-control js-select2-custom">
                            <option value="">{{ \App\CPU\translate('اختار مركز التكلفة') }}</option>
                            @foreach ($costcenters as $costcenter)
                                <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
                            @endforeach
                        </select>
                        <small id="costHint" class="hint-required">{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">{{ \App\CPU\translate('تاريخ البيع') }}</label>
                        <input type="date" name="sale_date" class="form-control" required>
                    </div>
                </div>

                <!-- السعر والملاحظات -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label required">{{ \App\CPU\translate('سعر البيع') }}</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\CPU\translate('ملاحظات') }}</label>
                        <textarea name="description" class="form-control" rows="1" placeholder="{{ \App\CPU\translate('أدخل الملاحظات') }}"></textarea>
                    </div>
                </div>

                <!-- الأزرار -->
                <div class="d-flex justify-content-end" style="gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="min-width: 120px;">
                        {{ \App\CPU\translate('حفظ') }}
                    </button>

                    <a href="{{ route('admin.depreciation.index') }}" class="btn btn-danger" style="min-width: 120px;">
                        {{ \App\CPU\translate('إلغاء') }}
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // عناصر النموذج
    const debitSelect  = document.getElementById('accountSelectTo'); // المدين
    const creditSelect = document.getElementById('accountSelect');   // الدائن
    const costSelect   = document.getElementById('costSelect');      // مركز التكلفة
    const costHint     = document.getElementById('costHint');
    const form         = document.getElementById('assetSaleForm');

    // هل الحساب المختار يتطلب مركز تكلفة؟
    const requiresCC = (selectEl) => {
        if(!selectEl) return false;
        const opt = selectEl.selectedOptions ? selectEl.selectedOptions[0] : null;
        const v = opt && opt.dataset ? opt.dataset.costCenter : 0;
        return parseInt(v || 0) === 1;
    };

    // تعيين الحقل كمطلوب + تنبيه بصري
    const setRequired = (el, required, hintEl) => {
        if(!el) return;
        el.required = required;
        const needStyle = required && !el.value;
        el.classList.toggle('is-required', needStyle);
        if(hintEl) hintEl.style.display = needStyle ? 'inline' : 'none';
    };

    const updateCostRequirement = () => {
        const need = requiresCC(debitSelect) || requiresCC(creditSelect);
        setRequired(costSelect, need, costHint);
    };

    debitSelect?.addEventListener('change', updateCostRequirement);
    creditSelect?.addEventListener('change', updateCostRequirement);
    updateCostRequirement(); // init

    form?.addEventListener('submit', function(e){
        const need = requiresCC(debitSelect) || requiresCC(creditSelect);
        setRequired(costSelect, need, costHint);
        if (need && !costSelect.value){
            e.preventDefault();
            alert('{{ \App\CPU\translate('هذا الحساب يتطلب اختيار مركز تكلفة') }}');
            // حاول فتح Select2 إن وُجد
            try { $(costSelect).select2('open'); } catch(_) { costSelect.focus(); }
            return false;
        }
    });
});
</script>
@endpush
