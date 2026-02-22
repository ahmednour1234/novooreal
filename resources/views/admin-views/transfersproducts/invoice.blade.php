
    <style>
        /* Overall printable area styles */
        .printableArea {
            direction: rtl;
            margin: 20px;
        }
        /* Company header styles */
        .company-header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .company-header .company-info {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-header .company-info h1 {
            font-size: 28px;
            margin: 0;
        }
        .company-header .company-info p {
            margin: 2px 0;
            font-size: 14px;
        }
        .company-header .contact-info {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        .company-header .contact-info div {
            margin: 0 15px;
            font-size: 13px;
        }
        .company-header .logo {
            text-align: center;
            margin-top: 10px;
        }
        .company-header .logo img {
            max-height: 80px;
        }
        .company-header .print-date {
            text-align: center;
            margin-top: 10px;
            font-size: 13px;
        }
        /* Transfer details table */
        .transfer-details {
            margin-bottom: 20px;
        }
        .transfer-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .transfer-details th,
        .transfer-details td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            font-size: 14px;
        }
        .transfer-details th {
            background-color: #f2f2f2;
        }
        /* Transfer items table */
        .transfer-items {
            margin-top: 20px;
        }
        .transfer-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .transfer-items th,
        .transfer-items td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            font-size: 14px;
        }
        .transfer-items th {
            background-color: #f2f2f2;
        }
        .transfer-items tfoot tr th {
            text-align: right;
        }
        /* Print media rules */
        @media print {
            body * {
                visibility: hidden;
            }
            .printableArea, .printableArea * {
                visibility: visible;
            }
            .printableArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>

<div class="container-fluid printableArea">
    @php
        // Fetch business settings once and key by 'key'
        $settings = \App\Models\BusinessSetting::whereIn('key', [
            'shop_name', 'shop_address', 'shop_phone', 'shop_email',
            'vat_reg_no', 'number_tax', 'shop_logo'
        ])->get()->keyBy('key');

        $shopName    = $settings['shop_name']->value ?? 'اسم المتجر';
        $shopAddress = $settings['shop_address']->value ?? 'عنوان المتجر';
        $shopPhone   = $settings['shop_phone']->value ?? 'رقم الجوال';
        $shopEmail   = $settings['shop_email']->value ?? 'البريد الإلكتروني';
        $vatRegNo    = $settings['vat_reg_no']->value ?? 'رقم السجل التجاري';
        $numberTax   = $settings['number_tax']->value ?? 'الرقم الضريبي';
        $shopLogo    = $settings['shop_logo']->value ?? 'default-logo.png';
    @endphp

    <!-- Company Header -->
    <div class="company-header">
        <div class="company-info">
            <h1>{{ $shopName }}</h1>
            <p>{{ $shopAddress }}</p>
        </div>
        <div class="contact-info">
            <div><strong>رقم الجوال:</strong> {{ $shopPhone }}</div>
            <div><strong>البريد الإلكتروني:</strong> {{ $shopEmail }}</div>
            <div><strong>الرقم الضريبي:</strong> {{ $numberTax }}</div>
            <div><strong>رقم السجل التجاري:</strong> {{ $vatRegNo }}</div>
        </div>
        <div class="logo">
            <img src="{{ asset('storage/app/public/shop/' . $shopLogo) }}" alt="شعار المتجر">
        </div>
        <div class="print-date">
            <p><strong>تاريخ الطباعة:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    <!-- Transfer Details -->
    <div class="transfer-details">
        <h3>تفاصيل التحويل</h3>
        <table>
            <tbody>
                <tr>
                    <th>رقم التحويل</th>
                    <td>{{ $transfer->transfer_number }}</td>
                </tr>
                <tr>
                    <th>الفرع المحول</th>
                    <td>{{ $transfer->sourceBranch->name ?? 'غير متوفر' }}</td>
                </tr>
                <tr>
                    <th>الفرع المحول له</th>
                    <td>{{ $transfer->destinationBranch->name ?? 'غير متوفر' }}</td>
                </tr>
                <tr>
                    <th>إجمالي التحويل</th>
                    <td>{{ number_format($transfer->total_amount, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
                </tr>
                <tr>
                    <th>تم التحويل بواسطة</th>
                    <td>{{ $transfer->createdBy->f_name ?? 'غير متوفر' }}</td>
                </tr>
                <tr>
                    <th>تمت الموافقة بواسطة</th>
                    <td>{{ $transfer->approvedBy->f_name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>تاريخ التحويل</th>
                    <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                <tr>
                    <th>حالة التحويل</th>
                    <td>
                        @if($transfer->status == 'approved')
                            تمت الموافقة
                        @elseif($transfer->status == 'pending')
                            معلقة
                        @elseif($transfer->status == 'rejected')
                            تم الرفض
                        @elseif($transfer->status == 'draft')
                            مسودة
                        @else
                            {{ $transfer->status }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>ملاحظات</th>
                    <td>{{ $transfer->notes }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Transfer Items -->
    <div class="transfer-items">
        <h3>الأصناف المحولة</h3>
        <table>
            <thead>
                <tr>
                    <th>اسم المنتج</th>
                    <th>السعر (لكل وحدة)</th>
                    <th>الكمية</th>
                    <th>الوحدة</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'غير متوفر' }}</td>
                    <td>{{ number_format($item->cost, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ number_format($item->total_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align:right">الإجمالي العام:</th>
                    <th>{{ number_format($transfer->total_amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
