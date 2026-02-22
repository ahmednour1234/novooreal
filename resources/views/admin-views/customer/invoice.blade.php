<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة إيصال استلام نقدية</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            direction: rtl;
            text-align: right;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .cheque-border {
            border: 5px double #000;
            padding: 10px;
            margin: 50px auto;
            max-width: 800px;
            position: relative;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header img {
            max-width: 100px;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
        }

        .header p {
            font-size: 16px;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th, table td {
            border: 1px solid #000;
            padding: 15px;
            font-size: 16px;
            text-align: center;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signatures div {
            width: 30%;
        }

        .signature-line {
            border-top: 2px solid #000;
            margin: 15px auto;
            width: 80%;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .cheque-border {
                margin: 0;
                border: none;
                padding: 0;
                width: 100%;
            }

            .footer {
                position: relative;
                bottom: 0;
            }

            .header img {
                max-width: 350px;
            }
        }
    </style>
</head>
<body>
    <div class="cheque-border">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}" alt="Logo">
            <h2>                        @if ($expense->tran_type == 4)
                        <span class="badge badge-danger">مبيعات</span>
          @elseif ($expense->tran_type == 0)
                        <span class="badge badge-info">رصيد افتتاحي</span>
                    @elseif ($expense->tran_type == 7)

                        <span class="badge badge-info">مرتجع مبيعات</span>
                    @elseif ($expense->tran_type == 12)
                        <span class="badge badge-warning">مشتريات</span>
                    @elseif ($expense->tran_type == 24)
                        <span class="badge badge-success">مرتجع مشتريات</span>
                    @elseif ($expense->tran_type == 13)
                        <span class="badge badge-soft-warning">سند صرف</span>
                    @elseif ($expense->tran_type == 26)
                        <span class="badge badge-soft-success">سند قبض</span>
                    @elseif ($expense->tran_type == 100)
                        <span class="badge badge-soft-success">سند صرف</span>
                                     @elseif($expense->tran_type == 30)
        <span class="badge badge-soft-success">خصم مكتسب</span>
                    @endif
            <p>{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}</p>
            <p>{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}</p>
            <p>رقم الجوال: {{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}</p>
            <p>الرقم الضريبي: {{ \App\Models\BusinessSetting::where(['key' => 'number_tax'])->first()->value }}</p>
        </div>

        <!-- Invoice Table -->
        <table>
            <tr>
                <th>التاريخ</th>
                <td>{{ $expense->date }}</td>
         <th>المبلغ</th>
<td>
    {{ $expense->credit == 0 && $expense->debit == 0 ? number_format($expense->amount, 2) : number_format($expense->credit == 0 ? $expense->debit : $expense->credit, 2) }}
    {{ ' ' . \App\CPU\Helpers::currency_symbol() }}
</td>

            </tr>
            <tr>
                <th>الحساب</th>
                <td>{{ $expense->account ? $expense->account->account : '' }}</td>
          <th>اسم العميل</th>
                <td>{{ $expense->customer_id ? $expense->customer->name : '' }}</td>
      
            </tr>
            <tr>
                <th>النوع</th>
                <td colspan="3">
                                       @if ($expense->tran_type == 4)
                        <span class="badge badge-danger">مبيعات</span>
          @elseif ($expense->tran_type == 0)
                        <span class="badge badge-info">رصيد افتتاحي</span >
                    @elseif ($expense->tran_type == 7)

                        <span class="badge badge-info">مرتجع مبيعات</span>
                    @elseif ($expense->tran_type == 12)
                        <span class="badge badge-warning">مشتريات</span>
                    @elseif ($expense->tran_type == 24)
                        <span class="badge badge-success">مرتجع مشتريات</span>
                    @elseif ($expense->tran_type == 13)
                        <span class="badge badge-soft-warning">سند صرف</span>
                    @elseif ($expense->tran_type == 26)
                        <span class="badge badge-soft-success">سند قبض</span>
                    @elseif ($expense->tran_type == 100)
                        <span class="badge badge-soft-success">سند صرف</span>
                                     @elseif($expense->tran_type == 30)
        <span class="badge badge-soft-success">خصم مكتسب</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>وذلك عن</th>
                <td colspan="3">{{ $expense->description }}</td>
            </tr>
        </table>

        <!-- Signatures -->
        <div class="signatures">
            <div>
                <p>إمضاء المحاسب</p>
                <div class="signature-line"></div>
            </div>
            <div>
                <p>إمضاء المدير المالي</p>
                <div class="signature-line"></div>
            </div>
            <div>
                <p>إمضاء المدير العام</p>
                <div class="signature-line"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>تاريخ الطباعة: {{ date('Y-m-d') }} | وقت الطباعة: {{ date('H:i') }}</p>
            <p>طبع بواسطة النظام</p>
        </div>
    </div>
</body>
</html>
