@extends('layouts.admin.app')

@section('title',\App\CPU\translate('admin_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
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
            <li class="breadcrumb-item">
                <a href="{{route('admin.admin.list')}}" class="text-primary">
                    {{ \App\CPU\translate('قائمة الأدمن') }}
                </a>
            </li>
                        <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                {{ \App\CPU\translate('إضافة ادمن جديد') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>
        <!-- Page Header -->
       
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
             
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table  table-nowrap table-align-middle card-table">
                            <thead >
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th>{{\App\CPU\translate('الاسم')}}</th>
                                <th>{{\App\CPU\translate('البريد الألكتروني')}}</th>
                                <th>{{\App\CPU\translate('الفرع')}}</th>
                                <th>{{\App\CPU\translate('الدور')}}</th>
                                <th>{{\App\CPU\translate('إجراءات')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($admins as $key=>$admin)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        {{ $admin->f_name . ' ' . $admin->l_name }}
                                    </td>
                                    <td>
                                        {{ $admin->email }}
                                    </td>
                                      <td>
                                        {{ $admin->branch->name??'' }}
                                    </td>   <td>
                                        {{ $admin->roles->name??'' }}
                                    </td>
                                    <td>
                                        {{-- <a class="btn btn-white mr-1" href="{{route('admin.admin.view',[$admin['id']])}}"><span class="tio-visible"></span></a> --}}
                                        <a class="btn btn-white mr-1"
                                            href="{{route('admin.admin.edit',[$admin['id']])}}">
                                            <span class="tio-edit"></span>
                                        </a>
                                        <!--<a class="btn btn-white mr-1" href="javascript:"-->
                                        <!--    onclick="form_alert('admin-{{$admin['id']}}','Want to delete this admin?')"><span class="tio-delete"></span>-->
                                        <!--</a>-->
                                        <!--<form action="{{route('admin.admin.delete',[$admin['id']])}}"-->
                                        <!--        method="post" id="admin-{{$admin['id']}}">-->
                                        <!--    @csrf @method('delete')-->
                                        <!--</form>-->
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="page-area">
                            <table>
                                <tfoot class="border-top">
                                {!! $admins->links() !!}
                                </tfoot>
                            </table>
                        </div>
                        @if(count($admins)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-cl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها')}}</p>
                            </div>
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
    <script src={{asset("public/assets/admin/js/global.js")}}></script>
@endpush
