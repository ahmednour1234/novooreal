@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_stock'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="row align-items-center mb-3">
        <div class="col-sm mb-2 mb-sm-0">
            <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                <i class="tio-add-circle-outlined"></i>
                {{ \App\CPU\translate('امر صرف مخزني') }}
            </h1>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="row gx-2 gx-lg-3">
        <div class="col-12 mb-lg-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.stock.store') }}" method="POST" id="stock_form" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('البريد الالكتروني للمندوب') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select id="seller" name="seller_id" class="form-control js-select2-custom" required>
                                        <option value="" hidden>-- {{ \App\CPU\translate('اختر مندوب') }} --</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}" @if(old('seller_id') == $seller->id) selected @endif>
                                                {{ $seller->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{ \App\CPU\translate('المنتجات') }}
                                        <span class="input-label-secondary text-danger">*</span>
                                    </label>
                                    <select id="products" name="products[]" class="form-control js-select2-custom" multiple required>
                                        <option value="" disabled>--- {{ \App\CPU\translate('اختر') }} ---</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ \App\CPU\translate('المنتج') }}</th>
                                        <th>{{ \App\CPU\translate('الكمية') }}</th>
                                        <th>{{ \App\CPU\translate('وحدة القياس') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="data"></tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            {{ \App\CPU\translate('حفظ') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let simple = [];

        // Fetch products when seller changes
        $('#seller').on('change', function() {
            let sellerId = $(this).val();
            if (!sellerId) return;

            $.get("{{ route('admin.stock.create') }}", { seller: sellerId })
                .done(function(response) {
                    // Flatten and store products
                    simple = response.option.flat().map(item => ({ id: item.id, name: item.name }));

                    // Populate products select
                    let options = '<option value="" disabled>--- {{ \App\CPU\translate('select') }} ---</option>';
                    simple.forEach(item => {
                        options += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $('#products').html(options).trigger('change.select2');
                })
                .fail(function(error) {
                    console.error('Error fetching products:', error);
                });
        });

        // Append a new row for each selected product
        $('#products').on('change', function() {
            let selected = $(this).val() || [];
            $('#data').empty();

            selected.forEach(function(productId) {
                let product = simple.find(item => item.id == productId);
                if (product) {
                    let row = `
                        <tr>
                            <td>
                                <span>${product.name}</span>
                                <input type="hidden" name="product_id[]" value="${product.id}">
                            </td>
                            <td>
                                <input type="number"
                                       step="0.1"
                                       placeholder="Ex: 0.2"
                                       name="stock[]"
                                       class="form-control"
                                       required>
                            </td>
                            <td>
                                <select name="unit[]" class="form-control" required>
                                    <option value="0">{{ \App\CPU\translate('وحدة قياس صغري') }}</option>
                                    <option value="1">{{ \App\CPU\translate('وحدة قياس كُبرى') }}</option>
                                </select>
                            </td>
                        </tr>
                    `;
                    $('#data').append(row);
                }
            });
        });
    });
</script>
