@extends('layouts.admin.app')

@section('content')
<div class="content container-fluid">
    {{-- Breadcrumb --}}
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('الرئيسية') }}</a>
                </li>
                                <li class="breadcrumb-item active">{{ \App\CPU\translate('مسودات عرض سعر') }}</li>

            </ol>
        </nav>
    </div>
    

    <table class="table ">
        <thead >
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
                    <a href="{{ route('admin.quotations.show',$q->id) }}" class="btn btn-sm btn-white">عرض</a>
                    @if($q->status!=2)
                    <a href="{{ route('admin.quotations.edit',$q->id) }}" class="btn btn-sm btn-primary">تعديل</a>
                @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $drafts->links() }}
</div>
@endsection
