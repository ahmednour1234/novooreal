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
      padding: 20px;
      margin: 50px auto;
      max-width: 800px;
      position: relative;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    .header {
      text-align: center;
      margin-bottom: 30px;
    }
    .header img {
      max-width: 100px;
      margin-bottom: 10px;
    }
    .header h2 {
      font-size: 28px;
      margin: 0;
      font-weight: bold;
      color: #333;
    }
    .header p {
      font-size: 16px;
      margin: 5px 0;
      color: #555;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    table th,
    table td {
      border: 1px solid #000;
      padding: 12px;
      font-size: 16px;
      text-align: center;
    }
    table th {
      background-color: #f0f0f0;
      font-weight: bold;
    }
    .tax-details {
      margin-top: 30px;
      border: 2px solid #007bff;
      padding: 15px;
      background-color: #f9f9f9;
    }
    .tax-details h3 {
      margin: 0 0 15px;
      text-align: center;
      color: #007bff;
      font-size: 22px;
    }
    .tax-details table {
      width: 100%;
      border-collapse: collapse;
    }
    .tax-details th,
    .tax-details td {
      border: 1px solid #007bff;
      padding: 10px;
      font-size: 15px;
    }
    .tax-details th {
      background-color: #e0eaff;
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
      color: #555;
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
        box-shadow: none;
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
      <h2>
        @if ($expense->tran_type === 'Expense')
          مصروف
        @elseif($expense->tran_type === '2')
          أصول ثابتة
        @elseif($expense->tran_type === '100')
          سند صرف
        @elseif($expense->tran_type === '200')
          سند قبض
        @elseif($expense->tran_type === 'Transfer')
           قيد يدوي
        @elseif($expense->tran_type === '500')
          تحويل مندوب
            @elseif($expense->tran_type == 3)
                        <span class="badge badge-soft-success">تحويل مخزني</span>
        @else
          -
        @endif
      </h2>
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
        <td>{{ $expense->amount . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
      </tr>
      <tr>
        <th>الحساب المدين</th>
        <td>{{ $expense->account_to ? $expense->account_to->account : '' }}</td>
        <th>الحساب الدائن</th>
        <td>{{ $expense->account ? $expense->account->account : '' }}</td>
      </tr>
      <tr colspan="2">
        <th>مركز التكلفة </th>
         <td colspan="3">{{ $expense->costcenter ? $expense->costcenter->name : '' }}</td>
   
      </tr>
      <tr>
        <th>النوع</th>
        <td colspan="3">
          @if ($expense->tran_type === 'Expense')
            مصروف
          @elseif($expense->tran_type === '2')
            أصول ثابتة
          @elseif($expense->tran_type === '100')
            سند صرف
          @elseif($expense->tran_type === '200')
            سند قبض
          @elseif($expense->tran_type === 'Transfer')
             قيد يدوي
          @elseif($expense->tran_type === '500')
            تحويل مندوب
                        @elseif($expense->tran_type == 3)
تحويل مخزني
                        @else
            -
          @endif
        </td>
      </tr>
      <tr>
        <th>وذلك عن</th>
        <td colspan="3">{{ $expense->description }}</td>
      </tr>
    </table>

    <!-- Tax Details (if available) -->
    @if ($expense->tax_id)
      <div class="tax-details">
        <h3>تفاصيل الضريبة</h3>
        <table>
          <tr>
            <th>نوع الضريبة</th>
            <td>{{ $expense->taxe->name }}</td>
          </tr>
          <tr>
            <th>اسم المستفيد</th>
            <td>{{ $expense->name }}</td>
          </tr>
          <tr>
            <th>الرقم الضريبي</th>
            <td>
              {{ $expense->tax_number }}
            </td>
          </tr>
          <tr>
            <th>قيمة الضريبة</th>
            <td>{{ $expense->tax . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
          </tr>
        </table>
      </div>
    @endif

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
