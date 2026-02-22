{{-- resources/views/admin-views/work-centers/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'قائمة مراكز العمل')

@push('css_or_js')
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <style>
        /* Cards */
        .filter-card, .data-card {
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .filter-card .card-header,
        .data-card .card-header {
            background: #001B63;
            color: #fff;
            font-weight: 600;
            border-bottom: none;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
        }
        .filter-card .card-body,
        .data-card .card-body {
            padding: 1.25rem 1.5rem;
        }

        /* Table styling */
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
            border: none;
            color: #fff;
            font-weight: 600;
            background: #001B63;
        }
        .data-card tbody td {
            vertical-align: middle;
            border: none;
        }

        /* Buttons */
        .btn-filter {
            background: #001B63;
            color: #fff;
            border-radius: 0.75rem;
        }
        .btn-action {
            background: #fff;
            border-radius: 0.75rem;
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

        /* Select2 */
        .select2-container--default .select2-selection--single {
            border-radius: .5rem;
            height: calc(1.5em + 1rem + 2px);
            padding: .375rem 1rem;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid" dir="rtl">
    {{-- Filter Section --}}
    <div class="card filter-card">
        <div class="card-header">تصفية مراكز العمل</div>
        <div class="card-body">
            <form action="{{ route('admin.work-centers.index') }}" method="GET">
                <div class="row gx-3 gy-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-select select2-single">
                            <option value="">كل الفروع</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ترتيب تكلفة الساعة</label>
                        <select name="cost_sort" class="form-select">
                            <option value="">بلا ترتيب</option>
                            <option value="asc" {{ request('cost_sort')=='asc' ? 'selected' : '' }}>الأقل أولاً</option>
                            <option value="desc" {{ request('cost_sort')=='desc' ? 'selected' : '' }}>الأعلى أولاً</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ترتيب سعة اليوم</label>
                        <select name="capacity_sort" class="form-select">
                            <option value="">بلا ترتيب</option>
                            <option value="asc" {{ request('capacity_sort')=='asc' ? 'selected' : '' }}>الأقل أولاً</option>
                            <option value="desc" {{ request('capacity_sort')=='desc' ? 'selected' : '' }}>الأعلى أولاً</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-filter">تطبيق التصفية</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card data-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>مراكز العمل</span>
            <a href="{{ route('admin.work-centers.create') }}" class="btn btn-success btn-sm">
                <i class="tio-add-circle"></i> إضافة جديد
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-borderless align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الفرع</th>
                        <th>اسم المركز</th>
                        <th>سعة اليوم (ساعات)</th>
                        <th>تكلفة الساعة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workCenters as $idx => $wc)
                        <tr>
                            <td>{{ $workCenters->firstItem() + $idx }}</td>
                            <td>{{ optional($wc->branch)->name ?? '-' }}</td>
                            <td>{{ $wc->name }}</td>
                            <td>{{ $wc->capacity_per_day ?? '-' }}</td>
                            <td>{{ $wc->cost_per_hour ?? '-' }}</td>
                            <td class="text-center">
                      
                                <a href="{{ route('admin.work-centers.edit', $wc->id) }}" class="btn-action" title="تعديل">
                                    <i class="tio-edit"></i>
                                </a>
                                <!--<form action="{{ route('admin.work-centers.destroy', $wc->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">-->
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

            @if($workCenters->isEmpty())
                <div class="text-center py-5 text-muted">لا توجد بيانات لعرضها.</div>
            @endif

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $workCenters->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.select2-single').select2({ placeholder: 'اختر', width: '100%' });
        });
    </script>
