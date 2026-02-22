@extends('layouts.admin.app')

@section('title', \App\CPU\translate('dashboard'))

@section('content')
    <div class="d-flex justify-content-center align-items-start vh-100 mb-5" style="padding-top:230px; padding-right:150px;">
         <img class="navbar-brand"
                         onerror="this.src='{{ asset('public/assets/admin/img/160x160/logo2.png') }}'"
                         src="{{ asset('public/assets/novoopng.png') }}" style="width:500px;" alt="Logo">
                         </div>
@endsection
