@php
  use Carbon\Carbon;

  // ===== Company =====
  $shopName    = \App\Models\BusinessSetting::where('key','shop_name')->first()->value ?? '—';
  $taxNumber   = \App\Models\BusinessSetting::where('key','number_tax')->first()->value ?? '—';
  $crNumber    = \App\Models\BusinessSetting::where('key','vat_reg_no')->first()->value ?? '—';
  $shopAddress = \App\Models\BusinessSetting::where('key','shop_address')->first()->value ?? '—';

  $logoVal = \App\Models\BusinessSetting::where('key','shop_logo')->first()->value ?? null;
  $logoUrl = $logoVal ? asset('storage/app/public/shop/' . $logoVal) : null;

  // ===== Payment ===== (غيّر keys حسب مشروعك)
  $bankName    = \App\Models\BusinessSetting::where('key','bank_name')->first()->value ?? '—';
  $bankAccount = \App\Models\BusinessSetting::where('key','bank_account')->first()->value ?? '—';
  $bankIban    = \App\Models\BusinessSetting::where('key','bank_iban')->first()->value ?? '—';

  // ===== Invoice Meta =====
  $invoiceNo   = $order->invoice_id ?? ('INV-' . $order->id);
  $invoiceDate = $order->date ?? $order->created_at;

  // ===== Customer =====
  $customerName    = optional($order->customer)->name ?? '—';
  $customerTax     = optional($order->customer)->tax_number ?? '—';
  $customerAddress = optional($order->customer)->address
    ?? (optional($order->customer)->street_address ?? '—');

  // ===== VAT rate =====
  $taxSetting = \App\Models\BusinessSetting::where('key', 'tax')->first();
  $vatRate = (float) ($taxSetting->value ?? 15);

  // ===== Build lines (like screenshot columns) =====
  $rows = [];
  $sumEx = 0.0; $sumVat = 0.0; $sumInc = 0.0;

  $totalOrderPrice = $order->details->sum(fn($d) => (float)$d['price'] * (float)$d['quantity']);
  $extraDiscount   = (float)($order->extra_discount ?? 0);
  $discountPercent = $totalOrderPrice > 0 ? ($extraDiscount / $totalOrderPrice) : 0;

  foreach ($order->details as $detail) {
    if (!$detail->product_details) continue;

    $p = json_decode($detail->product_details, true) ?: [];
    $name = $p['name'] ?? ($detail->product->name ?? '—');

    $qty   = (float)($detail->quantity ?? 0);
    $price = (float)($detail->price ?? 0);
    $gross = $qty * $price;

    $discountFromOrder = $discountPercent * $gross;
    $discountOnProduct = ((float)($detail->discount_on_product ?? 0)) * $qty;
    $discount = $discountFromOrder + $discountOnProduct;

    $ex = max($gross - $discount, 0);

    $vat = (float)($detail->tax_amount ?? 0) * $qty;
    if ($vat <= 0) $vat = $ex * ($vatRate / 100);

    $inc = $ex + $vat;

    $sumEx  += $ex;
    $sumVat += $vat;
    $sumInc += $inc;

    $rows[] = [
      'item' => $detail->product_id ?? '—',
      'desc' => $name,
      'qty'  => $qty,
      'price'=> $price,
      'ex'   => $ex,
      'rate' => $vatRate,
      'vat'  => $vat,
      'inc'  => $inc,
    ];
  }

  // Totals
  $finalTotal = (float)($order->order_amount ?? $sumInc);
  $finalVat   = (float)($order->total_tax ?? $sumVat);
  $finalEx    = (float)($finalTotal - $finalVat);
@endphp

