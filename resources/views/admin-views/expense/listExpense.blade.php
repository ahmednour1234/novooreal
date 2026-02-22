@extends('layouts.admin.app')

@section('title', \App\CPU\translate('Expense List'))

@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}">
  <style>
    /* Custom styling for a modern and clean design */
    .card {
      border: none;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 1.5rem;
    }
    .card-header {
      background-color: #001B63;
      color: #fff;
      font-size: 1.25rem;
      font-weight: 600;
      padding: 1rem 1.5rem;
    }
    .card-body {
      padding: 1.5rem;
      background-color: #fff;
    }
    .form-section {
      margin-bottom: 1.5rem;
      padding: 1rem;
      background-color: #f9f9f9;
      border-radius: 6px;
    }
    .form-section .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      border-bottom: 2px solid #001B63;
      padding-bottom: 0.5rem;
    }
    label {
      font-weight: 500;
    }
    .required::after {
      content: "*";
      color: red;
      margin-left: 0.25rem;
    }
    .table-responsive {
      margin-top: 1rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table th, table td {
      padding: 0.75rem;
      border: 1px solid #dee2e6;
      text-align: left;
    }
    table th {
      background-color: #f8f9fa;
    }
    .btn-print {
      margin-bottom: 1rem;
    }
    @media print {
      .no-print {
        display: none;
      }
    }
    /* Print area styling */
    .print-area {
      padding: 1rem;
    }
  </style>
@endpush

@section('content')
<div class="content container-fluid print-area">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex align-items-center">
        <i class="tio-receipt-outlined" style="font-size: 2rem; color: #001B63;"></i>
        <h1 class="ml-2 mb-0">{{ \App\CPU\translate('قائمة المصروفات') }}</h1>
      </div>
    </div>
  </div>
  <!-- End Page Header -->

  <!-- Filter Section -->
  <div class="form-section no-print">
  <!--  <div class="section-title">{{ \App\CPU\translate('تصفية المصروفات') }}</div>-->
  <!--  <form action="{{ url()->current() }}" method="GET" id="filterForm">-->
  <!--    <div class="row">-->
        <!-- Date Range -->
  <!--      <div class="col-md-3 mb-3">-->
  <!--        <label class="required">{{ \App\CPU\translate('من تاريخ') }}</label>-->
  <!--        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" required>-->
  <!--      </div>-->
  <!--      <div class="col-md-3 mb-3">-->
  <!--        <label class="required">{{ \App\CPU\translate('الى تاريخ') }}</label>-->
  <!--        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" required>-->
  <!--      </div>-->
        <!-- Account Filter -->
  <!--      <div class="col-md-2 mb-3">-->
  <!--        <label>{{ \App\CPU\translate('الحساب') }}</label>-->
  <!--        <select name="account_id" class="form-control js-select2-custom">-->
  <!--          <option value="">{{ \App\CPU\translate('كل الحسابات') }}</option>-->
  <!--          @foreach($accounts as $account)-->
  <!--            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>-->
  <!--              {{ $account->account }}-->
  <!--            </option>-->
  <!--          @endforeach-->
  <!--        </select>-->
  <!--      </div>-->
        <!-- Branch Filter -->
  <!--      <div class="col-md-2 mb-3">-->
  <!--        <label>{{ \App\CPU\translate('الفرع') }}</label>-->
  <!--        <select name="branch_id" class="form-control js-select2-custom">-->
  <!--          <option value="">{{ \App\CPU\translate('كل الفروع') }}</option>-->
  <!--          @foreach($branches as $branch)-->
  <!--            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>-->
  <!--              {{ $branch->name }}-->
  <!--            </option>-->
  <!--          @endforeach-->
  <!--        </select>-->
  <!--      </div>-->
        <!-- Seller Filter -->
  <!--      <div class="col-md-2 mb-3">-->
  <!--        <label>{{ \App\CPU\translate('الكاتب') }}</label>-->
  <!--        <select name="seller_id" class="form-control js-select2-custom">-->
  <!--          <option value="">{{ \App\CPU\translate('الجميع') }}</option>-->
  <!--          @foreach($sellers as $seller)-->
  <!--            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>-->
  <!--              {{ $seller->f_name }}-->
  <!--            </option>-->
  <!--          @endforeach-->
  <!--        </select>-->
  <!--      </div>-->
        <!-- Cost Center Filter -->
  <!--      <div class="col-md-2 mb-3">-->
  <!--        <label>{{ \App\CPU\translate('مركز التكلفة') }}</label>-->
  <!--        <select name="cost_id" class="form-control js-select2-custom">-->
  <!--          <option value="">{{ \App\CPU\translate('كل مراكز التكلفة') }}</option>-->
  <!--          @foreach($costcenters as $costcenter)-->
  <!--            <option value="{{ $costcenter->id }}" {{ request('cost_id') == $costcenter->id ? 'selected' : '' }}>-->
  <!--              {{ $costcenter->name }}-->
  <!--            </option>-->
  <!--          @endforeach-->
  <!--        </select>-->
  <!--      </div>-->
  <!--    </div>-->
  <!--    <div class="row">-->
  <!--      <div class="col-md-12 text-right">-->
  <!--        <button type="submit" class="btn btn-primary">-->
  <!--          {{ \App\CPU\translate('تطبيق الفلتر') }}-->
  <!--        </button>-->
  <!--      </div>-->
  <!--    </div>-->
  <!--  </form>-->
  <!--</div>-->
  
  <!-- Print Button -->
  <!--<div class="text-right no-print mb-3">-->
  <!--  <button class="btn btn-sm btn-outline-primary shadow-lg" onclick="printAllTable()">-->
  <!--    <i class="tio-print mr-2"></i> {{ \App\CPU\translate('طباعة الكل') }}-->
  <!--  </button>-->
  <!--</div>-->
            
  <!-- Total Expenses Section -->
  <!--<div class="form-section print-area">-->
  <!--  <div class="section-title">{{ \App\CPU\translate('إجمالي المصروفات') }}</div>-->
  <!--  <div class="row">-->
  <!--    <div class="col-md-12">-->
  <!--      <h4>{{ \App\CPU\translate('المجموع الكلي') }}: {{ number_format($totalAmount, 2) }}</h4>-->
  <!--    </div>-->
  <!--  </div>-->
  <!--</div>-->

  <!-- Expenses Table -->
  <div class="table-responsive">
    <table class="table table-striped table-hover datatable-custom">
      <thead>
        <tr>
          <th>{{ \App\CPU\translate('رقم') }}</th>
          <!--<th>{{ \App\CPU\translate('التاريخ') }}</th>-->
          <!--<th>{{ \App\CPU\translate('الحساب') }}</th>-->
          <!--<th>{{ \App\CPU\translate('مركز التكلفة') }}</th>-->
          <!--<th>{{ \App\CPU\translate('الفرع') }}</th>-->
          <th>{{ \App\CPU\translate('الكاتب') }}</th>
          <th>{{ \App\CPU\translate('الوصف') }}</th>
          <!--<th>{{ \App\CPU\translate('المبلغ') }}</th>-->
          <!--<th>{{ \App\CPU\translate('الإيصال') }}</th>-->
        </tr>
      </thead>
      <tbody>
        @forelse($expenses as $expense)
        <tr>
          <td>{{ $expense->id }}</td>
          <!--<td>{{ $expense->date }}</td>-->
          <!--<td>{{ $expense->account->account ?? '-' }}</td>-->
          <!--<td>{{ $expense->costcenter->name ?? '-' }}</td>-->
          <!--<td>{{ $expense->branch->name ?? '-' }}</td>-->
          <td>{{ $expense->seller->name ?? '-' }}</td>
          <td>{{ $expense->description }}</td>
          <!--<td>{{ number_format($expense->amount, 2) }}</td>-->
          <!--<td>-->
          <!--  @if($expense->attachment)-->
          <!--    <a href="{{ asset('storage/app/public/expenses/' . $expense->attachment) }}" target="_blank">-->
          <!--      {{ \App\CPU\translate('عرض') }}-->
          <!--    </a>-->
          <!--  @else-->
          <!--    {{ \App\CPU\translate('لا يوجد') }}-->
          <!--  @endif-->
          <!--</td>-->
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center">
            {{ \App\CPU\translate('لا توجد مصروفات متوفرة') }}
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    {{ $expenses->links() }}
  </div>
</div>
@endsection

@push('scripts')
@endpush

<script>
    function printAllTable() {
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head>');
        printWindow.document.write('<meta charset="utf-8">');
        printWindow.document.write('<title>{{ \App\CPU\translate("تقرير المصروفات اليومية") }}</title>');
        printWindow.document.write('<style>');
        printWindow.document.write(`
            body {
                direction: rtl;
                font-family: 'Cairo', Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background: #f4f4f9;
                color: #333;
            }
            .header-section {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 30px;
                border-bottom: 2px solid #ddd;
                padding-bottom: 10px;
            }
            .header-section .left, .header-section .right {
                width: 30%;
                font-size: 14px;
            }
            .header-section p {
                margin: 5px 0;
                line-height: 1.4;
                font-size: 16px;
                color: #333;
            }
            .logo {
                text-align: center;
                width: 30%;
            }
            .logo img {
                max-width: 150px;
                height: auto;
            }
            h2 {
                text-align: center;
                color: #444;
                margin-bottom: 20px;
                font-size: 24px;
                font-weight: bold;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                background: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            th, td {
                border: 1px solid #ddd;
                padding: 12px 15px;
                text-align: center;
                font-size: 14px;
                color: #333;
            }
            th {
                background: #007bff;
                color: #fff;
                font-weight: bold;
            }
            td {
                background: #f9f9f9;
            }
            td img, td button {
                display: none;
            }
            @media print {
                @page {
                    margin: 0mm;
                }
                footer {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    text-align: center;
                    font-size: 12px;
                    color: #555;
                    padding: 10px;
                    background-color: #f4f4f9;
                }
            }
        `);
        printWindow.document.write('</style></head><body>');

        // استرجاع بيانات المتجر
        let shopName    = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}";
        let shopAddress = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}";
        let shopPhone   = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}";
        let shopEmail   = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}";
        let taxNumber   = "{{ \App\Models\BusinessSetting::where(['key' => 'number_tax'])->first()->value }}";
        let vatRegNo    = "{{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}";

        // استخدام نفس مفاتيح التاريخ كما في النموذج
        let urlParams   = new URLSearchParams(window.location.search);
        let fromDate    = urlParams.get('start_date') ? urlParams.get('start_date') : '';
        let toDate      = urlParams.get('end_date') ? urlParams.get('end_date') : '';

        // كتابة محتوى الهيدر
        printWindow.document.write('<div class="header-section">');
        printWindow.document.write('<div class="right">');
        printWindow.document.write('<p><strong>رقم السجل التجاري:</strong> ' + vatRegNo + '</p>');
        printWindow.document.write('<p><strong>الرقم الضريبي:</strong> ' + taxNumber + '</p>');
        printWindow.document.write('<p><strong>البريد الإلكتروني:</strong> ' + shopEmail + '</p>');
        printWindow.document.write('</div>');
        
        let logoUrl = "{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}";
        printWindow.document.write('<div class="logo">');
        printWindow.document.write('<img src="' + logoUrl + '" alt="{{ \App\CPU\translate("شعار المتجر") }}">');
        printWindow.document.write('</div>');
        
        printWindow.document.write('<div class="left">');
        printWindow.document.write('<p><strong>اسم المتجر:</strong> ' + shopName + '</p>');
        printWindow.document.write('<p><strong>العنوان:</strong> ' + shopAddress + '</p>');
        printWindow.document.write('<p><strong>رقم الجوال:</strong> ' + shopPhone + '</p>');
        printWindow.document.write('</div>');
        printWindow.document.write('</div>');
        
        // كتابة عنوان التقرير والتواريخ
        printWindow.document.write('<h2>{{ \App\CPU\translate("تقرير المصروفات اليومية") }}</h2>');
        printWindow.document.write('<div style="text-align: center;">');
        printWindow.document.write('<p><strong>من تاريخ:</strong> ' + fromDate + ' <strong>إلى تاريخ:</strong> ' + toDate + '</p>');

        let currentDateTime = new Date().toLocaleString('ar-EG', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        printWindow.document.write('<p><strong>تاريخ الطباعة:</strong> ' + currentDateTime + '</p>');
        printWindow.document.write('</div>');
        
        // استرجاع محتوى الجدول من الصفحة
        let tableContent = document.querySelector('.datatable-custom table').outerHTML;
        printWindow.document.write(tableContent);

        // إضافة الفوتر (مثال: إجمالي المصروفات)
        let totalAmount = {{ $expenses->sum('amount') }};
        printWindow.document.write('<footer>');
        printWindow.document.write('<p><strong>{{ \App\CPU\translate("المجموع الكلي") }}:</strong> ' + totalAmount.toFixed(2) + '</p>');
        printWindow.document.write('</footer>');
        
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>
