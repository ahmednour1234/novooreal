@extends('layouts.admin.app')

@section('title', 'إضافة عميل جديد')

@section('content')
<div class="card shadow-sm border-0">
  <div class="card-header bg-primary text-white rounded-top">
    <h5 class="mb-0 text-white"><i class="bi bi-person-plus-fill me-2"></i> إضافة عميل جديد</h5>
  </div>
  <div class="card-body pt-4 px-5">
    <form action="{{ route('admin.clients.store') }}" method="POST">
      @csrf

      {{-- تضمين النموذج المشترك --}}
      @include('admin-views.clients.form')

      {{-- الأزرار في منتصف الصفحة --}}
      <div class="d-flex justify-content-center mt-4">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary me-3 px-4">
          إلغاء
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-save me-1"></i> حفظ
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
