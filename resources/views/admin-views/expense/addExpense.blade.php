@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_expense'))

@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
  <style>
    /* Enhanced form design */
    .card {
      border: none;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      border-radius: 8px;
      overflow: hidden;
    }
    .card-header {
      background-color: #001B63;
      color: #fff;
      font-size: 1.25rem;
      font-weight: 600;
      padding: 1rem 1.5rem;
    }
    .card-body {
      padding: 1.5rem;
      background-color: #fff;
    }
    .form-section {
      margin-bottom: 1.5rem;
    }
    .form-section .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      border-bottom: 2px solid #001B63;
      padding-bottom: 0.5rem;
    }
    label {
      font-weight: 500;
    }
    .required::after {
      content: "*";
      color: red;
      margin-left: 0.25rem;
    }
    .form-control {
      border-radius: 4px;
      box-shadow: none;
      border-color: #ced4da;
    }
    .btn-primary {
      background-color: #001B63;
      border-color: #001B63;
    }
    .spinner-border {
      vertical-align: middle;
    }
  </style>
@endpush

@section('content')
<div class="content container-fluid">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex align-items-center">
        <i class="tio-add-circle-outlined" style="font-size: 2rem; color: #001B63;"></i>
        <h1 class="ml-2 mb-0">{{ \App\CPU\translate('اضافة بند مصروف جديد') }}</h1>
      </div>
    </div>
  </div>
  <!-- End Page Header -->

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          {{ \App\CPU\translate('بيانات المصروف') }}
        </div>
        <div class="card-body">
          <form action="{{ route('admin.account.storeExpense') }}" method="post" enctype="multipart/form-data">
            @csrf

            <!-- Section: الحسابات -->
            <!--<div class="form-section">-->
            <!--  <div class="section-title">{{ \App\CPU\translate('الحسابات') }}</div>-->
            <!--  <div class="row">-->
            <!--    <div class="col-md-6 mb-3">-->
            <!--      <label for="accountSelect" class="required">{{ \App\CPU\translate('من الحساب') }}</label>-->
            <!--      <select id="accountSelect" name="account_id" class="form-control js-select2-custom" required>-->
            <!--        <option value="">--- {{ \App\CPU\translate('اختار الحساب') }} ---</option>-->
            <!--        @foreach ($accounts as $account)-->
            <!--          <option value="{{ $account->id }}">{{ $account->account }}</option>-->
            <!--        @endforeach-->
            <!--      </select>-->
            <!--    </div>-->
            <!--  </div>-->
            <!--</div>-->

            <!-- Section: الوصف والمبلغ -->
            <div class="form-section">
              <div class="section-title">{{ \App\CPU\translate('الوصف ') }}</div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="required">{{ \App\CPU\translate('الوصف') }}</label>
                  <input type="text" name="description" class="form-control" placeholder="{{ \App\CPU\translate('description') }}" required>
                </div>
             
              </div>
            </div>

            <!-- Section: التاريخ وصورة الإيصال -->
            <!--<div class="form-section">-->
            <!--  <div class="section-title">{{ \App\CPU\translate('التاريخ وصورة الإيصال') }}</div>-->
            <!--  <div class="row">-->
            <!--    <div class="col-md-6 mb-3">-->
            <!--      <label class="required">{{ \App\CPU\translate('التاريخ') }}</label>-->
            <!--      <input type="date" name="date" class="form-control" required>-->
            <!--    </div>-->
            <!--    <div class="col-md-6 mb-3">-->
            <!--      <label>{{ \App\CPU\translate('تحميل صورة الإيصال') }}</label>-->
            <!--      <input type="file" name="img" id="img" class="form-control" accept="image/*">-->
            <!--    </div>-->
            <!--  </div>-->
            <!--</div>-->

            <!-- Hidden field for type -->

            <!-- Optional Section: Cost Center (not required) -->
            <!--<div class="form-section">-->
            <!--  <div class="section-title">{{ \App\CPU\translate('بيانات إضافية (اختياري)') }}</div>-->
            <!--  <div class="row">-->
            <!--    <div class="col-md-6 mb-3">-->
            <!--      <label>{{ \App\CPU\translate('مركز التكلفة') }}</label>-->
            <!--      <select name="cost_id" class="form-control js-select2-custom">-->
            <!--        <option value="">--- {{ \App\CPU\translate('اختار مركز التكلفة') }} ---</option>-->
            <!--        @foreach ($costcenters as $costcenter)-->
            <!--          <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>-->
            <!--        @endforeach-->
            <!--      </select>-->
            <!--    </div>-->
            <!--  </div>-->
            <!--</div>-->

            <!-- Section: Save Button -->
            <div class="row">
              <div class="col-12 text-center">
                <button id="save-button" type="submit" class="btn btn-primary shadow-sm" onclick="disableButton(event)">
                  <span class="button-text">{{ \App\CPU\translate('حفظ') }}</span>
                  <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // تعطيل زر الحفظ أثناء الإرسال
  function disableButton(event) {
    event.preventDefault();
    const button = document.getElementById('save-button');
    button.disabled = true;
    button.querySelector('.button-text').classList.add('d-none');
    button.querySelector('.spinner-border').classList.remove('d-none');
    button.closest('form').submit();
  }
</script>
@endpush
