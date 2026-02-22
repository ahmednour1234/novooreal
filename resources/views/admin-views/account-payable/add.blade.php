@extends('layouts.admin.app')

@section('title', \App\CPU\translate('إضافة أرصدة إفتتاحية'))

<style>
    :root{
        --c-bg:#f6f8ff; --c-line:#e9eef5; --c-soft:#fff; --rd:14px;
        --c-green:#16a34a; --c-blue:#2563eb; --c-red:#ef4444; --c-muted:#6b7280;
    }
    .page-wrap{direction:rtl}
    .ob-card{border:1px solid var(--c-line); border-radius:var(--rd); background:var(--c-soft); box-shadow:0 12px 28px -14px rgba(2,32,71,.12)}
    .ob-head{display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-bottom:1px solid var(--c-line); background:linear-gradient(180deg,#fff,#fafcff)}
    .ob-title{font-weight:600; font-size:18px}
    .date-chip{display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border:1px dashed var(--c-line); border-radius:999px; background:#fff}
    .date-chip .dot{width:8px;height:8px;border-radius:999px;background:var(--c-blue)}
    .form-label.required::after{content:" *"; color:var(--c-red); font-weight:700; margin-right:4px}
    .hint{color:var(--c-muted); font-size:.85rem}
    .badge-soft{border:1px solid var(--c-line); background:#fff; border-radius:999px; padding:.25rem .6rem; font-weight:600}
    .input-group-text{background:#fff}
</style>

@section('content')
<div class="content container-fluid page-wrap">

    <!-- Breadcrumb -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                        <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                    </a>
                </li>
                <li class="breadcrumb-item active text-primary" aria-current="page">
                    {{ \App\CPU\translate('أرصدة إفتتاحية') }}
                </li>
            </ol>
        </nav>
    </div>

    <div class="ob-card">
        <div class="ob-head">
            <div class="ob-title">{{ \App\CPU\translate('قيد الرصيد الافتتاحي') }}</div>
            <div class="date-chip">
                <span class="dot"></span>
                <span class="hint">{{ \App\CPU\translate('التاريخ') }}:</span>
                <strong>{{ \Carbon\Carbon::now()->format('Y') }}-01-01</strong>
            </div>
        </div>

        <form action="{{ route('admin.account.store-payable') }}" method="post" id="openingBalanceForm" class="p-4" novalidate>
            @csrf
            <input type="hidden" name="entry_date" value="{{ \Carbon\Carbon::now()->format('Y') }}-01-01">

            <div class="row g-4">
                {{-- رقم الحساب + اسم الحساب جنب بعض --}}
                <div class="col-lg-6">
                    <label for="account_code" class="form-label required">{{ \App\CPU\translate('رقم الحساب') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="tio-hash"></i></span>
                        <input type="text" id="account_code" name="account_code" class="form-control"
                               list="accountsList" placeholder="{{ \App\CPU\translate('اكتب رقم الحساب') }}"
                               autocomplete="off" required>
                        <input type="text" id="account_name_display" class="form-control"
                               placeholder="{{ \App\CPU\translate('اسم الحساب') }}" readonly tabindex="-1" style="max-width:55%">
                    </div>

                    {{-- datalist للراحة في الاقتراح --}}
                    <datalist id="accountsList">
                        @foreach ($accounts as $acc)
                            @php
                                $code = $acc['code'] ?? $acc['account_number'] ?? '';
                                $name = $acc['account'] ?? '';
                            @endphp
                            @if($code)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endif
                        @endforeach
                    </datalist>

                    <input type="hidden" name="account_id" id="account_id">
                    @error('account_id') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                {{-- مدين --}}
                <div class="col-lg-3">
                    <label for="debit" class="form-label">{{ \App\CPU\translate('مدين') }}</label>
                    <div class="input-group">
                        <span class="input-group-text badge-soft">{{ \App\CPU\translate('مدين') }}</span>
                        <input type="number" step="0.01" min="0" name="debit" id="debit"
                               class="form-control" placeholder="0.00" value="{{ old('debit') }}">
                    </div>
                    @error('debit') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- دائن --}}
                <div class="col-lg-3">
                    <label for="credit" class="form-label">{{ \App\CPU\translate('دائن') }}</label>
                    <div class="input-group">
                        <span class="input-group-text badge-soft">{{ \App\CPU\translate('دائن') }}</span>
                        <input type="number" step="0.01" min="0" name="credit" id="credit"
                               class="form-control" placeholder="0.00" value="{{ old('credit') }}">
                    </div>
                    @error('credit') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

        <div class="row mt-4 g-2 justify-content-end">
    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4 py-2">
            {{ \App\CPU\translate('حفظ') }}
        </button>
    </div>
    <div class="col-auto">
        <button type="reset" class="btn btn-danger px-4 py-2">
            {{ \App\CPU\translate('الغاء') }}
        </button>
    </div>
</div>

        </form>
    </div>

</div>
@endsection

<script>
window.addEventListener('DOMContentLoaded', function () {
    // ✅ فهرس سريع: code/account_number => {id, name}
    const ACC_INDEX = @json(
        collect($accounts)->mapWithKeys(function($a){
            $key = $a['code'] ?? ($a['account_number'] ?? null);
            return $key ? [$key => ['id'=>$a['id'], 'name'=>$a['account'] ?? '']] : [];
        })
    );

    const inputCode   = document.getElementById('account_code');
    const hiddenId    = document.getElementById('account_id');
    const nameDisplay = document.getElementById('account_name_display');
    const debitInput  = document.getElementById('debit');
    const creditInput = document.getElementById('credit');
    const form        = document.getElementById('openingBalanceForm');

    // حراسة: لو عنصر ناقص، ما نكسرش الصفحة
    if (!inputCode || !hiddenId || !nameDisplay || !debitInput || !creditInput || !form) {
        console.warn('Opening Balance: required inputs not found.');
        return;
    }

    function updateAccountFields() {
        const code = (inputCode.value || '').trim();
        const acc  = ACC_INDEX[code] || null;
        if (acc) {
            hiddenId.value    = acc.id;
            nameDisplay.value = acc.name || '';
        } else {
            hiddenId.value    = '';
            nameDisplay.value = '';
        }
    }

    ['input','change','blur','keyup'].forEach(evt => {
        inputCode.addEventListener(evt, updateAccountFields);
    });

    // لا يُسمح بتعبئة المدين والدائن معًا
    debitInput.addEventListener('input', function () {
        if (parseFloat(this.value || 0) > 0) creditInput.value = '';
    });
    creditInput.addEventListener('input', function () {
        if (parseFloat(this.value || 0) > 0) debitInput.value = '';
    });

    // تحقق قبل الإرسال
    form.addEventListener('submit', function (e) {
        const debit  = parseFloat(debitInput.value  || 0);
        const credit = parseFloat(creditInput.value || 0);

        if (!hiddenId.value) {
            e.preventDefault();
            alert('من فضلك اختر رقم حساب صحيح من القائمة.');
            return;
        }
        if ((debit > 0 && credit > 0) || (debit === 0 && credit === 0)) {
            e.preventDefault();
            alert('أدخل مبلغًا واحدًا فقط: إمّا مدين أو دائن.');
        }
    });
});
</script>
