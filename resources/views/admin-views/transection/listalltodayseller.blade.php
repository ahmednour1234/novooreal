{{-- resources/views/admin/reports/transection_list.blade.php --}}
@extends('layouts.admin.app')

@section('title', \App\CPU\translate('transection_list'))

@push('css_or_js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
<style>
  /* ألوان عامة */
  .add{color:#28a745;}
  .subtract{color:#dc3545;}
  .result{color:#0d6efd;}
  .none{color:#6c757d;}

  /* رأس الكارد */
  .card-header{background:#001B63; color:#fff;}

  /* نموذج الفلاتر */
  .custom-form{background:#fff; border-radius:12px; padding:16px; box-shadow:0 4px 8px rgba(0,0,0,.06);}
  .form-label{font-weight:700; color:#495057;}
  .custom-input, .form-select{
    border:1px solid #ced4da; border-radius:8px; padding:10px 12px; font-size:1rem;
    transition:all .2s ease-in-out;
  }
  .custom-input:focus, .form-select:focus{
    border-color:#28a745; box-shadow:0 0 0 .2rem rgba(40,167,69,.15);
  }
  .btn{border-radius:10px; font-weight:700;}

  /* جدول */
  table.table{direction:rtl;}
  table.table th, table.table td{text-align:center; vertical-align:middle;}

  /* الطباعة */
  @media print{
    .no-print{display:none !important;}
    body{direction:rtl; font-family:Arial, sans-serif;}
    table{width:100%; border-collapse:collapse;}
    table th, table td{border:1px solid #ddd; padding:8px; text-align:center;}
    table th{background:#007bff; color:#fff;}
    table tr:nth-child(even){background:#f9f9f9;}
  }
</style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تقرير  المندوب') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== Filters ====== --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="custom-form">
        <form method="GET" action="{{ route('admin.taxe.listalltodaybyseller') }}" class="row g-3 align-items-end" dir="rtl">
          {{-- المندوب --}}
          <div class="col-md-4">
            <label for="seller_id" class="form-label">اختر المندوب</label>
            <select name="seller_id" id="seller_id" class="form-select custom-input">
              <option value="">{{ __('كل المناديب') }}</option>
              @foreach($sellers as $seller)
                <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                  {{ $seller->email }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- من تاريخ --}}
          <div class="col-md-4">
            <label for="from" class="form-label">من تاريخ</label>
            <input type="date" name="from" id="from" class="form-control custom-input" value="{{ request('from') }}">
          </div>

          {{-- إلى تاريخ --}}
          <div class="col-md-4">
            <label for="to" class="form-label">إلى تاريخ</label>
            <input type="date" name="to" id="to" class="form-control custom-input" value="{{ request('to') }}">
          </div>

          {{-- الأزرار --}}
          <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <button type="submit" class="btn btn-success px-4 py-2">
              تصفية
            </button>
            <a href="{{ route('admin.taxe.listalltodaybyseller') }}" class="btn btn-secondary px-4 py-2">
              إعادة تعيين
            </a>
            <button type="button" onclick="printReport()" class="btn btn-primary px-4 py-2 no-print">
              طباعة التقرير
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ====== Report Table ====== --}}
  <div class="card shadow-sm" id="table">
 
    <div class="card-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>البند</th>
            <th>المبلغ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>إجمالي المبيعات الكاش</td>
            <td class="none">{{ number_format($totalcashSales, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي المبيعات الشبكة</td>
            <td class="none">{{ number_format($totalshbakaSales, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي المبيعات الأجلة</td>
            <td class="none">{{ number_format($totalcreditSales, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي المبيعات</td>
            <td class="none">{{ number_format($totalSales, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي مبالغ المحصلة من المبيعات</td>
            <td class="add">{{ number_format($totalDoneSales, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي مردود المبيعات</td>
            <td class="none">{{ number_format($totalReSales, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي مبالغ المدفوعة من مردود المبيعات</td>
            <td class="add">{{ number_format($totalDoneReSales, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي التحصيلات النقدية كاش</td>
            <td class="add">{{ number_format($totalCreditcash, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي التحصيلات النقدية شبكة</td>
            <td class="add">{{ number_format($totalCreditshabaka, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي المبالغ المحولة من المندوب</td>
            <td class="add">{{ number_format($totalTranSeller, 2) }} ريال</td>
          </tr>
          <tr>
            <td>إجمالي المبالغ المتاحة مع المندوب نقدي</td>
            <td class="result">{{ number_format($tax, 2) }} ريال</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
  // Select2
  $(function(){
    $('#seller_id').select2({
      width:'100%',
      placeholder:'ابحث عن مندوب',
      allowClear:true
    });
  });

  // طباعة التقرير
  function printReport() {
    const fromDate = document.querySelector('#from')?.value || 'غير محدد';
    const toDate   = document.querySelector('#to')?.value || 'غير محدد';
    const printContents = document.querySelector('#table').innerHTML;

    const w = window.open('', '_blank');
    w.document.open();
    w.document.write(`
      <html>
      <head>
        <title>تقرير المندوب</title>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; direction: rtl; margin:20px; }
          h4, h6 { text-align: center; margin-bottom: 12px; }
          .header-section {
            display:flex; justify-content:space-between; align-items:center;
            border-bottom:2px solid #003366; padding-bottom:10px; margin-bottom:20px; flex-wrap:wrap;
          }
          .header-section .left, .header-section .right, .header-section .logo { width:32%; text-align:center; }
          .header-section p { margin:4px 0; line-height:1.5; font-size:15px; }
          .logo img { max-width:140px; height:auto; }
          table { width:100%; border-collapse:collapse; margin:20px 0; }
          th, td { border:1px solid #ddd; padding:8px; text-align:center; }
          th { background:#007bff; color:#fff; }
          tr:nth-child(even) { background:#f9f9f9; }
          .signatures { margin-top:30px; text-align:right; font-size:15px; display:flex; gap:12px; flex-wrap:wrap; }
          .signatures span { display:inline-block; width:32%; }
          .add{color:#28a745;} .subtract{color:#dc3545;} .result{color:#0d6efd;} .none{color:#6c757d;}
        </style>
      </head>
      <body>
        <div class="header-section">
          <div class="left">
            <p><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'vat_reg_no'])->first()->value }}</p>
            <p><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'number_tax'])->first()->value }}</p>
            <p><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'shop_email'])->first()->value }}</p>
          </div>
          <div class="logo">
            <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key'=>'shop_logo'])->first()->value) }}" alt="شعار المتجر">
          </div>
          <div class="right">
            <p><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'shop_name'])->first()->value }}</p>
            <p><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'shop_address'])->first()->value }}</p>
            <p><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where(['key'=>'shop_phone'])->first()->value }}</p>
          </div>
        </div>

        <h4>تقرير المندوب</h4>
        <h6>من تاريخ: ${fromDate} — إلى تاريخ: ${toDate}</h6>

        ${printContents}

        <div class="signatures">
          <span>توقيع مدير الحسابات: ...........................................</span>
          <span>توقيع المدير: ..................................................</span>
          <span>توقيع المندوب: ................................................</span>
        </div>
      </body>
      </html>
    `);
    w.document.close();
    w.print();
  }
</script>
