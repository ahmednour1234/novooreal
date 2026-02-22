@extends('layouts.admin.app')
@section('title','Notification List')
@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css"/>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
                <div class="mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">
                        {{ \App\CPU\translate('الأشعارات') }}
                    </li>
                </ol>
            </nav>
        </div>


        <!-- Notifications Table -->
        <div class="table-responsive mb-3">
            <table class="table ">
                <thead class="">
                    <tr>
                        <th>{{ \App\CPU\translate('#') }}</th>
                        <th>{{ \App\CPU\translate('النوع') }}</th>
                        <th>{{ \App\CPU\translate('اسم المندوب') }}</th>
                        <th>{{ \App\CPU\translate('تاريخ الإنشاء') }}</th>
                        <th>{{ \App\CPU\translate('الإجراءات') }}</th>
                    </tr>
                </thead>
              <tbody>
    @php $counter = ($paginatedNotifications->currentPage() - 1) * $paginatedNotifications->perPage() + 1; @endphp
    @foreach($paginatedNotifications as $notification)
        <tr>
            <td>{{ $counter++ }}</td>
<td class="
    @if($notification->notification_type == 'order') 
        badge bg-success text-white 
    @elseif($notification->notification_type == 'refundOrder') 
        badge bg-danger text-white 
    @elseif($notification->notification_type == 'installment') 
        badge bg-warning text-dark 
    @elseif($notification->notification_type == 'reserveProduct') 
        badge bg-primary text-white 
    @elseif($notification->notification_type == 'reReserveProduct') 
        badge bg-info text-white 
    @elseif($notification->notification_type == 'transaction') 
        badge bg-secondary text-white 
    @else 
        badge bg-light text-dark
    @endif
">
    @switch($notification->notification_type)
        @case('order')
            {{ \App\CPU\translate('مبيعات') }}
            @break
        @case('refundOrder')
            {{ \App\CPU\translate('مرتجع') }}
            @break
        @case('installment')
            {{ \App\CPU\translate('تحصيل') }}
            @break
        @case('reserveProduct')
            {{ \App\CPU\translate('أمر توريد بضاعة') }}
            @break
        @case('reReserveProduct')
            {{ \App\CPU\translate('أمر رد بضاعة') }}
            @break
        @case('transaction')
            {{ \App\CPU\translate('تحويل مندوب') }}
            @break
        @default
            {{ \App\CPU\translate('غير محدد') }}
    @endswitch
</td>

<td>
    @if(isset($notification->seller)) 
        {{ $notification->seller->f_name }} 

    @else
            {{ $notification->sellers->f_name ?? '-' }} 
    @endif
</td>
            <td>{{ $notification->created_at->format('Y-m-d H:i:s') }}</td>
            <td>
                <a href="{{ route('admin.admin.notifications.show', ['id' => $notification->id, 'type' => $notification->notification_type]) }}" class="btn btn-primary btn-sm">
                    {{ \App\CPU\translate('عرض') }}
                </a>
            </td>
        </tr>
    @endforeach
</tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $paginatedNotifications->links() }}
        </div>
    </div>
@endsection