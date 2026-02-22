@extends('layouts.admin.app')

@section('title', 'تفاصيل عقد التقسيط')

@push('css_or_js')
  {{-- Select2 CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

  <style>
    :root{
      --ink:#0f172a; --muted:#6b7280; --brand:#0b3d91; --accent:#f59e0b;
      --grid:#e5e7eb; --paper:#ffffff; --zebra:#fbfdff; --rd:14px;
      --success:#16a34a; --danger:#dc2626; --warn:#f59e0b; --info:#0284c7;
    }

    .content.container-fluid{ max-width: 98% }
    .breadcrumb{ direction:rtl }

    /* Action bar */
    .actions-bar{
      display:flex; gap:.5rem; flex-wrap:wrap; justify-content:flex-end;
    }
    .actions-bar .btn{ border-radius:10px; font-weight:800 }

    /* Cards */
    .card{
      border-radius:var(--rd); border:1px solid var(--grid);
      box-shadow:0 10px 26px -14px rgba(2,32,71,.18);
    }
  
    .section-title{ font-size:16px; font-weight:900; color:var(--ink); margin:14px 0 8px }

    /* Summary grid */
    .summary-grid{
      display:grid; gap:10px; grid-template-columns:repeat(12, 1fr);
    }
    .sg-3{ grid-column: span 3 }
    .sg-4{ grid-column: span 4 }
    .sg-6{ grid-column: span 6 }
    .sg-12{ grid-column: 1 / -1 }
    @media (max-width:1200px){ .sg-3,.sg-4,.sg-6{ grid-column: span 6 } }
    @media (max-width:768px){ .summary-grid{ grid-template-columns:repeat(2,1fr) } .sg-3,.sg-4,.sg-6,.sg-12{ grid-column:1 / -1 } }

    .kv{
      border:1px dashed var(--grid); border-radius:12px; padding:10px 12px; background:#fcfdff;
    }
    .kv small{ color:var(--muted); display:block }
    .kv strong{ color:var(--ink) }



    /* Status badges */
    .badge{ border-radius:999px; padding:6px 10px; font-weight:800 }
    .status-paid{ background:#dcfce7; color:#166534; border:1px solid #86efac }
    .status-overdue{ background:#fee2e2; color:#991b1b; border:1px solid #fecaca }
    .status-upcoming{ background:#e0f2fe; color:#075985; border:1px solid #bae6fd }
    .status-today{ background:#fef9c3; color:#854d0e; border:1px solid #fde68a }
    .status-canceled{ background:#ffe4e6; color:#9f1239; border:1px solid #fecdd3 }

    .guarantor-img{
      width:100px; height:100px; object-fit:cover; border:1px solid var(--grid); border-radius:10px
    }

    /* Cost center recommendation highlight (optional) */
    #costCenterWrap.cc-recommended .select2-selection{
      border-color:#f59e0b !important;
      box-shadow:0 0 0 3px rgba(245,158,11,.15) !important;
    }
    #costCenterWrap .hint-optional{ color:#6b7280 }
    #costCenterWrap .hint-recommended{ color:#b45309; font-weight:700 }

    /* Print */
    @media print{
      .no-print{ display:none !important }
      .card{ box-shadow:none; border:0 }
    }

    /* Bank-style contract for print */
    .bank-paper{
      font-family:'Cairo', Arial, sans-serif; color:#111827;
      border:1.5px solid #111827; border-radius:12px; padding:24px; background:#fff;
    }
    .bank-head{
      display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px;
      border-bottom:2px solid #0b3d91; padding-bottom:8px;
    }
    .bank-head .brand{ text-align:center }
    .brand h2{ margin:0; color:#0b3d91; font-weight:900 }
    .terms ol{ padding-inline-start: 20px }
    .signs{ display:flex; gap:10px; margin-top:16px }
    .sign-box{
      flex:1; border:1px dashed #9ca3af; border-radius:10px; padding:10px; min-height:120px; text-align:center
    }
  </style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- Breadcrumb --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('admin.depreciation.index') }}" class="text-primary">
            {{ \App\CPU\translate('عقود الأقساط') }}
          </a>
        </li>
        <li class="breadcrumb-item active">
          عقد #{{ $contract->id }}
        </li>
      </ol>
    </nav>
  </div>

  {{-- Actions --}}
  <div class="actions-bar no-print mb-3">
    <button type="button" onclick="printContract()" class="btn btn-primary">
      <i class="tio-file-text"></i> طباعة العقد
    </button>
    <button type="button" onclick="printInstallments()" class="btn btn-outline-secondary">
      <i class="tio-print"></i> طباعة جدول الأقساط
    </button>
  </div>

  <div id="printable-area">
    {{-- Contract Summary --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="mb-0">عقد تقسيط رقم #{{ $contract->id }}</h5>
        @switch($contract->status)
          @case('active')     <span class="badge status-paid">مفعل</span> @break
          @case('cancelled')  <span class="badge status-canceled">ملغي</span> @break
          @default            <span class="badge status-upcoming">غير معروف</span>
        @endswitch
      </div>
      <div class="card-body">
        <div class="summary-grid">
          <div class="sg-4 kv">
            <small>العميل</small>
            <strong>{{ $contract->customer->name ?? '—' }}</strong>
          </div>
          <div class="sg-4 kv">
            <small>رقم الفاتورة</small>
            <strong>#{{ $contract->order_id }}</strong>
          </div>
          <div class="sg-4 kv">
            <small>كاتب العقد</small>
            <strong>{{ $contract->order->seller->email ?? '—' }}</strong>
          </div>

          <div class="sg-3 kv">
            <small>تاريخ البداية</small>
            <strong>
              @if($contract->start_date)
                {{ \Carbon\Carbon::parse($contract->start_date)->timezone('Africa/Cairo')->translatedFormat('d M Y') }}
              @else
                —
              @endif
            </strong>
          </div>
          <div class="sg-3 kv">
            <small>المدة</small>
            <strong>{{ $contract->duration_months }} شهر</strong>
          </div>
          <div class="sg-3 kv">
            <small>نسبة الفائدة</small>
            <strong>{{ $contract->interest_percent }}%</strong>
          </div>
          <div class="sg-3 kv">
            <small>المبلغ الإجمالي</small>
            <strong>{{ number_format($contract->total_amount, 2) }}</strong>
          </div>

          <div class="sg-6 kv">
            <small>تاريخ الإنشاء</small>
            <strong>
              {{ optional($contract->created_at)->timezone('Africa/Cairo')->translatedFormat('d M Y، h:mm a') }}
              <span class="text-muted d-block" style="font-weight:600">{{ optional($contract->created_at)->diffForHumans() }}</span>
            </strong>
          </div>
          <div class="sg-6 kv">
            <small>الحالة</small>
            <strong>
              @if($contract->status === 'active') مفعل
              @elseif($contract->status === 'cancelled') ملغي
              @else {{ $contract->status ?? 'غير معروف' }}
              @endif
            </strong>
          </div>
        </div>
      </div>
    </div>

    {{-- Installments Table --}}
    <h5 class="section-title">جدول الأقساط</h5>
    <div class="table-responsive" id="installments-wrap">
      <table id="installmentsTable" class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>تاريخ الاستحقاق</th>
            <th>المبلغ</th>
            <th>المدفوع</th>
            <th>الحالة</th>
            <th class="no-print">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @php use Carbon\Carbon; $now = Carbon::now(); @endphp
          @forelse($contract->scheduledInstallments as $i => $inst)
            @php
              $due   = Carbon::parse($inst->due_date);
              $stat  = $inst->status;
              $over  = in_array($stat, ['pending','partial']) && $due->lt($now);
              $today = in_array($stat, ['pending','partial']) && $due->isToday();
              $upc   = in_array($stat, ['pending','partial']) && $due->gt($now);
              $late  = $over ? $due->diffInDays($now) : 0;
            @endphp
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $due->timezone('Africa/Cairo')->format('Y-m-d') }}</td>
              <td>{{ number_format($inst->amount, 2) }}</td>
              <td>{{ number_format($inst->purchased_amount, 2) }}</td>
              <td>
                @if($stat==='paid')
                  <span class="badge status-paid">مدفوع</span>
                @elseif($today)
                  <span class="badge status-today">يستحق اليوم</span>
                @elseif($over)
                  <span class="badge status-overdue">متأخر {{ $late }} يوم</span>
                @elseif($upc)
                  <span class="badge status-upcoming">لم يحن موعده</span>
                @elseif($stat==='cancelled')
                  <span class="badge status-canceled">ملغي</span>
                @else
                  <span class="badge" style="background:#fff; border:1px solid #e5e7eb; color:#111827">غير معروف</span>
                @endif
              </td>
              <td class="no-print">
                @if($stat==='pending'||$stat==='partial')
                  <button type="button" class="btn btn-success btn-sm" onclick="openPaymentModal({{ $inst->id }}, {{ max(0, $inst->amount - $inst->purchased_amount) }})">
                    <i class="tio-dollar-outlined"></i> دفع قسط
                  </button>
                @else
                  <span class="text-muted">غير متاح</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">لا توجد أقساط</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Guarantor --}}
    @if($contract->guarantor)
      <h5 class="section-title">تفاصيل الضامن</h5>
      <div class="card shadow-sm mb-4">
        <div class="card-body row g-3">
          <div class="col-md-6"><strong>الاسم:</strong> {{ $contract->guarantor->name }}</div>
          <div class="col-md-6"><strong>رقم الهوية:</strong> {{ $contract->guarantor->national_id }}</div>
          <div class="col-md-6"><strong>الهاتف:</strong> {{ $contract->guarantor->phone }}</div>
          <div class="col-md-6"><strong>العنوان:</strong> {{ $contract->guarantor->address }}</div>
          <div class="col-md-6"><strong>الوظيفة:</strong> {{ $contract->guarantor->job }}</div>
          <div class="col-md-6"><strong>دخل شهري:</strong> {{ number_format($contract->guarantor->monthly_income, 2) }}</div>
          <div class="col-md-6"><strong>العلاقة:</strong> {{ $contract->guarantor->relation }}</div>
          @php $imgs = json_decode($contract->guarantor->images, true); @endphp
          @if(is_array($imgs) && count($imgs))
            <div class="col-12">
              <h6 class="mt-2">المرفقات:</h6>
              <div class="d-flex flex-wrap gap-3 mt-2">
                @foreach($imgs as $img)
                  <a href="{{ asset($img) }}" target="_blank">
                    <img src="{{ asset($img) }}" alt="مرفق" class="guarantor-img">
                  </a>
                @endforeach
              </div>
            </div>
          @endif
        </div>
      </div>
    @endif
  </div>

  {{-- Payment Modal --}}
  <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form method="POST" action="{{ route('admin.installments.pay', $contract->id) }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="installment_id" id="installmentIdInput">
          <div class="modal-header rounded-top">
            <h5 class="modal-title" id="paymentModalLabel">دفع قسط</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body row g-3">
            <div class="col-md-6">
              <label class="form-label">من حساب</label>
              <select name="account_id" id="fromAccountSelect" class="form-select select2" required>
                @foreach($accounts as $acc)
                  <option value="{{ $acc->id }}">{{ $acc->account }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">إلى حساب</label>
              <select name="account_id_to" id="toAccountSelect" class="form-select select2" required>
                @foreach($accounts_to as $acc)
                  {{-- ننقل قيمة cost_center كـ data-attribute --}}
                  <option value="{{ $acc->id }}" data-requires-cc="{{ (int)($acc->cost_center ?? 0) }}">
                    {{ $acc->account }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- مركز التكلفة (ظاهر دائمًا + اختياري) --}}
            <div class="col-md-6" id="costCenterWrap">
              <label class="form-label">مركز التكلفة <span class="text-muted">(اختياري)</span></label>
              <select name="cost_center_id" id="costCenterSelect" class="form-select select2">
                <option value="">{{ \App\CPU\translate('اختر مركز تكلفة') }}</option>
                @foreach($cost_centers as $cc)
                  <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                @endforeach
              </select>
              <small id="costCenterHint" class="hint-optional">اختياري.</small>
            </div>

            <div class="col-md-6">
              <label class="form-label">المبلغ</label>
              <input type="number" name="payment_amount" id="paymentAmountInput" class="form-control" required min="0.01" step="0.01">
              <small class="text-muted">المبلغ المتبقي لهذا القسط: <span id="remainHint">—</span></small>
            </div>

            <div class="col-md-6">
              <label class="form-label">تاريخ الاستلام</label>
              <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">الوصف</label>
              <input type="text" name="description" class="form-control" placeholder="وصف الدفع" required>
            </div>

            <div class="col-12">
              <label class="form-label">صورة الإيصال</label>
              <input type="file" name="receipt" accept="image/*" class="form-control">
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">تأكيد الدفع</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection

@push('css_or_js')
  {{-- Select2 JS --}}
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    /**
     * تحديث المظهر والتنبيه لحقل مركز التكلفة بدون جعله إلزامي أو إخفاؤه.
     */
    function updateCostCenterRequirement(){
      const toSelect = document.getElementById('toAccountSelect');
      const ccWrap   = document.getElementById('costCenterWrap');
      const hint     = document.getElementById('costCenterHint');

      if(!toSelect || !ccWrap || !hint) return;

      // قراءة الخيار المختار (متوافق مع select2)
      let selectedOpt = toSelect.querySelector('option:checked');
      if (!selectedOpt && window.$) {
        selectedOpt = $(toSelect).find('option:selected').get(0);
      }

      const requires = selectedOpt && (String(selectedOpt.dataset.requiresCc) === '1' || String(selectedOpt.dataset.requiresCc).toLowerCase() === 'true');

      // لا نجعل الحقل required أبداً، فقط تنبيه مرئي
      ccWrap.classList.toggle('cc-recommended', !!requires);
      if(requires){
        hint.textContent = 'يُنصح بتحديد مركز تكلفة لهذا الحساب.';
        hint.classList.remove('hint-optional');
        hint.classList.add('hint-recommended');
      }else{
        hint.textContent = 'اختياري.';
        hint.classList.add('hint-optional');
        hint.classList.remove('hint-recommended');
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      // Select2 (خارج المودال)
      if (window.$ && $.fn.select2) {
        $('.select2').not('#paymentModal .select2').select2({
          placeholder:'اختر...',
          allowClear:true,
          dir:'rtl',
          width:'100%'
        });
      }

      // Select2 داخل المودال مع dropdownParent
      if (window.$ && $.fn.select2) {
        $('#paymentModal .select2').select2({
          placeholder:'اختر...',
          allowClear:true,
          dir:'rtl',
          width:'100%',
          dropdownParent: $('#paymentModal')
        });

        // اربط change + select2:select + select2:clear لضمان تحديث التنبيه فورًا
        $('#toAccountSelect').on('change select2:select select2:clear', function(){
          updateCostCenterRequirement();
        });
      } else {
        // احتياطي إن لم تتوفر jQuery/Select2
        const toSelect = document.getElementById('toAccountSelect');
        if (toSelect){
          toSelect.addEventListener('change', updateCostCenterRequirement);
        }
      }

      // تهيئة المودال
      const modalEl = document.getElementById('paymentModal');
      window.paymentModal = new bootstrap.Modal(modalEl);

      modalEl.addEventListener('shown.bs.modal', function(){
        updateCostCenterRequirement(); // تحديث الحالة عند الفتح
      });

      // فتح المودال مع ضبط القيم
      window.openPaymentModal = function(installmentId, remaining){
        document.getElementById('installmentIdInput').value = installmentId;
        document.getElementById('remainHint').textContent = (remaining !== undefined) ? Number(remaining).toFixed(2) : '—';
        if(remaining !== undefined){
          const amountInput = document.getElementById('paymentAmountInput');
          amountInput.value = Number(remaining).toFixed(2);
          amountInput.setAttribute('max', Number(remaining).toFixed(2));
        }
        updateCostCenterRequirement();
        window.paymentModal.show();
      };

      // طباعة العقد (نمط بنك)
      window.printContract = function(){
        const shopName   = `{{ \App\Models\BusinessSetting::where(['key'=>'shop_name'])->first()->value ?? '' }}`;
        const shopAddr   = `{{ \App\Models\BusinessSetting::where(['key'=>'shop_address'])->first()->value ?? '' }}`;
        const shopPhone  = `{{ \App\Models\BusinessSetting::where(['key'=>'shop_phone'])->first()->value ?? '' }}`;
        const shopEmail  = `{{ \App\Models\BusinessSetting::where(['key'=>'shop_email'])->first()->value ?? '' }}`;
        const logoPath   = `{{ asset('storage/app/public/shop/' . (\App\Models\BusinessSetting::where(['key'=>'shop_logo'])->first()->value ?? '')) }}`;

        const partyA = `{{ $contract->customer->name ?? '—' }}`;
        const partyB = shopName;

        const metaHtml = `
          <table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:13px">
            <tr>
              <td style="border:1px solid #e5e7eb; padding:8px;"><strong>رقم العقد:</strong> #{{ $contract->id }}</td>
              <td style="border:1px solid #e5e7eb; padding:8px;"><strong>العميل:</strong> ${partyA}</td>
              <td style="border:1px solid #e5e7eb; padding:8px;"><strong>المبلغ الإجمالي:</strong> {{ number_format($contract->total_amount, 2) }}</td>
            </tr>
            <tr>
              <td style="border:1px solid #e5e7eb; padding:8px;"><strong>تاريخ البداية:</strong> {{ \Carbon\Carbon::parse($contract->start_date)->translatedFormat('d M Y') }}</td>
              <td style="border:1px solid #e5e7eb; padding:8px;"><strong>المدة:</strong> {{ $contract->duration_months }} شهر</td>
              <td style="border:1px solid #e5e7eb; padding:8px;"><strong>الفائدة:</strong> {{ $contract->interest_percent }}%</td>
            </tr>
          </table>
        `;

        const termsHtml = `
          <div class="terms" style="margin-top:10px">
            <h3 style="margin:8px 0; color:#0b3d91">الشروط والأحكام</h3>
            <ol>
              <li>يلتزم الطرف الأول (${partyA}) بسداد الأقساط في مواعيدها المحددة دون تأخير.</li>
              <li>يُراعى احتساب فائدة متفق عليها بنسبة {{ $contract->interest_percent }}% على الرصيد وفق شروط العقد.</li>
              <li>في حال التأخر عن السداد، قد تُطبق غرامات أو إجراءات حسب الأنظمة المعمول بها.</li>
              <li>يحق للطرف الثاني (${partyB}) إلغاء أو تعليق العقد عند الإخلال بالشروط.</li>
              <li>أي تعديل على العقد يجب أن يكون بموافقة الطرفين خطيًا.</li>
            </ol>
          </div>
        `;

        const signsHtml = `
          <div class="signs">
            <div class="sign-box">
              <strong>توقيع العميل</strong>
              <div style="margin-top:14px">..................................................</div>
            </div>
            <div class="sign-box">
              <strong>توقيع البائع/الشركة</strong>
              <div style="margin-top:14px">..................................................</div>
            </div>
            <div class="sign-box">
              <strong>الختم</strong>
            </div>
          </div>
        `;

        const scheduleHtml = document.getElementById('installments-wrap').innerHTML;

        const w = window.open('', '', 'width=1200,height=1400');
        w.document.write(`
          <!DOCTYPE html>
          <html lang="ar" dir="rtl">
          <head>
            <meta charset="UTF-8">
            <title>عقد تقسيط #{{ $contract->id }}</title>
            <style>
              @page{ size:A4; margin:12mm }
              body{ font-family:'Cairo', Arial, sans-serif; background:#fff; color:#111827 }
              table{ width:100%; border-collapse:collapse }
              th,td{ text-align:right }
              .bank-paper{ border:1.5px solid #111827; border-radius:12px; padding:24px; }
              .bank-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; border-bottom:2px solid #0b3d91; padding-bottom:8px; }
              .brand h2{ margin:0; color:#0b3d91; font-weight:900 }
              .meta td{ border:1px solid #e5e7eb; padding:8px }
              thead th{ background:#fff7e6; border-bottom:2px solid #e5e7eb; padding:8px }
              tbody td{ border-bottom:1px solid #e5e7eb; padding:8px }
              .no-print{ display:none }
            </style>
          </head>
          <body onload="window.print(); window.onafterprint = () => window.close();">
            <div class="bank-paper">
              <div class="bank-head">
                <div>
                  <div><strong>العنوان:</strong> ${shopAddr}</div>
                  <div><strong>الهاتف:</strong> ${shopPhone}</div>
                  <div><strong>البريد:</strong> ${shopEmail}</div>
                </div>
                <div class="brand">
                  <img src="${logoPath}" alt="Logo" style="max-height:70px; display:block; margin:0 auto 6px">
                  <h2>${shopName}</h2>
                  <div style="color:#334155">عقد تقسيط</div>
                </div>
                <div style="text-align:left"></div>
              </div>

              ${metaHtml}
              ${termsHtml}

              <h3 style="margin:12px 0; color:#0b3d91">جدول الأقساط</h3>
              ${scheduleHtml}

              ${signsHtml}
            </div>
          </body>
          </html>
        `);
        w.document.close();
      };

      // طباعة جدول الأقساط فقط
      window.printInstallments = function(){
        const scheduleHtml = document.getElementById('installments-wrap').innerHTML;
        const w = window.open('', '', 'width=1200,height=1400');
        w.document.write(`
          <!DOCTYPE html>
          <html lang="ar" dir="rtl">
          <head>
            <meta charset="UTF-8">
            <title>جدول الأقساط - عقد #{{ $contract->id }}</title>
            <style>
              @page{ size:A4; margin:12mm }
              body{ font-family:'Cairo', Arial, sans-serif; background:#fff; color:#111827 }
              table{ width:100%; border-collapse:collapse; font-size:13px }
              thead th{ background:#fff7e6; border-bottom:2px solid #e5e7eb; padding:8px; text-align:right }
              td{ border-bottom:1px solid #e5e7eb; padding:8px; text-align:right; vertical-align:middle }
              .no-print{ display:none }
              h2{ text-align:center; color:#0b3d91; margin:0 0 10px }
            </style>
          </head>
          <body onload="window.print(); window.onafterprint = () => window.close();">
            <h2>جدول الأقساط — عقد #{{ $contract->id }}</h2>
            ${scheduleHtml}
          </body>
          </html>
        `);
        w.document.close();
      };
    });
  </script>
@endpush