<style>
  /* ===== Base ===== */
  .inv-page{
    direction: rtl;
    text-align: right;
    font-family: 'Cairo', Arial, sans-serif;
    background:#fff;
    width:100%;
    max-width: 920px;
    margin: 0 auto;
    padding: 22px 22px 40px;
    color:#111827;
  }
  .inv-page *{ box-sizing:border-box; }

  /* ===== Header layout without changing direction =====
     We control placement using AREAS only */
  .inv-header{
    display:grid;
    grid-template-columns: 180px 1fr 160px;
    gap: 10px;
    align-items:start;
    grid-template-areas: "logo company qr"; /* LOGO left, COMPANY center, QR right */
  }

  .inv-logo{ grid-area: logo; text-align:left; padding-top:6px; }
  .inv-logo img{ max-width:170px; max-height:70px; object-fit:contain; }

  .inv-company{ grid-area: company; text-align:center; padding-top:2px; }
  .inv-company .title{ font-weight:900; font-size:15px; margin-bottom:4px; }
  .inv-company .line{ font-size:13px; font-weight:700; line-height:1.5; }

  .inv-qr{ grid-area: qr; text-align:right; }
  .inv-qr .qr-title{ font-size:14px; font-weight:900; margin-bottom:6px; }
  .inv-qr img{ width:140px; height:140px; object-fit:contain; display:block; }

  /* ===== Meta row ===== */
  .inv-meta{
    margin-top: 12px;
    display:grid;
    grid-template-columns: 180px 1fr 160px;
    gap: 10px;
    align-items:start;
    grid-template-areas: "customer spacer invno"; /* customer left, invno right */
  }

  .inv-customer{ grid-area: customer; font-size:13px; font-weight:800; line-height:1.75; text-align:left; }
  .inv-customer .row{ display:flex; gap:8px; justify-content:flex-start; }
  .inv-customer .label{ min-width:72px; font-weight:900; }

  .inv-invno{ grid-area: invno; font-size:13px; font-weight:900; line-height:1.75; text-align:right; }
  .inv-invno .row{ display:flex; gap:8px; justify-content:flex-end; }
  .inv-invno .label{ min-width:50px; }

  /* ===== Table ===== */
  .inv-table{
    width:100%;
    border-collapse: collapse;
    margin-top: 14px;
    font-size: 13px;
  }
  .inv-table th, .inv-table td{
    border:1px solid #cbd5e1;
    padding: 10px 8px;
    text-align:center;
    font-weight:800;
  }
  .inv-table thead th{
    background:#dbe7f3;
    font-weight:900;
  }
  .inv-table td.desc{
    text-align:right;
    padding-right:12px;
  }

  /* ===== Bottom ===== */
  .inv-bottom{
    margin-top: 16px;
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    align-items:start;
    grid-template-areas: "payment totals"; /* payment left, totals right */
  }

  .inv-payment{ grid-area: payment; text-align:left; margin-top: 18px; font-weight:900; }
  .inv-payment .ptitle{ font-size:15px; margin-bottom:8px; }
  .inv-payment .pline{ font-size:13px; line-height:1.75; font-weight:800; }

  .inv-totals-wrap{ grid-area: totals; display:flex; justify-content:flex-end; }
  .inv-totals{
    width: 330px;
    font-weight:900;
    font-size:13px;
  }
  .inv-totals .trow{
    display:flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #111827;
  }
  .inv-totals .trow.light{
    border-bottom: 1px solid #cbd5e1;
  }
  .inv-totals .trow:last-child{ border-bottom:0; }

  @media print{
    .inv-page{ max-width:100%; padding:0; }
  }
</style>

<div class="inv-page">

  {{-- HEADER: Logo LEFT / Company CENTER / QR RIGHT --}}
  <div class="inv-header">
    <div class="inv-logo">
        <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}" alt="Logo">
    </div>

    <div class="inv-company">
      <div class="title">{{ $shopName }}</div>
      <div class="line">الرقم الضريبي: {{ $taxNumber }}</div>
      <div class="line">السجل التجاري: {{ $crNumber }}</div>
      <div class="line">{{ $shopAddress }}</div>
    </div>

    <div class="inv-qr">
      <div class="qr-title">فاتورة ضريبية</div>
      <img src="{{ asset('storage/app/public/' . $order->qrcode) }}" alt="QR">
    </div>
  </div>

  {{-- META: Customer LEFT / Invoice No+Date RIGHT --}}
  <div class="inv-meta">
    <div class="inv-customer">
      <div class="row"><div class="label">العميل:</div><div>{{ $customerName }}</div></div>
      <div class="row"><div class="label">الرقم الضريبي:</div><div>{{ $customerTax }}</div></div>
      <div class="row"><div class="label">العنوان:</div><div>{{ $customerAddress }}</div></div>
    </div>

    <div></div>

    <div class="inv-invno">
      <div class="row"><div class="label">الرقم</div><div>{{ $invoiceNo }}</div></div>
      <div class="row"><div class="label">التاريخ</div><div>{{ Carbon::parse($invoiceDate)->format('Y/m/d') }}</div></div>
    </div>
  </div>

  {{-- TABLE --}}
  <table class="inv-table">
    <thead>
      <tr>
        <th style="width:70px">البند</th>
        <th>الوصف</th>
        <th style="width:80px">الكمية</th>
        <th style="width:95px">السعر</th>
        <th style="width:150px">المجموع بدون الضريبة</th>
        <th style="width:90px">نسبة الضريبة</th>
        <th style="width:110px">قيمة الضريبة</th>
        <th style="width:110px">المجموع</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r['item'] }}</td>
          <td class="desc">{{ $r['desc'] }}</td>
          <td>{{ number_format($r['qty'], 0) }}</td>
          <td>{{ number_format($r['price'], 2) }}</td>
          <td>{{ number_format($r['ex'], 2) }}</td>
          <td>{{ rtrim(rtrim(number_format($r['rate'],2),'0'),'.') }}%</td>
          <td>{{ number_format($r['vat'], 2) }}</td>
          <td>{{ number_format($r['inc'], 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- BOTTOM: Payment LEFT / Totals RIGHT --}}
  <div class="inv-bottom">

    <div class="inv-totals-wrap">
      <div class="inv-totals">
        <div class="trow light">
          <div>الإجمالي قبل الضريبة</div>
          <div>{{ number_format($finalEx, 2) }}</div>
        </div>

        <div class="trow light">
          <div>القيمة المضافة ({{ rtrim(rtrim(number_format($vatRate,2),'0'),'.') }}%)</div>
          <div>{{ number_format($finalVat, 2) }}</div>
        </div>

        <div class="trow">
          <div>الإجمالي (ر.س)</div>
          <div>{{ number_format($finalTotal, 2) }}</div>
        </div>
      </div>
    </div>
  </div>

</div>
