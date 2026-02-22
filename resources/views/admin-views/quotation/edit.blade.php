{{-- resources/views/admin/quotations/edit.blade.php --}}
@extends('layouts.admin.app')

@section('content')
<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #fff;
        color: black;
        font-weight: bold;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    table thead {
        background-color: #f1f3f5;
    }
    table thead th {
        font-weight: 600;
        color: #495057;
    }
    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.4rem;
        font-size: 0.95rem;
    }
    .final-total {
        border-top: 2px solid #dee2e6;
        padding-top: 0.5rem;
        font-weight: bold;
        font-size: 1.1rem;
        color: #212529;
    }
</style>

<div class="content container-fluid">
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('الرئيسية') }}</a>
                </li>
                                <li class="breadcrumb-item active"> تعديل مسودة عرض الأسعار #{{ $quotation->id }}</li>

            </ol>
        </nav>
    </div>
    {{-- عنوان --}}


    {{-- تفاصيل العميل --}}
    <div class="card mb-4">
        <div class="card-header"> بيانات العميل</div>
        <div class="card-body row g-3">
            <div class="col-md-4"><strong>اسم العميل:</strong> {{ $quotation->customer->name }}</div>
            <div class="col-md-4"><strong>الهاتف:</strong> {{ $quotation->customer->mobile }}</div>
            <div class="col-md-4"><strong>البريد الإلكتروني:</strong> {{ $quotation->customer->email }}</div>
            <div class="col-md-4"><strong>السجل التجاري:</strong> {{ $quotation->customer->c_history }}</div>
            <div class="col-md-4"><strong>الرقم الضريبي:</strong> {{ $quotation->customer->tax_number }}</div>
            <div class="col-md-4"><strong>العنوان:</strong> {{ $quotation->customer->address }}</div>
        </div>
    </div>

    {{-- نموذج التعديل --}}
    <form id="quotation-form" action="{{ route('admin.quotations.update', $quotation->id) }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="date" value="{{ $quotation->date }}">
        <input type="hidden" name="cash" value="{{ $quotation->cash }}">
        <input type="hidden" name="type" value="0">
        <input type="hidden" name="order_amount" id="order_amount" value="{{ $quotation->order_amount }}">

        {{-- جدول المنتجات --}}
        <div class="card mb-4">
            <div class="card-header"> المنتجات / الخدمات</div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>كود</th>
                            @if($quotation->quotation_type !== 'service')
                                <th>وحدة</th>
                            @endif
                            <th>كمية</th>
                            <th>{{ $quotation->quotation_type === 'service' ? 'السعر' : 'سعر الوحدة' }}</th>
                            <th>{{ $quotation->quotation_type === 'service' ? 'الضريبة' : 'ضريبة الوحدة' }}</th>
                            <th>{{ $quotation->quotation_type === 'service' ? 'الخصم' : 'خصم الوحدة' }}</th>
                            <th>إجمالي الصف</th>
                            <th>حذف</th>
                        </tr>
                    </thead>
                    <tbody id="product-rows">
                        @foreach($quotation->details as $i => $d)
                            @php $pd = json_decode($d->product_details, true) ?? []; @endphp
                            <tr>
                                <td>
                                    <select name="products[{{ $i }}][id]" class="form-control select2" onchange="setProductData(this)" required>
                                        <option value="">-- اختر المنتج --</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}"
                                                    data-code="{{ $prod->product_code }}"
                                                    data-tax="{{ $prod->taxe->amount ?? 0 }}"
                                                    {{ $prod->id == $d->product_id ? 'selected' : '' }}>
                                                {{ $prod->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><span class="product-code">{{ $pd['product_code'] ?? '' }}</span></td>
                                @if($quotation->quotation_type !== 'service')
                                    <td>
                                        <select name="products[{{ $i }}][unit]" class="form-control">
                                            <option value="0" {{ $d->unit == 0 ? 'selected' : '' }}>كبري</option>
                                            <option value="1" {{ $d->unit == 1 ? 'selected' : '' }}>صغري</option>
                                        </select>
                                    </td>
                                @endif
                                <td><input name="products[{{ $i }}][quantity]" type="number" min="1" value="{{ $d->quantity }}" class="form-control"></td>
                                <td><input name="products[{{ $i }}][price]" type="number" step="0.01" value="{{ $d->price }}" class="form-control"></td>
                                <td><input name="products[{{ $i }}][tax]" type="text" value="{{ number_format($d->tax_amount ?? 0, 2) }}" class="form-control" readonly></td>
                                <td><input name="products[{{ $i }}][discount]" type="number" step="0.01" value="{{ $d->discount_on_product }}" class="form-control"></td>
                                <td><input name="products[{{ $i }}][row_total]" type="text" class="form-control" readonly></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">×</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">+ أضف صف</button>
            </div>
        </div>

        {{-- الملخص --}}
        <div class="row">
            <div class="col-lg-4 offset-lg-8">
                <div class="summary-card">
                    <div class="summary-row"><span>الإجمالي النهائي:</span><span id="finalTotal">{{ number_format($quotation->order_amount, 2) }}</span></div>
                    <div class="final-total"><span>الإجمالي بعد التعديلات:</span><span id="finalTotal"></span></div>
                    <button type="submit" class="btn btn-primary w-100 mt-3"> حفظ التعديلات</button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- سكريبت --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(function(){
    $('.select2').select2({ width: '100%' });

    $('#product-rows tr').each(function(){
        let sel = $(this).find('select[name*="[id]"]')[0];
        if (sel) setProductData(sel);
    });

    $('#product-rows').on('input change', '[name$="[quantity]"], [name$="[price]"], [name$="[discount]"]', function(){
        calculateRowTotal(this);
    });
});

function setProductData(sel) {
    let opt  = sel.selectedOptions[0],
        row  = $(sel).closest('tr'),
        taxP = parseFloat(opt.dataset.tax) || 0;
    row.data('tax', taxP);
    row.find('.product-code').text(opt.dataset.code || '');
    calculateRowTotal(sel);
}

function calculateRowTotal(el) {
    let row    = $(el).closest('tr'),
        q      = parseFloat(row.find('[name$="[quantity]"]').val()) || 0,
        p      = parseFloat(row.find('[name$="[price]"]').val()) || 0,
        d      = parseFloat(row.find('[name$="[discount]"]').val()) || 0,
        taxP   = row.data('tax') || 0,
        net    = Math.max(p - d, 0),
        taxVal = net * taxP / 100,
        total  = (net + taxVal) * q;
    row.find('[name$="[tax]"]').val(taxVal.toFixed(2));
    row.find('[name$="[row_total]"]').val(total.toFixed(2));
    updateSummary();
}

function updateSummary() {
    let grand = 0;
    $('#product-rows tr').each(function(){
        grand += parseFloat($(this).find('[name$="[row_total]"]').val()) || 0;
    });
    $('#finalTotal').text(grand.toFixed(2));
    $('#order_amount').val(grand.toFixed(2));
}

function addRow() {
    let idx   = $('#product-rows tr').length,
        clone = $('#product-rows tr:first').clone();
    clone.find('select, input').each(function(){
        let name = $(this).attr('name');
        if (name) $(this).attr('name', name.replace(/\[\d+\]/, `[${idx}]`));
        if (this.tagName === 'INPUT') $(this).val('');
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).val(null).trigger('change');
        }
    });
    $('#product-rows').append(clone);
    clone.find('.select2').select2({ width: '100%' });
}

function removeRow(btn) {
    if ($('#product-rows tr').length > 1) {
        $(btn).closest('tr').remove();
        updateSummary();
    }
}
</script>
@endsection
