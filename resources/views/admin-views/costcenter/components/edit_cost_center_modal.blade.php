{{-- resources/views/admin-views/costcenter/components/edit_cost_center_modal.blade.php --}}

@php
      $costcentersAll = \App\Models\CostCenter::orderBy('name')->get();

@endphp

<div id="editCostCenterFormContainer" class="mt-1" style="display:none;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0"><i class="tio-edit"></i> تعديل مركز تكلفة</h5>
            <small id="editCostCenterTitleName" class="text-muted d-block mt-1"></small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="hideEditCostCenterForm()">إغلاق</button>
    </div>

    <div class="card-body">
        {{-- ✅ هنبدّل __ID__/PLACEHOLDER_ID بالـ JS --}}
        <form action="{{ route('admin.costcenter.update', ['id' => '__ID__']) }}"
              id="editCostCenterForm" method="POST">
            @csrf

            <input type="hidden" id="edit_cc_id" name="id" value="">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" id="edit_cc_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">الكود</label>
                    <input type="text" name="code" id="edit_cc_code" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">الأب</label>
                    <select name="parent_id" id="edit_cc_parent_id" class="form-control">
                        <option value="">— بدون —</option>
                        @foreach(($costcentersAll ?? []) as $cc)
                            <option value="{{ $cc->id }}">
                                {{ $cc->code ? ($cc->code.' — '.$cc->name) : $cc->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">اتركه فارغًا لو كان جذر.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">نشط؟</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="edit_cc_active" name="active" value="1">
                        <label class="form-check-label" for="edit_cc_active">تفعيل المركز</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" id="edit_cc_note" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2">
                    تحديث
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showEditCostCenterForm(){ document.getElementById('editCostCenterFormContainer').style.display='block'; }
    function hideEditCostCenterForm(){ document.getElementById('editCostCenterFormContainer').style.display='none'; }

    // 🔁 استخدم selectedCost من سكريبت الشجرة بتاعك
    document.getElementById('editCostBtn').addEventListener('click', function(e){
        e.preventDefault();
        if (!window.selectedCost) return alert('اختَر مركز تكلفة أولًا.');

        const cont     = document.getElementById('editCostCenterFormContainer');
        const addCont  = document.getElementById('addCostCenterFormContainer');
        const form     = document.getElementById('editCostCenterForm');

        // بدّل الأكشن
        if (form) {
            let action = form.getAttribute('action');
            action = action.replace('__ID__', selectedCost.id).replace('PLACEHOLDER_ID', selectedCost.id);
            form.setAttribute('action', action);
        }

        // عبّي الحقول
        const idInp   = document.getElementById('edit_cc_id');
        const nameInp = document.getElementById('edit_cc_name');
        const codeInp = document.getElementById('edit_cc_code');
        const noteInp = document.getElementById('edit_cc_note');
        const parentSel = document.getElementById('edit_cc_parent_id');
        const activeCb  = document.getElementById('edit_cc_active');
        const titleName = document.getElementById('editCostCenterTitleName');

        if (idInp)   idInp.value   = selectedCost.id;
        if (nameInp) nameInp.value = selectedCost.name ?? '';
        if (codeInp) codeInp.value = selectedCost.code ?? '';
        if (noteInp) noteInp.value = selectedCost.description ?? selectedCost.note ?? '';
        if (parentSel) parentSel.value = selectedCost.parent_id ? String(selectedCost.parent_id) : '';
        if (activeCb) activeCb.checked = !!(selectedCost.active ?? 1);
        if (titleName) titleName.textContent = selectedCost.name ?? '';

        if (cont) {
            cont.style.display = 'block';
            cont.scrollIntoView({ behavior: 'smooth' });
        }
        if (addCont) addCont.style.display = 'none';
    });
</script>
