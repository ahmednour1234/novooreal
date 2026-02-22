<style>
    /* Container for the entire invoice */
    .invoice-container {
        font-family: 'Arial', sans-serif;
        margin: 0 auto;
        padding: 20px;
        width: 100%;
        max-width: 800px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background: #fff;
        direction: rtl;
        text-align: right;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* Header Section */
    .header {
        text-align: center;
        margin-bottom: 25px;
        position: relative;
    }

    .header img {
        width: 250px;
        margin-bottom: 10px;
    }

    .header h2 {
        font-size: 32px;
        font-weight: bold;
        color: #0044cc;
    }

    .header p {
        font-size: 16px;
        color: #555;
    }

    /* Invoice Title */
    .invoice-title {
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        margin-top: 10px;
        color: #0044cc;
        border-bottom: 2px solid #0044cc;
        padding-bottom: 10px;
    }

    /* Separator for sections */
    .separator {
        border-top: 1px solid #ddd;
        margin: 20px 0;
    }

    .line-dot {
        border-top: 1px dotted #ddd;
    }

    /* Invoice Table */
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    .invoice-table th, .invoice-table td {
        padding: 8px 15px;
        text-align: right;
        border: 1px solid #ddd;
        font-weight: normal;
    }

    .invoice-table th {
        background-color: #f4f4f4;
        color: #333;
    }

    .invoice-table td {
        background-color: #fff;
        color: #666;
    }

    .invoice-table tr:nth-child(even) {
        background-color: #f1f1f1;
    }

    .invoice-table tr:hover {
        background-color: #e9e9e9;
    }

    /* Payment Details */
    .payment-details {
        font-size: 16px;
        margin-top: 10px;
    }

    .payment-details p {
        font-size: 16px;
        font-weight: bold;
        margin: 10px 0;
    }

    /* Footer Styling */
    .footer {
        margin-top: 30px;
        text-align: center;
        font-size: 14px;
        color: #777;
    }

    .footer-logo {
        max-width: 120px;
        margin-top: 5px;
    }

    /* A4 page styling for print */
    @media print {
        .invoice-container {
            width: 100%;
            max-width: 100%;
            page-break-before: always;
            page-break-after: always;
        }

        .header, .footer {
        }

        .invoice-container {
            font-size: 14px;
        }

        .invoice-title {
            font-size: 26px;
        }
    }

    /* New style for logo in top right */
    .top-logo {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 250px;
        background-color: white;
        padding: 5px;
        border-radius: 10px 0 0 0;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    /* Prevent breaking within the table */
    .no-page-break {
        page-break-inside: avoid;
    }

    /* Compact layout for larger product lists */
    .compact-layout .invoice-table th, 
    .compact-layout .invoice-table td {
        padding: 6px;
        font-size: 12px;
    }

    /* Styling for the signatures section */
    .signature-section {
        display: flex;
        justify-content: space-between;
        padding: 20px 0;
        text-align: center;
    }

    .signature-box {
        width: 30%;
        padding: 10px;
        border-top: 2px solid #ccc;
    }

    .signature-box div {
        font-size: 14px;
        color: #555;
    }

</style>

<div class="invoice-container">
    <div class="header">
        <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}" alt="Shop Logo">
        <h2>فاتورة الرحلة</h2>
        <p>رقم الرحلة: {{ $order['id'] }}</p>
    </div>

    <hr class="separator">

    <!-- Company and Customer Information -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th colspan="4">معلومات الفاتورة</th>
            </tr>
        </thead>
        <tbody>
            <!-- Company Information -->
            <tr>
                <td>اسم المتجر</td>
                <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}</td>
                <td>العنوان</td>
                <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}</td>
            </tr>
            <tr>
                <td>رقم الجوال</td>
                <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}</td>
                <td>البريد الإلكتروني</td>
                <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}</td>
            </tr>
            <tr>
                <td>رقم الضريبي</td>
                <td>{{ \App\Models\BusinessSetting::where(['key' => 'number_tax'])->first()->value }}</td>
            </tr>
            <tr>
                <td>رقم السجل التجاري</td>
                <td>{{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}</td>
                <td>اسم المندوب</td>
                <td>{{ $order->seller ? $order->seller->f_name . ' ' . $order->seller->l_name : 'Seller Not Found' }}</td>
            </tr>
            <tr>
                <td>التاريخ</td>
                <td>{{ date('d/M/Y h:i a', strtotime($order['created_at'])) }}</td>
            </tr>
        </tbody>
    </table>

    <hr class="separator">
    <hr class="line-dot">

    <center class="mt-3">
        <h5>رقم الرحلة: {{ $order->id }}</h5>
        <h5>اسم المندوب: {{ optional($order->seller)->f_name . ' ' . optional($order->seller)->l_name }}</h5>
        <h5 class="font-inone fz-10">{{ date('d/M/Y h:i a', strtotime($order->created_at)) }}</h5>
    </center>

    <table class="invoice-table no-page-break">
        <thead>
            <tr>
                <th>رقم</th>
                <th>المنتج</th>
                <th>الكمية المصروفة</th>
                <th>الكمية المتبقية</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stocks as $detail)
                <tr>
                    <td>{{ $detail->product->product_code }}</td>
                    <td>
                        <span>{{ $detail->product->name }}</span><br />
                        السعر: {{ number_format($detail->product->selling_price, 2) }} ريال
                    </td>
                    <td>
                        @php
                            $mainStockValue = is_numeric($detail->main_stock) && floor($detail->main_stock) != $detail->main_stock
                                ? $detail->main_stock * $detail->product->unit_value
                                : $detail->main_stock;
                            $unitValue = is_numeric($detail->main_stock) && floor($detail->main_stock) != $detail->main_stock
                                ? $detail->product->unit->unit_type
                                : $detail->product->unit->subUnits->first()?->name;
                        @endphp
                        {{ $mainStockValue }} {{$unitValue}}
                    </td>
                    <td>
                        @php
                            $StockValue = is_numeric($detail->stock) && floor($detail->stock) != $detail->stock
                                ? $detail->stock * $detail->product->unit_value
                                : $detail->stock;
                            $unitValuestock = is_numeric($detail->stock) && floor($detail->stock) != $detail->stock
                                ? $detail->product->unit->unit_type
                                : $detail->product->unit->subUnits->first()?->name;
                        @endphp
                        {{ $StockValue }} {{$unitValuestock}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="separator">

    <table class="invoice-table">
        <tr>
            <td>رقم السيارة</td>
            <td>{{ $order->statistcs->vehicle_code }}</td>
        </tr>
        <tr>
            <td>اجمالي الكميات المنصرفة</td>
            <td>{{ $stocks->sum('main_stock') }}</td>
        </tr>
        <tr>
            <td>اجمالي الكميات المتبقية</td>
            <td>{{ $stocks->sum('stock') }}</td>
        </tr>
        <tr>
            <td>اجمالي المبيعات النقدي</td>
            <td>{{ $totalordercash }} ريال</td>
        </tr>
           <tr>
            <td>اجمالي المبيعات الشيكة</td>
            <td>{{ $totalordershabaka }} ريال</td>
        </tr>
           <tr>
            <td>اجمالي المبيعات اجل</td>
            <td>{{ $totalordercredit }} ريال</td>
        </tr>
           <tr>
            <td>اجمالي  المرتجعات</td>
            <td>{{ $ordersreturncredit }} ريال</td>
        </tr>
    </table>

    <hr class="line-dot">

    <!-- Footer -->
    <div class="footer">
        <p>تمت الفاتورة بواسطة النظام الإلكتروني</p>
    </div>
    <table class="invoice-table">

    <tr>
        <td style="text-align: center; padding: 20px;">
            <div>توقيع المحاسب</div>
            ..................................................
        </td>
        <td style="text-align: center; padding: 20px;">
            <div>توقيع المندوب</div>
            ..................................................
        </td>
        <td style="text-align: center; padding: 20px;">
            <div>توقيع المدير</div>
            ..................................................
        </td>
    </tr>
    </table>

</div>

