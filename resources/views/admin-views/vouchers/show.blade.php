@extends('layouts.admin.app')

@php
    $isReceipt = isset($voucher->type) ? ($voucher->type == 'receipt') : false;

    $pageTitle = ($isReceipt ? 'عرض سند قبض' : 'عرض سند صرف') . ' #' . $voucher->id;
@endphp

@section('title', $pageTitle)

@section('content')
@php
    // ترجمة طريقة الدفع/القبض
    $methodMap = [
        'cash'   => 'نقدًا',
        'bank'   => 'تحويل بنكي',
        'check'  => 'شيك',
        'cheque' => 'شيك',
        'card'   => 'بطاقة',
    ];
    $methodKey = strtolower($voucher->payment_method ?? '');
    $paymentMethodAr = $methodMap[$methodKey] ?? ($voucher->payment_method ?: '—');

    // إعدادات المتجر
    $settings = \App\Models\BusinessSetting::whereIn('key', [
        'shop_name','shop_address','shop_phone','shop_email','shop_logo',
        'tax_number','vat_number','vat_reg_no','commercial_register'
    ])->pluck('value','key');

    $shopName  = $settings['shop_name']    ?? '';
    $shopAddr  = $settings['shop_address'] ?? '';
    $shopPhone = $settings['shop_phone']   ?? '';
    $shopEmail = $settings['shop_email']   ?? '';
    $shopLogo  = $settings['shop_logo']    ?? '';

    // الأرقام (يحاول من أكثر من مفتاح شائع)
    $taxNumber = $settings['tax_number'] ?? $settings['vat_number'] ?? '';
    $vatRegNo  = $settings['commercial_register'] ?? $settings['vat_reg_no'] ?? '';

    // المستفيد
    $beneficiary = $voucher->payee_name ?? '—';

    // العملة
    $currencyCode   = $voucher->currency ?? config('app.currency', 'SAR');
    $currencySymbol = \App\CPU\Helpers::currency_symbol();

    // نصوص ديناميكية حسب النوع
    $voucherLabel        = $isReceipt ? 'سند قبض' : 'سند صرف';
    $methodLabel         = $isReceipt ? 'طريقة القبض' : 'طريقة الدفع';
    $creditLabel         = 'الحساب الدائن';
    $debitLabel          = 'الحساب المدين';
    $amountLabel         = 'المبلغ';
    $chequeNumber        = $voucher->id ?? null;
    $showChequeRow       = in_array($methodKey, ['check','cheque']) && $chequeNumber;

    // لون الشارة
    $badgeClass = $isReceipt ? 'pill pill-receipt' : 'pill pill-payment';
@endphp

