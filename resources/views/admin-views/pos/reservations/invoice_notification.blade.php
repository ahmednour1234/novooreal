@extends('layouts.admin.app')

@section('title', 'Reservations List')

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/custom.css') }}"/>
<style>
  .center-button { display: flex; justify-content: center; align-items: center; }
  .line-dot   { border-top: 1px dotted #ccc; margin: 1rem 0; }
  .modal {
    position: fixed; top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none; align-items: center; justify-content: center;
    z-index: 1050;
  }
  .modal-content {
    background: #fff; padding: 1.5rem;
    border-radius: 0.5rem; width: 90%; max-width: 400px;
    position: relative;
  }
  .modal-content .close {
    position: absolute; top: 0.5rem; right: 0.5rem;
    font-size: 1.5rem; cursor: pointer;
  }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('message'))
      <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="reservation-content mb-3">
        <hr class="line-dot">
        <h5>رقم الحجز: {{ $reserveProduct->id }}</h5>
        <h5>اسم المندوب: {{ $reserveProduct->seller->f_name . ' ' . $reserveProduct->seller->l_name }}</h5>
        <h5 class="font-inone fz-10">{{ $reserveProduct->created_at->format('d/M/Y h:i a') }}</h5>
        <hr class="line-dot">

        <form method="POST" action="{{ route('admin.stock.store') }}"
              class="p-4 border rounded bg-light shadow-sm">
            @csrf
            <input type="hidden" name="reservation_id" value="{{ $reserveProduct->id }}">
            <input type="hidden" name="type"           value="{{ $reserveProduct->type }}">
            <input type="hidden" name="user_id"        value="{{ $reserveProduct->customer_id }}">
            <input type="hidden" name="seller_id"      value="{{ $reserveProduct->seller_id }}">

            <div class="center-button mb-3">
                <button type="button" id="add-product" class="btn btn-primary">
                  اختيار منتج
                </button>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered" id="product-table">
                <thead class="thead-dark">
                  <tr>
                    <th>#</th>
                    <th>الوصف</th>
                    <th>كود المنتج</th>
                    <th>الكمية</th>
                    <th>وحدة القياس</th>
                    <th>إجراءات</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach(json_decode($reserveProduct->data) as $d)
                    @php
                      $prod = \App\Models\Product::find($d->product_id);
                      $isDec = floor($d->stock) != $d->stock;
                    @endphp
                    <tr data-id="{{ $prod->id }}">
                      <td class="row-number">1</td>
                      <td>
                        {{ $prod->name }}
                        <input type="hidden" name="product_id[]" value="{{ $prod->id }}">
                      </td>
                      <td>{{ $prod->product_code }}</td>
                      <td>
                        <input type="number"
                               name="stock[]"
                               value="{{ floor($d->stock) }}"
                               step="1"
                               class="form-control form-control-sm quantity"
                               min="0">
                      </td>
                      <td>
                        <select name="unit[]" class="form-select form-select-sm unit-select">
                          <option value="0" {{ $isDec ? 'selected' : '' }}>صغرى</option>
                          <option value="1" {{ $isDec ? '' : 'selected' }}>كبري</option>
                        </select>
                      </td>
                      <td>
                        <button type="button" class="btn btn-danger btn-sm remove-product">
                          حذف
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="mt-4 center-button">
              <button type="submit" class="btn btn-success">تنفيذ امر التوريد</button>
            </div>
        </form>

        <hr class="line-dot">
        <h5>شكراً لك</h5>
        <hr class="line-dot">
    </div>
</div>

{{-- Product Modal --}}
<div id="productModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h5>اختار منتج</h5>
    <input type="text" id="product-search" class="form-control mb-2" placeholder="ابحث باسم أو كود">
    <select id="modal-product-select" class="form-control mb-2">
      <option value="">اختار منتج</option>
      @foreach($products as $p)
        <option value="{{ $p->id }}">
          {{ $p->name }} ({{ $p->product_code }})
        </option>
      @endforeach
    </select>
    <button id="select-product" class="btn btn-primary">اضافة</button>
  </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody   = document.querySelector('#product-table tbody');
  const btnOpen = document.getElementById('add-product');
  const modal   = document.getElementById('productModal');
  const btnClose= modal.querySelector('.close');
  const filter  = document.getElementById('product-search');
  const select  = document.getElementById('modal-product-select');
  const btnAdd  = document.getElementById('select-product');

  // Re-number rows
  function renumber() {
    tbody.querySelectorAll('tr').forEach((tr, i) => {
      tr.querySelector('.row-number').textContent = i + 1;
    });
  }

  // Attach remove & input events
  function attach() {
    tbody.querySelectorAll('.remove-product').forEach(btn => {
      btn.onclick = e => {
        e.target.closest('tr').remove();
        renumber();
      };
    });
  }

  // Modal controls
  btnOpen.onclick  = () => modal.style.display = 'flex';
  btnClose.onclick = () => modal.style.display = 'none';
  window.onclick    = e => { if (e.target === modal) modal.style.display = 'none'; };

  // Filter products in dropdown
  filter.oninput = () => {
    const q = filter.value.toLowerCase();
    select.querySelectorAll('option').forEach(opt => {
      opt.style.display = (!opt.value || opt.text.toLowerCase().includes(q)) ? '' : 'none';
    });
  };

  // Add new product row
  btnAdd.onclick = () => {
    const id   = select.value;
    if (!id || tbody.querySelector(`tr[data-id="${id}"]`)) return;

    const text = select.options[select.selectedIndex].text;
    const idx  = tbody.rows.length;
    const td   = document.createElement('tr');
    td.dataset.id = id;
    td.innerHTML = `
      <td class="row-number">${idx+1}</td>
      <td>
        ${text}
        <input type="hidden" name="product_id[]" value="${id}">
      </td>
      <td>${text.match(/\((.*)\)/)[1]}</td>
      <td>
        <input
          type="number"
          name="stock[]"
          value="0"
          step="1"
          class="form-control form-control-sm quantity"
          min="0"
        >
      </td>
      <td>
        <select name="unit[]" class="form-select form-select-sm unit-select">
          <option value="0">صغرى</option>
          <option value="1" selected>كبري</option>
        </select>
      </td>
      <td>
        <button type="button" class="btn btn-danger btn-sm remove-product">حذف</button>
      </td>
    `;
    tbody.appendChild(td);
    attach();
    modal.style.display = 'none';
  };

  // Initialize
  attach();
  renumber();
});
</script>
