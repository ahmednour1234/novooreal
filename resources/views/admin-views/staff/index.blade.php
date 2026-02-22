@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_seller'))

@push('css_or_js')
    <!-- رابط لملف CSS مخصص (يمكنك تعديل المسار حسب الحاجة) -->

@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
       
        <li class="breadcrumb-item active">{{ \App\CPU\translate('إضافة موظف') }}</li>
      </ol>
    </nav>
  </div>
    <!-- End Page Header -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.staff.store') }}" method="post" id="product_form" enctype="multipart/form-data">
                        @csrf
                        <!-- بيانات الموظف الأساسية -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الاسم الاول') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="f_name" class="form-control" value="{{ old('f_name') }}" placeholder="{{ \App\CPU\translate('first_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الاسم الاخير') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="l_name" class="form-control" value="{{ old('l_name') }}" placeholder="{{ \App\CPU\translate('last_name') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('البريد الالكتروني') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{ \App\CPU\translate('Ex_:_ex@example.com') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('كود المندوب') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="mandob_code" class="form-control" value="{{ old('mandob_code') }}" placeholder="{{ \App\CPU\translate('A15') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('كلمة المرور') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="password" class="form-control" value="{{ old('password') }}" placeholder="{{ \App\CPU\translate('password') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('المرتب الشهري') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="salary" class="form-control" value="{{ old('salary') }}" placeholder="{{ \App\CPU\translate('salary') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الاجازات') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="holidays" class="form-control" value="{{ old('holidays') }}" placeholder="{{ \App\CPU\translate('holidays') }}">
                                </div>
                            </div>
                        </div>
                
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الفرع') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="branch_id" class="form-control" required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر فرع') }} --</option>
                                        @foreach($branches as $item)
                                            <option value="{{ $item->id }}" @if(old('branch_id') == $item->id) selected @endif>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('اسم الشيفت') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                       <select name="shift_id[]" class="form-control" multiple required>
    <option value="" hidden>-- {{ \App\CPU\translate('اختر شيفت') }} --</option>
    @foreach($shifts as $item)
        <option value="{{ $item->id }}"
            {{ in_array($item->id, old('shift_id', is_array($selectedShifts ?? null) ? $selectedShifts : json_decode($seller->shift_id ?? '[]', true))) ? 'selected' : '' }}>
            {{ $item->name }}
        </option>
    @endforeach
</select>
                                </div>
                            </div>
                        </div>

                        <!-- زر عرض/إخفاء البيانات الإضافية -->
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-info toggle-button" onclick="toggleAdditionalData()">
                                    {{ \App\CPU\translate('إظهار/إخفاء البيانات الإضافية') }}
                                </button>
                            </div>
                        </div>

                        <!-- قسم البيانات الإضافية -->
                        <div class="additional-data" id="additionalData">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="input-label">
                                            {{ \App\CPU\translate('رقم الهاتف') }}
                                        </label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="{{ \App\CPU\translate('phone') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="input-label">
                                            {{ \App\CPU\translate('القسم') }}
                                        </label>
                                        <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="{{ \App\CPU\translate('department') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="input-label">
                                            {{ \App\CPU\translate('المسمى الوظيفي') }}
                                        </label>
                                        <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}" placeholder="{{ \App\CPU\translate('job_title') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="input-label">
                                            {{ \App\CPU\translate('تاريخ التعيين') }}
                                        </label>
                                        <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="form-group">
                                        <label class="input-label">
                                            {{ \App\CPU\translate('المؤهلات') }}
                                        </label>
                                        <textarea name="qualifications" class="form-control" rows="3" placeholder="{{ \App\CPU\translate('المؤهلات') }}">{{ old('qualifications') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="form-group">
                                        <label class="input-label">
                                            {{ \App\CPU\translate('تفاصيل العقد') }}
                                        </label>
                                        <textarea name="contract_details" class="form-control" rows="3" placeholder="{{ \App\CPU\translate('تفاصيل العقد') }}">{{ old('contract_details') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- نهاية قسم البيانات الإضافية -->

<div class="mt-4 d-flex justify-content-end">
  <button type="submit" class="btn btn-primary col-3">
    {{ \App\CPU\translate('حفظ') }}
  </button>
</div>

                    </form>
                </div> <!-- card-body -->
            </div> <!-- card -->
        </div> <!-- col-12 -->
    </div> <!-- row -->
</div> <!-- container-fluid -->
@endsection

    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
    <script>
        function toggleAdditionalData() {
            var additionalData = document.getElementById('additionalData');
            if (additionalData.style.display === 'none' || additionalData.style.display === '') {
                additionalData.style.display = 'block';
            } else {
                additionalData.style.display = 'none';
            }
        }
    </script>
