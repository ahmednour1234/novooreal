{{-- resources/views/admin/regions/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('edit_regions'))

@push('css_or_js')
<style>
  :root{
    --ink:#0f172a; --muted:#6b7280; --brand:#0b3d91; --accent:#f59e0b;
    --grid:#e5e7eb; --paper:#ffffff; --rd:14px;
  }
  .breadcrumb{direction: rtl}

  .card{
    border-radius: var(--rd);
    border:1px solid var(--grid);
    box-shadow: 0 10px 26px -14px rgba(2,32,71,.18);
    overflow: hidden;
  }
  .card-header{
    border-bottom: 1px solid var(--grid);
    padding: 16px 18px;
    display:flex; align-items:center; justify-content:space-between; gap:10px;
  }
  .card-header .title{
    display:flex; align-items:center; gap:10px; color:black; font-weight:800; margin:0;
  }
  .card-header .title .ico{
    width:38px; height:38px; border-radius:10px; background:#0b3d91; color:#fff;
    display:grid; place-items:center; font-size:18px;
    box-shadow:0 6px 16px -10px #0b3d91;
  }
  .card-body{ padding: 18px }
  .input-label{ font-weight:800; color:var(--muted) }
  .form-control{
    border-radius: 10px;
    height: 44px;
  }
  .help-row{ display:flex; justify-content:space-between; align-items:center; margin-top:6px; color:var(--muted) }
  .char-count{ font-weight:700; color:#475569 }
  .actions{
    display:flex; gap:.5rem; flex-wrap:wrap; justify-content:flex-end; margin-top: 14px;
  }
  .btn{ border-radius: 10px; font-weight:800 }
  .alert{ border-radius:12px }
</style>
@endpush

@section('content')
<div class="content container-fluid">
  {{-- Breadcrumb --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('تعديل اسم منطقة') }}
        </li>
      </ol>
    </nav>
  </div>

  {{-- Flash & Errors --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Card --}}
  <div class="card">
    <div class="card-header">
      <h5 class="title mb-0">
        <span class="ico"><i class="tio-map"></i></span>
        {{ \App\CPU\translate('تعديل منطقة') }}
      </h5>
      <small class="text-muted">{{ \App\CPU\translate('قم بتحديث اسم المنطقة وحفظ التغييرات') }}</small>
    </div>

    <div class="card-body">
      <form action="{{ route('admin.regions.update', $region->id) }}" method="post" autocomplete="off">
        @csrf

        <div class="row g-3">
          <div class="col-12">
            <label class="input-label">{{ \App\CPU\translate('اسم المنطقة') }}
              <span class="text-danger">*</span>
            </label>
            <input
              type="text"
              name="region_name"
              id="regionNameInput"
              class="form-control @error('region_name') is-invalid @enderror"
              value="{{ old('region_name', $region->name) }}"
              placeholder="{{ \App\CPU\translate('region_name') }}"
              required
              maxlength="80"
              autofocus
            >
            <div class="help-row">
              <small class="text-muted">{{ \App\CPU\translate('استخدم اسمًا واضحًا وقصيرًا') }}</small>
              <small class="char-count"><span id="rnCount">0</span> / 80</small>
            </div>
            @error('region_name')
              <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn btn-primary">
            <i class="tio-save"></i> {{ \App\CPU\translate('تحديث') }}
          </button>
          <button type="reset" class="btn btn-outline-secondary">
            <i class="tio-undo"></i> {{ \App\CPU\translate('إعادة تعيين') }}
          </button>
          <a href="{{ url()->previous() }}" class="btn btn-light">
            <i class="tio-arrow_back"></i> {{ \App\CPU\translate('رجوع') }}
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Tiny script: live character counter --}}
<script>
  (function(){
    const input = document.getElementById('regionNameInput');
    const counter = document.getElementById('rnCount');
    if(input && counter){
      const update = () => counter.textContent = input.value.length;
      input.addEventListener('input', update);
      update();
    }
  })();
</script>
@endsection
