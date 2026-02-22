@extends('layouts.admin.app')

@section('content')
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
                <a href="{{ route('admin.costcenter.add') }}" class="text-primary">
مسودات فواتير البيع                </a>
            </li>
            
        </ol>
    </nav>
</div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>العميل</th>
                <th>التاريخ</th>
                <th>الإجمالي النهائي</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
        @foreach($drafts as $q)
            <tr>
                <td>{{ $q->id }}</td>
                <td>{{ $q->customer->name }}</td>
                <td>{{ \Carbon\Carbon::parse($q->date)->format('Y-m-d') }}</td>
                <td>{{ number_format($q->order_amount,2) }}</td>
                <td>
                    <a href="{{ route('admin.sells.show',$q->id) }}" class="btn btn-sm btn-white">عرض</a>
                    <!--<a href="{{ route('admin.sells.edit',$q->id) }}" class="btn btn-sm btn-primary">تعديل</a>-->
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $drafts->links() }}
</div>
@endsection
