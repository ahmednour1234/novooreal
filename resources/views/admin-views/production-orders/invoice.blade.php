
<style>
    /* Global Card Styles */
    .card-section { margin-bottom: 2rem; }
    .card-header-custom, .card-header {
        background-color: #161853;
        color: #fff;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-radius: .5rem .5rem 0 0;
    }
    .card-body {
        padding: 1.5rem;
        background: #fff;
        border-radius: 0 0 .5rem .5rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        page-break-inside: avoid;
    }
    .print-section { padding: 1rem; background: #fff; }

    /* Signature Area */
    .signatures {
        margin-top: 3rem;
        display: flex;
        justify-content: space-between;
        page-break-inside: avoid;
    }
    .sign-box {
        width: 30%;
        text-align: center;
        border-top: 1px solid #000;
        padding-top: 0.5rem;
        font-weight: 600;
    }

    /* Print Styles for A4 */
    @page {
        size: A4;
        margin: 1cm;
    }
    @media print {
        body * { visibility: hidden; }
        .print-section, .print-section * { visibility: visible; }
        .print-section {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 0;
        }
        .card-body { box-shadow: none; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { border: 1px solid #444; padding: .5rem; }
        th { background: #f0f0f0; }
        .no-print { display: none; }
        .signatures { page-break-inside: avoid; }
    }
</style>

<section class="print-section" dir="rtl">
    {{-- Detail Section --}}
    <div class="card-section">
        <div class="card-header">
            تفاصيل أمر الإنتاج
     
        </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-3">المنتج</dt>
                    <dd class="col-9">{{ $order->product->product_code }} – {{ $order->product->name }}</dd>
                    <dt class="col-3">الكمية</dt>
                    <dd class="col-9">
                        @if($order->unit == 0)
                            @php
                                $converted = $order->quantity / $order->product->unit_value;
                            @endphp
                            @if(is_float($converted) && floor($converted) != $converted)
                                {{-- نتيجة عشريّة: عرض الكمية الأصلية مع الوحدة الفرعيّة --}}
                                {{ $order->quantity }} {{ $order->product->unit->subUnits->first()?->name ?? $order->unit_label }}
                            @else
                                {{-- نتيجة صحيحة: عرض الكمية بعد التحويل مع الوحدة الأساسية --}}
                                {{ (int) $converted }} {{ $order->product->unit->unit_type ?? $order->unit_label }}
                            @endif
                        @else
                            {{ $order->quantity }} {{ $order->unit_label }}
                        @endif
                    </dd>
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
        <div class="card-header">سجل التعديلات</div>
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
        <div class="card-header">قائمة المواد (BOM)</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المادة</th>
                        <th>الكمية</th>
                        <th>الوحدة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->bom->components as $idx => $comp)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $comp->componentProduct->product_code }} – {{ $comp->componentProduct->name }}</td>
                            <td>{{ $comp->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Routing Operations Table --}}
    <div class="card-section">
        <div class="card-header">خطوات المسار: {{ $order->routing->name }}</div>
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
        <div class="card-section">
            <div class="card-header">خطوات التشغيل: {{ $order->routing->name }}</div>
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

    {{-- Signature Placeholders --}}
    <div class="signatures">
        <div class="sign-box">توقيع المدير</div>
        <div class="sign-box">توقيع أمين المصنع</div>
        <div class="sign-box">توقيع مدير خط الإنتاج</div>
    </div>
</section>

