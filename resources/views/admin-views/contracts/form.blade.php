@php $isEdit = isset($contract); @endphp

<div class="row g-4">

  {{-- رقم العقد --}}
  <div class="col-md-6">
    <label for="contract_number" class="form-label fw-semibold">رقم العقد <span class="text-danger">*</span></label>
    <input type="text"
           name="contract_number"
           id="contract_number"
           class="form-control shadow-sm @error('contract_number') is-invalid @enderror"
           value="{{ old('contract_number', $isEdit ? $contract->contract_number : '') }}"
           required>
    @error('contract_number')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- العميل --}}
  <div class="col-md-6">
    <label for="client_id" class="form-label fw-semibold">العميل <span class="text-danger">*</span></label>
    <select name="client_id"
            id="client_id"
            class="form-select shadow-sm select2 @error('client_id') is-invalid @enderror"
            required>
      <option value="">اختر العميل</option>
      @foreach($clients as $cli)
        <option value="{{ $cli->id }}" {{ old('client_id', $isEdit ? $contract->client_id : '') == $cli->id ? 'selected' : '' }}>
          {{ $cli->name }}
        </option>
      @endforeach
    </select>
    @error('client_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- عنوان العقد --}}
  <div class="col-md-6">
    <label for="title" class="form-label fw-semibold">عنوان العقد <span class="text-danger">*</span></label>
    <input type="text"
           name="title"
           id="title"
           class="form-control shadow-sm @error('title') is-invalid @enderror"
           value="{{ old('title', $isEdit ? $contract->title : '') }}"
           required>
    @error('title')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- القيمة الإجمالية --}}
  <div class="col-md-6">
    <label for="total_value" class="form-label fw-semibold">القيمة الإجمالية <span class="text-danger">*</span></label>
    <input type="number"
           step="0.01"
           name="total_value"
           id="total_value"
           class="form-control shadow-sm @error('total_value') is-invalid @enderror"
           value="{{ old('total_value', $isEdit ? $contract->total_value : '') }}"
           required>
    @error('total_value')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- تاريخ البداية --}}
  <div class="col-md-3">
    <label for="start_date" class="form-label fw-semibold">تاريخ البداية <span class="text-danger">*</span></label>
    <input type="date"
           name="start_date"
           id="start_date"
           class="form-control shadow-sm @error('start_date') is-invalid @enderror"
           value="{{ old('start_date', $isEdit ? $contract->start_date : '') }}"
           required>
    @error('start_date')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- تاريخ النهاية --}}
  <div class="col-md-3">
    <label for="end_date" class="form-label fw-semibold">تاريخ النهاية</label>
    <input type="date"
           name="end_date"
           id="end_date"
           class="form-control shadow-sm @error('end_date') is-invalid @enderror"
           value="{{ old('end_date', $isEdit && $contract->end_date ? $contract->end_date : '') }}">
    @error('end_date')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- الحالة --}}
  <div class="col-md-3">
    <label for="status" class="form-label fw-semibold">الحالة <span class="text-danger">*</span></label>
    <select name="status"
            id="status"
            class="form-select shadow-sm select2 @error('status') is-invalid @enderror"
            required>
      @foreach(['draft' => 'مسودة', 'active' => 'نشط', 'completed' => 'مكتمل', 'canceled' => 'ملغى'] as $val => $label)
        <option value="{{ $val }}" {{ old('status', $isEdit ? $contract->status : 'draft') == $val ? 'selected' : '' }}>
          {{ $label }}
        </option>
      @endforeach
    </select>
    @error('status')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- الوصف --}}
  <div class="col-12">
    <label for="description" class="form-label fw-semibold">الوصف</label>
    <textarea name="description"
              id="description"
              rows="3"
              class="form-control shadow-sm @error('description') is-invalid @enderror"
              placeholder="تفاصيل إضافية عن العقد...">{{ old('description', $isEdit ? $contract->description : '') }}</textarea>
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>

