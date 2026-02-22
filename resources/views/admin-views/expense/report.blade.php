@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_expense'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css" />
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-12 col-md-6 col-lg-5 mb-3 mb-lg-0">
                                <form action="{{ url()->current() }}" method="GET">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                            placeholder="{{ \App\CPU\translate('search_by_description') }}"
                                            value="{{ $search }}" required>
                                    

                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                            <div class="col-12 col-lg-7">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ \App\CPU\translate('من') }}
                                                </label>
                                                <input id="from_date" type="date" name="from" class="form-control"
                                                    value="{{ $from }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ \App\CPU\translate('الي') }}
                                                </label>
                                                <input id="to_date" type="date" name="to" class="form-control"
                                                    value="{{ $to }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button href="" class="btn btn-primary mt-4">
                                                {{ \App\CPU\translate('بحث') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0 text-capitalize">
            {{ \App\CPU\translate('تقرير سندات الصرف') }}
        </h5>
        <button class="btn btn-outline-primary btn-sm" onclick="printTable()">
            <i class="tio-print"></i> {{ \App\CPU\translate('طباعة التقرير') }}
        </button>
    </div>

    <div class="table-responsive datatable-custom">
<table class="table table-bordered table-hover table-align-middle card-table" id="printableTable">
    <thead class="thead-light">
        <tr>
            <th class="text-center">{{ \App\CPU\translate('رقم الحساب') }} <i class="tio-wallet-outlined"></i></th>
            <th class="text-center">{{ \App\CPU\translate('اسم الحساب') }} <i class="tio-wallet-outlined"></i></th>
            <th class="text-center">{{ \App\CPU\translate('إجمالي السندات') }} <i class="tio-money"></i></th>
        </tr>
    </thead>

    <tbody>
        @foreach ($accountSummary as $account)
            <tr>
                <td class="text-center">{{ $account['account_number'] }}</td>
                <td class="text-center">{{ $account['account'] ?? '' }}</td>
                <td class="text-center">{{ number_format($account['total_amount'], 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
            </tr>
        @endforeach
    </tbody>

    <!-- Footer with Total Amount -->
    <tfoot>
        <tr>
            <td class="text-center" colspan="2"><strong>{{ \App\CPU\translate('إجمالي السندات') }}</strong></td>
            <td class="text-center"><strong>{{ number_format($totalAmount, 2) . ' ' . \App\CPU\Helpers::currency_symbol() }}</strong></td>
        </tr>
    </tfoot>
</table>

        
        
    </div>
</div>



<!-- JavaScript to Print the Table -->

                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $('#from_date,#to_date').change(function() {
            let fr = $('#from_date').val();
            let to = $('#to_date').val();
            if (fr != '' && to != '') {
                if (fr > to) {
                    $('#from_date').val('');
                    $('#to_date').val('');
                    toastr.error('Invalid date range!', Error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            }

        })
    </script>


    <script src={{asset("public/assets/admin/js/global.js")}}></script>
@endpush

<script>
    function printTable() {
        let table = document.getElementById('printableTable');
        if (!table) {
            alert('Table not found!');
            return;
        }

        let printContent = table.outerHTML;

        // Get current date and time
        let currentDate = new Date();
        let formattedDate = currentDate.toLocaleDateString('ar-EG', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
        let formattedTime = currentDate.toLocaleTimeString('ar-EG', { hour12: false });

        let printWindow = window.open('', '', 'height=700,width=900');
        printWindow.document.write('<html><head><title>{{ \App\CPU\translate("تقرير سندات الصرف") }}</title>');
        printWindow.document.write('<style>');
        printWindow.document.write(`
            body {
                direction: rtl;
                font-family: 'Cairo', Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background: #f4f4f9;
                color: #333;
            }
            .header-section {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 30px;
                border-bottom: 2px solid #ddd;
                padding-bottom: 10px;
            }
            .header-section .left, .header-section .right {
                width: 30%;
                font-size: 14px;
            }
            .header-section p {
                margin: 5px 0;
                line-height: 1.4;
            }
            .logo {
                text-align: center;
                width: 30%;
            }
            .logo img {
                max-width: 100px;
                height: auto;
            }
            .date-time {
                text-align: center;
                font-size: 14px;
                color: #666;
                margin-bottom: 10px;
            }
            h2 {
                text-align: center;
                color: #444;
                margin-bottom: 20px;
                font-size: 20px;
                font-weight: bold;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                background: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            th, td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: center;
                font-size: 14px;
            }
            th {
                background: #007bff;
                color: #fff;
                font-weight: bold;
            }
            td {
                background: #f9f9f9;
            }
            @media print {
                @page {
                    margin: 20mm;
                }
                body::after {
                    content: counter(page);
                }
                footer {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    text-align: center;
                    font-size: 12px;
                    color: #555;
                }
            }
        `);
        printWindow.document.write('</style></head><body>');

        // Add Date and Time
        printWindow.document.write('<div class="date-time">');
        printWindow.document.write('<p><strong>تاريخ الطباعة:</strong> ' + formattedDate + ' <strong>الوقت:</strong> ' + formattedTime + '</p>');
        printWindow.document.write('</div>');

        // Add Business Details
        let shopName = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}";
        let shopAddress = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}";
        let shopPhone = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}";
        let shopEmail = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}";
        let taxNumber = "{{ \App\Models\BusinessSetting::where(['key' => 'number_tax'])->first()->value }}";
        let vatRegNo = "{{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}";

        printWindow.document.write('<div class="header-section">');
        
        // Right Section
        printWindow.document.write('<div class="right">');
        printWindow.document.write('<p><strong>رقم السجل التجاري:</strong> ' + vatRegNo + '</p>');
        printWindow.document.write('<p><strong>الرقم الضريبي:</strong> ' + taxNumber + '</p>');
        printWindow.document.write('<p><strong>البريد الإلكتروني:</strong> ' + shopEmail + '</p>');
        printWindow.document.write('</div>');

        // Logo Section
        let logoUrl = "{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}";
        printWindow.document.write('<div class="logo">');
        printWindow.document.write('<img src="' + logoUrl + '" alt="{{ \App\CPU\translate("شعار المتجر") }}">');
        printWindow.document.write('</div>');

        // Left Section
        printWindow.document.write('<div class="left">');
        printWindow.document.write('<p><strong>اسم المتجر:</strong> ' + shopName + '</p>');
        printWindow.document.write('<p><strong>العنوان:</strong> ' + shopAddress + '</p>');
        printWindow.document.write('<p><strong>رقم الجوال:</strong> ' + shopPhone + '</p>');
        printWindow.document.write('</div>');

        printWindow.document.write('</div>');

        // Add Title
        printWindow.document.write('<h2>{{ \App\CPU\translate("تقرير سندات الصرف") }}</h2>');

        // Add Table Content
        printWindow.document.write(printContent);

        // Add Footer (Page Number & Date-Time)
        printWindow.document.write('<footer>');
        printWindow.document.write('<p>تاريخ الطباعة: ' + formattedDate + ' - الوقت: ' + formattedTime + ' | الصفحة: <span class="pageNumber"></span> من <span class="totalPages"></span></p>');
        printWindow.document.write('</footer>');

        printWindow.document.write('</body></html>');
        printWindow.document.close();

        // Print the document
        printWindow.print();
    }
</script>
