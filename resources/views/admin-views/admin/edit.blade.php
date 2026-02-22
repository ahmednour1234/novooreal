@extends('layouts.admin.app')

@section('title', \App\CPU\translate('update_admin'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
    <style>
        .form-group label {
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-radius: 5px;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
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
                {{ \App\CPU\translate('تحديث ادمن') }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>
    <!-- End Page Header -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('admin.admin.update', [$admin->id]) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ \App\CPU\translate('الاسم الاول') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="f_name" class="form-control" value="{{ $admin->f_name }}" placeholder="{{ \App\CPU\translate('first_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ \App\CPU\translate('الاسم الاخير') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="l_name" class="form-control" value="{{ $admin->l_name }}" placeholder="{{ \App\CPU\translate('last_name') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ \App\CPU\translate('البريد الالكتروني') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ $admin->email }}" placeholder="{{ \App\CPU\translate('Ex_:_ex@example.com') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ \App\CPU\translate('كلمة المرور') }}</label>
                                    <input type="password" name="password" class="form-control" placeholder="{{ \App\CPU\translate('password') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
           
                                       <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ \App\CPU\translate('الفرع') }} <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-control" required>
                                        <option value="" hidden>-- اختار الفرع --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" @if($admin->branch_id == $branch->id) selected @endif>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ \App\CPU\translate('الدور') }} <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-control" required>
                                        <option value="" hidden>-- اختار الدور --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" @if($admin->role_id == $role->id) selected @endif>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                                  <div class="col-md-6">
                            @php
    use App\Models\AdminSeller;
    use App\Models\Seller;

    // المناديب المرتبطين بـ admin الحالي
    $selectedSellerIds = AdminSeller::where('admin_id', $admin->id)->pluck('seller_id')->toArray();

    // جميع المناديب المتاحين للاختيار
    $sellers = Seller::all();
@endphp

<div class="form-group">
    <label>{{ \App\CPU\translate('المناديب') }} <span class="text-danger">*</span></label>
    <select name="sellers[]" class="form-control select2" multiple required>
        <option value="" hidden>-- اختار المندوب --</option>
        @foreach($sellers as $cat)
            <option value="{{ $cat->id }}" {{ in_array($cat->id, $selectedSellerIds) ? 'selected' : '' }}>
                {{ $cat->email }}
            </option>
        @endforeach
    </select>
</div>

                            </div>
                        </div>
<div class="d-flex justify-content-end mt-5">
    <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
        {{ \App\CPU\translate('تحديث') }}
    </button>
</div>                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
<!-- CSS -->
<!-- في أعلى الصفحة أو الـ layout -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "اختر مناديب",
            width: '100%',
            dir: 'rtl',
            allowClear: true
        });
    });
</script>

