@extends('layouts.admin.app')

@section('title', \App\CPU\translate('category_update'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css"/>
<style>
  /* ـــــ تحسينات بصرية خفيفة — بدون تدرّجات ـــــ */
  .page-card{ border:1px solid #e9edf5; border-radius:14px; overflow:hidden; }
  .page-card .card-header{ background:#f8fafc; border-bottom:1px solid #e9edf5; }
  .page-title{ margin:0; font-weight:700; }
  .muted{ color:#6b7280; font-size:.9rem; }

  .form-label{ font-weight:600; }
  .hint{ font-size:.8rem; color:#9aa4b2; }

  .img-frame{
    border:1px solid #e9edf5; border-radius:12px; background:#fff;
    width:220px; height:220px; display:flex; align-items:center; justify-content:center; overflow:hidden;
    margin-inline:auto;
  }
  .img-frame img{ width:100%; height:100%; object-fit:cover; }

  .custom-file-label{ overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
  .btn-icon{ padding:.25rem .5rem; }

  .actions-bar{ display:flex; gap:.5rem; justify-content:flex-start; } /* زر صغير عالشمال */
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
          {{ \App\CPU\translate('تحديث التخصص') }}
        </li>
      </ol>
    </nav>
  </div>

  <div class="row gx-2 gx-lg-3">
    <div class="col-12">
      <div class="card page-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="page-title">{{ \App\CPU\translate('تحديث التخصص') }}</h5>
            <div class="muted">{{ \App\CPU\translate('قم بتعديل بيانات التخصص واحفظ التغييرات') }}</div>
          </div>
        </div>

        <div class="card-body">
          <form action="{{ route('admin.category.update', [$category['id']]) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <!-- الاسم -->
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label" for="catName">{{ \App\CPU\translate('name') }}</label>
                  <input id="catName" type="text" name="name" value="{{ $category['name'] }}"
                         class="form-control" placeholder="{{ \App\CPU\translate('new_category') }}" required>
                  <input name="position" value="0" class="d-none">
                </div>
              </div>

              <!-- الصورة (للجذر فقط) -->
              @if ($category['parent_id'] == 0)
                <div class="col-12 col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="customFileEg1">
                      {{ \App\CPU\translate('image') }}
                      <small class="text-danger">* ({{ \App\CPU\translate('ratio_1:1') }})</small>
                    </label>
                    <div class="custom-file">
                      <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                             accept=".jpg,.png,.jpeg,.gif,.bmp,.tif,.tiff,image/*">
                      <label class="custom-file-label" for="customFileEg1">
                        {{ \App\CPU\translate('choose') }} {{ \App\CPU\translate('file') }}
                      </label>
                    </div>
                    <div class="hint mt-1">{{ \App\CPU\translate('الحد_الأقصى_الموصى_به') }}: 800×800</div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="text-center my-2">
                    <div class="img-frame">
                      <img id="viewer"
                           onerror="this.src='{{ asset('public/assets/admin/img/400x400/img2.jpg') }}'"
                           src="{{ asset('storage/app/public/category') }}/{{ $category['image'] }}"
                           alt="{{ \App\CPU\translate('image') }}">
                    </div>
                  </div>
                </div>
              @endif

              <div class="col-12 mt-2">
                <div class="actions-bar">
                  <button type="submit" class="btn btn-primary col-3">
                    <i class="tio-save"></i> {{ \App\CPU\translate('تحديث') }}
                  </button>
                  <a href="{{ url()->previous() }}" class="btn btn-danger col-3">
                    {{ \App\CPU\translate('عودة') }}
                  </a>
                </div>
              </div>
            </div>
          </form>
        </div> <!-- /card-body -->
      </div>
    </div>
  </div>
</div>
@endsection

@push('script_2')
<script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
<script>
  // معاينة الصورة وتحديث الاسم على الليبل
  document.getElementById('customFileEg1')?.addEventListener('change', function(){
    const file = this.files && this.files[0];
    if(!file) return;
    const reader = new FileReader();
    reader.onload = e => { const img = document.getElementById('viewer'); if(img) img.src = e.target.result; };
    reader.readAsDataURL(file);
    const label = this.nextElementSibling;
    if(label) label.innerText = file.name || label.innerText;
  });
</script>
@endpush
