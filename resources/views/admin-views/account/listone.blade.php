@extends('layouts.admin.app')

@section('title', \App\CPU\translate('account_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-header-title text-capitalize d-flex align-items-center">
                <i class="tio-filter-list mr-2"></i>
                <span>{{ \App\CPU\translate('داخل حساب ') }} <strong class="text-primary">{{ $account->name }}</strong></span>
                <span class="badge badge-soft-dark ml-2">{{ $accounts->total() }}</span>
            </h1>
            <a href="{{ route('admin.account.addone', [$account->id]) }}" class="btn btn-primary">
                <i class="tio-add-circle"></i> {{ \App\CPU\translate('إضافة حساب جديد فرعي') }}
            </a>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <form action="{{ url()->current() }}" method="GET" class="w-100">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="tio-search"></i></span>
                        </div>
                        <input type="search" name="search" class="form-control" placeholder="{{ \App\CPU\translate('بحث باسم الحساب') }}" value="{{ $search }}" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('بحث') }}</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ \App\CPU\translate('معلومات الحساب') }}</th>
                            <th>{{ \App\CPU\translate('معلومات الميزانية') }}</th>
                            <th>{{ \App\CPU\translate('نوع الحساب') }}</th>
                            <th class="text-center">{{ \App\CPU\translate('إجراءات') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $key => $account)
                            <tr>
                                <td>{{ $account->code  }}</td>
                                <td>
                                    <strong>{{ $account->account }}</strong><br>
                                    <small class="text-muted">{{ $account->account_number }}</small><br>
                                    <small class="text-muted">{{ $account->description }}</small>
                                </td>
                                <td>
                                    <strong>{{ \App\CPU\translate('الإجمالي') }}:</strong> {{ $account->balance }} {{ \App\CPU\Helpers::currency_symbol() }}<br>
                                    <strong>{{ \App\CPU\translate('إيرادات الحساب') }}:</strong> {{ $account->total_in ?? 0 }} {{ \App\CPU\Helpers::currency_symbol() }}<br>
                                    <strong>{{ \App\CPU\translate('مصروفات الحساب') }}:</strong> {{ $account->total_out ?? 0 }} {{ \App\CPU\Helpers::currency_symbol() }}
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
                                    <a href="{{ route('admin.account.edit', $account->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="tio-edit"></i> {{ \App\CPU\translate('تعديل') }}
                                    </a>
                                    <a href="{{ route('admin.account.listone', $account->id) }}" class="btn btn-outline-info btn-sm">
                                        <i class="tio-visible"></i> {{ \App\CPU\translate('رؤية') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {!! $accounts->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
@endpush
