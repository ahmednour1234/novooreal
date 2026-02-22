<div id="prod-{{ $product->id }}" class="position-relative">
  <input type="hidden" id="product_id" name="id" value="{{ $product->id }}">
  <input type="hidden" id="product_qty" name="quantity" value="1">

  @php
    $unitValue = max(1, (float)($product->unit_value ?? 1));
    // احسب السعر النهائي المعروض حسب نوع الحركة
    if ($type == 4 || $type == 7) {
        $finalPrice = ($product['selling_price'] - \App\CPU\Helpers::discount_calculate($product, $product['selling_price']));
    } elseif ($type == 1) {
        $finalPrice = ($product['selling_price'] / $unitValue) - \App\CPU\Helpers::discount_calculate($product, $product['selling_price'] / $unitValue);
    } else {
        $finalPrice = ($product['purchase_price'] - \App\CPU\Helpers::discount_calculate($product, $product['purchase_price']));
    }
    $hasDiscount = (float)($product->discount ?? 0) > 0;
    $currency = \App\CPU\Helpers::currency_symbol();
  @endphp

  <div class="pos-product-item card shadow-sm border-0 overflow-hidden rounded-lg"
       role="button"
       tabindex="0"
       aria-label="Add {{ $product['name'] }} to cart"
       onclick="addToCart({{ $product->id }}, '{{ $type }}')"
       onkeydown="if(event.key==='Enter'){addToCart({{ $product->id }}, '{{ $type }}')}">

    <div class="pos-product-item_thumb position-relative">
      <img src="{{ asset('storage/app/public/product') }}/{{ $product['image'] }}"
           onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'"
           class="img-fluid product-thumb" alt="{{ $product['name'] }}">

      @if($hasDiscount)
        <span class="discount-badge">-{{ (float)$product->discount }}%</span>
      @endif>

      <button type="button"
              onclick="event.stopPropagation(); showallProductDetails({{ $product->id }})"
              class="btn btn-sm btn-light border rounded-circle position-absolute top-0 end-0 m-2 shadow-sm info-btn"
              title="{{ \App\CPU\translate('تفاصيل المنتج') }}">
        <i class="tio-info"></i>
      </button>
    </div>

    <div class="pos-product-item_content p-3">
      <div class="pos-product-item_title fw-bold text-dark mb-1 text-truncate" title="{{ $product['name'] }}">
        {{ $product['name'] }}
      </div>
      <div class="fz-12 text-muted mb-2">
        {{ \App\CPU\translate('code') }}: <span class="text-monospace">{{ $product['product_code'] }}</span>
      </div>

      <div class="pos-product-item_price">
        <span class="text-success fw-bold">{{ number_format($finalPrice, 2) }} {{ $currency }}</span>

        @if($hasDiscount)
          <br>
          <strike class="fz-10 text-muted">
            {{ number_format(($product['selling_price'] / $unitValue), 2) . ' ' . $currency }}
          </strike>
        @endif
      </div>
    </div>
  </div>
</div>

@push('css_or_js')
<style>
  .pos-product-item{ transition:transform .15s ease, box-shadow .15s ease; }
  .pos-product-item:hover{ transform:translateY(-2px); box-shadow:0 10px 24px rgba(0,0,0,.10); }
  .product-thumb{ width:100%; height:150px; object-fit:cover; display:block; }
  .discount-badge{
    position:absolute; inset-block-start:.5rem; inset-inline-start:.5rem;
    background:#e11d48; color:#fff; padding:.25rem .45rem; border-radius:.5rem; font-size:.75rem; font-weight:700;
    box-shadow:0 2px 8px rgba(225,29,72,.35);
  }
  .info-btn{ backdrop-filter: blur(4px); }
  .pos-product-item_title{ white-space:nowrap; }
</style>
@endpush

