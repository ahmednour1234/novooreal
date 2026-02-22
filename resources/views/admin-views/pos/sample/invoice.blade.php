<style>
  :root{
    --ink:#0f172a; --muted:#6b7280; --brand:#0b3d91; --accent:#ff851b;
    --grid:#e5e7eb; --paper:#ffffff; --zebra:#fbfdff; --rd:14px;
  }
  .invoice-paper{
    direction:rtl; text-align:right;
    font-family:'Cairo', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, 'Noto Sans Arabic', sans-serif;
    margin:0; background:#f7f8fb; padding:16px;
  }
  .invoice-container{
    max-width:900px; margin:0 auto; background:var(--paper); border:1px solid var(--grid);
    border-radius:var(--rd); box-shadow:0 14px 34px -18px rgba(2,32,71,.20);
    padding:22px 22px 16px;
  }

  /* Header */
  .header{
    display:grid; grid-template-columns:1fr auto 1fr; gap:12px; align-items:center;
    padding-bottom:14px; border-bottom:3px solid var(--brand);
  }
  .header .left, .header .right{ color:var(--ink); font-size:14px }
  .header .left p, .header .right p{ margin:2px 0 }
  .header .center{text-align:center}
  .header img{ max-width:220px; height:auto }
  .shop-name{ margin:6px 0 2px; font-size:22px; font-weight:900; color:var(--ink) }
  .shop-address, .shop-contact, .shop-email, .vat-number{ margin:0; color:var(--muted); font-size:13px }

  /* Meta box */
  .meta{
    display:grid; grid-template-columns:repeat(6, auto); gap:6px 16px; margin-top:12px; color:var(--ink);
    align-items:center;
  }
  .meta .badge{
    display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800;
    background:#e0f2fe; color:#075985; border:1px solid #bae6fd; margin-right:4px
  }

  /* Sections */
  .section-title{
    margin:14px 0 10px; font-size:15px; font-weight:900; color:var(--ink);
  }
  .info-grid{
    display:grid; grid-template-columns:1fr 1fr; gap:12px;
  }
  .card-soft{
    border:1px dashed var(--grid); border-radius:12px; padding:12px; background:#fcfdff;
  }
  .card-soft p{ margin:4px 0; color:var(--ink); font-size:14px }


  .invoice-table tbody td{
    border-bottom:1px solid var(--grid); padding:10px 12px; vertical-align:top; color:var(--ink)
  }
  .invoice-table tbody tr:nth-child(odd){ background:var(--zebra) }
  .muted{ color:var(--muted); font-size:12px }
  .badge-service{
    display:inline-block; font-size:11px; padding:2px 8px; border-radius:999px;
    background:#fef3c7; color:#92400e; border:1px solid #fde68a; margin-right:6px
  }

  /* Totals */
  .totals{ margin-top:12px; display:flex; justify-content:flex-end }
  .totals-box{ min-width:320px; border:1px solid var(--grid); border-radius:14px; overflow:hidden }
  .totals-row{
    display:flex; justify-content:space-between; padding:10px 12px; color:var(--ink)
  }
  .totals-row:nth-child(even){ background:#fff }
  .totals-row strong{ font-weight:900 }
  .totals-total{ border-top:2px solid var(--grid); background:#f9fafb; font-size:16px }

  /* Footer */
  .footer{ margin-top:10px; text-align:center; color:var(--muted); font-size:12px }
  .logo-qrcode{ display:flex; justify-content:center; align-items:center; gap:8px; flex-direction:column; margin-top:8px }
  .footer-qrcode{ max-width:100px }

  @media print{
    @page{ size:A4; margin:12mm }
    body, html{ background:#fff }
    .invoice-container{ box-shadow:none; border:0; padding:0 }
  }
</style>

<div class="invoice-paper">
  <div class="invoice-container">
    <!-- Header -->
    <div class="header">
      <div class="left">
        <p><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'vat_reg_no'])->first()->value }}</p>
        <p><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'number_tax'])->first()->value }}</p>
        <p><strong>{{ \App\CPU\translate('البريد الالكتروني') }}:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'shop_email'])->first()->value }}</p>
      </div>

      <div class="center">
        <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}" alt="Shop Logo">
        <h2 class="shop-name">{{ \App\Models\BusinessSetting::where(['key'=>'shop_name'])->first()->value }}</h2>
        <p class="shop-address">{{ \App\Models\BusinessSetting::where(['key'=>'shop_address'])->first()->value }}</p>
        <p class="shop-contact">{{ \App\CPU\translate('رقم الجوال') }}: {{ \App\Models\BusinessSetting::where(['key'=>'shop_phone'])->first()->value }}</p>
      </div>

      <div class="right" style="text-align:left">
        <!-- مكان فارغ/أو معلومات إضافية -->
      </div>
    </div>

    <!-- Meta -->
    <div class="meta">
      <div><strong>{{ \App\CPU\translate('رقم الفاتورة') }}:</strong></div>
      <div>#{{ $order['id'] }}</div>

      <div><strong>{{ \App\CPU\translate('نوع الفاتورة') }}:</strong></div>
      <div>
        @if($order->type == 12)
          <span class="badge">فاتورة مشتريات</span>
        @else
          <span class="badge">مردود مردود مشتريات</span>
        @endif
      </div>

      <div><strong>{{ \App\CPU\translate('التاريخ') }}:</strong></div>
      <div>{{ date('d/M/Y h:i a', strtotime($order['created_at'])) }}</div>

      <div><strong>{{ \App\CPU\translate('اسم المندوب') }}:</strong></div>
      <div>{{ $order->seller ? $order->seller->f_name . ' ' . $order->seller->l_name : 'Seller Not Found' }}</div>

      <div><strong>{{ \App\CPU\translate('اسم المورد') }}:</strong></div>
      <div>{{ optional($order->supplier)->name }}</div>

      <div><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong></div>
      <div>{{ optional($order->supplier)->c_history }}</div>

      <div><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong></div>
      <div>{{ optional($order->supplier)->tax_number }}</div>
    </div>

    <!-- Table -->
    <h4 class="section-title">{{ \App\CPU\translate('تفاصيل الفاتورة') }}</h4>
    <table class="table">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th style="width:38%">{{ \App\CPU\translate('المنتج') }}</th>
          <!-- حُذِف عمود "الوحدة" تمامًا -->
          <th style="width:80px">{{ \App\CPU\translate('الكمية') }}</th>
          <th style="width:120px">{{ \App\CPU\translate('السعر') }}</th>
          <th style="width:110px">{{ \App\CPU\translate('الخصم') }}</th>
          <th style="width:110px">{{ \App\CPU\translate('الضريبة') }}</th>
          <th style="width:120px">{{ \App\CPU\translate('القيمة') }}</th>
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
              $product          = json_decode($detail->product_details, true) ?: [];
              $totalPrice       = $detail['price'] * $detail['quantity'];

              $totalOrderPrice  = $order->details->sum(fn($d) => $d['price'] * $d['quantity']);
              $discountPercent  = $totalOrderPrice > 0 ? ($order['extra_discount'] / $totalOrderPrice) : 0;
              $discountAmount   = $discountPercent * $totalPrice;

              $discountOnProduct = $detail->discount_on_product ?? 0;
              $tax               = ($totalPrice - $discountAmount - $discountOnProduct) * ((\App\Models\BusinessSetting::where('key','tax')->first()->value ?? 0) / 100);

              $discountAmount   += $discountOnProduct * $detail->quantity;
              $discountAmount    = (float) number_format($discountAmount, 2, '.', '');

              // النهائي وفق منطقك الأصلي
              $lineTaxValue      = ($detail->tax_amount * $detail['quantity']); // من السطر نفسه
              $finalPrice        = $totalPrice - $discountAmount + $lineTaxValue;

              // منطق الخدمة: لو المنتج خدمة، أعتبر "كبرى × 1" (بدون كلمة وحدة)
              $isServiceLine = (isset($product['product_type']) && $product['product_type'] === 'service')
                               || (optional($detail->product)->product_type === 'service');

              // تسمية القياس للمنتج غير الخدمي (بدون كلمة "وحدة")
              $measureName = '';
              if(!$isServiceLine){
                if(($detail->unit ?? 1) == 1){
                  $measureName = optional(optional($detail->product)->unit)->unit_type ?? '';
                }else{
                  $measureName = optional(optional($detail->product)->unit)->subUnits->first()->name ?? '';
                }
              }

              // تجميع المجاميع
              $sub_total         += $totalPrice;
              $total_tax         += $tax;
              $disunt_on_products+= $discountAmount;
              $total_discount    += $discountAmount;
              $grand_total       += $finalPrice;
            @endphp

            <tr>
              <td>{{ $key + 1 }}</td>
              <td>
                <div style="font-weight:700">{{ $product['name'] ?? ($detail->product->name ?? '-') }}</div>

                @if($isServiceLine)
                  <div class="muted">
                    <span class="badge-service">خدمة</span>
                    <span>{{ \App\CPU\translate('القياس') }}: كبرى × 1</span>
                  </div>
                @elseif(!empty($measureName))
                  <div class="muted">
                    <span>{{ \App\CPU\translate('القياس') }}: {{ $measureName }}</span>
                  </div>
                @endif

                <div class="muted" style="margin-top:4px">
                  <span>{{ \App\CPU\translate('السعر') }}: {{ number_format($detail['price'], 2) }}</span>
                  <span style="margin:0 6px">•</span>
                  <span>{{ \App\CPU\translate('الخصم') }}: {{ number_format($discountAmount, 2) }}</span>
                </div>
              </td>

              <td>{{ number_format($detail['quantity'], 2) }}</td>
              <td>{{ number_format($totalPrice, 2) }}</td>
              <td>{{ number_format($discountAmount, 2) }}</td>
              <td>{{ number_format($lineTaxValue, 2) }}</td>
              <td>{{ number_format($finalPrice, 2) }}</td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
      <div class="totals-box">
        <div class="totals-row"><span>{{ \App\CPU\translate('اسعار المنتجات') }}</span><strong>{{ number_format($sub_total, 2) }}</strong></div>
        <div class="totals-row"><span>{{ \App\CPU\translate('خصم المنتجات') }}</span><strong>{{ number_format($disunt_on_products - $order['extra_discount'], 2) }}</strong></div>
        <div class="totals-row"><span>{{ \App\CPU\translate('خصم إضافي') }}</span><strong>{{ number_format($order['extra_discount'], 2) }}</strong></div>
        <div class="totals-row"><span>{{ \App\CPU\translate('الضريبة') }}</span><strong>{{ number_format($order->total_tax, 2) }}</strong></div>
        <div class="totals-row totals-total"><span>{{ \App\CPU\translate('الاجمالي') }}</span><strong>{{ number_format($order->order_amount, 2) }}</strong></div>
      </div>
    </div>

    <!-- Payment -->
    <h4 class="section-title">{{ \App\CPU\translate('بيانات الدفع') }}</h4>
    <div class="info-grid">
      <div class="card-soft">
        <p><strong>{{ \App\CPU\translate('طريقة الدفع') }}:</strong>
          {{ $order->cash == 2 ? \App\CPU\translate('أجل') : \App\CPU\translate('كاش') }}
        </p>
        <p><strong>{{ \App\CPU\translate('المبلغ المدفوع في الحال') }}:</strong>
          {{ number_format($order->collected_cash, 2) }}
        </p>
      </div>
      <div class="card-soft logo-qrcode">
        <img src="{{ asset('storage/app/public//' . $order['qrcode']) }}" alt="QR Code" class="footer-qrcode">
      </div>
    </div>

    <div class="footer">
      {{ \App\CPU\translate('شكراً لتعاملكم معنا') }} — {{ \App\CPU\translate('الرجاء الاحتفاظ بهذه الفاتورة للرجوع إليها عند الحاجة') }}.
    </div>
  </div>
</div>
