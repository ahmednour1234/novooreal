<style>
    .invoice-container {
        font-family: 'Arial', sans-serif;
        margin: 20px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background: #f0f0f0; /* Light gray background */
        direction: rtl;
        text-align: right;
    }

    .header img {
        max-width: 100px;
        margin-bottom: 10px;
    }

    .header h2, .header p, .footer p {
        margin: 5px 0;
    }

    .separator {
        border-top: 1px dashed #bbb;
        margin: 15px 0;
    }

    .invoice-details, .totals, .payment-details {
        font-size: 14px;
        color: #555;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .invoice-table th, .invoice-table td {
        padding: 10px;
        text-align: right;
        border-bottom: 1px solid #ddd;
    }

    .totals {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .totals-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .footer {
        margin-top: 0px;
        text-align: center;
    }

    .logo-qrcode {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0px;
        flex-direction: column;
        margin-top: 0px;
        padding: 0px;
        border-radius: 8px;
    }

    .footer-logo, .footer-qrcode {
        max-width: 80px;
    }

</style>

<div class="invoice-container">
    <div class="header text-center mb-4">
        <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}" alt="Shop Logo">
        <h2 class="shop-name">{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}</h2>
        <p class="shop-address">{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}</p>
        <p class="shop-contact">{{ \App\CPU\translate('رقم الجوال') }}: {{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}</p>
        <p class="shop-email">{{ \App\CPU\translate('البريد الالكتروني') }}: {{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}</p>
        <p class="vat-number">{{ \App\CPU\translate('رقم السجل التجاري') }}: {{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}</p>
    </div>

    <hr class="separator">

    <div class="invoice-details text-center mt-4">
        <p><strong>{{ \App\CPU\translate('رقم الفاتورة') }}:</strong> {{ $order['id'] }}</p>     
        <p><strong>{{ \App\CPU\translate('نوع الفاتورة') }}:</strong>فاتورة مرتجع مبيعات</p>
        <p><strong>{{ \App\CPU\translate('اسم المندوب') }}:</strong> {{ $order->seller ? $order->seller->f_name . ' ' . $order->seller->l_name : 'Seller Not Found' }}</p>
        <p><strong>{{ \App\CPU\translate('اسم العميل') }}:</strong> {{ optional($order->customer)->name }}</p>
        <p><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong> {{ optional($order->customer)->c_history }}</p>
        <p><strong>{{ \App\CPU\translate('رقم الضريبي') }}:</strong> {{ optional($order->customer)->tax_number }}</p>
        <p class="date"><strong>{{ date('d/M/Y h:i a', strtotime($order['created_at'])) }}</strong></p>
    </div>

    <hr class="separator">

    <table class="invoice-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ \App\CPU\translate('المنتج') }}</th>
                <th>{{ \App\CPU\translate('الوحدة') }}</th>
                <th>{{ \App\CPU\translate('الكمية') }}</th>
                <th>{{ \App\CPU\translate('السعر') }}</th>
            </tr>
        </thead>
        <tbody>
            @php($sub_total = 0)
            @php($total_tax = 0)
            @foreach ($order->details as $key => $detail)
                @if ($detail->product_details)
                    @php($product = json_decode($detail->product_details, true))
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <span class="product-name">{{ $product['name'] }}</span><br />
                            <span>{{ \App\CPU\translate('السعر') }}: {{ $detail['price'] }}</span><br />
                            <span>{{ \App\CPU\translate('الخصم') }}: {{ number_format($detail['discount_on_product'] * $detail['quantity'], 2) }}</span>
                        </td>
                        <td>{{ $detail->unit == 1 ? $detail->product->unit->unit_type ?? '' : $detail->product->unit->subUnits->first()?->name ?? '' }}</td>
                        <td>{{ $detail['quantity'] }}</td>
                        <td>@php($amount = ($detail['price'] - $detail['discount_on_product']) * $detail['quantity']) {{ number_format($amount, 2) }}</td>
                    </tr>
                    @php($sub_total += $amount)
                    @php($total_tax += $detail['tax_amount'] * $detail['quantity'])
                @endif
            @endforeach
        </tbody>
    </table>

    <hr class="separator">

    <dl class="totals">
        <div class="totals-row">
            <dt>{{ \App\CPU\translate('اسعار المنتجات') }}:</dt>
            <dd>{{ number_format($sub_total, 2) }}</dd>
        </div>
        <div class="totals-row">
            <dt>{{ \App\CPU\translate('الضريبة') }}:</dt>
            <dd>{{ number_format($total_tax, 2) }}</dd>
        </div>
        <div class="totals-row">
            <dt>{{ \App\CPU\translate('الاجمالي') }}:</dt>
            <dd>{{ number_format($order->order_amount, 2) }}</dd>
        </div>
        <div class="totals-row">
            <dt>{{ \App\CPU\translate('خصم إضافي') }}:</dt>
            <dd>{{ number_format($order['extra_discount'], 2) }}</dd>
        </div>
        <div class="totals-row">
            <dt>{{ \App\CPU\translate('كود الخصم') }}:</dt>
            <dd>{{ number_format($order['coupon_discount_amount'], 2) }}</dd>
        </div>
        <div class="totals-row">
            <dt><strong>{{ \App\CPU\translate('الصافي') }}:</strong></dt>
            <dd><strong>{{ number_format($order->order_amount, 2) }}</strong></dd>
        </div>
    </dl>

    <hr class="separator">

    <div class="payment-details text-center mt-4">
        <p><strong>{{ \App\CPU\translate('طريقة الدفع') }}:</strong> {{ $order->cash == 2 ? \App\CPU\translate('أجل') : \App\CPU\translate('كاش') }}</p>
        <p><strong>{{ \App\CPU\translate('المبلغ المدفوع في الحال') }}:</strong> {{ number_format($order->collected_cash, 2) }}</p>
    </div>

    <hr class="separator">

        <div class="logo-qrcode">
            <img src="{{ asset('storage/app/public//' . $order['qrcode']) }}" alt="QR Code" class="footer-qrcode">
        </div>

</div>
