@php
  use Illuminate\Support\Facades\DB;

  $r_from = request('r_from');  $r_to = request('r_to');  $r_q = request('r_q');
  $baseQ = DB::table('payment_vouchers')->where('type', 2) // 2 = سند قبض
      ->where(function($w) use ($customer){
          $w->where('credit_account_id', $customer->account_id)
            ->orWhere('debit_account_id', $customer->account_id); // احتياط
      });

  if($r_from){ $baseQ->whereDate('date', '>=', $r_from); }
  if($r_to){   $baseQ->whereDate('date', '<=', $r_to); }
  if($r_q){
    $baseQ->where(function($w) use ($r_q){
      $w->where('id', $r_q)->orWhere('voucher_no', 'like', '%'.$r_q.'%')->orWhere('notes','like','%'.$r_q.'%');
    });
  }

  $receipts = (clone $baseQ)->orderBy('date','desc')->orderBy('id','desc')->paginate(20)->appends(request()->query());
  $totalAmount = (clone $baseQ)->sum('amount');
@endphp

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="mb-0">{{ \App\CPU\translate('سندات قبض') }}</h3>
    <div class="toolbar">
      <button class="btn btn-outline-secondary" onclick="printTableById('tbl-receipts', '{{ \App\CPU\translate('سندات قبض') }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button class="btn btn-outline-primary" onclick="exportTableToCSV('tbl-receipts','receipts-{{ $customer->id }}.csv')">
        <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="get" class="mb-3">
      <input type="hidden" name="active_tab" value="tab-receipts">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="r_from" class="form-control" value="{{ $r_from }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="r_to" class="form-control" value="{{ $r_to }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('بحث') }}</label>
          <input type="text" name="r_q" class="form-control" placeholder="{{ \App\CPU\translate('رقم/ملاحظة') }}" value="{{ $r_q }}">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100" type="submit">{{ \App\CPU\translate('بحث') }}</button>
          <button class="btn btn-light w-100" type="button" onclick="clearTabFilters('tab-receipts')">{{ \App\CPU\translate('إلغاء البحث') }}</button>
        </div>
      </div>
    </form>

    <div class="mb-2"><strong>{{ \App\CPU\translate('الإجمالي') }}:</strong> {{ number_format($totalAmount,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</div>

    <div class="table-responsive">
      <table class="table table-bordered" id="tbl-receipts">
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
          @forelse($receipts as $row)
            <tr>
              <td>{{ $loop->iteration + ($receipts->currentPage()-1)*$receipts->perPage() }}</td>
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
      {!! $receipts->links() !!}
    </div>
  </div>
</div>
