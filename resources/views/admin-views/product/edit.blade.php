@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تحديث المنتج'))

@push('css_or_js')
<style>
  :root{
    --ink:#0f172a; --muted:#6b7280; --grid:#e9eef5; --bg:#fff; --brand:#0d6efd;
    --ok:#198754; --bad:#dc3545; --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  .page-wrap{max-width:1100px; margin-inline:auto}
  .card-soft{border:1px solid var(--grid); background:var(--bg); border-radius:var(--rd); box-shadow:var(--shadow)}
  .section-title{font-weight:700; font-size:18px; margin:6px 0 14px; display:flex; align-items:center; gap:10px}
  .section-title .dot{width:10px; height:10px; border-radius:50%; background:var(--brand)}
  .help{font-size:12px; color:var(--muted)}
  .preview-img{width:160px; height:160px; border-radius:12px; object-fit:cover; border:1px solid var(--grid)}
  .summary{position:sticky; top:80px}
  .metric{display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #eaecef}
  .metric:last-child{border-bottom:0}
  .metric .k{color:var(--muted)} .metric .v{font-weight:700}
  .v.good{color:var(--ok)} .v.bad{color:var(--bad)}
  .badge-soft{background:#e7f3ff; color:#0b5ed7; border:1px solid #d5e8ff; font-weight:600}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تحديث منتج') }}</li>
      </ol>
    </nav>
  </div>

  <div class="row g-3">
    {{-- ===== Left: Form ===== --}}
    <div class="col-lg-8">
      <div class="card card-soft">
        <div class="card-body p-4">
          <form action="{{ route('admin.product.update', [$product['id']]) }}" method="post" enctype="multipart/form-data" id="product_form">
            @csrf

            {{-- ====== بيانات أساسية ====== --}}
            <div class="section-title"><span class="dot"></span>{{ \App\CPU\translate('البيانات الأساسية') }}</div>
            <div class="row g-4">
              <div class="col-md-6">
                <label class="input-label">الاسم <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product['name']) }}" required>
              </div>
              <div class="col-md-6">
                <label class="input-label">الاسم بالإنجليزية <span class="text-danger">*</span></label>
                <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $product['name_en']) }}" required>
              </div>

              {{-- نوع المنتج (منتج/خدمة) --}}
              <div class="col-md-6">
                <label class="input-label">نوع المنتج <span class="text-danger">*</span></label>
                @php $pType = old('product_type', $product['product_type'] ?? 'product'); @endphp
                <select name="product_type" id="product_type" class="form-control js-select2-custom" required>
                  <option value="product" {{ $pType === 'product' ? 'selected' : '' }}>منتج</option>
                  <option value="service" {{ $pType === 'service' ? 'selected' : '' }}>خدمة</option>
                </select>
                <div class="help">تأثيره على ظهور الحقول الخاصة بالمخزون/الوحدات/الصلاحية</div>
              </div>

              {{-- SKU والضريبة --}}
              <div class="col-md-6">
                <label class="input-label">رمز المنتج / SKU <span class="text-danger">*</span></label>
                <input type="text" name="product_code" class="form-control" value="{{ old('product_code', $product['product_code']) }}" required>
              </div>

              <div class="col-md-6">
                <label class="input-label">الضريبة</label>
                <select name="tax_id" id="tax_id" class="form-control js-select2-custom">
                  <option value="" data-amount="0" data-kind="percent">بدون ضريبة</option>
                  @foreach ($taxes as $tax)
                    {{-- نفترض taxes.amount = نسبة مئوية --}}
                    <option value="{{ $tax['id'] }}"
                            data-amount="{{ $tax['amount'] ?? 0 }}"
                            data-kind="{{ $tax['type'] ?? 'percent' }}"
                            {{ (old('tax_id', $product['tax_id']) == $tax['id']) ? 'selected' : '' }}>
                      {{ $tax['name'] }} @if(isset($tax['amount'])) ({{ $tax['amount'] }}%) @endif
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- الفئة --}}
              <div class="col-md-6">
                <label class="input-label">الفئة</label>
                <select name="category_id" class="form-control js-select2-custom">
                  <option value="">اختر</option>
                  @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}" {{ (old('category_id', $product['category_id']) == $category['id']) ? 'selected' : '' }}>
                      {{ $category['name'] }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            {{-- ====== وحدات ومخزون (تختفي لو خدمة) ====== --}}
            @php $isService = ($pType === 'service'); @endphp
            <div id="unit-section" class="row g-4 mt-1" style="{{ $isService ? 'display:none;' : '' }}">
              <div class="col-md-6">
                <label class="input-label">نوع الوحدة</label>
                <select name="unit_type" class="form-control js-select2-custom">
                  <option value="">اختر</option>
                  @foreach ($units as $unit)
                    <option value="{{ $unit['id'] }}" {{ (old('unit_type', $product['unit_type']) == $unit['id']) ? 'selected' : '' }}>
                      {{ $unit['unit_type'] }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="input-label">قيمة الوحدة</label>
                <input type="number" name="unit_value" class="form-control" value="{{ old('unit_value', $product['unit_value']) }}">
              </div>
            </div>

            <div id="purchase-section" class="row g-4 mt-1" style="{{ $isService ? 'display:none;' : '' }}">
              <div class="col-md-6">
                <label class="input-label">سعر الشراء</label>
                <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control"
                       value="{{ old('purchase_price', $product['purchase_price']) }}">
              </div>
              <div class="col-md-6" id="supplier-section">
                <label class="input-label">المورد</label>
                <select name="supplier_id" class="form-control js-select2-custom">
                  <option value="">اختر</option>
                  @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier['id'] }}" {{ (old('supplier_id', $product['supplier_id']) == $supplier['id']) ? 'selected' : '' }}>
                      {{ $supplier['name'] }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            {{-- ====== البيع والخصم ====== --}}
            <div class="row g-4 mt-1">
              <div class="col-md-6">
                <label class="input-label">سعر البيع <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control"
                       value="{{ old('selling_price', $product['selling_price']) }}" required>
              </div>
              <div class="col-md-6">
                <label class="input-label">نوع الخصم</label>
                @php $dtype = old('discount_type', $product['discount_type'] ?? 'percentage'); @endphp
                <select name="discount_type" id="discount_type" class="form-control">
                  <option value="percentage" {{ $dtype === 'percentage' ? 'selected' : '' }}>نسبة %</option>
                  <option value="amount"     {{ $dtype === 'amount'     ? 'selected' : '' }}>مبلغ ثابت</option>
                </select>
              </div>
            </div>

            <div class="row g-4 mt-1">
              <div class="col-md-6">
                <label id="label_percent" class="input-label">قيمة الخصم (%)</label>
                <label id="label_amount"  class="input-label d-none">قيمة الخصم (مبلغ)</label>
                <input type="number" step="0.01" name="discount" id="discount_value" class="form-control"
                       value="{{ old('discount', $product['discount']) }}">
              </div>
            </div>

            {{-- ====== تاريخ الانتهاء + الصورة ====== --}}
            <div id="expire-section" class="row g-4 mt-1" style="{{ $isService ? 'display:none;' : '' }}">
              <div class="col-md-6">
                <label class="input-label">تاريخ الانتهاء</label>
                <input type="date" name="expire_at" id="expire_at" class="form-control" value="{{ old('expire_at', $product['expire_at']) }}">
                <div class="help" id="expire_hint"></div>
              </div>
              <div class="col-md-6">
                <label class="input-label">الصورة</label>
                <input type="file" name="image" id="image_input" class="form-control" accept="image/*">
                <div class="mt-2">
                  @php
                    $img = null;
                    if (!empty($product['image'])) {
                      $path = 'product/'.ltrim($product['image'], '/');
                      if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                        $img = \Illuminate\Support\Facades\Storage::url($path);
                      }
                    }
                  @endphp
                  @if($img)
                    <img src="{{ $img }}" class="preview-img" alt="product image">
                  @endif
                  <img id="viewer" class="preview-img d-none" alt="preview">
                </div>
              </div>
            </div>

            <div class="mt-4 text-center">
              <button type="submit" class="btn btn-primary px-5 py-2">تحديث المنتج</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- ===== Right: Summary ===== --}}
    <div class="col-lg-4">
      <div class="card card-soft summary">
        <div class="card-body p-4">
          <div class="section-title"><span class="dot"></span>ملخص التسعير</div>
          <div class="metric"><span class="k">السعر الأساسي</span><span class="v" id="m_base">0.00</span></div>
          <div class="metric"><span class="k">الخصم</span><span class="v" id="m_disc">0.00</span></div>
          <div class="metric"><span class="k">الصافي بعد الخصم</span><span class="v" id="m_net">0.00</span></div>
          <div class="metric"><span class="k">الضريبة</span><span class="v" id="m_tax">0.00</span></div>
          <div class="metric"><span class="k">الإجمالي بعد الضريبة</span><span class="v" id="m_gross">0.00</span></div>
          <div class="mt-3">
            <span class="badge badge-soft" id="m_badge">جاهز</span>
            <div class="help mt-2" id="m_hint">أدخل السعر/الخصم/الضريبة لرؤية الحسابات فورًا</div>
          </div>
        </div>
      </div>
    </div>
  </div> {{-- row --}}
