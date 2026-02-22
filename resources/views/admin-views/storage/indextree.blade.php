@extends('layouts.admin.app')

@section('title', 'Dashboard')

@section('content')
<style>
    .card-body.expanded-height {
        min-height: 400px; /* أو أي قيمة تراها مناسبة */
    }
</style>

    <div class="content container-fluid">
        {{-- 🔷 المسار الملاحي --}}
        <div class="mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">
                        {{ \App\CPU\translate('شجرة الحسابات') }}
                    </li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-4">
                @include('admin-views.storage.components.account_tree', ['accountTypes' => $accountTypes])
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
<div class="card-body" id="cardBodyContainer">

    @include('admin-views.storage.components._add_sub_account_modal')
    @include('admin-views.storage.components.edit_account_model')
</div>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cardBody = document.getElementById('cardBodyContainer');
        const addModal = document.getElementById('subAccountFormContainer');
        const editModal = document.getElementById('editAccountFormContainer');

        const addVisible = addModal && addModal.style.display !== 'none';
        const editVisible = editModal && editModal.style.display !== 'none';

        if (!addVisible && !editVisible) {
            cardBody.classList.add('expanded-height');
        }
    });
</script>
@endpush
