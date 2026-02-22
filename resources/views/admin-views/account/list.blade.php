@extends('layouts.admin.app')

@section('title',\App\CPU\translate('account_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="mb-3">
            <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                <i class="tio-filter-list"></i>
                <span>{{\App\CPU\translate('أرصدة الحسابات')}} <span
                        class="badge badge-soft-dark ml-2">{{$accounts->total()}}</span></span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-10 mb-1 mb-md-0 col-sm-7 col-md-6">
                                <form action="{{url()->current()}}" method="GET">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="{{\App\CPU\translate('بحث باسم الحساب')}}"
                                               value="{{ $search }}" required>
                                        <button type="submit"
                                                class="btn btn-primary">{{\App\CPU\translate('بحث')}} </button>

                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                            <div class="col-12 col-sm-5  col-md-4">
                                <a href="{{route('admin.account.add')}}" class="btn btn-primary float-right"><i
                                        class="tio-add-circle"></i> {{\App\CPU\translate('إضافة حساب جديد')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
            <table class="table table-hover table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{ \App\CPU\translate('معلومات الحساب') }}</th>
                        <th>{{ \App\CPU\translate('معلومات الميزانية') }}</th>
                        <th>{{ \App\CPU\translate('نوع الحساب') }}</th>
                        <th class="text-center">{{ \App\CPU\translate('الاجراءات') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accounts as $key => $account)
                    <tr>
                        <td>{{ $account->code  }}</td>
                        <td>
                            <strong>{{ $account->account }}</strong><br>
                            <span class="text-muted">{{ $account->account_number }}</span><br>
                            <span class="text-muted">{{ $account->description }}</span>
                        </td>
                        
                        <td>
                            <div>
                                <strong>{{ \App\CPU\translate('الاجمالي') }}:</strong> {{ $account->balance . ' ' . \App\CPU\Helpers::currency_symbol() }}<br>
                                <strong>{{ \App\CPU\translate('الإيرادات الحساب') }}:</strong> {{ $account->total_in ?? 0 }} {{ \App\CPU\Helpers::currency_symbol() }}<br>
                                <strong>{{ \App\CPU\translate('مصروفات الحساب') }}:</strong> {{ $account->total_out ?? 0 }} {{ \App\CPU\Helpers::currency_symbol() }}
                            </div>
                        </td>
                               <td>
<strong>
    @switch($account->account_type)
        @case('asset')
            أصل
            @break
        @case('liability')
            التزام
            @break
        @case('equity')
            حقوق ملكية
            @break
        @case('revenue')
            إيراد
            @break
        @case('expense')
            مصروف
            @break
        @default
            غير معروف
    @endswitch
</strong><br>
                            </td>
                        <td class="text-center">
                            <a href="{{ route('admin.account.edit', $account->id) }}" class="btn btn-sm btn-outline-primary mr-1"><i class="tio-edit"></i> {{ \App\CPU\translate('تعديل') }}</a>
                            <a href="{{ route('admin.account.listone', $account->id) }}" class="btn btn-sm btn-outline-primary mr-1"><i class="tio-visible"></i> {{ \App\CPU\translate('رؤوية') }}</a>

                            <!--<form action="{{ route('admin.account.delete', $account->id) }}" method="post" class="d-inline" id="delete-form-{{ $account->id }}">-->
                            <!--    @csrf-->
                            <!--    @method('delete')-->
                            <!--    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{\App\CPU\translate('هل انت متأكد؟')}}')">-->
                            <!--        <i class="tio-delete"></i> {{ \App\CPU\translate('حذف') }}-->
                            <!--    </button>-->
                            <!--</form>-->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

                        <div class="page-area">
                            <table>
                                <tfoot class="border-top">
                                {!! $accounts->links() !!}
                                </tfoot>
                            </table>
                        </div>
                        @if(count($accounts)==0)
                            @include('layouts.admin.partials._no-data-section')
                        @endif
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

@endsection

@push('script_2')

@endpush
