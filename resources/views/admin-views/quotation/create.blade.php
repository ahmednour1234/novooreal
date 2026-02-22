{{-- resources/views/admin/quotations/create.blade.php --}}
@extends('layouts.admin.app')

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<style>
    body {
        background: #f8f9fa;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .card-header {
        font-weight: 600;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .summary-card {
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .summary-card h5 {
        font-weight: 600;
        margin-bottom: 1rem;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.4rem;
    }
    .final-total {
        border-top: 1px solid #dee2e6;
        padding-top: 0.5rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    {{-- Breadcrumb --}}
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('الرئيسية') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ \App\CPU\translate('عرض سعر') }}</li>
            </ol>
        </nav>
    </div>

    <form id="quotation-form" action="{{ route('admin.quotations.store') }}" method="POST">
        @csrf
        <input type="hidden" name="type" id="form-type" value="12">
        <input type="hidden" name="order_amount" id="order_amount" value="0">
        <input type="hidden" name="cash" id="cash" value="2">

        {{-- اختيار العميل --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-1 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">اختر العميل</label>
                        <div class="input-group">
                            <select id="supplier" name="customer_id" class="form-control select" onchange="showSupplierDetails(this)" required>
                                <option value="">-- اختر العميل --</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    data-name="{{ $customer->name }}"
                                    data-email="{{ $customer->email }}"
                                    data-phone="{{ $customer->mobile }}"
                                    data-address="{{ $customer->address }}"
                                    data-tax_number="{{ $customer->tax_number }}"
                                    data-c_history="{{ $customer->c_history }}">
                                    {{ $customer->name }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تاريخ الفاتورة</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-success" onclick="submitForm(0)">حفظ</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- تفاصيل العميل --}}
        <div id="supplier-details" class="card mb-4" style="display:none;">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><strong>اسم العميل:</strong> <span id="sup-name"></span></div>
                    <div class="col-md-4"><strong>الهاتف:</strong> <span id="sup-phone"></span></div>
                    <div class="col-md-4"><strong>البريد الإلكتروني:</strong> <span id="sup-email"></span></div>
                    <div class="col-md-4"><strong>السجل التجاري:</strong> <span id="sup-history"></span></div>
                    <div class="col-md-4"><strong>الرقم الضريبي:</strong> <span id="sup-tax"></span></div>
                    <div class="col-md-4"><strong>العنوان:</strong> <span id="sup-address"></span></div>
                </div>
            </div>
        </div>

        {{-- جدول المنتجات --}}
        <div class="card mb-4">
            <div class="card-header">إضافة منتجات</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width:25%;">{{ $type == 'service' ? 'الخدمة' : 'المنتج' }}</th>
                                <th>كود</th>
                                @if($type != 'service')
                                <th>وحدة</th>
                                @endif
                                <th>كمية</th>
                                <th>سعر</th>
                                <th>ضريبة</th>
                                <th>شامل</th>
                                <th>خصم</th>
                                <th>إجمالي</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="product-rows">
                            <tr>
                                <td>
                                    <select name="products[0][id]" class="form-control select2" onchange="setProductData(this)" required>
                                        <option value="">-- اختر --</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                            data-code="{{ $product->product_code }}"
                                            data-tax="{{ $product->taxe->amount ?? 0 }}">
                                            {{ $product->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><span class="product-code"></span></td>
                                @if($type != 'service')
                                <td>
                                    <select name="products[0][unit]" class="form-control select2">
                                        <option value="0">كبري</option>
                                        <option value="1">صغري</option>
                                    </select>
                                </td>
                                @endif
                                <td><input type="number" name="products[0][quantity]" value="1" min="1" class="form-control" onchange="calculateRowTotal(this)"></td>
                                <td><input type="number" name="products[0][price]" value="0" step="0.01" min="0" class="form-control" onchange="calculateRowTotal(this)"></td>
                                <td><input type="text" name="products[0][tax]" class="form-control" readonly></td>
                                <td><input type="text" name="products[0][price_incl_tax]" class="form-control" readonly></td>
                                <td><input type="number" name="products[0][discount]" value="0" step="0.01" class="form-control" onchange="calculateRowTotal(this)"></td>
                                <td><input type="text" name="products[0][row_total]" class="form-control" readonly></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addRow()">+ أضف صف</button>
                </div>
            </div>
        </div>

        {{-- ملخص --}}
        <div class="row">
            <div class="col-lg-4 offset-lg-8">
                <div class="summary-card">
                    <h5>ملخص</h5>
                    <div class="summary-row"><span>دون خصم:</span><span id="subtotal">0.00</span></div>
                    <div class="summary-row"><span>خصم:</span><span id="totalDiscount">0.00</span></div>
                    <div class="summary-row"><span>ضريبة:</span><span id="totalTax">0.00</span></div>
                    <div class="summary-row"><span>إجمالي:</span><span id="grandTotal">0.00</span></div>
                    <div class="summary-row final-total"><span>الإجمالي النهائي:</span><span id="finalTotal">0.00</span></div>
                    <button type="button" class="btn btn-primary w-100 mt-3" onclick="submitForm(0)">حفظ</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('admin.customer.store') }}" method="POST" id="addClientForm">
            @csrf
            <div class="modal-content shadow">
                <div class="modal-header bg-secondary text-dark">
                    <h5 class="modal-title" style="color:black;" id="addClientModalLabel">
                     إضافة عميل جديد
                    </h5>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="ادخل اسم العميل" value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">رقم الجوال <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" name="mobile" class="form-control" placeholder="مثال: 05xxxxxxxx" value="{{ old('mobile') }}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="example@email.com" value="{{ old('email') }}">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary">
                     حفظ العميل
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@push('script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(function(){
    $('.select2').select2({ width:'100%' });
});
function showSupplierDetails(sel){
    let opt = sel.selectedOptions[0];
    if(!sel.value){ $('#supplier-details').hide(); return; }
    $('#sup-name').text(opt.dataset.name);
    $('#sup-phone').text(opt.dataset.phone);
    $('#sup-email').text(opt.dataset.email);
    $('#sup-history').text(opt.dataset.c_history);
    $('#sup-tax').text(opt.dataset.tax_number);
    $('#sup-address').text(opt.dataset.address);
    $('#supplier-details').show();
}
function setProductData(sel){
    let opt = sel.selectedOptions[0], row = $(sel).closest('tr');
    row.find('.product-code').text(opt.dataset.code);
    row.data('tax', +opt.dataset.tax || 0);
    calculateRowTotal(sel);
}
function calculateRowTotal(inp){
    let row = $(inp).closest('tr'),
        q   = +row.find('[name$="[quantity]"]').val(),
        p   = +row.find('[name$="[price]"]').val(),
        d   = +row.find('[name$="[discount]"]').val(),
        taxP= row.data('tax'),
        eff = Math.max(p - d, 0),
        taxVal = eff * taxP / 100;
    row.find('[name$="[tax]"]').val(taxVal.toFixed(2));
    let incl = eff + taxVal;
    row.find('[name$="[price_incl_tax]"]').val(incl.toFixed(2));
    row.find('[name$="[row_total]"]').val((q * incl).toFixed(2));
    updateSummary();
}
function updateSummary(){
    let sub=0, disc=0, tax=0, grand=0;
    $('#product-rows tr').each(function(){
        let r = $(this),
            q = +r.find('[name$="[quantity]"]').val(),
            p = +r.find('[name$="[price]"]').val(),
            d = +r.find('[name$="[discount]"]').val(),
            t = +r.find('[name$="[tax]"]').val();
        sub  += q * p;
        disc += q * d;
        tax  += q * t;
        grand+= q * (p - d + t);
    });
    $('#subtotal').text(sub.toFixed(2));
    $('#totalDiscount').text(disc.toFixed(2));
    $('#totalTax').text(tax.toFixed(2));
    $('#grandTotal').text(grand.toFixed(2));
    $('#finalTotal').text(grand.toFixed(2));
    $('#order_amount').val(grand.toFixed(2));
}
function addRow(){
    let idx = $('#product-rows tr').length,
        newRow = $('#product-rows tr:first').clone();
    newRow.find('select, input').each(function(){
        let nm = $(this).attr('name');
        if(nm){
            let newNm = nm.replace(/\[\d+\]/,`[${idx}]`);
            $(this).attr('name', newNm);
        }
        $(this).val('');
        if($(this).hasClass('select2-hidden-accessible')){
            $(this).next('span.select2').remove();
            $(this).select2({ width:'100%' });
        }
    });
    $('#product-rows').append(newRow);
}
function removeRow(btn){
    if($('#product-rows tr').length > 1){
        $(btn).closest('tr').remove();
        updateSummary();
    }
}
function submitForm(type){
    $('#form-type').val(type);
    $('#quotation-form').submit();
}
</script>
@endpush
