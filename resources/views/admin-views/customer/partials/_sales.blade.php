@php
  use Illuminate\Support\Facades\DB;

  $s_from = request('s_from'); $s_to = request('s_to'); $s_q = request('s_q');
  $baseQ = DB::table('orders')->where('user_id', $customer->id)->where('type', 4); // 4 = فاتورة مبيعات

  if($s_from){ $baseQ->whereDate('created_at','>=',$s_from); }
  if($s_to){   $baseQ->whereDate('created_at','<=',$s_to); }
  if($s_q){
    $baseQ->where(function($w) use ($s_q){
      $w->where('id', $s_q)->orWhere('order_number','like','%'.$s_q.'%');
    });
  }

  $orders = (clone $baseQ)->orderBy('created_at','desc')->orderBy('id','desc')->paginate(20)->appends(request()->query());
  $sumAmount = (clone $baseQ)->sum('order_amount');
@endphp

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="mb-0">{{ \App\CPU\translate('مبيعات') }}</h3>
    <div class="toolbar">
      <button class="btn btn-outline-secondary" onclick="printTableById('tbl-sales', '{{ \App\CPU\translate('مبيعات') }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button class="btn btn-outline-primary" onclick="exportTableToCSV('tbl-sales','sales-{{ $customer->id }}.csv')">
        <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="get" class="mb-3">
      <input type="hidden" name="active_tab" value="tab-sales">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="s_from" class="form-control" value="{{ $s_from }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="s_to" class="form-control" value="{{ $s_to }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('بحث') }}</label>
          <input type="text" name="s_q" class="form-control" placeholder="{{ \App\CPU\translate('رقم الفاتورة') }}" value="{{ $s_q }}">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100" type="submit">{{ \App\CPU\translate('بحث') }}</button>
          <button class="btn btn-light w-100" type="button" onclick="clearTabFilters('tab-sales')">{{ \App\CPU\translate('إلغاء البحث') }}</button>
        </div>
      </div>
    </form>

    <div class="mb-2"><strong>{{ \App\CPU\translate('إجمالي المبيعات') }}:</strong> {{ number_format($sumAmount,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</div>

    <div class="table-responsive">
      <table class="table table-bordered" id="tbl-sales">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الفاتورة رقم') }}</th>
            <th>{{ \App\CPU\translate('الإجمالي') }}</th>
            <th>{{ \App\CPU\translate('التاريخ') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $row)
            <tr>
              <td>{{ $loop->iteration + ($orders->currentPage()-1)*$orders->perPage() }}</td>
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
      {!! $orders->links() !!}
    </div>
  </div>
</div>
