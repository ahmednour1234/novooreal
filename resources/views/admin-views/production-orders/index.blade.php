{{-- resources/views/admin-views/production-orders/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'قائمة أوامر الإنتاج')

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
    width: 2rem;
    height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 .125rem;
}
.btn-action:hover {
    background: #f1f5f9;
}
.btn-start {
    background: #ffc107;
    border-radius: .5rem;
    color: #fff;
    padding: .25rem .5rem;
    font-size: .85rem;
    margin: 0 .125rem;
}
.btn-cancel {
    background: #dc3545;
    border-radius: .5rem;
    color: #fff;
    padding: .25rem .5rem;
    font-size: .85rem;
    margin: 0 .125rem;
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
    {{-- تصفية --}}
    <div class="card filter-card">
        <div class="card-header">تصفية أوامر الإنتاج</div>
        <div class="card-body">
            <form action="{{ route('admin.production-orders.index') }}" method="GET">
                <div class="row gx-3 gy-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-select select2-single">
                            <option value="">كل الفروع</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">المُصدر</label>
                        <select name="issued_by" class="form-select select2-single">
                            <option value="">كل المستخدمين</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('issued_by')==$u->id?'selected':'' }}>
                                    {{ $u->f_name }} {{ $u->l_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            @foreach(['draft'=>'مسودة','planned'=>'مخطط','in_progress'=>'قيد التنفيذ','completed'=>'مكتمل','cancelled'=>'ملغي'] as $key=>$label)
                                <option value="{{ $key }}" {{ request('status')==$key?'selected':'' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-primary">تطبيق</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول البيانات --}}
    <div class="card data-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>أوامر الإنتاج</span>
            <a href="{{ route('admin.production-orders.create') }}" class="btn btn-success btn-sm">
                <i class="tio-add-circle"></i> إنشاء أمر جديد
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-borderless align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>الوحدة</th>
                        <th>الحالة</th>
                        <th>الفرع</th>
                        <th>المُصدر</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $i => $o)
                        <tr>
                            <td>{{ $orders->firstItem() + $i }}</td>
                            <td>{{ $o->product->product_code ?? '' }} – {{ $o->product->name ?? '' }}</td>
                            <td>{{ $o->quantity }}</td>
                            <td>{{ $o->unit_label }}</td>
                            <td>
                              @switch($o->status)
                                @case('draft') مسودة @break
                                @case('planned') مخطط @break
                                @case('in_progress') قيد التنفيذ @break
                                @case('completed') مكتمل @break
                                @case('cancelled') ملغي @break
                                @default غير معروف
                              @endswitch
                            </td>
                            <td>{{ $o->branch->name ?? '' }}</td>
                            <td>{{ $o->issuer->f_name }} {{ $o->issuer->l_name }}</td>
                            <td class="text-center">
                                {{-- عرض --}}
                                <a href="{{ route('admin.production-orders.show', $o->id) }}"
                                   class="btn-action" title="عرض">
                                    <i class="tio-visible"></i>
                                </a>

                                {{-- تعديل (مسودة أو مخطط) --}}
                                @if(in_array($o->status, ['draft','planned']))
                                    <a href="{{ route('admin.production-orders.edit', $o->id) }}"
                                       class="btn-action" title="تعديل">
                                        <i class="tio-edit"></i>
                                    </a>
                                @endif

                                {{-- إلغاء الأمر (مسودة أو مخطط) --}}
                                @if(in_array($o->status, ['draft','planned']))
                                    <button type="button"
                                        class="btn-cancel"
                                        onclick="if(confirm('هل أنت متأكد من إلغاء هذا الأمر؟')) {
                                            document.getElementById('cancel-form-{{ $o->id }}').submit();
                                        }"
                                        title="إلغاء">
                                        إلغاء
                                    </button>
                                    <form id="cancel-form-{{ $o->id }}"
                                          action="{{ route('admin.production-orders.cancel', $o->id) }}"
                                          method="POST" class="d-none">
                                        @csrf
                                    </form>
                                @endif

                                {{-- بدء التنفيذ (فقط إذا كان مخطط) --}}
                                @if($o->status === 'planned')
                                    <button type="button"
                                        class="btn-start"
                                        onclick="if(confirm('هل تريد بدء تنفيذ هذا الأمر؟')) {
                                            document.getElementById('start-form-{{ $o->id }}').submit();
                                        }"
                                        title="بدء التنفيذ">
                                        بدء
                                    </button>
                                    <form id="start-form-{{ $o->id }}"
                                          action="{{ route('admin.production-orders.startProduction', $o->id) }}"
                                          method="POST" class="d-none">
                                        @csrf
                                    </form>
                                @endif
                               @if($o->status === 'in_progress')
    <a href="{{ route('admin.production-orders.finalize.form', ['order' => $o->id]) }}"
       class="btn btn-primary btn-start"
       title="بدء التنفيذ"
>        إنهاء
    </a>
@endif

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($orders->isEmpty())
                <div class="text-center py-5 text-muted">لا توجد أوامر لعرضها.</div>
            @endif

            <div class="mt-4">
                {{ $orders->appends(request()->query())->links() }}
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
