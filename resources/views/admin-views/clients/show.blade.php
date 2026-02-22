@extends('layouts.admin.app')

@section('title', 'تفاصيل العميل')

@section('content')
<div class="card shadow-sm border-0">
  <div class="card-header bg-primary text-white rounded-top">
    <h5 class="mb-0" style="color:white;"><i class="bi bi-person-lines-fill me-2"></i> تفاصيل العميل</h5>
  </div>

  <div class="card-body p-4">
    <div class="row gy-3">
      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">الرقم التعريفي</h6>
          <p class="fw-semibold mb-0">{{ $client->id }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">الاسم</h6>
          <p class="fw-semibold mb-0">{{ $client->name }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">البريد الإلكتروني</h6>
          <p class="fw-semibold mb-0">{{ $client->email ?? '-' }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">الهاتف</h6>
          <p class="fw-semibold mb-0">{{ $client->phone ?? '-' }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">اسم الشركة</h6>
          <p class="fw-semibold mb-0">{{ $client->company_name ?? '-' }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">العنوان</h6>
          <p class="fw-semibold mb-0">{{ $client->address ?? '-' }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">الرقم الضريبي</h6>
          <p class="fw-semibold mb-0">{{ $client->tax_number ?? '-' }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">شخص الاتصال</h6>
          <p class="fw-semibold mb-0">{{ $client->contact_person ?? '-' }}</p>
        </div>
      </div>

      <div class="col-12">
        <div class="bg-light p-3 rounded">
          <h6 class="text-muted mb-2">ملاحظات</h6>
          <p class="fw-semibold mb-0">{{ $client->notes ?? '-' }}</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded d-flex justify-content-between align-items-center">
          <h6 class="text-muted mb-0">الحالة</h6>
          @if($client->active)
            <span class="badge bg-success py-2 px-3">مفعل</span>
          @else
            <span class="badge bg-danger py-2 px-3">غير مفعل</span>
          @endif
        </div>
      </div>

      <div class="col-md-6">
        <div class="bg-light p-3 rounded d-flex justify-content-between align-items-center">
          <h6 class="text-muted mb-0">تاريخ الإنشاء</h6>
          <p class="fw-semibold mb-0">{{ $client->created_at->format('Y-m-d H:i') }}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="card-footer bg-white border-top-0 text-center">
    <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn btn-primary me-3 px-4">
      <i class="bi bi-pencil-square me-1"></i> تعديل
    </a>
    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary px-4">
      <i class="bi bi-arrow-left-circle me-1"></i> العودة للقائمة
    </a>
  </div>
</div>
@endsection
