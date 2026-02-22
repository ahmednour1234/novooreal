@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_product'))

@push('css_or_js')
<style>
  :root{
    --ink:#0f172a; --muted:#6b7280; --grid:#e9eef5; --head:#f6f8ff; --bg:#ffffff;
    --brand:#0d6efd; --ok:#198754; --bad:#dc3545; --shadow:0 12px 28px -18px rgba(2,32,71,.18);
    --rd:14px;
  }
  .page-wrap{max-width:1200px; margin-inline:auto}
  .card-soft{border:1px solid var(--grid); background:var(--bg); border-radius:var(--rd); box-shadow:var(--shadow)}
  .section-title{font-weight:700; font-size:18px; margin:6px 0 14px; display:flex; align-items:center; gap:10px}
  .section-title .dot{width:10px; height:10px; border-radius:50%; background:var(--brand)}
  .help{font-size:12px; color:var(--muted)}
  .preview-img{width:180px; height:180px; border-radius:12px; object-fit:cover; border:1px solid var(--grid)}
  .summary{position:sticky; top:80px}
  .metric{display:flex; align-items:center; justify-content:space-between; padding:9px 0; border-bottom:1px dashed #eaecef}
  .metric:last-child{border-bottom:0}
  .metric .k{color:var(--muted)}
  .metric .v{font-weight:700}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('إضافة منتج جديد') }}</li>
      </ol>
    </nav>
  </div>

  <div class="row g-3">
    {{-- ====== Left: Form ====== --}}
    <div class="col-lg-8">
      <div class="card card-soft">
        <div class="card-body">
          <form action="{{ route('admin.product.store') }}" method="post" id="product_form" enctype="multipart/form-data">
            @csrf

            {{-- ====== Basic Info ====== --}}
            <div class="section-title"><span class="dot"></span>{{ \App\CPU\translate('بيانات أساسية') }}</div>
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('الاسم بالعربي') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ \App\CPU\translate('product_name_in_arabic') }}" required>
              </div>
              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('الاسم بالانجليزي') }} <span class="text-danger">*</span></label>
                <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="{{ \App\CPU\translate('product_name_in_english') }}" required>
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-6">
                <label class="input-label">
                  {{ \App\CPU\translate('انشاء كود اتوماتيك') }} <span class="text-danger">*</span>
                  <a href="#" class="ms-2" onclick="event.preventDefault(); generateCode();">{{ \App\CPU\translate('انشاء كود') }}</a>
                </label>
                <input type="text" minlength="5" id="product_code" name="product_code" class="form-control"
                       value="{{ old('product_code') }}" placeholder="{{ \App\CPU\translate('product_code') }}" required>
                <div class="help">{{ \App\CPU\translate('يمكنك تعديله يدويًا بعد التوليد') }}</div>
              </div>

              <div class="col-md-6">
                <label class="input-label">{{ \App\CPU\translate('اختار الضريبة') }}</label>
                <select name="tax_id" id="tax_id" class="form-control js-select2-custom">
                  <option value="" data-amount="0" data-kind="percent">— {{ \App\CPU\translate('بدون ضريبة') }} —</option>
                  @foreach ($taxes as $tax)
                    {{-- نعتمد amount = نسبة مئوية --}}
                    <option value="{{ $tax['id'] }}"
                            data-amount="{{ $tax['amount'] ?? 0 }}"
                            data-kind="{{ $tax['type'] ?? 'percent' }}"
                            {{ $tax['id'] == old('tax_id') ? 'selected' : '' }}>
                      {{ $tax['name'] }} ({{ $tax['amount'] ?? 0 }}%)
                    </option>
                  @endforeach
                </select>
                <div class="help">{{ \App\CPU\translate('سيتم تطبيق النسبة على السعر بعد الخصم') }}</div>
              </div>
            </div>

            {{-- ====== Category / Type ====== --}}
            <div class="row g-3 mt-1">
              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('قسم') }} <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-control js-select2-custom"
                        onchange="getRequest('{{ url('/') }}/admin/product/get-categories?parent_id='+this.value, 'sub-categories')" required>
                  <option value="">— {{ \App\CPU\translate('اختار') }} —</option>
                  @foreach($categories as $category)
                    <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }}>
                      {{ $category['name'] }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('التصنيف') }} <span class="text-danger">*</span></label>
                <select name="type" id="type" class="form-control js-select2-custom">
                  <option value="imported">{{ \App\CPU\translate('مستورد') }}</option>
                  <option value="local">{{ \App\CPU\translate('محلي') }}</option>
                  <option value="assembled">{{ \App\CPU\translate('مجمع') }}</option>
                  <option value="company">{{ \App\CPU\translate('شركة') }}</option>
                </select>
              </div>
            </div>

            {{-- حاوية التصنيفات الفرعية التي يملؤها getRequest --}}
            <div id="sub-categories" class="mt-2"></div>

            {{-- ====== Qty / Unit / Prices ====== --}}
            <div class="row g-3 mt-1">
              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('الكمية') }} <span class="text-danger">*</span></label>
                <input type="number" name="quantity" id="quantity" class="form-control"
                       value="{{ old('quantity') }}" placeholder="{{ \App\CPU\translate('quantity') }}" required>
              </div>

              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('الوحدة') }} <span class="text-danger">*</span></label>
                <select name="unit_type" id="unit_type" class="form-control js-select2-custom">
                  <option value="">— {{ \App\CPU\translate('اختار') }} —</option>
                  @foreach($units as $unit)
                    <option value="{{ $unit['id'] }}" {{ old('unit_type') == $unit['id'] ? 'selected' : '' }}>
                      {{ $unit['unit_type'] }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('الوحدة كمية') }} <span class="text-danger">*</span></label>
                <input type="number" min="1" step="1" name="unit_value" id="unit_value" class="form-control"
                       value="{{ old('unit_value', 1) }}" placeholder="{{ \App\CPU\translate('unit_value') }}">
                <div class="help">{{ \App\CPU\translate('مثال: عبوة 12 قطعة → اكتب 12') }}</div>
              </div>

              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('سعر البيع') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control"
                       value="{{ old('selling_price') }}" placeholder="{{ \App\CPU\translate('selling_price') }}" required>
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('سعر الشراء') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control"
                       value="{{ old('purchase_price') }}" placeholder="{{ \App\CPU\translate('purchase_price') }}" required>
              </div>

              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('نوع الخصم') }}</label>
                <select name="discount_type" id="discount_type" class="form-control js-select2-custom">
                  <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>{{ \App\CPU\translate('نسبة') }}</option>
                  <option value="amount"  {{ old('discount_type') == 'amount'  ? 'selected' : '' }}>{{ \App\CPU\translate('رقم') }}</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-sm-6">
                <label id="label_disc_percent" class="input-label">{{ \App\CPU\translate('نسبة الخصم') }} (%)</label>
                <label id="label_disc_amount"  class="input-label d-none">{{ \App\CPU\translate('discount_amount') }}</label>
                <input type="number" min="0" step="0.01" name="discount" id="discount" class="form-control"
                       value="{{ old('discount') }}" placeholder="{{ \App\CPU\translate('discount') }}">
              </div>

              <div class="col-sm-6">
                <label class="input-label">{{ \App\CPU\translate('تاريخ انتهاء الصلاحية') }} <span class="text-danger">*</span></label>
                <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                <div class="help" id="expiry_hint"></div>
              </div>
            </div>

            {{-- ====== Image ====== --}}
            <div class="section-title mt-3"><span class="dot"></span>{{ \App\CPU\translate('الصور') }}</div>
            <div class="row g-3 align-items-center">
              <div class="col-md-8">
                <label class="mb-2">{{ \App\CPU\translate('صورة') }}</label>
                <div class="custom-file">
                  <input type="file" name="image" id="image_input" class="custom-file-input"
                         accept=".jpg,.png,.jpeg,.gif,.bmp,.tif,.tiff,image/*">
                  <label class="custom-file-label" for="image_input">{{ \App\CPU\translate('choose') }} {{ \App\CPU\translate('file') }}</label>
                </div>
              </div>
              <div class="col-md-4 d-flex justify-content-md-end">
                <img id="viewer" class="preview-img" src="{{ asset('public/assets/admin/img/400x400/img2.jpg') }}" alt="preview"/>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary w-100">{{ \App\CPU\translate('حفظ') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- ====== Right: Live Summary ====== --}}
    <div class="col-lg-4">
      <div class="card card-soft summary">
        <div class="card-body">
          <div class="section-title"><span class="dot"></span>{{ \App\CPU\translate('ملخص الحسابات') }}</div>

          <div class="metric"><span class="k">{{ \App\CPU\translate('السعر الأساسي') }}</span><span class="v" id="m_base">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الخصم') }}</span><span class="v" id="m_disc">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الصافي بعد الخصم') }}</span><span class="v" id="m_net">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الضريبة المضافة') }}</span><span class="v" id="m_tax">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('السعر بعد الضريبة') }}</span><span class="v" id="m_gross">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('سعر الوحدة (صافي/بعد الضريبة)') }}</span><span class="v" id="m_unit">—</span></div>

          <hr class="my-3">

          <div class="metric"><span class="k">{{ \App\CPU\translate('التكلفة للوحدة') }}</span><span class="v" id="m_cost">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الربح للوحدة') }}</span><span class="v" id="m_profit_unit">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الهامش %') }}</span><span class="v" id="m_margin">0%</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('الزيادة على التكلفة %') }}</span><span class="v" id="m_markup">0%</span></div>

          <hr class="my-3">

          <div class="metric"><span class="k">{{ \App\CPU\translate('إجمالي الإيراد') }}</span><span class="v" id="m_revenue">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('إجمالي التكلفة') }}</span><span class="v" id="m_total_cost">0.00</span></div>
          <div class="metric"><span class="k">{{ \App\CPU\translate('إجمالي الربح') }}</span><span class="v" id="m_profit_total">0.00</span></div>

          <div class="mt-3">
            <span class="badge badge-soft" id="m_badge">{{ \App\CPU\translate('جاهز للحفظ') }}</span>
            <div class="help mt-2" id="m_hint">{{ \App\CPU\translate('أدخل القيم لمشاهدة الحسابات مباشرة') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div> {{-- row --}}
</div>
@endsection

{{-- ====== Scripts ====== --}}
<script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
<script>
  // ========= لا نستخدم $ حتى لا نكسر jQuery =========
  const qs  = (sel, root=document) => root.querySelector(sel);
  const qsa = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const toNum = v => { v = parseFloat(v); return isNaN(v) ? 0 : v; };
  const money = v => toNum(v).toFixed(2);

  // ========= Override آمن لـ getRequest (يستخدم jQuery لو متاح، وإلا fetch) =========
  window.getRequest = function(url, id){
    const target = document.getElementById(id);
    if(!target) return;
    if (window.jQuery && typeof window.jQuery.get === 'function') {
      window.jQuery.get(url, function(data){ target.innerHTML = data; });
    } else if (window.$ && typeof window.$.get === 'function') {
      window.$.get(url, function(data){ target.innerHTML = data; });
    } else {
      fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text())
        .then(html => { target.innerHTML = html; })
        .catch(()=>{ target.innerHTML = '<div class="text-danger small">Failed to load</div>'; });
    }
  };

  // ========= كل الأكواد بعد تحميل الـ DOM =========
  document.addEventListener('DOMContentLoaded', function(){

    // توليد كود
    function generateCode(){
      if (typeof window.getRndInteger === 'function') {
        qs('#product_code').value = window.getRndInteger();
      } else {
        qs('#product_code').value = 'P' + Date.now().toString(36).toUpperCase();
      }
    }
    window.generateCode = generateCode; // علشان زرار "إنشاء كود" يستدعيها

    // قلب عنوان الخصم
    function flipDiscLabels(){
      const isPercent = qs('#discount_type')?.value === 'percent';
      qs('#label_disc_percent')?.classList.toggle('d-none', !isPercent);
      qs('#label_disc_amount')?.classList.toggle('d-none', isPercent);
    }
    qs('#discount_type')?.addEventListener('change', flipDiscLabels);

    // تاريخ صلاحية (min + عداد)
    (function initExpiry(){
      const el = qs('#expiry_date'); if(!el) return;
      const today = new Date(); today.setHours(0,0,0,0);
      const min = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
      el.min = min;
      el.addEventListener('change', () => {
        const v = el.value ? new Date(el.value) : null;
        const hint = qs('#expiry_hint'); if(!hint) return;
        if(!v){ hint.textContent = ''; return; }
        v.setHours(0,0,0,0);
        const diffDays = Math.round((v - today) / 86400000);
        hint.textContent = diffDays < 0 ? '{{ \App\CPU\translate('التاريخ أقدم من اليوم') }}'
                                        : `{{ \App\CPU\translate('متبقي') }} ${diffDays} {{ \App\CPU\translate('يوم') }}`;
      });
    })();

    // معاينة صورة
    qs('#image_input')?.addEventListener('change', e=>{
      const file = e.target.files?.[0]; if(!file) return;
      document.querySelector('label[for="image_input"]').textContent = file.name;
      const reader = new FileReader();
      reader.onload = ev => { qs('#viewer').src = ev.target.result; };
      reader.readAsDataURL(file);
    });

    // الحسابات
    function recalc(){
      const price = toNum(qs('#selling_price')?.value);
      const cost  = toNum(qs('#purchase_price')?.value);
      const qty   = Math.max(0, toNum(qs('#quantity')?.value));
      const unitV = Math.max(1, toNum(qs('#unit_value')?.value) || 1);

      const discType = qs('#discount_type')?.value || 'percent';
      let discVal    = toNum(qs('#discount')?.value);

      let discAmt = 0;
      if (discType === 'amount') {
        discAmt = Math.min(discVal, price);
      } else {
        if (discVal > 100) discVal = 100;
        discAmt = price * (discVal/100);
      }
      const net = Math.max(0, price - discAmt);

      const opt = qs('#tax_id')?.selectedOptions?.[0];
      const taxAmount = toNum(opt?.dataset?.amount || 0); // amount = %
      const taxKind   = (opt?.dataset?.kind || 'percent').toLowerCase();

      let taxAmt = 0;
      if (taxKind === 'percent') taxAmt = net * (taxAmount/100);
      else taxAmt = taxAmount; // ثابت (على المنتج ككل). إن أردت لكل وحدة: taxAmount * qty

      const gross = net + taxAmt;

      const unitPriceNet   = net   / unitV;
      const unitPriceGross = gross / unitV;
      const unitCost       = cost  / unitV;

      const profitUnit = unitPriceNet - unitCost; // قبل الضريبة
      const margin  = net > 0 ? ((net - cost) / net) * 100 : 0;
      const markup  = cost > 0 ? ((net - cost) / cost) * 100 : 0;

      const revenue    = gross * qty;    // بعد الضريبة
      const totalCost  = cost  * qty;
      const profitTotal = (net * qty) - totalCost; // الربح دون الضريبة (شائع محاسبيًا)

      setText('m_base',   money(price));
      setText('m_disc',   money(discAmt));
      setText('m_net',    money(net));
      setText('m_tax',    money(taxAmt));
      setText('m_gross',  money(gross));
      setText('m_unit',   `${money(unitPriceNet)} / ${money(unitPriceGross)}`);
      setText('m_cost',   money(unitCost));
      setText('m_profit_unit', money(profitUnit));
      setText('m_margin', margin.toFixed(2)+'%');
      setText('m_markup', markup.toFixed(2)+'%');
      setText('m_revenue', money(revenue));
      setText('m_total_cost', money(totalCost));
      setText('m_profit_total', money(profitTotal));

      colorVal('m_profit_unit', profitUnit >= 0);
      colorVal('m_profit_total', profitTotal >= 0);
      colorVal('m_margin', margin >= 0);

      const hint  = qs('#m_hint');
      const badge = qs('#m_badge');
      if (net < cost) {
        hint.textContent  = '{{ \App\CPU\translate('تنبيه: صافي البيع أقل من تكلفة الشراء') }}';
        badge.textContent = '{{ \App\CPU\translate('تحذير تسعير') }}';
      } else if (discType === 'percent' && discVal > 50) {
        hint.textContent  = '{{ \App\CPU\translate('خصم مرتفع جدًا، راجع الهامش') }}';
        badge.textContent = '{{ \App\CPU\translate('خصم عالٍ') }}';
      } else {
        hint.textContent  = '{{ \App\CPU\translate('كل شيء يبدو جيدًا — يمكنك الحفظ') }}';
        badge.textContent = '{{ \App\CPU\translate('جاهز للحفظ') }}';
      }
    }

    function setText(id, v){ const el = document.getElementById(id); if(el) el.textContent = v; }
    function colorVal(id, ok){
      const el = document.getElementById(id);
      if(!el) return;
      el.classList.remove('good','bad'); el.classList.add(ok ? 'good' : 'bad');
    }

    // ربط الأحداث بأمان
    ['selling_price','purchase_price','discount','quantity','unit_value','discount_type','tax_id']
      .forEach(id => {
        const el = document.getElementById(id);
        if(!el) return;
        el.addEventListener('input', recalc);
        el.addEventListener('change', recalc);
      });

    // تشغيل أولي
    flipDiscLabels();
    recalc();
  });

  // موجود أصلًا عندك
  function roundToNearestStep(input, step) {
    var value = parseFloat(input.value);
    if (isNaN(value)) return;
    var roundedValue = Math.round(value / step) * step;
    input.value = roundedValue.toFixed(2);
  }
</script>
