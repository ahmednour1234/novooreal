@extends('layouts.admin.app')

@section('title', 'عرض التحويلات')

@push('css_or_js')
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <!-- toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root{
            --ink:#0f172a; --muted:#667085; --grid:#e9eef5; --brand:#0d6efd;
            --ok:#16a34a; --warn:#f59e0b; --bad:#dc2626; --pending:#0ea5e9; --draft:#6b7280;
            --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
        }
        .card-soft{border:1px solid var(--grid); border-radius:var(--rd); box-shadow:var(--shadow); background:#fff}
        .page-head{display:flex; align-items:center; justify-content:space-between; gap:12px}
        .toolbar{display:grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap:8px; width:100%}
        .toolbar .btn{min-height:42px}

        .filter-form .form-label{font-weight:700}
        .select2-container{width:100%!important}
        .select2-selection--single{
            height:38px!important; border:1px solid #ced4da!important; border-radius:.375rem!important; display:flex; align-items:center
        }
        .select2-selection__rendered{line-height:36px!important; padding-right:8px!important}
        .select2-selection__arrow{height:36px!important}

        table.table thead th{position:sticky; top:0; z-index:5; background:#f8fafc}
        table.table td, table.table th{vertical-align:middle}
        .table-hover tbody tr:hover{background:#f9fbff}
        .status-badge{padding:.35rem .6rem; border-radius:999px; font-size:.78rem; font-weight:700}
        .st-approved{background:#ecfdf5; color:#065f46}
        .st-pending{background:#eff6ff; color:#1d4ed8}
        .st-rejected{background:#fef2f2; color:#991b1b}
        .st-draft{background:#f3f4f6; color:#374151}

        /* اخفاء عناصر بحث DataTables إن وُجد */
        .dataTables_filter, .dataTables_wrapper .dataTables_filter input{display:none}

        /* الطباعة */
        @media print {
            body * { visibility: hidden; }
            .printableArea, .printableArea * { visibility: visible; }
            .printableArea { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display:none !important; }
            .no-print-col { display:none !important; }
        }
        .print-header{
            text-align:center; margin-bottom:16px; border-bottom:2px solid #000; padding-bottom:10px; direction:rtl
        }
        .print-header .header-section{
            display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap
        }
        .print-header .header-section .left,
        .print-header .header-section .right{width:32%; text-align:right}
        .print-header .header-section .logo{width:28%; text-align:center}
        .print-header .header-section .logo img{max-height:80px}

        /* إخفاء حقل بحث DataTables إن وُجد */
        input[type="search"][aria-controls^="DataTables_Table"] { display: none; }
        label:has(input[type="search"][aria-controls^="DataTables_Table"]) { display: none; }

        /* أيقونات الإجراءات + مسافات */
        .btn-icon{ width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; padding:0; }
        .action-buttons{ display:flex; flex-wrap:wrap; align-items:center; }
        .action-buttons > *, .action-buttons form{ margin:0 10px 10px 0; }
        [dir="rtl"] .action-buttons > *, [dir="rtl"] .action-buttons form{ margin:0 0 10px 10px; }
    </style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('التحويلات المخزنية') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== بطاقة الفلاتر + شريط الأدوات ====== --}}
  <div class="card card-soft mb-4">
    <div class="card-body">
      <div class="page-head mb-3">
        <h4 class="m-0 fw-bold">{{ \App\CPU\translate('فلترة التحويلات') }}</h4>
      </div>

      <form action="{{ route('admin.transfer.index') }}" method="GET" class="filter-form">
        <div class="row g-3">
          <div class="col-md-2">
            <label for="start_date" class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control">
          </div>
          <div class="col-md-2">
            <label for="end_date" class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
          </div>
          <div class="col-md-2">
            <label for="source_branch_id" class="form-label">{{ \App\CPU\translate('الفرع المحول') }}</label>
            <select name="source_branch_id" id="source_branch_id" class="form-control select2">
              <option value="">{{ \App\CPU\translate('الكل') }}</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ request('source_branch_id') == $branch->id ? 'selected' : '' }}>
                  {{ $branch->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label for="destination_branch_id" class="form-label">{{ \App\CPU\translate('الفرع المحول له') }}</label>
            <select name="destination_branch_id" id="destination_branch_id" class="form-control select2">
              <option value="">{{ \App\CPU\translate('الكل') }}</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ request('destination_branch_id') == $branch->id ? 'selected' : '' }}>
                  {{ $branch->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label for="created_by" class="form-label">{{ \App\CPU\translate('تم التحويل بواسطة') }}</label>
            <select name="created_by" id="created_by" class="form-control select2">
              <option value="">{{ \App\CPU\translate('الكل') }}</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>
                  {{ $user->f_name . ' ' . $user->l_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label for="status" class="form-label">{{ \App\CPU\translate('حالة التحويل') }}</label>
            <select name="status" id="status" class="form-control select2">
              <option value="">{{ \App\CPU\translate('الكل') }}</option>
              <option value="draft"    {{ request('status') == 'draft' ? 'selected' : '' }}>{{ \App\CPU\translate('مسودة') }}</option>
              <option value="pending"  {{ request('status') == 'pending' ? 'selected' : '' }}>{{ \App\CPU\translate('معلقة') }}</option>
              <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ \App\CPU\translate('تمت الموافقة') }}</option>
              <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ \App\CPU\translate('تم الرفض') }}</option>
            </select>
          </div>
        </div>

        <div class="toolbar mt-3">
          <button type="submit" class="btn btn-secondary">
            <i class="tio-search"></i> {{ \App\CPU\translate('تصفية') }}
          </button>
          <a href="{{ route('admin.transfer.index') }}" class="btn btn-danger">
            <i class="tio-rotate-left"></i> {{ \App\CPU\translate('إعادة تعيين') }}
          </a>
          <button type="button" id="printButton" class="btn btn-primary">
            <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
          </button>
          <button type="button" id="exportExcelBtn" class="btn btn-success">
            <i class="tio-file-text-outlined"></i> {{ \App\CPU\translate('تصدير Excel') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ====== الجدول ====== --}}
  <div class="card card-soft">
    <div class="card-body">
      <div class="table-responsive printableArea">
        <table class="table table-bordered table-hover align-middle" id="transfersTable">
          <thead>
            <tr>
              <th>{{ \App\CPU\translate('رقم التحويل') }}</th>
              <th>{{ \App\CPU\translate('الفرع المحول') }}</th>
              <th>{{ \App\CPU\translate('الفرع المحول له') }}</th>
              <th>{{ \App\CPU\translate('إجمالي التحويل') }}</th>
              <th>{{ \App\CPU\translate('تم التحويل بواسطة') }}</th>
              <th>{{ \App\CPU\translate('تمت الموافقة بواسطة') }}</th>
              <th>{{ \App\CPU\translate('تاريخ التحويل') }}</th>
              <th>{{ \App\CPU\translate('حالة التحويل') }}</th>
              <th class="no-export no-print-col">{{ \App\CPU\translate('إجراء') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transfers as $transfer)
              <tr>
                <td>{{ $transfer->transfer_number }}</td>
                <td>{{ $transfer->sourceBranch->name ?? '—' }}</td>
                <td>{{ $transfer->destinationBranch->name ?? '—' }}</td>
                <td>{{ number_format($transfer->total_amount, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
                <td>{{ $transfer->createdBy->f_name ?? '—' }}</td>
                <td>{{ $transfer->approvedBy->f_name ?? '—' }}</td>
                <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                <td>
                  @php
                    $status = $transfer->status;
                    $map = [
                      'approved' => 'st-approved',
                      'pending'  => 'st-pending',
                      'rejected' => 'st-rejected',
                      'draft'    => 'st-draft'
                    ];
                    $cls = $map[$status] ?? 'st-draft';
                  @endphp
                  <span class="status-badge {{ $cls }}">
                    @switch($status)
                      @case('approved') {{ \App\CPU\translate('تمت الموافقة') }} @break
                      @case('pending')  {{ \App\CPU\translate('جاري') }} @break
                      @case('rejected') {{ \App\CPU\translate('تم الرفض') }} @break
                      @case('draft')    {{ \App\CPU\translate('مسودة') }} @break
                      @default          {{ $status }}
                    @endswitch
                  </span>
                </td>
                <td class="no-export no-print-col">
                  <div class="action-buttons">
                    {{-- فاتورة A4 --}}
                    <button type="button" class="btn btn-sm btn-white btn-icon"
                            title="{{ \App\CPU\translate('فاتورة A4') }}" data-toggle="tooltip"
                            onclick="print_invoicea2('{{ $transfer->id }}')">
                      <i class="tio-download"></i>
                    </button>

                    @if($transfer->status == 'pending')
                      {{-- حذف --}}
                      <form action="{{ route('admin.transfer.destroy', $transfer->id) }}" method="POST"
                            class="d-inline-block"
                            onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد من حذف التحويل؟') }}');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm btn-icon"
                                title="{{ \App\CPU\translate('حذف') }}" data-toggle="tooltip"
                                aria-label="{{ \App\CPU\translate('حذف') }}">
                          <i class="tio-delete-outlined"></i>
                        </button>
                      </form>

                      {{-- قبول --}}
                      <form action="{{ route('admin.transfer.accept', $transfer->id) }}" method="POST"
                            class="d-inline-block"
                            onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد من قبول التحويل؟') }}');">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success btn-sm btn-icon"
                                title="{{ \App\CPU\translate('قبول') }}" data-toggle="tooltip"
                                aria-label="{{ \App\CPU\translate('قبول') }}">
                          <i class="tio-done"></i>
                        </button>
                      </form>

                      {{-- رفض --}}
                      <form action="{{ route('admin.transfer.accept', $transfer->id) }}" method="POST"
                            class="d-inline-block"
                            onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد من رفض التحويل؟') }}');">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-warning btn-sm btn-icon"
                                title="{{ \App\CPU\translate('رفض') }}" data-toggle="tooltip"
                                aria-label="{{ \App\CPU\translate('رفض') }}">
                          <i class="tio-clear"></i>
                        </button>
                      </form>
                    @endif

                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center text-muted">{{ \App\CPU\translate('لا توجد تحويلات مطابقة للفلتر.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-center mt-3">
        {{ $transfers->appends(request()->query())->links() }}
      </div>
    </div>
  </div>
</div>

{{-- ====== Modal طباعة الفاتورة A4 ====== --}}
<div class="modal fade animate__animated" id="print-invoice" tabindex="-1" role="dialog" aria-labelledby="printInvoiceLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content modal-content1">
      <div class="modal-header">
        <h5 class="modal-title" id="printInvoiceLabel">{{ \App\CPU\translate('طباعة الفاتورة') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ \App\CPU\translate('إغلاق') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"><div id="printableArea"><!-- AJAX --></div></div>
      <div class="modal-footer">
        <button type="button" onclick="printDiv('printableArea')" class="btn btn-primary">{{ \App\CPU\translate('طباعة') }}</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ \App\CPU\translate('إغلاق') }}</button>
      </div>
    </div>
  </div>
</div>
@endsection

{{-- jQuery / Select2 / Toastr / SheetJS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
(function($){
  'use strict';

  // تهيئة Select2 و Tooltips
  $('.select2').select2({ placeholder: "{{ \App\CPU\translate('اختر') }}", width:'100%', dir:'rtl', allowClear:true });
  if (window.bootstrap && bootstrap.Tooltip) {
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  } else {
    $('[data-toggle="tooltip"]').tooltip?.();
  }

  // أدوات معلومات المتجر للطباعة
  const shopName    = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_name'])->first())->value);
  const shopAddress = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_address'])->first())->value);
  const shopPhone   = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_phone'])->first())->value);
  const shopEmail   = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_email'])->first())->value);
  const taxNumber   = @json(optional(\App\Models\BusinessSetting::where(['key'=>'number_tax'])->first())->value);
  const vatRegNo    = @json(optional(\App\Models\BusinessSetting::where(['key'=>'vat_reg_no'])->first())->value);
  const logoUrl     = @json(asset('storage/app/public/shop/' . optional(\App\Models\BusinessSetting::where(['key'=>'shop_logo'])->first())->value));

  // احسب فهرس عمود الإجراءات من الـ <th> (لو اتغير مكانه لاحقًا يفضل هذا النهج)
  function getActionColIndex(tableEl){
    const head = tableEl.tHead;
    if(!head || !head.rows.length) return -1;
    const cells = head.rows[0].cells;
    for(let i=0;i<cells.length;i++){
      if (cells[i].classList.contains('no-export') || cells[i].classList.contains('no-print-col')) {
        return i;
      }
    }
    return -1;
  }

  // أنشئ نسخة من الجدول وتخلّص من عمود الإجراءات + أي عناصر غير مطبوعة/مصَدّرة
  function cloneTableWithoutActions(){
    const table = document.getElementById('transfersTable');
    const clone = table.cloneNode(true);
    const actionIdx = getActionColIndex(clone);

    // احذف رأس العمود
    if (actionIdx > -1 && clone.tHead) {
      clone.tHead.rows[0].deleteCell(actionIdx);
    }
    // احذف خلايا العمود من كل صف
    Array.from(clone.tBodies || []).forEach(tb => {
      Array.from(tb.rows || []).forEach(tr => {
        if (actionIdx > -1 && tr.cells.length > actionIdx) tr.deleteCell(actionIdx);
      });
    });

    // إزالة أي عناصر لا نحتاجها
    clone.querySelectorAll('.no-export, .no-print-col, .action-buttons, form').forEach(el => el.remove());

    return clone;
  }

  // طباعة الجدول بالكامل مع رأس معلومات
  $('#printButton').on('click', function(){
    const currentDateTime = new Date().toLocaleString('ar-EG', {
      year:'numeric', month:'2-digit', day:'2-digit',
      hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false
    });

    const clone = cloneTableWithoutActions();

    const win = window.open('', '', 'width=1000,height=900');
    if(!win){ toastr.error('يمنع المتصفح فتح نافذة الطباعة (Pop-up).'); return; }

    win.document.write(`
      <html dir="rtl">
      <head>
        <meta charset="utf-8">
        <title>{{ \App\CPU\translate('طباعة التحويلات') }}</title>
        <style>
          body{font-family: Tahoma, Arial, sans-serif; margin:0; padding:12px;}
          table{width:100%; border-collapse: collapse; margin-top: 16px;}
          th, td{border:1px solid #333; padding:8px; text-align:center; font-size:13px}
          th{background:#f2f2f2}
          .print-header{text-align:center; margin-bottom:16px; border-bottom:2px solid #000; padding-bottom:10px;}
          .header-section{display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;}
          .header-section .left,.header-section .right{width:32%; text-align:right;}
          .header-section .logo{width:28%; text-align:center;}
          .header-section .logo img{max-height:80px;}
        </style>
      </head>
      <body>
        <div class="print-header">
          <div class="header-section">
            <div class="right">
              <p><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong> ${vatRegNo ?? '—'}</p>
              <p><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> ${taxNumber ?? '—'}</p>
              <p><strong>{{ \App\CPU\translate('البريد الإلكتروني') }}:</strong> ${shopEmail ?? '—'}</p>
            </div>
            <div class="logo"><img src="${logoUrl}" alt="logo"></div>
            <div class="left">
              <p><strong>{{ \App\CPU\translate('اسم المتجر') }}:</strong> ${shopName ?? '—'}</p>
              <p><strong>{{ \App\CPU\translate('العنوان') }}:</strong> ${shopAddress ?? '—'}</p>
              <p><strong>{{ \App\CPU\translate('رقم الجوال') }}:</strong> ${shopPhone ?? '—'}</p>
            </div>
          </div>
          <p><strong>{{ \App\CPU\translate('تاريخ الطباعة') }}:</strong> ${currentDateTime}</p>
        </div>
    `);
    win.document.body.appendChild(clone);
    win.document.write(`<script>window.onload=function(){window.print(); window.close();};<\/script></body></html>`);
    win.document.close();
  });

  // تصدير إلى Excel (SheetJS) بعد إزالة عمود الإجراءات
  $('#exportExcelBtn').on('click', function(){
    try{
      if (typeof XLSX === 'undefined') {
        toastr.error('مكتبة التصدير غير محملة.');
        return;
      }
      const clone = cloneTableWithoutActions();
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.table_to_sheet(clone, { raw: true });
      XLSX.utils.book_append_sheet(wb, ws, "Transfers");

      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth()+1).padStart(2,'0');
      const d = String(today.getDate()).padStart(2,'0');

      XLSX.writeFile(wb, `transfers_${y}${m}${d}.xlsx`);
    } catch(e){
      console.error(e);
      toastr.error('تعذر إنشاء ملف Excel.');
    }
  });

})(jQuery);

// تحميل محتوى فاتورة A4 داخل المودال
function print_invoicea2(transferId) {
  $.get({
    url: '{{ url("/") }}/admin/transfer/' + transferId,
    dataType: 'json',
    success: function (data) {
      $('#printableArea').html(data.view);
      $('#print-invoice').addClass('animate__fadeIn').modal('show');
    },
    error: function () {
      toastr.error("{{ \App\CPU\translate('حدث خطأ أثناء جلب بيانات الفاتورة') }}");
    }
  });
}

// طباعة محتوى div (محتوى الفاتورة داخل المودال)
function printDiv(divId) {
  const el = document.getElementById(divId);
  if(!el){ return; }
  const win = window.open('', '', 'width=900,height=800');
  win.document.write(`
    <html dir="rtl"><head>
      <meta charset="utf-8">
      <title>{{ \App\CPU\translate('طباعة') }}</title>
      <style>
        body{font-family: Tahoma, Arial, sans-serif; margin:0; padding:12px;}
        table{width:100%; border-collapse: collapse; margin-top: 16px;}
        th, td{border:1px solid #333; padding:8px; text-align:center; font-size:13px}
        th{background:#f2f2f2}
      </style>
    </head><body>${el.innerHTML}
    <script>window.onload=function(){window.print(); window.close();};<\/script>
    </body></html>
  `);
  win.document.close();
}
</script>
