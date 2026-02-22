@php
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\Schema;

  $st_from = request('st_from'); $st_to = request('st_to'); $st_q = request('st_q');

  $rows = collect(); $openingBalance = 0; $hasTable = Schema::hasTable('transections');

  if($hasTable){
    $Q = DB::table('transections')->where('account_id', $customer->account_id);

    // رصيد افتتاحي قبل تاريخ from
    if($st_from){
      $opDebit  = (clone $Q)->whereDate('date','<', $st_from)->sum('debit');
      $opCredit = (clone $Q)->whereDate('date','<', $st_from)->sum('credit');
      $openingBalance = $opDebit - $opCredit;
    }

    if($st_from){ $Q->whereDate('date','>=',$st_from); }
    if($st_to){   $Q->whereDate('date','<=',$st_to); }
    if($st_q){
      $Q->where(function($w) use ($st_q){
        $w->where('id','like','%'.$st_q.'%')
          ->orWhere('description','like','%'.$st_q.'%');
      });
    }

    $rows = $Q->orderBy('date')->orderBy('id')->paginate(30)->appends(request()->query());
  }
@endphp

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="mb-0">{{ \App\CPU\translate('كشف حساب') }}</h3>
    <div class="toolbar">
      <button class="btn btn-outline-secondary" onclick="printTableById('tbl-statement', '{{ \App\CPU\translate('كشف حساب') }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button class="btn btn-outline-primary" onclick="exportTableToCSV('tbl-statement','statement-{{ $customer->id }}.csv')">
        <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="get" class="mb-3">
      <input type="hidden" name="active_tab" value="tab-statement">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="st_from" class="form-control" value="{{ $st_from }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="st_to" class="form-control" value="{{ $st_to }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('بحث') }}</label>
          <input type="text" name="st_q" class="form-control" placeholder="{{ \App\CPU\translate('مرجع/وصف') }}" value="{{ $st_q }}">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100" type="submit">{{ \App\CPU\translate('بحث') }}</button>
          <button class="btn btn-light w-100" type="button" onclick="clearTabFilters('tab-statement')">{{ \App\CPU\translate('إلغاء البحث') }}</button>
        </div>
      </div>
    </form>

    @if(!$hasTable)
      <div class="alert alert-warning">
        {{ \App\CPU\translate('جدول كشف الحساب غير متوفر') }} (account_transactions)
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-bordered" id="tbl-statement">
          <thead>
            <tr>
              <th>#</th>
              <th>{{ \App\CPU\translate('التاريخ') }}</th>
              <th>{{ \App\CPU\translate('الوصف') }}</th>
              <th>{{ \App\CPU\translate('مرجع') }}</th>
              <th>{{ \App\CPU\translate('مدين') }}</th>
              <th>{{ \App\CPU\translate('دائن') }}</th>
              <th>{{ \App\CPU\translate('الرصيد') }}</th>
            </tr>
          </thead>
          <tbody>
            @php $running = $openingBalance; @endphp
            <tr>
              <td>—</td>
              <td>{{ $st_from ?: '—' }}</td>
              <td>{{ \App\CPU\translate('رصيد افتتاحي') }}</td>
              <td>—</td>
              <td>0.00</td>
              <td>0.00</td>
              <td>{{ number_format($running,2) }}</td>
            </tr>
            @forelse($rows as $row)
              @php
                $running += ($row->debit - $row->credit);
              @endphp
              <tr>
                <td>{{ $loop->iteration + ($rows->currentPage()-1)*$rows->perPage() }}</td>
                <td>{{ \Carbon\Carbon::parse($row->date)->format('Y-m-d') }}</td>
                <td>{{ $row->description }}</td>
                <td>{{ $row->id }}</td>
                <td>{{ number_format($row->debit,2) }}</td>
                <td>{{ number_format($row->credit,2) }}</td>
                <td>{{ number_format($running,2) }}</td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="pt-2">
        {!! $rows->links() !!}
      </div>
    @endif
  </div>
</div>
