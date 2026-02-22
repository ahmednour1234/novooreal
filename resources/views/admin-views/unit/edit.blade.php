@extends('layouts.admin.app')

@section('title',\App\CPU\translate('update_unit_type'))

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
      <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
{{\App\CPU\translate('تحديث وحدات القياس')}}        </li>
        
      </ol>
    </nav>
  </div>
        <!-- Page Header -->
     
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
<form action="{{ route('admin.unit.update', [$unit['id'], $type]) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="">{{\App\CPU\translate('الوحدة')}}</label>
                                        <input type="text" name="unit_type" class="form-control" value="{{ $unit->unit_type ??  $unit->name }}">
                                    </div>
                                </div>
  @if($type == 1)
        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label for="unit_id" class="font-weight-bold">{{ \App\CPU\translate('الوحدة') }}</label>
                <select name="unit_id" id="unit_id" class="form-control border-primary">
                    <option value="">{{ \App\CPU\translate('اختر الوحدة') }}</option>
                   @foreach($unitall as $units)
    <option value="{{ $units->id }}" {{ old('unit_id', $unit->unit_id ?? '') == $units->id ? 'selected' : '' }}>
        {{ $units->unit_type }}
    </option>
@endforeach

                </select>
            </div>
        </div>
    @endif
                            </div>
                            <hr>
    <div class="form-group mb-0">
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-left-fallback col-3">
                {{ \App\CPU\translate('تحديث') }}
            </button>
        </div>
    </div>                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('script_2')

@endpush
