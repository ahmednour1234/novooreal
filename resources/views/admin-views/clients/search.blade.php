<div class="card mb-4 border-0">
  <div class="card-body p-3" style="background: #f8f9f9;">
    <form method="GET" action="{{ route('admin.clients.index') }}">
      <div class="row gx-2 gy-2 align-items-center">

        {{-- الاسم --}}
        <div class="col-md">
          <input type="text"
                 name="name"
                 class="form-control form-control-sm border-bottom"
                 placeholder="الاسم"
                 value="{{ request('name') }}"
                 style="border-left:0;border-top:0;border-right:0;border-radius:0;">
        </div>

        {{-- البريد الإلكتروني --}}
        <div class="col-md">
          <input type="email"
                 name="email"
                 class="form-control form-control-sm border-bottom"
                 placeholder="البريد الإلكتروني"
                 value="{{ request('email') }}"
                 style="border-left:0;border-top:0;border-right:0;border-radius:0;">
        </div>

        {{-- الهاتف --}}
        <div class="col-md">
          <input type="text"
                 name="phone"
                 class="form-control form-control-sm border-bottom"
                 placeholder="الهاتف"
                 value="{{ request('phone') }}"
                 style="border-left:0;border-top:0;border-right:0;border-radius:0;">
        </div>

        {{-- اسم الشركة --}}
        <div class="col-md">
          <input type="text"
                 name="company_name"
                 class="form-control form-control-sm border-bottom"
                 placeholder="اسم الشركة"
                 value="{{ request('company_name') }}"
                 style="border-left:0;border-top:0;border-right:0;border-radius:0;">
        </div>

        {{-- الحالة --}}
        <div class="col-md-2">
          <select name="active"
                  class="form-select form-select-sm border-bottom"
                  style="border-left:0;border-top:0;border-right:0;border-radius:0;">
            <option value="">كل الحالات</option>
            <option value="1" {{ request('active')==='1' ? 'selected':'' }}>مفعل</option>
            <option value="0" {{ request('active')==='0' ? 'selected':'' }}>غير مفعل</option>
          </select>
        </div>

      </div>

      {{-- الأزرار الكبيرة --}}
      <div class="row mt-3">
        {{-- زر البحث --}}
        <div class="col-md-6 mb-2">
          <button type="submit"
                  class="btn btn-primary btn-lg w-100">
            <i class="bi bi-search me-1"></i> بحث
          </button>
        </div>
        {{-- زر إعادة --}}
        <div class="col-md-6 mb-2">
          <a href="{{ route('admin.clients.index') }}"
             class="btn btn-outline-secondary btn-lg w-100">
            <i class="bi bi-arrow-clockwise me-1"></i> إعادة
          </a>
        </div>
      </div>
    </form>
  </div>
</div>
