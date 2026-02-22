@extends('layouts.admin.app')

@section('title', \App\CPU\translate('قائمة المنتجات'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  :root{
    --brand:#0d6efd; --ink:#0f172a; --muted:#6b7280; --grid:#e9eef5; --bg:#fff;
    --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  .nav-tabs .nav-link.active{background-color:#003f88; color:#fff}
  .card-soft{border:1px solid var(--grid); background:var(--bg); border-radius:var(--rd); box-shadow:var(--shadow)}
  .page-actions .btn{min-height:42px}
  .table thead th{background:#f8fafc; position:sticky; top:0; z-index:1}
  .badge-soft{background:#eef4ff; color:#0b5ed7; border:1px solid #dce9ff}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('قائمة المنتجات') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== تبويبات النوع ====== --}}
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <a class="nav-link {{ request()->product_type !== 'service' ? 'active' : '' }}"
         href="{{ route('admin.product.list', ['product_type' => 'product'] + request()->except('page')) }}">
        <i class="tio-shopping-basket-outlined"></i> {{ \App\CPU\translate('منتجات') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ request()->product_type === 'service' ? 'active' : '' }}"
         href="{{ route('admin.product.list', ['product_type' => 'service'] + request()->except('page')) }}">
        <i class="tio-briefcase-outlined"></i> {{ \App\CPU\translate('خدمات') }}
      </a>
    </li>
  </ul>

  {{-- ====== بطاقة الفلاتر + الأزرار ====== --}}
  @php $pt = request('product_type') === 'service' ? 'service' : 'product'; @endphp
  <div class="card card-soft mb-4">
    <div class="card-body">
      <form action="{{ url()->current() }}" method="GET" id="filtersForm">
        <div class="row g-3 align-items-end">
          {{-- البحث بالحقل --}}
          <div class="col-lg-6">
            <label class="form-label fw-bold">{{ \App\CPU\translate('بحث باسم أو كود المنتج') }}</label>
            <input type="search" name="search" id="searchInput" class="form-control"
                   placeholder="{{ \App\CPU\translate('أدخل كلمة البحث') }}"
                   value="{{ request('search') }}">
          </div>



          {{-- صف الأزرار الأربعة --}}
          <div class="col-12">
            <div class="row g-2 page-actions">
              <div class="col-6 col-md-3">
                <button type="submit" class="btn btn-secondary w-100">
           {{ \App\CPU\translate('بحث') }}
                </button>
              </div>
              <div class="col-6 col-md-3">
                <button type="button" class="btn btn-danger w-100" onclick="clearFilters()">
                {{ \App\CPU\translate('إلغاء') }}
                </button>
              </div>
              <div class="col-6 col-md-3">
                <button type="button" class="btn btn-info w-100" onclick="exportCSV()">
               {{ \App\CPU\translate('تصدير CSV/Excel') }}
                </button>
              </div>
              <div class="col-6 col-md-3">
                <button type="button" class="btn btn-primary w-100" onclick="printTable()">
    {{ \App\CPU\translate('طباعة') }}
                </button>
              </div>
            </div>
          </div>
        </div> 
      </form>
    </div>
  </div>



  {{-- ====== الجدول ====== --}}
  <div class="card card-soft">
    <div class="card-body table-responsive" id="product-table">
      <table class="table table-bordered table-hover align-middle" id="products-table">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الاسم') }}</th>
            <th>{{ \App\CPU\translate('الكود') }}</th>
            @if(request()->product_type !== 'service')
              <th>{{ \App\CPU\translate('تاريخ الانتهاء') }}</th>
              <th>{{ \App\CPU\translate('عدد مرات البيع') }}</th>
              <th>{{ \App\CPU\translate('عدد مرات الشراء') }}</th>
              <th>{{ \App\CPU\translate('مردود البيع') }}</th>
              <th>{{ \App\CPU\translate('مردود الشراء') }}</th>
              <th>{{ \App\CPU\translate('هالك') }}</th>
            @endif
            <th>{{ \App\CPU\translate('سعر البيع') }}</th>
            <th>{{ \App\CPU\translate('سعر الشراء') }}</th>
            <th>{{ \App\CPU\translate('الاجراءات') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $index => $product)
            <tr>
              <td>{{ $index + $products->firstItem() }}</td>
              <td>{{ $product->name }}</td>
              <td>{{ $product->product_code }}</td>

              @if(request()->product_type !== 'service')
                <td>
                  @php
                    $exp = $product->expiry_date ?? $product->expire_at ?? null;
                  @endphp
                  {{ $exp ? \Carbon\Carbon::parse($exp)->format('d-m-Y') : '—' }}
                </td>
                <td>{{ $product->order_count }}</td>
                <td>{{ $product->purchase_count }}</td>
                <td>{{ $product->refund_count }}</td>
                <td>{{ $product->repurchase_count }}</td>
                <td>
                  @php
                    $wasteQty = $product->productexpire->sum('quantity');
                    $isDecimal = is_float($wasteQty) || (strpos((string)$wasteQty, '.') !== false);
                    $result = ($product->unit_value > 0) ? ($wasteQty * $product->unit_value) : $wasteQty;
                    $unitName = $product->unit->subUnits->first()->name ?? $product->unit->unit_type ?? '';
                  @endphp
                  {{ $isDecimal ? number_format($result, 2) : $result }} {{ $unitName }}
                </td>
              @endif

              <td>{{ number_format($product->selling_price, 2) }}</td>
              <td>{{ number_format($product->purchase_price, 2) }}</td>

              <td class="text-nowrap">
                <a class="btn btn-sm btn-primary" href="{{ route('admin.product.edit', $product->id) }}" title="{{ \App\CPU\translate('تعديل') }}">
                  <i class="tio-edit"></i>
                </a>
                <a class="btn btn-sm btn-secondary" href="{{ route('admin.product.barcode-generate', $product->id) }}" target="_blank" title="{{ \App\CPU\translate('باركود') }}">
                  <i class="tio-barcode"></i>
                </a>
                <a class="btn btn-sm btn-info" href="{{ route('admin.product.listProductsByOrderType', ['product_id' => $product->id, 'product_type' => $product->product_type]) }}" title="{{ \App\CPU\translate('عرض') }}">
                  <i class="tio-visible"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="d-flex justify-content-center mt-4">
        {{ $products->appends(request()->query())->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

<script>
  // ======= مساعدة: إعادة توجيه لإلغاء الفلاتر =======
  function clearFilters(){
    const baseUrl = @json(route('admin.product.list', ['product_type' => $pt]));
    window.location.href = baseUrl;
  }

  // ======= تصدير CSV (يفتح مباشرة في Excel) =======
  function exportCSV(){
    const table = document.getElementById('products-table');
    if(!table) return;

    // حدد عمود "الإجراءات" لتجاهله
    const headerCells = table.querySelectorAll('thead th');
    let skipIndex = -1;
    headerCells.forEach((th, i) => {
      if (th.textContent.trim().includes('الاجراءات') || th.textContent.trim().includes('الإجراءات')) skipIndex = i;
    });

    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach((row, rIdx) => {
      const cols = row.querySelectorAll('th, td');
      const rowData = [];
      cols.forEach((col, cIdx) => {
        if (cIdx === skipIndex) return; // تجاهل عمود الإجراءات
        let text = col.innerText.replace(/\s+/g, ' ').trim();
        // عالج الفواصل المزدوجة واقفل على دابل كوتس
        text = '"' + text.replace(/"/g, '""') + '"';
        rowData.push(text);
      });
      if (rowData.length) csv.push(rowData.join(','));
    });

    // أضف BOM ليتعرف Excel على UTF-8
    const csvContent = '\uFEFF' + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

    const a = document.createElement('a');
    const url = URL.createObjectURL(blob);
    a.href = url;
    const ts = new Date();
    const pad = n => String(n).padStart(2,'0');
    const fileName = `products_${ts.getFullYear()}${pad(ts.getMonth()+1)}${pad(ts.getDate())}_${pad(ts.getHours())}${pad(ts.getMinutes())}.csv`;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  // ======= طباعة محسنة =======
  function printTable() {
    const tableContent = document.getElementById('product-table').innerHTML;

    // Settings prefetch (تم التقليل من استدعاءات DB بوضعها هنا مرة واحدة)
    const vatRegNo   = @json(optional(\App\Models\BusinessSetting::where('key','vat_reg_no')->first())->value);
    const numberTax  = @json(optional(\App\Models\BusinessSetting::where('key','number_tax')->first())->value);
    const shopEmail  = @json(optional(\App\Models\BusinessSetting::where('key','shop_email')->first())->value);
    const shopName   = @json(optional(\App\Models\BusinessSetting::where('key','shop_name')->first())->value);
    const shopAddr   = @json(optional(\App\Models\BusinessSetting::where('key','shop_address')->first())->value);
    const shopPhone  = @json(optional(\App\Models\BusinessSetting::where('key','shop_phone')->first())->value);
    const shopLogoRel= @json(optional(\App\Models\BusinessSetting::where('key','shop_logo')->first())->value);
    const shopLogo   = shopLogoRel ? @json(\Illuminate\Support\Facades\Storage::url('shop/'.(optional(\App\Models\BusinessSetting::where('key','shop_logo')->first())->value))) : '';

    const printWindow = window.open('', '_blank', 'width=900,height=700');
    printWindow.document.write(`
      <!DOCTYPE html>
      <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>{{ \App\CPU\translate('تقرير المنتجات') }}</title>
        <style>
          body{font-family:'Cairo', Arial, sans-serif; margin:24px; color:#333;}
          h1{ text-align:center; color:#003366; font-size:24px; margin:10px 0 20px; }
          .header{display:flex; align-items:center; justify-content:space-between; gap:12px; border-bottom:2px solid #003366; padding-bottom:10px; margin-bottom:16px;}
          .col{width:33%;}
          .col p{margin:4px 0; font-size:14px;}
          .logo{ text-align:center;}
          .logo img{ max-width:140px; height:auto; }
          table{width:100%; border-collapse:collapse; margin-top:10px;}
          th,td{border:1px solid #ddd; padding:8px; text-align:right;}
          th{background:#f2f2f2;}
          .meta{font-size:12px; color:#666; margin-top:8px}
        </style>
      </head>
      <body>
        <div class="header">
          <div class="col">
            ${vatRegNo ? `<p><strong>رقم السجل التجاري:</strong> ${vatRegNo}</p>` : ''}
            ${numberTax ? `<p><strong>الرقم الضريبي:</strong> ${numberTax}</p>` : ''}
            ${shopEmail ? `<p><strong>البريد الإلكتروني:</strong> ${shopEmail}</p>` : ''}
          </div>
          <div class="logo">
            ${shopLogo ? `<img src="${shopLogo}" alt="Shop Logo">` : ''}
          </div>
          <div class="col">
            ${shopName ? `<p><strong>اسم المؤسسة:</strong> ${shopName}</p>` : ''}
            ${shopAddr ? `<p><strong>العنوان:</strong> ${shopAddr}</p>` : ''}
            ${shopPhone ? `<p><strong>رقم الجوال:</strong> ${shopPhone}</p>` : ''}
          </div>
        </div>

        <h1>{{ \App\CPU\translate('تقرير المنتجات') }}</h1>
        ${tableContent}
        <div class="meta">
          {{ \App\CPU\translate('نوع القائمة') }}: {{ $pt === 'service' ? \App\CPU\translate('خدمات') : \App\CPU\translate('منتجات') }}
        </div>

        <script>
          window.onload = function(){ window.print(); window.close(); };
        <\/script>
      </body>
      </html>
    `);
    printWindow.document.close();
  }
</script>
