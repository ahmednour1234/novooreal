<div id="subAccountFormContainer" class="mt-1" style="display: none;">
    <div class="card-header">
        <h5 class="mb-0">إضافة حساب فرعي</h5>
        <small id="selectedAccountName" class="text-muted d-block mt-1"></small>
    </div>

    <div class="card-body">
        <form id="addSubAccountForm" method="POST" action="{{ route('admin.account.store') }}">
            @csrf

            <input type="hidden" name="storage_id" id="storage_id">
            <input type="hidden" name="parent_id" id="add_parent_id">
            <input type="hidden" name="account_type" id="account_type">
            <input type="hidden" name="balance" value="0">

            <div class="form-group">
                <label>عنوان الحساب</label>
                <input type="text" name="account" class="form-control" required value="{{ old('account') }}">
            </div>

            <div class="form-group">
                <label>وصف الحساب</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}">
            </div>

            <!--<div class="form-group">-->
            <!--    <label>رقم الحساب</label>-->
            <!--    <input type="text" name="account_number" id="generated_account_number" class="form-control" required value="{{ old('account_number') }}">-->
            <!--</div>-->

            <div class="form-group d-none" id="type_toggle">
                <label class="me-3"><input type="radio" name="type" value="0" {{ old('type') === '0' ? 'checked' : '' }}> يظهر للمندوب</label>
                <label><input type="radio" name="type" value="1" {{ old('type') === '1' ? 'checked' : '' }}> لا يظهر للمندوب</label>
            </div>

            {{-- ✅ مجرد تفعيل أو تعطيل cost_center --}}
            <div class="form-group mt-3">
                <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           id="useCostCenter"
                           {{ old('cost_center') ? 'checked' : '' }}>
                    <label class="form-check-label" for="useCostCenter">
                        يستخدم مركز تكلفة
                    </label>
                </div>

                {{-- هذا الحقل هو اللي هيتبعت للقيمة 1 أو 0 --}}
                <input type="hidden" name="cost_center" id="cost_center_hidden" value="{{ old('cost_center', 0) }}">
            </div>

            <div class="d-flex justify-content-end mt-5">
                <button type="submit" class="btn btn-primary px-2 py-2 fs-5 w-25">
                    {{ \App\CPU\translate('حفظ') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function(){
        const cb  = document.getElementById('useCostCenter');
        const hid = document.getElementById('cost_center_hidden');

        function toggleCostCenterFlag() {
            hid.value = cb.checked ? 1 : 0;
        }

        if (cb) {
            cb.addEventListener('change', toggleCostCenterFlag);
            toggleCostCenterFlag();
        }
    })();
</script>
