@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_journal_entry'))

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root{
        --grid:#e7eaf0; --head:#f6f8ff; --ink:#0f172a; --muted:#667085; --ok:#16a34a; --bad:#ef4444; --warn:#f59e0b;
        --soft:#ffffff; --zebra:#fbfcff;
    }
    .page-wrap{direction:rtl}

    /* toolbar */
    .toolbar{
        display:flex; align-items:center; gap:14px; flex-wrap:wrap;
        background:#fff; border:1px solid var(--grid); border-radius:12px; padding:10px 12px; margin-bottom:12px
    }
    .toolbar .group{
        display:flex; align-items:center; gap:10px; flex-wrap:nowrap;
        background:#f9fafb; border:1px solid #eef2f7; border-radius:10px; padding:8px 10px
    }
    .toolbar label{margin:0; color:#667085; font-weight:700; white-space:nowrap}
    .toolbar .form-control{height:38px; border-radius:10px; min-width:180px}
    .toolbar .group .form-control:first-child{min-width:110px}
    .btn-compact{padding:.4rem .8rem; border-radius:10px}
    .toolbar .spacer{flex:1}

    /* table like Excel */
    .table-container{
        overflow:auto; background:#fff; border:1px solid var(--grid); border-radius:12px;
        box-shadow: 0 4px 14px rgba(17,24,39,.03);
    }
    #journalTable{min-width:1700px; width:100%; border-collapse:separate; border-spacing:0; font-size:13px}
    #journalTable thead th{
        position:sticky; top:0; z-index:5; background:var(--head); color:#1f2937;
        font-weight:800; text-align:center; border-bottom:1px solid var(--grid); border-right:1px solid var(--grid);
        padding:10px 8px;
    }
    #journalTable tbody td{border-right:1px solid var(--grid); border-bottom:1px solid var(--grid); padding:6px; vertical-align:middle; background:#fff}
    #journalTable tbody tr:nth-child(odd) td{ background: var(--zebra); }
    #journalTable tfoot th, #journalTable tfoot td{background:#fafafa; border-top:2px solid var(--grid); padding:10px 8px; font-weight:700}

    .col-num{text-align:center; min-width:90px}
    .col-code{min-width:140px}
    .col-acct{min-width:280px}
    .col-debit,.col-credit{min-width:140px}
    .col-desc{min-width:280px}
    .col-cost{min-width:220px}
    .col-img{min-width:180px}
    .col-actions{min-width:110px; text-align:center}

    .form-control{border:1px solid #e5e7eb; border-radius:8px; padding:.45rem .6rem; height:36px}
    textarea.form-control{height:auto; min-height:42px}
    .num{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; text-align:center}

    /* footer */
    .sheet-footer{
        position: sticky; bottom: 0; z-index: 6; margin-top:12px;
        background:#fff; border:1px solid var(--grid); border-radius:12px; padding:10px 14px;
        display:flex; align-items:center; justify-content:space-between; gap:12px;
    }
    .totals{display:flex; gap:14px; align-items:center; flex-wrap:wrap}
    .badge-soft{display:inline-flex; align-items:center; gap:8px; border-radius:999px; padding:6px 12px; border:1px solid var(--grid); background:#f8fafc}
    .badge-ok{ color:var(--ok); border-color:#bbf7d0; background:#f0fdf4 }
    .badge-bad{ color:var(--bad); border-color:#fecaca; background:#fef2f2 }
    .badge-warn{ color:#92400e; border-color:#fde68a; background:#fffbeb }

    .select2-container--default .select2-selection--single{height:36px; border:1px solid #e5e7eb; border-radius:8px}
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:34px; padding-right:10px}
    .select2-container--default .select2-selection--single .select2-selection__arrow{height:34px}

    /* error alert */
    .alert-error{border:1px solid #fecaca; background:#fef2f2; color:#991b1b; border-radius:10px; padding:10px 12px}

    /* cost center visual cue */
    .is-required{border-color:#ef4444!important; box-shadow:0 0 0 .08rem rgba(239,68,68,.25)}
    .hint-required{font-size:.8rem;color:#b91c1c;display:none;margin-top:.25rem}

    @media (max-width:768px){
        .toolbar .group{flex:1 1 100%; justify-content:space-between}
        .toolbar .form-control{min-width:unset; width:100%}
    }
</style>

<div class="content container-fluid page-wrap">
    <!-- Breadcrumb -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                        <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    {{ \App\CPU\translate('إضافة قيد يدوي جديد') }}
                </li>
            </ol>
        </nav>
    </div>

    {{-- رسالة خطأ من السيرفر لو entries مطلوبة --}}
    @if($errors->has('entries'))
        <div class="alert-error mb-2">
            {{ $errors->first('entries') }}
        </div>
    @endif

    {{-- رسالة خطأ عند عدم وجود أي قيود --}}
    <div id="entriesError" class="alert-error mb-2 d-none">
        The entries field is required.
    </div>

    <form action="{{ route('admin.account.store-transfer') }}" method="POST" enctype="multipart/form-data" id="journalForm">
        @csrf

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="group">
                <label for="rowCountInput">عدد الصفوف</label>
                <input type="number" id="rowCountInput" class="form-control" value="1" min="1">
                <button type="button" id="addRowsBtn" class="btn btn-primary btn-compact">
                    {{ \App\CPU\translate('إضافة') }}
                </button>
            </div>

            <div class="group">
                <label for="globalDate">التاريخ</label>
                <input type="date" id="globalDate" class="form-control" required>
            </div>

            <div class="group">
                <label for="searchByCode">بحث برقم/كود الحساب</label>
                <input type="text" id="searchByCode" class="form-control" placeholder="اكتب رقم الحساب لتصفية الصفوف…">
            </div>

            <div class="spacer"></div>

            <div class="group">
                <button type="button" id="addOneRowQuick" class="btn btn-outline-secondary btn-compact">+ صف سريع</button>
                <button type="button" id="clearEmptyRows" class="btn btn-outline-danger btn-compact">حذف الصفوف الفارغة</button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container" id="tableScrollContainer">
            <table id="journalTable">
                <thead>
                    @php
                        $headers = ['رقم الصف', 'رقم المرجع', 'رقم الحساب', 'الحساب', 'مدين', 'دائن', 'البيان', 'مركز التكلفة', 'الصورة', 'الإجراء'];
                    @endphp
                    <tr>
                        <th class="col-num">{{ $headers[0] }}</th>
                        <th>{{ $headers[1] }}</th>
                        <th class="col-code">{{ $headers[2] }}</th>
                        <th class="col-acct">{{ $headers[3] }}</th>
                        <th class="col-debit">{{ $headers[4] }}</th>
                        <th class="col-credit">{{ $headers[5] }}</th>
                        <th class="col-desc">{{ $headers[6] }}</th>
                        <th class="col-cost">{{ $headers[7] }}</th>
                        <th class="col-img">{{ $headers[8] }}</th>
                        <th class="col-actions">{{ $headers[9] }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-start" style="font-weight:700">إجمالي المدين / الدائن</td>
                        <td id="totalDebit" class="num" style="font-weight:900; color:var(--ok)">0.00</td>
                        <td id="totalCredit" class="num" style="font-weight:900; color:var(--bad)">0.00</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="sheet-footer">
            <div class="totals">
                <span class="badge-soft"><strong>مدين:</strong> <span id="totalDebitBadge" class="num">0.00</span></span>
                <span class="badge-soft"><strong>دائن:</strong> <span id="totalCreditBadge" class="num">0.00</span></span>
                <span id="balanceState" class="badge-soft badge-bad">غير متزن ✖</span>
            </div>
            <div class="actions">
                <button type="submit" id="saveBtn" class="btn btn-primary px-5 py-2" disabled>
                    {{ \App\CPU\translate('حفظ') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    const $tableBody = $('#journalTable tbody');
    const accounts = @json($accounts);
    const costcenters = @json($costcenters);
    let rowIndex = 0;

    // ---- helpers ----
    function generateAccountOptions(data) {
        return data.map(item => {
            const label = item.account ?? '';
            const code  = item.code ?? '';
            const ccReq = item.cost_center ?? 0;
            return `<option value="${item.id}" data-code="${code}" data-cost-center="${ccReq}">${label}</option>`;
        }).join('');
    }
    function generateCostOptions(data) {
        return data.map(item => `<option value="${item.id}">${item.name ?? ''}</option>`).join('');
    }

    const accountOptions = generateAccountOptions(accounts);
    const costOptions    = generateCostOptions(costcenters);

    function makeRow(i) {
        return `
        <tr>
            <td class="row-number col-num">${i + 1}</td>

            <td>
                <input type="text" class="form-control" name="entries[${i}][reference]" placeholder="رقم المرجع">
                <input type="hidden" name="entries[${i}][date]" class="row-date-hidden">
            </td>

            <td><input type="text" class="form-control account-code num" placeholder="كود الحساب" name="entries[${i}][account_code]"></td>

            <td>
                <select name="entries[${i}][account_id]" class="form-control select2 account-select">
                    <option value="">اختار الحساب</option>
                    ${accountOptions}
                </select>
            </td>

            <td><input type="number" step="0.01" min="0" name="entries[${i}][debit]" class="form-control debit num" value=""></td>
            <td><input type="number" step="0.01" min="0" name="entries[${i}][credit]" class="form-control credit num" value=""></td>

            <td>
                <textarea name="entries[${i}][description]" class="form-control" rows="1" placeholder="البيان"></textarea>
            </td>

            <td>
                <select name="entries[${i}][cost_id]" class="form-control select2 cost-select">
                    <option value="">اختار مركز التكلفة</option>
                    ${costOptions}
                </select>
                <small class="hint-required cc-hint">هذا الحساب يتطلب اختيار مركز تكلفة</small>
            </td>

            <td><input type="file" name="entries[${i}][img]" class="form-control" accept="image/*"></td>

            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-compact removeRow">حذف</button>
            </td>
        </tr>`;
    }

    function setRowDateFromGlobal($row){
        const d = $('#globalDate').val() || '';
        $row.find('.row-date-hidden').val(d);
    }

    function rowNeedsCC($row){
        const $acct = $row.find('.account-select');
        const need = parseInt($acct.find('option:selected').data('cost-center') || 0) === 1;
        return need;
    }

    function syncRowCCRequirement($row){
        const need = rowNeedsCC($row);
        const $cc  = $row.find('.cost-select');
        const $hint= $row.find('.cc-hint');
        $cc.prop('required', need);
        const showWarn = need && !$cc.val();
        $cc.toggleClass('is-required', showWarn);
        $hint.toggle(showWarn);
        return !showWarn; // valid?
    }

    // صف فاضي = مفيهوش حساب، ومفيهوش مبالغ، ومفيهوش وصف/مرجع/مركز تكلفة/ملف
    function isRowEmpty($row){
        const acct   = $row.find('.account-select').val();
        const debit  = parseFloat($row.find('.debit').val()) || 0;
        const credit = parseFloat($row.find('.credit').val()) || 0;
        const desc   = ($row.find('textarea').val() || '').trim();
        const ref    = ($row.find('input[name*="[reference]"]').val() || '').trim();
        const cost   = $row.find('.cost-select').val();
        const files  = $row.find('input[type="file"]')[0]?.files?.length || 0;
        return !acct && debit === 0 && credit === 0 && desc === '' && ref === '' && !cost && files === 0;
    }

    // صف صالح = حساب مختار + (مدين>0 XOR دائن>0)
    function isRowValid($row){
        const acct   = $row.find('.account-select').val();
        const debit  = parseFloat($row.find('.debit').val()) || 0;
        const credit = parseFloat($row.find('.credit').val()) || 0;
        const xorAmounts = (debit > 0 && credit === 0) || (credit > 0 && debit === 0);
        return !!acct && xorAmounts;
    }

    function addRow() {
        $tableBody.append(makeRow(rowIndex));
        const $last = $tableBody.find('tr:last-child');
        $last.find('.select2').select2({ width: '100%' });
        setRowDateFromGlobal($last);
        syncRowCCRequirement($last);
        rowIndex++;
        renumber();
    }

    function renumber() {
        $('#journalTable tbody tr').each(function (i) {
            $(this).find('.row-number').text(i + 1);
        });
        rowIndex = $('#journalTable tbody tr').length;
    }

    // إعادة فهرسة أسماء الحقول لضمان entries[0..n] متسلسلة بدون فراغات
    function reindexFormRows(){
        $('#journalTable tbody tr').each(function(i){
            $(this).find('input, select, textarea').each(function(){
                const name = $(this).attr('name');
                if(!name) return;
                $(this).attr('name', name.replace(/entries\[\d+\]/, 'entries['+i+']'));
            });
        });
        renumber();
    }

    // actions
    $('#addRowsBtn').on('click', function(){
        const count = parseInt($('#rowCountInput').val()) || 1;
        for(let i=0;i<count;i++) addRow();
        calcTotals();
    });
    $('#addOneRowQuick').on('click', function(){ addRow(); calcTotals(); });

    // initial rows
    for (let i = 0; i < 10; i++) addRow();

    // remove row
    $(document).on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
        reindexFormRows();
        calcTotals();
    });

    function hasAnyValidEntry() {
        let found = false;
        $('#journalTable tbody tr:visible').each(function(){
            if (isRowValid($(this))) { found = true; return false; }
        });
        return found;
    }

    function allCostCentersSatisfied(){
        let ok = true;
        $('#journalTable tbody tr').each(function(){
            if(!syncRowCCRequirement($(this))) ok = false;
        });
        return ok;
    }

    function calcTotals() {
        let totalDebit = 0, totalCredit = 0;
        $('.debit').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
        $('.credit').each(function(){ totalCredit += parseFloat($(this).val()) || 0; });

        $('#totalDebit, #totalDebitBadge').text(totalDebit.toFixed(2));
        $('#totalCredit, #totalCreditBadge').text(totalCredit.toFixed(2));

        const $state = $('#balanceState');
        if (totalDebit === 0 && totalCredit === 0) {
            $state.removeClass('badge-ok badge-bad').addClass('badge-warn').text('لا توجد مبالغ');
        } else if (Math.abs(totalDebit - totalCredit) < 0.00001) {
            $state.removeClass('badge-bad badge-warn').addClass('badge-ok').text('متزن ✔');
        } else {
            $state.removeClass('badge-ok badge-warn').addClass('badge-bad').text('غير متزن ✖');
        }

        const validEntries = hasAnyValidEntry();
        const dateFilled   = !!$('#globalDate').val();
        const ccOk         = allCostCentersSatisfied();

        $('#saveBtn').prop('disabled', !(validEntries && dateFilled && totalDebit > 0 && Math.abs(totalDebit - totalCredit) < 0.00001 && ccOk));
    }

    // امنع إدخال المدين والدائن معًا: كتابة في واحد تصفّر التاني
    $(document).on('input', '.debit', function(){
        const v = parseFloat($(this).val()) || 0;
        if(v > 0){ $(this).closest('tr').find('.credit').val(''); }
        calcTotals();
    });
    $(document).on('input', '.credit', function(){
        const v = parseFloat($(this).val()) || 0;
        if(v > 0){ $(this).closest('tr').find('.debit').val(''); }
        calcTotals();
    });

    // code <-> select
    $(document).on('input', '.account-code', function () {
        const code = ($(this).val() || '').trim();
        const $select = $(this).closest('tr').find('.account-select');
        if (!code) { $select.val('').trigger('change'); calcTotals(); return; }
        const $opt = $select.find('option').filter(function () {
            return String($(this).data('code') || '').toLowerCase() === code.toLowerCase();
        }).first();
        if ($opt.length) { $select.val($opt.val()).trigger('change'); }
        else { $select.val('').trigger('change'); }
        calcTotals();
    });

    $(document).on('change', '.account-select', function () {
        const code = $(this).find('option:selected').data('code');
        const $row = $(this).closest('tr');
        $row.find('.account-code').val(code || '');
        syncRowCCRequirement($row);
        calcTotals();
    });

    $(document).on('change', '.cost-select', function(){
        const $row = $(this).closest('tr');
        syncRowCCRequirement($row);
        calcTotals();
    });

    // filter rows by account code/text
    $('#searchByCode').on('input', function(){
        const q = ($(this).val() || '').trim().toLowerCase();
        $('#journalTable tbody tr').each(function(){
            const code = ($(this).find('.account-code').val() || '').toLowerCase();
            const acctText = ($(this).find('.account-select option:selected').text() || '').toLowerCase();
            const match = !q || code.includes(q) || acctText.includes(q);
            $(this).toggle(match);
        });
    });

    // global date -> push to hidden date inputs in rows
    $('#globalDate').on('change', function(){
        const d = $(this).val() || '';
        $('#journalTable tbody tr .row-date-hidden').val(d);
        calcTotals();
    });

    // clear empty rows (الصافيين بس)
    $('#clearEmptyRows').on('click', function(){
        $('#journalTable tbody tr').each(function(){
            if (isRowEmpty($(this))) $(this).remove();
        });
        reindexFormRows();
        calcTotals();
    });

    // before submit
    $('#journalForm').on('submit', function (e) {
        // لازم تاريخ عام
        const d = $('#globalDate').val();
        if(!d){
            e.preventDefault();
            alert('يرجى اختيار التاريخ.');
            $('#globalDate').focus();
            return false;
        }
        // حدث قيم التاريخ المخفية لكل صف
        $('#journalTable tbody tr .row-date-hidden').val(d);

        // 1) احذف كل صف فاضي (مش هيتبعت)
        $('#journalTable tbody tr').each(function(){
            if (isRowEmpty($(this))) $(this).remove();
        });

        // 2) امنع الصفوف غير الصالحة (حساب بدون مبلغ، أو مبلغين معًا)
        let badRow = false;
        $('#journalTable tbody tr').each(function(){
            if (!isRowValid($(this))) { badRow = true; return false; }
        });
        if (badRow) {
            e.preventDefault();
            alert('تأكد أن كل صف فيه حساب مختار ومبلغ واحد فقط (مدين أو دائن).');
            return false;
        }

        // 3) إلزام مركز التكلفة للصفوف التي تحتاجه
        let badCC = false, firstBad = null;
        $('#journalTable tbody tr').each(function(){
            const ok = syncRowCCRequirement($(this));
            if(!ok && !badCC){ badCC = true; firstBad = $(this).find('.cost-select')[0]; }
        });
        if(badCC){
            e.preventDefault();
            alert('هناك صف يتطلب اختيار مركز تكلفة.');
            try { $(firstBad).select2('open'); } catch(_) { firstBad.focus(); }
            return false;
        }

        // 4) لازم يبقى فيه على الأقل صف واحد صالح
        if (!hasAnyValidEntry()) {
            e.preventDefault();
            $('#entriesError').removeClass('d-none').text('The entries field is required.');
            $('html, body').animate({ scrollTop: $('#entriesError').offset().top - 80 }, 300);
            return false;
        }

        // 5) لازم يكون متزن
        const totalDebit  = parseFloat($('#totalDebit').text()) || 0;
        const totalCredit = parseFloat($('#totalCredit').text()) || 0;
        if (Math.abs(totalDebit - totalCredit) >= 0.00001) {
            e.preventDefault();
            alert('القيود غير متزنة. مجموع المدين يجب أن يساوي مجموع الدائن.');
            return false;
        }

        // 6) إعادة فهرسة الأسماء بعد الحذف لضمان تسلسل نظيف
        reindexFormRows();
    });

    // initial totals
    calcTotals();
});
</script>
