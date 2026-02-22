{{-- resources/views/admin-views/bom-components/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'المكونات في قائمة المواد')

@push('css_or_js')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <style>
        .filter-card, .table-card {
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .filter-card .card-header, .table-card .card-header {
            background: #001B63;
            color: #fff;
            font-weight: 600;
            border-bottom: none;
        }
        .filter-card .card-body, .table-card .card-body {
            padding: 1rem 1.5rem;
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
    {{-- Filter by Product --}}
    <div class="card filter-card">
        <div class="card-header">تصفية حسب المنتج</div>
        <div class="card-body">
            <form action="{{ route('admin.bomcomponents.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="product_id" class="form-select select2-single">
                            <option value="">عرض كل المكونات</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}"
                                    {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                    {{ $prod->product_code }} – {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">تصفية</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Components Table --}}
    <div class="card table-card">
        <div class="card-header">قائمة المكونات</div>
        <div class="card-body table-responsive">
            <table class="table table-borderless table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الوصفة</th>
                        <th>الصنف (مكون)</th>
                        <th>الكمية</th>
                        <th>وحدة القياس</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($components as $idx => $comp)
                        <tr>
                            <td>{{ $components->firstItem() + $idx }}</td>
                            <td>{{ $comp->billOfMaterial->product->product_code }} – {{ $comp->billOfMaterial->product->name }}
                                ({{ $comp->billOfMaterial->version }})
                            </td>
                            <td>{{ $comp->componentProduct->product_code }} – {{ $comp->componentProduct->name }}</td>
                            <td>{{ $comp->quantity }}</td>
                            <td> @if($comp->unit == 0)
                            @php $converted = $comp->quantity / $comp->componentProduct->unit_value; @endphp
                            @if(is_float($converted) && floor($converted) != $converted)
                                {{ $comp->quantity }} {{ $comp->componentProduct->unit->subUnits->first()?->name ?? $comp->unit_label }}
                            @else
                                {{ (int)$converted }} {{ $comp->componentProduct->unit->unit_type ?? $comp->unit_label }}
                            @endif
                        @else
                            {{ $comp->quantity }} {{ $comp->unit_label }}
                        @endif</td>
                            <td class="text-center">
                                <a href="{{ route('admin.bomcomponents.edit', $comp->id) }}"
                                   class="btn btn-white btn-sm me-1" title="تعديل">
                                    <i class="tio-edit"></i>
                                </a>
                                <!--<form action="{{ route('admin.bomcomponents.destroy', $comp->id) }}"-->
                                <!--      method="POST" class="d-inline-block"-->
                                <!--      onsubmit="return confirm('هل أنت متأكد من حذف هذا المكون؟');">-->
                                <!--    @csrf @method('DELETE')-->
                                <!--    <button class="btn btn-white btn-sm" title="حذف">-->
                                <!--        <i class="tio-delete"></i>-->
                                <!--    </button>-->
                                <!--</form>-->
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $components->appends(request()->query())->links() }}
            </div>

            {{-- No Data --}}
            @if($components->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted">لا توجد مكونات لعرضها.</p>
                </div>
            @endif
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
