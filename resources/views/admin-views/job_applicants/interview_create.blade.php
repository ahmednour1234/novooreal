@extends('layouts.admin.app')

@section('title', \App\CPU\translate('إضافة تقييم مقابلة'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
<style>
  :root{
    --ink:#0f172a; --muted:#667085; --grid:#e9eef5; --bg:#f8fafc;
    --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  body{background:var(--bg)}
  .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
  .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .page-head h1{font-size:1.15rem;margin:0;color:var(--ink);font-weight:800}
  .form-label{font-weight:700;color:#111827}
  .is-invalid{border-color:#dc3545}
  .invalid-feedback{display:block}
  /* شبكة الحقول: مصفوفة مرتبة ومتجاوبة */
  .grid{display:grid;gap:12px}
  .grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}
  .grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
  @media (max-width: 992px){ .grid-3{grid-template-columns:repeat(2,1fr)} }
  @media (max-width: 576px){ .grid-3, .grid-2{grid-template-columns:1fr} }
  .btn-bar{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
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
        <li class="breadcrumb-item">
          <a href="{{ route('admin.job_applicants.index') }}" class="text-secondary">
            {{ \App\CPU\translate('متقدمين الوظائف') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('إضافة تقييم مقابلة للمتقدم') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== الكارت ====== --}}
  <div class="card-soft p-3">
    <div class="page-head mb-2">
      <h1 class="mb-0">{{ \App\CPU\translate('إضافة تقييم مقابلة') }}</h1>
      <div class="btn-bar">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
          <i class="tio-rotate-left"></i> {{ \App\CPU\translate('رجوع') }}
        </a>
      </div>
    </div>

    <form action="{{ route('admin.interview_evaluations.store', $applicantId) }}" method="POST" novalidate>
      @csrf

      {{-- صف 1: المُقابِل - التاريخ - التقييم --}}
      <div class="grid grid-3">
        <div>
          <label class="form-label">{{ \App\CPU\translate('اسم المُقابِل') }} <span class="text-danger">*</span></label>
          <input type="text" name="interviewer"
                 class="form-control @error('interviewer') is-invalid @enderror"
                 placeholder="{{ \App\CPU\translate('ادخل اسم المُقابِل') }}"
                 value="{{ old('interviewer') }}" required>
          @error('interviewer') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="form-label">{{ \App\CPU\translate('تاريخ المقابلة') }} <span class="text-danger">*</span></label>
          <input type="date" name="interview_date"
                 class="form-control @error('interview_date') is-invalid @enderror"
                 value="{{ old('interview_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
          @error('interview_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="form-label">{{ \App\CPU\translate('التقييم') }}</label>
          <input type="number" name="score" min="1" max="10" step="1"
                 class="form-control @error('score') is-invalid @enderror"
                 placeholder="{{ \App\CPU\translate('من 1 إلى 10') }}"
                 value="{{ old('score') }}">
          @error('score') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- صف 2: الملاحظات --}}
      <div class="mt-3">
        <label class="form-label">{{ \App\CPU\translate('الملاحظات') }}</label>
        <textarea name="evaluation_notes" rows="4"
                  class="form-control @error('evaluation_notes') is-invalid @enderror"
                  placeholder="{{ \App\CPU\translate('ادخل الملاحظات') }}">{{ old('evaluation_notes') }}</textarea>
        @error('evaluation_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- أزرار --}}
      <div class="btn-bar mt-3">
        <button type="submit" class="btn btn-primary">
          <i class="tio-checkmark-circle-outlined"></i> {{ \App\CPU\translate('حفظ') }}
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-light">
          <i class="tio-clear"></i> {{ \App\CPU\translate('إلغاء') }}
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('script_2')
<script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
@endpush
