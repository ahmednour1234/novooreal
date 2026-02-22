@extends('layouts.admin.app')

@section('title', \App\CPU\translate('add_new_transfer'))

@push('css_or_js')
    <!-- Select2 CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
     <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .action-buttons .btn {
            min-width: 200px;
            margin: 5px;
        }
        .btn-gradient-primary {
            background: linear-gradient(45deg, #007bff, #00c6ff);
            border: none;
            color: #fff;
            transition: background 0.3s ease;
        }
        .btn-gradient-primary:hover {
            background: linear-gradient(45deg, #0056b3, #009fd9);
            color: #fff;
        }
        /* تحسين جدول التقارير */
        .datatable-custom table {
            width: 100%;
        }
        .datatable-custom th, .datatable-custom td {
            text-align: center;
            font-size: 12px;
        }
        .datatable-custom th {
            background: #fff;
            color: #000;
        }
        .filter-inputs {
            margin-bottom: 20px;
        }
        .filter-inputs .form-control {
            display: inline-block;
            width: auto;
            margin-right: 10px;
        }
        @media print {
            .non-printable, .filter-inputs, .action-buttons { display: none !important; }
        }
        th{
            color: black;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                        <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                    </a>
                </li>
                <li class="breadcrumb-item active text-primary" aria-current="page">
                        {{ \App\CPU\translate('القيود اليدوية') }}
                </li>
            </ol>
        </nav>
    </div>

        <!-- End Page Header -->

        <!-- Transfer Form -->
        <!-- End Transfer Form -->

        <!-- List of Transfers -->
        
        <div class="row">
            <div class="col-lg-12">
            <div>

              <div class="card shadow-sm p-3 mb-4">
    <!-- ✅ الصف الأول: الأزرار -->
 <div class="row g-2 align-items-center mb-2">
    <div class="col-auto">
        <button class="btn btn-sm btn-primary shadow non-printable" style="min-width: 160px;" onclick="printAllTable()">
            {{ \App\CPU\translate('طباعة الكل') }}
        </button>
    </div>

    <div class="col-auto">
        <a onclick="exportTableToExcel('expenseTable')" class="btn btn-sm btn-info shadow non-printable" style="min-width: 160px;">
            <i class="tio-download_to me-1"></i> {{ \App\CPU\translate('إصدار ملف أكسل') }}
        </a>
    </div>
    <div class="col-auto">
      
        <a href="{{ route('admin.account.add-transfer') }}"
           class="btn btn-sm btn-success shadow non-printable"
           style="min-width: 160px;">
            {{ \App\CPU\translate('إضافة قيد يدوي') }}
        </a>
    </div>

</div>


    <!-- ✅ الصف الثاني: الفلاتر -->
    <div class="row g-2">
        <div class="col-md-2 col-sm-6">
            <input type="text" id="searchBranch" class="form-control form-control-sm" placeholder="ابحث بالفرع">
        </div>
        <div class="col-md-2 col-sm-6">
            <input type="text" id="searchWriter" class="form-control form-control-sm" placeholder="ابحث بالكاتب">
        </div>
        <div class="col-md-2 col-sm-6">
            <input type="text" id="searchAccount" class="form-control form-control-sm" placeholder="ابحث بالحساب">
        </div>
        <div class="col-md-3 col-sm-6">
            <input type="date" id="searchFromDate" class="form-control form-control-sm" placeholder="من تاريخ">
        </div>
        <div class="col-md-3 col-sm-6">
            <input type="date" id="searchToDate" class="form-control form-control-sm" placeholder="إلى تاريخ">
        </div>
    </div>
</div>

    </div>



                    <!-- Table Content -->
                    <div class="card-body">
                    <div class="table-responsive datatable-custom">
                            <table id="expenseTable" class="table table-borderless table-thead-bordered table-nowrap table-align-middle">
                                <thead >
                                    <tr>
                                                                                                                <th class="text-center">{{ \App\CPU\translate('رقم') }}</th>

                                        <th data-col="date">{{ \App\CPU\translate('التاريخ') }}</th>
                                        <th data-col="account">{{ \App\CPU\translate('الحساب الدائن') }}</th>
                                        <th data-col="account">{{ \App\CPU\translate('الحساب المدين') }}</th>
                                        <th data-col="cost">{{ \App\CPU\translate('مركز التكلفة') }}</th>
                                        <th data-col="writer">{{ \App\CPU\translate('الكاتب') }}</th>
                                        <th data-col="branch">{{ \App\CPU\translate('الفرع') }}</th>
                                        <th data-col="type">{{ \App\CPU\translate('النوع') }}</th>
                                        <th data-col="desc">{{ \App\CPU\translate('الوصف') }}</th>
                                        <th data-col="amount">{{ \App\CPU\translate('المبلغ') }}</th>
                                        <th>{{ \App\CPU\translate('صورة') }}</th>
                                        <th class="text-center none">{{ \App\CPU\translate('طباعة') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfers as $transfer)
                                        <tr>
                                                                                                                            <td data-search="{{ $transfer->id }}">{{ $transfer->id ?? 'غير محدد' }}</td>

                                            <td data-search="{{ $transfer->date }}">{{ $transfer->date }}</td>
                                            <td data-search="{{ $transfer->account ? $transfer->account->account : 'غير محدد' }}">
                                                {{ $transfer->account ? $transfer->account->account : 'غير محدد' }}
                                            </td>
                                            <td data-search="{{ $transfer->account_to ? $transfer->account_to->account : 'غير محدد' }}">
                                                {{ $transfer->account_to ? $transfer->account_to->account : 'غير محدد' }}
                                            </td>
                                            <td data-search="{{ $transfer->costcenter ? $transfer->costcenter->name : 'غير محدد' }}">
                                                {{ $transfer->costcenter ? $transfer->costcenter->name : 'غير محدد' }}
                                            </td>
                                            <td data-search="{{ $transfer->seller->email ?? '' }}">{{ $transfer->seller->email ?? '' }}</td>
                                            <td data-search="{{ $transfer->branch->name ?? '' }}">{{ $transfer->branch->name ?? '' }}</td>
                                            <td data-search="
                                                @if ($transfer->tran_type === 'Transfer')
                                                    قيود يدوية
                                                @else
                                                    {{ $transfer->tran_type }}
                                                @endif
                                            ">
                                                <span class="badge-koyod">
                                                    @if ($transfer->tran_type === 'Transfer')
                                                        {{ 'قيود يدوية' }}
                                                    @else
                                                        {{ $transfer->tran_type }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-search="{{ Str::limit($transfer->description, 30) }}">{{ Str::limit($transfer->description, 30) }}</td>
                                            <td data-search="{{ $transfer->amount }}">{{ $transfer->amount . ' ' . \App\CPU\Helpers::currency_symbol() }}</td>
                                            <td>
                                                <img class="navbar-brand-logo" src="{{ asset('storage/app/public/shop/' . $transfer->img) }}" onerror="this.onerror=null; this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}';" alt="Logo">

                                            </td>
                                            <td class="none">
                                                <button class="btn btn-sm btn-white" type="button" onclick="print_invoicea2('{{ $transfer->id }}')">
                                                    <i class="tio-download"></i> {{ \App\CPU\translate('الإيصال') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="9" class="text-right">
                                            <strong>{{ \App\CPU\translate('إجمالي المبلغ') }}:</strong>
                                        </td>
                                        <td colspan="1" class="text-center">
                                            <strong>{{ $transfers->sum('amount') . ' ' . \App\CPU\Helpers::currency_symbol() }}</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="page-area mt-3">
                            {!! $transfers->links() !!}
                        </div>

                        @if (count($transfers) == 0)
                            <div class="text-center p-4">
                                <img class="mb-3" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}">
                                <p>{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Invoice Modal -->
    <div class="modal fade" id="print-invoice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modal-content1">
                <div class="modal-header">
                    <h5 class="modal-title">{{ \App\CPU\translate('طباعة') }} {{ \App\CPU\translate('إيصال') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span class="text-dark" aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <input type="button" class="btn btn-primary non-printable" onclick="printDiv('printableArea')" value="{{ \App\CPU\translate('اطبع لو الطابعة جاهزة') }}.">
                        <a href="{{ url()->previous() }}" class="btn btn-danger non-printable">{{ \App\CPU\translate('عودة') }}</a>
                    </div>
                    <div id="printableArea"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- ✅ كود التصدير -->
<script>
    function exportTableToExcel(tableId, filename = 'transactions.xlsx') {
        let table = document.getElementById(tableId);
        let workbook = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
        XLSX.writeFile(workbook, filename);
    }
</script>
    <!-- Select2 JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script>
        // Initialize Select2 for all select elements with the class "select2"
        $(document).ready(function(){
            $('.select2').select2({
                width: '100%',
                placeholder: "{{ \App\CPU\translate('اختار') }}"
            });
        });
    </script>
    <script>
        "use strict";
        function print_invoicea2(order_id) {
            $.get({
                url: '{{ url('/') }}/admin/account/invoice_expense/' + order_id,
                dataType: 'json',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#print-invoice').modal('show');
                    $('#printableArea').empty().html(data.view);
                },
                complete: function () {
                    $('#loading').hide();
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    </script>
        <script>
        function printAllTable() {
            var printWindow = window.open('', '_blank');
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
                    font-size: 16px;
                    color: #333;
                }
                .logo {
                    text-align: center;
                    width: 30%;
                }
                .logo img {
                    max-width: 150px;
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
                    font-size: 24px;
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
                    padding: 12px 15px;
                    text-align: center;
                    font-size: 14px;
                    color: #333;
                }
                th {
                    background: #007bff;
                    color: #fff;
                    font-weight: bold;
                }
                td {
                    background: #f9f9f9;
                }
                td img, td button {
                    display: none;
                }
                .none{
                    display:none;
                }
                @media print {
                    @page {
                        margin: 0mm;
                    }
                    footer {
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        width: 100%;
                        text-align: center;
                        font-size: 12px;
                        color: #555;
                        padding: 10px;
                        background-color: #f4f4f9;
                    }
                }
            `);
            printWindow.document.write('</style></head><body>');
            
            // Get business details
            let shopName = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}";
            let shopAddress = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}";
            let shopPhone = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}";
            let shopEmail = "{{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}";
            let taxNumber = "{{ \App\Models\BusinessSetting::where(['key' => 'number_tax'])->first()->value }}";
            let vatRegNo = "{{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}";
    
            // Get current date and time
            let currentDateTime = new Date().toLocaleString('ar-EG', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
    
            // Write header content
            printWindow.document.write('<div class="header-section">');
            printWindow.document.write('<div class="right"><p><strong>رقم السجل التجاري:</strong> ' + vatRegNo + '</p>');
            printWindow.document.write('<p><strong>الرقم الضريبي:</strong> ' + taxNumber + '</p>');
            printWindow.document.write('<p><strong>البريد الإلكتروني:</strong> ' + shopEmail + '</p></div>');
            let logoUrl = "{{ asset('storage/app/public/shop/' . \App\Models\BusinessSetting::where(['key' => 'shop_logo'])->first()->value) }}";
            printWindow.document.write('<div class="logo"><img src="' + logoUrl + '" alt="شعار المتجر"></div>');
            printWindow.document.write('<div class="left"><p><strong>اسم المتجر:</strong> ' + shopName + '</p>');
            printWindow.document.write('<p><strong>العنوان:</strong> ' + shopAddress + '</p>');
            printWindow.document.write('<p><strong>رقم الجوال:</strong> ' + shopPhone + '</p></div></div>');
    
            printWindow.document.write('<h2>{{ \App\CPU\translate("تقرير المصروفات اليومية") }}</h2>');
            printWindow.document.write('<div class="date-time"><p><strong>تاريخ الطباعة:</strong> ' + currentDateTime + '</p></div>');
    
            // Get table content and remove العناصر غير المراد طباعتها
            let tableContent = document.querySelector('.datatable-custom table').outerHTML;
            printWindow.document.write(tableContent);
            printWindow.document.write('<footer><p>{{ \App\CPU\translate("تمت الطباعة بواسطة النظام") }}</p></footer>');
    
            printWindow.document.close();
            printWindow.print();
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchBranch = document.getElementById('searchBranch');
            const searchWriter = document.getElementById('searchWriter');
            const searchAccount = document.getElementById('searchAccount');
            const searchFromDate = document.getElementById('searchFromDate');
            const searchToDate = document.getElementById('searchToDate');
            const tableBody = document.getElementById('expenseTable').getElementsByTagName('tbody')[0];
            const rows = tableBody.getElementsByTagName('tr');
            const filteredTotalEl = document.getElementById('filteredTotal');

            function filterTable() {
                let branchFilter = searchBranch.value.toLowerCase();
                let writerFilter = searchWriter.value.toLowerCase();
                let accountFilter = searchAccount.value.toLowerCase();
                let fromDateFilter = searchFromDate.value;
                let toDateFilter = searchToDate.value;
                let total = 0;

                for (let i = 0; i < rows.length; i++) {
                    let row = rows[i];
                    // الحصول على البيانات من الأعمدة باستخدام data-search
                    let dateText = row.cells[0].getAttribute('data-search') ? row.cells[0].getAttribute('data-search').toLowerCase() : '';
                    let creditorText = row.cells[1].getAttribute('data-search') ? row.cells[1].getAttribute('data-search').toLowerCase() : '';
                    let debtorText = row.cells[2].getAttribute('data-search') ? row.cells[2].getAttribute('data-search').toLowerCase() : '';
                    let writerText = row.cells[4].getAttribute('data-search') ? row.cells[4].getAttribute('data-search').toLowerCase() : '';
                    let branchText = row.cells[5].getAttribute('data-search') ? row.cells[5].getAttribute('data-search').toLowerCase() : '';

                    // التحقق من كل فلتر
                    let showRow = true;
                    if (branchFilter && branchText.indexOf(branchFilter) === -1) {
                        showRow = false;
                    }
                    if (writerFilter && writerText.indexOf(writerFilter) === -1) {
                        showRow = false;
                    }
                    if (accountFilter && creditorText.indexOf(accountFilter) === -1 && debtorText.indexOf(accountFilter) === -1) {
                        showRow = false;
                    }
                    // فلترة التاريخ: إذا تم تحديد تاريخ بداية ونهاية، نتحقق من كون تاريخ الصف داخل النطاق
                    if (fromDateFilter || toDateFilter) {
                        // تحويل التاريخ من النص إلى كائن تاريخ
                        let rowDate = new Date(row.cells[0].getAttribute('data-search'));
                        if (fromDateFilter) {
                            let fromDate = new Date(fromDateFilter);
                            if (rowDate < fromDate) {
                                showRow = false;
                            }
                        }
                        if (toDateFilter) {
                            let toDate = new Date(toDateFilter);
                            if (rowDate > toDate) {
                                showRow = false;
                            }
                        }
                    }
                    row.style.display = showRow ? '' : 'none';
                    // إذا كان الصف ظاهرًا، نجمع قيمة المبلغ (من data-amount)
                    if (showRow) {
                        let amount = parseFloat(row.cells[row.cells.length - 3].getAttribute('data-amount')) || 0;
                        total += amount;
                    }
                }
                // تحديث الإجمالي في التذييل
                filteredTotalEl.innerHTML = '<strong>' + total.toFixed(2) + ' {{ \App\CPU\Helpers::currency_symbol() }}</strong>';
            }

            // إضافة مستمعي الأحداث للتغييرات على مدخلات البحث
            searchBranch.addEventListener('input', filterTable);
            searchWriter.addEventListener('input', filterTable);
            searchAccount.addEventListener('input', filterTable);
            searchFromDate.addEventListener('change', filterTable);
            searchToDate.addEventListener('change', filterTable);
        });
    </script>
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
