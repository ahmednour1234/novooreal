@extends('layouts.admin.app')

@section('title', \App\CPU\translate('seller_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
@endpush

@section('content')
<style>
    /* Custom form styles */
.custom-select,
.custom-input {
    border-radius: 5px;
    padding: 10px;
    font-size: 14px;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    transition: all 0.3s ease;
}

.custom-select:focus,
.custom-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Submit Button Styles */
.custom-button {
    padding: 8px 20px;
    font-size: 14px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.custom-button:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Input fields and select alignment */
.d-flex input {
    margin-right: 10px;
}

.input-group-text {
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
}

.input-group-merge {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px;
}

/* Search icon styling */
.tio-search {
    color: #007bff;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .custom-button {
        width: 100%;
        margin-top: 10px;
    }
    .d-flex input {
        margin-right: 5px;
    }
}

</style>
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="row align-items-center mb-3">
        <div class="col-sm mb-2 mb-sm-0">
            <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                <i class="tio-filter-list"></i> 
                    {{ \App\CPU\translate('قائمة التحويلات من المناديب') }}
                <span class="badge badge-soft-dark ml-2">{{ $transactions->total() }}</span>
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
            <!-- Search Form -->
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group input-group-merge input-group-flush">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>

                    <!-- Seller Dropdown -->
                    <select name="seller_id" class="form-control custom-select">
                        <option value="">{{ \App\CPU\translate('اختار البائع') }}</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->f_name }} {{ $seller->l_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Date Range Filters -->
                    <div class="d-flex">
                        <input 
                            type="date" 
                            name="start_date" 
                            class="form-control mr-2 custom-input" 
                            value="{{ request('start_date') }}" 
                            placeholder="{{ \App\CPU\translate('من تاريخ') }}"
                        >
                        <input 
                            type="date" 
                            name="end_date" 
                            class="form-control custom-input" 
                            value="{{ request('end_date') }}" 
                            placeholder="{{ \App\CPU\translate('إلى تاريخ') }} "
                        >
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary custom-button ml-2">
                        {{ \App\CPU\translate('بحث') }}
                    </button>
                </div>
            </form>
            <!-- End Search Form -->
        </div>
    </div>
</div>

                <!-- End Header -->

                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ \App\CPU\translate('الاسم') }}</th>
                                <th>{{ \App\CPU\translate('الايميل') }}</th>
                                <th>{{ \App\CPU\translate('المديونية') }}</th>
                                <th>{{ \App\CPU\translate('الحساب') }}</th>
                                <th>{{ \App\CPU\translate('الملاحظة') }}</th>
                                <th>{{ \App\CPU\translate('المبلغ') }}</th>
                                <th>{{ \App\CPU\translate('الصورة') }}</th>
                                <th>{{ \App\CPU\translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $key => $transaction)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                      <td>{{ $transaction->sellers->f_name ?? ''}}</td>
<td>{{ $transaction->sellers->email ?? '' }}</td>
<td>{{ $transaction->sellers->credit ?? '0' }}</td>
<td>{{ $transaction->accounts->account ?? 'N/A' }}</td>

                                <td>{{ $transaction->note }}</td>
                                <td>{{ $transaction->amount }}</td>
<td>
    <img src="{{ asset('storage/app/public/' . $transaction->img) }}" alt="Transaction Image" width="100" height="100">
</td>
                                <td>
                                  @if($transaction->active == 0)
    <form action="{{ route('admin.TransactionSeller.status', $transaction->id) }}" method="POST" id="statusForm-{{ $transaction->id }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="active" value="1">
        <button type="submit" class="btn btn-primary btn-sm" id="submitButton-{{ $transaction->id }}" onclick="disableButton('{{ $transaction->id }}')">
            <span class="button-text">{{ \App\CPU\translate('موافقة علي التحويل') }}</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </form>
@else

                                        <span class="text-success">{{ \App\CPU\translate('لقد تمت الموافقة عل التحويل') }}</span>
                                    @endif
                                </td>
                         
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                   <div class="page-area">
    <table>
        <tfoot class="border-top">
            {!! $transactions->appends([
                'search' => request('search'),
                'seller_id' => request('seller_id'),
                'start_date' => request('start_date'),
                'end_date' => request('end_date'),
            ])->links() !!}
        </tfoot>
    </table>
</div>


                    <!-- No Data Found -->
                    @if($transactions->isEmpty())
                        <div class="text-center p-4">
                            <img class="mb-3 w-one-cl" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="{{ \App\CPU\translate('Image Description') }}">
                            <p class="mb-0">{{ \App\CPU\translate('لاتوجد تجويلات لعرضها') }}</p>
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
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
    <script>
        function disableButton(transactionId) {
    const button = document.getElementById(`submitButton-${transactionId}`);
    const form = document.getElementById(`statusForm-${transactionId}`);
    
    // Disable the button and show the spinner
    button.disabled = true;
    button.querySelector('.button-text').classList.add('d-none'); // Hide button text
    button.querySelector('.spinner-border').classList.remove('d-none'); // Show spinner
    
    // Allow form submission
    form.submit();
}

    </script>
@endpush
