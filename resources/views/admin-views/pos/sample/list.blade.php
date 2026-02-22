{{-- resources/views/admin/orders/index.blade.php --}}
@extends('layouts.admin.app')
@section('title','Order List')

@push('css_or_js')
  <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush

@section('content')
@php
  // هل المستخدم فعّل أي فلتر؟
  $hasFilters = ($search ?? null)
    || ($regionId ?? null)
    || ($seller_id ?? null)
    || ($customer_id ?? null)
    || ($branch_id ?? null)
    || ($fromDate ?? null)
    || ($toDate ?? null);
@endphp

<style>
  :root{
    --ink:#0f172a; --muted:#6b7280; --brand:#0b3d91; --accent:#ff851b;
    --bg:#ffffff; --grid:#e5e7eb; --zebra:#fbfdff; --rd:14px;
  }
  .content.container-fluid{max-width: 98%}
  .breadcrumb{direction:rtl}

  /* ===== Filter Card ===== */
  .filter-card{
    background:var(--bg); border-radius:var(--rd);
    box-shadow:0 10px 26px -14px rgba(2,32,71,.18);
    border:1px solid var(--grid);
  }
  .filter-head{
    padding:12px 16px; border-bottom:1px solid var(--grid);
    display:flex; align-items:center; justify-content:space-between; gap:10px
  }
  .filter-head h4{margin:0; font-size:16px; font-weight:800; color:var(--ink)}
  .filter-body{ padding:14px 16px }
  .filter-grid{
    display:grid; gap:12px;
    grid-template-columns: repeat(12, 1fr);
    align-items:end;
  }
  .fg-6{ grid-column: span 6 }
  .fg-4{ grid-column: span 4 }
  .fg-3{ grid-column: span 3 }
  .fg-2{ grid-column: span 2 }
  .fg-12{ grid-column: 1 / -1 }

  @media (max-width:1200px){
    .fg-6,.fg-4,.fg-3,.fg-2{ grid-column: span 6 }
  }
  @media (max-width:768px){
    .filter-grid{ grid-template-columns: repeat(2, 1fr) }
    .fg-6,.fg-4,.fg-3,.fg-2,.fg-12{ grid-column: 1 / -1 }
  }

  .filter-body label{ font-weight:700; color:var(--muted); margin-bottom:6px }

  /* أزرار الفلاتر: يمين */
  .filter-actions{
    display:flex; flex-wrap:wrap; gap:.5rem;
    justify-content:flex-start; /* في RTL = يمين */
  }
  .filter-actions .btn{ min-width:150px; font-weight:800; border-radius:10px; padding:10px 14px }

  /* ===== Table ===== */
  .card{ border-radius:var(--rd); border:1px solid var(--grid) }
  .card-header{ background:#fff; border-bottom:1px solid var(--grid) }
  .card-body{ padding:14px }
  .table-responsive{ overflow:auto }
  table.card-table{ border-collapse: separate; border-spacing:0 }
  .card-table thead th{
    position: sticky; top:0; z-index:2;
    background:linear-gradient(180deg,#fff7e8,#fff);
    border-bottom:2px solid var(--grid);
    color:var(--ink); font-weight:800;
  }
  .table td, .table th{ border-bottom:1px solid #ddd !important; vertical-align:middle }
  .card-table tbody tr:nth-child(odd){ background:var(--zebra) }
  tfoot td{ border-top:2px solid #000 !important }
  .none{ display:table-cell } /* نخفيها فقط أثناء الطباعة */

  /* ===== Empty State ===== */
  .empty-state{
    border:1px dashed var(--grid); border-radius:var(--rd); padding:18px;
    display:flex; align-items:center; justify-content:center; gap:12px; color:var(--muted)
  }

  /* ===== Print ===== */
  @media print{
    .no-print{ display:none !important }
    .none{ display:none !important }
  }
</style>

<div class="content container-fluid">
  {{-- ===== Breadcrumb ===== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('الرئيسية') }}</a>
        </li>
        <li class="breadcrumb-item active">{{ \App\CPU\translate('فواتير مشتريات') }}</li>
      </ol>
    </nav>
  </div>

  {{-- ===== Filters Card ===== --}}
  <div class="filter-card mb-3">
    <div class="filter-head">
      <h4>{{ \App\CPU\translate('بحث وفلاتر مشتريات') }}</h4>
      @if($hasFilters)
        <small class="text-success fw-bold">{{ \App\CPU\translate('تم تطبيق الفلاتر') }}</small>
      @else
        <small class="text-danger fw-bold">{{ \App\CPU\translate('لن تُعرض بيانات حتى تطبق فلتر') }}</small>
      @endif
    </div>

    <div class="filter-body">
      <form action="{{ url()->current() }}" method="GET">
        <div class="filter-grid">

          {{-- بحث برقم الفاتورة --}}
          <div class="fg-4">
            <label>{{ \App\CPU\translate('بحث') }}</label>
            <div class="input-group">
              <input type="search" name="search" class="form-control"
                     placeholder="{{ \App\CPU\translate('أدخل رقم الفاتورة') }}"
                     value="{{ $search }}">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
          </div>


          {{-- الفرع --}}
          <div class="fg-4">
            <label>{{ \App\CPU\translate('الفرع') }}</label>
            <select name="branch_id" class="form-control select2">
              <option value="">{{ \App\CPU\translate('اختر الفرع') }}</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ ($branch->id == $branch_id) ? 'selected' : '' }}>
                  {{ $branch->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- من/إلى تاريخ --}}
          <div class="fg-3">
            <label>{{ \App\CPU\translate('من تاريخ') }}</label>
            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
          </div>
          <div class="fg-3">
            <label>{{ \App\CPU\translate('إلى تاريخ') }}</label>
            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
          </div>

          {{-- الأزرار (يمين) --}}
          <div class="fg-12 filter-actions">
            <button type="submit" class="btn btn-secondary">
              {{ \App\CPU\translate('بحث') }}
            </button>

            <button type="button" class="btn btn-success" onclick="exportInvoicesExcel()">
              {{ \App\CPU\translate('إصدار في أكسل') }}
            </button>

            <button type="button" class="btn btn-primary" onclick="printTable()">
              {{ \App\CPU\translate('طباعة التقرير') }}
            </button>

            <a href="{{ url()->current() }}" class="btn btn-danger">
              {{ \App\CPU\translate('إلغاء') }}
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ===== النتائج ===== --}}
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">{{ \App\CPU\translate('نتائج البحث') }}</h5>
      @if($hasFilters)
        <small class="text-muted">{{ \App\CPU\translate('عدد السجلات الظاهرة') }}: {{ $orders->total() ?? $orders->count() }}</small>
      @endif
    </div>

    @if(!$hasFilters)
      <div class="card-body">
        <div class="empty-state no-print">
          <span>ℹ️</span>
          <strong>{{ \App\CPU\translate('لن يتم عرض أي بيانات حتى تقوم بتطبيق فلتر واحد على الأقل') }}</strong>
        </div>
      </div>
    @else
      <div class="card-body" id="product-table">
        {{-- ملخص الفلاتر للطباعة فقط (لن يدخل الإكسل) --}}
        <table class="d-none nour" style="width:100%; border-collapse:collapse">
          <tr>
            <td colspan="3" style="text-align:center; font-weight:bold">اسم المندوب</td>
            <td colspan="3" style="text-align:center; font-weight:bold">{{ $sellerw->f_name ?? '' }}</td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:center; font-weight:bold">اسم الفرع</td>
            <td colspan="3" style="text-align:center; font-weight:bold">{{ $branchw->name ?? '' }}</td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:center; font-weight:bold">اسم العميل</td>
            <td colspan="3" style="text-align:center; font-weight:bold">{{ $customerw->name ?? '' }}</td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:center; font-weight:bold">تاريخ</td>
            <td colspan="3" style="text-align:center; font-weight:bold">{{ $fromDate }} - {{ $toDate }}</td>
          </tr>
        </table>

        <div class="table-responsive">
          <table id="invoicesTable" class="table ">
            <thead >
              <tr>
                <th class="none">{{\App\CPU\translate('#')}}</th>
                <th class="table-column-pl-0">{{\App\CPU\translate('مبيعات')}}</th>
                <th>{{\App\CPU\translate('اسم البائع')}}</th>
                <th>{{\App\CPU\translate('اسم العميل')}}</th>
                <th class="none">{{\App\CPU\translate('المنطقة')}}</th>
                <th>{{\App\CPU\translate('تاريخ')}}</th>
                <th class="none">{{\App\CPU\translate('نوع')}}</th>
                <th>{{\App\CPU\translate('طريقة الدفع')}}</th>
                <th class="none">{{\App\CPU\translate('اسم الحساب')}}</th>
                <th>{{\App\CPU\translate('اجمالي الفاتورة')}}</th>
                <th>{{\App\CPU\translate('خصم اضافي')}}</th>
                <th>{{\App\CPU\translate('ضريبة')}}</th>
                <th>{{\App\CPU\translate('المبلغ المدفوع')}}</th>
                <th class="none">{{\App\CPU\translate('المبلغ المحصل')}}</th>
                <th class="none">{{\App\CPU\translate('صورة الفاتورة')}}</th>
                <th class="none">{{\App\CPU\translate('روؤية الفاتورة')}}</th>
              </tr>
            </thead>

            <tbody id="set-rows">
            @foreach($orders as $key=>$order)
              <tr class="status-{{$order['order_status']}} class-all">
                <td class="none">{{$key+$orders->firstItem()}}</td>
                <td class="table-column-pl-0">
                  <a class="text-primary" href="#" onclick="print_invoice('{{$order->id}}')">{{$order['id']}}</a>
                </td>
                <td>{{ $order->seller->email ?? '' }}</td>
                <td>{{ optional($order->customer)->name }}</td>
                <td class="none">{{ optional(optional($order->customer)->regions)->name ?? '' }}</td>
                <td>{{ date('d M Y',strtotime($order['created_at'])) }}</td>
                <td class="none">{{ $order['type'] == 4 ? 'مبيع' : 'مرتجع' }}</td>
                <td>
                  @if ($order['cash'] == 2) أجل
                  @elseif ($order['cash'] == 3) شبكة
                  @else كاش
                  @endif
                </td>
                <td class="none">
                  {{ ($order->payment_id != 0) ? ($order->account ? $order->account->account : \App\CPU\translate('account_deleted')): 'Customer balance' }}
                </td>
                <td>{{ number_format($order->order_amount , 2) }}</td>
                <td>{{ $order->extra_discount ? number_format($order->extra_discount, 2) : 0 }}</td>
                <td>{{ number_format($order['total_tax'], 2) }}</td>
                <td>{{ number_format($order->collected_cash, 2) }}</td>
                <td class="none">{{ number_format($order->transaction_reference, 2) }}</td>
                <td class="none">
                  <img src="{{ asset('storage/app/public/'.$order['img']) }}" alt="Image" style="width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid #eee;">
                </td>
                <td class="none">
                  <button class="btn btn-sm btn-white" type="button" onclick="print_invoice('{{$order->id}}')">
                    <i class="tio-download"></i> {{\App\CPU\translate('A2')}}
                  </button>
                  <button class="btn btn-sm btn-white" type="button" onclick="print_invoicea2('{{$order->id}}')">
                    <i class="tio-download"></i> {{\App\CPU\translate('A4')}}
                  </button>
                </td>
              </tr>
            @endforeach
            </tbody>

            <tfoot>
              <tr>
                <td colspan="9" class="text-right"><strong>{{ \App\CPU\translate('الاجمالي') }}</strong></td>
                <td><strong>{{ number_format($orderAmountSum, 2) }}</strong></td>
                <td></td>
                <td></td>
                <td><strong>{{ number_format($collectedCashSum, 2) }}</strong></td>
                <td class="none"></td>
                <td class="none"></td>
                <td class="none"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- Pagination --}}
      <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center none">
          <div class="col-sm-auto">
            <div class="d-flex justify-content-center justify-content-sm-end" id="links">
              {!! $orders->links() !!}
            </div>
          </div>
        </div>
        @if(count($orders)==0)
          <div class="text-center p-4">
            <img class="mb-3 img-one-ol" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="Image">
            <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
          </div>
        @endif
      </div>
    @endif
  </div>
