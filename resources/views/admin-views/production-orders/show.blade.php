{{-- resources/views/admin-views/production-orders/show.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'تفاصيل أمر الإنتاج')

@push('css_or_js')
<style>
    /* Container */
    .content { max-width: 1200px; margin: auto; }

    /* Card Wrapper */
    .card-section {
        margin-bottom: 2rem;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        background: #fff;
    }
    /* Header */
    .card-section .card-header {
        background: #001B63;
        color: #fff;
        font-weight: 700;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    /* Body */
    .card-section .card-body {
        padding: 1.5rem;
    }
    /* Definition list */
    .detail-dl dt {
        font-weight: 600;
        width: 25%;
        float: right;
        text-align: right;
    }
    .detail-dl dd {
        margin: 0 0 1rem;
        width: 75%;
        float: right;
    }
    .detail-dl::after { content: ""; display: table; clear: both; }

    /* Tables */
    .table-section table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .table-section thead th {
        background: #001B63;
        color: #161853;
        font-weight: 600;
        padding: .75rem 1rem;
        border: none;
    }
    .table-section tbody tr {
        background: #fff;
        border-radius: .75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .table-section tbody td {
        border: none;
        padding: .75rem 1rem;
        vertical-align: middle;
    }

    /* Buttons */
    .btn-group-sm .btn {
        margin-left: .5rem;
        font-size: .85rem;
        padding: .4rem .8rem;
    }
    .btn-cancel { background: #e63946; color: #fff; }
    .btn-start  { background: #ffb703; color: #fff; }
    .btn-print  { background: #023047; color: #fff; }

    /* Hide on print */
    @media print {
        .no-print { display: none !important; }
        body * { visibility: hidden; }
        .print-section, .print-section * { visibility: visible; }
        .print-section { position: absolute; top: 0; left: 0; width: 100%; }
    }
</style>@endpush

@section('content')
<section dir="rtl">
    {{-- احتساب وحدات الإنتاج الفعلية --}}
    @php
        $units = $order->quantity;
        if ($order->unit == 0) {
            $units = $order->quantity / $order->product->unit_value;
        }
        $units = round($units, 4);
    @endphp

    {{-- Detail Section --}}
    <div class="card-section">
        <div class="card-header">
            <span>تفاصيل أمر الإنتاج</span>
            <div class="no-print btn-group btn-group-sm">
                @if(in_array($order->status, ['draft','planned']))
                    <button id="cancel-btn" class="btn btn-outline-danger btn-sm">إلغاء الأمر</button>
                    <form id="cancel-form" action="{{ route('admin.production-orders.cancel', $order->id) }}" method="POST" class="d-none">@csrf</form>
                @endif
                @if($order->status === 'planned')
                    <button id="start-btn" class="btn btn-start btn-sm">بدء التنفيذ</button>
                    <form id="start-form" action="{{ route('admin.production-orders.startProduction', $order->id) }}" method="POST" class="d-none">@csrf</form>
                @endif
                <button id="toggle-logs" class="btn btn-sm btn-light">عرض/إخفاء السجل</button>
                <button class="btn btn-sm btn-primary" type="button" onclick="printInvoice('{{ $order->id }}')">طباعة</button>
            </div>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">المنتج</dt>
                <dd class="col-9">{{ $order->product->product_code }} – {{ $order->product->name }}</dd>

                <dt class="col-3">الكمية المطلوبة</dt>
                <dd class="col-9">{{ $order->quantity }} {{ $order->unit_label }}</dd>

                <dt class="col-3">الوحدات الأساسية</dt>
                <dd class="col-9">{{ $units }}</dd>

                <dt class="col-3">الحالة</dt>
                <dd class="col-9">
                    @switch($order->status)
                        @case('draft') مسودة @break
                        @case('planned') مخطط @break
                        @case('in_progress') قيد التنفيذ @break
                        @case('completed') مكتمل @break
                        @case('cancelled') ملغي @break
                        @default غير معروف
                    @endswitch
                </dd>

                <dt class="col-3">الفرع</dt>
                <dd class="col-9">{{ $order->branch->name }}</dd>

                <dt class="col-3">المُصدر</dt>
                <dd class="col-9">{{ $order->issuer->f_name }} {{ $order->issuer->l_name }}</dd>

                <dt class="col-3">تاريخ البدء</dt>
                <dd class="col-9">{{ optional($order->start_date)->format('Y-m-d') }}</dd>

                <dt class="col-3">تاريخ الانتهاء</dt>
                <dd class="col-9">{{ optional($order->end_date)->format('Y-m-d') }}</dd>

                <dt class="col-3">تاريخ الإنشاء</dt>
                <dd class="col-9">{{ $order->created_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </div>
    </div>

    {{-- Logs Section --}}
    <div id="logs-card" class="card-section d-none">
        <div class="card-header"><span>سجل التعديلات</span></div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @forelse($logs as $log)
                    <li class="mb-2">
                        <strong>{{ $log->created_at->format('Y-m-d H:i') }}</strong>
                        &mdash; {{ $log->user->f_name }} {{ $log->user->l_name }} ({{ $log->action }})
                    </li>
                @empty
                    <li class="text-muted">لا توجد سجلات بعد.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- BOM Components Table --}}
    <div class="card-section">
        <div class="card-header"><span>قائمة المواد (BOM)</span></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المادة</th>
                        <th>الكمية لكل وحدة</th>
                        <th>الإجمالي المستخدم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->bom->components as $idx => $comp)
                        @php
                            $totalUsed = round($comp->quantity * $units, 4);
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $comp->componentProduct->product_code }} – {{ $comp->componentProduct->name }}</td>
                            <td>{{ $comp->quantity }}</td>
                            <td>{{ $totalUsed }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Routing Operations Table --}}
    <div class="card-section">
        <div class="card-header"><span>خطوات المسار: {{ $order->routing->name }}</span></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>التسلسل</th>
                        <th>مركز العمل</th>
                        <th>وقت التجهيز (ساعياً)</th>
                        <th>وقت التشغيل (ساعياً)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->routing->operations as $op)
                        <tr>
                            <td>{{ $op->sequence }}</td>
                            <td>{{ $op->workCenter->name }}</td>
                            <td>{{ $op->setup_time }}</td>
                            <td>{{ $op->run_time }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
{{-- Print Invoice Modal --}}
<div class="modal fade" id="printInvoiceModal" tabindex="-1" aria-labelledby="printInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="printInvoiceModalLabel">طباعة الفاتورة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="printableArea">
                <!-- سيتم ملء المحتوى هنا ديناميكياً -->
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">طباعة</button>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(function(){
    $('#toggle-logs').on('click', function(){
        $('#logs-card').toggleClass('d-none');
    });

    @if(in_array($order->status, ['draft','planned']))
    $('#cancel-btn').on('click', function(){
        if (confirm('هل أنت متأكد من إلغاء هذا الأمر؟')) {
            $('#cancel-form').submit();
        }
    });
    @endif
        @if($order->status === 'planned')
    $('#start-btn').click(function(){
        if(confirm('هل تريد بدء تنفيذ هذا الأمر؟')){
            $('#start-form').submit();
        }
    });
    @endif
});

function printInvoice(orderId) {
    $.ajax({
        url: `/admin/production-orders/invoice/${orderId}`,
        dataType: 'json',
        beforeSend() {
            $('#loading').show();
        },
        success(data) {
            $('#printInvoiceModal').modal('show');
            $('#printableArea').html(data.view);
        },
        complete() {
            $('#loading').hide();
        },
        error(xhr) {
            console.error(xhr.responseText);
        }
    });
}
</script>
