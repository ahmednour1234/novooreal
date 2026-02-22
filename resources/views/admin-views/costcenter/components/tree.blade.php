{{-- resources/views/cost_centers/tree.blade.php --}}

<style>
.cost-tree-card{background:#fff;border:1px solid #ccc;padding:0;border-radius:6px;max-height:600px;overflow-y:auto;position:relative}
.cost-tree-header-fixed{position:sticky;top:0;background:#fff;padding:10px;border-bottom:1px solid #eee;z-index:10}
.cost-tree-header-buttons{display:flex;gap:10px;margin-bottom:8px}
.cost-tree-header-buttons a{padding:4px 8px;background:#f3f3f3;border-radius:4px;text-decoration:none;font-size:14px;color:#333}
.cost-tree-header-buttons a.disabled{opacity:.5;pointer-events:none}
.search-input{width:100%;padding:6px 10px;padding-top:4px;margin:10px 0;border:1px solid #ddd;border-radius:4px}
.group-header{font-weight:300;padding:10px;cursor:pointer;background:#f7f7f7;border-top:1px solid #eee;display:flex;justify-content:space-between;align-items:center}
.toggle-btn{cursor:pointer;font-weight:bold}
.cost-list{list-style:none;margin:0;padding-right:0;position:relative}
.cost-list li{padding:6px 20px 6px 8px;margin:2px 0;position:relative;border-radius:4px;text-align:right;font-size:12px}
.cost-list li.selected{font-weight:bold;background:#f8fafc}
.cost-list li::before{content:"";position:absolute;top:0;bottom:0;right:10px;width:.5px;background-color:#000}
.text-muted{color:#999;padding:10px;font-style:italic}
.search-tree{padding:10px;border-bottom:1px solid #eee}
</style>

<div class="cost-tree-card">
    <div class="cost-tree-header-fixed">
        <div class="cost-tree-header-buttons">
            <a href="#" id="addCostBtn">➕ إضافة</a>
            <a href="#" id="editCostBtn" class="disabled">✏️ تعديل</a>
            <a href="#" onclick="location.reload()">🔄 تحديث</a>
            {{-- ✅ تم إزالة زر كشف المركز حسب طلبك --}}
        </div>
        <input type="text" id="costSearchInput" class="search-input" placeholder="🔍 ابحث باسم/كود/وصف مركز التكلفة…">
    </div>

    <div id="searchCostTree" class="search-tree" style="display:none;"></div>

    @php
        // لو عندك مجموعات لمراكز التكلفة مرر مصفوفة: ['projects' => 'مشروعات', 'departments'=>'إدارات', ...]
        $groups = $costCenterGroups ?? ['all' => 'مراكز التكلفة'];
    @endphp

    @foreach($groups as $key => $label)
        <div class="group-header" onclick="toggleCostCenters('{{ $key }}')">
            <span>{{ $label }}</span>
            <span id="toggle-{{ $key }}" class="toggle-btn">+</span>
        </div>
        <ul class="cost-list" id="list-{{ $key }}"></ul>
    @endforeach
</div>

@push('script')
<script>
(function(){
    let selectedCost = null;

    // Helpers
    function normalizeCostCenter(cc){
        // وصف موحّد
        cc.description = (cc.description ?? cc.note ?? cc.desc ?? '');
        // Active موحّد (1/0)
        if ('active' in cc) {
            cc.active = Number(cc.active);
        } else if ('status' in cc) {
            cc.active = Number(cc.status); // لو status=1 يعني active
        } else {
            cc.active = 1; // fallback
        }
        return cc;
    }

    function updateCostToolbar() {
        const canAct = !!(selectedCost && selectedCost.id);
        const editBtn = document.getElementById('editCostBtn');
        if (editBtn) editBtn.classList.toggle('disabled', !canAct);
    }

    function highlightCostSelected(li) {
        document.querySelectorAll('.cost-list li.selected').forEach(el => el.classList.remove('selected'));
        if (li) li.classList.add('selected');
        updateCostToolbar();
    }

    // تحميل جذور/أبناء
    window.toggleCostCenters = function(key, isParent = false) {
        const list = document.getElementById('list-' + key);
        const toggle = document.getElementById('toggle-' + key);
        if (!list || !toggle) return;

        if (list.style.display === 'block') {
            list.style.display = 'none';
            toggle.textContent = '+';
            return;
        }

        const url = "{{ route('admin.costcenter.fetch') }}" + "?" + (isParent ? ('parent_id=' + encodeURIComponent(key)) : ('group=' + encodeURIComponent(key)));

        fetch(url)
            .then(r => r.json())
            .then(items => {
                list.innerHTML = '';
                if (!Array.isArray(items) || items.length === 0) {
                    list.innerHTML = `<li class="text-muted">لا يوجد مراكز.</li>`;
                } else {
                    items.forEach(raw => {
                        const cc = normalizeCostCenter(raw);

                        const li = document.createElement('li');

                        const text = document.createElement('span');
                        text.textContent = `${cc.name} ${cc.code ? '('+cc.code+')' : ''}`;
                        text.title = cc.description || ''; // Tooltip بالوصف
                        text.style.cursor = 'pointer';
                        text.addEventListener('click', (e) => {
                            e.stopPropagation();
                            selectedCost = cc;
                            highlightCostSelected(li);
                        });

                        const tgl = document.createElement('span');
                        tgl.id = `toggle-${cc.id}`;
                        tgl.className = 'toggle-btn';
                        tgl.textContent = '+';
                        tgl.style.float = 'left';
                        tgl.addEventListener('click', (e)=>{
                            e.stopPropagation();
                            toggleCostCenters(cc.id, true);
                        });

                        const childUl = document.createElement('ul');
                        childUl.id = `list-${cc.id}`;
                        childUl.className = 'cost-list';
                        childUl.style.display = 'none';

                        li.appendChild(text);
                        li.appendChild(tgl);
                        li.appendChild(childUl);
                        list.appendChild(li);
                    });
                }
                list.style.display = 'block';
                toggle.textContent = '-';
            })
            .catch(err => {
                console.warn('fetch cost-centers error:', err);
            });
    }

    // البحث
    const searchInput = document.getElementById('costSearchInput');
    const searchBox   = document.getElementById('searchCostTree');
    if (searchInput) {
        searchInput.addEventListener('input', function(){
            const term = this.value.trim();
            if (!term) {
                if (searchBox){ searchBox.innerHTML = ''; searchBox.style.display = 'none'; }
                return;
            }
            if (term.length < 2) return;

            fetch(`{{ route('admin.costcenter.search') }}?q=${encodeURIComponent(term)}`)
                .then(r=>r.json())
                .then(items=>{
                    if (!searchBox) return;
                    searchBox.innerHTML = '';
                    const ul = document.createElement('ul');
                    ul.className = 'cost-list';
                    ul.style.display = 'block';

                    if (!Array.isArray(items) || items.length === 0) {
                        searchBox.innerHTML = '<p class="text-muted">لا يوجد نتائج.</p>';
                    } else {
                        items.forEach(raw=>{
                            const cc = normalizeCostCenter(raw);
                            const li = document.createElement('li');
                            li.textContent = `${cc.name} ${cc.code ? '('+cc.code+')' : ''}`;
                            li.title = cc.description || '';
                            li.style.cursor = 'pointer';
                            li.addEventListener('click', ()=>{
                                selectedCost = cc;
                                highlightCostSelected(li);
                            });
                            ul.appendChild(li);
                        });
                        searchBox.appendChild(ul);
                    }
                    searchBox.style.display = 'block';
                })
                .catch(err => console.warn('search cost-centers error:', err));
        });
    }

    // ✅ إضافة: مسموح تضيف حتى لو ما اخترتش مركز (هيكون رئيسي لو سبت الأب فاضي)
    const addBtn = document.getElementById('addCostBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function(e){
            e.preventDefault();

            const cont  = document.getElementById('addCostCenterFormContainer');
            const pid   = document.getElementById('add_parent_cc_id');
            const label = document.getElementById('selectedCostCenterName');

            // لو مختار مركز، بنملاه كأب افتراضي — لو مش مختار نسيبه فاضي (رئيسي)
            if (pid)   pid.value = selectedCost ? selectedCost.id : '';
            if (label) label.textContent = selectedCost
                ? `المركز المختار: ${selectedCost.name} ${selectedCost.code ? '('+selectedCost.code+')' : ''}`
                : 'ستُضيف مركزًا رئيسيًا (يمكنك اختيار أب من القائمة داخل النموذج).';

            if (cont) {
                cont.style.display = 'block';
                cont.scrollIntoView({ behavior: 'smooth' });
            }
            const editCont = document.getElementById('editCostCenterFormContainer');
            if (editCont) editCont.style.display = 'none';
        });
    }

    // ✅ تعديل: لازم يكون في مركز مختار — وبنخلي اختيار الأب REQUIRED
    const editBtn = document.getElementById('editCostBtn');
    if (editBtn) {
        editBtn.addEventListener('click', function(e){
            e.preventDefault();
            if (!selectedCost) return alert('اختَر مركز تكلفة أولًا.');

            // عناصر فورم التعديل (لازم تكون متضمنة في الصفحة)
            const cont     = document.getElementById('editCostCenterFormContainer');
            const addCont  = document.getElementById('addCostCenterFormContainer');
            const form     = document.getElementById('editCostCenterForm');
            const idInp    = document.getElementById('edit_cc_id');
            const nameInp  = document.getElementById('edit_cc_name');
            const codeInp  = document.getElementById('edit_cc_code');
            const noteInp  = document.getElementById('edit_cc_note'); // الوصف
            const parentSel= document.getElementById('edit_cc_parent_id');
            const activeCb = document.getElementById('edit_cc_active'); // الحالة (اختياري)
            const titleName= document.getElementById('editCostCenterTitleName');

            if (!cont || !form) {
                console.warn('Edit container or form not found. Ensure you included the edit partial with proper IDs.');
                alert('نموذج تعديل المركز غير موجود في الصفحة. تأكد من تضمين partial التعديل.');
                return;
            }

            // بدّل Action: __ID__/PLACEHOLDER_ID
            let action = form.getAttribute('action') || '';
            action = action.replace('__ID__', selectedCost.id).replace('PLACEHOLDER_ID', selectedCost.id);
            form.setAttribute('action', action);

            // املأ البيانات
            if (idInp)    idInp.value   = selectedCost.id;
            if (nameInp)  nameInp.value = selectedCost.name ?? '';
            if (codeInp)  codeInp.value = selectedCost.code ?? '';
            if (noteInp)  noteInp.value = selectedCost.description ?? '';
            if (parentSel){
                parentSel.value = selectedCost.parent_id ? String(selectedCost.parent_id) : '';
                parentSel.setAttribute('required','required'); // ✅ الأب إجباري في التعديل فقط
            }
            if (activeCb) activeCb.checked = !!Number(selectedCost.active);
            if (titleName) titleName.textContent = selectedCost.name ?? '';

            // عرض/إخفاء
            cont.style.display = 'block';
            cont.scrollIntoView({ behavior: 'smooth' });
            if (addCont) addCont.style.display = 'none';
        });
    }
})();
</script>
@endpush
