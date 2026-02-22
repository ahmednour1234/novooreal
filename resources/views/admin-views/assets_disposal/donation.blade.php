@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تسجيل عملية الإهداء/التخلص'))

@push('css_or_js')
    <style>
        .form-title {
            font-size: 2rem;
            text-align: center;
            color: #001B63;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <h1 class="form-title">{{ \App\CPU\translate('تسجيل عملية الإهداء/التخلص') }}</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.disposal.donation.store', $asset->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ \App\CPU\translate('اسم الأصل') }}</label>
                    <input type="text" class="form-control" value="{{ $asset->asset_name }} ({{ $asset->code }})" disabled>
                </div>
                <div class="form-group">
                    <label>{{ \App\CPU\translate('تاريخ العملية') }}</label>
                    <input type="date" name="disposal_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>{{ \App\CPU\translate('ملاحظات') }}</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="{{ \App\CPU\translate('أدخل الملاحظات') }}"></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('حفظ') }}</button>
                    <a href="{{ route('admin.maintenance_logs.index') }}" class="btn btn-secondary">{{ \App\CPU\translate('إلغاء') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
