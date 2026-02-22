@extends('layouts.admin.app')

@section('title', \App\CPU\translate('اضافة تخصص جديد'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css" />
<style>
  /* تحسينات شكلية بسيطة بدون تدرّجات */
  .page-card { border:1px solid #e9edf5; border-radius:14px; }
  .page-card .card-header { background:#f8fafc; border-bottom:1px solid #e9edf5; }
  .soft-badge { background:#f3f6fa; border:1px solid #e7ecf2; color:#3a3f45; border-radius:999px; padding:.35rem .6rem; font-size:.75rem; }
  .img-preview { border:1px solid #e9edf5; border-radius:12px; max-width:220px; height:auto; }
  .table thead th { background:#f5f7fa; border-bottom:1px solid #e9edf5 !important; }
  .table tbody tr:hover { background:#fcfdff; }
  .toggle-switch-input { cursor:pointer; }
  .btn-icon { padding:.25rem .5rem; }
  .thumb-40 { width:40px; height:40px; object-fit:cover; border-radius:8px; border:1px solid #e9edf5; }
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
          {{ \App\CPU\translate('التخصصات') }}
        </li>
      </ol>
    </nav>
  </div>

  <div class="row gx-2 gx-lg-3">
    <!-- إضافة تخصص -->
    <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
      <div class="card page-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">{{ \App\CPU\translate('اضافة تخصص جديد') }}</h5>
          <span class="soft-badge"><i class="tio-plus"></i> {{ \App\CPU\translate('جديد') }}</span>
        </div>

        <div class="card-body">
          <form action="{{ route('admin.category.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="mb-1">{{ \App\CPU\translate('اسم التخصص') }}</label>
                  <input type="text" name="name" class="form-control" required
                         placeholder="{{ \App\CPU\translate('add_category_name') }}">
                  <input name="position" value="0" class="d-none">
                  <input type="hidden" name="type" value="0">
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                  <label class="mb-1">{{ \App\CPU\translate('صورة') }}</label>
                  <small class="text-danger">* ({{ \App\CPU\translate('ratio_1:1') }})</small>
                  <div class="custom-file">
                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                           accept=".jpg,.png,.jpeg,.gif,.bmp,.tif,.tiff,image/*">
                    <label class="custom-file-label" for="customFileEg1">{{ \App\CPU\translate('اختار ملف') }}</label>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="form-group text-center">
                  <img class="img-preview" id="viewer"
                       src="{{ asset('public/assets/admin/img/400x400/img2.jpg') }}"
                       alt="{{ \App\CPU\translate('صورة') }}">
                </div>
              </div>

              <div class="col-12">
                <!-- زر صغير عالشمال -->
                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary col-4">
             {{ \App\CPU\translate('حفظ') }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div> <!--/card-body-->
      </div>
    </div>

    <!-- جدول التخصصات -->
    <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
      <div class="card page-card">
        <div class="card-header">
          <div class="w-100">
            <div class="row align-items-center">
              <div class="col-12 col-md-7">
                <h5 class="mb-0">
                  {{ \App\CPU\translate('التخصصات') }}
                  <span class="soft-badge">{{$categories->total()}}</span>
                </h5>
              </div>
              <div class="col-12 col-md-5 mt-2 mt-md-0">
                <!-- Search -->
                <form action="{{ url()->current() }}" method="GET">
                  <div class="input-group input-group-merge input-group-flush">
                    <div class="input-group-prepend">
                      <div class="input-group-text"><i class="tio-search"></i></div>
                    </div>
                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                           placeholder="{{ \App\CPU\translate('بحث بالتخصص') }}"
                           aria-label="Search categories" value="{{ $search }}" required>
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-primary btn-sm">
                        {{ \App\CPU\translate('بحث') }}
                      </button>
                      @if(request()->has('search'))
                        <a href="{{ url()->current() }}" class="btn btn-light btn-sm">
                          {{ \App\CPU\translate('إلغاء البحث') }}
                        </a>
                      @endif
                    </div>
                  </div>
                </form>
                <!-- End Search -->
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th style="width:60px">{{ \App\CPU\translate('#') }}</th>
                <th style="width:70px">{{ \App\CPU\translate('صورة') }}</th>
                <th>{{ \App\CPU\translate('اسم التخصص') }}</th>
                <th style="width:120px">{{ \App\CPU\translate('نشط') }}</th>
                <th style="width:140px">{{ \App\CPU\translate('الاجراءات') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($categories as $key => $category)
                <tr>
                  <td>{{ $categories->firstitem() + $key }}</td>
                  <td>
                    <img src="{{ asset('storage/app/public/category') }}/{{ $category['image'] }}"
                         class="thumb-40"
                         onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'">
                  </td>
                  <td>
                    <div class="d-flex flex-column">
                      <span class="font-weight-bold">{{ $category['name'] }}</span>
                      @if(! $category->status)
                        <small class="text-muted">{{ \App\CPU\translate('غير نشط') }}</small>
                      @endif
                    </div>
                  </td>
                  <td>
                    <label class="toggle-switch toggle-switch-sm mb-0" title="{{ \App\CPU\translate('تغيير الحالة') }}">
                      <input type="checkbox" class="toggle-switch-input"
                             onclick="location.href='{{ route('admin.category.status', [$category['id'], $category->status ? 0 : 1]) }}'"
                             {{ $category->status ? 'checked' : '' }}>
                      <span class="toggle-switch-label">
                        <span class="toggle-switch-indicator"></span>
                      </span>
                    </label>
                  </td>
                  <td class="text-left">
                    <a class="btn btn-white btn-icon btn-sm mr-1"
                       href="{{ route('admin.category.edit', [$category['id']]) }}"
                       title="{{ \App\CPU\translate('تعديل') }}">
                      <span class="tio-edit"></span>
                    </a>
                    <a class="btn btn-white btn-icon btn-sm"
                       href="javascript:"
                       title="{{ \App\CPU\translate('حذف') }}"
                       onclick="form_alert('category-{{ $category['id'] }}','{{ \App\CPU\translate('Want to delete this category?') }}')">
                      <span class="tio-delete"></span>
                    </a>
                    <form action="{{ route('admin.category.delete', [$category['id']]) }}"
                          method="post" id="category-{{ $category['id'] }}">
                      @csrf @method('delete')
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <hr class="my-2">
          <div class="px-3 pb-3">
            {!! $categories->links() !!}
          </div>

          @if ($categories->count() == 0)
            <div class="text-center p-4">
              <img class="mb-3" style="max-width:220px"
                   src="{{ asset('public/assets/admin') }}/svg/illustrations/sorry.svg"
                   alt="Image Description">
              <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>
    <!-- End Table -->
  </div>
</div>
@endsection

@push('script_2')
<script src={{ asset('public/assets/admin/js/global.js') }}></script>
<script>
  // معاينة الصورة وتحديث اسم الملف
  document.getElementById('customFileEg1')?.addEventListener('change', function (e) {
    const file = this.files && this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev){
      const img = document.getElementById('viewer');
      img && (img.src = ev.target.result);
    };
    reader.readAsDataURL(file);

    const label = this.nextElementSibling;
    if (label && file.name) { label.innerText = file.name; }
  });
</script>
@endpush
