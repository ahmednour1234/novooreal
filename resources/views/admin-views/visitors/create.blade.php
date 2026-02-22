@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_visitors'))

@push('css_or_js')
    <!-- يمكنك تضمين CSS مخصص هنا إذا لزم الأمر -->
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="row align-items-center mb-3">
        <div class="col-sm mb-2 mb-sm-0">
            <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                <i class="tio-add-circle-outlined"></i> {{ \App\CPU\translate('انشاء زيارات الشهر') }}
            </h1>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="card mb-4">
        <div class="card-body">
            <form id="visitorForm" action="{{ route('admin.visitor.store') }}" method="POST">
                @csrf

                <div class="form-row mb-3">
                    <!-- مندوب -->
                    <div class="form-group col-md-4">
                        <label>{{ \App\CPU\translate('ايميل المندوب') }} <span class="text-danger">*</span></label>
                        <select id="seller" name="seller_id" class="form-control" required>
                            <option value="" disabled selected>-- {{ \App\CPU\translate('اختار مندوب') }} --</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- العملاء -->
                    <div class="form-group col-md-6">
                        <label>{{ \App\CPU\translate('العملاء') }} <span class="text-danger">*</span></label>
                        <select id="customer" class="form-control" disabled>
                            <option value="" disabled selected>-- {{ \App\CPU\translate('select') }} --</option>
                        </select>
                    </div>
                </div>

                <!-- جدول الزيارات -->
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>العنوان</th>
                                <th>رقم الهاتف</th>
                                <th>التاريخ</th>
                                <th>ملاحظة</th>
                                <th>حذف</th>
                            </tr>
                        </thead>
                        <tbody id="data"></tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary btn-block">{{ \App\CPU\translate('حفظ') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let customersList = [];

    // عند تغيير المندوب
    $('#seller').on('change', function() {
        const sellerId = $(this).val();

        // تعطيل واستهلال حقل العملاء
        $('#customer').prop('disabled', true).empty()
                      .append('<option value="" disabled>-- {{ \App\CPU\translate('select') }} --</option>');
        // مسح الجدول الحالي
        $('#data').empty();

        // طلب AJAX لجلب العملاء
        $.ajax({
            url: '{{ route('admin.visitor.create') }}',
            method: 'GET',
            data: { seller: sellerId },
            success: function(response) {
                customersList = response.option || [];
                // بناء الخيارات
                $('#customer').empty()
                              .append('<option value="" disabled selected>-- {{ \App\CPU\translate('select') }} --</option>');
                customersList.forEach(function(c) {
                    $('#customer').append(
                        $('<option>').val(c.id).text(c.name)
                    );
                });
                $('#customer').prop('disabled', false);
            },
            error: function(err) {
                console.error('Error fetching customers:', err);
            }
        });
    });

    // عند اختيار عميل
    $('#customer').on('change', function() {
        const custId = $(this).val();
        const cust = customersList.find(item => item.id == custId);
        if (!cust) return;

        // إنشاء صف جديد
        const row = `
            <tr>
                <td>
                    ${cust.name}
                    <input type="hidden" name="customer_id[]" value="${cust.id}">
                </td>
                <td>${cust.address || 'N/A'}</td>
                <td>${cust.mobile || 'N/A'}</td>
                <td><input type="date" name="date[]" class="form-control" required></td>
                <td><input type="text" name="note[]" class="form-control" required></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-btn">حذف</button></td>
            </tr>
        `;
        $('#data').append(row);

        // إعادة تعيين حقل العملاء
        $(this).val('');
    });

    // إزالة صف
    $('#data').on('click', '.remove-btn', function() {
        $(this).closest('tr').remove();
    });
});
</script>
