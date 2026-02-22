{{-- resources/views/admin-views/inventory_adjustments/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('inventory_adjustments'))

@push('css_or_js')
  <!-- DataTables (Bootstrap 4) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
  <!-- Toastr (اختياري) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>

  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --brand:#0d6efd;
      --bg:#f8fafc; --ok:#16a34a; --warn:#f59e0b; --bad:#dc2626; --draft:#6b7280;
      --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .card-header{background:#001B63;color:#fff;border-bottom:3px solid #001B63;border-top-left-radius:var(--rd);border-top-right-radius:var(--rd)}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{font-size:1.25rem;margin:0;color:#fff;font-weight:800}
    .toolbar{display:flex;flex-wrap:wrap}
    .toolbar .btn{min-height:42px}

    /* مسافات أزرار متوافقة مع RTL/LTR */
    .btn-row{display:flex; flex-wrap:wrap}
    .btn-row > .btn{margin:0 .5rem .5rem 0}
    [dir="rtl"] .btn-row > .btn{margin:0 0 .5rem .5rem}

    .filter-card .form-label{font-weight:700;color:#111827}
    .filter-card .form-control, .filter-card select{min-height:42px}
    .select2-container{width:100%!important}
    .select2-selection--single{
      height:42px!important; border:1px solid #ced4da!important; border-radius:.375rem!important; display:flex; align-items:center
    }
    .select2-selection__rendered{line-height:40px!important; padding-right:8px!important}
    .select2-selection__arrow{height:40px!important}

    .table thead th{background:#f3f6fb}
    .table td, .table th{vertical-align:middle}
    .table-hover tbody tr:hover{background:#f9fbff}

    /* شارات الحالة */
    .badge-soft{border-radius:999px;padding:.35rem .65rem;font-weight:700;font-size:.78rem}
    .st-pending{background:#eff6ff;color:#1d4ed8}
    .st-approved{background:#ecfdf5;color:#065f46}
    .st-completed{background:#fef3c7;color:#92400e}
    .st-draft{background:#f3f4f6;color:#374151}

    /* إخفاء بحث DataTables الافتراضي */
    .dataTables_filter, .dataTables_wrapper .dataTables_filter input{display:none}

    /* أعمدة غير قابلة للطباعة/التصدير */
    .no-print-col{}
    .no-export{}

    /* طباعة */
    @media print{
      body *{visibility:hidden}
      .print-scope, .print-scope *{visibility:visible}
      .print-scope{position:absolute;left:0;top:0;width:100%}
      .no-print{display:none!important}
      .no-print-col{display:none!important}
    }
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تسويات المخزون') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== رأس الصفحة + أدوات ====== --}}
  <div class="card card-soft mb-3">

    <div class="card-body filter-card">
      <form action="{{ route('admin.inventory_adjustments.index') }}" method="GET">
        <div class="row gx-2 gy-2">
          <div class="col-md-4">
            <label for="branch_id" class="form-label">{{ \App\CPU\translate('اختر الفرع') }}</label>
            <select name="branch_id" id="branch_id" class="form-control">
              <option value="">{{ \App\CPU\translate('جميع الفروع') }}</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                  {{ $branch->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label for="from_date" class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
            <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-4">
            <label for="to_date" class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
            <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>
        </div>

        <div class="btn-row mt-3">
          <button type="submit" class="btn btn-secondary" style="min-width: 140px; margin-right: 15px;">
            <i class="tio-search"></i> {{ \App\CPU\translate('بحث') }}
          </button>
          <a href="{{ route('admin.inventory_adjustments.index') }}" style="min-width: 140px; margin-right: 15px;" class="btn btn-danger">
            <i class="tio-rotate-left"></i> {{ \App\CPU\translate('الغاء') }}
          </a>
          <button type="button" style="min-width: 140px; margin-right: 15px;" class="btn btn-primary" id="btnPrintTable2">
            <i class="tio-print"></i> {{ \App\CPU\translate('طباعة الجدول') }}
          </button>
              <button class="btn btn-sm btn-info" style="min-width: 140px; margin-right: 15px;" onclick="exportTableToExcel('inventoryAdjustmentsTable')">
                        {{ \App\CPU\translate('إصدار ملف أكسل') }}
                    </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ====== الجدول ====== --}}
  <div class="card card-soft">
    <div class="card-body">
      <div class="table-responsive print-scope">
        <table id="inventoryAdjustmentsTable" class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>{{ \App\CPU\translate('الفرع') }}</th>
              <th>{{ \App\CPU\translate('تاريخ التسوية') }}</th>
              <th>{{ \App\CPU\translate('الحالة') }}</th>
              <th>{{ \App\CPU\translate('منشئ العملية') }}</th>
              <th>{{ \App\CPU\translate('ملاحظات') }}</th>
              <th class="no-print-col no-export" style="width:140px">{{ \App\CPU\translate('الإجراءات') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($adjustments as $adj)
              <tr>
                <td>{{ $loop->iteration + $adjustments->firstItem() - 1 }}</td>
                <td>{{ $adj->branch->name ?? '—' }}</td>
                <td>{{ $adj->adjustment_date }}</td>
                <td>
                  @switch($adj->status)
                    @case('pending')   <span class="badge-soft st-pending">{{ \App\CPU\translate('قيد الانتظار') }}</span> @break
                    @case('approved')  <span class="badge-soft st-approved">{{ \App\CPU\translate('معتمد') }}</span> @break
                    @case('completed') <span class="badge-soft st-completed">{{ \App\CPU\translate('مكتمل') }}</span> @break
                    @default           <span class="badge-soft st-draft">{{ $adj->status }}</span>
                  @endswitch
                </td>
                <td>{{ $adj->created_by ? ($adj->creator->f_name.' '.$adj->creator->l_name) : '—' }}</td>
                <td>{{ $adj->notes ?: '—' }}</td>
                <td class="no-print-col no-export">
                  <div class="btn-row">
                    {{-- عرض --}}
                    <a href="{{ route('admin.inventory_adjustments.show',$adj->id) }}"
                       class="btn btn-sm btn-white" title="{{ \App\CPU\translate('عرض') }}" data-toggle="tooltip">
                      <i class="tio-visible"></i>
                    </a>

                    @if(in_array($adj->status, ['approved','completed']))
                      <button class="btn btn-sm btn-white" title="{{ \App\CPU\translate('تعديل') }}" disabled>
                        <i class="tio-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-white" title="{{ \App\CPU\translate('حذف') }}" disabled>
                        <i class="tio-delete-outlined"></i>
                      </button>
                    @else
                      {{-- تعديل --}}
                      <a href="{{ route('admin.inventory_adjustments.edit',$adj->id) }}"
                         class="btn btn-sm btn-white" title="{{ \App\CPU\translate('تعديل') }}" data-toggle="tooltip">
                        <i class="tio-edit"></i>
                      </a>
                      {{-- حذف --}}
                      <form action="{{ route('admin.inventory_adjustments.destroy',$adj->id) }}"
                            method="POST" class="d-inline-block"
                            onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد من الحذف؟') }}');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-white" title="{{ \App\CPU\translate('حذف') }}" data-toggle="tooltip">
                          <i class="tio-delete-outlined"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- ترقيم الصفحات --}}
      <div class="d-flex justify-content-center mt-3">
        {!! $adjustments->appends(request()->query())->links() !!}
      </div>

      {{-- لا توجد بيانات --}}
      @if($adjustments->isEmpty())
        <div class="text-center p-4">
          <img class="mb-3" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" style="max-width:220px" alt="">
          <p class="mb-0 text-muted">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('script_2')
  {{-- jQuery --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  {{-- DataTables Core + Bootstrap 4 --}}
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
  {{-- Select2 --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  {{-- Toastr (اختياري) --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  {{-- SheetJS للـ Excel --}}
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <script>
        window.exportTableToExcel = function (tableId, filename = 'journal_entries.xlsx') {
    const table = document.getElementById(tableId);
    if (!table) return;
    const wb = XLSX.utils.table_to_book(table, { sheet: "Entries" });
    XLSX.writeFile(wb, filename);
  };
    (function($){
      'use strict';

      // تفعيل Select2
      $('#branch_id').select2({ placeholder: "{{ \App\CPU\translate('اختر فرع') }}", allowClear:true, width:'100%' });

      // تفعيل التلميحات
      $('[data-toggle="tooltip"]').tooltip();

      // DataTables (على الصفحة الحالية فقط — الفرز/الترتيب)
      if ($.fn.DataTable.isDataTable('#inventoryAdjustmentsTable')) {
        $('#inventoryAdjustmentsTable').DataTable().destroy();
      }
      $('#inventoryAdjustmentsTable').DataTable({
        ordering:true, paging:false, searching:false, info:false, autoWidth:false
      });

      // بيانات المتجر للطباعة
      const shopName    = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_name'])->first())->value);
      const shopAddress = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_address'])->first())->value);
      const shopPhone   = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_phone'])->first())->value);
      const shopEmail   = @json(optional(\App\Models\BusinessSetting::where(['key'=>'shop_email'])->first())->value);
      const taxNumber   = @json(optional(\App\Models\BusinessSetting::where(['key'=>'number_tax'])->first())->value);
      const vatRegNo    = @json(optional(\App\Models\BusinessSetting::where(['key'=>'vat_reg_no'])->first())->value);
      const logoUrl     = @json(asset('storage/app/public/shop/' . optional(\App\Models\BusinessSetting::where(['key'=>'shop_logo'])->first())->value));

      function printAllTable(){
        // انسخ الجدول واستبعد أعمدة الإجراءات
        const table = document.getElementById('inventoryAdjustmentsTable').cloneNode(true);
        table.querySelectorAll('.no-print-col').forEach(el => el.remove());

        const now = new Date().toLocaleString('ar-EG', {
          year:'numeric', month:'2-digit', day:'2-digit',
          hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false
        });

        const win = window.open('', '', 'width=1000,height=900');
        win.document.write(`
          <html dir="rtl">
          <head>
            <meta charset="utf-8">
            <title>{{ \App\CPU\translate('تقرير تسويات المخزون') }}</title>
            <style>
              body{font-family:'Cairo', Tahoma, Arial, sans-serif; margin:16px; color:#333}
              .header{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #ddd;padding-bottom:10px;margin-bottom:16px}
              .info{width:32%;font-size:13px;line-height:1.6}
              .logo{width:28%;text-align:center}
              .logo img{max-height:80px}
              h2{text-align:center;margin:8px 0 16px;font-size:20px}
              table{width:100%;border-collapse:collapse}
              th,td{border:1px solid #333;padding:8px;text-align:center;font-size:13px}
              th{background:#f3f6fb}
            </style>
          </head>
          <body>
            <div class="header">
              <div class="info">
                <p><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong> ${vatRegNo ?? '—'}</p>
                <p><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> ${taxNumber ?? '—'}</p>
                <p><strong>{{ \App\CPU\translate('البريد الإلكتروني') }}:</strong> ${shopEmail ?? '—'}</p>
              </div>
              <div class="logo"><img src="${logoUrl}" alt="logo"></div>
              <div class="info">
                <p><strong>{{ \App\CPU\translate('اسم المتجر') }}:</strong> ${shopName ?? '—'}</p>
                <p><strong>{{ \App\CPU\translate('العنوان') }}:</strong> ${shopAddress ?? '—'}</p>
                <p><strong>{{ \App\CPU\translate('رقم الجوال') }}:</strong> ${shopPhone ?? '—'}</p>
              </div>
            </div>
            <h2>{{ \App\CPU\translate('تقرير تسويات المخزون') }}</h2>
            <p style="text-align:center;margin-bottom:10px"><strong>{{ \App\CPU\translate('تاريخ الطباعة') }}:</strong> ${now}</p>
        `);
        win.document.body.appendChild(table);
        win.document.write(`
            <script>window.onload=function(){window.print(); window.close();};<\/script>
          </body></html>
        `);
        win.document.close();
      }

      // تصدير Excel عبر SheetJS – استبعاد الأعمدة ذات .no-export
      function exportExcel(){
        const table = document.getElementById('inventoryAdjustmentsTable');
        const clone = table.cloneNode(true);
        clone.querySelectorAll('.no-export').forEach(el => el.remove());
        clone.querySelectorAll('.no-print-col').forEach(el => el.remove());

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(clone, { raw:true });
        XLSX.utils.book_append_sheet(wb, ws, 'Adjustments');

        const dt = new Date();
        const y = dt.getFullYear();
        const m = String(dt.getMonth()+1).padStart(2,'0');
        const d = String(dt.getDate()).padStart(2,'0');
        XLSX.writeFile(wb, `inventory_adjustments_${y}${m}${d}.xlsx`);
      }

      // أزرار
      $('#btnPrintTable, #btnPrintTable2').on('click', printAllTable);
      $('#btnExportExcel').on('click', exportExcel);

    })(jQuery);
  </script>
@endpush
