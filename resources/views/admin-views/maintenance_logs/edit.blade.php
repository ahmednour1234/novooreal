@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تعديل سجل الصيانة'))

@push('css_or_js')
    <style>
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #001B63;
            text-align: center;
            margin-bottom: 1.5rem;
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
                    <a href="{{ route('admin.maintenance_logs.index') }}" class="text-secondary">
                        {{ \App\CPU\translate('تعديل صيانة الأصل') }}
                    </a>
                </li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.maintenance_logs.update', $maintenance->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label required">{{ \App\CPU\translate('تاريخ الصيانة') }}</label>
                        <input type="date" name="maintenance_date" class="form-control" value="{{ $maintenance->maintenance_date }}" required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label required">{{ \App\CPU\translate('نوع الصيانة') }}</label>
                        <select name="maintenance_type" class="form-control" required>
                            <option value="">{{ \App\CPU\translate('اختر النوع') }}</option>
                            <option value="preventive" {{ $maintenance->maintenance_type == 'preventive' ? 'selected' : '' }}>{{ \App\CPU\translate('وقائية') }}</option>
                            <option value="emergency" {{ $maintenance->maintenance_type == 'emergency' ? 'selected' : '' }}>{{ \App\CPU\translate('طارئة') }}</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">{{ \App\CPU\translate('التكلفة التقديرية للصيانة') }}</label>
                        <input type="number" step="0.01" name="estimated_cost" class="form-control" value="{{ $maintenance->estimated_cost }}" placeholder="{{ \App\CPU\translate('أدخل التكلفة التقديرية') }}">
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label required">{{ \App\CPU\translate('الحالة') }}</label>
                        <select name="status" class="form-control" required>
                            <option value="scheduled" {{ $maintenance->status == 'scheduled' ? 'selected' : '' }}>{{ \App\CPU\translate('مجدولة') }}</option>
                            <option value="in progress" {{ $maintenance->status == 'in progress' ? 'selected' : '' }}>{{ \App\CPU\translate('جاري التنفيذ') }}</option>
                            <option value="completed" {{ $maintenance->status == 'completed' ? 'selected' : '' }}>{{ \App\CPU\translate('مكتملة') }}</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ \App\CPU\translate('الملاحظات / التفاصيل') }}</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="{{ \App\CPU\translate('أدخل الملاحظات') }}">{{ $maintenance->notes }}</textarea>
                </div>

               <div class="d-flex justify-content-end mt-4">
    <button type="submit" class="btn btn-primary">
        {{ \App\CPU\translate('تحديث') }}
    </button>
    <a href="{{ route('admin.maintenance_logs.index') }}" class="btn btn-danger ml-2">
        {{ \App\CPU\translate('إلغاء') }}
    </a>
</div>

            </form>
        </div>
    </div>
</div>
@endsection
