@extends('layouts.admin.app')

@section('title', \App\CPU\translate('update_supplier'))

@push('css_or_js')
<style>
    :root{
        --bg:#f7f8fb; --card:#ffffff; --ink:#0f172a; --muted:#6b7280; --line:#e5e7eb;
        --brand:#2563eb; --shadow:0 8px 24px rgba(2,6,23,.06); --radius:14px;
    }
    body{ background:var(--bg); }
    .breadcrumb{ border:1px solid var(--line); }
    .card{ border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); }
    .card-body{ padding:18px; }
    .input-label{ font-weight:700; color:var(--ink); margin-bottom:6px; }
    .form-control{ border:1px solid var(--line); border-radius:10px; height:44px; }
    .form-group{ margin-bottom:14px; }
    .custom-file-input ~ .custom-file-label{ border:1px solid var(--line); border-radius:10px; }
    .img-one-su{ width:160px; height:160px; object-fit:cover; border:1px solid var(--line); border-radius:12px; }
    .btn-submit{ padding:.45rem .9rem; font-weight:700; }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    {{-- Breadcrumb --}}
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                        <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" class="text-primary">{{ \App\CPU\translate('تحديث مورد') }}</a>
                </li>
            </ol>
        </nav>
    </div>

    <div class="row gx-2 gx-lg-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('admin.supplier.update', [$supplier->id]) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="row pl-2">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('اسم المورد') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ $supplier->name }}"
                                           placeholder="{{ \App\CPU\translate('supplier_name') }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('رقم الهاتف') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="mobile" name="mobile" class="form-control"
                                           value="{{ $supplier->mobile }}"
                                           placeholder="{{ \App\CPU\translate('mobile_no') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row pl-2">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('رقم الضريبي') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="tax_number" class="form-control"
                                           value="{{ $supplier->tax_number }}"
                                           placeholder="{{ \App\CPU\translate('tax_number') }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('السجل التجاري') }}</label>
                                    <input type="text" name="c_history" class="form-control"
                                           value="{{ $supplier->c_history }}"
                                           placeholder="{{ \App\CPU\translate('c_history') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row pl-2">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('البريد الألكتروني') }}</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ $supplier->email }}"
                                           placeholder="{{ \App\CPU\translate('Ex_:_ex@example.com') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('المقاطعة') }}</label>
                                    <input type="text" name="state" class="form-control"
                                           value="{{ $supplier->state }}"
                                           placeholder="{{ \App\CPU\translate('state') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row pl-2">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('المدينة') }}</label>
                                    <input type="text" name="city" class="form-control"
                                           value="{{ $supplier->city }}"
                                           placeholder="{{ \App\CPU\translate('city') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('كود المدينة') }}</label>
                                    <input type="text" name="zip_code" class="form-control"
                                           value="{{ $supplier->zip_code }}"
                                           placeholder="{{ \App\CPU\translate('zip_code') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row pl-2">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ \App\CPU\translate('العنوان') }}</label>
                                    <input type="text" name="address" class="form-control"
                                           value="{{ $supplier->address }}"
                                           placeholder="{{ \App\CPU\translate('address') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="input-label d-block">
                                    {{ \App\CPU\translate('الصورة') }}
                                    <small class="text-danger">({{ \App\CPU\translate('ratio_1:1') }}) ({{ \App\CPU\translate('optional') }})</small>
                                </label>

                                <div class="custom-file">
                                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                           accept=".jpg,.png,.jpeg,.gif,.bmp,.tif,.tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg1">
                                        {{ \App\CPU\translate('اختار صورة') }}
                                    </label>
                                </div>

                                <div class="form-group my-3 mb-0">
                                    <center>
                                        <img class="img-one-su" id="viewer"
                                             onerror="this.src='{{ asset('public/assets/admin/img/400x400/img2.jpg') }}'"
                                             src="{{ asset('storage/app/public/supplier/'.$supplier['image']) }}"
                                             alt="{{ \App\CPU\translate('image') }}">
                                    </center>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-submit col-3">
                                {{ \App\CPU\translate('تحديث') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
<script>
    // label text + preview
    document.getElementById('customFileEg1').addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        if (file) {
            const label = this.nextElementSibling;
            if (label && label.classList.contains('custom-file-label')) {
                label.textContent = file.name;
            }
            document.getElementById('viewer').src = URL.createObjectURL(file);
        }
    });
</script>
@endpush
