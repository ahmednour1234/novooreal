{{-- resources/views/cost_centers/components/_add_cost_center_modal.blade.php --}}

<div id="addCostCenterFormContainer" class="mt-3" style="display:none;">
    <div class="card-header bg-white">
        <h5 class="mb-0">➕ إضافة مركز تكلفة</h5>
        <small id="selectedCostCenterName" class="text-muted d-block mt-1"></small>
    </div>

    <div class="card-body">
        <form id="addCostCenterForm" method="POST" action="{{ route('admin.costcenter.store') }}">
            @csrf

            {{-- اختيار المركز الأب (اختياري) --}}
            <div class="mb-3">
                <label class="form-label">المركز الأب (اتركه فارغ ليكون رئيسي)</label>
                <select name="parent_id" id="add_parent_cc_id" class="form-control">
                    <option value="">— لا يوجد (مركز رئيسي) —</option>
                    @foreach(\App\Models\CostCenter::whereNull('parent_id')->orderBy('name')->get() as $parent)
                        <option value="{{ $parent->id }}">
                            {{ $parent->code ? $parent->code . ' - ' : '' }}{{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" id="add_cc_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الكود</label>
                    <input type="text" name="code" id="add_cc_code" class="form-control" placeholder="اختياري">
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" id="add_cc_note" class="form-control" rows="2" placeholder="اختياري"></textarea>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="add_cc_active" checked>
                        <label class="form-check-label" for="add_cc_active">نشط</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-primary px-4">{{ __('حفظ') }}</button>
            </div>
        </form>
    </div>
</div>
