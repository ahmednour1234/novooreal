
@extends('layouts.admin.app')

@section('title', 'إضافة عقد جديد')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.2.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

@section('content')
<div class="card shadow-sm mb-4">
  <div class="card-header bg-primary text-white rounded-top">
    <h5 class="mb-0" style="color:white;"><i class="bi bi-plus-lg me-2"></i> تعديل عقد </h5>
  </div>
  <div class="card-body p-4">
    <form action="{{ route('admin.contracts.store') }}" method="POST">
      @csrf

      @include('admin-views.contracts.form')

      <div class="mt-4 text-center">
        <button type="submit" class="btn btn-warning px-5">
          <i class="bi bi-save me-1"></i> تحديث
        </button>
        <a href="{{ route('admin.contracts.show', $contract->id) }}" class="btn btn-outline-secondary px-4 ms-2">
          إلغاء
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
 


  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(function () {
      $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'اختر أو ابحث...',
        allowClear: true
      });
    });
  </script>