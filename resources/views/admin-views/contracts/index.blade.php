@extends('layouts.admin.app')

@section('title', 'قائمة العقود')
    <!-- باقي المكتبات -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@section('content')
<div class="card shadow mb-4 border-0 rounded-3">
  <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center rounded-top">
    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> قائمة العقود</h5>
    <a href="{{ route('admin.contracts.create') }}" class="btn btn-light btn-sm text-primary fw-semibold shadow-sm">
      <i class="bi bi-plus-circle me-1"></i> عقد جديد
    </a>
  </div>

  <div class="card-body p-4">
    {{-- بحث وفلترة --}}
    @include('admin-views.contracts.search')

    {{-- جدول العقود --}}
    <div class="table-responsive mt-4">
      <table class="table table-hover table-bordered align-middle text-center shadow-sm">
        <thead class="table-light text-secondary fw-bold">
          <tr>
            <th>#</th>
            <th>رقم العقد</th>
            <th>العميل</th>
            <th>القيمة</th>
            <th>تاريخ البداية</th>
            <th>تاريخ النهاية</th>
            <th>الحالة</th>
            <th>عمليات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($contracts as $i => $contract)
            <tr>
              <td>{{ $i + $contracts->firstItem() }}</td>
              <td class="fw-bold text-primary">{{ $contract->contract_number }}</td>
              <td>{{ $contract->client->name }}</td>
              <td class="text-success">{{ number_format($contract->total_value, 2) }}</td>
              <td>{{ $contract->start_date }}</td>
              <td>{{ optional($contract->end_date)->format('Y-m-d') ?? '-' }}</td>
              <td>
                @if($contract->status==='active')
                  <span class="badge bg-success px-3 py-2">نشط</span>
                @elseif($contract->status==='completed')
                  <span class="badge bg-info px-3 py-2">مكتمل</span>
                @elseif($contract->status==='canceled')
                  <span class="badge bg-danger px-3 py-2">ملغى</span>
                @else
                  <span class="badge bg-secondary px-3 py-2">مسودة</span>
                @endif
              </td>
              <td>
                <a href="{{ route('admin.contracts.show',$contract->id) }}" class="btn btn-sm btn-outline-primary me-1" title="عرض"><i class="bi bi-eye"></i></a>
                <a href="{{ route('admin.contracts.edit',$contract->id) }}" class="btn btn-sm btn-outline-warning me-1" title="تعديل"><i class="bi bi-pencil-square"></i></a>
                <form action="{{ route('admin.contracts.toggleStatus',$contract->id) }}" method="POST" class="d-inline-block">
                  @csrf @method('PATCH')
                  <button class="btn btn-sm {{ $contract->status==='active'?'btn-outline-danger':'btn-outline-success' }}" title="{{ $contract->status==='active'?'تعطيل':'تفعيل' }}">
                    <i class="bi {{ $contract->status==='active'?'bi-x-circle':'bi-check-circle' }}"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-muted">لا توجد عقود مطابقة.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- روابط التصفح --}}
    <div class="mt-3">
      {{ $contracts->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
