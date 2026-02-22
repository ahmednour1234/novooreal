@extends('layouts.admin.app')

@section('title', \App\CPU\translate('مراكز التكلفة'))

@section('content')
<style>
    .card-body.expanded-height {
        min-height: 400px; /* عدّلها لو حابب */
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
                    {{ \App\CPU\translate('مراكز التكلفة') }}
                </li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-4">
            {{-- ✅ الشجرة (نفس فكرة شجرة الحسابات بس لمراكز التكلفة) --}}
            @include('admin-views.costcenter.components.tree', [
                'groups' => $costCenterGroups ?? ['all' => 'مراكز التكلفة']
            ])
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body" id="ccCardBodyContainer">
                    {{-- ✅ نماذج الإضافة/التعديل --}}
                    @include('admin-views.costcenter.components._add_sub_cost_center_modal')
                    @include('admin-views.costcenter.components.edit_cost_center_modal')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cardBody = document.getElementById('ccCardBodyContainer');
    const addModal = document.getElementById('subCostCenterFormContainer');
    const editModal = document.getElementById('editCostCenterFormContainer');

    const addVisible  = addModal && addModal.style.display  !== 'none';
    const editVisible = editModal && editModal.style.display !== 'none';

    if (!addVisible && !editVisible) {
        cardBody.classList.add('expanded-height');
    }
});
</script>
@endpush
