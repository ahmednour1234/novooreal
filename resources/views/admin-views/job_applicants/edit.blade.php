@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تعديل طلب التوظيف'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
<style>
  :root{
    --ink:#0f172a; --muted:#667085; --grid:#e9eef5; --brand:#001B63;
    --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
  .section-title{font-weight:800;color:#111827;margin-bottom:.5rem}
  .form-label{font-weight:700;color:#111827}
  .grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
  .grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
  @media (max-width: 768px){
    .grid-2,.grid-3{grid-template-columns:1fr}
  }
  .file-note{font-size:.85rem;color:var(--muted)}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تعديل طلب التوظيف') }}</li>
      </ol>
    </nav>
  </div>

  <div class="card card-soft">
    <div class="card-body">
      <form action="{{ route('admin.job_applicants.update', $applicant->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ====== بيانات المتقدم ====== --}}
        <h6 class="section-title">{{ \App\CPU\translate('بيانات المتقدم') }}</h6>
        <div class="grid-2">
          {{-- الاسم الكامل --}}
          <div class="form-group mb-3">
            <label class="form-label">{{ \App\CPU\translate('الاسم الكامل') }} <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control"
                   value="{{ old('full_name', $applicant->full_name) }}" required>
            @error('full_name')<small class="text-danger">{{ $message }}</small>@enderror
          </div>

          {{-- البريد الإلكتروني --}}
          <div class="form-group mb-3">
            <label class="form-label">{{ \App\CPU\translate('البريد الالكتروني') }} <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email', $applicant->email) }}" required>
            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>

        <div class="grid-3">
          {{-- الهاتف --}}
          <div class="form-group mb-3">
            <label class="form-label">{{ \App\CPU\translate('رقم الهاتف') }}</label>
            <input type="text" name="phone" class="form-control"
                   value="{{ old('phone', $applicant->phone) }}">
            @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
          </div>

          {{-- تاريخ التقديم --}}
          <div class="form-group mb-3">
            <label class="form-label">{{ \App\CPU\translate('تاريخ التقديم') }} <span class="text-danger">*</span></label>
            <input type="date" name="applied_date" class="form-control"
                   value="{{ old('applied_date', optional($applicant->applied_date)->format('Y-m-d')) }}" required>
            @error('applied_date')<small class="text-danger">{{ $message }}</small>@enderror
          </div>

          {{-- تاريخ المقابلة (اختياري) --}}
          <div class="form-group mb-3">
            <label class="form-label">{{ \App\CPU\translate('تاريخ المقابلة') }}</label>
            <input type="date" name="interview_date" class="form-control"
                   value="{{ old('interview_date', optional($applicant->interview_date)->format('Y-m-d')) }}">
            @error('interview_date')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>

        {{-- ====== الحالة والسيرة الذاتية ====== --}}
        <div class="grid-2">
          {{-- الحالة --}}
          <div class="form-group mb-3">
            <label class="form-label">{{ \App\CPU\translate('الحالة') }}</label>
            @php
              $statuses = [
                'new'       => \App\CPU\translate('جديد'),
                'screening' => \App\CPU\translate('قيد الفرز'),
                'interview' => \App\CPU\translate('المقابلة'),
                'accepted'  => \App\CPU\translate('مقبول'),
                'rejected'  => \App\CPU\translate('مرفوض'),
              ];
              $currentStatus = old('status', $applicant->status);
            @endphp
            <select name="status" class="form-control">
              <option value="">{{ \App\CPU\translate('اختر الحالة') }}</option>
              @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus == $value)>{{ $label }}</option>
              @endforeach
            </select>
            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
          </div>

          {{-- السيرة الذاتية (PDF) --}}
          <div class="form-group mb-3">
            <label class="form-label">{{ \App\CPU\translate('السيرة الذاتية (PDF)') }}</label>
            <input type="file" name="resume_pdf" class="form-control" accept="application/pdf">
            @if($applicant->resume_pdf)
              <div class="file-note mt-1">
                {{ \App\CPU\translate('الملف الحالي') }}:
                <a href="{{ asset('storage/app/public/resumes/' . $applicant->resume_pdf) }}" target="_blank">
                  {{ $applicant->resume_pdf }}
                </a>
              </div>
            @endif
            @error('resume_pdf')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>

        {{-- زر التحديث --}}
        <div class="mt-3 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary col-3">
            <i class="tio-save"></i> {{ \App\CPU\translate('تحديث') }}
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
