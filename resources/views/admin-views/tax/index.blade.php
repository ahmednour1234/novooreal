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
                    {{ \App\CPU\translate('الضرائب') }}
                </a>
            </li>
                  
           
        </ol>
    </nav>
</div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.taxe.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="">{{ \App\CPU\translate('اسم الضريبة') }}</label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="{{ \App\CPU\translate('add_category_name') }}">
                                    </div>
                                </div>
                                   <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="">{{ \App\CPU\translate('المبلغ') }}</label>
                                        <input type="text" name="amount" class="form-control"
                                            placeholder="{{ \App\CPU\translate('%') }}">
                                    </div>
                                </div>
                    
                            </div>
<div class="d-flex justify-content-end mt-5">
    <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
        {{ \App\CPU\translate('حفظ') }}
    </button>
</div>                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                        <div class="w-100">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-6 col-lg-7 col-xl-8">
                                   

                                </div>
                                <div class=" col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                          
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
                                     <th>{{ \App\CPU\translate('القيمة') }}</th>
                                    <th>{{ \App\CPU\translate('التفعيل') }}</th>
                                    <th>{{ \App\CPU\translate('الاجراءات') }}</th>
                                </tr>

                            </thead>

                            <tbody>
                                @foreach ($taxes as $key => $taxe)
                                    <tr>
                                        <td>{{ $taxes->firstitem() + $key }}</td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $taxe['name'] }}
                                            </span>
                                        </td>
                                         <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $taxe['amount'] }}
                                            </span>
                                        </td>
                                        <td>

                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox" class="toggle-switch-input"
                                                    onclick="location.href='{{ route('admin.taxe.status', [$taxe['id'], $taxe->active ? 1 : 0]) }}'"
                                                    class="toggle-switch-input" {{ $taxe->active ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <a class="btn btn-white mr-1"
                                                href="{{ route('admin.taxe.edit', [$taxe['id']]) }}">
                                                <span class="tio-edit"></span>
                                            </a>
                                            <!--<a class="btn btn-white mr-1" href="javascript:"-->
                                            <!--    onclick="form_alert('category-{{ $taxe['id'] }}','Want to delete this tax?')">-->
                                            <!--    <span class="tio-delete"></span>-->
                                            <!--</a>-->
                                            <!--<form action="{{ route('admin.taxe.delete', [$taxe['id']]) }}"-->
                                            <!--    method="post" id="category-{{ $taxe['id'] }}">-->
                                            <!--    @csrf @method('delete')-->
                                            <!--</form>-->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <hr>
                        <table>
                            <tfoot>
                                {!! $taxes->links() !!}
                            </tfoot>
                        </table>
                        @if (count($taxes) == 0)
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

@push('script_2')
    <script src={{ asset('public/assets/admin/js/global.js') }}></script>
@endpush
