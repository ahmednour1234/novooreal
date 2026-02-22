<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\CPU\translate('أرصدة إفتتاحية') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
        <div class="content container-fluid">
        <!-- Page Header -->
        <div class="row align-items-center mb-2">
            <div class="col-sm mb-2 mb-sm-0">
                                @if($type == 'Expense')
                <h1 class="page-header-title d-flex align-items-center text-center g-2px text-capitalize"><i class="tio-files"></i>
                    {{ \App\CPU\translate('تقرير المصروفات') }}
                    <span class="badge badge-soft-dark ml-2"></span>
                </h1>
                <!-- Display total expenses amount -->
                <h3 class="text-capitalize text-center">
                    {{ \App\CPU\translate('إجمالي المصروفات') }}: {{ $totalAmount }} {{ \App\CPU\Helpers::currency_symbol() }}
                </h3>
        @elseif ($type === 'Income')
        <h1 class="page-header-title d-flex align-items-center text-center g-2px text-capitalize"><i class="tio-files"></i>
                    {{ \App\CPU\translate('تقرير إيرادات  ') }}
                    <span class="badge badge-soft-dark ml-2"></span>
                </h1>
                <!-- Display total fixed assets amount -->
                <h3 class="text-capitalize text-center">
                    {{ \App\CPU\translate('إجمالي إيرادات ') }}: {{ $totalAmount }} {{ \App\CPU\Helpers::currency_symbol() }}
                </h3>
        @elseif($type === '2')
                <h1 class="page-header-title d-flex align-items-center text-center g-2px text-capitalize"><i class="tio-files"></i>
                    {{ \App\CPU\translate('تقرير أصول ثابتة') }}
                    <span class="badge badge-soft-dark ml-2"></span>
                </h1>
                <!-- Display total fixed assets amount -->
                <h3 class="text-capitalize text-center">
                    {{ \App\CPU\translate('إجمالي الأصول الثابتة') }}: {{ $totalAmount }} {{ \App\CPU\Helpers::currency_symbol() }}
                </h3>
                  @elseif($type === '100')
                <h1 class="page-header-title d-flex align-items-center text-center g-2px text-capitalize"><i class="tio-files"></i>
                    {{ \App\CPU\translate('تقرير سند صرف') }}
                    <span class="badge badge-soft-dark ml-2"></span>
                </h1>
                <!-- Display total fixed assets amount -->
                <h3 class="text-capitalize text-center">
                    {{ \App\CPU\translate('إجمالي سند صرف ') }}: {{ $totalAmount }} {{ \App\CPU\Helpers::currency_symbol() }}
                </h3>
                  @elseif($type === '200')
                <h1 class="page-header-title d-flex align-items-center text-center g-2px text-capitalize"><i class="tio-files"></i>
                    {{ \App\CPU\translate('تقرير سندات قبض') }}
                    <span class="badge badge-soft-dark ml-2"></span>
                </h1>
                <!-- Display total fixed assets amount -->
                <h3 class="text-capitalize text-center">
                    {{ \App\CPU\translate('إجمالي سندات قبض') }}: {{ $totalAmount }} {{ \App\CPU\Helpers::currency_symbol() }}
                </h3>
                @else
            @endif
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                         <tr class="table-light">
    <th class="text-center">{{ \App\CPU\translate('التاريخ') }} <i class="tio-calendar"></i></th>
    <th class="text-center">{{ \App\CPU\translate('الحساب الدائن') }} <i class="tio-wallet-outlined"></i></th>
        <th class="text-center">{{ \App\CPU\translate('الحساب المدين') }} <i class="tio-wallet-outlined"></i></th>
        <th class="text-center">{{ \App\CPU\translate('الكاتب') }} <i class="tio-wallet-outlined"></i></th>

<th class="text-center">
    {{ \App\CPU\translate('نوع') }} <i class="tio-label"></i>
</th>
    <th class="text-center">{{ \App\CPU\translate('المبلغ') }} <i class="tio-money"></i></th>
    <th class="text-center">{{ \App\CPU\translate('الوصف') }} <i class="tio-document-text"></i></th>
    <th class="text-center">{{ \App\CPU\translate('الرصيد') }} <i class="tio-pie-chart"></i></th>
</tr>

                            </thead>

                            <tbody>
                                @foreach ($expenses as $key => $expense)
                                    <tr>

                                        <td>{{ $expense->date }}</td>
                                            <td>
                                            {{ $expense->account ? $expense->account->account : '' }} <br>
                                        </td>
                                         <td>
                                            {{ $expense->account_to ? $expense->account_to->account : '' }} <br>
                                        </td>
                                            <td>
                                            {{ $expense->seller->email ?? '' }} <br>
                                        </td>
                               <td>
    <span class="badge badge-danger ml-sm-3">
        @if ($expense->tran_type === 'Expense')
            {{ 'مصروف' }}
        @elseif ($expense->tran_type === 'Income')
إيرادات
       @elseif($expense->tran_type === '2')
أصول ثابتة
    @elseif($expense->tran_type === '100')
سند صرف
@elseif($expense->tran_type === '200')
سند قبض
@else
@endif
        <br>
    </span>
</td>

             
                                        <td>
                                            {{ $expense->amount . ' ' . \App\CPU\Helpers::currency_symbol() }}
                                        </td>
                                        <td>
                                            {{ Str::limit($expense->description, 30) }}
                                        </td>
                                     

                                        <td>
                                            {{ $expense->balance . ' ' . \App\CPU\Helpers::currency_symbol() }}
                                        </td>
                                          
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                            <p class="text-center">
        {{ \App\CPU\translate('تم إنشاء هذا التقرير بواسطة') }} {{ $seller->email }}
    </p>
                        @if (count($expenses) == 0)
                            <div class="text-center p-4">
                                <img class="mb-3 img-one-ex"
                                    src="{{ asset('public/assets/admin') }}/svg/illustrations/sorry.svg"
                                    alt="{{ \App\CPU\translate('Image Description') }}">
                                <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها') }}</p>
                            </div>
                        @endif
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

     </body>
</html>