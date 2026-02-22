@extends('layouts.admin.app')

@section('title', \App\CPU\translate('category_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
    <style>
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px;
            font-size: 16px;
        }
      
   
        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .page-header-title {
            font-weight: bold;
            color: #333;
        }
        .input-label {
            font-weight: bold;
            color: #555;
        }
    </style>
@endpush

@section('content')
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
                <a href="{{route('admin.taxe.list')}}" class="text-primary">
                    {{ \App\CPU\translate('الضرائب') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="#" class="text-primary">
{{ \App\CPU\translate('تحديث الضريبة') }}
</a>
            </li>
                  
           
        </ol>
    </nav>
</div>
   
    <!-- End Page Header -->

    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card">
                <div class="card-body">
                 <form action="{{ route('admin.taxe.update', [$taxe['id']]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- اسم الضريبة --}}
        <div class="col-md-6 mb-3">
            <label class="input-label">{{ \App\CPU\translate('اسم') }}</label>
            <input type="text" name="name" value="{{ $taxe['name'] }}" class="form-control"
                   placeholder="{{ \App\CPU\translate('new_taxe') }}" required>
        </div>

        {{-- القيمة --}}
        <div class="col-md-6 mb-3">
            <label class="input-label">{{ \App\CPU\translate('القيمة') }}</label>
            <input type="text" name="amount" value="{{ $taxe['amount'] }}" class="form-control"
                   placeholder="{{ \App\CPU\translate('%') }}" required>
        </div>
    </div>

    {{-- زر الحفظ --}}
   <div class="d-flex justify-content-end mt-5">
    <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
        {{ \App\CPU\translate('حفظ') }}
    </button>
</div>
</form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

