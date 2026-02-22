@extends('layouts.admin.app')

@section('title', \App\CPU\translate('تعديل تحويل'))

@push('css_or_js')
    <!-- ملف CSS مخصص -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <!-- toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <h2>{{ \App\CPU\translate('تعديل تحويل') }}</h2>
    <form action="{{ route('admin.transfer.update', $transfer->id) }}" method="POST">
        @csrf
        @method('PUT')
        <!-- بيانات التحويل الأساسية -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="transfer_number">{{ \App\CPU\translate('رقم التحويل') }}</label>
                <input type="text" name="transfer_number" id="transfer_number" class="form-control" value="{{ $transfer->transfer_number }}" required>
            </div>
            <div class="col-md-4">
                <label>{{ \App\CPU\translate('فرع المصدر') }}</label>
                <input type="text" class="form-control" value="{{ $transfer->sourceBranch->name }}" readonly>
                <input type="hidden" name="source_branch_id" value="{{ $transfer->source_branch_id }}">
            </div>
            <div class="col-md-4">
                <label for="destination_branch_id">{{ \App\CPU\translate('الفرع الوجهة') }}</label>
                <select name="destination_branch_id" id="destination_branch_id" class="form-control select2" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $transfer->destination_branch_id == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- حقل الملاحظات -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="notes">{{ \App\CPU\translate('ملاحظات') }}</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ $transfer->notes }}</textarea>
            </div>
        </div>

        <!-- تفاصيل الأصناف (صف واحد لكل منتج) -->
        <h4>{{ \App\CPU\translate('تفاصيل الأصناف') }}</h4>
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th>{{ \App\CPU\translate('المنتج') }}</th>
                    <th>{{ \App\CPU\translate('الكمية') }}</th>
                    <th>{{ \App\CPU\translate('الوحدة') }}</th>
                    <th>{{ \App\CPU\translate('سعر الوحدة') }}</th>
                    <th>{{ \App\CPU\translate('التكلفة الإجمالية') }}</th>
                    <th>{{ \App\CPU\translate('إجراء') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // تجميع سجلات الأصناف بحسب (product_id + unit)
                    $groups = [];
                    foreach ($transfer->items as $item) {
                        $key = $item->product_id . '_' . $item->unit;
                        if (!isset($groups[$key])) {
                            $groups[$key] = [];
                        }
                        $groups[$key][] = $item;
                    }
                @endphp
                @foreach($groups as $index => $group)
                    @php
                        $totalQuantity = 0;
                        $totalCost = 0;
                        foreach($group as $itm){
                            $totalQuantity += $itm->quantity;
                            $totalCost += $itm->total_cost;
                        }
                        $unitCost = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
                        $mainItem = $group[0];
                    @endphp
                    <tr class="item-row">
                        <td>
                            <select name="items[{{ $index }}][product_id]" class="form-control select2 product-select" required>
                                <option value="">{{ \App\CPU\translate('اختر المنتج') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $mainItem->product_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <!-- نضع الكمية الإجمالية مع data-original لتخزين القيمة الأصلية -->
                            <input type="number" step="0.01" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ $totalQuantity }}" data-original="{{ $totalQuantity }}" required>
                        </td>
                        <td>
                            <select name="items[{{ $index }}][unit]" class="form-control select2 unit-select" required>
                                <option value="كبري" {{ $mainItem->unit == 'كبري' ? 'selected' : '' }}>{{ \App\CPU\translate('كبري') }}</option>
                                <option value="صغري" {{ $mainItem->unit == 'صغري' ? 'selected' : '' }}>{{ \App\CPU\translate('صغري') }}</option>
                            </select>
                        </td>
                        <td>
                            <!-- سعر الوحدة يُحسب ويعرض -->
                            <input type="number" step="0.01" name="items[{{ $index }}][cost]" class="form-control" value="{{ $unitCost }}" readonly>
                        </td>
                        <td>
                            <input type="number" step="0.01" name="items[{{ $index }}][total_cost]" class="form-control total-cost-input" value="{{ $totalCost }}" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-row">{{ \App\CPU\translate('حذف') }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="button" id="addRow" class="btn btn-secondary">{{ \App\CPU\translate('أضف صف جديد') }}</button>

        <br><br>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="calculated_total">{{ \App\CPU\translate('إجمالي قيمة التحويل') }}</label>
                <input type="number" step="0.01" id="calculated_total" name="total_amount" class="form-control" value="{{ $transfer->total_amount }}" readonly>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('تحديث التحويل') }}</button>
    </form>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function(){
    $('.select2').select2({
        placeholder: "{{ \App\CPU\translate('اختر') }}",
        width: '100%'
    });

    let rowCount = {{ count($groups) }};

    // دالة لحساب إجمالي قيمة التحويل
    function recalcTotal() {
        let total = 0;
        $('#itemsTable tbody tr.item-row').each(function(){
            let rowTotal = parseFloat($(this).find('.total-cost-input').val()) || 0;
            total += rowTotal;
        });
        $('#calculated_total').val(total.toFixed(2));
    }

    // إضافة صف جديد (للمنتجات الأخرى)
    $('#addRow').click(function(){
        let newRow = `<tr class="item-row">
            <td>
                <select name="items[${rowCount}][product_id]" class="form-control select2 product-select" required>
                    <option value="">{{ \App\CPU\translate('اختر المنتج') }}</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowCount}][quantity]" class="form-control quantity-input" data-original="0" required>
            </td>
            <td>
                <select name="items[${rowCount}][unit]" class="form-control select2 unit-select" required>
                    <option value="كبري">{{ \App\CPU\translate('كبري') }}</option>
                    <option value="صغري">{{ \App\CPU\translate('صغري') }}</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowCount}][cost]" class="form-control" readonly value="0.00">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowCount}][total_cost]" class="form-control total-cost-input" readonly value="0.00">
            </td>
            <td>
                <button type="button" class="btn btn-danger remove-row">{{ \App\CPU\translate('حذف') }}</button>
            </td>
        </tr>`;
        $('#itemsTable tbody').append(newRow);
        $('#itemsTable tbody tr:last .select2').select2({
            placeholder: "{{ \App\CPU\translate('اختر') }}",
            width: '100%'
        });
        rowCount++;
    });

    // حذف صف
    $(document).on('click', '.remove-row', function(){
        $(this).closest('tr').remove();
        recalcTotal();
    });

    // عند تغيير بيانات الصف الرئيسي
    $(document).on('input change', '.product-select, .quantity-input, .unit-select', function(){
        let row = $(this).closest('tr.item-row');
        if($(this).hasClass('product-select')){
            row.find('.quantity-input').val(1);
            row.find('.unit-select').val("كبري").trigger('change');
            // عند تغيير المنتج، يتم استدعاء API لتحميل السعر الأولي
            let productId = row.find('.product-select').val();
            let quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            let unit = row.find('.unit-select').val();
            if(productId && quantity > 0){
                $.ajax({
                    url: "{{ route('admin.getPrice') }}",
                    method: "GET",
                    data: { product_id: productId, quantity: quantity, unit: unit },
                    success: function(response){
                        console.log(response);
                        if(response.error){
                            toastr.error(response.error);
                            row.find('.total-cost-input').val("0.00");
                            recalcTotal();
                            return;
                        }
                        if (quantity > response.total_available_quantity) {
                            toastr.error("{{ \App\CPU\translate('الكمية المدخلة تتعدى الكمية المتاحة بالمخزن') }}: " + response.total_available_quantity);
                            row.find('.total-cost-input').val("0.00");
                            recalcTotal();
                            return;
                        }
                        let totalCost = parseFloat(response.total_cost);
                        if (isNaN(totalCost) || totalCost <= 0) {
                            toastr.error("{{ \App\CPU\translate('لم يتم إيجاد السعر لهذا المنتج') }}");
                            row.find('.total-cost-input').val("0.00");
                            recalcTotal();
                            return;
                        }
                        let unitCost = totalCost / quantity;
                        row.find('.total-cost-input').val(totalCost.toFixed(2));
                        row.find('input[name$="[cost]"]').val(unitCost.toFixed(2));
                        // تحديث قيمة data-original بالكمية الأصلية التي تم جلبها من API
                        row.find('.quantity-input').attr('data-original', quantity);
                        recalcTotal();
                    },
                    error: function(xhr){
                        console.error(xhr.responseText);
                        toastr.error("{{ \App\CPU\translate('حدث خطأ أثناء جلب السعر') }}");
                    }
                });
            }
        } else if($(this).hasClass('quantity-input')){
            // إذا تم تعديل الكمية في الصف الرئيسي
            let originalQuantity = parseFloat(row.find('.quantity-input').attr('data-original')) || 0;
            let newQuantity = parseFloat($(this).val()) || 0;
            let currentTotalCost = parseFloat(row.find('.total-cost-input').val()) || 0;
            if(newQuantity <= originalQuantity && originalQuantity > 0){
                // إعادة حساب التكلفة بدون استدعاء API: newTotalCost = (currentTotalCost / originalQuantity) * newQuantity
                let newTotalCost = (currentTotalCost / originalQuantity) * newQuantity;
                let newUnitCost = newQuantity > 0 ? newTotalCost / newQuantity : 0;
                row.find('.total-cost-input').val(newTotalCost.toFixed(2));
                row.find('input[name$="[cost]"]').val(newUnitCost.toFixed(2));
                recalcTotal();
            } else {
                // إذا الكمية الجديدة أكبر من الأصلية، يتم استدعاء API للحصول على السعر الجديد
                let productId = row.find('.product-select').val();
                let unit = row.find('.unit-select').val();
                if(productId && newQuantity > 0){
                    $.ajax({
                        url: "{{ route('admin.getPrice') }}",
                        method: "GET",
                        data: { product_id: productId, quantity: newQuantity, unit: unit },
                        success: function(response){
                            console.log(response);
                            if(response.error){
                                toastr.error(response.error);
                                row.find('.total-cost-input').val("0.00");
                                recalcTotal();
                                return;
                            }
                            if (newQuantity > response.total_available_quantity) {
                                toastr.error("{{ \App\CPU\translate('الكمية المدخلة تتعدى الكمية المتاحة بالمخزن') }}: " + response.total_available_quantity);
                                row.find('.total-cost-input').val("0.00");
                                recalcTotal();
                                return;
                            }
                            let totalCost = parseFloat(response.total_cost);
                            if (isNaN(totalCost) || totalCost <= 0) {
                                toastr.error("{{ \App\CPU\translate('لم يتم إيجاد السعر لهذا المنتج') }}");
                                row.find('.total-cost-input').val("0.00");
                                recalcTotal();
                                return;
                            }
                            let unitCost = totalCost / newQuantity;
                            row.find('.total-cost-input').val(totalCost.toFixed(2));
                            row.find('input[name$="[cost]"]').val(unitCost.toFixed(2));
                            // تحديث قيمة data-original
                            row.find('.quantity-input').attr('data-original', newQuantity);
                            recalcTotal();
                        },
                        error: function(xhr){
                            console.error(xhr.responseText);
                            toastr.error("{{ \App\CPU\translate('حدث خطأ أثناء جلب السعر') }}");
                        }
                    });
                }
            }
        }
    });
});
</script>
