{{-- resources/views/admin/customers/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_customer'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
<style>
  :root{
    --ink:#0f172a; --muted:#667085; --brand:#001B63;
    --card:#ffffff; --bg:#f5f7fb; --line:#e6e8ef; --rd:12px;
    --shadow:0 8px 24px -14px rgba(2,32,71,.15)
  }
  body{background:var(--bg)}
  .page-wrap{direction:rtl}
  .breadcrumb{border:1px solid var(--line)}
  .section-card{
    background:var(--card); border-radius:var(--rd);
    padding:1.25rem 1.25rem; box-shadow:var(--shadow); margin-bottom:1.25rem
  }
  .section-title{
    font-size:1.05rem; font-weight:700; color:var(--brand); margin-bottom:.75rem
  }
  .hint{font-size:.85rem; color:var(--muted)}
  .img-preview{width:140px; height:140px; object-fit:cover; border-radius:10px; border:1px solid var(--line)}
  #map{width:100%; height:320px; border:1px solid var(--line); border-radius:10px}
  .btn-ghost{background:#fff; border:1px solid var(--line)}
  .req::after{content:" *"; color:#e11d48; font-weight:700}
  .form-label{font-weight:600}
  .invalid-feedback{display:block}
</style>
@endpush

@section('content')
<div class="content container-fluid page-wrap">

  {{-- ===== Breadcrumb ===== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('اضافة عميل جديد') }}
        </li>
      </ol>
    </nav>
  </div>

  {{-- ===== Alerts (Validation) ===== --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>حدثت أخطاء:</strong>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.customer.store') }}" method="post" enctype="multipart/form-data" class="mb-4">
    @csrf

    {{-- == القسم 1: المعلومات الأساسية == --}}
    <div class="section-card">
      <div class="section-title">المعلومات الأساسية</div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label req">{{ \App\CPU\translate('اسم العميل بالعربي') }}</label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name') }}" placeholder="مثال: مستشفى الشفاء" required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label req">{{ \App\CPU\translate('رقم الجوال') }}</label>
          <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                 value="{{ old('mobile') }}" placeholder="05XXXXXXXX" required>
          @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
          <div class="hint mt-1">أدخل رقمًا فعالًا للتواصل والفواتير.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">{{ \App\CPU\translate('الصورة') }}</label>
          <input type="file" name="image" accept="image/*" class="form-control"
                 onchange="previewCustomerImage(this)">
          <div class="d-flex align-items-center gap-3 mt-2">
            <img id="customerPreview"
                 src="{{ asset('public/assets/admin/img/400x400/img2.jpg') }}"
                 alt="preview" class="img-preview">
            <button type="button" class="btn btn-sm btn-ghost" onclick="clearCustomerImage()">
              إزالة المعاينة
            </button>
          </div>
          <div class="hint mt-1">يفضّل صورة مربعة لا تقل عن 400×400 بكسل.</div>
        </div>
      </div>
    </div>

    {{-- == القسم 2: معلومات إضافية == --}}
    <div class="section-card">
      <div class="section-title">معلومات إضافية</div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">{{ \App\CPU\translate('الايميل') }}</label>
          <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email') }}" placeholder="name@example.com">
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">{{ \App\CPU\translate('الرقم الضريبي') }}</label>
          <input type="text" name="tax_number" class="form-control"
                 value="{{ old('tax_number') }}" placeholder="رقم التسجيل الضريبي">
        </div>

        <div class="col-md-6">
          <label class="form-label">{{ \App\CPU\translate('السجل التجاري') }}</label>
          <input type="text" name="c_history" class="form-control"
                 value="{{ old('c_history') }}" placeholder="رقم السجل التجاري">
        </div>

        <div class="col-md-6">
          <label class="form-label">{{ \App\CPU\translate('المدينة') }}</label>
          <input type="text" name="city" class="form-control"
                 value="{{ old('city') }}" placeholder="مثل: الرياض / جدة / القاهرة">
        </div>

        <div class="col-md-6">
          <label class="form-label">{{ \App\CPU\translate('كود المحافظة') }}</label>
          <input type="text" name="zip_code" class="form-control"
                 value="{{ old('zip_code') }}" placeholder="رمز أو كود المنطقة">
        </div>

        <div class="col-12">
          <label class="form-label req">{{ \App\CPU\translate('العنوان') }}</label>
          <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                 value="{{ old('address') }}" placeholder="الحي/الشارع/المعلم المميز" required>
          @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- خريطة الموقع --}}
        <div class="col-12">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">الموقع على الخريطة</div>
            <div class="d-flex gap-2">
              <button type="button" id="btnGeo" class="btn btn-sm btn-ghost">
                استخدام موقعي الحالي
              </button>
              <span class="hint">اضغط على الخريطة لتحديد الموقع أو اسحب المؤشر.</span>
            </div>
          </div>
          <div id="map"></div>
          <input type="hidden" id="latitude" name="lat" value="{{ old('lat') }}">
          <input type="hidden" id="longitude" name="lng" value="{{ old('lng') }}">
          <div class="d-flex gap-3 mt-2">
            <div class="hint">Lat: <span id="latText">{{ old('lat') ?: '—' }}</span></div>
            <div class="hint">Lng: <span id="lngText">{{ old('lng') ?: '—' }}</span></div>
          </div>
        </div>
      </div>
    </div>

    {{-- == القسم 3: الضامن (اختياري) == --}}
    <div class="section-card">
      <div class="section-title">الضامن (اختياري)</div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">اسم الضامن</label>
          <input type="text" name="guarantor_name" class="form-control"
                 value="{{ old('guarantor_name') }}" placeholder="الاسم الكامل">
        </div>
        <div class="col-md-6">
          <label class="form-label">رقم الهوية</label>
          <input type="text" name="guarantor_national_id" class="form-control"
                 value="{{ old('guarantor_national_id') }}" placeholder="رقم الهوية/الإقامة">
        </div>
        <div class="col-md-6">
          <label class="form-label">رقم الجوال</label>
          <input type="text" name="guarantor_phone" class="form-control"
                 value="{{ old('guarantor_phone') }}" placeholder="05XXXXXXXX">
        </div>
        <div class="col-md-6">
          <label class="form-label">العلاقة</label>
          <input type="text" name="guarantor_relation" class="form-control"
                 value="{{ old('guarantor_relation') }}" placeholder="قريب / زميل / شريك">
        </div>
        <div class="col-12">
          <label class="form-label">مرفقات الضامن</label>
          <input type="file" name="guarantor_images[]" class="form-control" accept="image/*" multiple>
          <div class="hint mt-1">يمكن رفع صور الهوية، البطاقة العائلية، أو أي مستند داعم.</div>
        </div>
      </div>
    </div>

{{-- أزرار الإجراء --}}
<div class="d-flex justify-content-end">
  <a href="{{ url()->previous() }}" class="btn btn-danger col-3" style="margin-left:28px;">
    {{ \App\CPU\translate('الغاء') }}
  </a>
  <button type="submit" class="btn btn-primary col-3">
    {{ \App\CPU\translate('اضافة') }}
  </button>
</div>

  </form>
</div>
@endsection

<script>
  // ========== صورة العميل ==========
  function previewCustomerImage(input){
    if (!input.files || !input.files[0]) return;
    const url = URL.createObjectURL(input.files[0]);
    document.getElementById('customerPreview').src = url;
  }
  function clearCustomerImage(){
    const file = document.querySelector('input[name="image"]');
    if (file) file.value = '';
    document.getElementById('customerPreview').src = "{{ asset('public/assets/admin/img/400x400/img2.jpg') }}";
  }

  // ========== الخريطة ==========
  let map, marker;

  function setMarker(pos){
    if (!marker) {
      marker = new google.maps.Marker({ position: pos, map, draggable:true });
      marker.addListener('dragend', e => updateLatLng(e.latLng));
    } else {
      marker.setPosition(pos);
    }
    map.panTo(pos);
    updateLatLng(pos);
  }

  function updateLatLng(latLng){
    const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
    const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('latText').textContent = lat.toFixed(6);
    document.getElementById('lngText').textContent = lng.toFixed(6);
  }

  function initMap(){
    const latOld = parseFloat(document.getElementById('latitude').value);
    const lngOld = parseFloat(document.getElementById('longitude').value);
    const hasOld = !Number.isNaN(latOld) && !Number.isNaN(lngOld);
    const defaultLoc = hasOld ? {lat:latOld, lng:lngOld} : {lat:30.0444, lng:31.2357}; // Cairo

    map = new google.maps.Map(document.getElementById('map'), {
      center: defaultLoc, zoom: 12, streetViewControl:false, mapTypeControl:false
    });

    // ضع المؤشر الابتدائي
    setMarker(defaultLoc);

    // ضع/انقل المؤشر بالضغط
    map.addListener('click', e => setMarker({lat:e.latLng.lat(), lng:e.latLng.lng()}));

    // زر "موقعي الحالي"
    document.getElementById('btnGeo').addEventListener('click', () => {
      if (!navigator.geolocation) return;
      navigator.geolocation.getCurrentPosition(
        pos => {
          const p = { lat: pos.coords.latitude, lng: pos.coords.longitude };
          setMarker(p);
          map.setZoom(14);
        },
        () => { /* تجاهل الخطأ بصمت */ },
        { enableHighAccuracy:true, timeout:8000, maximumAge:0 }
      );
    });
  }

  // حمّل الخريطة بأمان إذا تأخر DOM
  window.initMap = initMap;
</script>
{{-- ملاحظة: استخدم نفس المفتاح الذي تعتمدونه في مشروعكم --}}
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAQgTQ30_TriFBdJPKKOK4zZQ8rfHCUk6c&callback=initMap"></script>
