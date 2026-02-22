<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض سعر #{{ $quotation->id }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 14px; color: #444; }
        .header, .footer { width: 100%; text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .info, .customer, .totals { width: 100%; margin-bottom: 20px; }
        .info td, .customer td, .totals td { padding: 5px; }
        .info td.label, .customer td.label { font-weight: bold; width: 150px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th, table.items td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        table.items th { background: #f0f0f0; }
        .totals { float: left; width: 40%; }
        .totals tr td:first-child { text-align: left; }
        .qr { position: absolute; bottom: 20px; left: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>عرض سعر</h1>
        <p>رقم العرض: {{ $quotation->id }} | التاريخ: {{ \Carbon\Carbon::parse($quotation->date)->format('Y-m-d') }}</p>
    </div>

    <table class="customer">
        <tr>
            <td class="label">اسم العميل:</td>
            <td>{{ $quotation->customer->name }}</td>
        </tr>
        <tr>
            <td class="label">الهاتف:</td>
            <td>{{ $quotation->customer->mobile }}</td>
        </tr>
        <tr>
            <td class="label">البريد الإلكتروني:</td>
            <td>{{ $quotation->customer->email }}</td>
        </tr>
        <tr>
            <td class="label">الرقم الضريبي:</td>
            <td>{{ $quotation->customer->tax_number }}</td>
        </tr>
        <tr>
            <td class="label">العنوان:</td>
            <td>{{ $quotation->customer->address }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكود</th>
                <th>الوحدة</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>ضريبة الوحدة</th>
                <th>خصم الوحدة</th>
                <th>إجمالي الصف</th>
            </tr>
        </thead>
        <tbody>
        @foreach($details as $item)
            <tr>
                <td>{{ $item->product_details['name'] ?? '' }}</td>
                <td>{{ $item->product_details['product_code'] ?? '' }}</td>
                <td>{{ $item->unit == 0 ? 'كبري' : 'صغري' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->tax_amount, 2) }}</td>
                <td>{{ number_format($item->discount_on_product, 2) }}</td>
                <td>{{ number_format(($item->price - $item->discount_on_product + $item->tax_amount) * $item->quantity, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">إجمالي الضريبة:</td>
            <td>{{ number_format($quotation->total_tax, 2) }}</td>
        </tr>
        <tr>
            <td class="label">إجمالي الخصومات:</td>
            <td>{{ number_format($quotation->extra_discount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">الإجمالي النهائي:</td>
            <td>{{ number_format($quotation->order_amount, 2) }}</td>
        </tr>
    </table>

    <div class="qr">
        <img src="{{ public_path('storage/' . $quotation->qrcode) }}" alt="QR Code" width="100">
    </div>
</body>
</html>
