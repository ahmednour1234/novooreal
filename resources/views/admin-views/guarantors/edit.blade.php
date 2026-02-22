@extends('layouts.admin.app')

@section('title', 'تعديل بيانات الضامن')

@push('css_or_js')
<style>
  /* ===== شكل عام هادئ بدون تدرّجات ===== */
  .page-card{ border:1px solid #e9edf5; border-radius:14px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,.06); }
  .page-card .card-header{ background:#f8fafc; border-bottom:1px solid #e9edf5; }
  .page-title{ margin:0; font-weight:700; }
  .page-sub{ color:#6b7280; font-size:.92rem; }

  .section-title{ font-weight:700; font-size:1rem; margin:0 0 .25rem; }
  .section-sub{ color:#8b95a1; font-size:.85rem; margin-bottom:1rem; }

  .form-label{ font-weight:600; }
  .form-control, .form-select{ border-radius:.5rem; border:1px solid #ced4da; }

  .input-with-icon{ position:relative; }
  .input-with-icon .lead-icon{
    position:absolute; inset-inline-start:.75rem; inset-block-start:50%; transform:translateY(-50%);
    color:#7b8794; pointer-events:none;
  }
  .input-with-icon .form-control{ padding-inline-start:2.1rem; }

  .thumb{ width:100px; height:100px; object-fit:cover; border:1px solid #e9edf5; border-radius:.5rem; }
  .thumb-grid{ display:flex; flex-wrap:wrap; gap:.5rem; }
  .badge-soft{ background:#f3f6fa; border:1px solid #e7ecf2; color:#3a3f45; border-radius:999px; padding:.3rem .55rem; font-size:.75rem; }

  /* أزرار */
  .btn-primary{ background:#001B63; border-color:#001B63; }
  .btn-primary:hover{ background:#0b5ed7; border-color:#0a58ca; }
  .actions-bar{ display:flex; gap:.5rem; justify-content:flex-end; } /* نهاية السطر (في RTL يسار) */
</style>
@endpush

@section('content')
<div class="content container-fluid">

  <!-- Breadcrumb -->
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('تعديل بيانات الضامن') }}
        </li>
      </ol>
    </nav>
  </div>

  <!-- Card -->
  <div class="card page-card">


    <div class="card-body">
      <form action="{{ route('admin.guarantors.update', $guarantor->id) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        {{-- ===== المعلومات الأساسية ===== --}}
        <div class="mb-2">
          <div class="section-title">{{ \App\CPU\translate('المعلومات الأساسية') }}</div>
          <div class="section-sub">{{ \App\CPU\translate('اسم الضامن وبيانات الهوية ووسائل الاتصال') }}</div>
        </div>

        <div class="row g-3">
          <!-- Name -->
          <div class="col-md-6">
            <label class="form-label">{{ \App\CPU\translate('الاسم') }} <span class="text-danger">*</span></label>
            <div class="input-with-icon">
              <i class="tio-user lead-icon"></i>
              <input type="text" name="name"
                     class="form-control @error('name') is-invalid @enderror"
                     value="{{ old('name', $guarantor->name) }}" required>
            </div>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- National ID -->
          <div class="col-md-6">
            <label class="form-label">{{ \App\CPU\translate('الرقم القومي') }} <span class="text-danger">*</span></label>
            <div class="input-with-icon">
              <i class="tio-id-badge lead-icon"></i>
              <input type="text" name="national_id"
                     class="form-control @error('national_id') is-invalid @enderror"
                     value="{{ old('national_id', $guarantor->national_id) }}" required>
            </div>
            @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Phone -->
          <div class="col-md-6">
            <label class="form-label">{{ \App\CPU\translate('الجوال') }} <span class="text-danger">*</span></label>
            <div class="input-with-icon">
              <i class="tio-android-phone-vs lead-icon"></i>
              <input type="text" name="phone"
                     class="form-control @error('phone') is-invalid @enderror"
                     value="{{ old('phone', $guarantor->phone) }}" required>
            </div>
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Address -->
          <div class="col-md-6">
            <label class="form-label">{{ \App\CPU\translate('العنوان') }}</label>
            <div class="input-with-icon">
              <i class="tio-home-vs lead-icon"></i>
              <input type="text" name="address"
                     class="form-control @error('address') is-invalid @enderror"
                     value="{{ old('address', $guarantor->address) }}">
            </div>
            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <hr class="my-4">

        {{-- ===== العمل والدخل ===== --}}
        <div class="mb-2">
          <div class="section-title">{{ \App\CPU\translate('العمل والدخل') }}</div>
          <div class="section-sub">{{ \App\CPU\translate('وظيفة الضامن وقيمة الدخل الشهري والعلاقة بالعميل') }}</div>
        </div>

        <div class="row g-3">
          <!-- Job -->
          <div class="col-md-4">
            <label class="form-label">{{ \App\CPU\translate('الوظيفة') }}</label>
            <div class="input-with-icon">
              <i class="tio-briefcase lead-icon"></i>
              <input type="text" name="job"
                     class="form-control @error('job') is-invalid @enderror"
                     value="{{ old('job', $guarantor->job) }}">
            </div>
            @error('job') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Monthly Income -->
          <div class="col-md-4">
            <label class="form-label">{{ \App\CPU\translate('الدخل الشهري') }}</label>
            <div class="input-with-icon">
              <i class="tio-dollar-outlined lead-icon"></i>
              <input type="number" name="monthly_income"
                     class="form-control @error('monthly_income') is-invalid @enderror"
                     value="{{ old('monthly_income', $guarantor->monthly_income) }}"
                     min="0" step="0.01">
            </div>
            @error('monthly_income') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Relation -->
          <div class="col-md-4">
            <label class="form-label">{{ \App\CPU\translate('العلاقة') }}</label>
            <div class="input-with-icon">
              <i class="tio-user-switch lead-icon"></i>
              <input type="text" name="relation"
                     class="form-control @error('relation') is-invalid @enderror"
                     value="{{ old('relation', $guarantor->relation) }}">
            </div>
            @error('relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <hr class="my-4">

        {{-- ===== المرفقات ===== --}}
        <div class="mb-2">
          <div class="section-title">{{ \App\CPU\translate('المرفقات') }}</div>
          <div class="section-sub">{{ \App\CPU\translate('قم بإضافة صور جديدة — يمكن اختيار أكثر من ملف') }}</div>
        </div>

        <div class="row g-3">
          <!-- Attach New Images -->
          <div class="col-12">
            <label class="form-label">{{ \App\CPU\translate('إضافة مرفقات جديدة') }}</label>
            <input type="file" name="images[]"
                   class="form-control @error('images.*') is-invalid @enderror"
                   accept="image/*" multiple>
            @error('images.*') <div class="invalid-feedback">{{ $message }}</div> @enderror

            <!-- Preview New Uploads -->
            <div id="newImagePreview" class="thumb-grid mt-2"></div>
          </div>

          <!-- Existing Images -->
          @if($guarantor->images)
            <div class="col-12">
              <label class="form-label">{{ \App\CPU\translate('الصور الحالية') }}</label>
              <div class="thumb-grid mt-2">
                @foreach(json_decode($guarantor->images, true) as $img)
                  <img src="{{ asset($img) }}" alt="attachment" class="thumb">
                @endforeach
              </div>
            </div>
          @endif
        </div>

        <!-- Actions -->
        <div class="mt-4 actions-bar">
          <a href="{{ url()->previous() }}" class="btn btn-danger px-5">
            {{ \App\CPU\translate('عودة') }}
          </a>
          <button type="submit" class="btn btn-primary px-5">
            {{ \App\CPU\translate('تحديث') }}
          </button>
        </div>

      </form>
    </div>
  </div>
  <!-- /Card -->

</div>
@endsection

<script>
  // معاينة الصور الجديدة فور الاختيار
  document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[name="images[]"]');
    const previewContainer = document.getElementById('newImagePreview');

    fileInput?.addEventListener('change', function() {
      previewContainer.innerHTML = '';
      Array.from(this.files).forEach(file => {
        const url = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = url;
        img.className = 'thumb';
        previewContainer.appendChild(img);
      });
    });
  });
</script>
