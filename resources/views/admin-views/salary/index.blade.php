@extends('layouts.admin.app')
@section('title', \App\CPU\translate('دفع مرتب جديد'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/select2.min.css') }}">
    <style>
        .page-header-title {
            font-size: 2.5rem;
            font-weight: 600;
            color: #001B63;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 15px;
            padding: 20px;
        }
        .form-control {
            border-radius: 10px;
        }
        .btn-primary {
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 16px;
        }
        .select2-container .select2-selection--single {
            height: 40px;
            border-radius: 10px;
            padding-top: 4px;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <h3 class="mt-4 text-center text-primary">{{ \App\CPU\translate('دفع مرتب جديد') }}</h3>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <form id="salary-form" method="POST" action="{{ route('admin.salaries.store') }}">
                    @csrf
                    <!-- First field: Month -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">
                            {{ \App\CPU\translate('عن شهر') }} <span class="text-danger">*</span>
                        </label>
                        <input type="month" id="month" name="month" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('الحساب') }}</label>
                            <select name="account_id" class="form-control select2" required>
                                <option value="">{{ \App\CPU\translate('اختار الحساب') }}</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('الحساب المحول له') }}</label>
                            <select name="account_id_to" class="form-control select2" required>
                                <option value="">{{ \App\CPU\translate('اختار الحساب المحول له') }}</option>
                                @foreach ($accounts_to as $account)
                                    <option value="{{ $account->id }}">{{ $account->account }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>{{ \App\CPU\translate('مركز التكلفة') }}</label>
                            <select name="cost_id" class="form-control select2">
                                <option value="">{{ \App\CPU\translate('اختار مركز التكلفة') }}</option>
                                @foreach ($costcenters as $costcenter)
                                    <option value="{{ $costcenter->id }}">{{ $costcenter->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Employee selection -->
                    <div class="mb-3">
                        <label>{{ \App\CPU\translate('اختار موظف') }}</label>
                        <select id="seller_id" name="seller_id" class="form-control select2" required>
                            <option value="">{{ \App\CPU\translate('اختار موظف') }}</option>
                            @foreach($sellers as $seller)
                                        <option value="{{ $seller->id }}">{{ $seller->email }}-({{$seller->f_name.$seller->l_name}})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Attendance Summary -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('عدد أيام العمل المتوقعة') }}</label>
                            <input type="text" id="number_of_days" name="number_of_days" class="form-control" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('عدد أيام العمل الفعلية') }}</label>
                            <input type="text" id="actual_work_days" name="actual_work_days" class="form-control" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('ساعات العمل المتوقعة') }}</label>
                            <input type="text" id="expected_work_hours" name="expected_work_hours" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('ساعات العمل الفعلية') }}</label>
                            <input type="text" id="worked_hours" name="worked_hours" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('التأخيرات') }}</label>
                            <input type="text" id="late_hours" name="late_hours" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Expected Deduction -->
                    <div class="form-group mb-3">
                        <label>{{ \App\CPU\translate('الخصومات المتوقعة') }}</label>
                        <input type="text" id="expected_deduction" name="expected_deduction" class="form-control" readonly>
                    </div>

                    <!-- New fields: Taxes and Insurances -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('الضرائب') }}</label>
                            <input type="number" id="taxes" name="taxes" class="form-control rounded-pill shadow-sm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('التأمينات') }}</label>
                            <input type="number" id="insurance" name="insurance" class="form-control rounded-pill shadow-sm" required>
                        </div>
                    </div>

                    <!-- New fields: Total Incentives & Total Deductions -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('اجمالي الحوافز') }}</label>
                            <!-- Sum of: حافز البيع, مكافأة الالتزام, بدلات أخرى -->
                            <input type="text" id="incentives_total" name="incentives_total" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>{{ \App\CPU\translate('اجمالي الخصومات') }}</label>
                            <!-- Sum of: خصم, الضرائب, التأمينات, والخصومات المتوقعة -->
                            <input type="text" id="deductions_total" name="deductions_total" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Salary Details -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('الراتب') }}</label>
                            <input type="text" id="salary" name="salary" class="form-control rounded-pill shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('اجمالي التحصيلات') }}</label>
                            <input type="text" id="commission" name="commission" class="form-control rounded-pill shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('حافز البيع') }}</label>
                            <input type="text" id="transport_amount" name="transport_amount" class="form-control rounded-pill shadow-sm" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('مكافأة الالتزام') }}</label>
                            <input type="text" id="salary_of_visitors" name="salary_of_visitors" class="form-control rounded-pill shadow-sm" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('خصم') }}</label>
                            <input type="text" id="discount" name="discount" class="form-control rounded-pill shadow-sm" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>{{ \App\CPU\translate('بدلات أخرى') }}</label>
                            <input type="number" id="other" name="other" class="form-control rounded-pill shadow-sm" required>
                        </div>
                    </div>

                    <!-- Final Salary -->
                    <div class="mb-3">
                        <label>{{ \App\CPU\translate('الراتب النهائي') }}</label>
                        <input type="text" id="total" name="total" class="form-control" readonly>
                    </div>

                    <!-- New fields for Score, Note, and Note Manager -->
                    <div class="form-group mb-3">
                        <label>{{ \App\CPU\translate('التقييم') }}</label>
                        <input type="text" id="score" name="score" class="form-control" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label>{{ \App\CPU\translate('ملاحظة') }}</label>
                        <textarea id="note" name="note" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label>{{ \App\CPU\translate('ملاحظة المدير') }}</label>
                        <textarea id="notemanager" name="notemanager" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">{{ \App\CPU\translate('حفظ') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/global.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            function calculateTotal() {
                // Retrieve numeric fields
                let salary = parseFloat($('#salary').val()) || 0;
                let commission = parseFloat($('#commission').val()) || 0;
                let transportAmount = parseFloat($('#transport_amount').val()) || 0;
                let salaryOfVisitors = parseFloat($('#salary_of_visitors').val()) || 0;
                let discount = parseFloat($('#discount').val()) || 0;
                let other = parseFloat($('#other').val()) || 0;
                let expectedWorkHours = parseFloat($('#expected_work_hours').val()) || 0;
                let workedHours = parseFloat($('#worked_hours').val()) || 0;

                // Calculate expected deduction based on attendance hours (if expected hours > worked)
                let expectedDeduction = 0;
                if (expectedWorkHours > 0) {
                    expectedDeduction = (expectedWorkHours - workedHours) * (salary / expectedWorkHours);
                }
                $('#expected_deduction').val(expectedDeduction.toFixed(2));

                // Calculate total incentives: transportAmount + salaryOfVisitors + other
                let incentivesTotal = transportAmount + salaryOfVisitors + other;
                $('#incentives_total').val(incentivesTotal.toFixed(2));

                // Calculate total deductions: discount + taxes + insurance
                let taxes = parseFloat($('#taxes').val()) || 0;
                let insurance = parseFloat($('#insurance').val()) || 0;
                let deductionsTotal = discount + taxes + insurance;
                $('#deductions_total').val(deductionsTotal.toFixed(2));

                // Calculate final salary: salary + incentivesTotal - deductionsTotal
                let finalTotal = salary + incentivesTotal - deductionsTotal;
                $('#total').val(finalTotal.toFixed(2));
            }

            function fetchSalarySummary() {
                var sellerId = $('#seller_id').val();
                var month = $('#month').val();
                if (sellerId && month) {
                    $.ajax({
                        url: '{{ route("admin.salaries.showsalary_summary", [":sellerId", ":month"]) }}'
                            .replace(':sellerId', sellerId)
                            .replace(':month', month),
                        method: 'GET',
                        success: function(data) {
                            // Set the attendance summary values
                            $('#number_of_days').val(data.number_of_days);
                            $('#actual_work_days').val(data.actual_work_days);
                            $('#expected_work_hours').val(data.expected_work_hours);
                            $('#worked_hours').val(data.worked_hours);
                            $('#late_hours').val(data.time_late);

                            // Set additional salary details if available
                            $('#salary').val(data.salary);
                            $('#commission').val(data.commission);
                            $('#score').val(data.score); // show score (readonly)
                            $('#number_of_visitors').val(data.visitors);
                            $('#result_of_visitors').val(data.result_visitors);

                            calculateTotal();
                        },
                        error: function() {
                            alert('Error fetching salary summary.');
                        }
                    });
                } else {
                    $('#number_of_days, #actual_work_days, #expected_work_hours, #worked_hours, #late_hours, #salary, #commission, #score, #number_of_visitors, #result_of_visitors, #expected_deduction, #incentives_total, #deductions_total').val('');
                    calculateTotal();
                }
            }

            $('#seller_id, #month').change(fetchSalarySummary);
            $('#salary_of_visitors, #transport_amount, #discount, #other, #taxes, #insurance').on('input', calculateTotal);
        });
    </script>
@endpush
