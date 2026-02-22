@php
    use App\Models\Transection;

    /** @var \App\Models\Supplier $supplier */
    $currency = \App\CPU\Helpers::currency_symbol();
    $accId    = $supplier->account_id ?? null;

    $sumDebit = 0.0;
    $sumCredit = 0.0;
    $net = 0.0;

    if ($accId) {
        $agg = Transection::where('account_id', $accId)
            ->selectRaw('COALESCE(SUM(debit),0) AS dsum, COALESCE(SUM(credit),0) AS csum')
            ->first();
        $sumDebit  = (float) data_get($agg, 'dsum', 0);
        $sumCredit = (float) data_get($agg, 'csum', 0);
        $net       = $sumCredit - $sumDebit; // موجب = دائن، سالب = مدين
    }
@endphp

<div class="row">
    {{-- بطاقة معلومات المورد --}}
    <div class="col-lg-5 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">{{ \App\CPU\translate('معلومات المورد') }}</h5>
            </div>
            <div class="card-body">
                <div class="media align-items-center mb-3">
                    <img class="mr-3" style="width:56px;height:56px;border-radius:50%;object-fit:cover"
                         src="{{ asset('storage/app/public/supplier/'.$supplier->image) }}"
                         onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'">
                    <div class="media-body">
                        <div class="h5 mb-1">{{ $supplier->name }}</div>
                        @if($supplier->c_history)
                            <div class="text-muted small">🏷 {{ $supplier->c_history }}</div>
                        @endif
                        @if($supplier->tax_number)
                            <div class="text-muted small">🧾 {{ $supplier->tax_number }}</div>
                        @endif
                    </div>
                </div>

                <hr>

                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><strong>{{ \App\CPU\translate('الهاتف') }}:</strong> {{ $supplier->mobile ?: '—' }}</li>
                    <li class="mb-2"><strong>{{ \App\CPU\translate('البريد') }}:</strong> {{ $supplier->email ?: '—' }}</li>
                    <li class="mb-2"><strong>{{ \App\CPU\translate('العنوان') }}:</strong> {{ $supplier->address ?: '—' }}</li>
                    <li class="mb-2"><strong>{{ \App\CPU\translate('المدينة') }}:</strong> {{ $supplier->city ?: '—' }}</li>
                    <li class="mb-2"><strong>{{ \App\CPU\translate('المقاطعة') }}:</strong> {{ $supplier->state ?: '—' }}</li>
                    <li class="mb-2"><strong>{{ \App\CPU\translate('كود المدينة') }}:</strong> {{ $supplier->zip_code ?: '—' }}</li>
                    @if(!empty($supplier->created_at))
                        <li class="mb-2"><strong>{{ \App\CPU\translate('تاريخ الإضافة') }}:</strong> {{ $supplier->created_at }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- بطاقة ملخص الحساب --}}
    <div class="col-lg-7 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">{{ \App\CPU\translate('ملخص الحساب') }}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="text-muted small">{{ \App\CPU\translate('إجمالي مدين') }}</div>
                        <div class="h4 mb-0">{{ number_format($sumDebit, 2, '.', ',') }} {{ $currency }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-muted small">{{ \App\CPU\translate('إجمالي دائن') }}</div>
                        <div class="h4 mb-0">{{ number_format($sumCredit, 2, '.', ',') }} {{ $currency }}</div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="text-muted small">{{ \App\CPU\translate('صافي الرصيد') }}</div>
                        <div class="h4 mb-0">
                            {{ number_format($net, 2, '.', ',') }} {{ $currency }}
                            <small class="text-muted">(
                                {{ $net >= 0 ? \App\CPU\translate('دائن') : \App\CPU\translate('مدين') }}
                            )</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <div>
                        <span class="text-muted small d-block">{{ \App\CPU\translate('عدد منتجات المورد') }}</span>
                        <span class="h5 mb-0">{{ $supplier->products->count() }}</span>
                    </div>
                    <div>
                        <span class="text-muted small d-block">{{ \App\CPU\translate('الحالة') }}</span>
                        <span class="h5 mb-0">
                            {{ $supplier->active ? \App\CPU\translate('نشط') : \App\CPU\translate('غير نشط') }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
