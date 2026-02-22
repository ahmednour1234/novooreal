@php
  use Illuminate\Support\Facades\DB;
  use Carbon\Carbon;

  $q_from = request('q_from'); 
  $q_to   = request('q_to'); 
  $q_q    = request('q_q');

  // ربط العميل حسب user_id (كما في كودك)
  $baseQ = DB::table('quotations')->where('user_id', $customer->id);

  if ($q_from) { $baseQ->whereDate('created_at', '>=', $q_from); }
  if ($q_to)   { $baseQ->whereDate('created_at', '<=', $q_to);   }
  if ($q_q) {
    $baseQ->where(function($w) use ($q_q){
      $w->where('id', $q_q)
        ->orWhere('quotation_no', 'like', '%'.$q_q.'%')
        ->orWhere('title', 'like', '%'.$q_q.'%');
    });
  }

  $quotes    = (clone $baseQ)->orderBy('created_at','desc')->orderBy('id','desc')->paginate(20)->appends(request()->query());
  $sumTotal  = (clone $baseQ)->sum('order_amount');

  // خريطة الحالة
  $statusText = [
    1 => 'مسودة',
    2 => 'منفذة',
  ];
  $statusBadge = [
    1 => 'badge-soft-secondary',
    2 => 'badge-soft-success',
  ];
@endphp

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="mb-0">{{ \App\CPU\translate('عروض الأسعار') }}</h3>
    <div class="toolbar">
      <button class="btn btn-outline-secondary" onclick="printTableById('tbl-quotes', '{{ \App\CPU\translate('عروض الأسعار') }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button class="btn btn-outline-primary" onclick="exportTableToCSV('tbl-quotes','quotations-{{ $customer->id }}.csv')">
        <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="get" class="mb-3">
      <input type="hidden" name="active_tab" value="tab-quotes">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('من تاريخ') }}</label>
          <input type="date" name="q_from" class="form-control" value="{{ $q_from }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <input type="date" name="q_to" class="form-control" value="{{ $q_to }}">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ \App\CPU\translate('بحث') }}</label>
          <input type="text" name="q_q" class="form-control" placeholder="{{ \App\CPU\translate('رقم/عنوان') }}" value="{{ $q_q }}">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100" type="submit">{{ \App\CPU\translate('بحث') }}</button>
          <button class="btn btn-light w-100" type="button" onclick="clearTabFilters('tab-quotes')">{{ \App\CPU\translate('إلغاء البحث') }}</button>
        </div>
      </div>
    </form>

    <div class="mb-2">
      <strong>{{ \App\CPU\translate('الإجمالي') }}:</strong>
      {{ number_format($sumTotal,2) }} {{ \App\CPU\Helpers::currency_symbol() }}
    </div>

    <div class="table-responsive">
      <table class="table table-bordered" id="tbl-quotes">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الرقم') }}</th>
            <th>{{ \App\CPU\translate('التاريخ') }}</th>
            <th>{{ \App\CPU\translate('الحالة') }}</th>
            <th>{{ \App\CPU\translate('الإجمالي') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($quotes as $row)
            @php
              $sVal   = (int)($row->status ?? 0);
              $sText  = $statusText[$sVal]  ?? 'غير معروف';
              $sClass = $statusBadge[$sVal] ?? 'badge-soft-dark';
            @endphp
            <tr>
              <td>{{ $loop->iteration + ($quotes->currentPage()-1)*$quotes->perPage() }}</td>
              <td>{{ $row->quotation_no ?? $row->id }}</td>
              <td>{{ Carbon::parse($row->created_at)->format('Y-m-d') }}</td>
              <td>
                <span class="badge {{ $sClass }}">{{ $sText }}</span>
              </td>
              <td>{{ number_format($row->order_amount ?? 0,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pt-2">
      {!! $quotes->links() !!}
    </div>
  </div>
</div>