</div>
@endsection

@push('script')
<script>
  // ===== Helpers (بدون $) =====
  const qs=(s,r=document)=>r.querySelector(s);
  const toNum=v=>{v=parseFloat(v);return isNaN(v)?0:v};
  const money=v=>toNum(v).toFixed(2);

  // تبديل الحقول حسب نوع المنتج
  function toggleServiceFields(){
    const isService = (qs('#product_type')?.value === 'service');
    ['unit-section','purchase-section','expire-section','supplier-section'].forEach(id=>{
      const el = document.getElementById(id);
      if(el) el.style.display = isService ? 'none' : '';
    });
  }

  // تبديل تسمية الخصم
  function flipDiscountLabel(){
    const isAmount = (qs('#discount_type')?.value === 'amount');
    qs('#label_amount')?.classList.toggle('d-none', !isAmount);
    qs('#label_percent')?.classList.toggle('d-none', isAmount);
    const ph = isAmount ? 'أدخل مبلغ' : 'أدخل نسبة %';
    const dv = qs('#discount_value'); if(dv) dv.placeholder = ph;
  }

  // Min لتاريخ الانتهاء + حساب الأيام
  function initExpireMin(){
    const el = qs('#expire_at'); if(!el) return;
    const today=new Date(); today.setHours(0,0,0,0);
    const min=`${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
    el.min=min;
    el.addEventListener('change', ()=>{
      const v = el.value? new Date(el.value):null;
      const hint = qs('#expire_hint'); if(!hint) return;
      if(!v){ hint.textContent=''; return; }
      v.setHours(0,0,0,0);
      const diffDays=Math.round((v-today)/86400000);
      hint.textContent = diffDays<0 ? 'التاريخ أقدم من اليوم' : `متبقي ${diffDays} يوم`;
    });
  }

  // معاينة الصورة
  function initImagePreview(){
    const input=qs('#image_input'); if(!input) return;
    input.addEventListener('change', e=>{
      const file=e.target.files?.[0]; if(!file) return;
      const reader=new FileReader();
      reader.onload=ev=>{ const img=qs('#viewer'); img.src=ev.target.result; img.classList.remove('d-none'); };
      reader.readAsDataURL(file);
    });
  }

  // حسابات فورية (الضريبة من taxes.amount%)
  function recalc(){
    const price = toNum(qs('#selling_price')?.value);
    const dtype = qs('#discount_type')?.value || 'percentage';
    let dval    = toNum(qs('#discount_value')?.value);

    // خصم
    let discAmt = 0;
    if (dtype === 'amount') {
      discAmt = Math.min(dval, price);
    } else { // percentage
      if (dval > 100) dval = 100;
      discAmt = price * (dval/100);
    }
    const net = Math.max(0, price - discAmt);

    // الضريبة
    const opt = qs('#tax_id')?.selectedOptions?.[0];
    const taxAmount = toNum(opt?.dataset?.amount || 0);
    const taxKind   = (opt?.dataset?.kind || 'percent').toLowerCase();

    let taxAmt = 0;
    if (taxKind === 'percent') taxAmt = net * (taxAmount/100);
    else taxAmt = taxAmount; // لو نوع ثابت

    const gross = net + taxAmt;

    // إخراج
    setTxt('m_base', money(price));
    setTxt('m_disc', money(discAmt));
    setTxt('m_net',  money(net));
    setTxt('m_tax',  money(taxAmt));
    setTxt('m_gross',money(gross));

    const hint=qs('#m_hint'), badge=qs('#m_badge');
    if (net === 0 && price>0) { hint.textContent='تنبيه: الخصم ألغى السعر بالكامل'; badge.textContent='تحذير'; }
    else { hint.textContent='كل شيء يبدو جيدًا — يمكنك الحفظ'; badge.textContent='جاهز'; }
  }
  function setTxt(id, v){ const el=document.getElementById(id); if(el) el.textContent=v; }

  // Bind
  document.addEventListener('DOMContentLoaded', ()=>{
    toggleServiceFields();
    flipDiscountLabel();
    initExpireMin();
    initImagePreview();
    ['product_type','discount_type','tax_id','selling_price','discount_value'].forEach(id=>{
      const el=document.getElementById(id); if(!el) return;
      el.addEventListener('change',()=>{ if(id==='product_type') toggleServiceFields(); if(id==='discount_type') flipDiscountLabel(); recalc(); });
      el.addEventListener('input', recalc);
    });
    recalc();
  });
</script>
@endpush
