@extends('layouts.admin.app')

@section('title', \App\CPU\translate('قائمة كورسات الموظفين'))

@push('css_or_js')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --bg:#f8fafc; --brand:#0d6efd;
      --ok:#16a34a; --bad:#dc2626; --warn:#f59e0b; --rd:14px;
      --shadow:0 12px 28px -18px rgba(2,32,71,.18);
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{margin:0;color:var(--ink);font-weight:800;font-size:1.25rem}

    .toolbar{display:flex;gap:8px;flex-wrap:wrap}
    .toolbar .btn{min-height:42px}

    .search-wrap{min-width:280px}
    .input-group-text{background:#fff}

    .table thead th{background:#f3f6fb; position:sticky; top:0; z-index:5}
    .table td,.table th{vertical-align:middle}
    .table-hover tbody tr:hover{background:#f9fbff}

    .btn-icon{
      width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;padding:0
    }
    .action-buttons{display:flex;gap:8px;flex-wrap:wrap}

    .cert-grid{
      display:grid; grid-template-columns:repeat( auto-fill, minmax(72px, 1fr) ); gap:6px; max-width:320px
    }
    .cert-grid img{width:100%; height:60px; object-fit:cover; border:1px solid var(--grid); border-radius:8px}

    .link-clip{max-width:260px; display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; vertical-align:bottom}
    .badge-empty{background:#fff3cd; color:#7c5100; border:1px dashed #f1d092; padding:.3rem .5rem; border-radius:999px; font-size:.8rem}

    .page-area nav{display:flex; justify-content:center}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('قائمة كورسات الموظفين') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== Header + Toolbar ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1>{{ \App\CPU\translate('كورسات التطوير للموظفين') }}</h1>

      <div class="toolbar">
        <a href="{{ route('admin.coursesellers.create') }}" class="btn btn-primary">
          <i class="tio-add-circle"></i> {{ \App\CPU\translate('إضافة كورس للتطوير') }}
        </a>

        <form action="{{ url()->current() }}" method="GET" class="search-wrap">
          <div class="input-group input-group-merge">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="tio-search"></i></span>
            </div>
            <input
              id="datatableSearch_"
              type="search"
              name="search"
              class="form-control"
              placeholder="{{ \App\CPU\translate('بحث بالاسم أو الإيميل') }}"
              aria-label="Search"
              value="{{ request('search') }}"
            >
            <div class="input-group-append">
              <button type="submit" class="btn btn-outline-primary">{{ \App\CPU\translate('بحث') }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ====== Table ====== --}}
  <div class="card-soft p-3">
    <div class="table-responsive table-hover">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>{{ \App\CPU\translate('الاسم') }}</th>
            <th>{{ \App\CPU\translate('الإيميل') }}</th>
            <th>{{ \App\CPU\translate('المدير') }}</th>
            <th>{{ \App\CPU\translate('اسم الكورس') }}</th>
            <th>{{ \App\CPU\translate('رابط الكورس') }}</th>
            <th style="min-width:180px">{{ \App\CPU\translate('الشهادات') }}</th>
            <th style="width:110px">{{ \App\CPU\translate('إجراءات') }}</th>
          </tr>
        </thead>
        <tbody id="set-rows">
          @forelse($courseSellers as $key => $seller)
            @php
              $imgs = null;
              if (!is_null($seller->img)) {
                $decoded = json_decode($seller->img, true);
                $imgs = is_array($decoded) ? $decoded : null;
              }
            @endphp
            <tr>
              <td>{{ $key + 1 }}</td>

              <td>{{ $seller->sellers->f_name . ' ' . $seller->sellers->l_name }}</td>

              <td>
                <a href="mailto:{{ $seller->sellers->email }}">{{ $seller->sellers->email }}</a>
              </td>

              <td>{{ $seller->admins->email }}</td>

              <td>{{ $seller->name }}</td>

              <td>
                @if($seller->link)
                  <a class="link-clip" href="{{ $seller->link }}" target="_blank" rel="noopener">
                    {{ $seller->link }}
                  </a>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                @if($imgs && count($imgs))
                  <div class="cert-grid">
                    @foreach($imgs as $image)
                      <a href="{{ asset('storage/app/public/' . $image) }}" target="_blank" rel="noopener" title="{{ \App\CPU\translate('عرض الشهادة') }}">
                        <img src="{{ asset('storage/app/public/' . $image) }}" alt="cert">
                      </a>
                    @endforeach
                  </div>
                @else
                  <span class="badge-empty">{{ \App\CPU\translate('لم يتم الانتهاء من الكورس') }}</span>
                @endif
              </td>

              <td>
                <div class="action-buttons">
                  <a class="btn btn-white btn-icon" href="{{ route('admin.coursesellers.edit', $seller->id) }}" title="{{ \App\CPU\translate('تعديل') }}">
                    <i class="tio-edit"></i>
                  </a>

                  <a class="btn btn-white btn-icon"
                     href="javascript:void(0)"
                     title="{{ \App\CPU\translate('حذف') }}"
                     onclick="if(typeof form_alert==='function'){form_alert('seller-{{ $seller->id }}','{{ \App\CPU\translate('هل تريد حذف هذا السجل؟') }}');}else{ if(confirm('{{ \App\CPU\translate('هل تريد حذف هذا السجل؟') }}')) document.getElementById('seller-{{ $seller->id }}').submit(); }">
                    <i class="tio-delete-outlined"></i>
                  </a>

                  <form action="{{ route('admin.coursesellers.destroy', $seller->id) }}"
                        method="POST" id="seller-{{ $seller->id }}" class="d-none">
                    @csrf
                    @method('delete')
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted">
                {{ \App\CPU\translate('لا توجد بيانات لعرضها') }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ====== Pagination ====== --}}
    <div class="page-area mt-3">
      {!! $courseSellers->links() !!}
    </div>
  </div>

  {{-- ====== Empty State (fallback) ====== --}}
  @if($courseSellers->isEmpty())
    <div class="text-center p-4">
      <img class="mb-3 w-one-cl" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="">
      <p class="mb-0">{{ \App\CPU\translate('No_data_to_show') }}</p>
    </div>
  @endif

</div>
@endsection

@push('script_2')
  <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
@endpush
