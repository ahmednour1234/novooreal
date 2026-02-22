@extends('layouts.admin.app')

@section('title', 'قائمة العملاء')

@section('content')
<!-- داخل layouts/admin/app.blade.php في القسم <head> -->
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

<div class="card shadow-sm border-0">
  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-top">
    <h5 class="mb-0" style="color:white;"><i class="bi bi-people-fill me-2"></i> قائمة العملاء</h5>
    <a href="{{ route('admin.clients.create') }}" class="btn btn-light text-primary fw-bold">
      <i class="bi bi-plus-circle me-1"></i> إضافة عميل جديد
    </a>
  </div>

  <div class="card-body">
    {{-- نموذج البحث --}}
    @include('admin-views.clients.search')

    {{-- جدول العملاء --}}
    <div class="table-responsive mt-3">
      <table class="table table-hover table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>البريد</th>
            <th>الهاتف</th>
            <th>الشركة</th>
            <th>الحالة</th>
            <th>العمليات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($clients as $index => $client)
            <tr>
              <td>{{ $index + $clients->firstItem() }}</td>
              <td class="fw-semibold">{{ $client->name }}</td>
              <td>{{ $client->email ?? '-' }}</td>
              <td>{{ $client->phone ?? '-' }}</td>
              <td>{{ $client->company_name ?? '-' }}</td>
              <td>
                @if($client->active)
                  <span class="badge bg-success">مفعل</span>
                @else
                  <span class="badge bg-danger">غير مفعل</span>
                @endif
              </td>
              <td>
                <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-sm btn-outline-info">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn btn-sm btn-outline-warning">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form action="{{ route('admin.clients.toggleStatus', $client->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn-sm {{ $client->active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                    <i class="bi {{ $client->active ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-muted">لا يوجد عملاء حالياً.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- روابط التصفح --}}
    <div class="mt-4">
      {{ $clients->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
