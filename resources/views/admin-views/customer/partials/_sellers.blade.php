@php
  use Illuminate\Support\Facades\DB;

  // pivot: seller_customers (seller_id, customer_id)
  $sl_q = request('sl_q');
  $baseQ = DB::table('seller_customers as sc')
    ->join('admins as s','s.id','=','sc.seller_id')
    ->where('sc.customer_id', $customer->id)
    ->select('s.id','s.email','s.phone','s.f_name','sc.created_at');

  if($sl_q){
    $baseQ->where(function($w) use ($sl_q){
      $w->where('s.f_name','like','%'.$sl_q.'%')
        ->orWhere('s.phone','like','%'.$sl_q.'%')
        ->orWhere('s.f_name','like','%'.$sl_q.'%');
    });
  }

  $sellers = (clone $baseQ)->orderBy('s.f_name')->paginate(20)->appends(request()->query());
@endphp

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="mb-0">{{ \App\CPU\translate('المناديب المرتبطين') }}</h3>
    <div class="toolbar">
      <button class="btn btn-outline-secondary" onclick="printTableById('tbl-sellers', '{{ \App\CPU\translate('المناديب المرتبطين') }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
      <button class="btn btn-outline-primary" onclick="exportTableToCSV('tbl-sellers','sellers-{{ $customer->id }}.csv')">
        <i class="tio-download"></i> {{ \App\CPU\translate('Excel') }}
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="get" class="mb-3">
      <input type="hidden" name="active_tab" value="tab-sellers">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-6">
          <label class="form-label">{{ \App\CPU\translate('بحث بالاسم/الهاتف/البريد') }}</label>
          <input type="text" name="sl_q" class="form-control" value="{{ $sl_q }}">
        </div>
        <div class="col-12 col-md-6 d-flex gap-2">
          <button class="btn btn-primary w-100" type="submit">{{ \App\CPU\translate('بحث') }}</button>
          <button class="btn btn-light w-100" type="button" onclick="clearTabFilters('tab-sellers')">{{ \App\CPU\translate('إلغاء البحث') }}</button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered" id="tbl-sellers">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ \App\CPU\translate('الاسم') }}</th>
            <th>{{ \App\CPU\translate('الهاتف') }}</th>
            <th>{{ \App\CPU\translate('البريد') }}</th>
            <th>{{ \App\CPU\translate('تاريخ الربط') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sellers as $row)
            <tr>
              <td>{{ $loop->iteration + ($sellers->currentPage()-1)*$sellers->perPage() }}</td>
              <td>{{ $row->f_name }}</td>
              <td>{{ $row->phone ?? '—' }}</td>
              <td>{{ $row->email ?? '—' }}</td>
              <td>{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pt-2">
      {!! $sellers->links() !!}
    </div>
  </div>
</div>
