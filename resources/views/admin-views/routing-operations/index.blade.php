{{-- resources/views/admin-views/routing-operations/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'خطوات التشغيل')

@push('css_or_js')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<style>
    .filter-card, .data-card {
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    .filter-card .card-header,
    .data-card .card-header {
        background: #161853;
        color: #fff;
        font-weight: 600;
        border-bottom: none;
        padding: 1rem 1.5rem;
    }
    .filter-card .card-body,
    .data-card .card-body {
        padding: 1rem 1.5rem;
    }
    .data-card table {
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .data-card thead th {
        background: #161853;
        color: #fff;
        border: none;
        font-weight: 600;
    }
    .data-card tbody tr {
        background: #fff;
        border-radius: .75rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .data-card tbody td {
        border: none;
        vertical-align: middle;
    }
    .btn-action {
        background: #fff;
        border-radius: .5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        color: #2596be;
        width: 2.2rem;
        height: 2.2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-action:hover {
        background: #f1f5f9;
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
    {{-- Filter --}}
    <div class="card filter-card">
        <div class="card-header">تصفية خطوات التشغيل</div>
        <div class="card-body">
            <form action="{{ route('admin.routing-operations.index') }}" method="GET">
                <div class="row gx-3 gy-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">المسار</label>
                        <select name="routing_id" class="form-select select2-single">
                            <option value="">كل المسارات</option>
                            @foreach($routings as $r)
                                <option value="{{ $r->id }}" {{ request('routing_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->bom->product->sku }} – {{ $r->bom->product->name }} ({{ $r->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">مركز العمل</label>
                        <select name="work_center_id" class="form-select select2-single">
                            <option value="">كل المراكز</option>
                            @foreach($workCenters as $wc)
                                <option value="{{ $wc->id }}" {{ request('work_center_id') == $wc->id ? 'selected' : '' }}>
                                    {{ $wc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="submit" class="btn btn-primary">تطبيق التصفية</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card data-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>خطوات التشغيل</span>
            <a href="{{ route('admin.routing-operations.create') }}" class="btn btn-success btn-sm">
                <i class="tio-add-circle"></i> إضافة خطوة
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-borderless align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المسار</th>
                        <th>مركز العمل</th>
                        <th>التسلسل</th>
                        <th>إعداد (ساعي)</th>
                        <th>تشغيل (ساعي)</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operations as $i => $op)
                        <tr>
                            <td>{{ $operations->firstItem() + $i }}</td>
                            <td>{{ $op->routing->name }}</td>
                            <td>{{ $op->workCenter->name }}</td>
                            <td>{{ $op->sequence }}</td>
                            <td>{{ $op->setup_time }}</td>
                            <td>{{ $op->run_time }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.routing-operations.edit', $op->id) }}" class="btn-action" title="تعديل">
                                    <i class="tio-edit"></i>
                                </a>
                                <!--<form action="{{ route('admin.routing-operations.destroy', $op->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">-->
                                <!--    @csrf @method('DELETE')-->
                                <!--    <button class="btn-action" title="حذف">-->
                                <!--        <i class="tio-delete"></i>-->
                                <!--    </button>-->
                                <!--</form>-->
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($operations->isEmpty())
                <div class="text-center py-5 text-muted">لا توجد خطوات لعرضها.</div>
            @endif

            <div class="mt-4">
                {{ $operations->appends(request()->query())->links() }}
            </div>
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
