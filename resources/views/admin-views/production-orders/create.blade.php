{{-- resources/views/admin-views/production-orders/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'إنشاء أمر إنتاج جديد')

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
        font-size: 1.3rem;
        font-weight: 700;
        padding: 1rem 1.5rem;
        border-bottom: none;
    }
    .form-card .card-body {
        padding: 2rem;
    }
    .form-label {
        font-weight: 600;
        margin-bottom: .5rem;
    }
    .form-control, .form-select {
        border-radius: .5rem;
        padding: .75rem 1rem;
    }
    .btn-submit {
        background: #161853;
        color: #fff;
        border-radius: .5rem;
        padding: .75rem 2rem;
        font-weight: 600;
    }
    .btn-cancel {
        background: #f1f3f5;
        color: #333;
        border-radius: .5rem;
        padding: .75rem 2rem;
        margin-left: 1rem;
        font-weight: 600;
    }
    .select2-container--default .select2-selection--single {
        border-radius: .5rem !important;
        height: calc(1.5em + 1rem + 2px) !important;
        padding: .375rem 1rem !important;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid" dir="rtl">
    <div class="card form-card">
        <div class="card-header">إنشاء أمر إنتاج جديد</div>
        <div class="card-body">
            <form action="{{ route('admin.production-orders.store') }}" method="POST">
                @csrf
                <div class="row gx-4 gy-4">
                    {{-- اختيار الوصفة فقط --}}
                    <div class="col-md-6">
                        <label class="form-label">الوصفة (BOM) <span class="text-danger">*</span></label>
                        <select name="bom_id" class="form-select select2-single" required>
                            <option value="" disabled selected>اختر الوصفة</option>
                            @foreach($boms as $b)
                                <option value="{{ $b->id }}" {{ old('bom_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->product->product_code }} – {{ $b->product->name }} ({{ $b->version }})
                                </option>
                            @endforeach
                        </select>
                        @error('bom_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- باقي الحقول --}}
                    <div class="col-md-6">
                        <label class="form-label">المسار (Routing)</label>
                        <select name="routing_id" class="form-select select2-single" required>
                            <option value="" disabled selected>اختر المسار</option>
                            @foreach($routings as $r)
                                <option value="{{ $r->id }}" {{ old('routing_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('routing_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-select select2-single" required>
                            <option value="" disabled selected>اختر الفرع</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity" class="form-control" step="0.01"
                               value="{{ old('quantity') }}" placeholder="مثلاً: 100.00" required>
                        @error('quantity')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">وحدة القياس</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="unit" id="unit0" value="0"
                                       {{ old('unit',0) == 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="unit0">صغرى</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="unit" id="unit1" value="1"
                                       {{ old('unit') == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="unit1">كبرى</label>
                            </div>
                        </div>
                        @error('unit')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">تاريخ البدء</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                        @error('start_date')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">تاريخ الانتهاء</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                        @error('end_date')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-submit">حفظ الأمر</button>
                        <a href="{{ route('admin.production-orders.index') }}" class="btn btn-cancel">إلغاء</a>
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
