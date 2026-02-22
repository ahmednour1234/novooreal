@extends('layouts.admin.app')

@section('title',\App\CPU\translate('add_new_product'))

@push('css_or_js')
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('public/assets/admin/css/tags-input.min.css')}}" rel="stylesheet"> --}}
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('إضافة منتج هالك') }}</li>
      </ol>
    </nav>
  </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.product.storeexpire')}}" method="post" id="product_form"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row pl-2">

                                <!-- Product Selection -->
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="product_id">
                                            {{\App\CPU\translate('اختار منتج')}}
                                            <span class="input-label-secondary">*</span>
                                        </label>
                                        <select name="product_id" id="product_id" class="form-control" required>
                                            <option value="" disabled selected>
                                                {{\App\CPU\translate('اختار منتج')}}
                                            </option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Quantity Input -->
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label"
                                               for="quantity">{{\App\CPU\translate('الكمية')}}
                                            <span class="input-label-secondary">*</span>
                                        </label>
                                        <input type="number" min="0" step="0.1" name="quantity" class="form-control"
                                               value="{{ old('quantity') }}"
                                               placeholder="{{\App\CPU\translate('quantity')}}" required>
                                    </div>
                                </div>
                                 <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="unit">
                                            {{\App\CPU\translate('اختار وحدة القياس')}}
                                            <span class="input-label-secondary">*</span>
                                        </label>
                                        <select name="unit" id="unit" class="form-control" required>
                                            <option value="" disabled selected>
                                                {{\App\CPU\translate('اختار وحدة القياس')}}
                                            </option>
                                                <option value="0">
                                                   وحدة قياس صغري
                                                </option>
                                                       <option value="1">
                                                   وحدة قياس كبري
                                                </option>
                                        </select>
                                    </div>
                                </div>
                                                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label"
                                               for="quantity">{{\App\CPU\translate('الملاحظة')}}
                                            <span class="input-label-secondary">*</span>
                                        </label>
                                        <input type="text"  name="note" class="form-control"
                                               value="{{ old('note') }}"
                                               placeholder="{{\App\CPU\translate('note')}}" required>
                                    </div>
                                </div>

                            </div>

                            <!-- Submit Button -->
<div class="mt-4 d-flex justify-content-end">
  <button type="submit" class="btn btn-primary col-3">
    {{ \App\CPU\translate('حفظ') }}
  </button>
</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

    <script src={{asset("public/assets/admin/js/global.js")}}></script>
    <script>
function roundToNearestStep(input, step) {
    // Get the entered value from the input field
    var value = parseFloat(input.value);

    // If the entered value is not a number, exit the function
    if (isNaN(value)) return;

    // Round the value to the nearest multiple of the step
    var roundedValue = Math.round(value / step) * step;

    // Update the input field with the rounded value
    input.value = roundedValue.toFixed(2); // Adjust toFixed argument based on your precision requirement
}
</script>

