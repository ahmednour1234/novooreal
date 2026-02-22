@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_admin'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
    <style>
    .tag-input {
        background-color: #f1f1f1;
        border-radius: 5px;
        padding: 6px 12px;
        margin: 3px;
        display: inline-block;
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
                <a href="{{route('admin.admin.list')}}" class="text-primary">
                    {{ \App\CPU\translate('قائمة الأدمن') }}
                </a>
            </li>
                        <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                {{ \App\CPU\translate('إضافة ادمن جديد') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>
    <!-- Page Header -->

    <!-- End Page Header -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.admin.store') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('الاسم الاول') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="f_name" class="form-control" value="{{ old('f_name') }}" placeholder="{{ \App\CPU\translate('first_name') }}" required>
                                </div>
                            </div>
                            
                            <!-- Last Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('الاسم الاخير') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="l_name" class="form-control" value="{{ old('l_name') }}" placeholder="{{ \App\CPU\translate('last_name') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('البريد الألكتروني') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{ \App\CPU\translate('Ex_:_ex@example.com') }}" required>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('كلمة المرور') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" placeholder="{{ \App\CPU\translate('password') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Select Sellers -->
     <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('الدور') }} <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-control select2" required>
                                        <option value="" hidden>-- اختار دور --</option>
                                        @foreach($roles as $cat)
                                            <option value="{{ $cat->id }}" {{ old('role_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>




                            <!-- Select Branch -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('الفرع') }} <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-control select2" required>
                                        <option value="" hidden>-- اختار فرع --</option>
                                        @foreach($branches as $cat)
                                            <option value="{{ $cat->id }}" {{ old('branch_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Select Role -->
                            <div class="col-md-6">
    <div class="form-group">
        <label class="input-label">
            {{ \App\CPU\translate('المناديب') }} <span class="text-danger">*</span>
        </label>
        <select name="sellers[]" id="sellers-select" class="form-control" multiple="multiple" required>
            @foreach($sellers as $cat)
                <option value="{{ $cat->id }}" {{ in_array($cat->id, old('sellers', [])) ? 'selected' : '' }}>
                    {{ $cat->email }}
                </option>
            @endforeach
        </select>
    </div>
</div>
                       
                        </div>


                 <!-- Submit Button -->
<!-- Submit Button -->
<!-- زر الحفظ في نهاية الصفحة وعلى اليمين -->
    <div class="d-flex justify-content-end mt-5">
    <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
        {{ \App\CPU\translate('حفظ') }}
    </button>
</div>



                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<!-- CSS -->
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#sellers-select').select2({
            placeholder: "اختر مناديب",
            width: '100%',
            dir: "rtl", // لدعم اللغة العربية من اليمين لليسار
            allowClear: true
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#sellers-select').select2({
            placeholder: "اختر المناديب",
            dir: "rtl", // يدعم اللغة العربية
            width: '100%'
        });
    });
</script>

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
  
@endpush
