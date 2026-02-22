@extends('layouts.admin.app')
@section('title', \App\CPU\translate('كورسات الموظف'))

@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/select2.min.css') }}">
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --bg:#f8fafc; --brand:#0d6efd;
      --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{font-size:1.2rem;margin:0;color:var(--ink);font-weight:800}

    .form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .col-12{grid-column:span 12}
    .col-4{grid-column:span 12}
    .col-8{grid-column:span 12}
    @media(min-width:768px){
      .col-md-4{grid-column:span 4}
      .col-md-8{grid-column:span 8}
      .col-md-6{grid-column:span 6}
      .col-md-3{grid-column:span 3}
    }

    .form-label{font-weight:700;color:#111827; margin-bottom:6px}
    .form-control, .select2-container--default .select2-selection--single{
      min-height:44px; border:1px solid var(--grid); border-radius:10px
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height:42px; padding-right:10px
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
      height:42px
    }

    .help{font-size:.85rem; color:var(--muted)}
    .btn-wide{min-height:44px; min-width:180px}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('كورسات الموظف') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== Header ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1>{{ \App\CPU\translate('إضافة كورس لموظف') }}</h1>
    </div>
  </div>

  {{-- ====== Form ====== --}}
  <div class="card-soft p-3">
    <form id="course-form" method="POST" action="{{ route('admin.coursesellers.store') }}" novalidate>
      @csrf

      <div class="form-grid">
        {{-- الموظف --}}
        <div class="col-12 col-md-6">
          <label for="seller_id" class="form-label">{{ \App\CPU\translate('الموظف') }}</label>
          <select id="seller_id" name="seller_id" class="form-control select2" required>
            <option value="">{{ \App\CPU\translate('اختر الموظف') }}</option>
            @foreach($sellers as $seller)
              <option value="{{ $seller->id }}">{{ $seller->email }}</option>
            @endforeach
          </select>
          <div class="help">{{ \App\CPU\translate('اختر الموظف المُستهدف لهذا الكورس') }}</div>
        </div>

        {{-- اسم الكورس --}}
        <div class="col-12 col-md-6">
          <label for="name" class="form-label">{{ \App\CPU\translate('اسم الكورس') }}</label>
          <input type="text" id="name" name="name" class="form-control" required
                 placeholder="{{ \App\CPU\translate('ادخل اسم الكورس') }}"
                 value="{{ old('name') }}">
        </div>

        {{-- الرابط --}}
        <div class="col-12 col-md-8">
          <label for="link" class="form-label">{{ \App\CPU\translate('رابط الكورس') }}</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="tio-link"></i></span>
            </div>
            <input type="url" id="link" name="link" class="form-control" required
                   placeholder="https://example.com/course"
                   pattern="https?://.*"
                   value="{{ old('link') }}">
          </div>
          <div class="help">{{ \App\CPU\translate('اكتب رابط صالح يبدأ بـ http أو https') }}</div>
        </div>

        {{-- زر الحفظ - على اليسار --}}
        <div class="col-12 d-flex justify-content-end mt-2">
          <button type="submit" class="btn btn-primary btn-wide col-3">
            <i class="tio-save"></i> {{ \App\CPU\translate('حفظ') }}
          </button>
        </div>
      </div>
    </form>
  </div>

</div>
@endsection

  <script src="{{ asset('public/assets/admin/js/jquery.min.js') }}"></script>
  <script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>
  <script>
    $(function(){
      // Select2
      $('.select2').select2({ width:'100%', placeholder: "{{ \App\CPU\translate('اختر') }}" });

      // بسيط: تأكيد أن الرابط يطابق الـ pattern
      $('#course-form').on('submit', function(e){
        const link = $('#link').val().trim();
        const urlOk = /^https?:\/\/.+/.test(link);
        if(!urlOk){
          e.preventDefault();
          alert("{{ \App\CPU\translate('من فضلك أدخل رابط صحيح يبدأ بـ http أو https') }}");
        }
      });
    });
  </script>
