@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_account'))

@push('css_or_js')
<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        background: #ffffff;
        padding: 20px;
    }
    .input-label {
        font-weight: bold;
        color: #555;
    }
    .form-control {
        border-radius: 8px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
    }
    .btn-primary {
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        font-size: 1rem;
        font-weight: bold;
    }
    .btn-primary:hover {
        background-color: #0056b3;
    }
    .page-header-title {
        font-size: 1.8rem;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    .page-header-title i {
        margin-right: 10px;
        color: #007bff;
    }
</style>
@endpush

@section('content')
@php
    // حساب عدد الحسابات الفرعية الحالية للحساب الأب
    $childCount = \App\Models\Account::where('parent_id', $account->id)->count();
    // الرقم التسلسلي الجديد: إذا ما فيش حسابات فرعية يكون 2، وإلا يكون childCount + 1
    $nextSequence = ($childCount == 0 ? 2 : $childCount + 1);
    // توليد رقم الحساب باستخدام كود الحساب الأب مع "0" والرقم التسلسلي
    $generatedAccountNumber = $account->code . '0' . $nextSequence;
@endphp

<div class="content container-fluid">
    <div class="mb-4 text-center">
        <h1 class="page-header-title">
            <i class="tio-add-circle-outlined"></i>
            <span>{{ \App\CPU\translate('اضافة دليل حساب ' . $account->account) }}-{{$account->account_number}}</span>
        </h1>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.account.store') }}" method="post">
                        @csrf
                        <input type="hidden" name="storage_id" value="{{ $account->storage_id }}">
                        <input type="hidden" name="parent_id" value="{{ $account->id }}">

                        <div class="mb-3">
                            <label class="input-label">{{ \App\CPU\translate('عنوان الحساب') }}</label>
                            <!-- هنا لا علاقة له بتوليد رقم الحساب -->
                            <input type="text" id="account" name="account" class="form-control" value="{{ old('account') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="input-label">{{ \App\CPU\translate('وصف الحساب') }}</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                        </div>
                        
                        <div class="row mb-3">
                     
                            <div class="col-md-6">
                                <label class="input-label">{{ \App\CPU\translate('رقم الحساب') }}</label>
                                <!-- يتم تعبئة الحقل بالقيمة المولدة من السيرفر، ويمكن للمستخدم تعديلها إذا رغب -->
                                <input type="text" name="account_number" id="account_number" class="form-control" 
                                    value="{{ old('account_number', $generatedAccountNumber) }}" 
                                    data-base-code="{{ $account->code }}" 
                                    data-next-sequence="{{ $nextSequence }}" 
                                    required>
                            </div>
                        </div>
                                                        <input type="hidden" step="0.01" min="0" name="balance" class="form-control" value="0" >

                        <div class="row mb-3">
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
                        </div>
                        
                        <input class="form-check-input" type="hidden" name="account_type" value="{{ $account->account_type }}">

                        <button type="submit" class="btn btn-primary w-100">{{ \App\CPU\translate('حفظ') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // يمكن إضافة وظيفة لتجديد الرقم تلقائيًا عند طلب المستخدم، مثلاً بالضغط على زر "توليد تلقائي"
    function generateAccountNumber() {
        let baseCode = document.getElementById('account_number').getAttribute('data-base-code');
        let nextSequence = document.getElementById('account_number').getAttribute('data-next-sequence');
        return baseCode + '0' + nextSequence;
    }

    // عند تحميل الصفحة، نملأ حقل account_number بالقيمة المولدة من السيرفر
    document.addEventListener('DOMContentLoaded', function() {
        let accountNumberInput = document.getElementById('account_number');
        accountNumberInput.value = generateAccountNumber();
    });

    // يمكنك إضافة حدث لإعادة توليد الرقم تلقائياً إذا احتجت، مع العلم أن الحقل يظل قابل للتعديل.
    // مثال: عند الضغط على زر توليد
    // document.getElementById('regenBtn').addEventListener('click', function() {
    //     document.getElementById('account_number').value = generateAccountNumber();
    // });
</script>
@endpush
