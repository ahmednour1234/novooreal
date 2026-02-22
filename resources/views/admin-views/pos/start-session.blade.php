@extends('layouts.admin.app')

@section('title', 'فتح جلسة كاشير')

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  /* ===== تهيئة عامة ===== */
  .session-viewport{
    min-height: calc(100vh - 140px); /* مساحة كافية بعد الـbreadcrumb */
    display:flex; align-items:center; justify-content:center;
  }

  /* ===== بطاقة الجلسة ===== */
  .card-session{
    width:100%;
    max-width: 520px;
    border:1px solid #e9edf5;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 16px 40px rgba(0,0,0,.08);
    animation: fadeInUp .5s ease-in-out;
    background:#fff;
  }
  @keyframes fadeInUp{
    from{opacity:0; transform:translateY(30px);}
    to{opacity:1; transform:translateY(0);}
  }

  .card-session .card-header{
    background:#f7f9fc;
    border-bottom:1px solid #e9edf5;
    text-align:center;
    padding:16px 20px;
  }
  .card-session .card-header h4{
    margin:0; color:#001B63; font-weight:800;
  }
  .card-session .card-header .sub{
    color:#6b7280; font-size:.92rem;
  }

  .form-control::placeholder{ color:#a0a7af; }
  .status-hint{ color:#6b7280; }
  .caps-hint{ font-size:.85rem; color:#b45309; display:none; }

  /* ===== حقل كلمة المرور مع أيقونة إظهار/إخفاء ===== */
  .input-with-action{ position:relative; }
  .toggle-visibility{
    position:absolute; inset-inline-end:.5rem; inset-block-start:50%;
    transform:translateY(-50%);
    border:1px solid #e9edf5; background:#fff; color:#334155;
    border-radius:8px; padding:.35rem .5rem; line-height:1; cursor:pointer;
  }

  .btn-primary{
    background:#001B63; border-color:#001B63;
  }
  .btn-primary:hover{
    background:#0b5ed7; border-color:#0a58ca;
  }

  @media (max-width: 575.98px){
    .card-session{ max-width: 100%; border-radius:12px; }
  }
</style>
@endpush

@section('content')
<div class="content container-fluid">
  {{-- Breadcrumb --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('الرئيسية') }}</a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('الكاشير') }}</li>
      </ol>
    </nav>
  </div>

  <div class="session-viewport">
    <div class="card card-session">
      <div class="card-header">
        <h4 class="mb-1"><i class="tio-lock-outlined"></i> {{ \App\CPU\translate('فتح جلسة كاشير') }}</h4>
        <div class="sub">{{ \App\CPU\translate('أدخل كلمة المرور لبدء الجلسة') }}</div>
      </div>

      <div class="card-body">
        <div id="sessionStatus" class="mb-3 status-hint text-center">
          <small>{{ \App\CPU\translate('يرجى إدخال كلمة المرور لبدء الجلسة.') }}</small>
        </div>

        <div class="mb-3">
          <label class="sr-only" for="cashierPassword">{{ \App\CPU\translate('كلمة المرور') }}</label>
          <div class="input-with-action">
            <input type="password" id="cashierPassword" class="form-control text-center"
                   placeholder="{{ \App\CPU\translate('كلمة المرور') }}" autocomplete="current-password" />
            <button type="button" id="togglePwd" class="toggle-visibility" aria-label="{{ \App\CPU\translate('إظهار/إخفاء كلمة المرور') }}">
              <i class="tio-remove-red-eye"></i>
            </button>
          </div>
          <div id="capsHint" class="caps-hint mt-2">
            <i class="tio-warning-outlined"></i> {{ \App\CPU\translate('مفتاح Caps Lock مفعّل') }}
          </div>
        </div>

        <button id="startSessionBtn" onclick="openPOSSession()" class="btn btn-primary btn-lg w-100">
          <i class="tio-play-circle"></i> {{ \App\CPU\translate('بدء الجلسة') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endsection
<script>
  // إظهار/إخفاء كلمة المرور
  document.getElementById('togglePwd')?.addEventListener('click', function(){
    const input = document.getElementById('cashierPassword');
    if(!input) return;
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    this.innerHTML = type === 'password' ? '<i class="tio-remove-red-eye"></i>' : '<i class="tio-invisible"></i>';
    input.focus();
  });

  // Caps Lock تنبيه
  document.getElementById('cashierPassword')?.addEventListener('keyup', function(e){
    const hint = document.getElementById('capsHint');
    if(!hint) return;
    const capsOn = e.getModifierState && e.getModifierState('CapsLock');
    hint.style.display = capsOn ? 'block' : 'none';
  });

  // الضغط على Enter لبدء الجلسة
  document.getElementById('cashierPassword')?.addEventListener('keydown', function(e){
    if(e.key === 'Enter'){ openPOSSession(); }
  });

  function openPOSSession() {
    const btn = document.getElementById('startSessionBtn');
    const passwordInput = document.getElementById('cashierPassword');
    const statusDiv = document.getElementById('sessionStatus');
    const password = (passwordInput?.value || '').trim();

    if (!password) {
      statusDiv.innerHTML = '<div class="alert alert-danger py-2 my-2">{{ \App\CPU\translate('من فضلك أدخل كلمة المرور.') }}</div>';
      passwordInput?.focus();
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> {{ \App\CPU\translate('جاري فتح الجلسة...') }}';

    fetch("{{ route('admin.pos.session.open') }}", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ password })
    })
    .then(async response => {
      const contentType = response.headers.get("content-type");
      if (contentType && contentType.includes("application/json")) {
        const data = await response.json();
        if (response.ok) {
          if (data.status) {
            window.location.href = "{{ route('admin.pos.index', ['type' => 1]) }}";
          } else {
            throw new Error(data.message || '{{ \App\CPU\translate('حدث خطأ أثناء فتح الجلسة.') }}');
          }
        } else {
          throw new Error(data.message || '{{ \App\CPU\translate('حدث خطأ بالسيرفر.') }}');
        }
      } else {
        throw new Error('{{ \App\CPU\translate('استجابة غير متوقعة من الخادم. الرجاء مراجعة السيرفر أو التحقق من المسار.') }}');
      }
    })
    .catch(error => {
      statusDiv.innerHTML = '<div class="alert alert-danger py-2 my-2">' + (error.message || 'Error') + '</div>';
      btn.disabled = false;
      btn.innerHTML = '<i class="tio-play-circle"></i> {{ \App\CPU\translate('بدء الجلسة') }}';
    });
  }
</script>

@push('script_2')
@endpush
