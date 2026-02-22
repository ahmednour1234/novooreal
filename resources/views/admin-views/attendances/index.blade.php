@extends('layouts.admin.app')

@section('title', \App\CPU\translate('سجلات الحضور'))

@push('css_or_js')
  <style>
    :root{
      --ink:#0f172a; --muted:#667085; --grid:#e5e7eb; --brand:#0d6efd;
      --bg:#f8fafc; --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
    }
    body{background:var(--bg)}
    .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .page-head h1{font-size:1.25rem;margin:0;color:var(--ink);font-weight:800}
    .toolbar{display:flex;gap:8px;flex-wrap:wrap}
    .toolbar .btn{min-height:42px}

    .filter-form .form-group{margin-bottom:8px}
    .filter-form .form-control{min-height:40px}

    .summary-box{
      background:#f9fafb;border:1px solid var(--grid);border-radius:12px;padding:14px;margin-bottom:16px
    }
    .summary-box p{margin:.25rem 0;color:#111827}
    .summary-box strong{color:#0b1324}

    .table-responsive{margin-top:12px}
    table.table thead th{position:sticky;top:0;z-index:5;background:#f3f6fb}
    table.table td, table.table th{vertical-align:middle}
    .table-hover tbody tr:hover{background:#f9fbff}

    /* Print tweaks */
    @media print{
      body{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .no-print{display:none!important}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('سجلات الحضور والانصراف') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== Header + Toolbar ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1 class="mb-0">{{ \App\CPU\translate('سجلات الحضور') }}</h1>
      <div class="toolbar">
        <button type="button" class="btn btn-info" onclick="printAttendance()">
          <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
        </button>
        <button type="button" class="btn btn-success" id="exportExcelBtn">
          <i class="tio-file-text-outlined"></i> {{ \App\CPU\translate('تصدير Excel') }}
        </button>
      </div>
    </div>
  </div>

  {{-- ====== Filter ====== --}}
  <div class="card-soft p-3 mb-3">
    <form action="{{ route('admin.attendance.index') }}" method="GET" class="filter-form">
      <div class="row align-items-end g-2">
        <div class="col-md-4">
          <label class="form-label">{{ \App\CPU\translate('الموظف') }}</label>
          <select name="employee_id" class="form-control">
            <option value="">{{ \App\CPU\translate('اختر الموظف') }}</option>
            @foreach($employees as $employee)
              <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                {{ $employee->email ?? $employee->email }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">
            <i class="tio-search"></i> {{ \App\CPU\translate('فلترة') }}
          </button>
        </div>
      </div>
    </form>
  </div>

  @php
    $totalWorkedHours   = $attendances->sum('worked_hours');
    $workingDays        = $attendances->pluck('date')->unique()->count();
    $totalExpectedHours = $attendances->sum('expected_hours');

    // Shop info (optional for print header)
    $shopName    = optional(\App\Models\BusinessSetting::where('key','shop_name')->first())->value;
    $shopAddress = optional(\App\Models\BusinessSetting::where('key','shop_address')->first())->value;
    $shopPhone   = optional(\App\Models\BusinessSetting::where('key','shop_phone')->first())->value;
    $shopEmail   = optional(\App\Models\BusinessSetting::where('key','shop_email')->first())->value;
    $vatRegNo    = optional(\App\Models\BusinessSetting::where('key','vat_reg_no')->first())->value;
    $taxNumber   = optional(\App\Models\BusinessSetting::where('key','number_tax')->first())->value;
    $logo        = optional(\App\Models\BusinessSetting::where('key','shop_logo')->first())->value;
    $logoUrl     = $logo ? asset('storage/app/public/shop/'.$logo) : null;
  @endphp

  {{-- ====== Summary ====== --}}
  <div id="summaryBox" class="summary-box">
    <p><strong>{{ \App\CPU\translate('إجمالي ساعات العمل الفعلية') }}:</strong> <span id="sumWorked">{{ $totalWorkedHours }}</span> {{ \App\CPU\translate('ساعة') }}</p>
    <p><strong>{{ \App\CPU\translate('عدد أيام العمل') }}:</strong> <span id="sumDays">{{ $workingDays }}</span> {{ \App\CPU\translate('يوم') }}</p>
    <p><strong>{{ \App\CPU\translate('إجمالي ساعات العمل المتوقعة') }}:</strong> <span id="sumExpected">{{ $totalExpectedHours }}</span> {{ \App\CPU\translate('ساعة') }}</p>
  </div>

  {{-- ====== Table ====== --}}
  <div class="card-soft p-3">
    <div class="table-responsive">
      <table id="attendanceTable" class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الموظف') }}</th>
            <th>{{ \App\CPU\translate('التاريخ') }}</th>
            <th>{{ \App\CPU\translate('تسجيل الدخول') }}</th>
            <th>{{ \App\CPU\translate('تسجيل الخروج') }}</th>
            <th>{{ \App\CPU\translate('الحالة') }}</th>
            <th>{{ \App\CPU\translate('ساعات العمل الفعلية') }}</th>
            <th>{{ \App\CPU\translate('ساعات العمل المتوقعة') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($attendances as $attendance)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $attendance->admins->email ?? '-' }}</td>
              <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}</td>
              <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
              <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
              <td>{{ ucfirst($attendance->status) }}</td>
              <td>{{ $attendance->worked_hours ?? '-' }}</td>
              <td>{{ $attendance->expected_hours ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted">{{ \App\CPU\translate('لا توجد سجلات') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
      {{ $attendances->appends(request()->query())->links() }}
    </div>
  </div>

  {{-- Hidden printable area --}}
  <div id="printTemplate" class="d-none">
    <div style="padding:14px" dir="rtl">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border-bottom:2px solid #e5e7eb;padding-bottom:10px;margin-bottom:12px">
        <div>
          <p style="margin:2px 0"><strong>{{ \App\CPU\translate('اسم المتجر') }}:</strong> {{ $shopName ?? '—' }}</p>
          <p style="margin:2px 0"><strong>{{ \App\CPU\translate('العنوان') }}:</strong> {{ $shopAddress ?? '—' }}</p>
          <p style="margin:2px 0"><strong>{{ \App\CPU\translate('الهاتف') }}:</strong> {{ $shopPhone ?? '—' }} — <strong>{{ \App\CPU\translate('البريد') }}:</strong> {{ $shopEmail ?? '—' }}</p>
        </div>
        <div style="text-align:center;flex:0 0 140px">
          @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="logo" style="max-height:60px;max-width:120px;object-fit:contain">
          @endif
        </div>
        <div>
          <p style="margin:2px 0"><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong> {{ $vatRegNo ?? '—' }}</p>
          <p style="margin:2px 0"><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> {{ $taxNumber ?? '—' }}</p>
          <p style="margin:2px 0"><strong>{{ \App\CPU\translate('تاريخ الطباعة') }}:</strong> <span id="printNow"></span></p>
        </div>
      </div>

      <h3 style="margin:0 0 8px;color:#111827">{{ \App\CPU\translate('تقرير سجلات الحضور') }}</h3>

      {{-- summary --}}
      <div style="margin:10px 0;border:1px solid #e5e7eb;border-radius:10px;padding:10px">
        <div><strong>{{ \App\CPU\translate('إجمالي ساعات العمل الفعلية') }}:</strong> <span id="prSumWorked"></span></div>
        <div><strong>{{ \App\CPU\translate('عدد أيام العمل') }}:</strong> <span id="prSumDays"></span></div>
        <div><strong>{{ \App\CPU\translate('إجمالي ساعات العمل المتوقعة') }}:</strong> <span id="prSumExpected"></span></div>
      </div>

      {{-- table placeholder --}}
      <div id="prTableWrap"></div>
    </div>
  </div>

</div>
@endsection

  <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
  {{-- SheetJS for Excel export --}}
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <script>
    // ====== Print ======
    function printAttendance(){
      // fill print datetime
      const now = new Date().toLocaleString('ar-EG', {
        year:'numeric', month:'2-digit', day:'2-digit',
        hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false
      });
      document.getElementById('printNow').textContent = now;

      // copy summary numbers
      document.getElementById('prSumWorked').textContent   = document.getElementById('sumWorked').textContent + ' {{ \App\CPU\translate('ساعة') }}';
      document.getElementById('prSumDays').textContent     = document.getElementById('sumDays').textContent   + ' {{ \App\CPU\translate('يوم') }}';
      document.getElementById('prSumExpected').textContent = document.getElementById('sumExpected').textContent + ' {{ \App\CPU\translate('ساعة') }}';

      // clone table (current page)
      const tableClone = document.getElementById('attendanceTable').cloneNode(true);
      // inject into printable wrapper
      const wrap = document.getElementById('prTableWrap');
      wrap.innerHTML = '';
      wrap.appendChild(tableClone);

      // open print window
      const src = document.getElementById('printTemplate').innerHTML;
      const win = window.open('', '', 'width=1000,height=900');
      win.document.write(`
        <html dir="rtl">
          <head>
            <meta charset="utf-8">
            <title>{{ \App\CPU\translate('تقرير سجلات الحضور') }}</title>
            <style>
              body{font-family: "Tahoma","Cairo",Arial,sans-serif;margin:12px;}
              @page{ size: A4; margin: 10mm; }
              table{width:100%; border-collapse:collapse;}
              th,td{border:1px solid #333; padding:6px; text-align:center; font-size:12px}
              thead th{background:#f2f2f2}
            </style>
          </head>
          <body>${src}
            <script>window.onload=function(){window.print(); window.close();};<\/script>
          </body>
        </html>
      `);
      win.document.close();
    }

    // ====== Export Excel (Summary + Attendance) ======
    document.getElementById('exportExcelBtn').addEventListener('click', function(){
      // Build workbook
      const wb = XLSX.utils.book_new();

      // Sheet 1: Summary
      const sWorked   = document.getElementById('sumWorked').textContent.trim();
      const sDays     = document.getElementById('sumDays').textContent.trim();
      const sExpected = document.getElementById('sumExpected').textContent.trim();

      const summaryAOA = [
        ["{{ \App\CPU\translate('ملخص السجلات') }}"],
        [],
        ["{{ \App\CPU\translate('إجمالي ساعات العمل الفعلية') }}", sWorked],
        ["{{ \App\CPU\translate('عدد أيام العمل') }}", sDays],
        ["{{ \App\CPU\translate('إجمالي ساعات العمل المتوقعة') }}", sExpected],
      ];
      const wsSummary = XLSX.utils.aoa_to_sheet(summaryAOA);
      XLSX.utils.book_append_sheet(wb, wsSummary, "Summary");

      // Sheet 2: Attendance (current page table)
      const table = document.getElementById('attendanceTable');
      const wsAttendance = XLSX.utils.table_to_sheet(table, {raw:true});
      XLSX.utils.book_append_sheet(wb, wsAttendance, "Attendance");

      // File name
      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth()+1).padStart(2,'0');
      const d = String(today.getDate()).padStart(2,'0');
      XLSX.writeFile(wb, `attendance_${y}${m}${d}.xlsx`);
    });
  </script>
