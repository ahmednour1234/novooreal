@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تعديل مندوب'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .page-header-title {
            color: #333;
            font-weight: 700;
        }
        .page-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="row align-items-center mb-3">
        <div class="col-sm">
            <h1 class="page-header-title d-flex align-items-center text-capitalize">
                <i class="tio-edit"></i> {{ \App\CPU\translate('تعديل مندوب') }}
            </h1>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-12 mb-lg-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.seller.update', $seller->id) }}" method="post" id="product_form" enctype="multipart/form-data">
                        @csrf

                        <!-- الاسم الأول والاسم الأخير -->
                        <div class="row pl-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الاسم الأول') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="text" name="f_name" class="form-control" 
                                           value="{{ old('f_name', $seller->f_name) }}"
                                           placeholder="{{ \App\CPU\translate('الاسم الأول') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الاسم الأخير') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="text" name="l_name" class="form-control" 
                                           value="{{ old('l_name', $seller->l_name) }}"
                                           placeholder="{{ \App\CPU\translate('الاسم الأخير') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- البريد الإلكتروني وكلمة المرور -->
                        <div class="row pl-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('البريد الإلكتروني') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="email" name="email" class="form-control" 
                                           value="{{ old('email', $seller->email) }}"
                                           placeholder="{{ \App\CPU\translate('ex@example.com') }}" required>
                                </div>
                            </div>
                            <!-- يمكن ترك حقل كلمة المرور فارغاً إذا لم يرغب المستخدم في تغييره -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('كلمة المرور') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="text" name="password" class="form-control" 
                                           value="{{ old('password') }}"
                                           placeholder="{{ \App\CPU\translate('كلمة المرور الجديدة') }}">
                                    <small class="form-text text-muted">
                                        {{ \App\CPU\translate('اتركه فارغاً إذا لم ترغب في تغييره') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- المناطق والفئات -->
                        <div class="row pl-2 mb-3">
                            <!-- المناطق -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('المناطق') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select name="regions[]" class="form-control" multiple required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر المناطق') }} --</option>
                                        @foreach($regions as $reg)
                                            <option value="{{ $reg->id }}" 
                                                {{ in_array($reg->id, old('regions', $seller->regions()->pluck('region_id')->toArray() ?? [])) ? 'selected':'' }}>
                                                {{ $reg->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- الفئات -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الفئات') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select name="cats[]" class="form-control" multiple required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر الفئات') }} --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ in_array($cat->id, old('cats', $seller->cats()->pluck('cat_id')->toArray() ?? [])) ? 'selected':'' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- العملاء والمركبة -->
                        <div class="row pl-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('العملاء') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select name="customers[]" class="form-control" multiple required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر العملاء') }} --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ in_array($customer->id, old('customers', $seller->customers()->pluck('customer_id')->toArray() ?? [])) ? 'selected':'' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- اسم المركبة -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('اسم المركبة') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select name="vehicle_code" class="form-control" required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر كود المركبة') }} --</option>
                                        @foreach($vehicles as $item)
                                            <option value="{{ $item->store_id }}"
                                                {{ old('vehicle_code', $seller->vehicle_code) == $item->store_id ? 'selected':'' }}>
                                                {{ $item->store_name1 }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- كود المندوب والراتب -->
                        <div class="row pl-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('كود المندوب') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="text" name="mandob_code" class="form-control" 
                                           value="{{ old('mandob_code', $seller->mandob_code) }}"
                                           placeholder="{{ \App\CPU\translate('كود المندوب') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الراتب') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="number" name="salary" class="form-control" 
                                           value="{{ old('salary', $seller->salary) }}"
                                           placeholder="{{ \App\CPU\translate('الراتب') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- نسبة المبيعات والإجازات -->
                        <div class="row pl-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('نسبة المبيعات %') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="number" name="precent_of_sales" class="form-control" 
                                           value="{{ old('precent_of_sales', $seller->precent_of_sales) }}"
                                           placeholder="{{ \App\CPU\translate('نسبة المبيعات %') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الإجازات') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <input type="number" name="holidays" class="form-control" 
                                           value="{{ old('holidays', $seller->holidays) }}"
                                           placeholder="{{ \App\CPU\translate('الإجازات') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- النوع -->
                        <div class="row pl-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('النوع') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select name="type" class="form-control" required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر النوع') }} --</option>
                                        <option value="credit" {{ old('type', $seller->type) == "credit" ? 'selected':'' }}>قسط</option>
                                        <option value="cash" {{ old('type', $seller->type) == "cash" ? 'selected':'' }}>كاش</option>
                                        <option value="full" {{ old('type', $seller->type) == "full" ? 'selected':'' }}>كلاهما</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- الفرع والشيفت -->
                        <div class="row pl-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('الفرع') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select name="branch_id" class="form-control" required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر الفرع') }} --</option>
                                        @foreach($branches as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('branch_id', $seller->branch_id) == $item->id ? 'selected':'' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('اسم الشيفت') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                  @php
    $selectedShifts = old('shift_id', is_array($seller->shift_id) ? $seller->shift_id : json_decode($seller->shift_id, true));
@endphp
<select name="shift_id[]" class="form-control" multiple required>
    <option value="" hidden>-- {{ \App\CPU\translate('اختر شيفت') }} --</option>
    @foreach($shifts as $item)
        <option value="{{ $item->id }}"
            {{ in_array($item->id, $selectedShifts ?? []) ? 'selected' : '' }}>
            {{ $item->name }}
        </option>
    @endforeach
</select>

                                </div>
                            </div>
                        </div>

                        <!-- الصلاحيات -->
                        @php
                            $permissions = [
                                'dashboard' => 'لوحة التحكم',
                                'stock'     => 'حجز المخزون',
                                'store'     => 'إنشاء مبيعات',
                                'pos'       => 'إنشاء مرتجع',
                                'sales'     => 'تحويل مندوب',
                                'admin'     => 'إنشاء تحصيل',
                            ];
                        @endphp

                        <div class="row pl-2 mb-4">
                            @foreach ($permissions as $key => $label)
                                <div class="col-12 col-sm-4">
                                    <div class="form-group">
                                        <label class="input-label">{{ $label }}</label>
                                        <input type="checkbox" name="{{ $key }}" value="1"
                                            {{ old($key, $seller->$key ?? 0) == 1 ? 'checked':'' }}>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- زر الإرسال -->
                        <div class="row pl-2">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary col-12">
                                    {{ \App\CPU\translate('حفظ') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div><!-- End card-body -->
            </div><!-- End card -->
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
@endpush
