<div class="receipt-container p-4 bg-white rounded shadow-sm">
    <div class="text-center mb-4">
        <h2 class="text-primary font-weight-bold">
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}
        </h2>
        <p class="text-muted">
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}
        </p>
        <p class="text-dark"><strong>{{ \App\CPU\translate('رقم الجوال') }}:</strong> 
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}</p>
        <p class="text-dark"><strong>{{ \App\CPU\translate('البريد الإلكتروني') }}:</strong> 
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}</p>
        <p class="text-dark"><strong>{{ \App\CPU\translate('السجل التجاري') }}:</strong> 
            {{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}</p>
    </div>

    <hr class="border-dashed">

    <div class="text-center">
        <h5 class="text-dark"><strong>{{ \App\CPU\translate('رقم طلب التوريد') }}:</strong> {{ $reserveProduct['id'] }}</h5>
        <h5 class="text-dark"><strong>{{ \App\CPU\translate('اسم المندوب') }}:</strong> 
            {{ $reserveProduct->seller->f_name . ' ' . $reserveProduct->seller->l_name }}</h5>
        <h5 class="text-muted font-italic">{{ date('d/M/Y h:i a', strtotime($reserveProduct['created_at'])) }}</h5>
    </div>

    <hr class="border-dashed">

    <table class="table table-bordered mt-3">
        <thead class="bg-primary text-white">
            <tr>
                <th>#</th>
                <th>{{ \App\CPU\translate('المنتج') }}</th>
                <th>{{ \App\CPU\translate('الكمية') }}</th>
                <th>{{ \App\CPU\translate('وحدة القياس') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach (json_decode($reserveProduct->data) as $key => $detail)
                @php($product = \App\Models\Product::find($detail->product_id))
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td>
                        <strong>{{ $product->name }}</strong><br>
                        <small>{{ \App\CPU\translate('السعر') }}: {{ number_format($product->selling_price, 2) }}</small>
                    </td>
                    <td class="text-center">{{ $detail->stock }}</td>
                    <td class="text-center">كبري</td>

                </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="border-dashed">

    <div class="text-center">
        <h5 class="text-uppercase font-weight-bold text-primary">
            """ {{ \App\CPU\translate('شكراََ لك') }} """
        </h5>
    </div>
</div>

<style>
    .receipt-container {
        max-width: 600px;
        margin: auto;
        border: 1px solid #ddd;
        padding: 20px;
    }
    .border-dashed {
        border-top: 2px dashed #ccc;
    }
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }
</style>
