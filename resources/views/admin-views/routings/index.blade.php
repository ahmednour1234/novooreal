{{-- resources/views/admin-views/routings/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'قوائم التشغيل (Routings)')

@push('css_or_js')
    <!-- Select2 CSS -->
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
            font-size: 1.1rem;
        }
        .filter-card .card-body,
        .data-card .card-body {
            padding: 1rem 1.5rem;
        }
        .data-card table {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }
        .data-card tbody tr {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }
        .data-card thead th {
            background: #161853;
            color: #fff;
            font-weight: 600;
            border: none;
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
        <div class="card-header">تصفية المسارات</div>
        <div class="card-body">
            <form action="{{ route('admin.routings.index') }}" method="GET">
                <div class="row gx-3 gy-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">قائمة المواد (BOM)</label>
                        <select name="bom_id" class="form-select select2-single">
                            <option value="">كل الوصفات</option>
                            @foreach($boms as $bom)
                                <option value="{{ $bom->id }}" {{ request('bom_id')==$bom->id?'selected':'' }}>
                                    {{ $bom->product->sku }} – {{ $bom->product->name }} ({{ $bom->version }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">اسم المسار</label>
                        <input type="text" name="name" class="form-control" value="{{ request('name') }}" placeholder="ابحث بالاسم">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">تطبيق التصفية</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card data-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>قائمة المسارات</span>
            <a href="{{ route('admin.routings.create') }}" class="btn btn-success btn-sm">
                <i class="tio-add-circle"></i> مسار جديد
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-borderless align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>BOM</th>
                        <th>اسم المسار</th>
                        <th>تاريخ التفعيل</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routings as $idx => $routing)
                        <tr>
                            <td>{{ $routings->firstItem() + $idx }}</td>
                            <td>{{ $routing->bom->product->sku }} – {{ $routing->bom->product->name }} ({{ $routing->bom->version }})</td>
                            <td>{{ $routing->name }}</td>
                            <td>{{ $routing->effective_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.routings.edit', $routing->id) }}" class="btn-action" title="تعديل">
                                    <i class="tio-edit"></i>
                                </a>
                                <!--<form action="{{ route('admin.routings.destroy', $routing->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">-->
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

            @if($routings->isEmpty())
                <div class="text-center py-5 text-muted">لا توجد مسارات لعرضها.</div>
            @endif

            <div class="mt-4">
                {{ $routings->appends(request()->query())->links() }}
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
