{{-- resources/views/admin-views/product/list-products-by-order-type.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('حركة متابعة صنف'))

@push('css_or_js')
<style>
  :root{
    --brand:#0d6efd; --ink:#0f172a; --muted:#6b7280; --grid:#e9eef5; --bg:#fff;
    --ok:#198754; --warn:#f59e0b; --bad:#dc3545; --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  .card-soft{border:1px solid var(--grid); background:var(--bg); border-radius:var(--rd); box-shadow:var(--shadow)}
  .section-title{font-weight:700; font-size:18px; margin:6px 0 14px; display:flex; align-items:center; gap:10px}
  .section-title .dot{width:10px; height:10px; border-radius:50%; background:var(--brand)}
  .help{font-size:12px; color:var(--muted)}
  .kpis .card{border:1px dashed #e6ecf3}
  .kpi .label{color:var(--muted); font-size:12px}
  .kpi .value{font-weight:800; font-size:20px}
  .table thead th{background:#f8fafc; position:sticky; top:0; z-index:5}
  .badge-soft{background:#eef4ff; color:#0b5ed7; border:1px solid #dce9ff}
  .pill{display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background:#f5f7fb; border:1px solid #e2e8f0; font-size:12px}
  .pill i{font-size:14px}
  .btn-min{min-height:42px}
</style>
@endpush

@section('content')
@php
    $isService = request('product_type') === 'service';
    $orderLabel = [
        ''   => 'مجمع',
        '7'  => 'مرتجع مبيعات',
        '4'  => 'مبيعات',
        '12' => 'مشتريات',
        '24' => 'مردود مشتريات',
    ];
    $currentLabel = $orderLabel[ (string)request('order_type') ] ?? 'مجمع';
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('حركة متابعة صنف') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== فلاتر ====== --}}
  <form method="GET" action="{{ route('admin.product.listProductsByOrderType') }}" class="card card-soft p-4 mb-4">
    <div class="section-title mb-2"><span class="dot"></span>{{ \App\CPU\translate('تصفية النتائج') }}</div>
    <input type="hidden" name="product_id" value="{{ request('product_id') }}">
    <input type="hidden" name="product_type" value="{{ request('product_type') }}">
    <div class="row g-3">
      <div class="col-md-4">
        <label for="order_type" class="form-label fw-bold">{{ \App\CPU\translate('نوع التصنيف') }}</label>
        <select name="order_type" id="order_type" class="form-select">
          <option value=""    {{ request('order_type')===''    ? 'selected' : '' }}>مجمع</option>
          <option value="7"   {{ request('order_type')=='7'    ? 'selected' : '' }}>مرتجع مبيعات</option>
          <option value="4"   {{ request('order_type')=='4'    ? 'selected' : '' }}>مبيعات</option>
          <option value="12"  {{ request('order_type')=='12'   ? 'selected' : '' }}>مشتريات</option>
          <option value="24"  {{ request('order_type')=='24'   ? 'selected' : '' }}>مردود مشتريات</option>
        </select>
        <div class="help">{{ \App\CPU\translate('اختر نوع الفواتير لعرض التفاصيل أو اتركه مجمعًا') }}</div>
      </div>
      <div class="col-md-4">
        <label for="date_from" class="form-label fw-bold">{{ \App\CPU\translate('من تاريخ') }}</label>
        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
      </div>
      <div class="col-md-4">
        <label for="date_to" class="form-label fw-bold">{{ \App\CPU\translate('إلى تاريخ') }}</label>
        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
      </div>

      <div class="col-12">
        <div class="row g-2">
          <div class="col-6 col-md-3">
            <button type="submit" class="btn btn-primary w-100 btn-min">
              <i class="tio-search"></i> {{ \App\CPU\translate('بحث') }}
            </button>
          </div>
          <div class="col-6 col-md-3">
            <button type="button" class="btn btn-outline-secondary w-100 btn-min" onclick="resetFilters()">
              <i class="tio-clear"></i> {{ \App\CPU\translate('إلغاء') }}
            </button>
          </div>
          <div class="col-6 col-md-3">
            <button type="button" class="btn btn-outline-dark w-100 btn-min" onclick="printDetails()">
              <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
            </button>
          </div>
          <div class="col-6 col-md-3">
            <button type="button" class="btn btn-outline-success w-100 btn-min" onclick="exportCSV('details-table','product-movements')">
              <i class="tio-file-text"></i> {{ \App\CPU\translate('تصدير CSV') }}
            </button>
          </div>
        </div>
      </div>

      <div class="col-12">
        <span class="pill">
          <i class="tio-filter-outlined"></i>
          {{ \App\CPU\translate('عامل التصفية الحالي') }}:
          <strong>{{ $currentLabel }}</strong>
          @if(request('date_from') || request('date_to'))
            <span class="text-muted">—</span>
            <small>
              {{ request('date_from') ? (\App\CPU\translate('من').' '.request('date_from')) : '' }}
              {{ (request('date_from') && request('date_to')) ? ' / ' : '' }}
              {{ request('date_to') ? (\App\CPU\translate('إلى').' '.request('date_to')) : '' }}
            </small>
          @endif
        </span>
      </div>
    </div>
  </form>

  {{-- ====== عند اختيار نوع محدد: جدول تفاصيل + KPI ====== --}}
  @if (request()->has('order_type') && request('order_type') !== 'all' && request('order_type') !== null)
    @php
      // تلخيص سريع للصفحة الحالية
      $pageOrders = $products->count();
      $pageQty    = $isService ? 0 : $products->sum('quantity');
      $pageTotal  = $isService ? 0 : $products->sum(fn($p) => ($p->quantity * $p->price));
    @endphp

    <div class="row g-3 kpis mb-3">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body kpi">
            <div class="label">{{ \App\CPU\translate('إجمالي الفواتير (هذه الصفحة)') }}</div>
            <div class="value">{{ number_format($pageOrders) }}</div>
            <div class="help">{{ \App\CPU\translate('قد يختلف الإجمالي الكلي عبر الصفحات') }}</div>
          </div>
        </div>
      </div>
      @unless($isService)
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body kpi">
            <div class="label">{{ \App\CPU\translate('إجمالي الكميات (هذه الصفحة)') }}</div>
            <div class="value">{{ number_format($pageQty, 2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body kpi">
            <div class="label">{{ \App\CPU\translate('إجمالي السعر (هذه الصفحة)') }}</div>
            <div class="value">{{ number_format($pageTotal, 2) }}</div>
          </div>
        </div>
      </div>
      @endunless
    </div>

    <div class="card card-soft">
      <div class="card-body">
        <div class="section-title mb-3"><span class="dot"></span>{{ \App\CPU\translate('تفاصيل الفواتير') }}</div>
        <div class="table-responsive">
          <table class="table table-bordered align-middle" id="details-table">
            <thead class="table-light">
              <tr>
                <th>{{ \App\CPU\translate('رقم الفاتورة') }}</th>
                <th>{{ \App\CPU\translate('تاريخ الفاتورة') }}</th>
                <th>{{ \App\CPU\translate('اسم العميل') }}</th>
                <th>{{ \App\CPU\translate('اسم الكاتب') }}</th>
                <th>{{ \App\CPU\translate('اسم الصنف') }}</th>
                <th>{{ \App\CPU\translate('كود الصنف') }}</th>
                @unless($isService)
                  <th>{{ \App\CPU\translate('الكمية') }}</th>
                  <th>{{ \App\CPU\translate('السعر') }}</th>
                  <th>{{ \App\CPU\translate('الإجمالي') }}</th>
                  <th>{{ \App\CPU\translate('تاريخ الصلاحية') }}</th>
                @endunless
              </tr>
            </thead>
            <tbody>
              @foreach ($products as $product)
                @php
                  $rowTotal = $isService ? 0 : ($product->quantity * $product->price);
                  $customerName = $product->order->customer->name ?? $product->order->supplier->name ?? '—';
                  $sellerName   = $product->order->seller->email ?? $product->order->seller->name ?? '—';
                  $expDate      = $product->product->expiry_date ?? $product->product->expire_at ?? null;
                @endphp
                <tr>
                  <td>{{ $product->order_id }}</td>
                  <td>{{ \Carbon\Carbon::parse($product->created_at)->format('d-m-Y H:i') }}</td>
                  <td>{{ $customerName }}</td>
                  <td>{{ $sellerName }}</td>
                  <td>{{ $product->product->name }}</td>
                  <td>{{ $product->product->product_code }}</td>
                  @unless($isService)
                    <td>
                      {{ number_format($product->quantity, 2) }}
                      @if ($product->unit == 1)
                        {{ $product->product->unit->unit_type ?? '' }}
                      @else
                        {{ $product->product->unit->subUnits->first()?->name ?? '' }}
                      @endif
                    </td>
                    <td>{{ number_format($product->price, 2) }}</td>
                    <td>{{ number_format($rowTotal, 2) }}</td>
                    <td>{{ $expDate ? \Carbon\Carbon::parse($expDate)->format('d-m-Y') : 'N/A' }}</td>
                  @endunless
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
          {{ $products->appends(request()->query())->links() }}
        </div>
      </div>
    </div>

    @unless($isService)
      {{-- تلخيص إضافي (اختياري) --}}
      <div class="card card-soft mt-4">
        <div class="card-body">
          <div class="section-title mb-2"><span class="dot"></span>{{ \App\CPU\translate('تلخيص (هذه الصفحة)') }}</div>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>{{ \App\CPU\translate('إجمالي الفواتير') }}</th>
                  <th>{{ \App\CPU\translate('إجمالي الكميات') }}</th>
                  <th>{{ \App\CPU\translate('إجمالي السعر') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ number_format($pageOrders) }}</td>
                  <td>{{ number_format($pageQty, 2) }}</td>
                  <td>{{ number_format($pageTotal, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="help">{{ \App\CPU\translate('الإجماليات أعلاه تخص الصفحة الحالية فقط.') }}</div>
        </div>
      </div>
    @endunless
  @endif

  {{-- ====== عند (مجمع) أو بدون اختيار: تجميعات عامة ====== --}}
  @if (!request()->has('order_type') || request('order_type') === null || request('order_type') === 'all' || request('order_type') === '')
    <div class="card card-soft mt-4">
      <div class="card-body">
        <div class="section-title mb-3"><span class="dot"></span>{{ \App\CPU\translate('تجميعات حسب نوع التصنيف') }}</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>{{ \App\CPU\translate('نوع الفاتورة') }}</th>
                <th>{{ \App\CPU\translate('إجمالي الفواتير') }}</th>
                @unless($isService)
                  <th>{{ \App\CPU\translate('إجمالي الكميات') }}</th>
                  <th>{{ \App\CPU\translate('إجمالي السعر') }}</th>
                @endunless
              </tr>
            </thead>
            <tbody>
              @php
                $types = [
                  'sales'            => 'مبيعات',
                  'salesReturns'     => 'مرتجع مبيعات',
                  'purchases'        => 'مشتريات',
                  'purchaseReturns'  => 'مردود مشتريات'
                ];
              @endphp
              @foreach ($types as $key => $label)
                @php $typeData = $$key; @endphp
                <tr>
                  <td>{{ $label }}</td>
                  <td>{{ number_format($typeData->count()) }}</td>
                  @unless($isService)
                    <td>
                      {{ number_format($typeData->sum('quantity'), 2) }}
                      @php
                        $first = $typeData->first();
                        $unitName = $first?->product?->unit?->unit_type ?? $first?->product?->unit?->subUnits?->first()?->name ?? '';
                      @endphp
                      {{ $unitName }}
                    </td>
                    <td>{{ number_format($typeData->sum(fn($item) => $item->quantity * $item->price), 2) }}</td>
                  @endunless
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        @unless($isService)
        <div class="section-title mt-4 mb-2"><span class="dot"></span>{{ \App\CPU\translate('تفاصيل أخرى') }}</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>{{ \App\CPU\translate('آخر سعر بيع') }}</th>
                <th>{{ \App\CPU\translate('آخر سعر شراء') }}</th>
                <th>{{ \App\CPU\translate('أقل كمية بيع') }}</th>
                <th>{{ \App\CPU\translate('أقل كمية شراء') }}</th>
                <th>{{ \App\CPU\translate('رصيد المخزن') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ isset($last_sale_price) ? number_format($last_sale_price, 2) : 'N/A' }}</td>
                <td>{{ isset($last_purchase_price) ? number_format($last_purchase_price, 2) : 'N/A' }}</td>
                <td>{{ isset($min_sale_quantity) ? number_format($min_sale_quantity, 2) : 'N/A' }}</td>
                <td>{{ isset($min_purchase_quantity) ? number_format($min_purchase_quantity, 2) : 'N/A' }}</td>
                <td>{{ isset($total_stock_quantity) ? number_format($total_stock_quantity, 2) : 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        @endunless
      </div>
    </div>
  @endif
</div>
@endsection

<script>
  // ====== Reset Filters (يحافظ على product_id و product_type) ======
  function resetFilters(){
    const url = new URL(@json(route('admin.product.listProductsByOrderType')), window.location.origin);
    url.searchParams.set('product_id', @json(request('product_id')));
    if (@json(request('product_type'))){
      url.searchParams.set('product_type', @json(request('product_type')));
    }
    window.location.href = url.toString();
  }

  // ====== Export CSV من جدول التفاصيل ======
  function exportCSV(tableId, filenameBase){
    const table = document.getElementById(tableId);
    if(!table){ alert('لا يوجد جدول للتصدير'); return; }

    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach(row=>{
      const cols = row.querySelectorAll('th,td');
      const data = [];
      cols.forEach(col=>{
        let text = col.innerText.replace(/\s+/g,' ').trim();
        text = '"' + text.replace(/"/g,'""') + '"';
        data.push(text);
      });
      csv.push(data.join(','));
    });

    const csvContent = '\uFEFF' + csv.join('\n'); // BOM ليدعم العربية في Excel
    const blob = new Blob([csvContent], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    const t = new Date();
    const pad=n=>String(n).padStart(2,'0');
    a.download = `${filenameBase}_${t.getFullYear()}${pad(t.getMonth()+1)}${pad(t.getDate())}_${pad(t.getHours())}${pad(t.getMinutes())}.csv`;
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(a.href);
  }

  // ====== Print (تفاصيل الفواتير) ======
  function printDetails(){
    const details = document.getElementById('details-table');
    if(!details){ window.print(); return; } // fallback

    const wrap = document.createElement('div');
    wrap.innerHTML = details.parentElement.innerHTML;

    const w = window.open('', '_blank', 'width=900,height=700');
    w.document.write(`
      <!DOCTYPE html>
      <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>{{ \App\CPU\translate('تفاصيل حركة الصنف') }}</title>
        <style>
          body{font-family:'Cairo', Arial, sans-serif; margin:24px; color:#333;}
          h1{ text-align:center; color:#003366; font-size:22px; margin:10px 0 20px; }
          table{width:100%; border-collapse:collapse; margin-top:10px;}
          th,td{border:1px solid #ddd; padding:8px; text-align:right;}
          th{background:#f2f2f2;}
        </style>
      </head>
      <body>
        <h1>{{ \App\CPU\translate('تفاصيل حركة الصنف') }} — {{ $currentLabel }}</h1>
        ${wrap.innerHTML}
        <script>window.onload=function(){window.print(); window.close();};<\/script>
      </body>
      </html>
    `);
    w.document.close();
  }
</script>
