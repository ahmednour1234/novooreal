{{-- resources/views/admin/purchase_invoices/create.blade.php --}}
@extends('layouts.admin.app')

@section('content')
<style>
    :root{
        --bg:#f6f8fb; --card:#ffffff; --ink:#243447; --muted:#6b7280; --brand:#2563eb;
        --line:#e5e7eb; --ok:#16a34a; --warn:#f59e0b; --danger:#dc2626; --soft:#f3f4f6;
        --pad:16px; --radius:12px;
    }
    body{ background:var(--bg); }

    .page-wrap{ max-width:1200px; margin:16px auto; padding:0 8px; }
    .card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius); box-shadow:0 1px 2px rgba(0,0,0,.04); margin-bottom:12px; }
    .card-h{ padding:14px var(--pad); border-bottom:1px solid var(--line); font-weight:700; color:var(--ink); }
    .card-b{ padding:var(--pad); }

    .grid{ display:grid; gap:12px; }
    .grid-2{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .grid-3{ grid-template-columns:repeat(3,minmax(0,1fr)); }
    .grid-4{ grid-template-columns:repeat(4,minmax(0,1fr)); }

    /* صف الكاردين السفلي: نفس العرض/الارتفاع */
    .layout-2{
        display:grid; gap:12px;
        grid-template-columns: repeat(2, minmax(0,1fr));
        align-items: stretch;
    }
    .layout-2 > .card{ display:flex; flex-direction:column; height:100%; }
    .layout-2 > .card .card-b{ flex:1; display:flex; flex-direction:column; }

    @media (max-width: 991.98px){
        .grid-4{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .layout-2{ grid-template-columns:1fr; }
    }
    @media (max-width: 575.98px){
        .grid-2,.grid-3,.grid-4{ grid-template-columns:1fr; }
    }

    label{ font-size:13px; color:var(--muted); margin-bottom:6px; display:block; }
    .form-control, .select2-container--default .select2-selection--single{
        border:1px solid var(--line) !important; border-radius:10px !important; height:40px;
        padding:8px 10px; background:#fff; font-size:14px; color:var(--ink);
    }
    textarea.form-control{ height:auto; min-height:100px; resize:vertical; }
    .small-hint{ font-size:12px; color:var(--muted); }

    .btn{ border:1px solid transparent; border-radius:10px; padding:9px 14px; font-weight:600; font-size:14px; transition:.15s ease-in-out; line-height:1.2; }
    .btn-soft{ background:var(--soft); color:var(--ink); border-color:var(--line); }
   
    .btn-link{ background:transparent; color:var(--brand); padding:0; border:none; }
    .btn-disabled{ opacity:.6; pointer-events:none; }

    .table-responsive{ border-top:1px solid var(--line); }
    table{ width:100%; border-collapse:separate; border-spacing:0; }
    thead th{ background:#fafafa; color:#111827; font-size:13px; font-weight:700; padding:10px; border-bottom:1px solid var(--line); position:sticky; top:0; z-index:1; }
    tbody td{ padding:8px; border-bottom:1px solid var(--line); vertical-align:middle; }

    .badge-line{ display:flex; flex-wrap:wrap; gap:8px; }
    .badge-soft{
        display:inline-flex; align-items:center; gap:6px; padding:6px 10px;
        background:#f8fafc; border:1px solid var(--line); border-radius:999px; font-size:12px; color:#111827;
    }

    .img-preview{ width:120px; height:120px; border-radius:10px; border:1px solid var(--line); object-fit:cover; background:#f9fafb; }

    .sum-line{ display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px dashed var(--line); font-size:14px; }
    .sum-line:last-child{ border-bottom:none; }
    .sum-amount{ font-weight:700; color:#111827; }

    .row-split{ display:grid; gap:10px; grid-template-columns:1fr 1fr; }
    @media (max-width: 575.98px){ .row-split{ grid-template-columns:1fr; } }
</style>

<div class="content container-fluid">

{{-- تجهيز قوائم الحسابات لسندات الصرف: مصروفات نهائية فقط --}}
@php
    use App\Models\Account;

    // كل حسابات المصروفات التي ليس لها أبناء (Leaf)
    $expenseLeafAccounts = Account::query()
        ->whereRaw('LOWER(account_type) = ?', ['expense'])
        ->whereNotIn('id', Account::query()->select('parent_id')->whereNotNull('parent_id'))
        ->orderBy('account')
        ->get();

    // إن كنت تحتاج باقي القوائم (الدفع من حساب... إلخ)
    $allAccounts = Account::orderBy('account')->get();
@endphp


    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                        <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.costcenter.add') }}" class="text-primary">{{ \App\CPU\translate('فاتورة مشتريات') }}</a>
                </li>
            </ol>
        </nav>
    </div>

    {{-- بيانات الفاتورة --}}
    <div class="card">
        <div class="card-h">بيانات الفاتورة</div>
        <div class="card-b">
            <div class="grid grid-4">
                <div>
                    <label for="supplier">اختر المورد</label>
                    <select id="supplier" class="select2" onchange="showSupplierDetails(this)">
                        <option value="">-- اختر المورد --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                data-name="{{ $supplier->name }}"
                                data-phone="{{ $supplier->mobile }}"
                                data-address="{{ $supplier->address }}"
                                data-tax_number="{{ $supplier->tax_number }}"
                                data-c_history="{{ $supplier->c_history }}">
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="invoice_date">تاريخ الفاتورة</label>
                    <input type="date" id="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="status">حالة الفاتورة</label>
                    <select id="status" class="form-control">
                        <option value="approved">معتمدة</option>
                    </select>
                    <div id="status-hint" class="small-hint mt-1"></div>
                </div>
                <div>
                    <label for="otherExpenses">مصاريف إضافية (شحن/خدمات)</label>
                    <input type="number" id="otherExpenses" class="form-control" step="0.01" min="0" value="0" oninput="updateFinalTotal(); syncOtherDefault();">
                    <div class="small-hint">تضاف على الإجمالي النهائي. يمكن دفعها الآن من خلال سند صرف.</div>
                </div>
            </div>

            <div class="grid grid-2" style="margin-top:12px;">
                <div>
                    <label for="invoiceImage">صورة الفاتورة (اختياري)</label>
                    <div class="grid grid-2" style="align-items:center;">
                        <input type="file" id="invoiceImage" class="form-control" accept="image/*">
                        <img id="invoiceImagePreview" class="img-preview" alt="">
                    </div>
                    <div class="small-hint">صيغة صورة فقط (JPG/PNG).</div>
                </div>
                <div>
                    <label for="note">ملاحظة</label>
                    <textarea id="note" class="form-control" placeholder="اكتب أي ملاحظات داخلية..."></textarea>
                </div>
            </div>

            {{-- بيانات المورد كبادجات --}}
            <div id="supplier-details" class="card" style="display:none; margin-top:12px;">
                <div class="card-b">
                    <div id="supplier-info" class="badge-line"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- المنتجات --}}
    <div class="card">
        <div class="card-h">المنتجات</div>
        <div class="card-b">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="min-width:220px">المنتج</th>
                        <th>كود</th>
                        <th>الوحدة</th>
                        <th style="min-width:110px">الكمية</th>
                        <th style="min-width:130px">السعر/وحدة</th>
                        <th style="min-width:120px">قيمة الضريبة</th>
                        <th style="min-width:140px">السعر شامل الضريبة</th>
                        <th style="min-width:120px">الخصم/وحدة</th>
                        <th style="min-width:140px">الإجمالي (بعد الخصم)</th>
                        <th style="width:1%"></th>
                    </tr>
                    </thead>
                    <tbody id="product-rows">
                    <tr>
                        <td>
                            <select name="product_id" class="select2 form-control" onchange="setProductData(this)">
                                <option value="">-- اختر المنتج --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-code="{{ $product->product_code }}"
                                            data-tax="{{ $product->taxe->amount ?? 0 }}">
                                        {{ $product->name }} - {{ $product->product_code }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><span class="product-code"></span></td>
                        <td>
                            <select name="unit" class="select2 form-control" required>
                                <option value="">-- اختر الوحدة --</option>
                                <option value="1">كبري</option>
                                <option value="0">صغري</option>
                            </select>
                        </td>
                        <td><input type="number" name="quantity" class="form-control" step="1" min="1" onchange="calculateRowTotal(this)"></td>
                        <td><input type="number" name="price" class="form-control" step="0.01" min="0" onchange="calculateRowTotal(this)"></td>
                        <td><input type="number" name="tax" class="form-control" step="0.01" min="0" readonly></td>
                        <td><input type="number" name="price_incl_tax" class="form-control" step="0.01" readonly></td>
                        <td><input type="number" name="discount" class="form-control" step="0.01" min="0" value="0" onchange="calculateRowTotal(this)"></td>
                        <td><input type="number" name="row_total" class="form-control" step="0.01" readonly></td>
                        <td class="text-center"><button type="button" class="btn btn-danger" onclick="removeRow(this)">حذف</button></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
                <button type="button" class="btn btn-soft" onclick="addRow()">+ صف جديد</button>
                <button type="button" class="btn btn-soft" onclick="location.reload()">تحديث الشاشة</button>
                <button type="button" class="btn btn-danger" onclick="cancelInvoice()">إلغاء الفاتورة</button>
            </div>
        </div>
    </div>

    {{-- صف الكاردين (نفس العرض/الارتفاع) --}}
    <div class="layout-2">
        {{-- يسار: ملخص الفاتورة --}}
        <div class="card">
            <div class="card-h">ملخص الفاتورة</div>
            <div class="card-b">
                <div class="sum-line"><span>إجمالي أسعار المنتجات</span><span id="subtotal" class="sum-amount">0.00</span></div>
                <div class="sum-line"><span>إجمالي الخصم</span><span id="totalDiscount" class="sum-amount">0.00</span></div>
                <div class="sum-line"><span>إجمالي الضرائب</span><span id="totalTax" class="sum-amount">0.00</span></div>
                <div class="sum-line"><span>الإجمالي قبل المصاريف</span><span id="grandTotal" class="sum-amount">0.00</span></div>
                <div class="sum-line"><span>المصاريف الإضافية</span><span class="sum-amount" id="otherShow">0.00</span></div>
                <div class="sum-line" style="border-bottom:none"><span>الإجمالي النهائي</span><span id="finalTotal" class="sum-amount">0.00</span></div>
            </div>
        </div>

        {{-- يمين: إعدادات الدفع والتنفيذ --}}
        <div class="card">
            <div class="card-h">إعدادات الدفع والتنفيذ</div>
            <div class="card-b">
                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <label class="mb-0" style="display:flex; gap:8px; align-items:center;">
                        <input type="checkbox" id="isCash" onclick="toggleCashHint()"> دفع كاش
                    </label>
                    <div id="cash-hint" class="small-hint" style="display:none;">عند اختيار الكاش يجب أن يساوي المبلغ المدفوع <strong>الإجمالي النهائي</strong>.</div>
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:auto;">
                    <button type="button" id="btnPayment" class="btn btn-secondary" onclick="openPaymentModal()">إدخال بيانات الدفع</button>
                    <button type="button" class="btn btn-primary" onclick="executeInvoice()">تنفيذ الفاتورة</button>
                </div>

                <div class="small-hint mt-3">
                    في حالة الأجل يمكنك تحديد دفعة حالًا، كما يمكنك دفع المصاريف الإضافية الآن وعمل سند صرف لها.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal الدفع --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#fff; color:#000;">
        <h5 class="modal-title" id="paymentModalLabel">بيانات الدفع</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق" style="color:#000;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

         {{-- كاش --}}
        <div id="cashFields" style="display:none;">
           <div class="form-group">
              <label for="account">الحساب (الدفع منه)</label>
              <select class="form-control" id="account" onchange="toggleCcByAccount(this,'cash_costcenter_wrap')">
                 <option value="">-- اختر الحساب --</option>
                 @foreach($accounts as $account)
                   <option value="{{ $account->id }}" data-costcenter="{{ $account->cost_center ?? 0 }}">{{ $account->account }}</option>
                 @endforeach
              </select>
           </div>

           <div id="cash_costcenter_wrap" class="form-group" style="display:none;">
              <label for="cash_costcenter">مركز التكلفة</label>
              <select id="cash_costcenter" class="form-control">
                 <option value="">-- اختر مركز التكلفة --</option>
                 @foreach($cost_centers as $center)
                   <option value="{{ $center->id }}">{{ $center->name }}</option>
                 @endforeach
              </select>
              <div class="small-hint">مطلوب لهذا الحساب.</div>
           </div>

           <div class="form-group">
              <label for="cash_payment_amount">المبلغ المدفوع (يساوي الإجمالي النهائي)</label>
              <input type="number" class="form-control" id="cash_payment_amount" step="0.01" min="0">
           </div>
           <div class="form-group">
              <label for="receipt_image">رفع صورة الإيصال (اختياري)</label>
              <input type="file" class="form-control" id="receipt_image" accept="image/*">
           </div>
        </div>

        {{-- آجل --}}
        <div id="creditFields" style="display:none;">

           <div class="card" style="border:1px dashed var(--line);">
             <div class="card-h" style="border-bottom:none;">دفعة مقدمة الآن (اختياري)</div>
             <div class="card-b">
                <div class="row-split">
                   <div>
                      <label for="credit_now_amount">المبلغ الآن</label>
                      <input type="number" id="credit_now_amount" class="form-control" step="0.01" min="0" placeholder="اختياري">
                   </div>
                   <div>
                      <label for="credit_pay_from_account">الدفع من حساب</label>
                      <select id="credit_pay_from_account" class="form-control" onchange="toggleCcByAccount(this,'credit_pay_from_cc_wrap')">
                         <option value="">-- اختر الحساب --</option>
                         @foreach($accounts as $account)
                           <option value="{{ $account->id }}" data-costcenter="{{ $account->cost_center ?? 0 }}">{{ $account->account }}</option>
                         @endforeach
                      </select>
                   </div>
                </div>
                <div id="credit_pay_from_cc_wrap" class="form-group" style="display:none; margin-top:10px;">
                    <label for="credit_pay_from_cc">مركز التكلفة</label>
                    <select id="credit_pay_from_cc" class="form-control">
                       <option value="">-- اختر مركز التكلفة --</option>
                       @foreach($cost_centers as $center)
                         <option value="{{ $center->id }}">{{ $center->name }}</option>
                       @endforeach
                    </select>
                    <div class="small-hint">مطلوب لهذا الحساب.</div>
                </div>
                <div class="small-hint">اترك المبلغ فارغًا لو مش هتدفع حاجة دلوقتي.</div>
             </div>
           </div>

           <div class="card" style="border:1px dashed var(--line); margin-top:10px;">
             <div class="card-h" style="border-bottom:none;">المصاريف الإضافية الآن (اختياري)</div>
             <div class="card-b">
                <label style="display:flex; gap:8px; align-items:center;">
                    <input type="checkbox" id="pay_other_now" onchange="toggleOtherNow()"> دفع المصاريف الإضافية الآن
                </label>

                <div id="other_now_wrap" style="display:none; margin-top:10px;">
                    <div class="row-split">
                        <div>
                            <label for="other_now_amount">مبلغ المصاريف (افتراضي = قيمة المصاريف الإضافية)</label>
                            <input type="number" id="other_now_amount" class="form-control" step="0.01" min="0">
                        </div>
                        <div>
                            <label>جهة الدفع</label>
                            <div style="display:flex; gap:12px;">
                                <label><input type="radio" name="other_to" value="supplier" checked onchange="toggleOtherTo()"> للمورد</label>
                                <label><input type="radio" name="other_to" value="other" onchange="toggleOtherTo()"> حساب آخر (سند صرف)</label>
                            </div>
                        </div>
                    </div>

                    {{-- للمورد --}}
                    <div id="other_to_supplier" style="margin-top:10px;">
                        <div class="row-split">
                            <div>
                                <label for="other_supplier_pay_from_account">الدفع من حساب</label>
                                <select id="other_supplier_pay_from_account" class="form-control" onchange="toggleCcByAccount(this,'other_supplier_cc_wrap')">
                                   <option value="">-- اختر الحساب --</option>
                                   @foreach($accounts as $account)
                                     <option value="{{ $account->id }}" data-costcenter="{{ $account->cost_center ?? 0 }}">{{ $account->account }}</option>
                                   @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="other_supplier_note">بيان</label>
                                <input type="text" id="other_supplier_note" class="form-control" placeholder="بيان الدفع (اختياري)">
                            </div>
                        </div>
                        <div id="other_supplier_cc_wrap" class="form-group" style="display:none; margin-top:10px;">
                            <label for="other_supplier_cc">مركز التكلفة</label>
                            <select id="other_supplier_cc" class="form-control">
                               <option value="">-- اختر مركز التكلفة --</option>
                               @foreach($cost_centers as $center)
                                 <option value="{{ $center->id }}">{{ $center->name }}</option>
                               @endforeach
                            </select>
                            <div class="small-hint">مطلوب لهذا الحساب.</div>
                        </div>
                    </div>

                    {{-- سند صرف لحساب آخر (الحساب الدائن = مصروف نهائي فقط) --}}
                    <div id="other_to_other" style="display:none; margin-top:10px;">
                        <div class="row-split">
                            <div>
                                <label for="other_creditor_account">الحساب الدائن (مصروف نهائي)</label>
                                <select id="other_creditor_account" class="form-control" onchange="toggleCcByAccount(this,'other_creditor_cc_wrap')">
                                   <option value="">-- اختر حساب مصروف نهائي --</option>
                                   @forelse($expenseLeafAccounts as $acc)
                                     <option value="{{ $acc->id }}" data-costcenter="{{ $acc->cost_center ?? 0 }}">{{ $acc->account }}</option>
                                   @empty
                                     <option value="" disabled>لا توجد حسابات مصروفات نهائية متاحة</option>
                                   @endforelse
                                </select>
                                <div class="small-hint">يظهر هنا فقط حسابات المصروفات التي ليس لها أبناء.</div>
                            </div>
                            <div>
                                <label for="other_creditor_pay_from_account">الدفع من حساب</label>
                                <select id="other_creditor_pay_from_account" class="form-control" onchange="toggleCcByAccount(this,'other_creditor_pay_from_cc_wrap')">
                                   <option value="">-- اختر حساب الدفع --</option>
                                   @foreach($accounts as $account)
                                     <option value="{{ $account->id }}" data-costcenter="{{ $account->cost_center ?? 0 }}">{{ $account->account }}</option>
                                   @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row-split" style="margin-top:10px;">
                            <div id="other_creditor_cc_wrap" style="display:none;">
                                <label for="other_creditor_cc">مركز التكلفة (للحساب الدائن)</label>
                                <select id="other_creditor_cc" class="form-control">
                                   <option value="">-- اختر مركز التكلفة --</option>
                                   @foreach($cost_centers as $center)
                                     <option value="{{ $center->id }}">{{ $center->name }}</option>
                                   @endforeach
                                </select>
                                <div class="small-hint">مطلوب لهذا الحساب إذا كان مفعّلًا.</div>
                            </div>
                            <div id="other_creditor_pay_from_cc_wrap" style="display:none;">
                                <label for="other_creditor_pay_from_cc">مركز التكلفة (لحساب الدفع)</label>
                                <select id="other_creditor_pay_from_cc" class="form-control">
                                   <option value="">-- اختر مركز التكلفة --</option>
                                   @foreach($cost_centers as $center)
                                     <option value="{{ $center->id }}">{{ $center->name }}</option>
                                   @endforeach
                                </select>
                                <div class="small-hint">مطلوب لهذا الحساب إذا كان مفعّلًا.</div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:10px;">
                            <label for="other_now_desc">البيان</label>
                            <input type="text" id="other_now_desc" class="form-control" placeholder="بيان سند الصرف">
                        </div>
                    </div>
                </div>
             </div>
           </div>

        </div>
      </div>
      <div class="modal-footer" style="display:flex; justify-content:space-between;">
        <button type="button" class="btn btn-soft" data-dismiss="modal">إغلاق</button>
        <button type="button" class="btn btn-primary" onclick="savePaymentInfo()">حفظ</button>
      </div>
    </div>
  </div>
</div>
@endsection

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Select2 --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
{{-- Bootstrap (with Popper) --}}
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
/* ===== Server data ===== */
var allProducts = @json($products);
var sessionCart = @json($cart);

/* ===== Helpers ===== */
function fmt(n){ return (parseFloat(n||0)).toFixed(2); }
function toggleCashHint(){ document.getElementById('cash-hint').style.display = document.getElementById('isCash').checked ? 'block' : 'none'; }
function syncOtherDefault(){ $('#other_now_amount').val($('#otherExpenses').val()); }

/* ===== CC show/hide by account (data-costcenter) ===== */
function toggleCcByAccount(selectEl, wrapId){
    const opt = selectEl && selectEl.options ? selectEl.options[selectEl.selectedIndex] : null;
    const need = opt && parseInt(opt.getAttribute('data-costcenter')||'0',10) === 1;
    const wrap = document.getElementById(wrapId);
    if(!wrap) return;
    wrap.style.display = need ? 'block':'none';
}

/* ===== Init ===== */
$(function(){
    $('.select2').select2({ placeholder:'اختر...', allowClear:true, width:'100%' });

    // Preview invoice image
    $('#invoiceImage').on('change', function(e){
        const file = e.target.files[0];
        const prev = document.getElementById('invoiceImagePreview');
        if(file){ prev.src = URL.createObjectURL(file); } else { prev.removeAttribute('src'); }
    });

    if (sessionCart && Object.keys(sessionCart).length > 0) {
        refreshInvoiceData();
    }

    $('#otherExpenses').on('input', function(){ $('#otherShow').text(fmt(this.value)); });
});

/* ===== Supplier details (badges) ===== */
function showSupplierDetails(select){
    var opt = select.options[select.selectedIndex]; var val = select.value;
    var wrap = document.getElementById('supplier-details');
    if(!val){ wrap.style.display='none'; return; }
    wrap.style.display='block';
    function badge(label, value){ return `<span class="badge-soft"><strong>${label}:</strong> ${value||'غير متوفر'}</span>`; }
    const html = [
        badge('الاسم', opt.getAttribute('data-name')),
        badge('الهاتف', opt.getAttribute('data-phone')),
        badge('السجل التجاري', opt.getAttribute('data-c_history')),
        badge('الرقم الضريبي', opt.getAttribute('data-tax_number')),
        badge('العنوان', opt.getAttribute('data-address')),
    ].join('');
    document.getElementById('supplier-info').innerHTML = html;
    localStorage.setItem('cached_supplier', val);
}

/* ===== Product rows ===== */
function setProductData(select){
    var opt = select.options[select.selectedIndex];
    var row = select.closest('tr');
    var productCode = opt.getAttribute('data-code') || '';
    var taxPercentage = parseFloat(opt.getAttribute('data-tax')) || 0;
    row.querySelector('.product-code').innerText = productCode;
    row.dataset.taxPercentage = taxPercentage;
    calculateRowTotal(row.querySelector('input[name="price"]') || select);
}
function calculateRowTotal(changedInput){
    var row = changedInput.closest('tr');
    var q = parseFloat(row.querySelector('input[name="quantity"]').value) || 0;
    var p = parseFloat(row.querySelector('input[name="price"]').value) || 0;
    var disc = parseFloat(row.querySelector('input[name="discount"]').value) || 0;
    var taxPct = parseFloat(row.dataset.taxPercentage) || 0;

    var eff = Math.max(p - disc, 0);
    var taxValue = eff * taxPct / 100;
    var unitInc = eff + taxValue;

    row.querySelector('input[name="tax"]').value = fmt(taxValue);
    row.querySelector('input[name="price_incl_tax"]').value = fmt(unitInc);
    row.querySelector('input[name="row_total"]').value = fmt(q * unitInc);

    updateSummary();
}
function addRow(){
    var body = document.getElementById('product-rows');
    var newRow = document.createElement('tr');
    var options = ['<option value="">-- اختر المنتج --</option>'].concat(allProducts.map(function(p){
        var tax = (p.taxe ? p.taxe.amount : 0);
        return `<option value="${p.id}" data-name="${p.name}" data-code="${p.product_code}" data-tax="${tax}">${p.name} - ${p.product_code}</option>`;
    })).join('');
    newRow.innerHTML = `
        <td><select name="product_id" class="select2 form-control" onchange="setProductData(this)">${options}</select></td>
        <td><span class="product-code"></span></td>
        <td>
            <select name="unit" class="select2 form-control" required>
                <option value="">-- اختر الوحدة --</option>
                <option value="1">كبري</option>
                <option value="0">صغري</option>
            </select>
        </td>
        <td><input type="number" name="quantity" class="form-control" step="1" min="1" onchange="calculateRowTotal(this)"></td>
        <td><input type="number" name="price" class="form-control" step="0.01" min="0" onchange="calculateRowTotal(this)"></td>
        <td><input type="number" name="tax" class="form-control" step="0.01" min="0" readonly></td>
        <td><input type="number" name="price_incl_tax" class="form-control" step="0.01" readonly></td>
        <td><input type="number" name="discount" class="form-control" step="0.01" min="0" value="0" onchange="calculateRowTotal(this)"></td>
        <td><input type="number" name="row_total" class="form-control" step="0.01" readonly></td>
        <td class="text-center"><button type="button" class="btn btn-link" onclick="removeRow(this)">حذف</button></td>
    `;
    body.appendChild(newRow);
    $(newRow).find('.select2').select2({ placeholder:'اختر...', allowClear:true, width:'100%' });
}
function removeRow(btn){
    var row = btn.closest('tr'); var body = document.getElementById('product-rows');
    if(body.rows.length <= 1){ alert('يجب أن يكون هناك صف واحد على الأقل'); return; }
    body.removeChild(row);
    updateSummary();
}

/* ===== Summary ===== */
function updateSummary(){
    var rows = document.querySelectorAll('#product-rows tr');
    var subtotal=0, totalTax=0, totalDiscount=0, grand=0;
    rows.forEach(function(row){
        var q = parseFloat(row.querySelector('input[name="quantity"]').value) || 0;
        var p = parseFloat(row.querySelector('input[name="price"]').value) || 0;
        var d = parseFloat(row.querySelector('input[name="discount"]').value) || 0;
        var taxPct = parseFloat(row.dataset.taxPercentage) || 0;

        subtotal += q * p;
        totalDiscount += q * d;

        var eff = Math.max(p - d, 0);
        var tax = eff * taxPct / 100;
        totalTax += q * tax;
        grand += q * (eff + tax);
    });

    $('#subtotal').text(fmt(subtotal));
    $('#totalDiscount').text(fmt(totalDiscount));
    $('#totalTax').text(fmt(totalTax));
    $('#grandTotal').text(fmt(grand));

    updateFinalTotal();
}
function updateFinalTotal(){
    var other = parseFloat($('#otherExpenses').val()) || 0;
    $('#otherShow').text(fmt(other));
    var grand = parseFloat($('#grandTotal').text()) || 0;
    $('#finalTotal').text(fmt(grand + other));
}

/* ===== Refresh from session (if any backend fills it) ===== */
function refreshInvoiceData(){
    $.ajax({
        url: '{{ route("admin.purchase_invoice.refresh") }}',
        method: 'GET',
        success: function(res){
            if(res.invoice && res.invoice.summary){
                $('#subtotal').text(fmt(res.invoice.summary.subtotal));
                $('#totalTax').text(fmt(res.invoice.summary.totalTax));
                $('#grandTotal').text(fmt(res.invoice.summary.grandTotal));
                $('#totalDiscount').text(fmt(res.invoice.summary.totalDiscount || 0));
                updateFinalTotal();
            }
            if(res.invoice && res.invoice.supplier_id){
                $('#supplier').val(res.invoice.supplier_id).trigger('change');
                showSupplierDetails(document.getElementById('supplier'));
            }

            var $body = $('#product-rows'); $body.empty();
            $.each(res.cart, function(_, item){
                var productCode = item.product_code;
                if(!productCode){
                    allProducts.forEach(function(p){ if(p.id == item.product_id){ productCode = p.product_code; } });
                }
                var optionsHtml = '<option value="">-- اختر المنتج --</option>';
                allProducts.forEach(function(p){
                    var sel = (item.product_id == p.id) ? 'selected' : '';
                    optionsHtml += `<option value="${p.id}" data-name="${p.name}" data-code="${p.product_code}" data-tax="${(p.taxe ? p.taxe.amount : 0)}" ${sel}>${p.name} - ${p.product_code}</option>`;
                });

                var row = $(`
                    <tr data-tax-percentage="${item.tax_percentage||0}">
                        <td><select name="product_id" class="select2 form-control" onchange="setProductData(this)">${optionsHtml}</select></td>
                        <td><span class="product-code">${productCode||''}</span></td>
                        <td>
                            <select name="unit" class="select2 form-control" required>
                                <option value="1" ${item.unit==="1"?'selected':''}>كبري</option>
                                <option value="0" ${item.unit==="0"?'selected':''}>صغري</option>
                            </select>
                        </td>
                        <td><input type="number" name="quantity" value="${item.quantity}" step="1" min="1" class="form-control" onchange="calculateRowTotal(this)"></td>
                        <td><input type="number" name="price" value="${item.price}" step="0.01" min="0" class="form-control" onchange="calculateRowTotal(this)"></td>
                        <td><input type="number" name="tax" value="${fmt(item.tax)}" step="0.01" min="0" class="form-control" readonly></td>
                        <td><input type="number" name="price_incl_tax" value="${fmt(item.price_incl_tax)}" step="0.01" class="form-control" readonly></td>
                        <td><input type="number" name="discount" value="${fmt(item.discount||0)}" step="0.01" min="0" class="form-control" onchange="calculateRowTotal(this)"></td>
                        <td><input type="number" name="row_total" value="${fmt(item.row_total||0)}" step="0.01" class="form-control" readonly></td>
                        <td class="text-center"><button type="button" class="btn btn-link" onclick="removeRow(this)">حذف</button></td>
                    </tr>
                `);
                var pct = parseFloat(item.tax_percentage || 0);
                row.get(0).dataset.taxPercentage = isFinite(pct)? pct : 0;
                $body.append(row);
            });
            $('.select2').select2({ placeholder:'اختر...', allowClear:true, width:'100%' });
            updateSummary();
        },
        error: function(){ alert('حدث خطأ أثناء جلب بيانات الفاتورة'); }
    });
}

/* ===== Payment modal logic ===== */
function openPaymentModal(){
    var isCash = document.getElementById('isCash').checked;
    document.getElementById('cashFields').style.display = isCash ? 'block' : 'none';
    document.getElementById('creditFields').style.display = isCash ? 'none' : 'block';

    // default other amount
    syncOtherDefault();

    // CC show/hide for selections
    toggleCcByAccount(document.getElementById('account'),'cash_costcenter_wrap');
    toggleCcByAccount(document.getElementById('credit_pay_from_account'),'credit_pay_from_cc_wrap');
    toggleCcByAccount(document.getElementById('other_supplier_pay_from_account'),'other_supplier_cc_wrap');
    toggleCcByAccount(document.getElementById('other_creditor_account'),'other_creditor_cc_wrap');
    toggleCcByAccount(document.getElementById('other_creditor_pay_from_account'),'other_creditor_pay_from_cc_wrap');

    $('#paymentModal').modal('show');
}
function toggleOtherNow(){
    const on = document.getElementById('pay_other_now').checked;
    document.getElementById('other_now_wrap').style.display = on ? 'block' : 'none';
}
function toggleOtherTo(){
    const val = document.querySelector('input[name="other_to"]:checked').value;
    document.getElementById('other_to_supplier').style.display = (val==='supplier') ? 'block' : 'none';
    document.getElementById('other_to_other').style.display = (val==='other') ? 'block' : 'none';
}

function savePaymentInfo(){
    const isCash = document.getElementById('isCash').checked;
    const order_amount = parseFloat($('#finalTotal').text()) || 0;

    if(isCash){
        const payAcc = $('#account').val();
        const needCC = $('#account option:selected').data('costcenter') == 1;
        const cc = $('#cash_costcenter').val();
        const amount = parseFloat($('#cash_payment_amount').val()) || 0;

        if(!payAcc){ alert('اختر حساب الدفع.'); return; }
        if(needCC && !cc){ alert('هذا الحساب يحتاج مركز تكلفة.'); return; }
        if(Math.abs(amount - order_amount) > 0.001){ alert('مبلغ الكاش يجب أن يساوي الإجمالي النهائي.'); return; }
    }else{
        // دفعة مقدمة (اختياري)
        const advAmount = parseFloat($('#credit_now_amount').val() || 0);
        const payAcc = $('#credit_pay_from_account').val();
        const needCC = $('#credit_pay_from_account option:selected').data('costcenter') == 1;
        const cc = $('#credit_pay_from_cc').val();

        if(advAmount > 0 && !payAcc){ alert('اختر حساب الدفع للدفعة المقدمة.'); return; }
        if(advAmount > 0 && needCC && !cc){ alert('الحساب يحتاج مركز تكلفة.'); return; }

        // مصاريف إضافية الآن (اختياري)
        if($('#pay_other_now').is(':checked')){
            const otherAmt = parseFloat($('#other_now_amount').val() || 0);
            if(otherAmt <= 0){ alert('أدخل مبلغ المصاريف الإضافية.'); return; }

            const otherTo = $('input[name="other_to"]:checked').val();
            if(otherTo === 'supplier'){
                const pfa = $('#other_supplier_pay_from_account').val();
                const need = $('#other_supplier_pay_from_account option:selected').data('costcenter') == 1;
                const ccc = $('#other_supplier_cc').val();
                if(!pfa){ alert('اختر حساب الدفع (المورد).'); return; }
                if(need && !ccc){ alert('حساب الدفع يحتاج مركز تكلفة.'); return; }
            }else{
                const cred = $('#other_creditor_account').val();
                const pfa  = $('#other_creditor_pay_from_account').val();
                const need1 = $('#other_creditor_account option:selected').data('costcenter') == 1;
                const need2 = $('#other_creditor_pay_from_account option:selected').data('costcenter') == 1;
                const cc1 = $('#other_creditor_cc').val();
                const cc2 = $('#other_creditor_pay_from_cc').val();
                if(!cred || !pfa){ alert('اختر الحساب الدائن وحساب الدفع لسند الصرف.'); return; }
                if(need1 && !cc1){ alert('الحساب الدائن يحتاج مركز تكلفة.'); return; }
                if(need2 && !cc2){ alert('حساب الدفع يحتاج مركز تكلفة.'); return; }
            }
        }
    }
    $('#paymentModal').modal('hide');
}

/* ===== Cancel invoice ===== */
function cancelInvoice(){
    if(!confirm('هل أنت متأكد من إلغاء الفاتورة؟ سيتم مسح كافة البيانات.')) return;
    $.ajax({
        url: '{{ route("admin.purchase_invoice.cancel") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res){ alert(res.message); location.reload(); },
        error: function(){ alert('حدث خطأ أثناء إلغاء الفاتورة!'); }
    });
}

/* ===== Execute (submit) invoice ===== */
function executeInvoice(){
    var products = [];
    $('#product-rows tr').each(function(){
        var $r = $(this);
        var pid = $r.find('select[name="product_id"]').val();
        if(!pid) return;
        products.push({
            id: pid,
            quantity: parseFloat($r.find('input[name="quantity"]').val()) || 0,
            price: parseFloat($r.find('input[name="price"]').val()) || 0,
            unit: $r.find('select[name="unit"]').val(),
            tax: parseFloat($r.find('input[name="tax"]').val()) || 0,
            discount: parseFloat($r.find('input[name="discount"]').val()) || 0
        });
    });

    if(products.length < 1){ alert('يجب إضافة منتج واحد على الأقل.'); return; }

    var supplier_id = $('#supplier').val();
    if(!supplier_id){ alert('يرجى اختيار المورد.'); return; }

    var order_amount = parseFloat($('#finalTotal').text()) || 0;
    var total_tax = parseFloat($('#totalTax').text()) || 0;
    var invoice_date = $('#invoice_date').val();
    var status = $('#status').val();
    var other_expenses = parseFloat($('#otherExpenses').val()) || 0;
    var note = $('#note').val() || '';

    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('supplier_id', supplier_id);
    formData.append('order_amount', order_amount);
    formData.append('total_tax', total_tax);
    formData.append('date', invoice_date);
    formData.append('status', status);
    formData.append('other_expenses', other_expenses);
    formData.append('note', note);

    // المنتجات
    for (var i = 0; i < products.length; i++) {
        formData.append(`products[${i}][id]`, products[i].id);
        formData.append(`products[${i}][quantity]`, products[i].quantity);
        formData.append(`products[${i}][price]`, products[i].price);
        formData.append(`products[${i}][unit]`, products[i].unit);
        formData.append(`products[${i}][tax]`, products[i].tax);
        formData.append(`products[${i}][discount]`, products[i].discount);
    }

    // صورة الفاتورة
    var invoiceImageInput = document.getElementById('invoiceImage');
    if (invoiceImageInput && invoiceImageInput.files.length > 0) {
        formData.append('invoice_image', invoiceImageInput.files[0]);
    }

    var isCash = document.getElementById('isCash').checked;

    if(isCash){
        var account = $('#account').val();
        var cashPaid = parseFloat($('#cash_payment_amount').val()) || 0;
        var needCC = $('#account option:selected').data('costcenter') == 1;
        var cc = $('#cash_costcenter').val();

        if(Math.abs(cashPaid - order_amount) > 0.001){
            alert('المبلغ المدفوع يجب أن يساوي الإجمالي النهائي!');
            return;
        }
        formData.append('cash', 1);
        formData.append('payment_info[payment_type]', 'cash');
        formData.append('payment_info[account_id]', account);
        formData.append('payment_info[payment_amount]', cashPaid);
        if(needCC && cc){ formData.append('payment_info[cost_center_id]', cc); }

        var receipt = document.getElementById('receipt_image');
        if(receipt && receipt.files.length > 0){
            formData.append('payment_info[receipt_image]', receipt.files[0]);
        }
    }else{
        formData.append('cash', 2);

        // دفعة مقدمة الآن (اختياري)
        var advAmount = parseFloat($('#credit_now_amount').val() || 0);
        var advAcc = $('#credit_pay_from_account').val();
        var advNeed = $('#credit_pay_from_account option:selected').data('costcenter') == 1;
        var advCC = $('#credit_pay_from_cc').val();
        if(advAmount > 0){
            formData.append('advance_payment[amount]', advAmount);
            formData.append('advance_payment[account_id]', advAcc || '');
            if(advNeed && advCC){ formData.append('advance_payment[cost_center_id]', advCC); }
        }

        // مصاريف إضافية الآن (اختياري)
        if($('#pay_other_now').is(':checked')){
            var otherAmt = parseFloat($('#other_now_amount').val() || 0);
            var otherTo = $('input[name="other_to"]:checked').val();
            formData.append('other_expenses_payment[pay_now]', 1);
            formData.append('other_expenses_payment[amount]', otherAmt);

            if(otherTo === 'supplier'){
                var pfa = $('#other_supplier_pay_from_account').val();
                var need = $('#other_supplier_pay_from_account option:selected').data('costcenter') == 1;
                var ccc = $('#other_supplier_cc').val();
                var noteS = $('#other_supplier_note').val() || '';
                formData.append('other_expenses_payment[to_supplier]', 1);
                formData.append('other_expenses_payment[pay_from_account_id]', pfa || '');
                if(need && ccc){ formData.append('other_expenses_payment[pay_from_cost_center_id]', ccc); }
                formData.append('other_expenses_payment[description]', noteS);
            }else{
                var cred = $('#other_creditor_account').val();
                var pfa2 = $('#other_creditor_pay_from_account').val();
                var need1 = $('#other_creditor_account option:selected').data('costcenter') == 1;
                var need2 = $('#other_creditor_pay_from_account option:selected').data('costcenter') == 1;
                var cc1 = $('#other_creditor_cc').val();
                var cc2 = $('#other_creditor_pay_from_cc').val();
                var desc = $('#other_now_desc').val() || '';
                formData.append('other_expenses_payment[to_supplier]', 0);
                formData.append('other_expenses_payment[creditor_account_id]', cred || '');
                formData.append('other_expenses_payment[pay_from_account_id]', pfa2 || '');
                if(need1 && cc1){ formData.append('other_expenses_payment[creditor_cost_center_id]', cc1); }
                if(need2 && cc2){ formData.append('other_expenses_payment[pay_from_cost_center_id]', cc2); }
                formData.append('other_expenses_payment[description]', desc);
            }
        }
    }

    $.ajax({
        url: '{{ route("admin.purchase_invoice.execute") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res){ alert(res.message); location.reload(); },
        error: function(xhr){ console.log(xhr.responseText); alert('حدث خطأ أثناء تنفيذ الفاتورة: ' + (xhr.responseText || '')); }
    });
}
</script>
