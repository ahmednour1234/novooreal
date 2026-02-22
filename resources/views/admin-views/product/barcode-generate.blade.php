@extends('layouts.admin.app')

@section('title', $product->name .' - '.__('باركود').' '. date("Y/m/d"))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/barcode.css') }}"/>
<style>
  :root{
    --ink:#0f172a; --muted:#667085; --grid:#e9eef5; --brand:#0d6efd;
    --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  .card-soft{border:1px solid var(--grid); border-radius:var(--rd); box-shadow:var(--shadow)}
  .help{font-size:12px; color:var(--muted)}
  .btn-min{min-height:42px}

  /* ====== A4 + شبكة الملصقات ====== */
  #printarea{page-break-inside:auto}
  .sheet{
    width:100%;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8mm;
    padding:10mm 8mm;
    box-sizing:border-box;
    page-break-after:always;
  }
  .label{
    width:100%;
    border:1px dashed #d9e2ec;
    border-radius:12px;
    padding:6px 8px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    overflow:hidden;
  }
  .label h6{margin:0; font-weight:800; font-size:12px; color:#111827; line-height:1.2}
  .label .meta{
    display:flex; align-items:center; justify-content:space-between; gap:8px;
    margin:2px 0 4px; font-size:11px; color:#111827; font-weight:700;
  }
  .label .price{font-weight:800; font-size:12px; color:#111827}
  .label .code{font-size:10px; color:#1f2937}
  .label .barcode{margin:2px 0 0; display:flex; align-items:center; justify-content:center}
  .label .barcode svg,.label .barcode img{max-width:100%; height:28px}

  /* أحجام الملصق */
  .label--s{ min-height:34mm }
  .label--m{ min-height:44mm }
  .label--l{ min-height:58mm }

  /* إظهار/إخفاء عناصر في الطباعة */
  .hide-shop  .label .shop{ display:none !important }
  .hide-price .label .price{ display:none !important }

  /* تنبيه الشاشات الصغيرة */
  .print-note{
    background:#fff7ed; border:1px solid #ffedd5; color:#9a3412;
    border-radius:12px; padding:16px; text-align:center;
  }

  @media print{
    body{ -webkit-print-color-adjust:exact; print-color-adjust:exact }
    .no-print{ display:none !important }
    .sheet{ page-break-after:always }
    .sheet:last-child{ page-break-after:auto }
  }
</style>
@endpush

@section('content')
@php
  $size    = request('size','s'); // s|m|l
  $perPage = $size==='m' ? 24 : ($size==='l' ? 18 : 27); // 3×8 / 3×6 / 3×9
  $limit   = (int) ($limit ?? request('limit',27));
  if($limit>270) $limit = 270;
  $shopName = optional(\App\Models\BusinessSetting::where('key','shop_name')->first())->value;
@endphp

<div class="content container-fluid">
  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('توليد الباركود') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== لوحة التحكّم ====== --}}
  <div class="card card-soft no-print">
    <div class="card-body">
      <form action="{{ url()->current() }}" method="GET" id="barcodeForm">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label fw-bold">{{ \App\CPU\translate('الكود') }}</label>
            <input type="text" class="form-control" value="{{ $product->product_code }}" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">{{ \App\CPU\translate('الاسم') }}</label>
            <input type="text" class="form-control" value="{{ \Illuminate\Support\Str::limit($product->name,60) }}" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">{{ \App\CPU\translate('الكمية') }}</label>
            <input type="number" name="limit" class="form-control" min="1" max="270" value="{{ $limit }}">
            <div class="help">{{ \App\CPU\translate('الحد الأقصى') }} 270</div>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">{{ \App\CPU\translate('حجم الملصق') }}</label>
            <select name="size" class="form-select">
              <option value="s" {{ $size==='s' ? 'selected':'' }}>{{ \App\CPU\translate('صغير (3×9)') }}</option>
              <option value="m" {{ $size==='m' ? 'selected':'' }}>{{ \App\CPU\translate('متوسط (3×8)') }}</option>
              <option value="l" {{ $size==='l' ? 'selected':'' }}>{{ \App\CPU\translate('كبير (3×6)') }}</option>
            </select>
          </div>

          <div class="col-md-3">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="toggleShop" checked>
              <label class="form-check-label" for="toggleShop">{{ \App\CPU\translate('إظهار اسم المتجر') }}</label>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="togglePrice" checked>
              <label class="form-check-label" for="togglePrice">{{ \App\CPU\translate('إظهار السعر') }}</label>
            </div>
          </div>

          <div class="col-md-6">
            <div class="d-flex gap-2 justify-content-md-end">
              <button class="btn btn-info btn-min" type="submit">
                <i class="tio-barcode"></i> {{ \App\CPU\translate('توليد الباركود') }}
              </button>
              <a class="btn btn-outline-secondary btn-min"
                 href="{{ route('admin.product.barcode-generate',[$product['id']]) }}">
                <i class="tio-refresh"></i> {{ \App\CPU\translate('إعادة الضبط') }}
              </a>
              <button type="button" class="btn btn-primary btn-min" id="print_bar">
                <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
              </button>
            </div>
          </div>
        </div>
      </form>

      <div class="mt-3 print-note">
        {{ \App\CPU\translate('هذه الصفحة مخصصة للطباعة على ورق A4، قد لا تظهر بشكل مثالي على الشاشات الصغيرة.') }}
      </div>
    </div>
  </div>

  {{-- ====== مساحة الطباعة ====== --}}
  <div id="printarea" class="mt-4">
    @if ($limit)
      @php $counter = 0; @endphp
      <div class="sheet">
      @for ($i = 0; $i < $limit; $i++)
        @php $counter++; @endphp
        <div class="label {{ $size==='m' ? 'label--m' : ($size==='l' ? 'label--l' : 'label--s') }}">
          <div class="meta">
            <span class="shop">{{ $shopName }}</span>
            <span class="price">{{ number_format($product['selling_price'],2) }} {{ \App\CPU\Helpers::currency_symbol() }}</span>
          </div>
          <h6 title="{{ $product->name }}">{{ \Illuminate\Support\Str::limit($product->name, 38) }}</h6>
          <div class="barcode">{!! DNS1D::getBarcodeHTML($product->product_code, "C128") !!}</div>
          <div class="code">{{ \App\CPU\translate('الكود') }}: {{ $product->product_code }}</div>
        </div>

        @if($counter % $perPage === 0 && $i !== $limit-1)
          </div><div class="sheet">
        @endif
      @endfor
      </div>
    @endif
  </div>
</div>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function(){
    const printArea  = document.getElementById('printarea');
    const toggleShop = document.getElementById('toggleShop');
    const togglePrice= document.getElementById('togglePrice');
    const printBtn   = document.getElementById('print_bar');

    function applyToggles(){
      if(!printArea) return;
      printArea.classList.toggle('hide-shop',  !(toggleShop && toggleShop.checked));
      printArea.classList.toggle('hide-price', !(togglePrice && togglePrice.checked));
    }
    toggleShop?.addEventListener('change', applyToggles);
    togglePrice?.addEventListener('change', applyToggles);
    applyToggles();

    function printLabels(){
      applyToggles();
      const content = document.getElementById('printarea');
      if(!content){ window.print(); return; }

      const popup = window.open('', '_blank', 'width=900,height=700');
      popup.document.write(`
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
          <meta charset="utf-8">
          <title>{{ \App\CPU\translate('طباعة الباركود') }}</title>
          <link rel="stylesheet" href="{{ asset('public/assets/admin/css/barcode.css') }}">
          <style>
            ${document.querySelector('style')?.innerHTML || '' }
            @page { size: A4 portrait; margin: 8mm; }
            body{ margin:0; }
          </style>
        </head>
        <body class="${printArea.className}">
          ${content.innerHTML}
          <script>window.onload=function(){window.print(); window.close();};<\/script>
        </body>
        </html>
      `);
      popup.document.close();
    }

    printBtn?.addEventListener('click', printLabels);
  });
</script>
