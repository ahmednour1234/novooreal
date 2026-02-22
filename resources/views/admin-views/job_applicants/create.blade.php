@extends('layouts.admin.app')

@section('title', \App\CPU\translate('إضافة متقدم وظيفي'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
<style>
  :root{
    --grid:#e5e7eb; --shadow:0 10px 30px -18px rgba(2,32,71,.15); --rd:14px;
  }
  .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
  .form-label{font-weight:700}
  .help{font-size:.85rem; color:#6b7280}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('إضافة متقدم وظيفي') }}</li>
      </ol>
    </nav>
  </div>

  <div class="card card-soft">
    <div class="card-body">
      <form action="{{ route('admin.job_applicants.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        {{-- شبكة الحقول: صفوف + أعمدة (جنب بعض) --}}
        <div class="row g-3">

          {{-- الاسم الكامل --}}
          <div class="col-12 col-md-6">
            <label class="form-label">{{ \App\CPU\translate('الاسم الكامل') }} <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control"
                   placeholder="{{ \App\CPU\translate('الاسم الكامل') }}"
                   value="{{ old('full_name') }}" required>
            @error('full_name') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- البريد الإلكتروني --}}
          <div class="col-12 col-md-6">
            <label class="form-label">{{ \App\CPU\translate('البريد الالكتروني') }} <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control"
                   placeholder="{{ \App\CPU\translate('ex: ex@example.com') }}"
                   value="{{ old('email') }}" required>
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- رقم الهاتف --}}
          <div class="col-12 col-md-6">
            <label class="form-label">{{ \App\CPU\translate('رقم الهاتف') }}</label>
            <input type="text" name="phone" class="form-control"
                   placeholder="{{ \App\CPU\translate('رقم الهاتف') }}"
                   value="{{ old('phone') }}">
            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- تاريخ التقديم --}}
          <div class="col-12 col-md-6">
            <label class="form-label">{{ \App\CPU\translate('تاريخ التقديم') }} <span class="text-danger">*</span></label>
            <input type="date" name="applied_date" class="form-control"
                   value="{{ old('applied_date') }}" required>
            @error('applied_date') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- السيرة الذاتية (PDF) --}}
          <div class="col-12 col-md-6">
            <label class="form-label">{{ \App\CPU\translate('السيرة الذاتية (PDF)') }}</label>
            <input type="file" name="resume_pdf" class="form-control" accept="application/pdf">
            <div class="help">{{ \App\CPU\translate('تعليمات') }}: {{ \App\CPU\translate('يرجى رفع ملف PDF فقط') }}</div>
            @error('resume_pdf') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- تاريخ المقابلة (اختياري) --}}
          <div class="col-12 col-md-6">
            <label class="form-label">{{ \App\CPU\translate('تاريخ المقابلة') }}</label>
            <input type="date" name="interview_date" class="form-control"
                   value="{{ old('interview_date') }}">
            @error('interview_date') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

        </div>

        {{-- زر الحفظ --}}
        <div class="mt-4 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary col-12 col-md-3">
            {{ \App\CPU\translate('حفظ') }}
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection

@push('script_2')
<script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
@endpush
