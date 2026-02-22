@extends('layouts.admin.app')

@section('title', 'تفاصيل العقد')

@push('styles')
  <style>
    @media print {
      body * {
        visibility: hidden;
      }
      #print-area, #print-area * {
        visibility: visible;
      }
      #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
@endpush

@section('content')
<div class="card shadow-sm mb-4 border-0 rounded-3" id="print-area">
  <div class="card-header bg-gradient-info text-white rounded-top d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> تفاصيل العقد</h5>
 <button type="button" onclick="printDivContent('print-area')" class="btn btn-sm btn-light text-info fw-semibold no-print">
  <i class="bi bi-printer me-1"></i> طباعة
</button>

  </div>

  <div class="card-body p-4">
    <div class="row gy-3">
      <div class="col-md-6">
        <div class="bg-light p-3 rounded shadow-sm">
          <h6 class="text-muted mb-1">رقم العقد</h6>
          <p class="fw-semibold mb-0">{{ $contract->contract_number }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="bg-light p-3 rounded shadow-sm">
          <h6 class="text-muted mb-1">العميل</h6>
          <p class="fw-semibold mb-0">{{ $contract->client->name }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="bg-light p-3 rounded shadow-sm">
          <h6 class="text-muted mb-1">عنوان العقد</h6>
          <p class="fw-semibold mb-0">{{ $contract->title }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="bg-light p-3 rounded shadow-sm">
          <h6 class="text-muted mb-1">القيمة الإجمالية</h6>
          <p class="fw-semibold mb-0 text-success">{{ number_format($contract->total_value, 2) }} جنيه</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="bg-light p-3 rounded shadow-sm">
          <h6 class="text-muted mb-1">تاريخ البداية</h6>
          <p class="fw-semibold mb-0">{{ $contract->start_date }}</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="bg-light p-3 rounded shadow-sm">
          <h6 class="text-muted mb-1">تاريخ النهاية</h6>
          <p class="fw-semibold mb-0">{{ optional($contract->end_date)->format('Y-m-d') ?? '-' }}</p>
        </div>
      </div>
      <div class="col-12">
        <div class="bg-light p-3 rounded shadow-sm">
          <h6 class="text-muted mb-1">الوصف</h6>
          <p class="mb-0">{{ $contract->description ?? '-' }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="d-flex bg-light p-3 rounded shadow-sm justify-content-between align-items-center">
          <h6 class="text-muted mb-0">الحالة</h6>
          @php
            $badge = match($contract->status) {
              'active'    => 'badge bg-success',
              'completed' => 'badge bg-info',
              'canceled'  => 'badge bg-danger',
              default     => 'badge bg-secondary',
            };
            $labels = ['draft'=>'مسودة','active'=>'نشط','completed'=>'مكتمل','canceled'=>'ملغى'];
          @endphp
          <span class="{{ $badge }} px-3 py-2">{{ $labels[$contract->status] ?? $contract->status }}</span>
        </div>
      </div>
      <div class="col-md-6">
        <div class="d-flex bg-light p-3 rounded shadow-sm justify-content-between align-items-center">
          <h6 class="text-muted mb-0">تاريخ الإنشاء</h6>
          <p class="fw-semibold mb-0">{{ $contract->created_at->format('Y-m-d H:i') }}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="card-footer text-center bg-white border-top-0 no-print">
    <a href="{{ route('admin.contracts.edit', $contract->id) }}" class="btn btn-warning me-3 px-4">
      <i class="bi bi-pencil-square me-1"></i> تعديل
    </a>
    <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary px-4">
      <i class="bi bi-arrow-left-circle me-1"></i> العودة للقائمة
    </a>
  </div>
</div>
@endsection
<script>
  function printDivContent(divId) {
    const content = document.getElementById(divId).innerHTML;

    const printWindow = window.open('', '', 'width=1000,height=800');
    printWindow.document.write(`
      <html>
        <head>
          <title>طباعة</title>
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
          <style>
            body { padding: 30px; font-family: 'Cairo', sans-serif; direction: rtl; }
            .shadow-sm { box-shadow: none !important; }
          </style>
        </head>
        <body onload="window.print(); setTimeout(() => window.close(), 200);">
          ${content}
        </body>
      </html>
    `);
    printWindow.document.close();
  }
</script>
