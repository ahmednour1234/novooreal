@php
  use Illuminate\Support\Facades\DB;

  $p_from = request('p_from');  $p_to = request('p_to');  $p_q = request('p_q');
  $baseQ = DB::table('payment_vouchers')->where('type', 1) // 1 = سند صرف
      ->where(function($w) use ($customer){
          // عادةً سند الصرف: مدين = حساب العميل (صرف له)
          $w->where('debit_account_id', $customer->account_id)
            ->orWhere('credit_account_id', $customer->account_id); // احتياط
      });

  if($p_from){ $baseQ->whereDate('date', '>=', $p_from); }
  if($p_to){   $baseQ->whereDate('date', '<=', $p_to); }
  if($p_q){
    $baseQ->where(function($w) use ($p_q){
      $w->where('id', $p_q)->orWhere('voucher_no', 'like', '%'.$p_q.'%')->orWhere('notes','like','%'.$p_q.'%');
    });
  }

  $payments = (clone $baseQ)->orderBy('date','desc')->orderBy('id','desc')->paginate(20)->appends(request()->query());
  $totalAmount = (clone $baseQ)->sum('amount');
@endphp

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="mb-0">{{ \App\CPU\translate('سندات صرف') }}</h3>
    <div class="toolbar">
      <button class="btn btn-outline-secondary" onclick="printTableById('tbl-payments', '{{ \App\CPU\translate('سندات صرف') }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button class="btn btn-outline-primary" onclick="exportTableToCSV('tbl-payments','payments-{{ $customer->id }}.csv')">
        <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="get" class="mb-3">
      <input type="hidden" name="active_tab" value="tab-payments">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="p_from" class="form-control" value="{{ $p_from }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="p_to" class="form-control" value="{{ $p_to }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('بحث') }}</label>
          <input type="text" name="p_q" class="form-control" placeholder="{{ \App\CPU\translate('رقم/ملاحظة') }}" value="{{ $p_q }}">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100" type="submit">{{ \App\CPU\translate('بحث') }}</button>
          <button class="btn btn-light w-100" type="button" onclick="clearTabFilters('tab-payments')">{{ \App\CPU\translate('إلغاء البحث') }}</button>
        </div>
      </div>
    </form>

    <div class="mb-2"><strong>{{ \App\CPU\translate('الإجمالي') }}:</strong> {{ number_format($totalAmount,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</div>

    <div class="table-responsive">
      <table class="table table-bordered" id="tbl-payments">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الرقم') }}</th>
            <th>{{ \App\CPU\translate('التاريخ') }}</th>
            <th>{{ \App\CPU\translate('الوصف') }}</th>
            <th>{{ \App\CPU\translate('المبلغ') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $row)
            <tr>
              <td>{{ $loop->iteration + ($payments->currentPage()-1)*$payments->perPage() }}</td>
              <td>{{ $row->voucher_no ?? $row->id }}</td>
              <td>{{ \Carbon\Carbon::parse($row->date ?? $row->created_at)->format('Y-m-d') }}</td>
              <td>{{ $row->notes ?? '-' }}</td>
              <td>{{ number_format($row->amount,2) }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pt-2">
      {!! $payments->links() !!}
    </div>
  </div>
</div>
