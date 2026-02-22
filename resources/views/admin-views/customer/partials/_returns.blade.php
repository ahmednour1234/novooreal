@php
  use Illuminate\Support\Facades\DB;

  $rtn_from = request('rtn_from'); $rtn_to = request('rtn_to'); $rtn_q = request('rtn_q');
  $baseQ = DB::table('orders')->where('user_id', $customer->id)->where('type', 7); // 7 = مردود مبيعات

  if($rtn_from){ $baseQ->whereDate('created_at','>=',$rtn_from); }
  if($rtn_to){   $baseQ->whereDate('created_at','<=',$rtn_to); }
  if($rtn_q){
    $baseQ->where(function($w) use ($rtn_q){
      $w->where('id', $rtn_q)->orWhere('order_number','like','%'.$rtn_q+'%');
    });
  }

  $returns = (clone $baseQ)->orderBy('created_at','desc')->orderBy('id','desc')->paginate(20)->appends(request()->query());
  $sumAmount = (clone $baseQ)->sum('order_amount');
@endphp

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="mb-0">{{ \App\CPU\translate('مرتجعات') }}</h3>
    <div class="toolbar">
      <button class="btn btn-outline-secondary" onclick="printTableById('tbl-returns', '{{ \App\CPU\translate('مرتجعات') }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button class="btn btn-outline-primary" onclick="exportTableToCSV('tbl-returns','returns-{{ $customer->id }}.csv')">
        <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="get" class="mb-3">
      <input type="hidden" name="active_tab" value="tab-returns">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="rtn_from" class="form-control" value="{{ $rtn_from }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="rtn_to" class="form-control" value="{{ $rtn_to }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('بحث') }}</label>
          <input type="text" name="rtn_q" class="form-control" placeholder="{{ \App\CPU\translate('رقم الفاتورة') }}" value="{{ $rtn_q }}">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100" type="submit">{{ \App\CPU\translate('بحث') }}</button>
          <button class="btn btn-light w-100" type="button" onclick="clearTabFilters('tab-returns')">{{ \App\CPU\translate('إلغاء البحث') }}</button>
        </div>
      </div>
    </form>

    <div class="mb-2"><strong>{{ \App\CPU\translate('إجمالي المرتجعات') }}:</strong> {{ number_format($sumAmount,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</div>

    <div class="table-responsive">
      <table class="table table-bordered" id="tbl-returns">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الفاتورة رقم') }}</th>
            <th>{{ \App\CPU\translate('الإجمالي') }}</th>
            <th>{{ \App\CPU\translate('التاريخ') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($returns as $row)
            <tr>
              <td>{{ $loop->iteration + ($returns->currentPage()-1)*$returns->perPage() }}</td>
              <td>{{ $row->id }}</td>
              <td>{{ number_format($row->order_amount,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</td>
              <td>{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pt-2">
      {!! $returns->links() !!}
    </div>
  </div>
</div>
