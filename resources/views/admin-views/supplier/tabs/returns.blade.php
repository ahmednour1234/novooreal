@php
  use App\Models\Order;

  $q = Order::query()
      ->where('supplier_id', $supplier->id)
      ->where('type', 24);

  if($start_date){ $q->whereDate('date','>=',$start_date); }
  if($end_date){   $q->whereDate('date','<=',$end_date); }
  if($search){
    $q->where(function($qq) use($search){
      $qq->where('id','like',"%$search%")
         ->orWhere('coupon_code','like',"%$search%");
    });
  }

  $rows = $q->orderBy('date','desc')->orderBy('id','desc')->paginate(20)->appends(request()->all());
  $sumAmount = (float) $q->clone()->sum('order_amount');
  $tableId = 'tbl-returns';
@endphp

<script>window._activeTableId = '{{ $tableId }}';</script>

<div class="table-responsive">
  <table id="{{ $tableId }}" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th class="text-center">{{ \App\CPU\translate('رقم الفاتورة') }}</th>
        <th>{{ \App\CPU\translate('الإجمالي') }}</th>
        <th>{{ \App\CPU\translate('التاريخ') }}</th>
        <th class="no-print">{{ \App\CPU\translate('إجراء') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $i=>$o)
        <tr>
          <td>{{ $rows->firstItem()+$i }}</td>
          <td class="text-center">{{ $o->id }}</td>
          <td>{{ number_format($o->order_amount,2,'.',',') }} {{ $currency }}</td>
          <td>{{ $o->date ?: $o->created_at }}</td>
          <td class="no-print">
            <a href="javascript:;" class="btn btn-sm btn-white" onclick="printTable('{{ $tableId }}','{{ \App\CPU\translate('فواتير مرتجع مشتريات') }}')">
              <i class="tio-print"></i>
            </a>
          </td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="2" class="text-right">{{ \App\CPU\translate('إجمالي المبالغ (حسب الفلتر)') }}</th>
        <th>{{ number_format($sumAmount,2,'.',',') }} {{ $currency }}</th>
        <th colspan="2">—</th>
      </tr>
    </tfoot>
  </table>
</div>

<div class="table-footer px-3 py-2 d-flex justify-content-between align-items-center">
  <div class="small text-muted">{{ \App\CPU\translate('عدد النتائج') }}: {{ $rows->count() }}</div>
  <div>{!! $rows->links() !!}</div>
</div>
