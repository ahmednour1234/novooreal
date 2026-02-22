@php $isEdit = isset($client); @endphp

<div class="row gy-3">
  {{-- الاسم --}}
  <div class="col-md-6">
    <label class="form-label">الاسم <span class="text-danger">*</span></label>
    <input type="text" name="name"
           value="{{ old('name', $isEdit ? $client->name : '') }}"
           class="form-control @error('name') is-invalid @enderror" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- البريد الإلكتروني --}}
  <div class="col-md-6">
    <label class="form-label">البريد الإلكتروني</label>
    <input type="email" name="email"
           value="{{ old('email', $isEdit ? $client->email : '') }}"
           class="form-control @error('email') is-invalid @enderror">
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- الهاتف --}}
  <div class="col-md-6">
    <label class="form-label">الهاتف</label>
    <input type="text" name="phone"
           value="{{ old('phone', $isEdit ? $client->phone : '') }}"
           class="form-control @error('phone') is-invalid @enderror">
    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- اسم الشركة --}}
  <div class="col-md-6">
    <label class="form-label">اسم الشركة</label>
    <input type="text" name="company_name"
           value="{{ old('company_name', $isEdit ? $client->company_name : '') }}"
           class="form-control @error('company_name') is-invalid @enderror">
    @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- العنوان --}}
  <div class="col-md-6">
    <label class="form-label">العنوان</label>
    <textarea name="address" rows="2"
              class="form-control @error('address') is-invalid @enderror">{{ old('address', $isEdit ? $client->address : '') }}</textarea>
    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- الرقم الضريبي --}}
  <div class="col-md-6">
    <label class="form-label">الرقم الضريبي</label>
    <input type="text" name="tax_number"
           value="{{ old('tax_number', $isEdit ? $client->tax_number : '') }}"
           class="form-control @error('tax_number') is-invalid @enderror">
    @error('tax_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- شخص الاتصال --}}
  <div class="col-md-6">
    <label class="form-label">شخص للاتصال به أو معلومات التواصل</label>
    <input type="text" name="contact_person"
           value="{{ old('contact_person', $isEdit ? $client->contact_person : '') }}"
           class="form-control @error('contact_person') is-invalid @enderror">
    @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- ملاحظات --}}
  <div class="col-12">
    <label class="form-label">ملاحظات</label>
    <textarea name="notes" rows="3"
              class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $isEdit ? $client->notes : '') }}</textarea>
    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
</div>
