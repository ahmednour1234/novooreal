@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_regions'))

@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>

  <style>
    /* ===== جماليات عامة للصفحة ===== */
    .page-hero {
      background: #fff;
    }
 

    .card-elevated {
      border: 0;
      border-radius: 14px;
      box-shadow: 0 10px 28px rgba(0,0,0,.08);
      overflow: hidden;
    }
    .card-header-soft {
      background:white;
      color: black;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .card-header-soft h5 { margin: 0; font-weight: 700; letter-spacing: .3px; }

    .input-label { font-weight: 600; color: #374151; }
    .form-control {
      border-radius: 10px;
      border-color: #e5e7eb;
    }
    .form-control:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 .15rem rgba(99,102,241,.15);
    }

    .btn-gradient {
      background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
      color: #fff;
      border: 0;
      border-radius: 12px;
      padding: 10px 22px;
      transition: transform .12s ease, filter .12s ease;
    }
    .btn-gradient:hover { filter: brightness(.97); transform: translateY(-1px); }
    .btn-danger-soft {
      background: #fff;
      border: 1px solid #fee2e2;
      color: #ef4444;
      border-radius: 10px;
    }
    .btn-danger-soft:hover { background: #fee2e2; }

    /* جدول */
    .table-modern thead th {
      background: #f9fafb;
      border-bottom: 1px solid #e5e7eb;
      color: #374151;
      font-weight: 700;
    }
    .table-modern tbody td { vertical-align: middle; }
    .table-modern tr:hover td { background: #fcfcfd; }

    /* مساحة التصفح */
    .page-area tfoot { background: transparent; }

    /* Empty state */
    .empty-wrap {
      text-align: center;
      padding: 38px 20px;
    }
    .empty-wrap img { max-width: 180px; opacity: .95; }
    .empty-wrap p { margin: 10px 0 0; color: #6b7280; }

    /* زر الحفظ يسار */
    .save-actions { display: flex; justify-content: flex-end; gap: 10px; }
  </style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb + عنوان لطيف ====== --}}
    <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
  <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('المناطق') }}
        </li>
      </ol>
    </nav>
  </div>

  {{-- ====== رسائل النجاح / الأخطاء ====== --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row gx-2 gx-lg-3">
    <div class="col-12 mb-3">
      <div class="card card-elevated">
        <div class="card-header-soft">
          <h5>{{ \App\CPU\translate('إضافة منطقة جديدة') }}</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.regions.store') }}" method="post" id="product_form" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
              <div class="col-12 col-sm-8 col-md-6">
                <label class="input-label">{{ \App\CPU\translate('اسم المنطقة') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="tio-map"></i></span>
                  <input type="text"
                         name="region_name"
                         class="form-control"
                         value="{{ old('region_name') }}"
                         placeholder="{{ \App\CPU\translate('اضف مدينة او دولة') }}"
                         required>
                </div>
              </div>
            </div>

            <div class="mt-3 save-actions">
              <button type="submit" class="btn btn-primary col-3">
                 {{ \App\CPU\translate('حفظ') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- ====== الجدول ====== --}}
    <div class="col-12">
      <div class="card card-elevated">
        <div class="card-header-soft">
          <h5>{{ \App\CPU\translate('قائمة المناطق') }}</h5>
          {{-- مكان بحث صغير (اختياري) ممكن تفعلّه لاحقًا --}}
          {{-- 
          <form method="GET" class="d-none d-md-block">
            <div class="input-group input-group-sm" style="width: 240px;">
              <input type="text" class="form-control" name="q" placeholder="{{ \App\CPU\translate('بحث...') }}" value="{{ request('q') }}">
              <button class="btn btn-light" type="submit"><i class="tio-search"></i></button>
            </div>
          </form>
          --}}
        </div>

        <div class="table-responsive">
          <table class="table table-modern align-middle mb-0">
            <thead>
              <tr>
                <th style="width: 80px;">{{ \App\CPU\translate('#') }}</th>
                <th>{{ \App\CPU\translate('اسم المنطقة') }}</th>
                <th style="width: 140px;" class="text-center">{{ \App\CPU\translate('إجراءات') }}</th>
              </tr>
            </thead>

            <tbody id="set-rows">
            @forelse($regions as $key => $reg)
              <tr>
                <td class="text-muted">{{ $key + 1 }}</td>
                <td class="fw-semibold">{{ $reg->name }}</td>
                <td class="text-center">
                  <a class="btn btn-sm btn-light border me-1"
                     href="{{ route('admin.regions.edit', $reg['id']) }}"
                     title="{{ \App\CPU\translate('تعديل') }}">
                    <span class="tio-edit"></span>
                  </a>

                  <a class="btn btn-sm btn-danger-soft"
                     href="javascript:void(0)"
                     onclick="form_alert('region-{{ $reg['id'] }}','{{ \App\CPU\translate('هل متأكد من حذف المنطقة?') }}')"
                     title="{{ \App\CPU\translate('حذف') }}">
                    <span class="tio-delete"></span>
                  </a>

                  <form action="{{ route('admin.regions.delete', [$reg['id']]) }}"
                        method="post" id="region-{{ $reg['id'] }}" class="d-none">
                    @csrf @method('delete')
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3">
                  <div class="empty-wrap">
                    <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}"
                         alt="{{ \App\CPU\translate('Image Description') }}">
                    <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات للعرض') }}</p>
                  </div>
                </td>
              </tr>
            @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if($regions->hasPages())
          <div class="p-3 d-flex justify-content-center">
            {!! $regions->onEachSide(1)->links() !!}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('script_2')
  <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
  <script>
    // تنبيه حذف آمن
    function form_alert(id, message) {
      if (confirm(message)) {
        document.getElementById(id).submit();
      }
    }
  </script>
@endpush
