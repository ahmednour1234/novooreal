@extends('layouts.admin.app')

@section('title', \App\CPU\translate('category_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
          <div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                    <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('admin.branch.add')}}" class="text-primary">
                    {{ \App\CPU\translate('الفروع') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate('تحديث الفرع') }}
                </a>
            </li>
                  
           
        </ol>
    </nav>
</div>
    <!-- End Page Header -->

    <div class="row gx-2 gx-lg-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.branch.update', [$category['id']]) }}"
                          method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Basic Info -->
                            <div class="col-12 col-sm-6 mb-3">
                                <label for="name" class="input-label">{{ \App\CPU\translate('الاسم') }}</label>
                                <input type="text" name="name" id="name"
                                       value="{{ $category['name'] }}"
                                       class="form-control"
                                       placeholder="{{ \App\CPU\translate('new_category') }}"
                                       required>
                                <input type="hidden" name="position" value="0">
                            </div>

         
                            <!-- Code Field -->
                            <div class="col-12 col-sm-6 mb-3">
                                <label for="code" class="input-label">{{ \App\CPU\translate('الكود') }}</label>
                                <input type="text" name="code" id="code"
                                       value="{{ $category['code'] ?? '' }}"
                                       class="form-control"
                                       placeholder="{{ \App\CPU\translate('ادخل الكود أو سيتم توليده تلقائياً') }}">
                            </div>

                            <!-- Google Map -->
                            <div class="col-12 mb-3">
                                <label class="input-label">{{ \App\CPU\translate('اختر الموقع على الخريطة') }}</label>
                                <div id="map" style="width:100%; height:300px; border:1px solid #ddd;"></div>
                            </div>

                            <!-- Coordinates -->
                            <div class="col-6 mb-3">
                                <label for="longitude" class="input-label">{{ \App\CPU\translate('خط العرض') }}</label>
                                <input type="text" name="lang" id="longitude"
                                       value="{{ $category['lang'] ?? '' }}"
                                       class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="latitude" class="input-label">{{ \App\CPU\translate('خط الطول') }}</label>
                                <input type="text" name="lat" id="latitude"
                                       value="{{ $category['lat'] ?? '' }}"
                                       class="form-control" required>
                            </div>
                        </div>
     <div class="d-flex justify-content-end mt-5">
    <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
        {{ \App\CPU\translate('تحديث') }}
    </button>
</div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    // 1) Auto‑generate code when account changes
    document.addEventListener("DOMContentLoaded", function() {
        const acct = document.getElementById("account_id");
        acct?.addEventListener("change", function() {
            document.getElementById("code").value = this.value
                ? `BR-${this.value}-${Date.now()}`
                : '';
        });
    });

    // 2) Safe initMap: retry on window.load if map container not yet in DOM
    function initMap() {
        const mapEl = document.getElementById("map");
        if (!mapEl) {
            window.addEventListener("load", initMap);
            return;
        }

        // Parse stored coordinates or fall back to Cairo
        const lat = parseFloat("{{ $category['lat'] ?? 30.0444 }}"),
              lng = parseFloat("{{ $category['lang'] ?? 31.2357 }}");
        const defaultLoc = { lat, lng };

        const map = new google.maps.Map(mapEl, {
            center: defaultLoc,
            zoom: 10
        });

        const marker = new google.maps.Marker({
            position: defaultLoc,
            map,
            draggable: true
        });

        marker.addListener("dragend", evt => {
            document.getElementById("latitude").value  = evt.latLng.lat();
            document.getElementById("longitude").value = evt.latLng.lng();
        });
    }
</script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAQgTQ30_TriFBdJPKKOK4zZQ8rfHCUk6c&callback=initMap">
</script>
