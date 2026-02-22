@extends('layouts.admin.app')

@section('title',\App\CPU\translate('add_new_account'))

@push('css_or_js')
<style>
    label.required:after {
        content: " *";
        color: red;
        font-weight: bold;
    }

 

</style>
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
                <a href="{{ route('admin.account.add') }}" class="text-primary">
                    {{ \App\CPU\translate('إضافة دليل محاسبي') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>

    <div class="row gx-2 gx-lg-3">
        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <div class="card p-4">
                <div class="card-body">
<form action="{{ route('admin.account.store') }}" method="post">
    @csrf
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="input-label required">نوع الحساب</label>
            <select name="account_type" class="form-control select2" required>
                <option value="" disabled selected>اختر نوع الحساب</option>
                <option value="asset">الأصول</option>
                <option value="liability">الالتزامات</option>
                <option value="equity">حقوق الملكية</option>
                <option value="revenue">الإيرادات</option>
                <option value="expense">المصروفات</option>
                <option value="other">أخرى</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="input-label required">{{ \App\CPU\translate('عنوان الحساب') }}</label>
            <input type="text" name="account" class="form-control" value="{{ old('account') }}" required>
        </div>

        <div class="col-md-4">
            <label class="input-label">{{ \App\CPU\translate('وصف الحساب') }}</label>
            <input type="text" name="description" class="form-control" value="{{ old('description') }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="input-label required">{{ \App\CPU\translate('رقم الحساب') }}</label>
            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" required>
        </div>
    </div>

    <input type="hidden" name="balance" value="0">
<div class="row mt-4">
    <div class="col-12 d-flex justify-content-end">
        <button type="submit"
                class="btn btn-primary px-9"
                onclick="disableButton(event)">
            <span class="button-text">{{ \App\CPU\translate('حفظ') }}</span>
            <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
        </button>
    </div>
</div>

</form>                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'اختر الخزنة',
            allowClear: true
        });
    });
</script>
