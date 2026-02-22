@extends('layouts.admin.app')

@section('title', 'إضافة ضامن جديد')

@push('css_or_js')
    <style>
        .card-header {
            background: #fff;
            color: #000;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
        }
        .form-control, .form-select {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
        }
        .img-preview {
            max-width: 120px;
            max-height: 120px;
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            margin-top: 0.5rem;
        }
     
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('قائمة الضمناء') }}
        </li>
      </ol>
    </nav>
  </div>

    <div class="card shadow-sm">
        <div class="card-header">بيانات الضامن</div>
        <div class="card-body">
            <form action="{{ route('admin.guarantors.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="row g-3">
                    <!-- Name -->
                    <div class="col-md-6">
                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- National ID -->
                    <div class="col-md-6">
                        <label class="form-label">الرقم القومي <span class="text-danger">*</span></label>
                        <input type="text"
                               name="national_id"
                               class="form-control @error('national_id') is-invalid @enderror"
                               value="{{ old('national_id') }}"
                               required>
                        @error('national_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label class="form-label">الجوال <span class="text-danger">*</span></label>
                        <input type="text"
                               name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}"
                               required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="col-md-6">
                        <label class="form-label">العنوان</label>
                        <input type="text"
                               name="address"
                               class="form-control @error('address') is-invalid @enderror"
                               value="{{ old('address') }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Job -->
                    <div class="col-md-4">
                        <label class="form-label">الوظيفة</label>
                        <input type="text"
                               name="job"
                               class="form-control @error('job') is-invalid @enderror"
                               value="{{ old('job') }}">
                        @error('job')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Monthly Income -->
                    <div class="col-md-4">
                        <label class="form-label">الدخل الشهري</label>
                        <input type="number"
                               name="monthly_income"
                               class="form-control @error('monthly_income') is-invalid @enderror"
                               value="{{ old('monthly_income') }}"
                               min="0" step="0.01">
                        @error('monthly_income')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Relation -->
                    <div class="col-md-4">
                        <label class="form-label">العلاقة</label>
                        <input type="text"
                               name="relation"
                               class="form-control @error('relation') is-invalid @enderror"
                               value="{{ old('relation') }}">
                        @error('relation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Attachments -->
                    <div class="col-12">
                        <label class="form-label">مرفقات الصور</label>
                        <input type="file"
                               name="images[]"
                               class="form-control @error('images.*') is-invalid @enderror"
                               accept="image/*"
                               multiple>
                        @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <!-- Preview placeholders -->
                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2">
                            <!-- JavaScript will insert <img> tags here -->
                        </div>
                    </div>
                </div>
<!-- دايمًا يمين -->
<div class="mt-4 d-flex justify-content-end">
    <button type="submit" class="btn btn-primary px-5">حفظ الضامن</button>
</div>


            </form>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.querySelector('input[name="images[]"]');
    const preview = document.getElementById('imagePreview');

    input?.addEventListener('change', function() {
        preview.innerHTML = '';
        Array.from(this.files).forEach(file => {
            const url = URL.createObjectURL(file);
            const img = document.createElement('img');
            img.src = url;
            img.className = 'img-preview';
            preview.appendChild(img);
        });
    });
});
</script>
