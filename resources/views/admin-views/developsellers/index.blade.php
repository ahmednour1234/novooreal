@extends('layouts.admin.app')

@section('title', \App\CPU\translate('seller_list'))

@push('css_or_js')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --brand:#0d6efd; --bg:#f8fafc;
      --ok:#16a34a; --warn:#d97706; --bad:#dc2626; --info:#0ea5e9; --rd:14px;
      --shadow:0 12px 28px -18px rgba(2,32,71,.18);
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{margin:0;font-size:1.15rem;color:var(--ink);font-weight:800}

    .toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .search-wrap{flex:1 1 420px}
    .input-group.input-group-merge .input-group-text{background:#fff;border-right:0}
    .input-group.input-group-merge .form-control{border-left:0}

    .btn-primary, .btn-outline-primary, .btn-white{min-height:40px}
    .btn-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;padding:0}
    .gap-6{gap:6px}

    .table thead th{background:#f3f6fb}
    .table td, .table th{vertical-align:middle}
    .table-hover tbody tr:hover{background:#f9fbff}

    .chip{display:inline-flex;align-items:center;gap:6px;padding:.25rem .5rem;border-radius:999px;font-size:.78rem;font-weight:700}
    .chip-blue{background:#eff6ff;color:#1d4ed8}
    .chip-amber{background:#fff7ed;color:#9a3412}
    .chip-green{background:#ecfdf5;color:#065f46}
    .chip-gray{background:#f3f4f6;color:#374151}

    .note-cell{max-width:380px}
    .truncate-2{
      display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden
    }

    .badge-soft{padding:.35rem .6rem;border-radius:999px;font-size:.78rem;font-weight:700}
    .bdg-warn{background:#fff7ed;color:#9a3412}
    .bdg-ok{background:#ecfdf5;color:#065f46}

    .page-area nav{display:inline-block}
  </style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-1">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">
          @if($type == 0)
            {{ \App\CPU\translate('قائمة ملاحظات التطوير') }}
          @elseif($type == 1)
            {{ \App\CPU\translate('قائمة التطوير') }}
          @else
            {{ \App\CPU\translate('طلبات اجازات') }}
          @endif
        </li>
      </ol>
    </nav>
  </div>

  {{-- ====== Header + Tools ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1>
        @if($type == 0)
          {{ \App\CPU\translate('قائمة ملاحظات التطوير') }}
          <span class="chip chip-blue"><i class="tio-lightbulb-on"></i> {{ \App\CPU\translate('ملاحظات') }}</span>
        @elseif($type == 1)
          {{ \App\CPU\translate('قائمة  التطوير') }}
          <span class="chip chip-amber"><i class="tio-book-opened"></i> {{ \App\CPU\translate('التطوير') }}</span>
        @else
          {{ \App\CPU\translate('طلبات اجازات') }}
          <span class="chip chip-green"><i class="tio-sun"></i> {{ \App\CPU\translate('إجازات') }}</span>
        @endif
      </h1>

      <div class="toolbar">
    

        {{-- Add Button (only for type 0) --}}
        @if($type == 0)
          <a href="{{ route('admin.developsellers.create', ['type' => 0]) }}" class="btn btn-outline-primary">
            <i class="tio-add-circle"></i> {{ \App\CPU\translate('اضافة ملاحظة للتطوير') }}
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- ====== Table ====== --}}
  <div class="card-soft p-3">
    <div class="table-responsive datatable-custom">
      <table class="table table-borderless table-hover table-align-middle card-table">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الاسم') }}</th>
            <th>{{ \App\CPU\translate('الايميل') }}</th>
            <th>{{ \App\CPU\translate('المدير') }}</th>
            <th class="note-cell">
              @if($type == 0)
                {{ \App\CPU\translate('الملاحظة التطوير') }}
              @elseif($type == 1)
                {{ \App\CPU\translate('طلب موظف') }}
              @else
                {{ \App\CPU\translate('طلب إجازة') }}
              @endif
            </th>
            @if($type == 2)
              <th>{{ \App\CPU\translate('تاريخ الاجازة') }}</th>
              <th>{{ \App\CPU\translate('موافقة الاجازة') }}</th>
            @endif
            <th class="text-center" style="width:120px">{{ \App\CPU\translate('الاجراءات') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($developSellers as $key => $seller)
            <tr>
              <td>{{ $key + 1 }}</td>
              <td>{{ $seller->sellers->f_name . ' ' . $seller->sellers->l_name }}</td>
              <td>{{ $seller->sellers->email }}</td>
              <td>{{ $seller->admins->email }}</td>
              <td class="note-cell">
                <div class="truncate-2" title="{{ $seller->note }}">{{ $seller->note }}</div>
              </td>

              @if($type == 2)
                <td>{{ $seller->date }}</td>
                <td>
                  @if($seller->active == 0)
                    <form action="{{ route('admin.developsellers.status', $seller->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('PUT')
                      <button type="submit" class="btn btn-sm btn-success">
                        <i class="tio-checkmark-circle-outlined"></i>
                        {{ \App\CPU\translate('موافقة علي اجازة') }}
                      </button>
                    </form>
                  @elseif($seller->active == 1)
                    <span class="badge-soft bdg-ok"><i class="tio-done"></i> {{ \App\CPU\translate('لقد تمت الموافقة عل اجازة') }}</span>
                  @endif
                </td>
              @endif

              <td class="text-center">
                <div class="d-inline-flex gap-6">
                  <a class="btn btn-white btn-icon" href="{{ route('admin.developsellers.edit', $seller->id) }}" data-toggle="tooltip" title="{{ \App\CPU\translate('تعديل') }}">
                    <i class="tio-edit"></i>
                  </a>

                  <a class="btn btn-white btn-icon"
                     href="javascript:"
                     data-toggle="tooltip"
                     title="{{ \App\CPU\translate('حذف') }}"
                     onclick="form_alert('seller-{{ $seller->id }}', '{{ \App\CPU\translate('Want to delete this seller?') }}')">
                    <i class="tio-delete"></i>
                  </a>

                  <form action="{{ route('admin.developsellers.destroy', $seller->id) }}" method="post" id="seller-{{ $seller->id }}">
                    @csrf
                    @method('delete')
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Pagination --}}
      <div class="page-area d-flex justify-content-center mt-2">
        {!! $developSellers->links() !!}
      </div>

      {{-- No Data --}}
      @if($developSellers->isEmpty())
        <div class="text-center p-4">
          <img class="mb-3 w-one-cl" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="{{ \App\CPU\translate('Image Description') }}">
          <p class="mb-0">{{ \App\CPU\translate('No_data_to_show') }}</p>
        </div>
      @endif
    </div>
  </div>

</div>
@endsection

@push('script_2')
  <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
  <script>
    // تفعيل التولتيب إن كان Bootstrap مُحمّل في الـ layout
    if (window.$ && typeof $.fn.tooltip === 'function') {
      $('[data-toggle="tooltip"]').tooltip();
    }
  </script>
@endpush
