
<div class="container my-4">
    <h1 class="mb-4 text-center">فاتورة دفعات المخزون للمنتج: {{ $productId }}</h1>
    
    @if(isset($branchId) && $branchId)
        @php
            $branch = \App\Models\Branch::find($branchId);
        @endphp
        <h3 class="text-center">الفرع: {{ $branch ? $branch->name : 'غير محدد' }}</h3>
    @else
        <h3 class="text-center">الفرع: كل الفروع</h3>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>رقم الدفعة</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batches as $batch)
                <tr>
                    <td>{{ $batch->id }}</td>
                    <td>{{ number_format($batch->quantity, 2) }}</td>
                    <td>{{ number_format($batch->price, 2) }}</td>
                    <td>{{ number_format($batch->quantity * $batch->price, 2) }}</td>
                </tr>
            @endforeach
            <tr class="font-weight-bold">
                <td colspan="3" class="text-right">الاجمالي</td>
                <td>{{ number_format($totalCost, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
