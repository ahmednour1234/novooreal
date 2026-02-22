@extends('layouts.admin.app')

@section('title', \App\CPU\translate('category_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
@endpush

@section('content')
                  <style>
    label.required:after {
        content: " *";
        color: red;
        font-weight: bold;
    }
</style>
<div class="content container-fluid">
    <!-- Page Header -->
<div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                    <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate(' تحديث مراكز التكلفة') }}
                </a>
            </li>
      
        </ol>
    </nav>
</div>
    <!-- End Page Header -->

    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">


<form action="{{ route('admin.costcenter.update', [$category['id']]) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label required">{{ \App\CPU\translate('الاسم') }}</label>
            <input type="text" name="name" value="{{ $category['name'] }}" class="form-control"
                   placeholder="{{ \App\CPU\translate('new_category') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label required">{{ \App\CPU\translate('الكود') }}</label>
            <input type="text" name="code" value="{{ $category['code'] }}" class="form-control"
                   placeholder="{{ \App\CPU\translate('new_category') }}" required>
        </div>

        <div class="col-12">
            <label class="form-label required">{{ \App\CPU\translate('الوصف') }}</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="{{ \App\CPU\translate('new_category') }}" required>{{ $category['description'] }}</textarea>
        </div>
    </div>
<div class="row mt-4">
    <div class="col-12 d-flex justify-content-end">
        <button type="submit"
                class="btn btn-primary px-9"
                onclick="disableButton(event)">
            <span class="button-text">{{ \App\CPU\translate('تحديث') }}</span>
            <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
        </button>
    </div>
</div>
</form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
@endpush
