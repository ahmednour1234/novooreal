@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_category'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
    <style>
 
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
                <a href="{{ route('admin.costcenter.add') }}" class="text-primary">
                    {{ \App\CPU\translate('مراكز التكلفة') }}
                </a>
            </li>
            <li class="breadcrumb-item active text-highlight" aria-current="page">
                {{ \App\CPU\translate('إنشاء مراكز تكلفة فرعية تحت مركز') }} {{ $costCenter->name }}
            </li>
        </ol>
    </nav>
</div>


    <div class="row">
        <!-- Form -->
        <div class="col-lg-12">
            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('admin.costcenter.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label required">{{ \App\CPU\translate('اسم') }}</label>
                                <input type="text" name="name" class="form-control" placeholder="مركز تكلفة مشتريات" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">{{ \App\CPU\translate('الكود') }}</label>
                                <input type="text" name="code" class="form-control" placeholder="8825a" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label required">{{ \App\CPU\translate('الوصف') }}</label>
                                <input type="text" name="description" class="form-control" placeholder="مركز تكلفة رئيسي" required>
                            </div>

                            <input type="hidden" name="parent_id" value="{{ $id }}">
                        </div>

                  <div class="row mt-4">
    <div class="col-12 d-flex justify-content-end">
        <button type="submit"
                class="btn btn-primary px-9"
                onclick="disableButton(event)">
            <span class="button-text">{{ \App\CPU\translate('حفظ') }}</span>
            <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
        </button>
    </div>
</div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cost Center Table -->
        <div class="col-lg-12 mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="w-100">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-6 col-lg-7 col-xl-8">
                                    <h5>{{ \App\CPU\translate('جدول مراكز التكلفة') }}
                                        <span class="badge badge-soft-dark">{{$costCenters->total()}}</span>
                                    </h5>


                                </div>
                                <div class=" col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                                    <!-- Search -->
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="tio-search"></i>
                                                </div>
                                            </div>
                                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                                   placeholder="{{ \App\CPU\translate('بحث باسم مركز التكلفة') }}"
                                                   aria-label="Search orders" value="{{ $search }}">
                                            <button type="submit"
                                                    class="btn btn-primary">{{ \App\CPU\translate('بحث') }}</button>
                                        </div>
                                    </form>
                                    <!-- End Search -->
                                </div>
                            </div>
                        </div>

                </div>
                <div class="table-responsive">
            <table
                            class="table">
                            <thead class="">
                                <tr>
                                    <th>{{ \App\CPU\translate('#') }}</th>
                                    <th>{{ \App\CPU\translate('الاسم') }}</th>
                                    <th>{{ \App\CPU\translate('الكود') }}</th>
                                                                        <th>{{ \App\CPU\translate('الوصف') }}</th>

                                    <th>{{ \App\CPU\translate('الحالة') }}</th>
                                    <th>{{ \App\CPU\translate('اجراءات') }}</th>
                                </tr>

                            </thead>

                            <tbody>
                                @foreach ($costCenters as $key => $category)
                                    <tr>
                                        <td>{{ $costCenters->firstitem() + $key }}</td>
                                
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $category['name'] }}
                                            </span>
                                        </td>
                                                <td>
                                                                                     {{ $category['code'] }}

                                        </td>
                                                <td>
                                                                                     {{ $category['description'] }}

                                        </td>
                                        <td>

                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox" class="toggle-switch-input"
                                                    onclick="location.href='{{ route('admin.costcenter.status', [$category['id'], $category->active ? 0 : 1]) }}'"
                                                    class="toggle-switch-input" {{ $category->active ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <a class="btn btn-white mr-1"
                                                href="{{ route('admin.costcenter.edit', [$category['id']]) }}">
                                                <span class="tio-edit"></span>
                                            </a>
                                         <a class="btn btn-white mr-1" href="{{ route('admin.costcenter.show', [$category['id']]) }}">
    <i class="tio-visible"></i>
</a>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    <div class="d-flex justify-content-center mt-3">
                        {!! $costCenters->links() !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- End Table -->
    </div>
</div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
@endpush
