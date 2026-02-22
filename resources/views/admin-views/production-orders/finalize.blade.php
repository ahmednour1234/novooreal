{{-- resources/views/admin-views/production-orders/complete.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'إنهاء أمر الإنتاج')

@push('css_or_js')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<style>
  .form-card { border-radius:1rem; box-shadow:0 4px 16px rgba(0,0,0,0.1); margin-bottom:2rem; background:#fff; }
  .form-card .card-header { background:#161853; color:#fff; font-weight:700; padding:1rem 1.5rem; }
  .form-card .card-body { padding:1.75rem; }
  .form-label { font-weight:600; margin-bottom:.5rem; }
  .form-control, .form-select { border-radius:.5rem; padding:.75rem 1rem; }
  .is-invalid { border-color: #dc3545; }
  .invalid-feedback { display:block; font-size:.875em; color:#dc3545; }
  .table-fixed { width:100%; table-layout:fixed; border-collapse: separate; border-spacing:0 0.5rem; }
  .table-fixed thead th { background:#bee0ec; color:#161853; font-weight:600; padding:.75rem 1rem; border:none; }
  .table-fixed tbody tr { background:#fff; border-radius:.75rem; box-shadow:0 2px 6px rgba(0,0,0,0.05); transition: background .2s; }
  .table-fixed tbody td { padding:.75rem 1rem; vertical-align:middle; border:none; }
  .unit-radio { display:flex; gap:.5rem; font-size:.9rem; }
  .btn-submit { background:#161853; color:#fff; padding:.75rem 2rem; border-radius:.5rem; font-weight:600; }
  .btn-cancel { background:#f1f3f5; color:#333; padding:.75rem 2rem; margin-left:1rem; border-radius:.5rem; font-weight:600; }
  .summary { margin-top:1rem; font-weight:600; }
</style>
@endpush

@section('content')
<div class="content container-fluid" dir="rtl">
  <div class="card form-card">
    <div class="card-header">إنهاء أمر الإنتاج #{{ $order->id }}</div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.production-orders.finalize', $order->id) }}">
        @csrf

        {{-- بيانات عامة --}}
        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <label class="form-label">المنتج</label>
            <input type="text" class="form-control"
                   value="{{ $order->product->product_code }} – {{ $order->product->name }}" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">الكمية المخططة</label>
            <input type="text" class="form-control"
                   value="{{ number_format($order->quantity,2) }} {{ $order->unit_label }}" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">تاريخ ووقت البدء</label>
            <input type="datetime-local" name="start_time"
                   class="form-control @error('start_time') is-invalid @enderror"
                   value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}" required>
            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">الكمية المنتجة</label>
            <input type="number" name="produced_quantity" step="0.01"
                   class="form-control @error('produced_quantity') is-invalid @enderror"
                   value="{{ old('produced_quantity') }}" required>
            @error('produced_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- جدول المكونات --}}
        <div class="mb-4">
          <h5 class="form-label">تفاصيل المكونات المحجوزة</h5>
          <table class="table-fixed">
            <thead>
              <tr>
                <th>#</th><th>المكون</th><th>محجوز</th><th>سعر الوحدة</th><th>تكلفة محجوز</th>
                <th>استهلاك فعلي</th><th>هالك</th><th>تكلفة فعلي</th><th>تكلفة هالك</th><th>المتبقي</th>
              </tr>
            </thead>
            <tbody>
              @foreach($order->batches as $batch)
                @php
                  $reservedMaj  = $batch->pivot->reserved_quantity;
                  $uv           = $batch->product->unit_value ?: 1;
                  $minorLabel   = $batch->product->unit_label;
                  $price        = $batch->price ?: 0;
                @endphp
                <tr data-id="{{ $batch->id }}"
                    data-reserved-major="{{ $reservedMaj }}"
                    data-unit-value="{{ $uv }}"
                    data-price="{{ $price }}">
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $batch->product->product_code }} – {{ $batch->product->name }}</td>
                  <td>
                    {{ number_format($reservedMaj * $uv,2) }} {{ $minorLabel }}
                  </td>
                  <td>{{ number_format($price,2) }}</td>
                  <td class="reserved-cost">{{ number_format($reservedMaj * $price,2) }}</td>

                  {{-- استهلاك فعلي --}}
                  <td>
                    <input type="number" step="0.01" class="form-control actual-qty"
                           value="{{ old('batches.'.$batch->id.'.actual_input') }}">
                    <input type="hidden"
                           name="batches[{{ $batch->id }}][actual_quantity]"
                           class="actual-hidden"
                           value="{{ old('batches.'.$batch->id.'.actual_quantity') }}">
                    <div class="unit-radio">
                      <label><input type="radio"
                        name="batches[{{ $batch->id }}][actual_unit]" value="minor" checked> صغرى</label>
                      <label><input type="radio"
                        name="batches[{{ $batch->id }}][actual_unit]" value="major"> كبرى</label>
                    </div>
                  </td>

                  {{-- هالك --}}
                  <td>
                    <input type="number" step="0.01" class="form-control waste-qty"
                           value="{{ old('batches.'.$batch->id.'.waste_input') }}">
                    <input type="hidden"
                           name="batches[{{ $batch->id }}][waste_quantity]"
                           class="waste-hidden"
                           value="{{ old('batches.'.$batch->id.'.waste_quantity') }}">
                    <div class="unit-radio">
                      <label><input type="radio"
                        name="batches[{{ $batch->id }}][waste_unit]" value="minor" checked> صغرى</label>
                      <label><input type="radio"
                        name="batches[{{ $batch->id }}][waste_unit]" value="major"> كبرى</label>
                    </div>
                  </td>

                  <td class="actual-cost">0.00</td>
                  <td class="waste-cost">0.00</td>
                  <td>
                    <span class="remaining-qty">0.00</span> {{ $minorLabel }}
                  </td>
                  {{-- للحساب الداخلي --}}
                  <input type="hidden"
                         name="batches[{{ $batch->id }}][reserved_quantity]"
                         value="{{ $reservedMaj }}">
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th colspan="4" class="text-end">الإجمالي:</th>
                <th id="total-reserved-cost">0.00</th>
                <th id="total-actual-qty">0.00</th>
                <th id="total-waste-qty">0.00</th>
                <th id="total-actual-cost">0.00</th>
                <th id="total-waste-cost">0.00</th>
                <th id="total-remaining-qty">0.00</th>
              </tr>
            </tfoot>
          </table>

          {{-- تكاليف إضافية --}}
          <div class="mb-4">
            <label class="form-label">تكاليف إضافية (اختياري)</label>
            <div id="costs-container">
              @php $i = old('additional_costs') ? count(old('additional_costs')) : 1; @endphp
              @foreach(old('additional_costs', [['desc'=>'','amount'=>'']]) as $idx => $cost)
                <div class="row g-2 cost-row mb-2">
                  <div class="col">
                    <input type="text"
                           name="additional_costs[{{ $idx }}][desc]"
                           class="form-control"
                           placeholder="الوصف"
                           value="{{ $cost['desc'] }}">
                  </div>
                  <div class="col">
                    <input type="number"
                           name="additional_costs[{{ $idx }}][amount]"
                           class="form-control"
                           placeholder="المبلغ"
                           value="{{ $cost['amount'] }}">
                  </div>
                  <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger btn-remove-cost">حذف</button>
                  </div>
                </div>
              @endforeach
            </div>
            <button type="button" id="btn-add-cost" class="btn btn-outline-primary btn-sm mt-2">
              إضافة تكلفة
            </button>
          </div>

          <div class="summary">
            إجمالي تكلفة الوحدة المنتجة:
            <span id="produced-unit-cost">0.00</span>
          </div>
        </div>

        {{-- أزرار --}}
        <div class="text-center">
          <button type="submit" class="btn btn-submit">إنهاء الإنتاج</button>
          <a href="{{ route('admin.production-orders.show', $order->id) }}" class="btn btn-cancel">إلغاء</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(function(){
  // إضافة/حذف تكاليف إضافية
  let costIndex = {{ $i }};
  $('#btn-add-cost').click(function(){
    $('#costs-container').append(`
      <div class="row g-2 cost-row mb-2">
        <div class="col">
          <input type="text"
                 name="additional_costs[${costIndex}][desc]"
                 class="form-control"
                 placeholder="الوصف">
        </div>
        <div class="col">
          <input type="number"
                 name="additional_costs[${costIndex}][amount]"
                 class="form-control"
                 placeholder="المبلغ">
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-outline-danger btn-remove-cost">حذف</button>
        </div>
      </div>`);
    costIndex++;
  });
  $(document).on('click', '.btn-remove-cost', function(){
    $(this).closest('.cost-row').remove();
  });

  function recalc(){
    let totalResCost = 0,
        totalActMin  = 0,
        totalWasteMin= 0,
        totalActCost = 0,
        totalWasteCost=0,
        totalRemMin  = 0,
        totalAddCost = 0;

    $('tbody tr').each(function(){
      const $tr   = $(this),
            id    = $tr.data('id'),
            resMaj= parseFloat($tr.data('reserved-major')) || 0,
            uv    = parseFloat($tr.data('unit-value')) || 1,
            price = parseFloat($tr.data('price')) || 0;

      // محجوز
      const resMin = resMaj * uv;
      totalResCost += resMaj * price;

      // استهلاك فعلي
      const aRaw  = parseFloat($tr.find('.actual-qty').val()) || 0;
      const aUnit = $tr.find(`[name="batches[${id}][actual_unit]"]:checked`).val();
      const aMin  = aUnit === 'minor' ? aRaw : aRaw * uv;
      const aMaj  = aUnit === 'minor' ? aRaw / uv : aRaw;
      totalActMin  += aMin;
      totalActCost += aMaj * price;

      // هالك
      const wRaw  = parseFloat($tr.find('.waste-qty').val()) || 0;
      const wUnit = $tr.find(`[name="batches[${id}][waste_unit]"]:checked`).val();
      const wMin  = wUnit === 'minor' ? wRaw : wRaw * uv;
      const wMaj  = wUnit === 'minor' ? wRaw / uv : wRaw;
      totalWasteMin += wMin;
      totalWasteCost += wMaj * price;

      // المتبقي
      const remMin = resMin - (aMin + wMin);
      totalRemMin += remMin;

      // تحديث الحقول المخفية للإرسال
      $tr.find('.actual-hidden').val(aMaj.toFixed(2));
      $tr.find('.waste-hidden').val(wMaj.toFixed(2));

      // عرض القيم في الجدول
      $tr.find('.actual-cost').text((aMaj * price).toFixed(2));
      $tr.find('.waste-cost').text((wMaj * price).toFixed(2));
      $tr.find('.remaining-qty').text(remMin.toFixed(2));

      // تمييز الصفوف غير المتطابقة
      if (Math.abs((aMin + wMin) - resMin) > 0.001) $tr.css('background','#ffecec');
      else $tr.css('background','');
    });

    // جمع التكاليف الإضافية
    $('input[name^="additional_costs"]').each(function(){
      const name = $(this).attr('name');
      if (name.endsWith('[amount]')) totalAddCost += parseFloat($(this).val())||0;
    });

    $('#total-reserved-cost').text(totalResCost.toFixed(2));
    $('#total-actual-qty').text(totalActMin.toFixed(2));
    $('#total-waste-qty').text(totalWasteMin.toFixed(2));
    $('#total-actual-cost').text(totalActCost.toFixed(2));
    $('#total-waste-cost').text(totalWasteCost.toFixed(2));
    $('#total-remaining-qty').text(totalRemMin.toFixed(2));

    const producedQty = parseFloat($('input[name="produced_quantity"]').val()) || 1;
    const unitCost = (totalActCost + totalWasteCost + totalAddCost) / producedQty;
    $('#produced-unit-cost').text(unitCost.toFixed(2));
  }

  $(document).on('input change', '.actual-qty, .waste-qty, input[type=radio], input[name="produced_quantity"], input[name^="additional_costs"]', recalc);
  recalc();
});
</script>
@endsection