<script>
  function showallProductDetails(productId) {
    const url = "{{ route('admin.pos.session.product', ['id' => '__ID__']) }}".replace('__ID__', productId);
    const CURRENCY = @json(\App\CPU\Helpers::currency_symbol());

    fetch(url, {
      method: "GET",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        "Accept": "application/json"
      }
    })
    .then(res => {
      const contentType = res.headers.get("content-type") || "";
      if (!contentType.includes("application/json")) {
        return res.text().then(text => { throw new Error("الرد غير متوقع:\n" + text.slice(0, 120)); });
      }
      return res.json();
    })
    .then(data => {
      if (!data.status) throw new Error(data.message || 'لم يتم العثور على المنتج');

      const p = data.product || {};
      const unitValue = Number(p.unit_value || 1) || 1;
      const basePrice = parseFloat(p.selling_price || 0);
      const discount = parseFloat(p.discount || 0);
      const taxRate = parseFloat((p.taxe && p.taxe.amount) || 0);

      // أسعار محسوبة
      const unitBase = basePrice / unitValue;
      const unitAfterDiscount = unitBase * (1 - (discount/100));
      const unitWithTax = unitAfterDiscount * (1 + (taxRate/100));

      const unitSmall = (p.unit_type && p.unit_type.unit) ? p.unit_type.unit : 'غير متوفر';
      const unitLarge = (p.unit && p.unit.subUnits && p.unit.subUnits.name) ? p.unit.subUnits.name : 'غير متوفر';

      const imgPath = (p.image ? "{{ asset('storage/app/public/product') }}/" + p.image : "{{ asset('public/assets/admin/img/160x160/img2.jpg') }}");

      const html = `
        <div style="direction:rtl; text-align:right;">
          <div style="display:flex; gap:14px; align-items:flex-start;">
            <img src="${imgPath}" style="width:100px;height:100px;object-fit:cover;border-radius:10px;border:1px solid #eee" />
            <div style="flex:1;">
              <h5 style="margin:0 0 6px;">${p.name || ''}</h5>
              <div class="text-muted" style="font-size:.9rem;">
                <b>كود المنتج:</b> <span style="font-family:monospace">${p.product_code || '—'}</span>
              </div>
              <div style="font-size:.9rem; margin-top:6px;">
                <b>الوصف:</b> ${p.description ? p.description : 'لا يوجد'}
              </div>
            </div>
          </div>

          <hr style="margin:12px 0;">

          <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; font-size:.95rem;">
            <div style="background:#fbfdff;border:1px solid #eef3f8;border-radius:10px;padding:8px 10px;">
              <div class="text-muted" style="font-size:.8rem;">سعر البيع الكلي</div>
              <div><b>${(basePrice || 0).toFixed(2)} ${CURRENCY}</b></div>
            </div>
            <div style="background:#fbfdff;border:1px solid #eef3f8;border-radius:10px;padding:8px 10px;">
              <div class="text-muted" style="font-size:.8rem;">سعر الوحدة (قبل الخصم)</div>
              <div><b>${unitBase.toFixed(2)} ${CURRENCY}</b></div>
            </div>
            <div style="background:#fbfdff;border:1px solid #eef3f8;border-radius:10px;padding:8px 10px;">
              <div class="text-muted" style="font-size:.8rem;">سعر الوحدة بعد الخصم</div>
              <div><b>${unitAfterDiscount.toFixed(2)} ${CURRENCY}</b></div>
            </div>
            <div style="background:#fbfdff;border:1px solid #eef3f8;border-radius:10px;padding:8px 10px;">
              <div class="text-muted" style="font-size:.8rem;">سعر الوحدة شامل الضريبة</div>
              <div><b>${unitWithTax.toFixed(2)} ${CURRENCY}</b></div>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-top:8px; font-size:.95rem;">
            <div style="background:#fff;border:1px dashed #e3e8ef;border-radius:10px;padding:8px 10px;">
              <span class="text-muted" style="font-size:.8rem;">نسبة الخصم</span>
              <div><b>${(discount || 0)}%</b></div>
            </div>
            <div style="background:#fff;border:1px dashed #e3e8ef;border-radius:10px;padding:8px 10px;">
              <span class="text-muted" style="font-size:.8rem;">نوع الضريبة</span>
              <div><b>${(p.taxe && p.taxe.name) ? p.taxe.name : 'غير محدد'}</b></div>
            </div>
            <div style="background:#fff;border:1px dashed #e3e8ef;border-radius:10px;padding:8px 10px;">
              <span class="text-muted" style="font-size:.8rem;">نسبة الضريبة</span>
              <div><b>${(taxRate || 0)}%</b></div>
            </div>
            <div style="background:#fff;border:1px dashed #e3e8ef;border-radius:10px;padding:8px 10px;">
              <span class="text-muted" style="font-size:.8rem;">عدد الوحدات في الكرتونة</span>
              <div><b>${unitValue}</b></div>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-top:8px; font-size:.95rem;">
            <div style="background:#fbfdff;border:1px solid #eef3f8;border-radius:10px;padding:8px 10px;">
              <div class="text-muted" style="font-size:.8rem;">وحدة القياس الصغرى</div>
              <div><b>${unitSmall}</b></div>
            </div>
            <div style="background:#fbfdff;border:1px solid #eef3f8;border-radius:10px;padding:8px 10px;">
              <div class="text-muted" style="font-size:.8rem;">وحدة القياس الكبرى</div>
              <div><b>${unitLarge}</b></div>
            </div>
          </div>
        </div>
      `;

      Swal.fire({
        title: '{{ \App\CPU\translate('تفاصيل المنتج') }}',
        html: html,
        width: '700px',
        showCloseButton: true,
        confirmButtonText: '{{ \App\CPU\translate('تم') }}'
      });
    })
    .catch((error) => {
      Swal.fire('{{ \App\CPU\translate('خطأ') }}', '{{ \App\CPU\translate('فشل في تحميل التفاصيل') }}: ' + error.message, 'error');
    });
  }
</script>
