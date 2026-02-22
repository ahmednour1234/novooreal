@php
  // عناوين الطباعة لكل تبويب
  $titles = [
    'statement' => \App\CPU\translate('كشف حساب المورد'),
    'purchases' => \App\CPU\translate('فواتير المشتريات'),
    'returns'   => \App\CPU\translate('فواتير مرتجع المشتريات'),
    'vouchers'  => \App\CPU\translate('السندات (قبض/صرف)'),
    'journal'   => \App\CPU\translate('القيود اليومية'),
    'details'   => \App\CPU\translate('تفاصيل المورد'),
  ];
  $printTitle = $titles[$activeTab] ?? '';
@endphp

<style>
  .tab-toolbar{
    background:#f8f9fb;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:14px;
    margin-bottom:12px;
  }
  .toolbar-inner{
    display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;
  }
  .filter-form .form-group label{font-size:.85rem;color:#4b5563;margin-bottom:6px}
  .filter-form .form-control{height:40px;border-radius:10px;border:1px solid #e5e7eb}
  .filter-actions .btn{height:40px;border-radius:10px}
  .toolbar-actions .btn{height:40px;border-radius:10px}
  .btn-light{background:#fff;border:1px solid #e5e7eb}
  .quick-range .btn{padding:.25rem .6rem;border-radius:999px}
  @media (max-width: 991.98px){
    .toolbar-inner{align-items:stretch}
    .toolbar-actions{width:100%;display:flex;gap:8px;justify-content:flex-start}
  }
</style>

<div class="tab-toolbar no-print">
  <div class="toolbar-inner">
    {{-- الفورم --}}
    <form action="{{ url()->current() }}" method="GET" class="filter-form" style="flex:1 1 680px;max-width:100%">
      <input type="hidden" name="tab" value="{{ $activeTab }}">

      <div class="form-row">
        <div class="form-group col-lg-5 col-md-6 col-12">
          <label for="filter-search">{{ \App\CPU\translate('بحث بالكلام/الرقم') }}</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="tio-search"></i></span>
            </div>
            <input id="filter-search" type="text" name="search" class="form-control"
                   value="{{ $search }}" placeholder="{{ \App\CPU\translate('اكتب للبحث...') }}">
          </div>
        </div>

        <div class="form-group col-lg-3 col-md-3 col-6">
          <label for="filter-start">{{ \App\CPU\translate('من تاريخ') }}</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="tio-calendar-month"></i></span>
            </div>
            <input id="filter-start" type="date" name="start_date" class="form-control" value="{{ $start_date }}">
          </div>
        </div>

        <div class="form-group col-lg-3 col-md-3 col-6">
          <label for="filter-end">{{ \App\CPU\translate('إلى تاريخ') }}</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="tio-calendar-month"></i></span>
            </div>
            <input id="filter-end" type="date" name="end_date" class="form-control" value="{{ $end_date }}">
          </div>
        </div>

        <div class="form-group col-lg-1 col-md-12 col-12 d-flex align-items-end filter-actions" style="gap:6px">
          <button type="submit" class="btn btn-primary btn-block">
            {{ \App\CPU\translate('بحث') }}
          </button>
        </div>

        <div class="form-group col-12 quick-range" style="margin-top:-2px">
          <div class="d-flex align-items-center flex-wrap" style="gap:8px">
            <span class="text-muted" style="font-size:.85rem">{{ \App\CPU\translate('نطاق سريع') }}:</span>
            <button type="button" class="btn btn-light" data-range="today">{{ \App\CPU\translate('اليوم') }}</button>
            <button type="button" class="btn btn-light" data-range="week">{{ \App\CPU\translate('هذا الأسبوع') }}</button>
            <button type="button" class="btn btn-light" data-range="month">{{ \App\CPU\translate('هذا الشهر') }}</button>
            <button type="button" class="btn btn-light" data-range="quarter">{{ \App\CPU\translate('هذا الربع') }}</button>
            <a href="{{ url()->current() }}?tab={{ $activeTab }}" class="btn btn-light">
              {{ \App\CPU\translate('تفريغ') }}
            </a>
          </div>
        </div>
      </div>
    </form>

    {{-- أزرار التصدير والطباعة --}}
    <div class="toolbar-actions">
      <button type="button" class="btn btn-light"
              onclick="exportTableCSV(window._activeTableId || '', '{{ $printTitle }}')">
        <i class="tio-download-to"></i> {{ \App\CPU\translate('تصدير إكسل') }}
      </button>
      <button type="button" class="btn btn-primary"
              onclick="printTable(window._activeTableId || '', '{{ $printTitle }}')">
        <i class="tio-print"></i> {{ \App\CPU\translate('طباعة') }}
      </button>
    </div>
  </div>
</div>

<script>
  (function(){
    // مساعد: ضبط نطاقات سريعة للتواريخ
    function formatDate(d){
      const pad = n => (n<10?'0':'') + n;
      return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
    }
    function startOfWeek(d){
      const c = new Date(d);
      const day = (c.getDay()+6)%7; // اجعل الاثنين بداية (يمكن تغييره)
      c.setDate(c.getDate() - day);
      return c;
    }
    function endOfWeek(d){
      const s = startOfWeek(d);
      const e = new Date(s);
      e.setDate(s.getDate()+6);
      return e;
    }
    function startOfMonth(d){ return new Date(d.getFullYear(), d.getMonth(), 1); }
    function endOfMonth(d){ return new Date(d.getFullYear(), d.getMonth()+1, 0); }
    function startOfQuarter(d){
      const qStartMonth = Math.floor(d.getMonth()/3)*3;
      return new Date(d.getFullYear(), qStartMonth, 1);
    }
    function endOfQuarter(d){
      const s = startOfQuarter(d);
      return new Date(s.getFullYear(), s.getMonth()+3, 0);
    }

    document.addEventListener('DOMContentLoaded', function(){
      var wrap = document.querySelector('.tab-toolbar');
      if(!wrap) return;

      var btns = wrap.querySelectorAll('.quick-range .btn[data-range]');
      var startInput = document.getElementById('filter-start');
      var endInput   = document.getElementById('filter-end');

      btns.forEach(function(btn){
        btn.addEventListener('click', function(){
          const now = new Date();
          let s, e;
          switch(this.dataset.range){
            case 'today':
              s = e = now;
              break;
            case 'week':
              s = startOfWeek(now);
              e = endOfWeek(now);
              break;
            case 'month':
              s = startOfMonth(now);
              e = endOfMonth(now);
              break;
            case 'quarter':
              s = startOfQuarter(now);
              e = endOfQuarter(now);
              break;
          }
          startInput.value = formatDate(s);
          endInput.value   = formatDate(e);
        });
      });
    });
  })();
</script>
