<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عقد تقسيط رقم #{{ $contract->id }}</title>
    <style>
        body {
            font-family: 'amiri', DejaVu Sans, sans-serif;
            direction: rtl;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header-section .left,
        .header-section .right {
            width: 30%;
            font-size: 12px;
        }

        .header-section .logo {
            width: 30%;
            text-align: center;
        }

        .header-section .logo img {
            max-height: 70px;
        }

        h2 {
            text-align: center;
            margin: 15px 0;
            font-size: 18px;
        }

        .summary-section {
            margin: 20px 0;
            font-size: 13px;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 6px;
        }

        .summary-section p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

    <div class="header-section">
        <div class="left">
            <p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where("key", "vat_reg_no")->value("value") }}</p>
            <p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where("key", "number_tax")->value("value") }}</p>
            <p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_email")->value("value") }}</p>
        </div>
        <div class="logo">
            <img src="{{ public_path('storage/app/public/shop/' . \App\Models\BusinessSetting::where('key', 'shop_logo')->value('value')) }}" alt="شعار">
        </div>
        <div class="right">
            <p><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_name")->value("value") }}</p>
            <p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_address")->value("value") }}</p>
            <p><strong>الجوال:</strong> {{ \App\Models\BusinessSetting::where("key", "shop_phone")->value("value") }}</p>
        </div>
    </div>

    <h2>عقد تقسيط رقم #{{ $contract->id }}</h2>

    <div class="summary-section">
        <p><strong>اسم العميل:</strong> {{ $contract->customer->name ?? '-' }}</p>
        <p><strong>تاريخ بداية العقد:</strong> {{ $contract->start_date }}</p>
        <p><strong>مدة العقد:</strong> {{ $contract->duration_months }} شهر</p>
        <p><strong>نسبة الفائدة:</strong> {{ $contract->interest_percent }}%</p>
        <p><strong>الإجمالي بعد الفائدة:</strong> {{ number_format($contract->total_amount, 2) }} ريال</p>
    </div>

    <h4>جدول الأقساط</h4>
    <table>
        <thead>
            <tr>
                <th>م</th>
                <th>تاريخ الاستحقاق</th>
                <th>المبلغ</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contract->installments as $i => $ins)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $ins->due_date }}</td>
                    <td>{{ number_format($ins->amount, 2) }} ريال</td>
                    <td>
                        @switch($ins->status)
                            @case('paid') <span>مدفوع</span> @break
                            @case('pending') <span>غير مدفوع</span> @break
                            @case('canceled') <span>ملغي</span> @break
                            @default {{ $ins->status }}
                        @endswitch
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
