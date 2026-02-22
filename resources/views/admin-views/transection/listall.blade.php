@extends('layouts.admin.app')

@section('title', \App\CPU\translate('transection_list'))

@push('css_or_js')
<style>
    .card-header {
        background-color: #001B63;
        color: white;
    }
    h3{
        color: white;
    }
  h4{
        color: white;
    }
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .form-control {
        border-radius: 5px;
    }

    h6 {
        font-weight: bold;
    }

    .summary-section p {
        font-size: 1.2em;
        font-weight: bold;
        color: #333;
    }

    .card-shadow-lg {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header text-center">
            <h3>عرض الضرائب</h3>
            <p>يمكنك تصفية الضرائب حسب التاريخ أو النوع</p>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.taxe.listall') }}" class="row gy-3">
                <div class="col-md-4">
                    <label for="account_id" class="form-label">الحساب</label>
                    <select name="account_id" id="account_id" class="form-control">
                        <option value="">اختر الحساب</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tran_type" class="form-label">نوع المعاملة</label>
                    <select name="tran_type" id="tran_type" class="form-control">
                        <option value="">اختر النوع</option>
                        <option value="12" {{ request('tran_type') == '12' ? 'selected' : '' }}>مشتريات</option>
                        <option value="24" {{ request('tran_type') == '24' ? 'selected' : '' }}>مردود مشتريات</option>
                        <option value="4" {{ request('tran_type') == '4' ? 'selected' : '' }}>مبيعات</option>
                        <option value="7" {{ request('tran_type') == '7' ? 'selected' : '' }}>مردود مبيعات</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="percent_tax" class="form-label">نسبة الضريبة</label>
                    <input type="number" step="0.1" name="percent_tax" id="percent_tax" class="form-control" placeholder="أدخل نسبة الضريبة" value="{{ request('percent_tax') }}">
                </div>

                <div class="col-md-6">
                    <label for="from" class="form-label">من تاريخ</label>
                    <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-6">
                    <label for="to" class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-success">تصفية</button>
                    <a href="{{ route('admin.taxe.listall') }}" class="btn btn-secondary">إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-lg mt-4">
        <div class="card-header text-center">
            <h4>ملخص المعاملات</h4>
        </div>
        <div class="card-body">
            <div class="row gy-3 summary-section">
                <div class="col-md-4">
                    <h6>إجمالي المشتريات:</h6>
                    <p>{{ number_format($totalPurchases, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>إجمالي مردود المشتريات:</h6>
                    <p>{{ number_format($totalRePurchases, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>إجمالي المبيعات:</h6>
                    <p>{{ number_format($totalSales, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>إجمالي مردود المبيعات:</h6>
                    <p>{{ number_format($totalReSales, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>إجمالي الدخل:</h6>
                    <p>{{ number_format($totalIncome, 2) }} ريال</p>
                </div>
                   <div class="col-md-4">
                    <h6>إجمالي  سندات القبض:</h6>
                    <p>{{ number_format($totalBalance, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>إجمالي المصروفات:</h6>
                    <p>{{ number_format($totalExpense, 2) }} ريال</p>
                </div>
                  <div class="col-md-4">
                    <h6>إجمالي سندات الصرف:</h6>
                    <p>{{ number_format($totalCredit, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>الرصيد الافتتاحي:</h6>
                    <p>{{ number_format($totalStart, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>الضريبة المحسوبة:</h6>
                    <p>{{ number_format($tax, 2) }} ريال</p>
                </div>
                <div class="col-md-4">
                    <h6>الضريبة بعد النسبة:</h6>
                    <p>{{ number_format($taxWithPercent, 2) }} ريال</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
