{{-- resources/views/admin-views/routings/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'تعديل مسار')

@push('css_or_js')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <style>
        .form-card {
            border-radius: 1rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .form-card .card-header {
            background: #161853;
            color: #fff;
            font-size: 1.25rem;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        .form-card .card-body {
            padding: 1.75rem;
        }
        .form-label {
            font-weight: 600;
        }
        .btn-submit {
            background: #161853;
            color: #fff;
            border: none;
            padding: .6rem 1.5rem;
            border-radius: .5rem;
            font-weight: 600;
        }
        .btn-submit:hover {
            background: #1e7fa8;
        }
        .btn-cancel {
            background: #f1f3f5;
            color: #333;
            border: none;
            padding: .6rem 1.5rem;
            border-radius: .5rem;
            font-weight: 600;
            margin-left: .5rem;
        }
        .btn-cancel:hover {
            background: #e2e6ea;
        }
        .select2-container--default .select2-selection--single {
            border-radius: .5rem;
            height: calc(1.5em + 1rem + 2px);
            padding: .375rem 1rem;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid" dir="rtl">
    <div class="card form-card">
        <div class="card-header">تعديل مسار</div>
        <div class="card-body">
            <form action="{{ route('admin.routings.update', $routing->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row gx-4 gy-3">
                    <div class="col-md-6">
                        <label class="form-label">قائمة المواد (BOM)</label>
                        <select name="bom_id" class="form-select select2-single" required>
                            <option value="" disabled>اختر الوصفة</option>
                            @foreach($boms as $bom)
                                <option value="{{ $bom->id }}" {{ old('bom_id', $routing->bom_id)==$bom->id?'selected':'' }}>
                                    {{ $bom->product->sku }} – {{ $bom->product->name }} ({{ $bom->version }})
                                </option>
                            @endforeach
                        </select>
                        @error('bom_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">اسم المسار</label>
                        <input type="text" name="name"
                               class="form-control"
                               value="{{ old('name', $routing->name) }}"
                               placeholder="مثلاً: المسار القياسي"
                               required>
                        @error('name')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $routing->description) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">تاريخ التفعيل</label>
                        <input type="date" name="effective_date"
                               class="form-control"
                               value="{{ old('effective_date', optional($routing->effective_date)->format('Y-m-d')) }}">
                        @error('effective_date')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-submit">تحديث</button>
                        <a href="{{ route('admin.routings.index') }}" class="btn btn-cancel">إلغاء</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        $(function(){
            $('.select2-single').select2({ placeholder: 'اختر', width: '100%' });
        });
    </script>
