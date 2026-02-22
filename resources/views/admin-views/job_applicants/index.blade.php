@extends('layouts.admin.app')

@section('title', \App\CPU\translate('متقدمين الوظائف'))

@push('css_or_js')
<style>
  :root{
    --ink:#0f172a; --muted:#667085; --grid:#e9eef5; --brand:#001B63;
    --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
  }
  .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
  .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .page-title{font-size:1.35rem;color:var(--brand);font-weight:800;margin:0}
  .toolbar{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar .btn{min-height:42px}

  .table-wrap{overflow:auto;margin-top:12px}
  table thead th{background:#f3f6fb;position:sticky;top:0;z-index:5}
  table td, table th{vertical-align:middle}
  .table-hover tbody tr:hover{background:#f9fbff}

  .status-badge{padding:.35rem .6rem;border-radius:999px;font-size:.78rem;font-weight:700}
  .st-new{background:#eff6ff;color:#1d4ed8}
  .st-screening{background:#fff7ed;color:#9a3412}
  .st-interview{background:#ecfeff;color:#0e7490}
  .st-accepted{background:#ecfdf5;color:#065f46}
  .st-rejected{background:#fef2f2;color:#991b1b}

  .btn-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;padding:0}
  .actions{display:flex;flex-wrap:wrap;gap:10px}

  /* طباعة */
  @media print{
    .no-print{display:none !important}
    .no-print-col{display:none !important}
    body{print-color-adjust:exact;-webkit-print-color-adjust:exact}
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
        <li class="breadcrumb-item active">{{ \App\CPU\translate('قائمة طلبات التوظيف') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== رأس الصفحة + أدوات ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1 class="page-title">{{ \App\CPU\translate('متقدمين الوظائف') }}</h1>
      <div class="toolbar no-print">
        <button type="button" class="btn btn-outline-primary" onclick="printApplicantsTable()">
          <i class="tio-print"></i> {{ \App\CPU\translate('طباعة الجدول') }}
        </button>
      </div>
    </div>
  </div>

  {{-- ====== الجدول ====== --}}
  <div class="card-soft p-3">
    <div class="table-wrap print-scope">
      <table class="table table-bordered table-hover align-middle" id="applicantsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الاسم') }}</th>
            <th>{{ \App\CPU\translate('البريد الالكتروني') }}</th>
            <th>{{ \App\CPU\translate('رقم الهاتف') }}</th>
            <th>{{ \App\CPU\translate('السيرة الذاتية') }}</th>
            <th>{{ \App\CPU\translate('تاريخ التقديم') }}</th>
            <th>{{ \App\CPU\translate('الحالة') }}</th>
            <th class="no-print-col" style="width:180px">{{ \App\CPU\translate('الإجراءات') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($applicants as $applicant)
            @php
              $statusMapping = [
                'new'       => ['label' => 'جديد',      'cls' => 'st-new'],
                'screening' => ['label' => 'قيد الفرز',  'cls' => 'st-screening'],
                'interview' => ['label' => 'المقابلة',  'cls' => 'st-interview'],
                'accepted'  => ['label' => 'مقبول',     'cls' => 'st-accepted'],
                'rejected'  => ['label' => 'مرفوض',     'cls' => 'st-rejected'],
              ];
              $st = $statusMapping[$applicant->status] ?? ['label' => ucfirst($applicant->status), 'cls' => 'st-new'];
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $applicant->full_name }}</td>
              <td>{{ $applicant->email }}</td>
              <td>{{ $applicant->phone ?? '—' }}</td>
              <td>
                @if(!empty($applicant->resume_pdf))
                  <a class="btn btn-sm btn-white btn-icon" href="{{ asset('storage/app/public/resumes/' . $applicant->resume_pdf) }}" target="_blank" title="{{ \App\CPU\translate('عرض السيرة الذاتية') }}" data-toggle="tooltip">
                    <i class="tio-file-text-outlined"></i>
                  </a>
                @else
                  —
                @endif
              </td>
              <td>{{ \Carbon\Carbon::parse($applicant->applied_date)->format('Y-m-d') }}</td>
              <td><span class="status-badge {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
              <td class="no-print-col">
                <div class="actions">
                  {{-- تعديل --}}
                  <a href="{{ route('admin.job_applicants.edit', $applicant->id) }}"
                     class="btn btn-info btn-sm btn-icon"
                     title="{{ \App\CPU\translate('تعديل') }}" data-toggle="tooltip">
                    <i class="tio-edit"></i>
                  </a>

                  {{-- تاريخ المقابلات --}}
                  <a href="{{ route('admin.job_applicants.interviews', $applicant->id) }}"
                     class="btn btn-secondary btn-sm btn-icon"
                     title="{{ \App\CPU\translate('تاريخ المقابلات') }}" data-toggle="tooltip">
                    <i class="tio-calendar"></i>
                  </a>

                  {{-- إضافة مقابلات --}}
                  <a href="{{ route('admin.interview_evaluations.create', $applicant->id) }}"
                     class="btn btn-success btn-sm btn-icon"
                     title="{{ \App\CPU\translate('إضافة المقابلات') }}" data-toggle="tooltip">
                    <i class="tio-add"></i>
                  </a>

                  {{-- حذف --}}
                  <form action="{{ route('admin.job_applicants.destroy', $applicant->id) }}"
                        method="POST" onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد من الحذف؟') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm btn-icon"
                            title="{{ \App\CPU\translate('حذف') }}" data-toggle="tooltip">
                      <i class="tio-delete-outlined"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted">{{ \App\CPU\translate('لا يوجد بيانات') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
      {{ $applicants->links() }}
    </div>
  </div>

</div>
@endsection

<script>
  // بيانات المتجر للطباعة (نحقنها من السيرفر بأمان)
  const SHOP = {
    name:    @json(optional(\App\Models\BusinessSetting::where('key','shop_name')->first())->value),
    address: @json(optional(\App\Models\BusinessSetting::where('key','shop_address')->first())->value),
    phone:   @json(optional(\App\Models\BusinessSetting::where('key','shop_phone')->first())->value),
    email:   @json(optional(\App\Models\BusinessSetting::where('key','shop_email')->first())->value),
    vat:     @json(optional(\App\Models\BusinessSetting::where('key','vat_reg_no')->first())->value),
    tax:     @json(optional(\App\Models\BusinessSetting::where('key','number_tax')->first())->value),
    logo:    @json(asset('storage/app/public/shop/' . (optional(\App\Models\BusinessSetting::where('key','shop_logo')->first())->value ?? '')))
  };

  // تفعيل Tooltips لو Bootstrap موجود
  (function(){
    if (typeof $ !== 'undefined' && typeof $.fn.tooltip === 'function') {
      $('[data-toggle="tooltip"]').tooltip();
    }
  })();

  // طباعة الجدول
  function printApplicantsTable() {
    // انسخ الجدول مع إخفاء عمود الإجراءات
    const table = document.getElementById('applicantsTable').cloneNode(true);
    table.querySelectorAll('.no-print-col').forEach(el => el.remove());

    const now = new Date().toLocaleString('ar-EG', {
      year:'numeric', month:'2-digit', day:'2-digit',
      hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false
    });

    const w = window.open('', '', 'width=1000,height=900');
    w.document.write(`
      <html dir="rtl">
      <head>
        <meta charset="utf-8">
        <title>{{ \App\CPU\translate('طباعة المتقدمين') }}</title>
        <style>
          body{font-family: Tahoma, Arial, sans-serif; margin:16px; color:#111827}
          .header{display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding-bottom:10px; border-bottom:2px solid #000}
          .header .info{width:33%}
          .header .logo{width:33%; text-align:center}
          .header .logo img{max-height:80px; max-width:160px}
          h2{margin:12px 0; font-size:18px; text-align:center}
          table{width:100%; border-collapse: collapse; margin-top: 10px;}
          th, td{border:1px solid #333; padding:8px; text-align:center; font-size:13px}
          th{background:#f2f2f2}
          .foot{margin-top:10px; font-size:12px; color:#555}
        </style>
      </head>
      <body>
        <div class="header">
          <div class="info">
            <div><strong>{{ \App\CPU\translate('اسم المتجر') }}:</strong> ${SHOP.name ?? '—'}</div>
            <div><strong>{{ \App\CPU\translate('العنوان') }}:</strong> ${SHOP.address ?? '—'}</div>
            <div><strong>{{ \App\CPU\translate('الهاتف') }}:</strong> ${SHOP.phone ?? '—'}</div>
          </div>
          <div class="logo">
            ${SHOP.logo ? `<img src="${SHOP.logo}" alt="logo">` : ''}
          </div>
          <div class="info" style="text-align:right">
            <div><strong>{{ \App\CPU\translate('رقم السجل التجاري') }}:</strong> ${SHOP.vat ?? '—'}</div>
            <div><strong>{{ \App\CPU\translate('الرقم الضريبي') }}:</strong> ${SHOP.tax ?? '—'}</div>
            <div><strong>{{ \App\CPU\translate('البريد') }}:</strong> ${SHOP.email ?? '—'}</div>
          </div>
        </div>
        <h2>{{ \App\CPU\translate('تقرير المتقدمين للوظائف') }}</h2>
        <div style="text-align:center; margin-bottom:6px"><strong>{{ \App\CPU\translate('تاريخ الطباعة') }}:</strong> ${now}</div>
    `);
    w.document.body.appendChild(table);
    w.document.write(`
        <div class="foot" style="text-align:center">{{ \App\CPU\translate('تمت الطباعة بواسطة النظام') }}</div>
        <script>window.onload=function(){window.print(); window.close();};<\/script>
      </body></html>
    `);
    w.document.close();
  }
</script>
