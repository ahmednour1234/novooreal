@extends('layouts.admin.app')

@section('title', \App\CPU\translate('قائمة الرواتب'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/select2.min.css') }}">
    <style>
        .table td, .table th {
            vertical-align: middle;
            text-align: center;
        }
        .score-cell {
            position: relative;
            text-align: center;
        }
        .score-chart {
            display: block;
            margin: 0 auto;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <h3 class="mt-4 mb-4">{{ \App\CPU\translate('قائمة الرواتب') }}</h3>



        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.salaries.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4 row-md-6">
                    <label for="seller_id">{{ \App\CPU\translate('اختر الموظف') }}</label>
                    <select id="seller_id" name="seller_id" class="form-control select2">
                        <option value="">{{ \App\CPU\translate('اختر الموظف') }}</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="month">{{ \App\CPU\translate('الشهر') }}</label>
                    <input type="month" id="month" name="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">{{ \App\CPU\translate('بحث') }}</button>
                </div>
            </div>
        </form>

        <!-- Salaries Table -->

<div class="table-responsive">
    <!-- Table -->
    <table class="table table-bordered table-hover">
        <!-- Caption for the table (Month Header) -->
        <caption class="text-start fw-bold">{{ \App\CPU\translate('عن شهر') }} {{ request()->get('month') }}</caption>
        
        <thead class="table-light">
            <tr>
                <th>{{ \App\CPU\translate('الشهر') }}</th>
                <th>{{ \App\CPU\translate('معرف الموظف') }}</th>
                <th>{{ \App\CPU\translate('اسم الموظف') }}</th>
                <th>{{ \App\CPU\translate('الراتب') }}</th>
                <th>{{ \App\CPU\translate('ملاحظة') }}</th>
                <th>{{ \App\CPU\translate('عدد ايام العمل') }}</th>
                <th>{{ \App\CPU\translate('مبلغ النقل') }}</th>
                <th>{{ \App\CPU\translate('عمولة بيع') }}</th>
                <th>{{ \App\CPU\translate('بدلات أخري') }}</th>
                <th>{{ \App\CPU\translate('السلف') }}</th>
                <th>{{ \App\CPU\translate('الصافي') }}</th>
                <th class="nonew">{{ \App\CPU\translate('طباعة') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salaries as $salary)
                <tr id="row-{{ $salary->id }}">
                    <td>{{ \Carbon\Carbon::parse($salary->month)->format('Y-m') }}</td>
                    <td>{{ $salary->seller->id }}</td>
                    <td>{{ $salary->seller->f_name }}</td>
                    <td>{{ $salary->salary }}</td>
                    <td>{{ $salary->note }}</td>
                    <td>31</td>
                    <td>{{ $salary->salary_of_visitors }}</td>
                    <td>{{ $salary->transport_amount }}</td>
                    <td>{{ $salary->other }}</td>
                    <td>{{ $salary->discount }}</td>
                    <td>{{ $salary->total }}</td>
                    <td class="nonew">
                        <button class="btn btn-primary btn-sm" onclick="printRowWithHeaders('row-{{ $salary->id }}')">
                            {{ \App\CPU\translate('طباعة السطر') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">{{ \App\CPU\translate('لا توجد رواتب') }}</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10" class="text-end"><strong>{{ \App\CPU\translate('الإجمالي') }}:</strong></td>
                <td colspan="2" class="text-start">
                    {{ $salaries->sum('total') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Print Table Button -->
    <button class="btn btn-primary col-md-12" onclick="printTable()">
        {{ \App\CPU\translate('طباعة الجدول') }}
    </button>
</div>





        <!-- Pagination Links -->
        <div class="d-flex justify-content-center">
            {{ $salaries->links() }}
        </div>
    </div>
@endsection

@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });

    document.addEventListener('DOMContentLoaded', function () {
        @foreach($salaries as $salary)
            const ctx{{ $salary->id }} = document.getElementById('scoreChart{{ $salary->id }}').getContext('2d');
            
            new Chart(ctx{{ $salary->id }}, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [{{ $salary->score }}, 100 - {{ $salary->score }}],
                        backgroundColor: ['rgba(54, 162, 235, 0.7)', 'rgba(220, 220, 220, 0.2)'],
                        borderColor: ['rgba(54, 162, 235, 1)', 'rgba(220, 220, 220, 0.1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '80%', // Thickness of the circle
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return 'النقاط: ' + tooltipItem.raw + '%';
                                }
                            }
                        }
                    }
                }
            });
        @endforeach
    });
</script>
@endpush
<script>
    function generateHeader() {
        return `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="text-align: right; flex: 1;">
                    <p style="margin: 5px 0;"><strong>رقم السجل التجاري:</strong> {{ \App\Models\BusinessSetting::where(["key" => "vat_reg_no"])->first()->value }}</p>
                    <p style="margin: 5px 0;"><strong>الرقم الضريبي:</strong> {{ \App\Models\BusinessSetting::where(["key" => "number_tax"])->first()->value }}</p>
                    <p style="margin: 5px 0;"><strong>البريد الإلكتروني:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_email"])->first()->value }}</p>
                </div>
                <div style="flex: 1; text-align: center;">
                    <img src="{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}" alt="شعار المتجر" style="max-width: 150px; height: auto;">
                </div>
                <div style="text-align: right; flex: 1;">
                    <p style="margin: 5px 0;"><strong>اسم المؤسسة:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_name"])->first()->value }}</p>
                    <p style="margin: 5px 0;"><strong>العنوان:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_address"])->first()->value }}</p>
                    <p style="margin: 5px 0;"><strong>رقم الجوال:</strong> {{ \App\Models\BusinessSetting::where(["key" => "shop_phone"])->first()->value }}</p>
                </div>
            </div>
            <div style="text-align: center; margin-bottom: 20px; font-size: 20px; font-weight: bold;">سند صرف مرتب</div>
        `;
    }

    function printRowWithHeaders(rowId) {
        const row = document.getElementById(rowId);
        const headers = document.querySelectorAll('.table thead tr th');

        if (row && headers.length > 0) {
            let printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print</title><style>');
            printWindow.document.write(`
                @media print {
                    body {
                        font-family: Arial, sans-serif; direction: rtl; text-align: right;
                        margin: 20px;
                    }
                    table {
                        border-collapse: collapse; width: 100%; margin: 20px 0;
                    }
                    th, td {
                        border: 1px solid #ddd; padding: 10px; text-align: center;
                        font-size: 14px;
                    }
                    th {
                        background-color: #4CAF50; color: white; font-weight: bold;
                    }
                    tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }
                    .signature-section {
                        display: flex; justify-content: space-around; margin-top: 50px;
                    }
                    .signature {
                        width: 30%; text-align: center; border-top: 1px solid black; padding-top: 10px;
                        font-size: 14px;
                    }
                    .nonew{
                        display:none;
                    }
                }
            `);
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(generateHeader());
            printWindow.document.write('<table><thead><tr>');

            // Add headers
            headers.forEach(header => {
                printWindow.document.write('<th>' + header.innerHTML + '</th>');
            });
            printWindow.document.write('</tr></thead><tbody>');

            // Add row content
            printWindow.document.write(row.outerHTML);
            printWindow.document.write('</tbody></table>');
            printWindow.document.write(`
                <div class="signature-section">
                    <div class="signature">توقيع الموظف</div>
                    <div class="signature">توقيع المحاسب</div>
                    <div class="signature">توقيع المدير</div>
                </div>
            `);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
    }

    function printTable() {
        const table = document.querySelector('.table');

        if (table) {
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print</title><style>');
            printWindow.document.write(`
                @media print {
                    body {
                        font-family: Arial, sans-serif; direction: rtl; text-align: right;
                        margin: 20px;
                    }
                    table {
                        border-collapse: collapse; width: 100%; margin: 20px 0;
                    }
                    th, td {
                        border: 1px solid #ddd; padding: 0px; text-align: center;
                        font-size: 14px;
                    }
                    th {
                        background-color: #4CAF50; color: white; font-weight: bold;
                    }
                    tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }
                    .signature-section {
                        display: flex; justify-content: space-around; margin-top: 50px;
                    }
                    .signature {
                        width: 30%; text-align: center; border-top: 1px solid black; padding-top: 10px;
                        font-size: 14px;
                    }
                    .nonew{
                        display:none;
                    }
                    input[type="search"][aria-controls="DataTables_Table_0"] {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_0"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_1"]) {
    display: none;
}
                }
            `);
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(generateHeader());
            printWindow.document.write('<table>' + table.outerHTML + '</table>');
            printWindow.document.write(`
                <div class="signature-section">
                    <div class="signature">توقيع الموظف</div>
                    <div class="signature">توقيع المحاسب</div>
                    <div class="signature">توقيع المدير</div>
                </div>
            `);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
    }
</script>
