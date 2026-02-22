@php
  use App\Models\PaymentVoucher;

  $q = PaymentVoucher::query()
      ->where(function($qq) use($accId){
        $qq->where('debit_account_id',  $accId)
           ->orWhere('credit_account_id', $accId);
      });

  if($start_date){ $q->whereDate('date','>=',$start_date); }
  if($end_date){   $q->whereDate('date','<=',$end_date); }
  if($search){
    $q->where(function($qq) use($search){
      $qq->where('voucher_number','like',"%$search%")
         ->orWhere('payee_name','like',"%$search%")
         ->orWhere('description','like',"%$search%");
    });
  }

  $rows = $q->orderBy('date','desc')->orderBy('id','desc')->paginate(20)->appends(request()->all());

  $pageDebit=0; $pageCredit=0;
  foreach($rows as $v){
    // اعتبار: لو debit_account_id = حساب المورد => صرف (نحن ندفع له)
    if($v->debit_account_id == $accId) $pageDebit += (float)$v->amount;
    if($v->credit_account_id == $accId) $pageCredit += (float)$v->amount;
  }

  $tableId = 'tbl-vouchers';
@endphp

<script>window._activeTableId = '{{ $tableId }}';</script>

<div class="table-responsive">
  <table id="{{ $tableId }}" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>{{ \App\CPU\translate('رقم السند') }}</th>
        <th>{{ \App\CPU\translate('التاريخ') }}</th>
        <th>{{ \App\CPU\translate('الجهة') }}</th>
        <th class="text-center">{{ \App\CPU\translate('نوع السند') }}</th>
        <th class="text-center">{{ \App\CPU\translate('المبلغ') }}</th>
        <th>{{ \App\CPU\translate('طريقة الدفع') }}</th>
        <th>{{ \App\CPU\translate('الوصف') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $i=>$v)
        @php
          $typeLabel = $v->debit_account_id==$accId ? \App\CPU\translate('سند صرف') : ($v->credit_account_id==$accId ? \App\CPU\translate('سند قبض') : '—');
        @endphp
        <tr>
          <td>{{ $rows->firstItem()+$i }}</td>
          <td>{{ $v->voucher_number }}</td>
          <td>{{ $v->date }}</td>
          <td>{{ $v->payee_name ?: '—' }}</td>
          <td class="text-center">{{ $typeLabel }}</td>
          <td class="text-center">{{ number_format($v->amount,2,'.',',') }} {{ $currency }}</td>
          <td>{{ $v->payment_method ?: '—' }}</td>
          <td>{{ $v->description ?: '—' }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4" class="text-right">{{ \App\CPU\translate('إجمالي الصفحة') }}</th>
        <th class="text-center">{{ \App\CPU\translate('قبض') }}: {{ number_format($pageCredit,2,'.',',') }} {{ $currency }}</th>
        <th class="text-center">{{ \App\CPU\translate('صرف') }}: {{ number_format($pageDebit,2,'.',',') }} {{ $currency }}</th>
        <th colspan="2">—</th>
      </tr>
    </tfoot>
  </table>
</div>

<div class="table-footer px-3 py-2 d-flex justify-content-between align-items-center">
  <div class="small text-muted">{{ \App\CPU\translate('عدد النتائج') }}: {{ $rows->count() }}</div>
  <div>{!! $rows->links() !!}</div>
</div>
