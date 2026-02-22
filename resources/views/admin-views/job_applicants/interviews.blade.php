@extends('layouts.admin.app')

@section('title', \App\CPU\translate('Interviews for Applicant'))

@push('css_or_js')
<style>
  :root{
    --ink:#0f172a; --muted:#667085; --grid:#e9eef5; --brand:#001B63;
    --bg:#f8fafc; --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
    --ok:#16a34a; --warn:#d97706; --bad:#dc2626; --info:#0ea5e9;
  }
  body{background:var(--bg)}
  .card-soft{background:#fff;border:1px solid var(--grid);border-radius:var(--rd);box-shadow:var(--shadow)}
  .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .page-head h1{font-size:1.15rem;margin:0;color:var(--ink);font-weight:800}
  .toolbar{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar .btn{min-height:42px}

  .meta-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
  @media (max-width: 768px){ .meta-grid{grid-template-columns:1fr} }
  .form-label{font-weight:700;color:#111827}

  .interview-card{border:1px solid var(--grid);border-radius:12px;padding:14px;background:#fff;transition:all .2s ease}
  .interview-card:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
  .interview-head{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
  .interviewer{font-weight:800;color:#111827}
  .date-chip{background:#f3f6fb;border:1px solid var(--grid);padding:.35rem .6rem;border-radius:999px;font-size:.8rem}

  .rows-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:10px}
  @media (max-width: 992px){ .rows-grid{grid-template-columns:repeat(2,1fr)} }
  @media (max-width: 576px){ .rows-grid{grid-template-columns:1fr} }

  .kv{display:flex;flex-direction:column;gap:4px}
  .kv .k{font-size:.78rem;color:var(--muted)}
  .kv .v{font-weight:700;color:#111827}

  .note-box{background:#f9fafb;border:1px dashed var(--grid);border-radius:10px;padding:10px;margin-top:10px;color:#111827}

  .action-buttons{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .btn-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;padding:0}

  .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px;color:var(--muted)}
  .empty-state img{max-width:220px;margin-bottom:12px;opacity:.9}
</style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('admin.job_applicants.index') }}" class="text-secondary">
            {{ \App\CPU\translate('متقدمين الوظائف') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تاريخ مقابلات') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ====== ترويسة — بيانات المتقدم + أدوات ====== --}}
  <div class="card-soft p-3 mb-3">
    <div class="page-head">
      <h1 class="mb-0">
        {{ \App\CPU\translate('مقابلات') }} — {{ $applicant->full_name }}
      </h1>
      <div class="toolbar">
        <a href="{{ route('admin.interview_evaluations.create', $applicant->id) }}" class="btn btn-primary">
          <i class="tio-add"></i> {{ \App\CPU\translate('اضافة مقابلة جديدة') }}
        </a>
        <a href="{{ route('admin.job_applicants.index') }}" class="btn btn-outline-secondary">
          <i class="tio-rotate-left"></i> {{ \App\CPU\translate('رجوع للقائمة') }}
        </a>
      </div>
    </div>

    <div class="meta-grid mt-2">
      <div>
        <label class="form-label">{{ \App\CPU\translate('البريد الإلكتروني') }}</label>
        <input class="form-control" value="{{ $applicant->email }}" readonly>
      </div>
      <div>
        <label class="form-label">{{ \App\CPU\translate('رقم الهاتف') }}</label>
        <input class="form-control" value="{{ $applicant->phone ?? '—' }}" readonly>
      </div>
      <div>
        <label class="form-label">{{ \App\CPU\translate('السيرة الذاتية') }}</label>
        @if(!empty($applicant->resume_pdf))
          <a class="form-control" href="{{ asset('storage/app/public/resumes/' . $applicant->resume_pdf) }}" target="_blank">
            <i class="tio-download"></i> {{ \App\CPU\translate('عرض السيرة') }}
          </a>
        @else
          <input class="form-control" value="—" readonly>
        @endif
      </div>
    </div>
  </div>

  {{-- ====== قائمة المقابلات ====== --}}
  @if($interviews->isEmpty())
    <div class="card-soft">
      <div class="empty-state">
        <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="no-data">
        <div>{{ \App\CPU\translate('لا يوجد سجلات مقابلات') }}</div>
      </div>
    </div>
  @else
    <div class="d-grid gap-3">
      @foreach($interviews as $interview)
        <div class="interview-card">
          <div class="interview-head">
            <div class="interviewer">
              <i class="tio-user"></i>
              {{ \App\CPU\translate('المقابِل') }}: {{ $interview->interviewer }}
            </div>
            <span class="date-chip">
              <i class="tio-calendar-month"></i>
              {{ \Carbon\Carbon::parse($interview->interview_date)->format('Y-m-d') }}
            </span>
          </div>

          <div class="rows-grid">
            <div class="kv">
              <div class="k">{{ \App\CPU\translate('التقييم') }}</div>
              <div class="v">{{ $interview->score ?? '—' }}</div>
            </div>
            <div class="kv">
              <div class="k">{{ \App\CPU\translate('آخر تحديث') }}</div>
              <div class="v">{{ optional($interview->updated_at)->format('Y-m-d H:i') }}</div>
            </div>
            <div class="kv">
              <div class="k">{{ \App\CPU\translate('أُنشئت في') }}</div>
              <div class="v">{{ optional($interview->created_at)->format('Y-m-d H:i') }}</div>
            </div>
            <div class="kv">
              <div class="k">{{ \App\CPU\translate('رقم السجل') }}</div>
              <div class="v">#{{ $interview->id }}</div>
            </div>
          </div>

          <div class="note-box">
            <div class="k">{{ \App\CPU\translate('الملاحظات') }}</div>
            <div class="v">{{ $interview->evaluation_notes ?? '—' }}</div>
          </div>

          <div class="action-buttons mt-2">
            <a href="{{ route('admin.interview_evaluations.edit', $interview->id) }}"
               class="btn btn-info btn-sm btn-icon"
               title="{{ \App\CPU\translate('تعديل') }}" data-toggle="tooltip">
              <i class="tio-edit"></i>
            </a>

            <form action="{{ route('admin.interview_evaluations.destroy', $interview->id) }}"
                  method="POST" onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد من حذف هذه المقابلة؟') }}');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm btn-icon"
                      title="{{ \App\CPU\translate('حذف') }}" data-toggle="tooltip">
                <i class="tio-delete-outlined"></i>
              </button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>
@endsection

@push('script_2')
<script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
@endpush
