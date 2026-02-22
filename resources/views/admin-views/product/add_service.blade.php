@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_service'))

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
  .preview-img{width:180px; height:180px; border-radius:12px; object-fit:cover; border:1px solid var(--grid)}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('إضافة خدمة جديد') }}</li>
      </ol>
    </nav>
  </div>

  <div class="row g-3">
    {{-- ===== Left: Form ===== --}}
    <div class="col-lg-8">
      <div class="card card-soft">
        <div class="card-body p-4">
          <form action="{{ route('admin.product.storeservice') }}" method="POST" enctype="multipart/form-data" id="service_form">
            @csrf

            <div class="section-title"><span class="dot"></span>{{ \App\CPU\translate('بيانات أساسية') }}</div>
            <div class="row g-4">
              <!-- الاسم بالعربي -->
              <div class="col-md-6">
                <label class="form-label fw-bold">{{ \App\CPU\translate('الاسم بالعربي') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="{{ \App\CPU\translate('اسم الخدمة بالعربي') }}" value="{{ old('name') }}" required>
              </div>

              <!-- الاسم بالإنجليزي -->
              <div class="col-md-6">
                <label class="form-label fw-bold">{{ \App\CPU\translate('الاسم بالانجليزي') }} <span class="text-danger">*</span></label>
                <input type="text" name="name_en" class="form-control" placeholder="{{ \App\CPU\translate('اسم الخدمة بالانجليزي') }}" value="{{ old('name_en') }}" required>
              </div>

              <!-- كود الخدمة -->
              <div class="col-md-6">
                <label class="form-label fw-bold d-flex justify-content-between">
                  {{ \App\CPU\translate('كود الخدمة') }}
                  <a href="javascript:void(0)" onclick="document.getElementById('code_input').value = getRndInteger()" class="btn btn-sm btn-outline-primary">
                    {{ \App\CPU\translate('توليد تلقائي') }}
                  </a>
                </label>
                <input type="text" id="code_input" name="product_code" class="form-control" value="{{ old('product_code') }}" placeholder="SRV12345" required>
                <div class="help">{{ \App\CPU\translate('يمكنك تعديله يدويًا بعد التوليد') }}</div>
              </div>

              <!-- الضريبة -->
              <div class="col-md-6">
                <label class="form-label fw-bold">{{ \App\CPU\translate('الضريبة') }}</label>
                <select name="tax_id" id="tax_id" class="form-select shadow-sm border rounded js-select2-custom">
                  <option value="" data-amount="0" data-kind="percent">{{ \App\CPU\translate('بدون ضريبة') }}</option>
                  @foreach($taxes as $tax)
                    <option value="{{ $tax['id'] }}"
                            data-amount="{{ $tax['amount'] ?? 0 }}"
                            data-kind="{{ $tax['type'] ?? 'percent' }}"
                            {{ old('tax_id') == $tax['id'] ? 'selected' : '' }}>
                      {{ $tax['name'] }} @if(isset($tax['amount'])) ({{ $tax['amount'] }}%) @endif
                    </option>
                  @endforeach
                </select>
                <div class="help">{{ \App\CPU\translate('تحسب كنسبة مئوية من السعر بعد الخصم') }}</div>
              </div>

              <!-- القسم -->
              <div class="col-md-6">
                <label class="form-label fw-bold">{{ \App\CPU\translate('القسم') }} <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-select js-select2-custom"
                        onchange="getRequest('{{ url('/') }}/admin/product/get-categories?parent_id=' + this.value, 'sub-categories')" required>
                  <option value="">--- {{ \App\CPU\translate('اختار') }} ---</option>
                  @foreach($categories as $category)
                    <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }}>{{ $category['name'] }}</option>
                  @endforeach
                </select>
              </div>

              <!-- السعر -->
              <div class="col-md-6">
                <label class="form-label fw-bold">{{ \App\CPU\translate('سعر الخدمة') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control" placeholder="100.00" value="{{ old('selling_price') }}" required>
              </div>

              <!-- نوع الخصم -->
              <div class="col-md-6">
                <label class="form-label fw-bold">{{ \App\CPU\translate('نوع الخصم') }}</label>
                <select name="discount_type" id="discount_type" class="form-select js-select2-custom">
                  <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>{{ \App\CPU\translate('نسبة') }}</option>
                  <option value="amount"  {{ old('discount_type') == 'amount'  ? 'selected' : '' }}>{{ \App\CPU\translate('مبلغ') }}</option>
                </select>
              </div>

              <!-- قيمة الخصم -->
              <div class="col-md-6">
                <label id="label_percent" class="form-label fw-bold">{{ \App\CPU\translate('قيمة الخصم (%)') }}</label>
                <label id="label_amount"  class="form-label fw-bold d-none">{{ \App\CPU\translate('قيمة الخصم (ريال)') }}</label>
                <input type="number" min="0" step="0.01" name="discount" id="discount" class="form-control" placeholder="0" value="{{ old('discount') }}">
              </div>

              <!-- صورة الخدمة -->
              <div class="col-12">
                <label class="form-label fw-bold">{{ \App\CPU\translate('صورة الخدمة') }}</label>
                <input type="file" name="image" id="image_input" class="form-control" accept="image/*">
                <div class="text-center mt-3">
                  <img id="viewer" src="{{ asset('public/assets/admin/img/400x400/img2.jpg') }}" class="img-thumbnail shadow preview-img" alt="Service Image Preview">
                </div>
              </div>
            </div>

            {{-- حاوية التصنيفات الفرعية --}}
            <div id="sub-categories" class="mt-4"></div>

            <div class="mt-4 text-center">
              <button type="submit" class="btn btn-primary px-5 py-2">{{ \App\CPU\translate('حفظ الخدمة') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- ===== Right: Live Summary ===== --}}
    <div class="col-lg-4">
      <div class="card card-soft summary">
        <div class="card-body p-4">
          <div class="section-title"><span class="dot"></span>{{ \App\CPU\translate('ملخص الحسابات') }}</div>

          <div class="metric"><span class="k">{{ \App\CPU\translate('السعر الأساسي') }}</span><span class="v" id="m_base">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الخصم') }}</span><span class="v" id="m_disc">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الصافي بعد الخصم') }}</span><span class="v" id="m_net">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الضريبة المضافة') }}</span><span class="v" id="m_tax">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الإجمالي بعد الضريبة') }}</span><span class="v" id="m_gross">0.00</span></div>

          <div class="mt-3">
            <span class="badge badge-soft" id="m_badge">{{ \App\CPU\translate('جاهز للحفظ') }}</span>
            <div class="help mt-2" id="m_hint">{{ \App\CPU\translate('أدخل السعر والخصم والضريبة لمشاهدة الحسابات مباشرة') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div> {{-- row --}}
</div>
@endsection

<script>
  // ========= Helpers (بدون استخدام $) =========
  const qs = (s, r=document)=>r.querySelector(s);
  const toNum = v => { v = parseFloat(v); return isNaN(v) ? 0 : v; };
  const money = v => toNum(v).toFixed(2);

  // ========= getRequest آمن (jQuery إن وجد، وإلا fetch) =========
  window.getRequest = function(url, id){
    const target = document.getElementById(id);
    if(!target) return;
    if (window.jQuery && typeof window.jQuery.get === 'function') {
      window.jQuery.get(url, function(data){ target.innerHTML = data; });
    } else if (window.$ && typeof window.$.get === 'function') {
      window.$.get(url, function(data){ target.innerHTML = data; });
    } else {
      fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html => target.innerHTML = html)
        .catch(()=> target.innerHTML = '<div class="text-danger small">Failed to load</div>');
    }
  };

  // ========= مولّد الكود =========
  function getRndInteger() {
    return 'SRV' + Math.floor(10000 + Math.random() * 90000);
  }
  window.getRndInteger = getRndInteger;

  // ========= تبديل تسميات الخصم =========
  function flipDiscountLabel(){
    const isAmt = qs('#discount_type')?.value === 'amount';
    qs('#label_amount')?.classList.toggle('d-none', !isAmt);
    qs('#label_percent')?.classList.toggle('d-none', isAmt);
  }

  // ========= حسابات فورية =========
  function recalc(){
    const price = toNum(qs('#selling_price')?.value);
    const discType = qs('#discount_type')?.value || 'percent';
    let discVal = toNum(qs('#discount')?.value);

    // خصم
    let discAmt = 0;
    if (discType === 'amount') {
      discAmt = Math.min(discVal, price);
    } else {
      if (discVal > 100) discVal = 100;
      discAmt = price * (discVal/100);
    }
    const net = Math.max(0, price - discAmt);

    // ضريبة من الـ option: data-amount (%)
    const opt = qs('#tax_id')?.selectedOptions?.[0];
    const taxAmount = toNum(opt?.dataset?.amount || 0);
    const taxKind   = (opt?.dataset?.kind || 'percent').toLowerCase();
    let taxAmt = 0;
    if (taxKind === 'percent') taxAmt = net * (taxAmount/100);
    else taxAmt = taxAmount; // ثابت (لو أردتها ثابت لكل خدمة)

    const gross = net + taxAmt;

    // المخرجات
    setText('m_base',  money(price));
    setText('m_disc',  money(discAmt));
    setText('m_net',   money(net));
    setText('m_tax',   money(taxAmt));
    setText('m_gross', money(gross));

    const hint  = qs('#m_hint');
    const badge = qs('#m_badge');
    if (net === 0 && price > 0) {
      hint.textContent  = '{{ \App\CPU\translate('تنبيه: الخصم ألغى السعر بالكامل') }}';
      badge.textContent = '{{ \App\CPU\translate('خصم مبالغ فيه') }}';
    } else {
      hint.textContent  = '{{ \App\CPU\translate('كل شيء يبدو جيدًا — يمكنك الحفظ') }}';
      badge.textContent = '{{ \App\CPU\translate('جاهز للحفظ') }}';
    }
  }
  function setText(id, v){ const el = document.getElementById(id); if(el) el.textContent = v; }

  // ========= معاينة صورة =========
  function initImagePreview(){
    const input = qs('#image_input'); if(!input) return;
    input.addEventListener('change', e=>{
      const file = e.target.files?.[0]; if(!file) return;
      const reader = new FileReader();
      reader.onload = ev => { qs('#viewer').src = ev.target.result; };
      reader.readAsDataURL(file);
    });
  }

  // ========= Bind on DOM ready =========
  document.addEventListener('DOMContentLoaded', function(){
    initImagePreview();
    ['selling_price','discount','discount_type','tax_id'].forEach(id=>{
      const el = document.getElementById(id); if(!el) return;
      el.addEventListener('input', recalc);
      el.addEventListener('change', ()=>{ if(id==='discount_type') flipDiscountLabel(); recalc(); });
    });
    flipDiscountLabel();
    recalc();
  });
</script>