<style>
    :root{
        --ink:#0f172a; --muted:#64748b; --line:#e5e7eb; --soft:#fafafa; --ok:#16a34a; --warn:#ef4444; --card:#ffffff;
    }
    .page-wrapper{direction:rtl}
    .voucher-wrap{max-width:980px;margin:0 auto;padding:6px}

    #voucher-card{
        background:var(--card);
        border-radius:16px;
        border:1px solid #eef2f7;
        overflow:hidden;
    }

    .card-header-area{padding:18px 22px 10px 22px}
    .head-grid{display:grid;grid-template-columns:1fr auto 1fr;gap:18px;align-items:center}
    .org-box .line{display:flex;gap:8px;align-items:center;margin:4px 0;color:var(--ink);font-size:13px}
    .org-box .key{color:var(--muted);min-width:110px}
    .org-box .val{font-weight:700}
    .logo img{height:72px;width:auto;border-radius:12px;border:1px solid #eef2f7;padding:6px;background:#fff}

    .meta{display:flex;gap:12px;justify-content:space-between;color:var(--ink);font-size:13px;margin-top:10px;flex-wrap:wrap}
    .pill{display:inline-block;border-radius:999px;padding:4px 10px;font-size:11px;border:1px solid #e0e7ff}
    .pill-receipt{background:#ecfdf5;color:#065f46;border-color:#d1fae5}
    .pill-payment{background:#fef2f2;color:#991b1b;border-color:#fee2e2}

    .divider{height:1px;background:var(--line);margin:14px 0}

    .card-body-area{padding:12px 22px 18px 22px;background:
        radial-gradient(900px 220px at 100% 100%, #f9fbff 0, transparent 70%)}

    .details-table{width:100%;border-collapse:collapse;font-size:14px}
    .details-table th,
    .details-table td{padding:10px 12px;border:1px solid #eef2f7;vertical-align:top}
    .details-table th{background:#f8fafc;color:#475569;font-weight:700}
    .details-table .label{color:var(--muted);width:180px;white-space:nowrap}
    .amount{font-weight:900;color:var(--ok)}
    .desc{line-height:1.7}

    .signatures{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:16px}
    .sign-box{padding:8px 0}
    .sign-line{height:42px;border-bottom:1px dashed #cbd5e1;margin-bottom:6px}
    .sign-title{text-align:center;color:var(--muted);font-size:12px;font-weight:700}

    .card-footer-area{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 20px;background:#fafafa;border-top:1px solid #eef2f7}
    .muted{font-size:12px;color:#475569}

    @media print{
        body *{visibility:hidden !important}
        #voucher-card, #voucher-card *{visibility:visible !important}
        #voucher-card{position:absolute;inset:0;width:100%;border-color:#ddd}
        .non-printable{display:none !important}
        .details-table th, .details-table td{border-color:#ddd}
    }
</style>

<div class="container-fluid page-wrapper">
    <div class="voucher-wrap">
        <!-- Breadcrumb (خارج الطباعة) -->
        <div class="row align-items-center mb-3 non-printable">
            <div class="col-sm">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                                <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.vouchers.index', ['type' => $isReceipt ? 'receipt' : 'payment']) }}" class="text-secondary">
                                {{ \App\CPU\translate($isReceipt ? 'سندات القبض' : 'سندات الصرف') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">
                            {{ $voucherLabel }} #{{ $voucher->id }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- الكارت الواحد -->
        <section id="voucher-card">
            <!-- الهيدر -->
            <div class="card-header-area">
                <div class="head-grid">
                    <!-- يمين -->
                    <div class="org-box">
                        <div class="line"><span class="key">اسم المتجر:</span> <span class="val">{{ $shopName ?: '—' }}</span></div>
                        <div class="line"><span class="key">العنوان:</span> <span class="val">{{ $shopAddr ?: '—' }}</span></div>
                        <div class="line"><span class="key">رقم الجوال:</span> <span class="val">{{ $shopPhone ?: '—' }}</span></div>
                    </div>
                    <!-- اللوجو وسط -->
                    <div class="logo">
                        @if($shopLogo)
                            <img src="{{ asset('storage/app/public/shop/' . $shopLogo) }}" alt="logo">
                        @endif
                    </div>
                    <!-- شمال -->
                    <div class="org-box">
                        <div class="line"><span class="key">رقم السجل التجاري:</span> <span class="val">{{ $vatRegNo ?: '—' }}</span></div>
                        <div class="line"><span class="key">الرقم الضريبي:</span> <span class="val">{{ $taxNumber ?: '—' }}</span></div>
                        <div class="line"><span class="key">البريد الإلكتروني:</span> <span class="val">{{ $shopEmail ?: '—' }}</span></div>
                    </div>
                </div>

                <div class="meta">
                    <div>{{ $voucherLabel }}: <strong>#{{ $voucher->id }}</strong></div>
                    <div>التاريخ: <strong>{{ \Carbon\Carbon::parse($voucher->date)->format('Y-m-d') }}</strong></div>
                    <div><span class="{{ $badgeClass }}">{{ $methodLabel }}: {{ $paymentMethodAr }}</span></div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- البودي -->
            <div class="card-body-area">
                <table class="details-table">
                    <tbody>
                        <tr>
                            <th class="label">{{ $amountLabel }}</th>
                            <td colspan="3" class="amount">
                                {{ number_format($voucher->amount, 2) }}
                                <span class="amount-curr">{{ $currencySymbol }} ({{ $currencyCode }})</span>
                            </td>
                        </tr>

                        @if($showChequeRow)
                        <tr>
                            <th class="label">رقم الشيك</th>
                            <td colspan="3"><strong>{{ $chequeNumber }}</strong></td>
                        </tr>
                        @endif

                        <tr>
                            <th class="label">اسم المستفيد</th>
                            <td colspan="3"><strong>{{ $beneficiary }}</strong></td>
                        </tr>

                        <tr>
                            <th class="label">{{ $creditLabel }}</th>
                            <td>{{ $voucher->creditAccount->account ?? '—' }}</td>
                            <th class="label">{{ $debitLabel }}</th>
                            <td>{{ $voucher->debitAccount->account ?? '—' }}</td>
                        </tr>

                        <tr>
                            <th class="label">البيان</th>
                            <td colspan="3" class="desc">{{ $voucher->description ?: '—' }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- التواقيع -->
                <div class="signatures">
                    <div class="sign-box">
                        <div class="sign-line"></div>
                        <div class="sign-title">توقيع المدير</div>
                    </div>
                    <div class="sign-box">
                        <div class="sign-line"></div>
                        <div class="sign-title">توقيع المحاسب</div>
                    </div>
                    <div class="sign-box">
                        <div class="sign-line"></div>
                        <div class="sign-title">توقيع المستفيد</div>
                    </div>
                </div>
            </div>

            <!-- الفوتر -->
            <div class="card-footer-area">
                <div class="muted">
                    {{ $shopPhone ?: '—' }} • {{ $shopEmail ?: '—' }}
                </div>
                <div class="d-flex non-printable">
                    <button class="btn btn-primary" onclick="window.print()" style="margin-left: 20px;">طباعة</button>
                    <a href="{{ route('admin.vouchers.index', ['type' => $isReceipt ? 'receipt' : 'payment']) }}" class="btn btn-outline-secondary">عودة</a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
