@extends('layouts.admin.app')
@section('title', \App\CPU\translate('تقييم الموظف'))

@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/select2.min.css') }}">
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --bg:#f8fafc; --brand:#0d6efd;
      --rd:14px; --shadow:0 12px 28px -18px rgba(2,32,71,.18);
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{margin:0;font-size:1.15rem;color:var(--ink);font-weight:800}
    .form-label{font-weight:700;color:#111827}
    .select2-container{width:100%!important}
    .select2-selection--single{
      min-height:44px;border:1px solid #ced4da;border-radius:.375rem;display:flex;align-items:center
    }
    .select2-selection__rendered{line-height:42px!important}
    .select2-selection__arrow{height:42px!important}
    .btn-xl{min-height:44px}
  </style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تطوير الموظف') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== Header ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1>{{ \App\CPU\translate('تعديل ملاحظة تطوير') }}</h1>
      <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-xl">
        <i class="tio-rollback"></i> {{ \App\CPU\translate('رجوع') }}
      </a>
    </div>
  </div>

  {{-- ====== Form Card ====== --}}
  <div class="card-soft p-3">
    {{-- رسائل الأخطاء --}}
    @if ($errors->any())
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="dev-update-form" method="POST" action="{{ route('admin.developsellers.update', $developSeller->id) }}">
      @csrf
      @method('PUT')

      <div class="row g-3 align-items-end">
        {{-- الموظف (مقفل) --}}
        <div class="col-md-6">
          <label for="seller_id" class="form-label">{{ \App\CPU\translate('اختار موظف') }}</label>
          {{-- نعرض Select2 معطّل + نخزن القيمة بحقل hidden عشان تتبعت مع الفورم --}}
          <input type="hidden" name="seller_id" value="{{ $developSeller->seller_id }}">
          <select id="seller_id" class="form-control select2" disabled>
            <option value="{{ $developSeller->sellers->id }}" selected>
              {{ $developSeller->sellers->email }}
            </option>
          </select>
        </div>

        {{-- الملاحظة --}}
        <div class="col-md-6">
          <label for="note" class="form-label">{{ \App\CPU\translate('ملاحظات') }} <span class="text-danger">*</span></label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="tio-note"></i></span>
            </div>
            <input type="text" id="note" name="note" class="form-control"
                   placeholder="{{ \App\CPU\translate('اكتب ملاحظة واضحة ومختصرة') }}"
                   value="{{ old('note', $developSeller->note) }}" required>
          </div>
        </div>
      </div>

      {{-- زرّ الحفظ (على الشمال) --}}
      <div class="mt-4 d-flex w-100" style="justify-content:flex-end;">
        <button type="submit" class="btn btn-primary btn-xl col-3">
          <i class="tio-save"></i> {{ \App\CPU\translate('حفظ') }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

  <script src="{{ asset('public/assets/admin/js/jquery.min.js') }}"></script>
  <script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>
  <script>
    $(function(){
      // تشغيل Select2 للعرض فقط (disabled في الـ HTML)
      $('.select2').select2({
        width:'100%',
        dir:'rtl',
        minimumResultsForSearch: -1   // إخفاء البحث لأنها قراءة فقط
      });
    });
  </script>
