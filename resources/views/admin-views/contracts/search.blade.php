    <form method="GET" action="{{ route('admin.contracts.index') }}" class="row g-3 mb-4 align-items-end bg-light p-3 rounded shadow-sm">
      <div class="col-md-3">
        <label class="form-label small text-muted">رقم العقد</label>
        <input type="text" name="contract_number" value="{{ request('contract_number') }}" class="form-control form-control-sm shadow-sm" placeholder="ابحث برقم العقد">
      </div>

      <div class="col-md-3">
        <label class="form-label small text-muted">اسم العميل</label>
        <input type="text" name="client_name" value="{{ request('client_name') }}" class="form-control form-control-sm shadow-sm" placeholder="ابحث باسم العميل">
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted">من تاريخ</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm shadow-sm">
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted">إلى تاريخ</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm shadow-sm">
      </div>

      <div class="col-md-1">
        <label class="form-label small text-muted">الحد الأدنى</label>
        <input type="number" step="0.01" name="min_value" value="{{ request('min_value') }}" class="form-control form-control-sm shadow-sm" placeholder="0.00">
      </div>

      <div class="col-md-1">
        <label class="form-label small text-muted">الحد الأقصى</label>
        <input type="number" step="0.01" name="max_value" value="{{ request('max_value') }}" class="form-control form-control-sm shadow-sm" placeholder="0.00">
      </div>

      <div class="col-md-12 text-end">
        <button type="submit" class="btn btn-sm btn-primary me-2 shadow-sm">
          <i class="bi bi-search me-1"></i> بحث
        </button>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
          <i class="bi bi-arrow-clockwise me-1"></i> إعادة
        </a>
      </div>
    </form>
