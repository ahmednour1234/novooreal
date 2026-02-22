@extends('layouts.admin.app')

@section('title', 'تعديل عميل')

@section('content')
<div class="card shadow-sm border-0">
  <div class="card-header bg-primary text-dark rounded-top">
    <h5 class="mb-0" style="color:white;"><i class="bi bi-pencil-square me-2"></i> تعديل بيانات العميل</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.clients.update', $client->id) }}" method="POST">
      @csrf
      @method('PUT')

      {{-- تضمين النموذج المشترك --}}
      @include('admin-views.clients.form')

      {{-- الأزرار --}}
      <div class="d-flex justify-content-center mt-4">
        <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-outline-secondary me-2">إلغاء</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i> تحديث
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
