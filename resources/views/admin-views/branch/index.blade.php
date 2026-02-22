@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_branch'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}" />
@endpush
<Style>
    table.dataTable.no-footer {
    border-bottom: 1px solid #ffffff;
}
</Style>
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
                <a href="#" class="text-primary">
                    {{ \App\CPU\translate('الفروع') }}
                </a>
            </li>
                  
           
        </ol>
    </nav>
</div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <!-- Add Branch Form -->
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.branch.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Branch Name -->
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="name" class="form-label">{{ \App\CPU\translate('اسم الفرع') }}</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                           placeholder="{{ \App\CPU\translate('add_branch_name') }}" required>
                                </div>
<div class="col-12 col-md-6 mb-3">
                                    <label for="code" class="form-label">{{ \App\CPU\translate('الكود') }}</label>
                                    <input type="text" name="code" id="code" class="form-control"
                                           placeholder="{{ \App\CPU\translate('ادخل الكود أو سيتم توليده تلقائياً') }}">
                                </div>
                                <input type="hidden" name="active" value="1">

                                <!-- Map -->
                                <div class="col-12 mb-3">
                                    <div id="map" style="width:100%; height:300px; border:1px solid #ddd;"></div>
                                    <input type="hidden" id="latitude" name="lat">
                                    <input type="hidden" id="longitude" name="lang">
                                </div>

                                <!-- Account Selector -->
                        

                                <!-- Code -->
                                
                            </div>

<div class="d-flex justify-content-end mt-5">
    <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
        {{ \App\CPU\translate('حفظ') }}
    </button>
</div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Branches Table -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="w-100">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-6 col-lg-7 col-xl-8">
                             


                                </div>
                                <div class=" col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                                    <!-- Search -->
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="tio-search"></i>
                                                </div>
                                            </div>
                                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                                   placeholder="{{ \App\CPU\translate('بحث باسم الفرع') }}"
                                                   aria-label="Search orders" value="{{ $search }}">
                                            <button type="submit"
                                                    class="btn btn-primary">{{ \App\CPU\translate('بحث') }}</button>
                                        </div>
                                    </form>
                                    <!-- End Search -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id=""
                            class="table  table-nowrap ">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ \App\CPU\translate('الاسم') }}</th>
                                    <th>{{ \App\CPU\translate('الحالة') }}</th>
                                    <th>{{ \App\CPU\translate('اجراءات') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $key => $branch)
                                    <tr>
                                        <td>{{ $categories->firstItem() + $key }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td>
                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox"
                                                       class="toggle-switch-input"
                                                       onclick="location.href='{{ route('admin.branch.status', [$branch->id, $branch->active ? 0 : 1]) }}'"
                                                       {{ $branch->active ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.branch.edit', $branch->id) }}"
                                               class="btn btn-white">
                                                <i class="tio-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4">
                                            <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}"
                                                 class="mb-3 w-100px" alt="No data">
                                            <p>{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    // 1) Auto‑generate branch code
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
        const defaultLoc = { lat: 30.0444, lng: 31.2357 };
        const map = new google.maps.Map(mapEl, {
            center: defaultLoc,
            zoom: 10
        });
        const marker = new google.maps.Marker({
            position: defaultLoc,
            map: map,
            draggable: true
        });
        marker.addListener("dragend", evt => {
            document.getElementById("latitude").value  = evt.latLng.lat();
            document.getElementById("longitude").value = evt.latLng.lng();
        });
    }

    // 3) Explicit DataTables init (only if plugin is loaded)
    document.addEventListener("DOMContentLoaded", function() {
        const table = document.getElementById("branches-table");
        if (table && window.jQuery && jQuery.fn.DataTable) {
            jQuery(table).DataTable({
                paging:   false,
                searching:false,
                info:     false,
                ordering: false
            });
        }
    });
</script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAQgTQ30_TriFBdJPKKOK4zZQ8rfHCUk6c&callback=initMap">
</script>
