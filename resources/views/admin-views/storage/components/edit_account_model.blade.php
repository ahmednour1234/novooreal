<div id="editAccountFormContainer" class="mt-1" style="display: none;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0"><i class="tio-edit"></i> تعديل الحساب</h5>
            <small id="editAccountName" class="text-muted d-block mt-1"></small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="hideEditAccountForm()">إغلاق</button>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.account.update', ['id' => '__ID__']) }}"
              id="editAccountForm" method="post">
            @csrf

            <input type="hidden" id="edit_account_id" name="id" value="">

            <div class="form-group">
                <label>عنوان الحساب</label>
                <input type="text" name="account" id="edit_account" class="form-control" required>
            </div>

            <div class="form-group">
                <label>وصف الحساب</label>
                <input type="text" name="description" id="edit_description" class="form-control">
            </div>

            <!--<div class="form-group">-->
            <!--    <label>رقم الحساب</label>-->
            <!--    <input type="text" name="account_number" id="edit_account_number" class="form-control" required>-->
            <!--</div>-->

            <!--<div class="form-group d-none" id="edit_type_toggle">-->
            <!--    <label class="me-3"><input type="radio" name="type" value="0"> يظهر للمندوب</label>-->
            <!--    <label><input type="radio" name="type" value="1"> لا يظهر للمندوب</label>-->
            <!--</div>-->

            {{-- ✅ Checkbox لتحديد cost_center --}}
            <div class="form-group mt-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="edit_costCenterCheckbox" name="cost_center" value="1">
                    <label class="form-check-label" for="edit_costCenterCheckbox">يَستخدم مركز تكلفة</label>
                </div>
                <small class="text-muted d-block mt-1">عند التفعيل، سيتم ضبط الحقل cost_center = 1، وإلا سيكون 0.</small>
            </div>

            <div class="d-flex justify-content-end mt-5">
                <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
                    {{ \App\CPU\translate('تحديث') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const editActionTemplate = @json(route('admin.account.update', ['id' => '__ID__']));

    function showEditAccountForm() {
        document.getElementById('editAccountFormContainer').style.display = 'block';
    }
    function hideEditAccountForm() {
        document.getElementById('editAccountFormContainer').style.display = 'none';
    }

    function initEditAccountForm(account) {
        const form  = document.getElementById('editAccountForm');
        const idInp = document.getElementById('edit_account_id');

        form.action = editActionTemplate.replace('__ID__', account.id);

        idInp.value = account.id || '';
        document.getElementById('editAccountName').textContent = account.account || '';
        document.getElementById('edit_account').value = account.account || '';
        document.getElementById('edit_description').value = account.description || '';
        document.getElementById('edit_account_number').value = account.account_number || '';

        // ✅ Checkbox cost_center
        document.getElementById('edit_costCenterCheckbox').checked = account.cost_center == 1;

        showEditAccountForm();
    }
</script>
