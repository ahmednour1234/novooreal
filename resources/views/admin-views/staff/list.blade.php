@extends('layouts.admin.app')

@section('title',\App\CPU\translate('seller_list'))

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
                    {{ \App\CPU\translate('قائمة الموظفين') }}
                </a>
            </li>
                  
           
        </ol>
    </nav>
</div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-12 col-sm-7 col-md-6 col-lg-4 col-xl-6 mb-3 mb-sm-0">
                                <form action="{{url()->current()}}" method="GET">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="{{\App\CPU\translate('بحث باسم الموظف')}}" aria-label="Search" value="{{ $search }}"  required>
                                        <button type="submit" class="btn btn-primary">{{\App\CPU\translate('بحث')}} </button>

                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                         
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th>{{\App\CPU\translate('الاسم')}}</th>
                                <th>{{\App\CPU\translate('الايميل')}}</th>
                                <th>{{ \App\CPU\translate('الراتب') }}</th>
                                <th>{{ \App\CPU\translate('التقييم') }}</th>
                                <th class="text-center">{{ \App\CPU\translate('دائن') }}</th>
                               <th class="text-center">{{ \App\CPU\translate('مدين') }}</th>
                             
                                <th>{{\App\CPU\translate('إجراءات')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($sellers as $key=>$seller)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        {{ $seller->f_name . ' ' . $seller->l_name }}
                                    </td>
                                    <td>
                                        {{ $seller->email }}
                                    </td>
                                   
                                        <td>
                                        {{ $seller->salary }}
                                    </td>  
                                     
                                    <td>
                                        {{ $seller->score }}%
                                    </td>
                                       <td class="text-center p-5">
                        @if ($seller->id != 0)
                            <div class="row">
                                <!--  <div class="col-5">-->
                                <!--    <a class="btn btn-primary p-1" id="{{ $seller->seller_id }}" onclick="update_seller_balance_cl({{ $seller->seller_id }})" data-toggle="modal" data-target="#update-seller-balance">-->
                                <!--        <i class="tio-add-circle"></i>-->
                                <!--        {{ \App\CPU\translate('') }}-->
                                <!--    </a>-->
                                <!--</div>-->
                                <div class="col-5">
                                    {{ number_format($seller->balance,2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
                                </div>
                              
                            </div>
                        @else
                            <div class="row">
                                <div class="col-6">
                                    {{ \App\CPU\translate('هذا العميل غير مدائن لنا بشئ') }}
                                </div>
                            </div>
                        @endif
                    </td>
                    <td class="text-center p-5">
                        @if ($seller->id != 0)
                            <div class="row">
                                <!--    <div class="col-5">-->
                                <!--    <a class="btn btn-primary p-1" id="{{ $seller->seller_id }}" onclick="update_seller_credit_cl({{ $seller->seller_id }})" data-toggle="modal" data-target="#update-seller-credit">-->
                                <!--        <i class="tio-add-circle"></i>-->
                                <!--        {{ \App\CPU\translate('') }}-->
                                <!--    </a>-->
                                <!--</div>-->
                                <div class="col-5">
                                    {{ number_format($seller->credit,2) . ' ' . \App\CPU\Helpers::currency_symbol() }}
                                </div>
                            
                            </div>
                        @else
                            <div class="row">
                                <div class="col-6">
                                    {{ \App\CPU\translate('هذا العميل غير مدين لنا بشئ') }}
                                </div>
                            </div>
                        @endif
                    </td>
                    <!-- <td class="text-center p-5">-->
                            <!--<div class="row">-->
                            <!--      <div class="col-5">-->
                            <!--        <a class="btn btn-primary p-1" id="{{ $seller->seller_id }}" onclick="update_seller_loan_cl({{ $seller->seller_id }})" data-toggle="modal" data-target="#update-seller-loan">-->
                            <!--            <i class="tio-add-circle"></i>-->
                            <!--            {{ \App\CPU\translate('') }}-->
                            <!--        </a>-->
                            <!--    </div>-->
                                <!--<div class="col-5">-->
                                <!--    {{ number_format($seller->loan,2) . ' ' . \App\CPU\Helpers::currency_symbol() }}-->
                                <!--</div>-->
                              
                    <!--        </div>-->
                    
                    <!--</td>-->
                                 <td>
    <!-- Existing Buttons for Prices, Edit, and Delete -->
    <a class="btn btn-white mr-1" href="{{ route('admin.staff.edit', [$seller['seller_id']]) }}">
        <span class="tio-edit"></span>
    </a>
    <!--<a class="btn btn-white mr-1" href="javascript:" onclick="form_alert('seller-{{ $seller['seller_id'] }}','Want to delete this seller?')">-->
    <!--    <span class="tio-delete"></span>-->
    <!--</a>-->


    <!-- Delete Form -->
    <!--<form action="{{ route('admin.staff.delete', [$seller['seller_id']]) }}" method="post" id="seller-{{ $seller['seller_id'] }}">-->
    <!--    @csrf-->
    <!--    @method('delete')-->
    <!--</form>-->
</td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="page-area">
                            <table>
                                <tfoot class="border-top">
                                {!! $sellers->links() !!}
                                </tfoot>
                            </table>
                        </div>
                        @if(count($sellers)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-cl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
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
