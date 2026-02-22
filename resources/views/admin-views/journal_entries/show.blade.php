@extends('layouts.admin.app')

@section('title', __('قيد #').$entry->id)

@section('content')
@php
    use App\Models\BusinessSetting;

    // إعدادات المتجر للطباعة
    $settings = BusinessSetting::whereIn('key', [
        'shop_name','shop_address','shop_phone','shop_email','number_tax','vat_reg_no','shop_logo'
    ])->pluck('value','key');

    $shopName   = $settings['shop_name']    ?? '';
    $shopAddr   = $settings['shop_address'] ?? '';
    $shopPhone  = $settings['shop_phone']   ?? '';
    $shopEmail  = $settings['shop_email']   ?? '';
    $taxNumber  = $settings['number_tax']   ?? '';
    $vatRegNo   = $settings['vat_reg_no']   ?? '';
    $shopLogo   = $settings['shop_logo']    ?? '';
@endphp

<style>
    .page-wrap{direction:rtl}
    .details-table th, .details-table td{vertical-align: middle}
    .toolbar{position:sticky; top:64px; z-index:6; background:#fff; border:1px solid #eef2f7; border-radius:10px; padding:10px 12px}
    @media print{ .non-printable{display:none !important} }
        .toolbar-actions{
        display:flex; flex-wrap:wrap; align-items:center;
    }
    .toolbar-actions > .btn{
        min-width: 150px;           /* نفس العرض */
        padding: .6rem 1rem;        /* نفس الارتفاع/الحشو */
    }
    .toolbar-actions > .btn + .btn{
        margin-inline-start: 12px;  /* مسافة بين الأزرار (RTL-aware) */
    }
</style>

<div class="container-fluid page-wrap">

    

    <div class="row mb-3">
        <div class="col-sm">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-secondary">{{ __('الرئيسية') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.journal-entries.index') }}" class="text-secondary">{{ __('قيود اليومية') }}</a></li>
                    <li class="breadcrumb-item active text-primary">{{ $entry->id }}</li>
                </ol>
            </nav>
        </div>
    </div>

<div class="toolbar non-printable mb-3 d-flex align-items-center justify-content-between">


    <div class="toolbar-actions">
        <button type="button"
                class="btn btn-info"
                onclick="exportTableToExcel('detailsTable')">
            {{ __('تصدير Excel') }}
        </button>

        <button type="button"
                class="btn btn-primary"
                onclick="printEntry()">
            {{ __('طباعة') }}
        </button>
    </div>
</div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><strong>{{ __('التاريخ') }}:</strong> {{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}</div>
                                <div class="col-md-3"><strong>{{ __('التسلسل') }}:</strong> {{ $entry->id }}</div>

                <div class="col-md-3"><strong>{{ __('الفرع') }}:</strong> {{ $entry->branch->name ?? '—' }}</div>
                <div class="col-md-3"><strong>{{ __('المستخدم') }}:</strong> {{ $entry->seller->email ?? '—' }}</div>
                <div class="col-12"><strong>{{ __('الوصف') }}:</strong> {{ $entry->description ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong>{{ __('تفاصيل القيد') }}</strong>
            @if( (int)($entry->reserve ?? 0) !== 0 )

            <div class="non-printable">
                <a href="{{ route('admin.journal-entries.edit', $entry->id) }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('تعديل') }}
                </a>
            </div>
            @endif
        </div>
        <div class="table-responsive">
            <table id="detailsTable" class="table details-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('#') }}</th>
                        <th>{{ __('الحساب') }}</th>
                        <th>{{ __('الكود') }}</th>
                        <th>{{ __('الوصف') }}</th>
                        <th>{{ __('مدين') }}</th>
                        <th>{{ __('دائن') }}</th>
                        <th>{{ __('مركز التكلفة') }}</th>
                        <th>{{ __('التاريخ') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->details as $i => $d)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $d->account->account ?? '—' }}</td>
                            <td>{{ $d->account->code ?? '—' }}</td>
                            <td>{{ $d->description ?? '—' }}</td>
                            <td class="text-success fw-bold">{{ number_format($d->debit,2) }}</td>
                            <td class="text-danger fw-bold">{{ number_format($d->credit,2) }}</td>
                            <td>{{ $d->costCenter->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($d->entry_date)->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        @php
                            $tdr = $entry->details->sum('debit');
                            $tcr = $entry->details->sum('credit');
                        @endphp
                        <th colspan="4" class="text-end">{{ __('الإجمالي') }}</th>
                        <th class="text-success fw-bold">{{ number_format($tdr,2) }}</th>
                        <th class="text-danger fw-bold">{{ number_format($tcr,2) }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

<!-- مكتبة xlsx للتصدير -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
<script>
    // تصدير الجدول إلى Excel
    function exportTableToExcel(tableId, filename = 'journal_entry_{{ $entry->id }}.xlsx') {
        const table = document.getElementById(tableId);
        if (!table) return;
        const wb = XLSX.utils.table_to_book(table, { sheet: "EntryDetails" });
        XLSX.writeFile(wb, filename);
    }

    // طباعة القيد مع هيدر بيانات المتجر
    function printEntry() {
        const table = document.getElementById('detailsTable');
        if (!table) return;

        const logoUrl = `{{ asset('storage/app/public/shop/' . $shopLogo) }}`;
        const now = new Date().toLocaleString('ar-EG', { hour12:false });

        const html = `
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Print</title>
<style>
    body{direction:rtl;font-family:'Cairo',Arial,sans-serif;background:#f4f6fa;color:#333;padding:24px}
    .header{display:flex;gap:16px;align-items:center;justify-content:space-between;border-bottom:2px solid #e5e7ef;padding-bottom:12px;margin-bottom:16px}
    .header .col{width:33%}
    .logo{text-align:center}
    .logo img{max-width:140px;height:auto}
    h2{text-align:center;margin:14px 0 6px 0}
    .muted{text-align:center;color:#666;margin-bottom:10px}
    table{width:100%;border-collapse:collapse;background:#fff}
    th,td{border:1px solid #e6e9f2;padding:10px 12px;text-align:center;font-size:13px}
    thead th{background:#eef3ff}
    tfoot th{background:#f7fafc}
    @page{margin:10mm}
    @media print{.non-printable{display:none!important}}
</style>
</head>
<body>

    <div class="header">
        <div class="col">
            <div><strong>رقم السجل التجاري:</strong> {{ $vatRegNo }}</div>
            <div><strong>الرقم الضريبي:</strong> {{ $taxNumber }}</div>
            <div><strong>البريد الإلكتروني:</strong> {{ $shopEmail }}</div>
        </div>
        <div class="logo">
            <img src="${logoUrl}" alt="Logo">
        </div>
        <div class="col" style="text-align:left">
            <div><strong>اسم المتجر:</strong> {{ $shopName }}</div>
            <div><strong>العنوان:</strong> {{ $shopAddr }}</div>
            <div><strong>رقم الجوال:</strong> {{ $shopPhone }}</div>
        </div>
    </div>

    <h2>{{ __('قيد يومية') }} — #{{ $entry->id }}</h2>
    <div class="muted">
        <strong>{{ __('التاريخ:') }}</strong> {{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}
        &nbsp;•&nbsp;
        <strong>{{ __('مرجع:') }}</strong> {{ $entry->reference }}
        &nbsp;•&nbsp;
        <strong>{{ __('طُبع في:') }}</strong> ${now}
    </div>

    ${table.outerHTML}

</body>
</html>`.trim();

        const win = window.open('', '_blank');
        win.document.open();
        win.document.write(html);
        win.document.close();
        win.focus();
        win.print();
    }
</script>
