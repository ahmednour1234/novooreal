@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_category'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css" />
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
                <a href="#" class="text-primary">
{{ \App\CPU\translate('الشيفت') }}                </a>
            </li>
           
        </ol>
    </nav>
</div>

        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                        <div class="w-100">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-6 col-lg-7 col-xl-8">
                             


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
                                                   placeholder="{{ \App\CPU\translate('بحث باسم الشيفت') }}"
                                                   aria-label="Search orders" value="{{ $search }}" >
                                            <button type="submit"
                                                    class="btn btn-primary">{{ \App\CPU\translate('بحث') }}</button>
                                        </div>
                                    </form>
                                    <!-- End Search -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive ">
                        <table
                            class="table  table-nowrap table-align-middle card-table">
                            <thead >
                                <tr>
                                    <th>{{ \App\CPU\translate('#') }}</th>
                                    <th>{{ \App\CPU\translate('الاسم') }}</th>
                                    <th>{{ \App\CPU\translate('الحالة') }}</th>
                                    <th>{{ \App\CPU\translate('اجراءات') }}</th>
                                </tr>

                            </thead>

                            <tbody>
                                @foreach ($categories as $key => $category)
                                    <tr>
                                        <td>{{ $categories->firstitem() + $key }}</td>
                                   
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $category['name'] }}
                                            </span>
                                        </td>
                                        <td>

                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox" class="toggle-switch-input"
                                                    onclick="location.href='{{ route('admin.shift.status', [$category['id'], $category->active ? 0 : 1]) }}'"
                                                    class="toggle-switch-input" {{ $category->active ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <a class="btn btn-white mr-1"
                                                href="{{ route('admin.shift.edit', [$category['id']]) }}">
                                                <span class="tio-edit"></span>
                                            </a>
                                          
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <hr>
                        <table>
                            <tfoot>
                                {!! $categories->links() !!}
                            </tfoot>
                        </table>
                        @if (count($categories) == 0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-cati"
                                    src="{{ asset('public/assets/admin') }}/svg/illustrations/sorry.svg"
                                    alt="Image Description">
                                <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>
@endsection

