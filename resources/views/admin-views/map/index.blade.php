@extends('layouts.admin.app')

@section('title', 'Admin Locations')

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title text-capitalize">تتبع المناديب</h1>
        </div>

        <div id="map" style="width:100%; height:500px; border:1px solid #ddd;"></div>
    </div>
@endsection

@php
    // Pre-format the admins data for JavaScript
    $adminLocations = $admins->map(fn($a) => [
        'name'      => trim("{$a->f_name} {$a->l_name}"),
        'latitude'  => (float) $a->latitude,
        'longitude' => (float) $a->longitude,
    ]);
@endphp

<script>
    const OFFSET = 0.0001;

    function initMap() {
        const mapEl = document.getElementById('map');
        if (!mapEl) {
            window.addEventListener('load', initMap);
            return;
        }

        const map = new google.maps.Map(mapEl, {
            center: { lat: 30.0444, lng: 31.2357 },
            zoom: 10
        });

        const admins = @json($adminLocations);
        const used = new Set();

        admins.forEach(a => {
            let lat = a.latitude;
            let lng = a.longitude;
            if (!lat || !lng) return;

            const key = `${lat.toFixed(6)},${lng.toFixed(6)}`;
            if (used.has(key)) {
                lat += (Math.random() - 0.5) * OFFSET;
                lng += (Math.random() - 0.5) * OFFSET;
            }
            used.add(key);

            const marker = new google.maps.Marker({
                map,
                position: { lat, lng },
                title: a.name
            });

            const info = new google.maps.InfoWindow({
                content: `<strong>${a.name}</strong><br>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`
            });

            marker.addListener('click', () => info.open(map, marker));
        });
    }

    window.initMap = initMap;
</script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAQgTQ30_TriFBdJPKKOK4zZQ8rfHCUk6c&callback=initMap">
</script>
