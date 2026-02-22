@extends('layouts.admin.app')  
@section('title', \App\CPU\translate('تقييم الموظف'))

@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('public/assets/admin/css/select2.min.css') }}">
  <style>
    :root{
      --ink:#0f172a; --grid:#e5e7eb; --shadow:0 12px 28px -18px rgba(2,32,71,.18); --rd:14px;
    }
    .card{border:1px solid var(--grid); border-radius:var(--rd); box-shadow:var(--shadow); margin-top:16px}
    .card-header{font-weight:700}
    .form-label{font-weight:700; color:#111827; margin-bottom:.35rem}
    .form-control{min-height:42px}
    .select2-container{width:100%!important}
    .select2-selection--single{
      height:42px!important; display:flex; align-items:center; border:1px solid #ddd!important; border-radius:6px!important
    }
    .select2-selection__rendered{line-height:40px!important; padding-right:8px!important}
    .select2-selection__arrow{height:40px!important}
    .row.gx-2>[class^="col-"], .row.gx-2>[class*=" col-"]{padding-left:.35rem; padding-right:.35rem}
    .row.gy-2{row-gap:.5rem}
    /* زر الحفظ لليسار */
    .actions-bar{display:flex; justify-content:flex-end; gap:8px}
    @media (max-width: 576px){
      .actions-bar{justify-content:stretch}
    }
  </style>
@endpush

@section('content')
<div class="content container-fluid">

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-1">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('تقييم الموظفيين') }}</li>
      </ol>
    </nav>
  </div>

  <div class="card">
    <div class="card-header">{{ \App\CPU\translate('تقييم الموظف') }}</div>
    <div class="card-body">
      <form id="salary-form" method="POST" action="{{ route('admin.salaries.storerating') }}">
        @csrf

        {{-- الصف الأول: الموظف + التقييم + ملاحظات --}}
        <div class="row gx-2 gy-2">
          <div class="col-lg-6">
            <label for="seller_id" class="form-label">{{ \App\CPU\translate('اختار موظف') }}</label>
            <select id="seller_id" name="seller_id" class="form-control select2" required>
              <option value="">{{ \App\CPU\translate('اختار موظف') }}</option>
              @foreach($sellers as $seller)
                <option value="{{ $seller->id }}">
                  {{ $seller->email }} - ({{ $seller->f_name.$seller->l_name }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-3 col-md-6">
            <label for="score" class="form-label">{{ \App\CPU\translate('التقييم') }}</label>
            <input type="number" min="0" step="0.01" id="score" name="score"
                   class="form-control" placeholder="{{ \App\CPU\translate('مثال: 0 إلى 100') }}" required>
          </div>

          <div class="col-lg-3 col-md-6">
            <label for="note" class="form-label">{{ \App\CPU\translate('ملاحظات') }}</label>
            <input type="text" id="note" name="note" class="form-control"
                   placeholder="{{ \App\CPU\translate('أدخل ملاحظة مختصرة') }}" required>
          </div>
        </div>

        {{-- الصف الثاني: المؤشرات (قراءة فقط) --}}
        <div class="row gx-2 gy-2 mt-2">
          <div class="col-lg-4 col-md-6">
            <label for="commission" class="form-label">{{ \App\CPU\translate('اجمالي التحصيلات') }}</label>
            <input type="text" id="commission" name="commission" class="form-control" readonly>
          </div>

          <div class="col-lg-4 col-md-6">
            <label for="number_of_visitors" class="form-label">{{ \App\CPU\translate('عدد الزيارات التي من المتفرض القيام بها') }}</label>
            <input type="text" id="number_of_visitors" name="number_of_visitors" class="form-control" readonly>
          </div>

          <div class="col-lg-4 col-md-6">
            <label for="result_of_visitors" class="form-label">{{ \App\CPU\translate('عدد الزيارات التي قام بها بالفعل') }}</label>
            <input type="text" id="result_of_visitors" name="result_of_visitors" class="form-control" readonly>
          </div>
        </div>

        {{-- الأزرار (يسار) --}}
        <div class="actions-bar mt-3">
          <button type="submit" class="btn btn-primary col-3">
            {{ \App\CPU\translate('حفظ') }}
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

{{-- JS --}}
<script src="{{ asset('public/assets/admin/js/jquery.min.js') }}"></script>
<script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>
<script>
  $(function(){
    // تهيئة Select2
    $('.select2').select2({ width:'100%' });

    // عنوان جلب البيانات (آمن مع الاستبدال)
    const showUrlTemplate = @json(route('admin.salaries.showsalary', ['id' => '__ID__']));

    // جلب تفاصيل الموظف المختار
    $('#seller_id').on('change', function(){
      const sellerId = $(this).val();
      if(!sellerId){
        $('#commission, #number_of_visitors, #result_of_visitors').val('');
        return;
      }
      const url = showUrlTemplate.replace('__ID__', sellerId);
      $.get(url)
        .done(function(data){
          $('#commission').val(data.commission ?? '');
          $('#number_of_visitors').val(data.visitors ?? '');
          $('#result_of_visitors').val(data.result_visitors ?? '');
        })
        .fail(function(){
          alert('{{ \App\CPU\translate('حدث خطأ أثناء جلب بيانات الموظف') }}');
        });
    });
  });
</script>
