<!-- ========== Modal تنفيذ الفاتورة (محايد + لون أساسي 71869d) ========== -->
<div class="modal fade" id="executeQuotationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
    <form
      action="{{ $quotation->quotation_type == 'service'
        ? route('admin.quotations.executequotation_service', $quotation->id)
        : route('admin.quotations.executequotation', $quotation->id) }}"
      method="POST"
      enctype="multipart/form-data"
      class="modal-content border-0 rounded-4"
      id="executeForm">
      @csrf

      <!-- ===== Styles ===== -->
      <style>
        :root{ --brand:#71869d; --line:#e7e7e7; --bg:#fafafa; --soft:#fff; }
        .exq-wrap{direction:rtl}
        .exq-hdr{background:var(--brand); color:#fff}
        .exq-body{background:var(--bg)}
        .exq-card{background:var(--soft); border:1px solid var(--line); border-radius:12px}
        .exq-title{font-weight:700}
        .exq-row{display:flex;flex-wrap:wrap;align-items:center}
        .gap-12{gap:12px}
        .even-buttons>*{flex:1 0 0;min-width:160px;height:40px}
        .btn-soft{background:#fff;border:1px solid #d8d8d8;border-radius:10px}
        .btn-soft.active{border-color:#999;background:#f3f3f3}
        .btn-soft:hover{background:#f6f6f6}
        .muted{color:#666}
        .table-clean{border:1px solid var(--line);border-radius:10px;overflow:hidden}
        .table-clean th,.table-clean td{vertical-align:middle}
        .table-clean thead th{background:#f5f5f5;border-bottom:1px solid var(--line)}
        .table-clean tbody td{background:#fff}
        .alert-soft{background:#fff;border:1px solid var(--line);border-radius:10px}
        .exq-footer{background:#f7f7f7;border-top:1px solid var(--line)}
        .note-label{color:#fff;background:var(--brand); display:inline-block; padding:.25rem .6rem; border-radius:8px; font-size:.9rem;}
        .text-brand{color:var(--brand)}
        .help-small{font-size:.85rem; color:#a94442; display:none}
        @media (max-width: 575.98px){ .even-buttons>*{min-width:130px} }
      </style>

      <!-- ===== Header ===== -->
      <div class="modal-header exq-hdr">
        <h5 class="modal-title w-100 text-center exq-title">تنفيذ الفاتورة</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- ===== Body ===== -->
      <div class="modal-body exq-body">
        <div class="container-xxl exq-wrap">

          <!-- إجمالي الفاتورة -->
          <div class="alert alert-soft text-center fw-bold mb-3">
            الإجمالي المستحق:
            <span id="dueAmountText">{{ number_format($quotation->order_amount, 2) }}</span>
            {{ \App\Models\BusinessSetting::where('key','currency')->value('value') ?? 'AED' }}
          </div>

          <div class="exq-card p-3 mb-3">
            <!-- زرّان بنفس الصف وبنفس الحجم -->
            <div class="exq-row gap-12 even-buttons mb-3">
              <button type="button" id="cashBtn"   class="btn-soft active">نقدًا</button>
              <button type="button" id="creditBtn" class="btn-soft">آجل</button>
            </div>

            <!-- hidden required by backend -->
            <input type="hidden" name="cash"                  id="cashInput"            value="1">
            <input type="hidden" name="collected_cash"        id="collectedCash"        value="{{ $quotation->order_amount }}">
            <input type="hidden" name="transaction_reference" id="transactionReference" value="{{ $quotation->order_amount }}">
            <input type="hidden" name="installment"           id="installmentInput"     value="0">

            <!-- نقدًا: جدول التحصيل -->
            <div id="cashFields" class="mt-3">
              <div class="table-responsive table-clean">
                <table class="table mb-0">
                  <thead>
                    <tr><th style="width:35%">الحقل</th><th>القيمة</th></tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="muted">الحساب</td>
                      <td>
                        <select name="payment_id" class="form-select" required>
                          @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account }}</option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td class="muted">مركز التكلفة</td>
                      <td>
                        <select name="cost_id" class="form-select" required>
                          @foreach($cost_centers as $cc)
                            <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td class="muted">مبلغ مُحصَّل</td>
                      <td>
                        <input type="number" step="0.01" min="0" class="form-control"
                               id="cashCollectedInput"
                               value="{{ $quotation->order_amount }}">
                        <small id="cashCollectedHelp" class="help-small">لا يمكن أن يزيد عن المبلغ المستحق.</small>
                      </td>
                    </tr>
                    <tr>
                      <td class="muted">مرجع العملية</td>
                      <td>
                        <input type="text" class="form-control"
                               id="transactionReferenceInput"
                               value="{{ $quotation->order_amount }}">
                      </td>
                    </tr>
                    <tr>
                      <td><span class="note-label">ملاحظة</span></td>
                      <td>
                        <textarea name="note" rows="2" class="form-control" placeholder="اكتب ملاحظة للفاتورة (اختياري)"></textarea>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- آجل: جدول + تقسيط + دفعة محصلة الآن -->
            <div id="creditFields" class="mt-3 d-none">
              <div class="table-responsive table-clean mb-3">
                <table class="table mb-0">
                  <thead>
                    <tr><th style="width:35%">الحقل</th><th>القيمة</th></tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="muted">مركز التكلفة</td>
                      <td>
                        <select name="cost_id" class="form-select">
                          @foreach($cost_centers as $cc)
                            <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td class="muted">تاريخ الدفع</td>
                      <td><input type="date" name="payment_date" class="form-control"></td>
                    </tr>
                    <tr>
                      <td class="muted">دفعة مُحصَّلة الآن (اختياري)</td>
                      <td>
                        <input type="number" step="0.01" min="0" class="form-control"
                               id="creditCollectedInput" placeholder="يمكن إدخال عربون/دفعة أولى">
                        <small class="text-muted">هذا الحقل مرن في الآجل (غير مقيّد بقيمة المستحق).</small>
                      </td>
                    </tr>
                    <tr>
                      <td><span class="note-label">ملاحظة</span></td>
                      <td>
                        <textarea name="note" rows="2" class="form-control" placeholder="اكتب ملاحظة للفاتورة (اختياري)"></textarea>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- تقسيط -->
              <div class="exq-row gap-12 even-buttons mb-3">
                <button type="button" id="installmentYes" class="btn-soft">تقسيط: نعم</button>
                <button type="button" id="installmentNo"  class="btn-soft active">تقسيط: لا</button>
              </div>

              <div id="installmentDetails" class="d-none">
                <div class="exq-card p-3 mb-3">
                  <div class="exq-row gap-12 even-buttons mb-3">
                    <button type="button" id="byInterestBtn" class="btn-soft">حسب الفائدة</button>
                    <button type="button" id="byPaymentBtn"  class="btn-soft">حسب القسط</button>
                  </div>

                  <!-- حسب الفائدة -->
                  <div id="interestForm" class="d-none">
                    <div class="row g-2">
                      <div class="col-md-4">
                        <label class="form-label">المبلغ الأساسي</label>
                        <input type="number" id="principal" class="form-control"
                               value="{{ $quotation->order_amount }}" disabled>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">نسبة الفائدة (%)</label>
                        <input type="number" id="interestPercent" name="interest_percent"
                               class="form-control" step="0.01" value="0">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">عدد الشهور</label>
                        <input type="number" id="monthsByInterest" name="duration_months"
                               class="form-control" min="1" value="1">
                      </div>
                    </div>
                  </div>

                  <!-- حسب القسط -->
                  <div id="paymentForm" class="d-none">
                    <div class="row g-2">
                      <div class="col-md-4">
                        <label class="form-label">المبلغ الأساسي</label>
                        <input type="number" id="principal2" class="form-control"
                               value="{{ $quotation->order_amount }}" disabled>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">القسط الشهري</label>
                        <input type="number" id="monthlyPayment" name="monthly_payment"
                               class="form-control" step="0.01" value="0">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">عدد الشهور</label>
                        <input type="number" id="monthsByPayment" name="duration_months"
                               class="form-control" min="1" value="1">
                      </div>
                    </div>
                    <input type="hidden" id="interestPercentHidden" name="interest_percent" value="0">
                  </div>

                  <div class="row g-2 mt-2">
                    <div class="col-md-4">
                      <label class="form-label">تاريخ البداية</label>
                      <input type="date" id="startDate" name="start_date"
                             class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                  </div>
                </div>

                <!-- جدول الأقساط -->
                <input type="hidden" id="totalPaidInput" name="total_paid_amount" value="0">
                <div class="table-responsive table-clean" id="installmentSummaryWrap" style="display:none">
                  <table class="table mb-0" id="installmentSummaryTable">
                    <thead><tr><th>#</th><th>التاريخ</th><th>المبلغ</th></tr></thead>
                    <tbody></tbody>
                    <tfoot><tr><th colspan="2" class="text-end">الإجمالي</th><th id="installmentTotalCell">0.00</th></tr></tfoot>
                  </table>
                </div>

                <!-- الضامن -->
                <div class="exq-card p-3 mt-3">
                  <h6 class="fw-bold mb-2 text-brand">الضامن</h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label">اختر ضامن</label>
                      <select id="existingGuarantor" name="guarantor_id" class="form-select">
                        @if($quotation->customer->guarantor)
                          <option value="{{ $quotation->customer->guarantor->id }}" selected>
                            {{ $quotation->customer->guarantor->name }}
                          </option>
                        @endif
                        <option value="">-- جديد --</option>
                        @foreach($guarantors as $g)
                          <option value="{{ $g->id }}">{{ $g->name }} - {{ $g->phone }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div id="guarantorForm" class="mt-2 d-none">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <label class="form-label">اسم الضامن <span class="text-danger">*</span></label>
                        <input type="text" name="guarantor_name" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">رقم الهوية</label>
                        <input type="text" name="guarantor_national_id" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">رقم الجوال</label>
                        <input type="text" name="guarantor_phone" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="guarantor_address" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">الوظيفة</label>
                        <input type="text" name="guarantor_job" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">الدخل الشهري</label>
                        <input type="number" name="guarantor_monthly_income" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">العلاقة مع العميل</label>
                        <input type="text" name="guarantor_relation" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">مرفقات الضامن</label>
                        <input type="file" name="guarantor_images[]" class="form-control" multiple accept="image/*,application/pdf">
                      </div>
                    </div>
                  </div>
                </div>

              </div> <!-- /installmentDetails -->
            </div> <!-- /creditFields -->
          </div>

        </div>
      </div>

      <!-- ===== Footer ===== -->
      <div class="modal-footer exq-footer justify-content-center py-3">
        <div class="exq-row gap-12 even-buttons" style="width:100%;max-width:420px">
          <button type="button" class="btn-soft" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn-soft fw-bold">تأكيد</button>
        </div>
      </div>

    </form>
  </div>
</div>

<!-- ========== Script ========== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openExecuteBtn');
  const modalEl = document.getElementById('executeQuotationModal');

  const cashBtn   = document.getElementById('cashBtn');
  const creditBtn = document.getElementById('creditBtn');
  const cashFields   = document.getElementById('cashFields');
  const creditFields = document.getElementById('creditFields');
  const cashInput = document.getElementById('cashInput');

  const cashCollectedInput = document.getElementById('cashCollectedInput');
  const cashCollectedHelp  = document.getElementById('cashCollectedHelp');
  const collectedCashHidden = document.getElementById('collectedCash');

  const transactionReferenceInput = document.getElementById('transactionReferenceInput');
  const transactionReferenceHidden = document.getElementById('transactionReference');

  const creditCollectedInput = document.getElementById('creditCollectedInput');

  const instYes = document.getElementById('installmentYes');
  const instNo  = document.getElementById('installmentNo');
  const instDetails = document.getElementById('installmentDetails');
  const installmentIn= document.getElementById('installmentInput');

  const existingSel = document.getElementById('existingGuarantor');
  const guarantorForm = document.getElementById('guarantorForm');

  const byInterestBtn = document.getElementById('byInterestBtn');
  const byPaymentBtn  = document.getElementById('byPaymentBtn');
  const interestForm  = document.getElementById('interestForm');
  const paymentForm   = document.getElementById('paymentForm');

  const summaryWrap = document.getElementById('installmentSummaryWrap');
  const summaryTable= document.getElementById('installmentSummaryTable');
  const summaryBody = summaryTable.querySelector('tbody');
  const totalCell   = document.getElementById('installmentTotalCell');

  const totalInput  = document.getElementById('totalPaidInput');
  const interestHid = document.getElementById('interestPercentHidden');

  const P1 = document.getElementById('principal');
  const R  = document.getElementById('interestPercent');
  const N1 = document.getElementById('monthsByInterest');
  const P2 = document.getElementById('principal2');
  const M  = document.getElementById('monthlyPayment');
  const N2 = document.getElementById('monthsByPayment');
  const startDate = document.getElementById('startDate');

  const currency = "{{ \App\Models\BusinessSetting::where('key','currency')->value('value') ?? 'AED' }}";
  const due = parseFloat(("{{ $quotation->order_amount }}").replace(/,/g,'')) || 0;

  // فتح المودال
  if (openBtn) openBtn.addEventListener('click', () => new bootstrap.Modal(modalEl).show());

  // helper
  const setActive = (btn, group=[]) => { group.forEach(b=>b.classList.remove('active')); btn.classList.add('active'); };

  // نقدًا / آجل
  const switchToCash = () => {
    cashFields.classList.remove('d-none');
    creditFields.classList.add('d-none');
    cashInput.value = '1';
    setActive(cashBtn, [creditBtn]);

    // مزامنة القيم المخفية
    collectedCashHidden.value = cashCollectedInput.value;
    transactionReferenceHidden.value = transactionReferenceInput.value;
  };
  const switchToCredit = () => {
    creditFields.classList.remove('d-none');
    cashFields.classList.add('d-none');
    cashInput.value = '2';
    setActive(creditBtn, [cashBtn]);

    // في الآجل: “دفعة محصلة الآن” اختيارية، نحدّث الهيدن عند الإدخال فقط
    collectedCashHidden.value = creditCollectedInput.value || 0;
  };
  cashBtn.addEventListener('click', switchToCash);
  creditBtn.addEventListener('click', switchToCredit);

  // منع تجاوز المبلغ في نقدًا
  const clampCashCollected = () => {
    let v = parseFloat(cashCollectedInput.value || 0);
    if (v > due) {
      cashCollectedHelp.style.display = 'block';
      v = due;
      cashCollectedInput.value = due.toFixed(2);
    } else {
      cashCollectedHelp.style.display = 'none';
    }
    if (v < 0 || isNaN(v)) { v = 0; cashCollectedInput.value = '0.00'; }
    collectedCashHidden.value = v;
  };
  cashCollectedInput.addEventListener('input', clampCashCollected);
  clampCashCollected();

  // تحديث مرجع العملية (الهيدن)
  const syncRef = () => { transactionReferenceHidden.value = transactionReferenceInput.value || ''; };
  transactionReferenceInput.addEventListener('input', syncRef);
  syncRef();

  // آجل: دفعة محصلة الآن (مرنة)
  const syncCreditCollected = () => {
    let v = parseFloat(creditCollectedInput.value || 0);
    if (v < 0 || isNaN(v)) { v = 0; creditCollectedInput.value = '0.00'; }
    collectedCashHidden.value = v;
  };
  creditCollectedInput && creditCollectedInput.addEventListener('input', syncCreditCollected);

  // تقسيط
  const enableInstallment = () => { instDetails.classList.remove('d-none'); installmentIn.value = '1'; setActive(instYes,[instNo]); };
  const disableInstallment = () => {
    instDetails.classList.add('d-none'); installmentIn.value = '0';
    summaryWrap.style.display='none'; summaryBody.innerHTML=''; totalCell.textContent='0.00';
    setActive(instNo,[instYes]);
  };
  instYes.addEventListener('click', enableInstallment);
  instNo .addEventListener('click', disableInstallment);

  // ضامن
  if (existingSel) {
    existingSel.addEventListener('change', function(){ guarantorForm.classList.toggle('d-none', this.value !== ''); });
  }

  // تبويب طريقة التقسيط
  const showInterestForm = () => { interestForm.classList.remove('d-none'); paymentForm.classList.add('d-none'); buildSchedule([]); setActive(byInterestBtn,[byPaymentBtn]); };
  const showPaymentForm  = () => { paymentForm.classList.remove('d-none'); interestForm.classList.add('d-none'); buildSchedule([]); setActive(byPaymentBtn,[byInterestBtn]); };
  byInterestBtn.addEventListener('click', showInterestForm);
  byPaymentBtn .addEventListener('click', showPaymentForm);

  // جدول الأقساط
  function buildSchedule(rows){
    if (!rows || !rows.length){ summaryWrap.style.display='none'; summaryBody.innerHTML=''; totalCell.textContent='0.00'; totalInput.value='0.00'; return; }
    summaryBody.innerHTML = rows.map((r,i)=>`<tr><td>${i+1}</td><td>${r.date}</td><td>${r.amount.toFixed(2)} ${currency}</td></tr>`).join('');
    const total = rows.reduce((s,r)=>s + r.amount, 0);
    totalCell.textContent = total.toFixed(2);
    totalInput.value = total.toFixed(2);
    summaryWrap.style.display='';
  }

  // حساب (حسب الفائدة)
  function calcInterest(){
    const p = parseFloat(P1?.value || 0);
    const r = (parseFloat(R?.value || 0) / 100) || 0;
    const n = Math.max(1, parseInt(N1?.value || 1,10));
    if (!p || !n) return buildSchedule([]);
    const total = p * (1 + r);
    const monthly = total / n;
    const baseDate = new Date(startDate?.value || new Date());
    const rows = Array.from({length:n}, (_,i)=>{ const d=new Date(baseDate); d.setMonth(d.getMonth()+i); return {date:d.toLocaleDateString('ar-EG'), amount:monthly}; });
    interestHid.value = (r*100).toFixed(2);
    buildSchedule(rows);
  }

  // حساب (حسب القسط)
  function calcPayment(){
    const p = parseFloat(P2?.value || 0);
    const m = parseFloat(M?.value  || 0);
    const n = Math.max(1, parseInt(N2?.value || 1,10));
    if (!p || !m || !n) return buildSchedule([]);
    const total = m * n;
    const interestPct = p ? ((total - p) / p) * 100 : 0;
    const baseDate = new Date(startDate?.value || new Date());
    const rows = Array.from({length:n}, (_,i)=>{ const d=new Date(baseDate); d.setMonth(d.getMonth()+i); return {date:d.toLocaleDateString('ar-EG'), amount:m}; });
    interestHid.value = interestPct.toFixed(2);
    buildSchedule(rows);
  }

  [R,N1,startDate].forEach(el=>{ el && el.addEventListener('input', calcInterest); el && el.addEventListener('change', calcInterest); });
  [M,N2,startDate].forEach(el=>{ el && el.addEventListener('input', calcPayment);  el && el.addEventListener('change', calcPayment);  });

  // افتراضيات
  switchToCash();
  disableInstallment();
});
</script>
