@extends('layouts.admin.app')

@section('title', 'قائمة قوائم المواد')

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h3 mb-0">قائمة قوائم المواد</h1>
        <a href="{{ route('admin.boms.create') }}" class="btn btn-primary">إضافة قائمة مواد</a>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>المنتج</th>
                <th>الإصدار</th>
                <th>الوصف</th>
                <th>تاريخ الإنشاء</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($boms as $bom)
            <tr>
                <td>{{ $loop->iteration + ($boms->currentPage()-1)*$boms->perPage() }}</td>
                <td>{{ $bom->product->name }}</td>
                <td>{{ $bom->version }}</td>
                <td>{{ Str::limit($bom->description, 50) }}</td>
                <td>{{ $bom->created_at->format('Y-m-d') }}</td>
                <td>
                    <a href="{{ route('admin.boms.edit', $bom->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                    <!--<form action="{{ route('admin.boms.destroy', $bom->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد؟');">-->
                    <!--    @csrf @method('DELETE')-->
                    <!--    <button class="btn btn-sm btn-danger">حذف</button>-->
                    <!--</form>-->
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-3">
        {{ $boms->links() }}
    </div>
</div>
@endsection

