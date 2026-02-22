{{-- resources/views/admin-views/inventory_adjustments/show.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('عرض أمر تسوية المخزون'))

@push('css_or_js')
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --brand:#0d6efd;
      --bg:#f8fafc; --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
      --ok:#16a34a; --warn:#d97706; --bad:#dc2626; --info:#0ea5e9;
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}

    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{font-size:1.15rem;margin:0;color:var(--ink);font-weight:800}

    /* أزرار موحّدة المقاس */
    .toolbar{
      display:grid;
      grid-template-columns: repeat(3, minmax(180px,1fr));
      gap:12px; width:100%;
    }
    @media (max-width: 640px){ .toolbar{ grid-template-columns: 1fr; } }
    .tool-item{ width:100%; }
    .tool-item form{ margin:0; }
    .toolbar .btn{ min-height:44px; width:100%; }

    .meta-grid{display:grid;grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px}
    @media (max-width: 576px){ .meta-grid{grid-template-columns:1fr} }
    .form-label{font-weight:700;color:#111827}
    .form-control[readonly]{background:#f8fafc}

    .status-badge{padding:.35rem .6rem;border-radius:999px;font-size:.78rem;font-weight:700;display:inline-flex;align-items:center;gap:6px}
    .st-pending{background:#fff7ed;color:#9a3412}
    .st-approved{background:#ecfdf5;color:#065f46}
    .st-rejected{background:#fef2f2;color:#991b1b}
    .st-completed{background:#eff6ff;color:#1d4ed8}

    .table thead th{background:#f3f6fb}
    .table td,.table th{vertical-align:middle}
    .table-hover tbody tr:hover{background:#f9fbff}

    /* إيصال الاعتماد للطباعة (نسخة العرض داخل الصفحة) */
    .receipt{padding:14px}
    .receipt .hdr{
      display:grid; grid-template-columns: 1fr auto 1fr; align-items:center; gap:12px;
      border-bottom:2px solid #11182720; padding-bottom:12px; margin-bottom:14px
    }
    .receipt .hdr .info p{margin:2px 0;font-size:13px}
    .receipt .hdr .logo{ text-align:center }
    .receipt .hdr .logo img{
      max-height:64px; max-width:140px; width:auto; height:auto; object-fit:contain;
    }
    .receipt h3{margin:0 0 8px;font-size:18px;color:#111827}
    .receipt small{color:#6b7280}
    .totals{margin-top:10px;display:flex;gap:14px;flex-wrap:wrap}

    /* التواقيع جنب بعض دائماً */
    .sig-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:24px}
    .sig-box{border:1px dashed #cbd5e1;border-radius:10px;padding:14px;min-height:120px;display:flex;flex-direction:column;justify-content:space-between}
    .sig-box .line{height:1px;background:#cbd5e1;margin:18px 0 8px}
    .sig-box .role{font-weight:700;color:#111827}

    /* عناصر خاصة بالطباعة في نافذة منفصلة تُحقن عبر JS */
    @media print { .no-print{display:none!important} }
  </style>
@endpush

@section('content')
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
        <li class="breadcrumb-item">
          <a href="{{ route('admin.inventory_adjustments.index') }}" class="text-secondary">
            {{ \App\CPU\translate('تسويات المخزون') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('عرض أمر تسوية المخزون') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== رأس الصفحة + أزرار ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1 class="mb-0">
        {{ \App\CPU\translate('أمر تسوية') }} #{{ $adjustment->id }}
        @php
          $st = $adjustment->status;
          $cls = $st==='approved' ? 'st-approved' : ($st==='pending' ? 'st-pending' : ($st==='rejected' ? 'st-rejected' : 'st-completed'));
          $label = $st==='approved' ? \App\CPU\translate('معتمد')
                  : ($st==='pending' ? \App\CPU\translate('قيد الانتظار')
                  : ($st==='rejected' ? \App\CPU\translate('مرفوض')
                  : \App\CPU\translate('منتهي')));
        @endphp
        <span class="status-badge {{ $cls }}">{{ $label }}</span>
      </h1>

      <div class="toolbar no-print">
        {{-- طباعة --}}
        <div class="tool-item">
          <button type="button" class="btn btn-info" onclick="printReceipt()">
            <i class="tio-print"></i> {{ \App\CPU\translate('طباعة إيصال الاعتماد') }}
          </button>
        </div>
        {{-- اعتماد --}}
        <div class="tool-item">
          <form action="{{ route('admin.inventory_adjustments.approve', $adjustment->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary"
              @if(in_array($adjustment->status, ['approved','completed'])) disabled @endif>
              <i class="tio-done"></i> {{ \App\CPU\translate('اعتماد') }}
            </button>
          </form>
        </div>
        {{-- إنهاء --}}
        <div class="tool-item">
          <form action="{{ route('admin.inventory_adjustments.complete', $adjustment->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary"
              @if(in_array($adjustment->status, ['pending','completed'])) disabled @endif>
              <i class="tio-checkmark-circle-outlined"></i> {{ \App\CPU\translate('إنهاء') }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ====== تفاصيل عامة ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="meta-grid">
      <div>
        <label class="form-label">{{ \App\CPU\translate('الفرع') }}</label>
        <input type="text" class="form-control" value="{{ $adjustment->branch->name ?? '—' }}" readonly>
      </div>
      <div>
        <label class="form-label">{{ \App\CPU\translate('تاريخ التسوية') }}</label>
        <input type="text" class="form-control" value="{{ $adjustment->adjustment_date }}" readonly>
      </div>
      <div>
        <label class="form-label">{{ \App\CPU\translate('الحالة الحالية') }}</label>
        <input type="text" class="form-control" value="{{ $label }}" readonly>
      </div>
      <div>
        <label class="form-label">{{ \App\CPU\translate('ملاحظات') }}</label>
        <input type="text" class="form-control" value="{{ $adjustment->notes ?? '—' }}" readonly>
      </div>
    </div>
  </div>

  {{-- ====== بنود التسوية ====== --}}
  <div class="card-soft p-3">
    <h6 class="mb-3">{{ \App\CPU\translate('بنود التسوية') }}</h6>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('المنتج') }}</th>
            <th>{{ \App\CPU\translate('الكمية النظامية') }}</th>
            <th>{{ \App\CPU\translate('الكمية الجديدة') }}</th>
            <th>{{ \App\CPU\translate('الفرق') }}</th>
            <th>{{ \App\CPU\translate('السبب') }}</th>
          </tr>
        </thead>
        <tbody>
          @php $sumDiff = 0; @endphp
          @foreach($adjustment->items as $i => $item)
            @php
              $diff = (float)$item->new_system_quantity - (float)$item->adjustment_amount;
              $sumDiff += $diff;
            @endphp
            <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $item->product->name ?? '—' }}</td>
              <td>{{ number_format($item->adjustment_amount, 2) }}</td>
              <td>{{ number_format($item->new_system_quantity, 2) }}</td>
              <td class="{{ $diff >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($diff, 2) }}
              </td>
              <td>{{ $item->reason ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">{{ \App\CPU\translate('إجمالي الفروق') }}</th>
            <th class="{{ $sumDiff >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($sumDiff, 2) }}</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- ====== منطقة الإيصال للطباعة (محتوى) ====== --}}
  @php
    $shopName    = optional(\App\Models\BusinessSetting::where('key','shop_name')->first())->value;
    $shopAddress = optional(\App\Models\BusinessSetting::where('key','shop_address')->first())->value;
    $shopPhone   = optional(\App\Models\BusinessSetting::where('key','shop_phone')->first())->value;
    $shopEmail   = optional(\App\Models\BusinessSetting::where('key','shop_email')->first())->value;
    $vatRegNo    = optional(\App\Models\BusinessSetting::where('key','vat_reg_no')->first())->value;
    $taxNumber   = optional(\App\Models\BusinessSetting::where('key','number_tax')->first())->value;
    $logo        = optional(\App\Models\BusinessSetting::where('key','shop_logo')->first())->value;
    $logoUrl     = $logo ? asset('storage/app/public/shop/'.$logo) : null;
  @endphp

  <div id="printArea" class="d-none">
    <div class="receipt" dir="rtl">
      <div class="hdr">
        <div class="info">
          <p><strong>{{ \App\CPU\translate('اسم المتجر') }}:</strong> {{ $shopName ?? '—' }}</p>
          <p><strong>{{ \App\CPU\translate('العنوان') }}:</strong> {{ $shopAddress ?? '—' }}</p>
          <p><strong>{{ \App\CPU\translate('الهاتف') }}:</strong> {{ $shopPhone ?? '—' }} — <strong>{{ \App\CPU\translate('البريد') }}:</strong> {{ $shopEmail ?? '—' }}</p>
        </div>
        <div class="logo">
          @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo">
          @endif
        </div>
        <div class="info" style="text-align:left">
          <p><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong> {{ $vatRegNo ?? '—' }}</p>
          <p><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> {{ $taxNumber ?? '—' }}</p>
          <p><strong>{{ \App\CPU\translate('التاريخ') }}:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
      </div>

      <h3>{{ \App\CPU\translate('إيصال اعتماد تسوية مخزون') }}</h3>
      <small>
        {{ \App\CPU\translate('رقم التسوية') }}: #{{ $adjustment->id }}
        — {{ \App\CPU\translate('الفرع') }}: {{ $adjustment->branch->name ?? '—' }}
        — {{ \App\CPU\translate('تاريخ التسوية') }}: {{ $adjustment->adjustment_date }}
        — {{ \App\CPU\translate('الحالة') }}: {{ $label }}
      </small>

      <div class="totals">
        <div><strong>{{ \App\CPU\translate('إجمالي الفروق') }}:</strong> {{ number_format($sumDiff, 2) }}</div>
        @if($adjustment->notes)
          <div><strong>{{ \App\CPU\translate('الملاحظات') }}:</strong> {{ $adjustment->notes }}</div>
        @endif
      </div>

      <div class="table-responsive" style="margin-top:12px">
        <table style="width:100%; border-collapse:collapse" border="1" cellpadding="7">
          <thead style="background:#f3f6fb">
            <tr>
              <th>#</th>
              <th>{{ \App\CPU\translate('المنتج') }}</th>
              <th>{{ \App\CPU\translate('الكمية النظامية') }}</th>
              <th>{{ \App\CPU\translate('الكمية الجديدة') }}</th>
              <th>{{ \App\CPU\translate('الفرق') }}</th>
              <th>{{ \App\CPU\translate('السبب') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($adjustment->items as $i => $item)
              @php $diff = (float)$item->new_system_quantity - (float)$item->adjustment_amount; @endphp
              <tr>
                <td style="text-align:center">{{ $i+1 }}</td>
                <td>{{ $item->product->name ?? '—' }}</td>
                <td style="text-align:center">{{ number_format($item->adjustment_amount,2) }}</td>
                <td style="text-align:center">{{ number_format($item->new_system_quantity,2) }}</td>
                <td style="text-align:center">{{ number_format($diff,2) }}</td>
                <td>{{ $item->reason ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" style="text-align:end">{{ \App\CPU\translate('إجمالي الفروق') }}</th>
              <th style="text-align:center">{{ number_format($sumDiff, 2) }}</th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- توقيعات (صف واحد ثابت) --}}
      <div class="sig-grid">
        <div class="sig-box">
          <div class="role">{{ \App\CPU\translate('توقيع المنشئ') }}</div>
          <div class="line"></div>
          <div>{{ \App\CPU\translate('الاسم') }}: ____________________</div>
          <div>{{ \App\CPU\translate('التاريخ') }}: ____/____/______</div>
        </div>
        <div class="sig-box">
          <div class="role">{{ \App\CPU\translate('توقيع المراجع') }}</div>
          <div class="line"></div>
          <div>{{ \App\CPU\translate('الاسم') }}: ____________________</div>
          <div>{{ \App\CPU\translate('التاريخ') }}: ____/____/______</div>
        </div>
        <div class="sig-box">
          <div class="role">{{ \App\CPU\translate('توقيع مدير المخزن') }}</div>
          <div class="line"></div>
          <div>{{ \App\CPU\translate('الاسم') }}: ____________________</div>
          <div>{{ \App\CPU\translate('التاريخ') }}: ____/____/______</div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

  <script>
    // طباعة الإيصال: نافذة مخصّصة بطباعة A4 وحدود وجداول واضحة
    function printReceipt(){
      const src = document.getElementById('printArea');
      if(!src){ window.print(); return; }

      const html = `
        <html dir="rtl">
        <head>
          <meta charset="utf-8">
          <title>{{ \App\CPU\translate('إيصال اعتماد تسوية مخزون') }}</title>
          <style>
            @page { size: A4; margin: 12mm; }
            body {
              font-family: "Cairo","Tahoma", Arial, sans-serif;
              color:#111827; -webkit-print-color-adjust: exact; print-color-adjust: exact;
              margin:0;
            }
            .wrap { max-width: 770px; margin: 0 auto; }
            .hdr-grid{
              display: grid; grid-template-columns: 1fr auto 1fr; align-items:center; gap:12px;
              border-bottom:2px solid #e5e7eb; padding-bottom:10px; margin-bottom:14px;
            }
            .hdr-grid .logo{ text-align:center }
            .hdr-grid .logo img{ max-height:70px; max-width:160px; width:auto; height:auto; object-fit:contain }
            .hdr-grid .info p{ margin:2px 0; font-size:13px }
            h1{ font-size:18px; margin:0 0 8px }
            small{ color:#6b7280 }
            table{ width:100%; border-collapse:collapse; margin-top:10px }
            thead th{ background:#f3f6fb }
            th,td{ border:1px solid #cbd5e1; padding:8px; font-size:13px }
            tfoot th, tfoot td{ font-weight:700; background:#fafafa }
            .sig-row{
              display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-top:18px;
              page-break-inside: avoid;
            }
            .sig{
              border:1px dashed #cbd5e1; border-radius:10px; padding:12px; min-height:110px;
              display:flex; flex-direction:column; justify-content:space-between;
            }
            .sig .role{ font-weight:700 }
            .sig .line{ height:1px; background:#cbd5e1; margin:18px 0 8px }
            .meta{ display:flex; gap:10px; flex-wrap:wrap; margin:8px 0 }
            .chip{ background:#f3f4f6; border-radius:999px; padding:4px 10px; font-size:12px }
          </style>
        </head>
        <body>
          <div class="wrap">
            ${src.innerHTML}
          </div>
          <script>window.onload=function(){window.print(); window.close();};<\/script>
        </body></html>
      `;

      const win = window.open('', '', 'width=1000,height=900');
      win.document.open(); win.document.write(html); win.document.close();
    }
  </script>
