<!-- resources/views/admin/quotations/show.blade.php -->
@extends('layouts.admin.app')

@section('content')
<!-- Bootstrap CSS -->

<!-- Bootstrap JS -->

<style>
  .invoice-card {
    background: #fff;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
  }
  .invoice-header {
    border-bottom: 3px solid gray;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
  }
  .invoice-title {
    color: black;
    font-size: 2rem;
    font-weight: 700;
  }
  .invoice-subtitle {
    color: black;
  }
  .invoice-meta p,
  .invoice-details h5,
  .invoice-details p {
    color: #212529;
  }
  
  }
  .summary-box {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
  }
  .summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
  }
  .summary-total {
    font-weight: bold;
    border-top: 2px solid #000;
    padding-top: 1rem;
  }
  .modal-header {
    background-color: #004085;
    color: #fff;
  }
  .modal-content {
    border-radius: 0.75rem;
  }
  .form-floating label {
    padding: 0.5rem 1rem;
  }
  .btn-cash {
    background-color: #004085;
    color: white;
  }
  .btn-credit {
    background-color: #ffc107;
    color: white;
  }
  @media print {
    .no-print { display: none; }
  }
  .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    color: #ffffff;
}
</style>
<div class="content container-fluid">

        <div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                    <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.costcenter.add') }}" class="text-black">
                    {{ \App\CPU\translate('فاتورة مبيعات') }}
                </a>
            </li>
            
        </ol>
    </nav>
</div>
<div class="invoice-card">
  <div class="invoice-header d-flex justify-content-between align-items-center">
    <div>
      <h2 class="invoice-title mb-0">فاتورة بيع</h2>
      <p class="invoice-subtitle mb-0">Novoo ERP System</p>
    </div>
    <div class="text-end invoice-meta">
      <p><strong>التاريخ:</strong> {{ $quotation->created_at->format('Y-m-d') }}</p>
      <p><strong>الحالة:</strong> {{ ['مسودة','مسودة','منفذ'][$quotation->status] ?? 'غير معروف' }}</p>
    </div>
  </div>

  <div class="row invoice-details mb-4">
    <div class="col-md-6">
      <h5>تفاصيل العميل</h5>
      <p>
        <strong>الاسم:</strong> {{ $quotation->customer->name }}<br>
        <strong>جوال:</strong> {{ $quotation->customer->mobile }}<br>
        <strong>البريد:</strong> {{ $quotation->customer->email }}<br>
        <strong>العنوان:</strong> {{ $quotation->customer->address }}
      </p>
    </div>
    <div class="col-md-6 text-md-end">
      <h5>الفرع والمنفّذ</h5>
      <p>
        <strong>رقم الفاتورة:</strong> #{{ $quotation->id }}<br>
        <strong>الفرع:</strong> {{ $quotation->branch->name ?? '—' }}<br>
        <strong>المنفّذ:</strong> {{ optional($quotation->seller)->f_name . ' ' . optional($quotation->seller)->l_name ?? '—' }}
      </p>
    </div>
  </div>

  <div class="table-responsive mb-4">
@php
    $isService = $quotation->quotation_type === 'service';
@endphp

<table class="table  invoice-table">
  <thead>
    <tr>
      <th>المنتج</th>
      <th>الكمية</th>
      @unless($isService)
        <th>سعر الوحدة</th>
        <th>ضريبة/وحدة</th>
        <th>خصم/وحدة</th>
        <th>خصم إضافي/وحدة</th>
      @else
        <th>السعر</th>
        <th>الضريبة</th>
        <th>الخصم</th>
        <th>الخصم الإضافي</th>
      @endunless
      <th class="text-end">الإجمالي</th>
    </tr>
  </thead>
  <tbody>
    @foreach($quotation->details as $detail)
      @php
        $pd = json_decode($detail->product_details, true) ?: [];
        $line = ($detail->price + $detail->tax_amount - $detail->discount_on_product - $detail->extra_discount_on_product) * $detail->quantity;
      @endphp
      <tr>
        <td>{{ $pd['name'] ?? '—' }}</td>
        <td>{{ $detail->quantity }}</td>
        <td>{{ number_format($detail->price, 2) }}</td>
        <td>{{ number_format($detail->tax_amount, 2) }}</td>
        <td>{{ number_format($detail->discount_on_product, 2) }}</td>
        <td>{{ number_format($detail->extra_discount_on_product, 2) }}</td>
        <td class="text-end">{{ number_format($line, 2) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
  </div>

  <div class="row justify-content-end mb-4">
    <div class="col-md-5 summary-box">
      <div class="summary-row"><span>الإجمالي الفرعي</span><span>{{ number_format($quotation->details->sum(fn($d)=>$d->price*$d->quantity),2) }}</span></div>
      <div class="summary-row"><span>خصم المنتجات</span><span>{{ number_format($quotation->details->sum(fn($d)=>$d->discount_on_product*$d->quantity),2) }}</span></div>
      <div class="summary-row"><span>الخصم الإضافي</span><span>{{ number_format($quotation->extra_discount,2) }}</span></div>
      <div class="summary-row"><span>إجمالي الضرائب</span><span>{{ number_format($quotation->total_tax,2) }}</span></div>
      <div class="summary-row summary-total"><span>الإجمالي النهائي</span><span>{{ number_format($quotation->order_amount,2) }}</span></div>
    </div>
  </div>

@if($quotation->status != 2)
    <div class="no-print mb-4 d-flex justify-content-end">
        <form action="{{ route('admin.sells.destroy', $quotation->id) }}" 
              method="POST" 
              class="d-inline" 
              onsubmit="return confirm('هل أنت متأكد من الحذف؟');"
              style="margin-left:12px;">
            @csrf 
            @method('DELETE')
            <button type="submit" class="btn btn-danger px-5 py-1 fw-bold" style="font-size:1rem;">
                حذف
            </button>
        </form>

        <button id="openExecuteBtn" class="btn btn-primary px-5 py-1 fw-bold" style="font-size:1rem;">
            تنفيذ
        </button>
    </div>
@endif

</div>
</div>
@include('admin-views.sell.partials.execute-modal', ['quotation' => $quotation, 'accounts' => $accounts, 'cost_centers' => $cost_centers])

@endsection
