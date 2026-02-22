{{-- resources/views/admin/quotations/show.blade.php --}}
@extends('layouts.admin.app')

@section('content')
@php
    $isService   = ($quotation->quotation_type === 'service');
    $statusLabel = in_array($quotation->status, [0,1], true) ? 'مسودة' : 'منفّذ';

    // اسم المنفذ (البائع)
    $sellerName  = trim((optional($quotation->seller)->f_name ?? '').' '.(optional($quotation->seller)->l_name ?? ''));
    $sellerName  = $sellerName !== '' ? $sellerName : '—';

    // إجمالي فرعي
    $subTotal = 0;
    foreach ($quotation->details as $d) {
        $subTotal += ($d->price * $d->quantity);
    }
@endphp

<style>
  :root{
    --brand:#0b3d91;   /* كحلي أنيق */
    --accent:#ff851b;  /* برتقالي */
    --ink:#0f172a;
    --muted:#6b7280;
    --paper:#ffffff;
    --grid:#e5e7eb;
    --zebra:#fbfdff;
    --rd:16px;
    --shadow:0 14px 34px -18px rgba(2,32,71,.20)
  }

  .page{direction:rtl}

  /* ===== بطاقة الشاشة (قابلة للطباعة) ===== */
  .sheet{
    background:var(--paper);
    border-radius:var(--rd);
    box-shadow:var(--shadow);
    padding:28px 28px 20px;
    margin-bottom:24px;
    position:relative;
  }

  .sheet-head{
    display:flex; align-items:flex-start; justify-content:space-between; gap:16px;
    padding-bottom:18px; border-bottom:3px solid var(--brand); margin-bottom:18px;
  }
  .brand-wrap{display:flex; align-items:center; gap:14px}
  .brand-logo{
    width:56px; height:56px; border-radius:14px;
    background:linear-gradient(135deg,var(--brand),#193f7f);
    display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:20px
  }
  .brand-text{line-height:1.15}
  .brand-text h1{margin:0; font-size:22px; color:var(--ink); font-weight:800}
  .brand-text small{color:var(--muted)}

  .doc-meta{text-align:end}
  .doc-title{margin:0; font-size:28px; font-weight:800; color:var(--ink)}
  .doc-sub{margin-top:4px; color:var(--accent); font-weight:700}
  .meta-grid{
    margin-top:12px; display:grid; grid-template-columns:auto auto; gap:6px 14px; color:var(--ink)
  }
  .badge-status{
    display:inline-block; padding:6px 10px; border-radius:999px; font-size:13px; margin-top:6px;
    background:#fef3c7; color:#92400e; border:1px solid #fde68a
  }
  .badge-status.executed{background:#e0f2fe; color:#075985; border-color:#bae6fd}

  /* ===== كتل المعلومات ===== */
  .info-grid{
    display:grid; gap:16px; grid-template-columns:1fr 1fr; margin:12px 0 18px;
  }
  .info-card{
    padding:14px; border:1px dashed var(--grid); border-radius:12px; background:#fcfdff
  }
  .info-card h5{margin:0 0 8px; font-size:15px; color:var(--ink); font-weight:800}
  .info-card p{margin:0; color:var(--ink)}
  .info-card p + p{margin-top:4px}

  /* ===== الجدول ===== */
  .table-wrap{border:1px solid var(--grid); border-radius:14px; overflow:hidden}
  table.invoice{
    width:100%; border-collapse:collapse; font-size:14px;
  }
  .invoice thead th{
    background:linear-gradient(180deg,#fff7e8,#fff);
    color:var(--ink); text-align:right; border-bottom:2px solid var(--grid);
    padding:10px 12px; font-weight:800; white-space:nowrap
  }
  .invoice tbody td{
    border-bottom:1px solid var(--grid); padding:10px 12px; color:var(--ink); vertical-align:middle
  }
  .invoice tbody tr:nth-child(odd){ background:var(--zebra) }
  .t-end{text-align:left} /* لأن الاتجاه RTL */

  /* ===== الملخص ===== */
  .summary{
    margin-top:16px; display:flex; justify-content:flex-end
  }
  .summary-card{
    min-width:320px; border:1px solid var(--grid); border-radius:14px; overflow:hidden
  }
  .sum-row{
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 12px; color:var(--ink); background:#fcfdff
  }
  .sum-row:nth-child(even){ background:#fff }
  .sum-row strong{font-weight:800}
  .sum-total{
    border-top:2px solid var(--grid); background:#f9fafb; font-weight:900; font-size:16px
  }

  /* ===== أزرار العمليات ===== */
  .toolbar{
    display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin:18px 0;
  }


  /* ===== تذييل أنيق ===== */
  .foot-note{
    margin-top:14px; padding-top:10px; border-top:1px dashed var(--grid);
    color:var(--muted); font-size:13px; text-align:center
  }

  /* ===== الطباعة (A4) ===== */
  @media print{
    @page{ size:A4; margin:14mm }
    html, body{ background:white }
    .no-print{ display:none !important }
    .sheet{ box-shadow:none; border-radius:0; padding:0 }
    .content.container-fluid{ max-width:100% }
  }
    .toolbar.no-print{
    display:flex;
    gap:.5rem;            /* مسافة بين الأزرار */
  }
  .toolbar.no-print > *{
    flex:1 1 0;           /* كل عنصر ياخد نفس المساحة */
  }
  .toolbar.no-print form{
    display:flex;         /* علشان زر الفورم يتمدد */
  }
  .toolbar.no-print form .btn{
    width:100%;           /* يملأ خانته بالكامل */
  }
</style>

<div class="page content container-fluid">
  {{-- ===== مسار تنقل ===== --}}
  <div class="mb-3 no-print">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('الرئيسية') }}</a>
        </li>
        <li class="breadcrumb-item active">عرض الأسعار #{{ $quotation->id }}</li>
      </ol>
    </nav>
  </div>

  {{-- ===== ورقة العرض (قابلة للطباعة) ===== --}}
  <div id="print-sheet" class="sheet">
    <div class="sheet-head">
      <div class="brand-wrap">
        {{-- ضع لوجو شركتك إن أردت بدلاً من المربع --}}
        <div class="brand-logo">N</div>
        <div class="brand-text">
          <h1>عرض أسعار</h1>
          <small>Novoo ERP System</small>
        </div>
      </div>

      <div class="doc-meta">
        <h2 class="doc-title">#{{ $quotation->id }}</h2>
        <div class="doc-sub">Quotation</div>
        <div class="meta-grid">
          <div><strong>التاريخ:</strong></div>
          <div>{{ $quotation->created_at->format('Y-m-d') }}</div>
          <div><strong>الفرع:</strong></div>
          <div>{{ $quotation->branch->name ?? '—' }}</div>
          <div><strong>المنفّذ:</strong></div>
          <div>{{ $sellerName }}</div>
        </div>
        <span class="badge-status {{ $statusLabel === 'منفّذ' ? 'executed' : '' }}">{{ $statusLabel }}</span>
      </div>
    </div>

    {{-- ===== معلومات العميل & معلومات العرض ===== --}}
    <div class="info-grid">
      <div class="info-card">
        <h5>تفاصيل العميل</h5>
        <p><strong>الاسم:</strong> {{ $quotation->customer->name }}</p>
        <p><strong>جوال:</strong> {{ $quotation->customer->mobile }}</p>
        <p><strong>البريد:</strong> {{ $quotation->customer->email }}</p>
        <p><strong>العنوان:</strong> {{ $quotation->customer->address }}</p>
      </div>
      <div class="info-card">
        <h5>معلومات العرض</h5>
        <p><strong>رقم العرض:</strong> #{{ $quotation->id }}</p>
        <p><strong>نوع العرض:</strong> {{ $isService ? 'خدمات' : 'منتجات' }}</p>
        <p><strong>عدد البنود:</strong> {{ $quotation->details->count() }}</p>
        <p><strong>حالة التنفيذ:</strong> {{ $statusLabel }}</p>
      </div>
    </div>

    {{-- ===== جدول المنتجات/الخدمات ===== --}}
    <div class="table-wrap">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th style="width:35%">المنتج</th>
              <th style="width:10%">الكمية</th>
              <th style="width:15%">{{ $isService ? 'السعر' : 'سعر الوحدة' }}</th>
              <th style="width:15%">{{ $isService ? 'الضريبة' : 'ضريبة/وحدة' }}</th>
              <th style="width:15%">{{ $isService ? 'الخصم' : 'خصم/وحدة' }}</th>
              <th class="t-end" style="width:10%">الإجمالي</th>
            </tr>
          </thead>
          <tbody>
            @foreach($quotation->details as $detail)
              @php
                $pd   = json_decode($detail->product_details, true) ?: [];
                $line = ($detail->price + $detail->tax_amount - $detail->discount_on_product) * $detail->quantity;
              @endphp
              <tr>
                <td>{{ $pd['name'] ?? '—' }}</td>
                <td>{{ number_format($detail->quantity, 2) }}</td>
                <td>{{ number_format($detail->price, 2) }}</td>
                <td>{{ number_format($detail->tax_amount, 2) }}</td>
                <td>{{ number_format($detail->discount_on_product, 2) }}</td>
                <td class="t-end">{{ number_format($line, 2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- ===== الملخص ===== --}}
    <div class="summary">
      <div class="summary-card">
        <div class="sum-row"><span>الإجمالي الفرعي</span><strong>{{ number_format($subTotal,2) }}</strong></div>
        <div class="sum-row"><span>الخصم الإضافي</span><strong>{{ number_format($quotation->extra_discount,2) }}</strong></div>
        <div class="sum-row"><span>إجمالي الضرائب</span><strong>{{ number_format($quotation->total_tax,2) }}</strong></div>
        <div class="sum-row sum-total"><span>الإجمالي النهائي</span><strong>{{ number_format($quotation->order_amount,2) }}</strong></div>
      </div>
    </div>

    {{-- ===== ملاحظات/تذييل ===== --}}
    <div class="foot-note">
      شكراً لتعاملكم معنا. هذا العرض ساري لمدة 15 يومًا من تاريخ الإصدار ما لم يُذكر خلاف ذلك.
    </div>
  </div>

  {{-- ===== أزرار العمليات ===== --}}
@if($quotation->status != 2)
  <div class="toolbar no-print">
    <button id="btnPrint" class="btn btn-info">️طباعة</button>

    <a href="{{ route('admin.quotations.edit',$quotation->id) }}" class="btn btn-secondary">
      تعديل
    </a>

    <form action="{{ route('admin.quotations.destroy',$quotation->id) }}" method="POST" class="d-inline m-0"
          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-danger">️حذف</button>
    </form>

    <button id="openExecuteBtn"  class="btn btn-primary">تنفيذ</button>
  </div>
@endif

</div>

{{-- ===== Modal تنفيذ العرض ===== --}}
@include('admin-views.sell.partials.execute-modal', ['quotation' => $quotation, 'accounts' => $accounts, 'cost_centers' => $cost_centers])
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function(){
    // ===== عناصر التبديل في المودال =====
    const btnExecute   = document.getElementById('btnExecute');
    if(btnExecute){
      btnExecute.addEventListener('click', () => new bootstrap.Modal(document.getElementById('executeQuotationModal')).show());
    }

    const cashBtn = document.getElementById('cashBtn');
    const creditBtn = document.getElementById('creditBtn');
    const cashFields = document.getElementById('cashFields');
    const creditFields = document.getElementById('creditFields');
    const amountDisplay = document.getElementById('amountDisplay');
    const cashInput = document.getElementById('cashInput');
    const collectedCash = document.getElementById('collectedCash');
    const transactionReference = document.getElementById('transactionReference');
    const orderAmount = Number('{{ number_format($quotation->order_amount, 2, ".", "") }}');

    function selectCash(){
      cashInput.value = 1;
      collectedCash.value = orderAmount.toFixed(2);
      transactionReference.value = orderAmount.toFixed(2);
      amountDisplay.textContent = 'المبلغ المستحق: ' + orderAmount.toFixed(2);
      cashFields.classList.remove('d-none');
      creditFields.classList.add('d-none');
      cashBtn.classList.add('btn-primary');
      cashBtn.classList.remove('btn-light');
      creditBtn.classList.add('btn-light');
      creditBtn.classList.remove('btn-primary');
    }

    function selectCredit(){
      cashInput.value = 2;
      collectedCash.value = 0;
      transactionReference.value = orderAmount.toFixed(2);
      amountDisplay.textContent = 'المبلغ المستحق: 0.00';
      cashFields.classList.add('d-none');
      creditFields.classList.remove('d-none');
      creditBtn.classList.add('btn-primary');
      creditBtn.classList.remove('btn-light');
      cashBtn.classList.add('btn-light');
      cashBtn.classList.remove('btn-primary');
    }

    if(cashBtn && creditBtn){
      cashBtn.addEventListener('click', selectCash);
      creditBtn.addEventListener('click', selectCredit);
      selectCash(); // افتراضي نقداً
    }

    // ===== الطباعة الأنيقة =====
    const btnPrint = document.getElementById('btnPrint');
    if(btnPrint){
      btnPrint.addEventListener('click', function(){
        const html = buildPrintHTML();
        const win = window.open('', '_blank', 'width=900,height=1000');
        win.document.open();
        win.document.write(html);
        win.document.close();
      });
    }

    function buildPrintHTML(){
      const sheet = document.getElementById('print-sheet').innerHTML;
      const title = 'عرض أسعار #{{ $quotation->id }}';
      return `
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>${title}</title>
<style>
  @page{ size:A4; margin:14mm }
  html,body{ background:#fff; font-family:'Cairo', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans Arabic', 'Noto Sans', sans-serif; color:#0f172a; }
  .wrap{ max-width:100%; }
  .sheet{ padding:0 }
  /* نعيد استخدام جزء من أنماط الشاشة مع تبسيط */
  .head{ display:flex; justify-content:space-between; gap:16px; padding-bottom:10px; border-bottom:3px solid #0b3d91; margin-bottom:10px }
  .brand{ display:flex; align-items:center; gap:12px }
  .logo{ width:50px; height:50px; border-radius:12px; background:linear-gradient(135deg,#0b3d91,#193f7f); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px }
  .htext h1{ margin:0; font-size:22px; font-weight:800 }
  .htext small{ color:#6b7280 }
  .meta{text-align:end}
  .meta h2{ margin:0; font-size:24px; font-weight:900 }
  .meta small{ color:#ff851b; font-weight:700 }
  .grid{ margin-top:8px; display:grid; grid-template-columns:auto auto; gap:4px 12px }
  .ibox{ display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:10px 0 }
  .card{ border:1px dashed #e5e7eb; border-radius:10px; padding:10px }
  .card h5{ margin:0 0 6px; font-size:14px }
  table{ width:100%; border-collapse:collapse; font-size:13px; margin-top:8px }
  thead th{ background:linear-gradient(180deg,#fff7e8,#fff); text-align:right; padding:8px 10px; border-bottom:2px solid #e5e7eb }
  tbody td{ border-bottom:1px solid #e5e7eb; padding:8px 10px; vertical-align:middle }
  tbody tr:nth-child(odd){ background:#fbfdff }
  .t-end{text-align:left}
  .sum{ margin-top:10px; display:flex; justify-content:flex-end }
  .sbox{ min-width:300px; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden }
  .row{ display:flex; justify-content:space-between; padding:8px 10px }
  .row:nth-child(even){ background:#fff }
  .total{ border-top:2px solid #e5e7eb; background:#f9fafb; font-weight:900 }
  .note{ margin-top:8px; padding-top:8px; border-top:1px dashed #e5e7eb; color:#6b7280; font-size:12px; text-align:center }
</style>
</head>
<body onload="window.print(); window.onafterprint = () => window.close();">
  <div class="wrap">
    <div class="head">
      <div class="brand">
        <div class="logo">N</div>
        <div class="htext">
          <h1>عرض أسعار</h1>
          <small>Novoo ERP System</small>
        </div>
      </div>
      <div class="meta">
        <h2>#{{ $quotation->id }}</h2>
        <small>Quotation</small>
        <div class="grid" style="margin-top:6px">
          <div><strong>التاريخ:</strong></div><div>{{ $quotation->created_at->format('Y-m-d') }}</div>
          <div><strong>الفرع:</strong></div><div>{{ $quotation->branch->name ?? '—' }}</div>
          <div><strong>المنفّذ:</strong></div><div>{{ $sellerName }}</div>
        </div>
      </div>
    </div>

    <div class="ibox">
      <div class="card">
        <h5>تفاصيل العميل</h5>
        <div><strong>الاسم:</strong> {{ $quotation->customer->name }}</div>
        <div><strong>جوال:</strong> {{ $quotation->customer->mobile }}</div>
        <div><strong>البريد:</strong> {{ $quotation->customer->email }}</div>
        <div><strong>العنوان:</strong> {{ $quotation->customer->address }}</div>
      </div>
      <div class="card">
        <h5>معلومات العرض</h5>
        <div><strong>رقم العرض:</strong> #{{ $quotation->id }}</div>
        <div><strong>نوع العرض:</strong> {{ $isService ? 'خدمات' : 'منتجات' }}</div>
        <div><strong>عدد البنود:</strong> {{ $quotation->details->count() }}</div>
        <div><strong>حالة التنفيذ:</strong> {{ $statusLabel }}</div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width:35%">المنتج</th>
          <th style="width:10%">الكمية</th>
          <th style="width:15%">{{ $isService ? 'السعر' : 'سعر الوحدة' }}</th>
          <th style="width:15%">{{ $isService ? 'الضريبة' : 'ضريبة/وحدة' }}</th>
          <th style="width:15%">{{ $isService ? 'الخصم' : 'خصم/وحدة' }}</th>
          <th class="t-end" style="width:10%">الإجمالي</th>
        </tr>
      </thead>
      <tbody>
        @foreach($quotation->details as $detail)
          @php
            $pd   = json_decode($detail->product_details, true) ?: [];
            $line = ($detail->price + $detail->tax_amount - $detail->discount_on_product) * $detail->quantity;
          @endphp
          <tr>
            <td>{{ $pd['name'] ?? '—' }}</td>
            <td>{{ number_format($detail->quantity, 2) }}</td>
            <td>{{ number_format($detail->price, 2) }}</td>
            <td>{{ number_format($detail->tax_amount, 2) }}</td>
            <td>{{ number_format($detail->discount_on_product, 2) }}</td>
            <td class="t-end">{{ number_format($line, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="sum">
      <div class="sbox">
        <div class="row"><span>الإجمالي الفرعي</span><strong>{{ number_format($subTotal,2) }}</strong></div>
        <div class="row"><span>الخصم الإضافي</span><strong>{{ number_format($quotation->extra_discount,2) }}</strong></div>
        <div class="row"><span>إجمالي الضرائب</span><strong>{{ number_format($quotation->total_tax,2) }}</strong></div>
        <div class="row total"><span>الإجمالي النهائي</span><strong>{{ number_format($quotation->order_amount,2) }}</strong></div>
      </div>
    </div>

    <div class="note">شكراً لتعاملكم معنا. هذا العرض ساري لمدة 15 يومًا من تاريخ الإصدار ما لم يُذكر خلاف ذلك.</div>
  </div>
</body>
</html>`;
    }
  });
</script>
