<style>
  /* Container for the entire invoice */
  .invoice-container {
    font-family: 'Cairo', Arial, sans-serif;
    margin: 0 auto;
    padding: 15px;
    width: 100%;
    max-width: 900px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: #fff;
    direction: rtl;
    text-align: right;
    box-shadow: 0 10px 28px -16px rgba(0, 0, 0, 0.15);
  }

  /* Header Section */
  .header {
    text-align: center;
    margin-bottom: 18px;
    position: relative;
  }

  .header img {
    width: 320px;
    margin-bottom: 10px;
  }

  .invoice-title {
    text-align: center;
    font-size: 28px;
    font-weight: 900;
    margin: 6px 0 10px;
    color: #0b3d91;
    border-bottom: 2px solid #0b3d91;
    padding-bottom: 8px;
  }

  .separator {
    border-top: 1px dashed #cbd5e1;
    margin: 16px 0;
  }

  /* Table */
  .invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background-color: #fafafa;
    overflow: hidden;
  }

  .invoice-table th, .invoice-table td {
    padding: 8px 10px;
    text-align: right;
    border: 1px solid #e5e7eb;
    font-weight: 600;
  }

  .invoice-table th {
    background-color: #fff7e6;
    color: #0f172a;
    font-weight: 800;
  }

  .invoice-table td {
    background-color: #fff;
    color: #475569;
    font-weight: 600;
  }

  .invoice-table tr:nth-child(even) td {
    background-color: #fbfdff;
  }

  .invoice-table tr:hover td {
    background-color: #f3f4f6;
  }

  /* Info cards */
  .two-col {
    display: flex;
    gap: 2%;
    justify-content: space-between;
    margin-bottom: 10px;
  }
  .two-col > div { width: 49%; }
  .box-title {
    text-align: center; background: #eef2ff; color: #0b3d91; padding: 6px 8px;
    border-radius: 8px; font-weight: 900; border: 1px solid #e5e7eb; margin-bottom: 6px;
  }

  /* Payment Details */
  .payment-details p {
    font-size: 16px;
    font-weight: 700;
    margin: 5px 0;
  }

  /* Footer */
  .footer {
    margin-top: 8px;
    text-align: center;
    font-size: 13px;
    color: #64748b;
  }
  .footer-logo { max-width: 120px; margin-top: 5px; }

  /* Print */
  @media print {
    .invoice-container {
      width: 100%;
      max-width: 100%;
      page-break-before: always;
      page-break-after: always;
      border: 0; box-shadow: none; border-radius: 0; padding: 0;
    }
    .invoice-title { font-size: 24px; border-bottom-width: 2px; }
  }

  .no-page-break { page-break-inside: avoid; }

  /* Compact option (if needed) */
  .compact-layout .invoice-table th,
  .compact-layout .invoice-table td {
    padding: 6px;
    font-size: 12px;
  }

  /* Small badges/info text under product name */
  .muted { color:#64748b; font-size: 12px; font-weight: 600; }
  .badge-service {
    display:inline-block; font-size:11px; padding:2px 8px; border-radius:999px;
    background:#fef3c7; color:#92400e; border:1px solid #fde68a; margin-left:6px
  }
</style>

<div class="invoice-container">
  <!-- عنوان الفاتورة -->
  <div class="invoice-title">
    @if($order['type'] == 12)
      فاتورة شراء
    @elseif($order['type'] == 24)
      مردود مشتريات
    @else
      نوع الفاتورة غير معروف
    @endif
  </div>

  <!-- لوجو + أكواد QR -->
  <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding:8px 6px;">
    <!-- شعار المتجر -->
    <div style="flex:1; text-align:center">
      <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}"
           alt="Shop Logo"
           style="max-width:110px; display:block; margin:0 auto;">
      <div style="margin-top:4px; color:#475569; font-weight:700">شعار المتجر</div>
    </div>

    <!-- QR لفاتورة الأصل (لو مردود) -->
    <div style="flex:1; text-align:center">
      @if($order['type'] == 24 && $order->parent)
        <img src="{{ asset('storage/app/public/' . $order->parent->qrcode) }}"
             alt="Parent QR Code"
             style="max-width:110px; display:block; margin:0 auto;">
        <div style="margin-top:4px; color:#475569; font-weight:700">فاتورة الشراء: {{ $order['parent_id'] }}</div>
      @endif
    </div>

    <!-- QR للفاتورة الحالية -->
    <div style="flex:1; text-align:center">
      <img src="{{ asset('storage/app/public/' . $order['qrcode']) }}"
           alt="Current QR Code"
           style="max-width:110px; display:block; margin:0 auto;">
      <div style="margin-top:4px; color:#475569; font-weight:700">فاتورة: {{ $order['id'] }}</div>
    </div>
  </div>

  <hr class="separator">

  <!-- الشركة والعميل -->
  <div class="two-col">
    <div>
      <div class="box-title">بيانات الشركة</div>
      <table class="invoice-table">
        <tr>
          <td style="width:35%; font-weight:800;">اسم المتجر</td>
          <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">العنوان</td>
          <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">رقم الجوال</td>
          <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">البريد الإلكتروني</td>
          <td>{{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">الرقم الضريبي</td>
          <td>{{ \App\Models\BusinessSetting::where(['key' => 'number_tax'])->first()->value }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">رقم السجل التجاري</td>
          <td>{{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">اسم المندوب</td>
          <td>{{ $order->seller ? $order->seller->f_name . ' ' . $order->seller->l_name : 'Seller Not Found' }}</td>
        </tr>
      </table>
    </div>

    <div>
      <div class="box-title">بيانات المورد</div>
      <table class="invoice-table">
        <tr>
          <td style="width:35%; font-weight:800;">اسم المورد</td>
          <td>{{ optional($order->supplier)->name }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">رقم السجل التجاري</td>
          <td>{{ optional($order->supplier)->c_history }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">الرقم الضريبي</td>
          <td>{{ optional($order->supplier)->tax_number }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">رقم الجوال</td>
          <td>{{ optional($order->supplier)->mobile }}</td>
        </tr>
    
        <tr>
          <td style="font-weight:800;">التاريخ</td>
          <td>{{ date('d/M/Y h:i a', strtotime($order['created_at'])) }}</td>
        </tr>
        <tr>
          <td style="font-weight:800;">نوع الدفع</td>
          <td>{{ $order->cash == 2 ? 'أجل' : 'نقدي' }}</td>
        </tr>
      </table>
    </div>
  </div>

  <hr class="separator">

  <!-- جدول المنتجات -->
  <table class="table">
    <thead>
      <tr>
        <th style="width:48px">#</th>
        <th>المنتج</th>
        <th style="width:140px">القياس</th> <!-- بدل كلمة "الوحدة" -->
        <th style="width:90px">الكمية</th>
        <th style="width:120px">السعر</th>
        <th style="width:120px">الخصم</th>
        <th style="width:110px">الضريبة</th>
        <th style="width:130px">القيمة</th>
      </tr>
    </thead>
    <tbody>
      @php
        $sub_total = 0;
        $total_tax = 0;
        $total_discount = 0;
        $grand_total = 0;
        $disunt_on_products = 0;
      @endphp

      @foreach ($order->details as $key => $detail)
        @if ($detail->product_details)
          @php
            $productArr      = json_decode($detail->product_details, true) ?: [];
            $quantity        = $detail['quantity'];
            $price           = $detail['price'];
            $totalPrice      = $price * $quantity;

            $totalOrderPrice = $order->details->sum(fn($d) => $d['price'] * $d['quantity']);
            $discountPercent = $totalOrderPrice > 0 ? ($order['extra_discount'] / $totalOrderPrice) : 0;
            $discountFromOrder   = $discountPercent * $totalPrice;
            $discountOnProduct   = ($detail->discount_on_product ?? 0) * $quantity;
            $totalDiscountAmount = $discountFromOrder + $discountOnProduct;

            $taxSetting = \App\Models\BusinessSetting::where('key', 'tax')->first();
            $taxRate    = $taxSetting ? $taxSetting->value : 0;
            $tax        = ($totalPrice - $totalDiscountAmount) * ($taxRate / 100);

            $lineTaxShown = $detail->tax_amount * $quantity; // ما يظهر بالجدول حسب بيانات السطر
            $finalPrice   = $totalPrice - $totalDiscountAmount + $lineTaxShown;

            // منطق الخدمة
            $isService = (isset($productArr['product_type']) && $productArr['product_type'] === 'service')
                         || (optional($detail->product)->product_type === 'service');

            // قياس للمنتج غير الخدمي (بدون كلمة "وحدة")
            $measureName = '—';
            if($isService){
              $measureName = 'كبرى × 1';
            } else {
              if(($detail->unit ?? 1) == 1){
                $measureName = optional(optional($detail->product)->unit)->unit_type ?? '—';
              } else {
                $measureName = optional(optional($detail->product)->unit)->subUnits->first()->name ?? '—';
              }
            }

            // تجميع
            $sub_total            += $totalPrice;
            $total_tax            += $tax;
            $disunt_on_products   += $totalDiscountAmount;
            $total_discount       += $totalDiscountAmount;
            $grand_total          += $finalPrice;
          @endphp

          <tr>
            <td>{{ $key + 1 }}</td>
            <td>
              <div style="font-weight:800">{{ $productArr['name'] ?? ($detail->product->name ?? '-') }}</div>
              <div class="muted" style="margin-top:2px">
                <span>السعر: {{ number_format($price, 2) }} ريال</span>
                <span style="margin:0 6px">•</span>
                <span>الخصم: {{ number_format($totalDiscountAmount, 2) }} ريال</span>
                @if($isService)
                  <span style="margin:0 6px">•</span><span class="badge-service">خدمة</span>
                @endif
              </div>
            </td>
            <td>{{ $measureName }}</td>
            <td>{{ number_format($quantity, 2) }}</td>
            <td>{{ number_format($totalPrice, 2) }} ريال</td>
            <td>{{ number_format($totalDiscountAmount, 2) }} ريال</td>
            <td>{{ number_format($lineTaxShown, 2) }} ريال</td>
            <td>{{ number_format($finalPrice, 2) }} ريال</td>
          </tr>
        @endif
      @endforeach
    </tbody>
  </table>

  <hr class="separator">

  <!-- إجماليات -->
  <table class="invoice-table" style="margin-top:6px">
    <tr>
      <td style="width:40%">الإجمالي الفرعي</td>
      <td>{{ number_format($sub_total, 2) }} ريال</td>
    </tr>
    <tr>
      <td>خصم المنتجات</td>
      <td>{{ number_format($disunt_on_products - $order['extra_discount'], 2) }} ريال</td>
    </tr>
    <tr>
      <td>الخصم الإضافي</td>
      <td>{{ number_format($order['extra_discount'], 2) }} ريال</td>
    </tr>
    <tr>
      <td>الضريبة</td>
      <td>{{ number_format($order['total_tax'], 2) }} ريال</td>
    </tr>
    <tr>
      <td><strong>الإجمالي</strong></td>
      <td><strong>{{ number_format($order['order_amount'], 2) }} ريال</strong></td>
    </tr>
    <tr>
      <td>المبلغ المدفوع</td>
      <td>{{ number_format($order['collected_cash'], 2) }} ريال</td>
    </tr>
    <tr>
      <td>المبلغ المتبقي</td>
      <td>{{ number_format($order['order_amount'] - $order['collected_cash'], 2) }} ريال</td>
    </tr>
  </table>

  <!-- ملاحظات الفاتورة -->
  <div style="margin-top:10px">
    <div class="box-title">ملاحظات الفاتورة</div>
    <div style="border:1px dashed #e5e7eb; border-radius:10px; padding:10px; background:#fcfdff; min-height:48px">
      {{ $order->note ?: '—' }}
    </div>
  </div>

  <!-- التوقيعات -->
  <table class="invoice-table" style="margin-top:10px">
    <tr>
      <td style="text-align:center; padding:18px;">
        <div>توقيع المحاسب</div>
        <div style="margin-top:10px">..................................................</div>
      </td>
      <td style="text-align:center; padding:18px;">
        <div>توقيع البائع</div>
        <div style="margin-top:10px">..................................................</div>
      </td>
      <td style="text-align:center; padding:18px;">
        <div>توقيع المورد</div>
        <div style="margin-top:10px">..................................................</div>
      </td>
    </tr>
  </table>

  <div class="footer">
    شكراً لتعاملكم معنا — الرجاء الاحتفاظ بهذه الفاتورة للرجوع إليها عند الحاجة.
  </div>
</div>
