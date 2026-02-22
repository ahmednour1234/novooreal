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
        <!-- End Page Header -->
    </div>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="text-center"><i
                            class="text-center"></i> {{\App\CPU\translate('تقرير أرصدة إفتتاحية')}}</h1>
                </div>
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
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{ \App\CPU\translate('التاريخ') }}</th>
                                <th >{{ \App\CPU\translate('الحساب') }}</th>
                               <th >{{ \App\CPU\translate('الكاتب') }}</th>
                                <th>{{\App\CPU\translate('النوع')}}</th>
                                <th>{{\App\CPU\translate('المبلغ')}}</th>
                                <th class="w-one-payable">{{\App\CPU\translate('الوصف')}}</th>
                                <th >{{\App\CPU\translate('الاجمالي')}}</th>
                                <!--<th>{{\App\CPU\translate('اجراءات')}}</th>-->
                            </tr>
                            </thead>

                            <tbody>
                                @foreach ($payables as $key=>$payable)
                                    <tr>
                                        <input type="hidden" id="available_balance-{{ $payable->id }}" value="{{ $payable->amount }}">
                                        <td>{{ $payable->date }}</td>
                                        <td>
                                            {{ $payable->account->account ?? ''}} <br>
                                        </td>
                                        <td>
                                            {{ $payable->seller->email ?? ''}} <br>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                               رصيد إفتتاحي <br>
                                            </span>
                                        </td>
                                        <td>
                                            {{ $payable->amount ." ".\App\CPU\Helpers::currency_symbol()}}
                                        </td>
                                        <td>
                                            {{ Str::limit($payable->description,30) }}
                                        </td>
                                        <td>
                                            {{ $payable->balance ." ".\App\CPU\Helpers::currency_symbol()}}
                                        </td>
                                        <td>

                                        <!--<button class="btn btn-sm" id="{{ $payable->id }}" onclick="balance_transfer({{ $payable->id }})" type="button" data-toggle="modal" data-target="#balance-transfer">-->
                                        <!--    <i class="tio-edit"></i>-->
                                        <!--</button>-->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                          <p class="text-center">
        {{ \App\CPU\translate('تم إنشاء هذا التقرير بواسطة') }} {{ $seller->email }}
    </p>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
   </body>
</html>