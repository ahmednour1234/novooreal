@php
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\Schema;
  use Carbon\Carbon;

  // اختر حساب العميل: account_id_to ثم account_id
  $accountId = $customer->account_id_to ?? $customer->account_id;

  // حدد اسم جدول الحركات المتوفر
  $txTable = null;
  if (Schema::hasTable('account_transactions')) {
      $txTable = 'account_transactions';
  } elseif (Schema::hasTable('transactions')) {
      $txTable = 'transactions';
  } elseif (Schema::hasTable('transections')) { // في حال كان الاسم بهذا الشكل عندك
      $txTable = 'transections';
  }

  // إجماليات مدين/دائن من جدول الحركات
  $sumDebit  = $txTable ? DB::table($txTable)->where('account_id', $accountId)->sum('debit')  : 0;
  $sumCredit = $txTable ? DB::table($txTable)->where('account_id', $accountId)->sum('credit') : 0;

  // الصافي = مدين - دائن
  $net = $sumDebit - $sumCredit;

  // عدّاد الفواتير (كما في كودك الحالي)
  $ordersCount = DB::table('orders')->where('user_id', $customer->id)->count();
@endphp

<style>
  /* ====== Full-width Identity Card (scoped) ====== */
  .idc-wrap { width:100%; }
  .idc-card{
    width:100%;
    position:relative; border:1px solid #e9edf5; border-radius:16px; overflow:hidden;
    box-shadow:0 8px 22px rgba(0,0,0,.06);
    background:#fff; margin-bottom:20px;
  }
  .idc-top{
    background:#f5f7fa; color:#333;
    padding:16px 18px; display:flex; align-items:center; gap:12px; border-bottom:1px solid #e9edf5;
  }
  .idc-photo{
    width:92px; height:92px; border-radius:12px; overflow:hidden; flex:0 0 auto;
    box-shadow:0 4px 14px rgba(0,0,0,.10); background:#f6f6f8; border:3px solid #fff;
  }
  .idc-photo img{ width:100%; height:100%; object-fit:cover; display:block; }
  .idc-name{ font-weight:700; font-size:20px; margin:0; }
  .idc-sub{ opacity:.8; font-size:12px; }

  .idc-body{ padding:18px; }
  .idc-strip{
    margin:0 0 14px; background:#fff; border:1px dashed #e3e8ef; border-radius:12px; padding:10px 12px;
    display:flex; flex-wrap:wrap; gap:10px;
  }
  .idc-badge{
    background:#fbfdff; border:1px solid #eef3f8; border-radius:10px; padding:8px 10px; min-width:140px;
    display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:13px;
  }
  .idc-badge .k{ opacity:.7; }
  .idc-badge .v{ font-weight:600; }

  .idc-list{ list-style:none; padding:0; margin:0; display:grid; gap:8px; }
  .idc-li{
    display:flex; align-items:center; gap:10px; padding:10px 12px;
    background:#fbfdff; border:1px solid #eef3f8; border-radius:10px;
  }
  .idc-icon{
    width:34px; height:34px; border-radius:10px; display:grid; place-items:center; background:#f0f3f7; color:#445;
    font-size:16px;
  }

  .idc-footer{
    display:flex; justify-content:space-between; align-items:center; gap:8px; padding:12px 18px;
    border-top:1px solid #eef2f7; background:#fcfcfe;
  }
  .idc-tag{
    padding:6px 10px; border-radius:999px; font-size:12px; border:1px solid #e8edf4; background:#fff;
  }
  .idc-net{ font-weight:800; }
  .idc-net.debit { color:#2e7d32; }   /* صافي مدين */
  .idc-net.credit{ color:#c62828; }   /* صافي دائن */
</style>

<div class="idc-wrap">
  {{-- ================= بطاقة العميل (Full-width) ================= --}}
  <div class="idc-card" id="idc-customer">
    <div class="idc-top">
      <div class="idc-photo">
        <img
          onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
          src="{{ asset('storage/customer/'.$customer->image) }}"
          alt="{{ \App\CPU\translate('image_description') }}">
      </div>
      <div>
        <h5 class="idc-name mb-1">{{ $customer->name }}</h5>
        <div class="idc-sub">
          {{ \App\CPU\translate('رقم العميل') }} #{{ $customer->id }}
          • {{ \App\CPU\translate('تاريخ الانضمام') }} {{ Carbon::parse($customer->created_at)->format('Y-m-d') }}
          @if($accountId)
            • {{ \App\CPU\translate('رقم الحساب') }} {{ $accountId }}
          @endif
        </div>
      </div>
      <div class="ml-auto">
        <span class="idc-tag">{{ \App\CPU\translate('عدد الفواتير') }}: {{ $ordersCount }}</span>
      </div>
    </div>

    <div class="idc-body">
      {{-- شريط الأرصدة من جدول الحركات --}}
      <div class="idc-strip">
        <div class="idc-badge">
          <span class="k">{{ \App\CPU\translate('مدين') }}</span>
          <span class="v">{{ number_format($sumDebit,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</span>
        </div>
        <div class="idc-badge">
          <span class="k">{{ \App\CPU\translate('دائن') }}</span>
          <span class="v">{{ number_format($sumCredit,2) }} {{ \App\CPU\Helpers::currency_symbol() }}</span>
        </div>
        <div class="idc-badge" style="min-width:220px">
          <span class="k">{{ \App\CPU\translate('الصافي') }}</span>
          <span class="v">
            {{ number_format(abs($net),2) }} {{ \App\CPU\Helpers::currency_symbol() }}
            ({{ $net >= 0 ? \App\CPU\translate('صافي مدين') : \App\CPU\translate('صافي دائن') }})
          </span>
        </div>
      </div>

      {{-- بيانات الاتصال والعنوان --}}
      <ul class="idc-list">
        <li class="idc-li">
          <div class="idc-icon"><i class="tio-android-phone-vs"></i></div>
          <div>
            <div class="small text-muted">{{ \App\CPU\translate('الهاتف') }}</div>
            <div>{{ $customer->mobile ?: '—' }}</div>
          </div>
        </li>

        @if($customer->email)
        <li class="idc-li">
          <div class="idc-icon"><i class="tio-online"></i></div>
          <div>
            <div class="small text-muted">{{ \App\CPU\translate('البريد الإلكتروني') }}</div>
            <div>{{ $customer->email }}</div>
          </div>
        </li>
        @endif

        <li class="idc-li">
          <div class="idc-icon"><i class="tio-map"></i></div>
          <div>
            <div class="small text-muted">{{ \App\CPU\translate('المقاطعة') }} / {{ \App\CPU\translate('المدينة') }}</div>
            <div>{{ $customer->state ?: '—' }} — {{ $customer->city ?: '—' }}</div>
          </div>
        </li>

        <li class="idc-li">
          <div class="idc-icon"><i class="tio-poi"></i></div>
          <div>
            <div class="small text-muted">{{ \App\CPU\translate('كود المدينة') }}</div>
            <div>{{ $customer->zip_code ?: '—' }}</div>
          </div>
        </li>

        <li class="idc-li">
          <div class="idc-icon"><i class="tio-home-vs"></i></div>
          <div>
            <div class="small text-muted">{{ \App\CPU\translate('العنوان') }}</div>
            <div>{{ $customer->address ?: '—' }}</div>
          </div>
        </li>
      </ul>
    </div>

    <div class="idc-footer">
      <span class="idc-tag">{{ \App\CPU\translate('العميل') }}</span>
      <span class="idc-net {{ $net >= 0 ? 'debit' : 'credit' }}">
        {{ number_format(abs($net),2) }} {{ \App\CPU\Helpers::currency_symbol() }}
      </span>
    </div>
  </div>

  {{-- ================= بطاقة الضامن (Full-width) - إن وجِد) ================= --}}
  @if($customer->guarantor)
    @php $g = $customer->guarantor; $imgs = json_decode($g->images, true) ?? []; @endphp

    <div class="idc-card" id="idc-guarantor">
      <div class="idc-top">
        <div class="idc-photo">
          <img
            onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
            src="{{ asset('storage/' . ($imgs[0] ?? 'assets/admin/img/160x160/img1.jpg')) }}"
            alt="{{ $g->name }}">
        </div>
        <div>
          <h5 class="idc-name mb-1">{{ $g->name }}</h5>
          <div class="idc-sub">{{ \App\CPU\translate('الضامن المرتبط بالعميل') }} #{{ $customer->id }}</div>
        </div>
        <div class="ml-auto">
          <span class="idc-tag">{{ \App\CPU\translate('صلة القرابة') }}: {{ $g->relation ?: '—' }}</span>
        </div>
      </div>

      <div class="idc-body">
        <div class="idc-strip">
          <div class="idc-badge">
            <span class="k">{{ \App\CPU\translate('الوظيفة') }}</span>
            <span class="v">{{ $g->job ?: '—' }}</span>
          </div>
          <div class="idc-badge">
            <span class="k">{{ \App\CPU\translate('الدخل الشهري') }}</span>
            <span class="v">{{ number_format($g->monthly_income ?? 0,2) }}</span>
          </div>
          <div class="idc-badge">
            <span class="k">{{ \App\CPU\translate('الهوية') }}</span>
            <span class="v">{{ $g->national_id ?: '—' }}</span>
          </div>
        </div>

        <ul class="idc-list">
          <li class="idc-li">
            <div class="idc-icon"><i class="tio-call-phone"></i></div>
            <div>
              <div class="small text-muted">{{ \App\CPU\translate('الهاتف') }}</div>
              <div>{{ $g->phone ?: '—' }}</div>
            </div>
          </li>

          @if(count($imgs) > 1)
          <li class="idc-li" style="align-items:flex-start;">
            <div class="idc-icon"><i class="tio-attachment"></i></div>
            <div>
              <div class="small text-muted mb-1">{{ \App\CPU\translate('مرفقات الضامن') }}</div>
              <div class="d-flex flex-wrap" style="gap:8px;">
                @foreach(array_slice($imgs,1) as $img)
                  <img src="{{ asset('storage/'.$img) }}" alt="attachment"
                       style="height:70px; width:auto; border-radius:8px; border:1px solid #e9eef5;">
                @endforeach
              </div>
            </div>
          </li>
          @endif
        </ul>
      </div>

      <div class="idc-footer">
        <span class="idc-tag">{{ \App\CPU\translate('الضامن') }}</span>
        <span class="idc-tag">{{ \App\CPU\translate('صلة') }}: {{ $g->relation ?: '—' }}</span>
      </div>
    </div>
  @endif
</div>
