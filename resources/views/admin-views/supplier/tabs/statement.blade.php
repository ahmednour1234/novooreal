@php
  use App\Models\Transection;

  $q = Transection::query()->where('account_id', $accId);

  if($start_date){ $q->whereDate('date', '>=', $start_date); }
  if($end_date){   $q->whereDate('date', '<=', $end_date); }
  if($search){
    $q->where(function($qq) use($search){
      $qq->where('description','like',"%$search%")
         ->orWhere('order_id','like',"%$search%");
    });
  }

  $rows = $q->orderBy('date','asc')->orderBy('id','asc')->paginate(20)->appends(request()->all());

  // إجمالي الصفحة + رصيد تراكمي
  $pageDebit = 0; $pageCredit = 0;
  foreach($rows as $r){ $pageDebit += (float)$r->debit; $pageCredit += (float)$r->credit; }
  $running = 0; // رصيد تراكمي داخل الصفحة
  $tableId = 'tbl-statement';
@endphp

<script>window._activeTableId = '{{ $tableId }}';</script>

<div class="table-responsive">
  <table id="{{ $tableId }}" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>{{ \App\CPU\translate('التاريخ') }}</th>
        <th>{{ \App\CPU\translate('الوصف') }}</th>
        <th class="text-center">{{ \App\CPU\translate('مدين') }}</th>
        <th class="text-center">{{ \App\CPU\translate('دائن') }}</th>
        <th class="text-center">{{ \App\CPU\translate('الرصيد التراكمي') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $i=>$r)
        @php
          $running += ((float)$r->credit - (float)$r->debit);
        @endphp
        <tr>
          <td>{{ $rows->firstItem()+$i }}</td>
          <td>{{ $r->date }}</td>
          <td>{{ $r->description ?? '—' }}</td>
          <td class="text-center">{{ number_format($r->debit,2,'.',',') }} {{ $currency }}</td>
          <td class="text-center">{{ number_format($r->credit,2,'.',',') }} {{ $currency }}</td>
          <td class="text-center">{{ number_format($running,2,'.',',') }} {{ $currency }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3" class="text-right">{{ \App\CPU\translate('إجمالي الصفحة') }}</th>
        <th class="text-center">{{ number_format($pageDebit,2,'.',',') }} {{ $currency }}</th>
        <th class="text-center">{{ number_format($pageCredit,2,'.',',') }} {{ $currency }}</th>
        <th class="text-center">{{ number_format($running,2,'.',',') }} {{ $currency }}</th>
      </tr>
    </tfoot>
  </table>
</div>

<div class="table-footer px-3 py-2 d-flex justify-content-between align-items-center">
  <div class="small text-muted">{{ \App\CPU\translate('عدد النتائج') }}: {{ $rows->count() }}</div>
  <div>{!! $rows->links() !!}</div>
</div>
