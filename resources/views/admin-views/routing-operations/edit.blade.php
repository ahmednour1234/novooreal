{{-- resources/views/admin-views/routing-operations/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'تعديل خطوة تشغيل')

@push('css_or_js')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<style>
    .form-card {
        border-radius: 1rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
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
        margin-bottom: .5rem;
    }
    .btn-submit {
        background: #161853;
        color: #fff;
        border: none;
        padding: .6rem 1.5rem;
        border-radius: .5rem;
        font-weight: 600;
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
        <div class="card-header">تعديل خطوة تشغيل</div>
        <div class="card-body">
            <form action="{{ route('admin.routing-operations.update', $operation->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row gx-4 gy-3">
                    <div class="col-md-6">
                        <label class="form-label">المسار</label>
                        <select name="routing_id" class="form-select select2-single" required>
                            @foreach($routings as $r)
                                <option value="{{ $r->id }}" {{ old('routing_id', $operation->routing_id)==$r->id?'selected':'' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('routing_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">مركز العمل</label>
                        <select name="work_center_id" class="form-select select2-single" required>
                            @foreach($workCenters as $wc)
                                <option value="{{ $wc->id }}" {{ old('work_center_id', $operation->work_center_id)==$wc->id?'selected':'' }}>
                                    {{ $wc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('work_center_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">التسلسل</label>
                        <input type="number" name="sequence" class="form-control" value="{{ old('sequence', $operation->sequence) }}" min="1" required>
                        @error('sequence')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">زمن الإعداد (ساعي)</label>
                        <input type="number" step="0.01" name="setup_time" class="form-control" value="{{ old('setup_time', $operation->setup_time) }}" required>
                        @error('setup_time')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">زمن التشغيل (ساعي)</label>
                        <input type="number" step="0.01" name="run_time" class="form-control" value="{{ old('run_time', $operation->run_time) }}" required>
                        @error('run_time')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-submit">تحديث</button>
                        <a href="{{ route('admin.routing-operations.index') }}" class="btn btn-cancel">إلغاء</a>
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
