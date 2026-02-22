@extends('layouts.admin.app')

@section('title', \App\CPU\translate('إغلاق الأصل - إهلاك تام'))

@push('css_or_js')
    <style>
        .form-title {
            font-size: 2.5rem;
            text-align: center;
            color: #001B63;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .card-body {
            padding: 2rem;
        }
        .form-group label {
            font-weight: 600;
            color: #001B63;
        }
        .required::after {
            content: '*';
            color: red;
            margin-left: 3px;
        }
        .info-box {
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-top: 2rem;
        }
        .form-label.required::after {
        content: " *";
        color: red;
    }

    .info-box {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-top: 25px;
    }

    .info-box p {
        margin-bottom: 8px;
        font-size: 16px;
        color: #343a40;
    }

    .section-title {
        font-size: 18px;
        font-weight: bold;
        color: #000;
        margin-top: 30px;
        margin-bottom: 15px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
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
                <a href="{{ route('admin.depreciation.index') }}" class="text-primary">
                    {{ \App\CPU\translate(' الأصول الثابتة') }}
                </a>
            </li>
                <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate('إهلاك تام للأصل') }} : {{ $asset->asset_name }}
                </a>
            </li>
           
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.disposal.complete.store', $asset->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 📌 بيانات الأصل --}}
        <div class="row">
    {{-- 🏷️ اسم الأصل --}}
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ \App\CPU\translate('اسم الأصل') }}</label>
        <input type="text" class="form-control" value="{{ $asset->asset_name }} ({{ $asset->code }})" disabled>
    </div>

    {{-- 📅 تاريخ الإغلاق --}}
    <div class="col-md-6 mb-3">
        <label class="form-label required">{{ \App\CPU\translate('تاريخ الإغلاق') }}</label>
        <input type="date" name="closure_date" class="form-control" required>
    </div>
</div>


            {{-- 📝 ملاحظات --}}
            <div class="mb-3">
                <label class="form-label">{{ \App\CPU\translate('ملاحظات') }}</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="{{ \App\CPU\translate('أدخل الملاحظات') }}"></textarea>
            </div>

            {{-- 🧾 بيانات سند صرف الاهلاك --}}
            <div class="section-title">{{ \App\CPU\translate('بيانات سند صرف الاهلاك') }}</div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">{{ \App\CPU\translate('الحساب المدين') }}</label>
                    <select name="account_id_to" class="form-control js-select2-custom" required>
                        <option value="">{{ \App\CPU\translate('اختار الحساب المحول له') }}</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account['id'] }}">{{ $account['account'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">{{ \App\CPU\translate('الحساب الدائن') }}</label>
                    <select name="account_id" class="form-control js-select2-custom" required>
                        <option value="">{{ \App\CPU\translate('اختار الحساب') }}</option>
                        @foreach ($accounts_to as $account)
                            <option value="{{ $account->id }}">{{ $account->account }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 🏢 مركز التكلفة --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ \App\CPU\translate('مركز التكلفة') }}</label>
                    <select name="cost_id" class="form-control js-select2-custom">
                        <option value="">{{ \App\CPU\translate('اختر مركز التكلفة') }}</option>
                        @foreach ($costcenters as $costcenter)
                            <option value="{{ $costcenter['id'] }}">{{ $costcenter['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                   <div class="col-md-6 mb-3">
                    <label class="form-label">{{ \App\CPU\translate('تحميل صورة الإيصال') }}</label>
                    <input type="file" name="voucher_img" class="form-control" accept="image/*">
                </div>
            </div>
  {{-- 📊 معلومات إضافية --}}
      
    
  <div class="info-box">
            <p>
                {{ \App\CPU\translate('قيمة الأصل الكاملة قبل الاهلاك:') }}
                <strong>{{ number_format($asset->total_cost, 2) }} {{ \App\CPU\translate('جنيه') }}</strong>
            </p>
            <p>
                {{ \App\CPU\translate('قيمة الاهلاك الكامل (المجموع حتى الإغلاق):') }}
                <strong>{{ number_format($asset->total_cost - $asset->book_value, 2) }} {{ \App\CPU\translate('جنيه') }}</strong>
            </p>
        </div>
            {{-- ✅ الأزرار --}}
            <div class="d-flex justify-content-end mt-4" style="gap: 10px;">
                <button type="submit" class="btn btn-primary" style="min-width: 120px;">
                    {{ \App\CPU\translate('حفظ') }}
                </button>
                <a href="{{ route('admin.maintenance_logs.index') }}" class="btn btn-danger" style="min-width: 120px;">
                    {{ \App\CPU\translate('إلغاء') }}
                </a>
            </div>
        </form>

      
    </div>
</div>

</div>
@endsection
