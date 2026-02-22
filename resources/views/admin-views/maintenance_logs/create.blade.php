@extends('layouts.admin.app')

@section('title', \App\CPU\translate('إضافة سجل صيانة جديد'))

@push('css_or_js')
    <style>
        .form-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #001B63;
            margin-bottom: 1rem;
            text-align: center;
        }
        .required:after {
            content: " *";
            color: red;
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
                <li class="breadcrumb-item active text-primary" aria-current="page">
                    {{ \App\CPU\translate('إضافة صيانة أصل') }}
                </li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.maintenance_logs.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">{{ \App\CPU\translate('اختر الأصل') }}</label>
                        <select name="asset_id" class="form-control" required>
                            <option value="">{{ \App\CPU\translate('اختر الأصل') }}</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->asset_name }} ({{ $asset->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">{{ \App\CPU\translate('تاريخ الصيانة') }}</label>
                        <input type="date" name="maintenance_date" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">{{ \App\CPU\translate('نوع الصيانة') }}</label>
                        <select name="maintenance_type" class="form-control" required>
                            <option value="">{{ \App\CPU\translate('اختر النوع') }}</option>
                            <option value="preventive">{{ \App\CPU\translate('وقائية') }}</option>
                            <option value="emergency">{{ \App\CPU\translate('طارئة') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ \App\CPU\translate('التكلفة التقديرية للصيانة') }}</label>
                        <input type="number" step="0.01" name="estimated_cost" class="form-control" placeholder="{{ \App\CPU\translate('أدخل التكلفة التقديرية') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ \App\CPU\translate('الملاحظات / التفاصيل') }}</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="{{ \App\CPU\translate('أدخل الملاحظات') }}"></textarea>
                </div>

                <div class="d-flex justify-content-end" style="gap: 1rem;">
                    <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('حفظ') }}</button>
                    <a href="{{ route('admin.maintenance_logs.index') }}" class="btn btn-danger">{{ \App\CPU\translate('إلغاء') }}</a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
