<div class="receipt-container p-4 bg-white rounded shadow-sm">
    <div class="text-center mb-4">
        <h2 class="shop-name font-weight-bold text-primary">
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}
        </h2>
        <p class="shop-address text-muted">
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}
        </p>
        <p class="contact-info text-dark">
            <strong>{{ \App\CPU\translate('رقم الجوال') }}:</strong> 
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}
        </p>
        <p class="contact-info text-dark">
            <strong>{{ \App\CPU\translate('البريد الإلكتروني') }}:</strong> 
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}
        </p>
        <p class="contact-info text-dark">
            <strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> 
            {{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}
        </p>
    </div>

    <hr class="my-3 border-dashed">

    <div class="text-center">
        <h5 class="text-dark">
            <strong>{{ \App\CPU\translate('رقم التحصيل') }}:</strong> 
            {{ $installment['id'] }}
        </h5>
        <h5 class="text-dark">
            <strong>{{ \App\CPU\translate('اسم المندوب') }}:</strong> 
            {{ $installment->seller->f_name . ' ' . $installment->seller->l_name }}
        </h5>
        <h5 class="text-dark">
            <strong>{{ \App\CPU\translate('اسم العميل') }}:</strong> 
            {{ $installment->customer->name ?? '---' }}
        </h5>
        <h5 class="text-muted font-italic">
            {{ date('d/M/Y h:i a', strtotime($installment['created_at'])) }}
        </h5>
        <h5 class="text-danger">
            <strong>{{ \App\CPU\translate('ملاحظة') }}:</strong> 
            {{ $installment['note'] }}
        </h5>
        <h4 class="text-success font-weight-bold">
            <strong>{{ \App\CPU\translate('المبلغ المحصل') }}:</strong> 
            {{ number_format($installment['total_price'], 2) }} {{ \App\CPU\translate('ريال') }}
        </h4>
    </div>

    <hr class="my-3 border-dashed">

    <div class="text-center">
        <h5 class="text-uppercase font-weight-bold text-primary">
            """ {{ \App\CPU\translate('شكراً لك') }} """
        </h5>
    </div>
</div>

<style>
    .receipt-container {
        max-width: 500px;
        margin: auto;
        border: 1px solid #ddd;
    }
    .border-dashed {
        border-top: 2px dashed #ccc;
    }
    .shop-name {
        font-size: 22px;
    }
    .shop-address, .contact-info {
        font-size: 14px;
    }
    .text-dark {
        font-size: 16px;
    }
</style>
