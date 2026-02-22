{{-- resources/views/admin-views/bom-components/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'إضافة مكون جديد')

@push('css_or_js')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <style>
        .form-card {
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .form-card .card-header {
            background: #001B63;
            color: #fff;
            font-weight: 600;
            border-bottom: none;
        }
        .form-card .card-body { padding: 1.5rem; }
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
        <div class="card-header">إضافة مكون جديد</div>
        <div class="card-body">
            <form action="{{ route('admin.bomcomponents.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    {{-- BOM --}}
                    <div class="col-md-6">
                        <label class="form-label">قائمة مواد (BOM)</label>
                        <select name="bom_id" class="form-select select2-single" required>
                            <option value="" disabled selected>اختر الوصفة</option>
                            @foreach($boms as $bom)
                                <option value="{{ $bom->id }}"
                                    {{ old('bom_id') == $bom->id ? 'selected' : '' }}>
                                    {{ $bom->product->product_code }} – {{ $bom->product->name }}
                                    ({{ $bom->version }})
                                </option>
                            @endforeach
                        </select>
                        @error('bom_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Component Product --}}
                    <div class="col-md-6">
                        <label class="form-label">المكون (صنف خام)</label>
                        <select name="component_product_id" class="form-select select2-single" required>
                            <option value="" disabled selected>اختر المكون</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}"
                                    {{ old('component_product_id') == $prod->id ? 'selected' : '' }}>
                                    {{ $prod->product_code }} – {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('component_product_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Quantity --}}
                    <div class="col-md-4">
                        <label class="form-label">الكمية</label>
                        <input type="number" step="0.0001" name="quantity"
                               class="form-control" value="{{ old('quantity') }}" required>
                        @error('quantity')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Unit --}}
                        <div class="col-md-4">
                        <label class="form-label d-block">وحدة القياس</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="unit" id="unit_main" value="0"
                                {{ old('unit') == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="unit_main">وحدة رئيسية</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="unit" id="unit_sub" value="1"
                                {{ old('unit') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="unit_sub">وحدة فرعية</label>
                        </div>
                        @error('unit')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>


                    {{-- Submit --}}
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success">حفظ</button>
                        <a href="{{ route('admin.bomcomponents.index') }}" class="btn btn-secondary">إلغاء</a>
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
