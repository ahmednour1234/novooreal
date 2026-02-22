@extends('layouts.admin.app')

@section('title', \App\CPU\translate('استيراد_المنتجات_بالجملة'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('لوحة_التحكم') }}</a>
                </li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{ route('admin.product.list') }}">{{ \App\CPU\translate('المنتجات') }}</a>
                </li>
                <li class="breadcrumb-item">{{ \App\CPU\translate('الاستيراد_بالجملة') }} </li>
            </ol>
        </nav>

        <!-- Content Row -->
        <div class="row">
            <div class="col-12">
                <div class="jumbotron bg-white">
                    <h1 class="display-4">{{ \App\CPU\translate('تعليمات :') }}</h1>
                    <p> {{ \App\CPU\translate('1._قم_بتحميل_ملف_التنسيق_وتعبئته_بالبيانات_الصحيحة') }}.</p>
                    <p>{{ \App\CPU\translate('2._يمكنك_تحميل_ملف_المثال_لتفهم_كيفية_تعبئة_البيانات') }}.</p>
                    <p>{{ \App\CPU\translate('3._بعد_تحميل_الملف_وتعبئته,_قم_برفعه_في_النموذج_أدناه_ثم_قم_بإرساله') }}.</p>
                    <p> {{ \App\CPU\translate('4._بعد_رفع_المنتجات_يجب_عليك_تعديلها_وتحديد_صور_المنتج_والخيارات') }}.</p>
                    <p> {{ \App\CPU\translate('5._يمكنك_الحصول_على_معرفات_الفئة_والفئة_الفرعية_من_قائمة_الفئات,_يرجى_إدخال_المعرفات_الصحيحة') }}.</p>
                </div>
            </div>

            <div class="col-md-12">
                <form class="product-form" action="{{ route('admin.product.bulk-import') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="card mt-2 rest-part">
                        <div class="card-header">
                            <h4>{{ \App\CPU\translate('رفع_ملف_المنتجات') }}</h4>
                            <a href="{{ asset('public/assets/product_bulk_format.xlsx') }}" download=""
                               class="btn btn-secondary">{{ \App\CPU\translate('تحميل_التنسيق') }}</a>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="file" name="products_file">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-footer">
                        <div class="row">
                            <div class="col-md-12 pt-3">
                                <button type="submit" class="btn btn-primary col-12">{{ \App\CPU\translate('إرسال') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')

@endpush
