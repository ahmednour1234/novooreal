@extends('layouts.admin.app')

@section('title',\App\CPU\translate('update_account'))

@push('css_or_js')
<style>
    .form-control {
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #764ba2, #667eea);
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4 d-flex align-items-center">
        <h1 class="page-header-title d-flex align-items-center text-capitalize">
            <i class="tio-edit mr-2"></i>
            {{\App\CPU\translate('تعديل الحساب')}}
        </h1>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card p-4">
                <form action="{{ route('admin.account.update', [$account->id]) }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label>{{ \App\CPU\translate('عنوان الحساب') }}</label>
                        <input type="text" name="account" class="form-control" value="{{ $account->account }}" required>
                    </div>
                    <div class="form-group">
                        <label>{{ \App\CPU\translate('وصف الحساب') }}</label>
                        <input type="text" name="description" class="form-control" value="{{ $account->description }}" required>
                    </div>
                    <div class="form-group">
                        <label>{{ \App\CPU\translate('رقم الحساب') }}</label>
                        <input type="text" name="account_number" class="form-control" value="{{ $account->account_number }}" required>
                    </div>
               @php
    // جذور الحسابات المسموح لها
    $roots = [8, 14];

    // نتحقق إن كان الحساب نفسه أو أحد أجداده في القائمة
    $showTypeToggle = in_array($account->id, $roots)
                   || collect($roots)->contains(fn($id) => $account->isDescendantOf($id));
@endphp

@if($showTypeToggle)
    <div class="form-group">
        <label>
            <input 
                type="radio" 
                name="type" 
                value="0" 
                {{ old('type', $account->type) == 0 ? 'checked' : '' }}
            > 
            {{ \App\CPU\translate('يظهر للمندوب') }}
        </label>
        <label class="ml-3">
            <input 
                type="radio" 
                name="type" 
                value="1" 
                {{ old('type', $account->type) == 1 ? 'checked' : '' }}
            > 
            {{ \App\CPU\translate('لا يظهر للمندوب') }}
        </label>
    </div>
@endif
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2">{{ \App\CPU\translate('تعديل') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
@endpush
