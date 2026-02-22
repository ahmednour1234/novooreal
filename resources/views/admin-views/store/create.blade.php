@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_store'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css"/>
    <style>
        .form-group label {
            font-weight: bold;
        }
        .form-control {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 2rem;
        }
        .btn-primary {
            padding: 10px 20px;
            font-weight: bold;
        }
        .page-header-title {
            font-size: 1.5rem;
            color: #343a40;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="row align-items-center mb-3">
        <div class="col-sm mb-2 mb-sm-0">
            <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                <i class="tio-add-circle-outlined"></i> {{ \App\CPU\translate('إضافة مخزن جديد') }}
            </h1>
        </div>
    </div>
    <!-- End Page Header -->
    <div class="row gx-2 gx-lg-3">
        <div class="col-sm-12 col-lg-12 mb-lg-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.stores.store') }}" method="post" id="product_form" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('اسم المخزن') }} <span class="input-label-secondary text-danger">*</span></label>
                                    <input type="text" name="store_name1" class="form-control" placeholder="مخزن الرئيسي">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('كود المخزن') }}</label>
                                    <input type="text" name="store_code" class="form-control" placeholder="512164">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary col-12">{{ \App\CPU\translate('حفظ') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
