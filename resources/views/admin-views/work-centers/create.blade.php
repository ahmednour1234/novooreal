{{-- resources/views/admin-views/work-centers/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'إضافة مركز عمل جديد')

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
            background: #001B63;
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
            background: #001B63;
            color: #fff;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .btn-submit:hover {
            background: #1e7fa8;
        }
        .btn-cancel {
            background: #f1f3f5;
            color: #333;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .btn-cancel:hover {
            background: #e2e6ea;
        }
        .select2-container--default .select2-selection--single {
            border-radius: 0.5rem;
            height: calc(1.5em + 1rem + 2px);
            padding: 0.375rem 1rem;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid" dir="rtl">
    <div class="card form-card">
        <div class="card-header">إضافة مركز عمل</div>
        <div class="card-body">
            <form action="{{ route('admin.work-centers.store') }}" method="POST">
                @csrf
                <div class="row gx-4 gy-3">
                    <div class="col-md-6">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-select select2-single" required>
                            <option value="" disabled selected>اختر الفرع</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">اسم المركز</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               placeholder="مثلاً: مركز القطع CNC"
                               required>
                        @error('name')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">الوصف</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  placeholder="أضف ملاحظات أو تعليمات إن وجدت...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">ساعات العمل اليومية</label>
                        <input type="number"
                               name="capacity_per_day"
                               class="form-control"
                               step="0.01"
                               value="{{ old('capacity_per_day') }}"
                               placeholder="مثلاً: 8">
                        @error('capacity_per_day')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">تكلفة التشغيل لكل ساعة</label>
                        <div class="input-group">
                            <input type="number"
                                   name="cost_per_hour"
                                   class="form-control"
                                   step="0.01"
                                   value="{{ old('cost_per_hour') }}"
                                   placeholder="مثلاً: 120.00">
   <span class="input-group-text">
            {{ \App\Models\BusinessSetting::where('key','currency')->first()->value }}
        </span>                        </div>
                        @error('cost_per_hour')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-submit">حفظ</button>
                        <a href="{{ route('admin.work-centers.index') }}" class="btn btn-cancel">إلغاء</a>
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
            $('.select2-single').select2({
                placeholder: 'اختر',
                width: '100%'
            });
        });
    </script>
