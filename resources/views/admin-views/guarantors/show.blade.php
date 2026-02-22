@extends('layouts.admin.app')

@section('title', 'تفاصيل الضامن')

@push('css_or_js')
<style>
  /* ===== ألوان ولمسات عامة هادئة ===== */
  :root{
    --card-border:#e9edf5;
    --muted:#6b7280;
    --soft:#f5f7fa;
    --ink:#101828;
    --accent:#001B63;
  }
  .page-card{ border:1px solid var(--card-border); border-radius:14px; overflow:hidden; box-shadow:0 10px 24px rgba(0,0,0,.06); }
  .page-card .card-header{ background:#f8fafc; border-bottom:1px solid var(--card-border); }
  .page-title{ margin:0; font-weight:800; color:var(--ink); }
  .page-sub{ color:var(--muted); font-size:.92rem; }

  /* الهيدر العلوي */
  .header-actions .btn{ padding:.35rem .6rem; }

  /* صورة الضامن */
  .avatar-wrap{
    width:140px; height:140px; border-radius:50%; margin-inline:auto; position:relative;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    border:4px solid #fff; outline:3px solid var(--accent);
    overflow:hidden; background:#fff;
  }
  .avatar-img{ width:100%; height:100%; object-fit:cover; display:block; }

  /* شبكة التفاصيل */
  .detail-grid{ display:grid; grid-template-columns:repeat(1, minmax(0,1fr)); gap:.75rem; }
  @media(min-width:768px){ .detail-grid{ grid-template-columns:repeat(2, minmax(0,1fr)); } }
  .detail-item{
    display:flex; align-items:center; gap:.7rem; background:#fbfdff; border:1px solid var(--card-border);
    border-radius:12px; padding:.65rem .75rem;
  }
  .detail-ico{
    width:34px; height:34px; border-radius:10px; background:var(--soft); color:var(--accent);
    display:grid; place-items:center; flex:0 0 auto;
  }
  .detail-label{ font-size:.82rem; color:#495057; margin:0; }
  .detail-value{ color:#111827; font-weight:600; }

  /* شارة معلومات */
  .chip{ background:#f3f6fa; border:1px solid #e7ecf2; color:#3a3f45; border-radius:999px; padding:.3rem .6rem; font-size:.75rem; }

  /* المرفقات */
  .attachments{ display:grid; grid-template-columns:repeat(auto-fill, minmax(140px,1fr)); gap:.75rem; }
  .att{
    position:relative; border:1px solid var(--card-border); border-radius:12px; overflow:hidden; background:#fff;
  }
  .att img{ width:100%; height:140px; object-fit:cover; display:block; transition:transform .25s ease; }
  .att:hover img{ transform:scale(1.03); }
  .att a{ display:block; }

  /* فواصل لطيفة */
  .section-head{ margin-bottom:.5rem; font-weight:700; }
  .section-sub{ color:var(--muted); font-size:.9rem; margin-bottom:1rem; }
</style>
@endpush

@section('content')
<div class="content container-fluid">
  <!-- Breadcrumb -->
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ \App\CPU\translate('تفاصيل  الضامن') }}
        </li>
      </ol>
    </nav>
  </div>

  @php
    $images = json_decode($guarantor->images, true) ?? [];
    $firstImage = $images[0] ?? null;
  @endphp

  <!-- بطاقة التفاصيل -->
  <div class="card page-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div>
        <h5 class="page-title mb-1">{{ \App\CPU\translate('بيانات الضامن') }}</h5>
        <div class="page-sub">
          {{ \App\CPU\translate('مراجعة بيانات الضامن والمرفقات') }}
        </div>
      </div>
      <div class="header-actions d-flex align-items-center gap-2">
        @if(isset($guarantor->updated_at))
          <span class="chip d-none d-md-inline">
            <i class="tio-date-range"></i>
            {{ \App\CPU\translate('آخر تحديث') }}:
            {{ \Carbon\Carbon::parse($guarantor->updated_at)->format('Y-m-d H:i') }}
          </span>
        @endif
        <a href="{{ route('admin.guarantors.edit', $guarantor->id) }}" class="btn btn-sm btn-outline-primary">
          <i class="tio-edit"></i> {{ \App\CPU\translate('تعديل') }}
        </a>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger">
          {{ \App\CPU\translate('عودة') }}
        </a>
      </div>
    </div>

    <div class="card-body">
      <div class="row align-items-start g-4">
        <!-- Avatar -->
        <div class="col-md-3 text-center">
          <div class="avatar-wrap">
            <img
              src="{{ $firstImage ? asset('storage/'.$firstImage) : asset('public/assets/admin/img/160x160/img1.jpg') }}"
              onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
              alt="Avatar" class="avatar-img">
          </div>
          <div class="mt-3">
            <span class="chip">
              <i class="tio-user"></i>
              {{ $guarantor->name }}
            </span>
          </div>
        </div>

        <!-- Details -->
        <div class="col-md-9">
          <div class="section-head">{{ \App\CPU\translate('التفاصيل') }}</div>
          <div class="detail-grid">
            <div class="detail-item">
              <div class="detail-ico"><i class="tio-id-badge"></i></div>
              <div>
                <p class="detail-label mb-1">{{ \App\CPU\translate('الرقم القومي') }}</p>
                <div class="detail-value">{{ $guarantor->national_id ?: '—' }}</div>
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-ico"><i class="tio-android-phone-vs"></i></div>
              <div>
                <p class="detail-label mb-1">{{ \App\CPU\translate('الجوال') }}</p>
                <div class="detail-value">{{ $guarantor->phone ?: '—' }}</div>
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-ico"><i class="tio-home-vs"></i></div>
              <div>
                <p class="detail-label mb-1">{{ \App\CPU\translate('العنوان') }}</p>
                <div class="detail-value">{{ $guarantor->address ?: '—' }}</div>
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-ico"><i class="tio-briefcase"></i></div>
              <div>
                <p class="detail-label mb-1">{{ \App\CPU\translate('الوظيفة') }}</p>
                <div class="detail-value">{{ $guarantor->job ?: '—' }}</div>
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-ico"><i class="tio-dollar-outlined"></i></div>
              <div>
                <p class="detail-label mb-1">{{ \App\CPU\translate('الدخل الشهري') }}</p>
                <div class="detail-value">
                  @if(!is_null($guarantor->monthly_income))
                    {{ number_format($guarantor->monthly_income, 2) }}
                  @else
                    —
                  @endif
                </div>
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-ico"><i class="tio-user-switch"></i></div>
              <div>
                <p class="detail-label mb-1">{{ \App\CPU\translate('العلاقة') }}</p>
                <div class="detail-value">{{ $guarantor->relation ?: '—' }}</div>
              </div>
            </div>
          </div>

          @if(isset($guarantor->created_at) || isset($guarantor->updated_at))
            <div class="mt-3 d-flex flex-wrap gap-2">
              @if(isset($guarantor->created_at))
                <span class="chip">
                  <i class="tio-date-range"></i>
                  {{ \App\CPU\translate('تاريخ الإنشاء') }}:
                  {{ \Carbon\Carbon::parse($guarantor->created_at)->format('Y-m-d H:i') }}
                </span>
              @endif
              @if(isset($guarantor->updated_at))
                <span class="chip">
                  <i class="tio-refresh"></i>
                  {{ \App\CPU\translate('آخر تحديث') }}:
                  {{ \Carbon\Carbon::parse($guarantor->updated_at)->format('Y-m-d H:i') }}
                </span>
              @endif
            </div>
          @endif
        </div>
      </div>

      {{-- المرفقات --}}
      @if(!empty($images))
        <hr class="my-4">
        <div class="section-head">{{ \App\CPU\translate('مرفقات الصور') }}</div>
        <div class="section-sub">{{ \App\CPU\translate('اضغط على الصورة لفتحها في تبويب جديد') }}</div>

        <div class="attachments">
          @foreach($images as $img)
            <div class="att">
              <a href="{{ asset('storage/'.$img) }}" target="_blank" rel="noopener">
                <img src="{{ asset('storage/'.$img) }}"
                     alt="Attachment"
                     onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'">
              </a>
            </div>
          @endforeach
        </div>
      @endif

    </div>
  </div>
  <!-- /بطاقة التفاصيل -->
</div>
@endsection
