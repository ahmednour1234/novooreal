@extends('layouts.admin.app')

@section('title', 'قائمة جلسات الكاشير')

@push('css_or_js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
<style>
  /* تنسيقات خفيفة وحيادية */
  .filter-card{
    border:1px solid #e9ecef; border-radius: .5rem; background:#fff;
  }
  .filter-card .filter-head{
    padding:.75rem 1rem; border-bottom:1px solid #e9ecef; display:flex; align-items:center; justify-content:space-between;
  }
  .filter-card .filter-body{ padding:1rem; }
  .table thead th{ background:#f8f9fa; }
  .btn-toolbar-gap > * { margin-inline-start:.5rem; }
  .table-responsive { border:1px solid #e9ecef; border-radius:.5rem; background:#fff; }
</style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ===== Breadcrumb ===== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('الرئيسية') }}</a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('جلسات الكاشير') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ===== Filters Card ===== --}}
  @php
    $hasFilters = request()->filled('from_date') || request()->filled('to_date') || request()->filled('status') || request()->filled('admin_id') || request()->filled('branch_id');
  @endphp
  <div class="filter-card mb-3">
    <div class="filter-head">
      <h5 class="mb-0">{{ \App\CPU\translate('بحث وفلاتر جلسات الكاشير') }}</h5>
      @if($hasFilters)
        <small class="text-success fw-bold">{{ \App\CPU\translate('تم تطبيق الفلاتر') }}</small>
      @else
        <small class="text-danger fw-bold">{{ \App\CPU\translate('لن تُعرض بيانات حتى تطبق فلتر') }}</small>
      @endif
    </div>
    <div class="filter-body">
      <form method="GET" class="row gx-3 gy-2 align-items-end">
        <div class="col-sm-6 col-md-3">
          <label class="form-label mb-1">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-sm-6 col-md-3">
          <label class="form-label mb-1">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">{{ \App\CPU\translate('الحالة') }}</label>
          <select name="status" class="form-control searchable">
            <option value="">{{ \App\CPU\translate('الكل') }}</option>
            <option value="open"   {{ request('status')==='open'?'selected':'' }}>{{ \App\CPU\translate('مفتوحة') }}</option>
            <option value="closed" {{ request('status')==='closed'?'selected':'' }}>{{ \App\CPU\translate('مغلقة') }}</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">{{ \App\CPU\translate('الموظف') }}</label>
          <select name="admin_id" class="form-control searchable">
            <option value="">{{ \App\CPU\translate('الكل') }}</option>
            @foreach($admins as $admin)
              <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                {{ $admin->f_name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">{{ \App\CPU\translate('الفرع') }}</label>
          <select name="branch_id" class="form-control searchable">
            <option value="">{{ \App\CPU\translate('الكل') }}</option>
            @foreach($branches as $branch)
              <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                {{ $branch->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 mt-3 d-flex flex-wrap align-items-center justify-content-between">
          <div class="mb-2 mb-sm-0">
            {{-- بحث فوري على الجدول (كلمات) --}}
            <input id="tableQuickSearch" type="text" class="form-control" style="min-width:280px"
                   placeholder="🔍 {{ \App\CPU\translate('ابحث داخل النتائج.. (اسم موظف/فرع/حالة)') }}">
          </div>
          <div class="btn-toolbar-gap">
            <button type="submit" class="btn btn-secondary">
              <i class="tio-search"></i> {{ \App\CPU\translate('بحث') }}
            </button>
            <a href="{{ url()->current() }}" class="btn btn-danger">
              <i class="tio-clear"></i> {{ \App\CPU\translate('إلغاء الفلاتر') }}
            </a>
            <button type="button" class="btn btn-primary" onclick="printTable()">
              <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
            </button>
            <button type="button" class="btn btn-info" onclick="exportTableToCSV('cashier-sessions.csv')">
              <i class="tio-download"></i> {{ \App\CPU\translate('تصدير Excel') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ===== Data Table ===== --}}
  <div id="product-table" class="table-responsive p-0">
    <table id="sessionsTable" class="table table-hover mb-0">
      <thead>
      <tr>
        <th>#</th>
        <th>{{ \App\CPU\translate('الموظف') }}</th>
        <th>{{ \App\CPU\translate('الفرع') }}</th>
        <th>{{ \App\CPU\translate('وقت الفتح') }}</th>
        <th>{{ \App\CPU\translate('وقت الإغلاق') }}</th>
        <th>{{ \App\CPU\translate('عدد فواتير البيع') }}</th>
        <th>{{ \App\CPU\translate('عدد المرتجعات') }}</th>
        <th>{{ \App\CPU\translate('إجمالي المبيعات') }}</th>
        <th>{{ \App\CPU\translate('إجمالي المرتجعات') }}</th>
        <th>{{ \App\CPU\translate('إجمالي الخصومات') }}</th>
        <th>{{ \App\CPU\translate('الحالة') }}</th>
      </tr>
      </thead>
      <tbody>
      @forelse($sessions as $idx => $session)
        <tr>
          <td>{{ ($sessions->firstItem() ?? 1) + $idx }}</td>
          <td class="text-nowrap">{{ $session->admin->email ?? '—' }}</td>
          <td class="text-nowrap">{{ $session->branch->name ?? '—' }}</td>
          <td class="text-nowrap">{{ $session->start_time }}</td>
          <td class="text-nowrap">{{ $session->end_time ?? '—' }}</td>
          <td>{{ $session->total_orders ?? 0 }}</td>
          <td>{{ $session->total_returns ?? 0 }}</td>
          <td>{{ number_format($session->total_cash, 2) }} {{ \App\CPU\translate('ر.س') }}</td>
          <td>{{ number_format($session->total_amount_returns, 2) }} {{ \App\CPU\translate('ر.س') }}</td>
          <td>{{ number_format($session->total_discount, 2) }} {{ \App\CPU\translate('ر.س') }}</td>
          <td>
            <span class="badge bg-{{ $session->status === 'open' ? 'success' : 'secondary' }}">
              {{ $session->status === 'open' ? \App\CPU\translate('مفتوحة') : \App\CPU\translate('مغلقة') }}
            </span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="11" class="text-center py-4">
            <img class="mb-3" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" style="width:100px">
            <div class="text-muted">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>

    <div id="links" class="p-3 d-flex justify-content-end">
      {{ $sessions->withQueryString()->links() }}
    </div>
  </div>

</div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
  // Select2
  $(function(){
    $('select.searchable').select2({ placeholder:'{{ \App\CPU\translate('اختر') }}', allowClear:true, width:'100%' });
  });

  // بحث سريع داخل الجدول (فرونت فقط)
  (function(){
    const input = document.getElementById('tableQuickSearch');
    const table = document.getElementById('sessionsTable');
    if(!input || !table) return;

    const rows = Array.from(table.tBodies[0].rows);
    input.addEventListener('input', function(){
      const term = this.value.trim().toLowerCase();
      rows.forEach(tr=>{
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(term) ? '' : 'none';
      });
    });
  })();

  // طباعة
  function printTable() {
    const tableContent = document.getElementById('product-table').innerHTML;
    const w = window.open('', '_blank', 'width=1000,height=700');
    w.document.write(`
      <!DOCTYPE html>
      <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>{{ \App\CPU\translate('تقرير جلسات الكاشير') }}</title>
        <style>
          body{font-family: Arial, sans-serif; margin:24px; color:#333;}
          h2{margin:0 0 16px; font-size:20px;}
          .header{display:flex; justify-content:space-between; gap:16px; border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:16px;}
          .header div{font-size:12px; line-height:1.6}
          table{width:100%; border-collapse:collapse; font-size:12px;}
          th,td{border:1px solid #e5e7eb; padding:8px; text-align:center; white-space:nowrap;}
          thead th{background:#f8f9fa;}
          #links{ display:none; }
          img{ max-width:140px; }
        </style>
      </head>
      <body>
        <div class="header">
          <div>
            <div><strong>{{ \App\CPU\translate('اسم المؤسسة') }}:</strong> {{ \App\Models\BusinessSetting::where('key','shop_name')->value('value') }}</div>
            <div><strong>{{ \App\CPU\translate('العنوان') }}:</strong> {{ \App\Models\BusinessSetting::where('key','shop_address')->value('value') }}</div>
            <div><strong>{{ \App\CPU\translate('الهاتف') }}:</strong> {{ \App\Models\BusinessSetting::where('key','shop_phone')->value('value') }}</div>
          </div>
          <div style="text-align:center">
            <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where('key','shop_logo')->value('value')) }}" onerror="this.style.display='none'">
          </div>
          <div>
            <div><strong>{{ \App\CPU\translate('السجل التجاري') }}:</strong> {{ \App\Models\BusinessSetting::where('key','vat_reg_no')->value('value') }}</div>
            <div><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> {{ \App\Models\BusinessSetting::where('key','number_tax')->value('value') }}</div>
            <div><strong>{{ \App\CPU\translate('البريد الإلكتروني') }}:</strong> {{ \App\Models\BusinessSetting::where('key','shop_email')->value('value') }}</div>
          </div>
        </div>
        <h2>{{ \App\CPU\translate('تقرير جلسات الكاشير') }}</h2>
        ${tableContent}
        <script>window.onload = function(){ window.print(); window.close(); }<\/script>
      </body>
      </html>
    `);
    w.document.close();
  }

  // تصدير CSV (متوافق مع Excel)
  function exportTableToCSV(filename){
    const table = document.getElementById('sessionsTable');
    if(!table) return;

    const rows = Array.from(table.querySelectorAll('tr'));
    const csv = [];
    rows.forEach(tr=>{
      // تخطّي الصفوف المخفية (عند استخدام البحث السريع)
      if (tr.style.display === 'none') return;
      const cols = Array.from(tr.querySelectorAll('th,td')).map(td=>{
        let text = td.innerText.replace(/\s+/g,' ').trim();
        // تأمين الفواصل والعلامات
        if (text.includes(',') || text.includes('"') || text.includes('\n')) {
          text = '"' + text.replace(/"/g, '""') + '"';
        }
        return text;
      });
      if (cols.length) csv.push(cols.join(','));
    });

    const blob = new Blob(["\ufeff"+csv.join('\n')], { type: 'text/csv;charset=utf-8;' }); // BOM للعربية
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename || 'export.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
</script>
