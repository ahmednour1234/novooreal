{{-- resources/views/products/select_type.blade.php --}}
@extends('layouts.admin.app')

@section('content')
<div class="content container-fluid">
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
                        {{ \App\CPU\translate(' اختر نوع المنتج') }}
                    </a>
                </li>
            </ol>
        </nav>
    </div>
  <div class="border-0 rounded-3 mt-5 pt-5">
        <div class="row justify-content-center mt-5 g-4">

            {{-- بطاقة بيع المنتجات --}}
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('admin.product.add', 'product') }}"
                   class="card  text-center text-decoration-none shadow-sm transition-all hover-shadow-lg"
                   style="min-height: 280px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="mb-3">
                            <img src="{{ asset('public/google.png') }}" alt="فاتورة بيع منتجات"
                                 style="max-width:80px; max-height:80px;">
                        </div>
                        <h5 class="card-title fw-bold text-dark">{{ __('إضافة منتج يحتوي علي مخزون') }}</h5>
                        <p class="text-muted small">{{ __('إضافة منتج يحتوي علي مخزون') }}</p>
                    </div>
                </a>
            </div>

            {{-- بطاقة بيع الخدمات --}}
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('admin.product.add', 'service') }}"
                   class="card  text-center text-decoration-none shadow-sm transition-all hover-shadow-lg"
                   style="min-height: 280px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="mb-3">
                            <img src="{{ asset('public/technical-support.png') }}" alt="فاتورة بيع خدمات"
                                 style="max-width:80px; max-height:80px;">
                        </div>
                        <h5 class="card-title fw-bold text-dark">{{ __('إضافة خدمة ليس لها مخزون') }}</h5>
                        <p class="text-muted small">{{ __('إنشاء خدمة ليس لها مخزون') }}</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center mt-5">
            {{ session('success') }}
        </div>
    @endif
</div>
@endsection
