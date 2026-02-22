@php
  use App\Models\JournalEntry;
  use App\Models\JournalEntryDetail;

  // القيود التي يظهر بها حساب المورد ضمن التفاصيل
  $entryIdsQ = JournalEntryDetail::query()->where('account_id', $accId);
  if($start_date){ $entryIdsQ->whereDate('entry_date','>=',$start_date); }
  if($end_date){   $entryIdsQ->whereDate('entry_date','<=',$end_date); }
  if($search){
    // سنفلتر لاحقاً على الـ JournalEntry (الوصف/المرجع)
  }
  $entryIds = $entryIdsQ->pluck('journal_entry_id')->unique();

  $q = JournalEntry::query()->whereIn('id', $entryIds);
  if($search){
    $q->where(function($qq) use($search){
      $qq->where('description','like',"%$search%")
         ->orWhere('reference','like',"%$search%");
    });
  }

  $rows = $q->orderBy('entry_date','desc')->orderBy('id','desc')->paginate(20)->appends(request()->all());

  // خريطة مبالغ (مدين/دائن) لحساب المورد داخل كل قيد
  $details = JournalEntryDetail::whereIn('journal_entry_id', $rows->pluck('id'))
            ->where('account_id', $accId)
            ->get()
            ->groupBy('journal_entry_id');

  $tableId = 'tbl-journal';
@endphp

<script>window._activeTableId = '{{ $tableId }}';</script>

<div class="table-responsive">
  <table id="{{ $tableId }}" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>{{ \App\CPU\translate('رقم القيد') }}</th>
        <th>{{ \App\CPU\translate('التاريخ') }}</th>
        <th>{{ \App\CPU\translate('المرجع') }}</th>
        <th>{{ \App\CPU\translate('الوصف') }}</th>
        <th class="text-center">{{ \App\CPU\translate('مدين (لحساب المورد)') }}</th>
        <th class="text-center">{{ \App\CPU\translate('دائن (لحساب المورد)') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $i=>$e)
        @php
          $d = $details->get($e->id, collect());
          $sumD = (float) $d->sum('debit');
          $sumC = (float) $d->sum('credit');
        @endphp
        <tr>
          <td>{{ $rows->firstItem()+$i }}</td>
          <td>{{ $e->id }}</td>
          <td>{{ $e->entry_date }}</td>
          <td>{{ $e->reference ?: '—' }}</td>
          <td>{{ $e->description ?: '—' }}</td>
          <td class="text-center">{{ number_format($sumD,2,'.',',') }} {{ $currency }}</td>
          <td class="text-center">{{ number_format($sumC,2,'.',',') }} {{ $currency }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="table-footer px-3 py-2 d-flex justify-content-between align-items-center">
  <div class="small text-muted">{{ \App\CPU\translate('عدد النتائج') }}: {{ $rows->count() }}</div>
  <div>{!! $rows->links() !!}</div>
</div>