</div>

{{-- ===== Modal طباعة فاتورة مفردة ===== --}}
<div class="modal fade col-md-12" id="print-invoice" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-content1">
      <div class="modal-header">
        <h5 class="modal-title">{{\App\CPU\translate('طباعة')}} {{\App\CPU\translate('الفاتورة')}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="text-dark" aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body row">
        <div class="col-md-12">
          <center class="no-print">
            <input type="button" class="mt-2 btn btn-primary"
                   onclick="printDiv('printableArea')"
                   value="{{\App\CPU\translate('لو متصل بالطابعة اطبع')}}."/>
            <a href="{{url()->previous()}}" class="mt-2 btn btn-danger">{{\App\CPU\translate('عودة')}}</a>
          </center>
          <hr class="no-print">
        </div>
        <div class="row m-auto" id="printableArea"></div>
      </div>
    </div>
  </div>
</div>
@endsection

{{-- ===== Scripts ===== --}}
{{-- SheetJS لتصدير الإكسل --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
  // متغير عام لمنع الطباعة/التصدير بدون فلاتر
  const HAS_FILTERS = {!! $hasFilters ? 'true' : 'false' !!};

  // ===== Excel: جدول الفواتير فقط =====
  function exportInvoicesExcel(){
    if(!HAS_FILTERS){
      alert('من فضلك طبّق فلتر واحد على الأقل قبل التصدير إلى Excel.');
      return;
    }
    const table = document.getElementById('invoicesTable');
    if(!table){
      alert('جدول الفواتير غير موجود.');
      return;
    }

    // إنشاء Workbook من الجدول مباشرة
    const wb = XLSX.utils.table_to_book(table, { sheet: "Invoices" });

    // اسم الملف مع التاريخ/النطاق
    const from = (document.querySelector('input[name="from_date"]')?.value || '').trim();
    const to   = (document.querySelector('input[name="to_date"]')?.value || '').trim();
    const pad  = n => String(n).padStart(2,'0');
    const now  = new Date();
    const stamp = `${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}`;
    const range = (from || to) ? `_${from || 'start'}-to-${to || 'end'}` : '';
    const filename = `invoices${range}_${stamp}.xlsx`;

    XLSX.writeFile(wb, filename);
  }

  // ===== طباعة التقرير (مع الهيدر) =====
  function printTable(){
    if(!HAS_FILTERS){
      alert('من فضلك طبّق فلتر واحد على الأقل لعرض وطباعة البيانات.');
      return;
    }
    const tableContent = document.getElementById('product-table').innerHTML;
    const shopName   = `{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}`;
    const shopAddr   = `{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}`;
    const shopPhone  = `{{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}`;
    const shopEmail  = `{{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}`;
    const vatNo      = `{{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}`;
    const taxNo      = `{{ \App\Models\BusinessSetting::where(['key' => 'number_tax'])->first()->value }}`;
    const logoPath   = `{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}`;

    const win = window.open('', '_blank', 'width=900,height=1000');
    win.document.write(`
      <!DOCTYPE html>
      <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>{{ \App\CPU\translate('تقرير المبيعات') }}</title>
        <style>
          @page{ size:A4; margin:14mm }
          html,body{ background:#fff; font-family:'Cairo', Arial, sans-serif; color:#0f172a; }
          .header{
            display:flex; justify-content:space-between; align-items:center; gap:8px; border-bottom:2px solid #003366; padding:10px 0; margin-bottom:16px; flex-wrap:wrap
          }
          .header .col{ width:32%; text-align:center }
          .header .logo img{ max-width:150px; height:auto }
          h2{ text-align:center; color:#003366; margin:6px 0 12px; font-weight:900 }
          table{ width:100%; border-collapse:collapse; margin-top:6px; font-size:13px }
          th,td{ border:1px solid #ddd; padding:6px 8px; text-align:right }
          thead th{ background:#f2f2f2 }
          .none{ display:none }
        </style>
      </head>
      <body onload="window.print(); window.onafterprint = () => window.close();">
        <div class="header">
          <div class="col">
            <p><strong>رقم السجل التجاري:</strong> ${vatNo}</p>
            <p><strong>الرقم الضريبي:</strong> ${taxNo}</p>
            <p><strong>البريد الإلكتروني:</strong> ${shopEmail}</p>
          </div>
          <div class="col logo"><img src="${logoPath}" alt="Logo"></div>
          <div class="col">
            <p><strong>اسم المؤسسة:</strong> ${shopName}</p>
            <p><strong>العنوان:</strong> ${shopAddr}</p>
            <p><strong>رقم الجوال:</strong> ${shopPhone}</p>
          </div>
        </div>
        <h2>{{ \App\CPU\translate('تقرير المبيعات') }}</h2>
        ${tableContent}
      </body>
      </html>
    `);
    win.document.close();
  }

  function printDiv(divId){
    const content = document.getElementById(divId).innerHTML;
    const w = window.open('', '_blank', 'width=900,height=1000');
    w.document.write('<html dir="rtl"><head><title>Print</title></head><body onload="window.print();window.close();">'+content+'</body></html>');
    w.document.close();
  }
</script>

<script>
  "use strict";
  function print_invoice(order_id) {
    $.get({
      url: '{{url('/')}}/admin/pos/sample/invoice/' + order_id,
      dataType: 'json',
      beforeSend: function () { $('#loading').show(); },
      success: function (data) {
        $('#print-invoice').modal('show');
        $('#printableArea').empty().html(data.view);
      },
      complete: function () { $('#loading').hide(); },
      error: function (error) { console.log(error) }
    });
  }

  function print_invoicea2(order_id) {
    $.get({
      url: '{{url('/')}}/admin/pos/sample/invoicea2/' + order_id,
      dataType: 'json',
      beforeSend: function () { $('#loading').show(); },
      success: function (data) {
        $('#print-invoice').modal('show');
        $('#printableArea').empty().html(data.view);
      },
      complete: function () { $('#loading').hide(); },
      error: function (error) { console.log(error) }
    });
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
  $(function(){
    $('.select2').select2({ placeholder:'اختر...', allowClear:true });
  });
</script>

<script src={{asset("public/assets/admin/js/global.js")}}></script>
