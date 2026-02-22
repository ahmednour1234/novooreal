@extends('layouts.admin.app')

@section('title',\App\CPU\translate('seller_list'))

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
                        class="tio-filter-list"></i> {{\App\CPU\translate('seller_list')}}
                    <span class="badge badge-soft-dark ml-2">{{$sellers->total()}}</span>
                </h1>
            </div>
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
                                               placeholder="{{\App\CPU\translate('بحث باسم المندوب')}}" aria-label="Search" value="{{ $search }}"  required>
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
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th>{{\App\CPU\translate('الاسم')}}</th>
                                <th>{{\App\CPU\translate('الايميل')}}</th>
                                <th>{{ \App\CPU\translate('كود المندوب') }}</th>
                                <th>{{ \App\CPU\translate('كود العربة') }}</th>
                                <th>{{ \App\CPU\translate('الراتب') }}</th>
                                <th>{{ \App\CPU\translate('النسبة علي المبيعات') }}</th>
                                <th>{{ \App\CPU\translate('عدد الزيارات التي قام بها هذا الشهر') }}</th>
                                <th>{{ \App\CPU\translate(' عدد الزيارات التي من المفترض القيام بها') }}</th>
                                <th>{{ \App\CPU\translate('التقييم') }}</th>
                                <th>{{\App\CPU\translate('الاجراءات')}}</th>
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
                                        {{ $seller->mandob_code }}
                                    </td>
                                    <td>
                                        {{ optional(\App\Models\Store::where('store_id', $seller->vehicle_code)->first())->store_code }}
                                    </td>
                                        <td>
                                        {{ $seller->salary }}
                                    </td>    <td>
                                        {{ $seller->precent_of_sales }}
                                    </td>
                                      <td>
                                        {{ $seller->result_visitors }}
                                    </td>
                                    <td>
                                        {{ $seller->visitors }}
                                    </td>
                                    <td>
                                        {{ $seller->score }}%
                                    </td>
                    <!--                   <td class="text-center p-5">-->
                    <!--    @if ($seller->id != 0)-->
                    <!--        <div class="row">-->
                    <!--                 <div class="col-5">-->
                    <!--                <a class="btn btn-primary p-1" id="{{ $seller->seller_id }}" onclick="update_seller_balance_cl({{ $seller->seller_id }})" data-toggle="modal" data-target="#update-seller-balance">-->
                    <!--                    <i class="tio-add-circle"></i>-->
                    <!--                    {{ \App\CPU\translate('') }}-->
                    <!--                </a>-->
                    <!--            </div>-->
                    <!--            <div class="col-5">-->
                    <!--                {{ $seller->balance . ' ' . \App\CPU\Helpers::currency_symbol() }}-->
                    <!--            </div>-->
                           
                    <!--        </div>-->
                    <!--    @else-->
                    <!--        <div class="row">-->
                    <!--            <div class="col-6">-->
                    <!--                {{ \App\CPU\translate('هذا العميل غير مدائن لنا بشئ') }}-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--    @endif-->
                    <!--</td>-->
                    <!--<td class="text-center p-5">-->
                    <!--    @if ($seller->id != 0)-->
                    <!--        <div class="row">-->
                    <!--               <div class="col-5">-->
                    <!--                <a class="btn btn-primary p-1" id="{{ $seller->seller_id }}" onclick="update_seller_credit_cl({{ $seller->seller_id }})" data-toggle="modal" data-target="#update-seller-credit">-->
                    <!--                    <i class="tio-add-circle"></i>-->
                    <!--                    {{ \App\CPU\translate('') }}-->
                    <!--                </a>-->
                    <!--            </div>-->
                    <!--            <div class="col-5">-->
                    <!--                {{ $seller->credit . ' ' . \App\CPU\Helpers::currency_symbol() }}-->
                    <!--            </div>-->
                             
                    <!--        </div>-->
                    <!--    @else-->
                    <!--        <div class="row">-->
                    <!--            <div class="col-6">-->
                    <!--                {{ \App\CPU\translate('هذا العميل غير مدين لنا بشئ') }}-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--    @endif-->
                    <!--</td>-->
                    <!-- <td class="text-center p-5">-->
                    <!--        <div class="row">-->
                    <!--            <div class="col-5">-->
                    <!--                <a class="btn btn-primary p-1" id="{{ $seller->seller_id }}" onclick="update_seller_loan_cl({{ $seller->seller_id }})" data-toggle="modal" data-target="#update-seller-loan">-->
                    <!--                    <i class="tio-add-circle"></i>-->
                    <!--                    {{ \App\CPU\translate('') }}-->
                    <!--                </a>-->
                    <!--            </div>-->
                    <!--            <div class="col-5">-->
                    <!--                {{ $seller->loan . ' ' . \App\CPU\Helpers::currency_symbol() }}-->
                    <!--            </div>-->
                                
                    <!--        </div>-->
                    
                    <!--</td>-->
                                 <td>
    <!-- Existing Buttons for Prices, Edit, and Delete -->
    <a class="btn btn-white mr-1" href="{{ route('admin.seller.prices', [$seller['seller_id']]) }}"><span class="tio-money"></span></a>
    <a class="btn btn-white mr-1" href="{{ route('admin.seller.edit', [$seller['seller_id']]) }}">
        <span class="tio-edit"></span>
    </a>
    <!--<a class="btn btn-white mr-1" href="javascript:" onclick="form_alert('seller-{{ $seller['seller_id'] }}','Want to delete this seller?')">-->
    <!--    <span class="tio-delete"></span>-->
    <!--</a>-->
    
    <!-- Show Button for Viewing Visitors -->
    <a class="btn btn-white mr-1" href="{{ route('admin.visitor.showResultVisitors', [$seller['seller_id']]) }}">
        <span class="tio-visible"></span> <!-- This icon is for viewing -->
    </a>

    <!-- Delete Form -->
    <!--<form action="{{ route('admin.seller.delete', [$seller['seller_id']]) }}" method="post" id="seller-{{ $seller['seller_id'] }}">-->
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
<div class="modal fade" id="update-seller-balance" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ \App\CPU\translate('اضافة استلام نقدية') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.seller.update-balance') }}" method="post" class="row" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="seller_id" name="seller_id">

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('استلام نقدية') }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="amount" required>
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('الحساب الذي ستضاف له سيتم دفع منه المديونية') }}</label>
                        <select id="account_id" name="account_id" class="form-control js-select2-custom" required>
                            <option value="">---{{ \App\CPU\translate('اختار') }}---</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account['id'] }}" data-balance="{{ $account['balance'] }}">{{ $account['account'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('وصف') }}</label>
                        <input type="text" name="description" class="form-control" placeholder="{{ \App\CPU\translate('description') }}">
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('التاريخ') }}</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
    <div class="form-group">
        <label class="input-label" for="img">{{ \App\CPU\translate('تحميل صورة') }}</label>
        <input type="file" name="img" id="img" class="form-control" required>
                   </div>
                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('رصيد الحساب') }}</label>
                        <p id="account_balance">0</p>
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{ \App\CPU\translate('حفظ') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update-seller-credit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ \App\CPU\translate('اضافة دفع نقدية') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.seller.update-credit') }}" method="post" class="row" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('دفع نقدية') }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="amount" required>
                    </div>
                    
<input type="hidden" id="seller_credit_id" name="seller_id">

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('الحساب الذي ستضاف له سيتم دفع اليه المبلغ ') }}</label>
                        <select id="account_id" name="account_id" class="form-control js-select2-custom" required>
                            <option value="">---{{ \App\CPU\translate('اختار') }}---</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account['id'] }}" data-balance="{{ $account['balance'] }}">{{ $account['account'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('وصف') }}</label>
                        <input type="text" name="description" class="form-control" placeholder="{{ \App\CPU\translate('description') }}">
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('التاريخ') }}</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
    <div class="form-group">
        <label class="input-label" for="img">{{ \App\CPU\translate('تحميل صورة') }}</label>
        <input type="file" name="img" id="img" class="form-control" required>
                   </div>
                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('رصيد الحساب') }}</label>
                        <p id="account_balance">0</p>
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{ \App\CPU\translate('حفظ') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update-seller-loan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ \App\CPU\translate('اضافة سلفة نقدية') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.seller.update-loan') }}" method="post" class="row" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('سلفة نقدية') }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="amount" required>
                    </div>
                    
<input type="hidden" id="seller_loan_id" name="seller_id">

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('الحساب الذي ستضاف له سيتم دفع منه المبلغ ') }}</label>
                        <select id="account_id" name="account_id" class="form-control js-select2-custom" required>
                            <option value="">---{{ \App\CPU\translate('اختار') }}---</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account['id'] }}" data-balance="{{ $account['balance'] }}">{{ $account['account'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('وصف') }}</label>
                        <input type="text" name="description" class="form-control" placeholder="{{ \App\CPU\translate('description') }}">
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('التاريخ') }}</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
    <div class="form-group">
        <label class="input-label" for="img">{{ \App\CPU\translate('تحميل صورة') }}</label>
        <input type="file" name="img" id="img" class="form-control" required>
                   </div>
                    <div class="form-group col-12 col-sm-6">
                        <label>{{ \App\CPU\translate('رصيد الحساب') }}</label>
                        <p id="account_balance">0</p>
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{ \App\CPU\translate('حفظ') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@push('script_2')
    <script src={{asset("public/assets/admin/js/global.js")}}></script>
        <!-- jQuery -->

<!-- Bootstrap JS -->

    <script>     
    function update_seller_balance_cl(sellerId) {
    document.getElementById('seller_id').value = sellerId; // For balance modal
}

function update_seller_credit_cl(sellerId) {
    document.getElementById('seller_credit_id').value = sellerId; // For credit modal
}
function update_seller_loan_cl(sellerId) {
    document.getElementById('seller_loan_id').value = sellerId; // For credit modal
}

    
    document.addEventListener('DOMContentLoaded', function () {
    const accountSelect = document.getElementById('account_id');
    const balanceDisplay = document.getElementById('account_balance');

    accountSelect.addEventListener('change', function () {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const balance = selectedOption.getAttribute('data-balance');
        balanceDisplay.textContent = balance ? balance : '0';
    });

    // Initialize the balance display for the default selected option
    if (accountSelect.value) {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const balance = selectedOption.getAttribute('data-balance');
        balanceDisplay.textContent = balance ? balance : '0';
    }
});

</script>
@endpush
