@extends('layouts.admin.app')

@section('title',\App\CPU\translate('Result Visitors'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="row align-items-center mb-3">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize"><i
                        class="tio-filter-list"></i> {{\App\CPU\translate('قائمة نتائج الزيارات')}}
                    <span class="badge badge-soft-dark ml-2">{{$visitors->total()}}</span>
                </h1>
            </div>
        </div>

        <div class="card-header">
<form action="{{ route('admin.visitor.showResultVisitors', ['seller_id' => $seller_id]) }}" method="GET">
    <div class="row">
        <!-- Customer ID -->
        <div class="col-md-3">
            <label for="customer_id">{{ __('Search by Customer') }}</label>
            <select name="customer_id" id="customer_id" class="form-control">
                <option value="">{{ __('Select Customer') }}</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request()->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>
             <div class="col-md-3">
            <label for="region_id">{{ __('اختار منطقة') }}</label>
            <select name="region_id" id="region_id" class="form-control">
                <option value="">{{ __('اختار') }}</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ request()->region_id == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- From Date -->
        <div class="col-md-3">
            <label for="from_date">{{ __('From Date') }}</label>
            <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request()->from_date }}">
        </div>

        <!-- To Date -->
        <div class="col-md-3">
            <label for="to_date">{{ __('To Date') }}</label>
            <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request()->to_date }}">
        </div>

        <!-- Submit Button -->
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
        </div>
    </div>
</form>
        </div>

        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th>{{\App\CPU\translate('اسم العميل')}}</th>
                                <th>{{\App\CPU\translate('العميل الذي زار')}}</th>
                                                                <th>{{\App\CPU\translate('المنطقة')}}</th>
                                <th>{{ \App\CPU\translate('تاريخ الزيارة') }}</th>
                                <th>{{ \App\CPU\translate('ملاحظة') }}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($visitors as $key=>$visitor)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $visitor->customer->name }}</td>
                                    
                                    <td>{{ $visitor->customer->address }}/{{ $visitor->customer->mobile }}</td>
                                                                        <td>{{ $visitor->customer->regions->name }}</td>
                                    <td>{{ $visitor->created_at }}</td>
                                    <td>{{ $visitor->note }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="page-area">
                            <table>
                                <tfoot class="border-top">
                                    {!! $visitors->appends(request()->query())->links() !!}
                                </tfoot>
                            </table>
                        </div>

                        @if(count($visitors)==0)
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
