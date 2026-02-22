@extends('layouts.admin.app')

@section('title', 'إضافة قائمة مواد جديدة')

@push('css_or_js')
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <style>
        .form-card {
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .form-card .card-header {
            background: #001B63;
            color: #ffff;
            font-size: 1.25rem;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: none;
        }
        .form-card .card-body {
            padding: 1.5rem;
        }
        .select2-container--default .select2-selection--single {
            border-radius: 0.5rem;
            height: calc(1.5em + 1rem + 2px);
            padding: .375rem 1rem;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid" dir="rtl">
    <div class="card form-card">
        <div class="card-header">
            إضافة قائمة مواد جديدة
        </div>
        <div class="card-body">
            <form action="{{ route('admin.boms.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <!-- Product -->
                    <div class="col-md-6">
                        <label class="form-label">المنتج</label>
                        <select name="product_id" class="form-select select2-single" required>
                            <option value="" disabled selected>اختر المنتج</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->product_code }} – {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Version -->
                    <div class="col-md-6">
                        <label class="form-label">الإصدار</label>
                        <input type="text"
                               name="version"
                               class="form-control"
                               value="{{ old('version', 'v1') }}"
                               placeholder="مثال: v1"
                               required>
                        @error('version')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">الوصف</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  placeholder="أضف ملاحظات أو تعليمات إن وجدت">{{ old('description') }}</textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-success">
                            حفظ
                        </button>
                        <a href="{{ route('admin.boms.index') }}" class="btn btn-secondary">
                            إلغاء
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

    <!-- jQuery (required for Select2) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-single').select2({
                placeholder: 'اختر',
                width: '100%'
            });
        });
    </script>
