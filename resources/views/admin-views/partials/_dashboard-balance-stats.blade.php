<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .card-title {
        background-color: #3c4b96;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        font-size: 16px;
        margin-bottom: 20px;
    }

    .chart-container {
        position: relative;
        height: 100%;
    }

    .img-one-dash {
        width: 120px;
        opacity: 0.7;
    }
</style>

<div class="row mb-4">
    <!-- Salary & Credit Charts -->
    <div class="col-md-6">
        <div class="card shadow bg-white" style="min-height: 350px;">
            <div class="card-body d-flex flex-column justify-content-between">
                <h6 class="card-title">فرق المرتبات</h6>
                <div class="row d-flex align-items-center">
                    <div class="col-md-6">
                        <canvas id="salaryDoughnutChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="creditBalanceDoughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Bar Chart -->
    <div class="col-md-6">
        <div class="card shadow bg-white text-black" style="min-height: 350px;">
            <div class="card-body d-flex flex-column justify-content-between">
                <h6 class="card-title">مخطط بياني للمبيعات</h6>
                <canvas id="salesBarCharts"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Monthly Sales Bar Chart -->
    <div class="col-md-6 d-flex align-items-stretch">
        <div class="card shadow bg-white w-100">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">إجمالي المبيعات الشهرية</h6>
                <div class="chart-container">
                    <canvas id="salesBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Installment Pie Chart -->
    <div class="col-md-6 d-flex align-items-stretch">
        <div class="card shadow bg-white w-100">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">تحصيلات الأقساط الشهرية</h6>
                <div class="chart-container">
                    <canvas id="installmentPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Net Sales + Best Sellers -->
<div class="row mb-4">
    <!-- Net Sales Chart -->
    <div class="col-md-6">
        <div class="card shadow bg-white">
            <div class="card-body">
                <h6 class="card-title">صافي المبيعات (مبيعات ومرتجعات) لهذا العام</h6>
                <canvas id="salesChart" height="185"></canvas>
            </div>
        </div>
    </div>

    <!-- Best Sellers Table -->
    <div class="col-md-6">
        <div class="card shadow bg-white">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">{{ \App\CPU\translate('أفضل المناديب') }}</h6>
                <a class="btn btn-sm btn-primary" href="{{ route('admin.admin.list') }}">{{ \App\CPU\translate('رؤية المزيد') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ \App\CPU\translate('اسم المندوب') }}</th>
                                <th>{{ \App\CPU\translate('كود المندوب') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bestsellers as $key => $bestseller)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a class="text-primary" href="{{ route('admin.admin.list') }}">
                                            {{ $bestseller->f_name }}
                                        </a>
                                    </td>
                                    <td>{{ $bestseller->mandob_code }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="text-center p-4">
                                            <img class="mb-3 img-one-dash" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="No Data">
                                            <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات للعرض') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->




<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
// Ensure you are summing the collections before using them in calculations
$total_income = $account['total_income']->sum(); // Sum the collection of total_income
$total_expense = $account['total_expense']->sum(); // Sum the collection of total_expense
$total_refund = $account['total_refund']->sum(); // Sum the collection of total_refund
$total_installment = $account['total_installment']->sum(); // Sum the collection of total_installment

$cards = [
    ['title' => 'إجمالي المبيعات', 'value' => round($total_income + $total_expense, 2)],
    ['title' => 'أجمالي المبيعات النقدية', 'value' => round($total_income, 2)],
    ['title' => 'أجمالي المبيعات الآجلة', 'value' => round($total_expense, 2)],
    ['title' => 'أجمالي التحصيلات', 'value' => round($total_installment, 2)],
    ['title' => 'أجمالي المرتجعات الآجلة', 'value' => round($total_refund, 2)],
    ['title' => 'صافي المبيعات', 'value' => round($total_income + $total_expense - $total_refund, 2)],
];
@endphp
<script>
    // Debugging: Check if data exists
    console.log("Account Data:", {!! json_encode($accountw) !!});

    // Extract Data from Laravel (JSON encode to pass as JavaScript variables)
    var total_expense = {!! json_encode($accountw['total_expense'] ?? []) !!};
    var total_income = {!! json_encode($accountw['total_income'] ?? []) !!};
    var total_shabaka = {!! json_encode($accountw['total_shabaka'] ?? []) !!};
    var total_installment = {!! json_encode($accountw['total_installment'] ?? []) !!};

    // Define months (1 to 12)
    var months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

    // Extract data for 12 months (default to 0 if missing)
    var expenseData = months.map((_, i) => total_expense[i + 1] ?? 0);
    var incomeData = months.map((_, i) => total_income[i + 1] ?? 0);
    var shabakaData = months.map((_, i) => total_shabaka[i + 1] ?? 0);
    var installmentData = months.map((_, i) => total_installment[i + 1] ?? 0);

    // 12 Unique Colors for Months
    var monthColors = [
        '#efad0a', '#3c4b96', '#efad0a', '#3c4b96', '#efad0a', '#3c4b96',
        '#efad0a', '#3c4b96', '#efad0a', '#3c4b96', '#efad0a', '#3c4b96'
    ];

    var ctxBarq = document.getElementById('salesBarChart').getContext('2d');
    var ctxPieq = document.getElementById('installmentPieChart').getContext('2d');

    // Bar Chart: Sales Data by Month
    new Chart(ctxBarq, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'إجمالي مبيعات أجل',
                    data: expenseData,
                    backgroundColor: '#efad0a',
                    borderColor: '#efad0a',
                    borderWidth: 1
                },
                {
                    label: 'إجمالي مبيعات نقدي',
                    data: incomeData,
                    backgroundColor: '#3c4b96',
                    borderColor: '#3c4b96',
                    borderWidth: 1
                },
                {
                    label: 'إجمالي الشبكة',
                    data: shabakaData,
                    backgroundColor: '#efad0a',
                    borderColor: '#efad0a',
                    borderWidth: 1
                }
            ]
        },
options: {
    responsive: true,
    scales: {
        y: {
   grid: {
                        display: false  // This hides the grid lines on the y-axis
                    },
                    ticks: {
                beginAtZero: true
            }
        },
      x: {
                grid: {
                    display: false  // Hide the grid on the x-axis
                }
            }   
    },
     
    plugins: {
        legend: {
            position: 'top'
        }
    }
}
    });

    // Pie Chart: Installment Collections by Month
new Chart(ctxPieq, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            data: installmentData,
            backgroundColor: 'rgba(173, 216, 230, 0.2)',  // Light blue color with transparency
            borderColor: '#efad0a',  // Light blue color for the line
            borderWidth: 2,  // Make the line width a bit thicker
            fill: true  // Fill the area under the line
        }]
    },
    options: {
        responsive: true,
        plugins: {
        
        },
        scales: {
            x: {
                grid: {
                    display: false  // Hide the grid on the x-axis
                }
            },
            y: {
                beginAtZero: true,  // Start the Y-axis from zero
                grid: {
                    display: false  // Hide the grid on the y-axis
                }
            }
        }
    }
});
</script>


<script>
// Get data from PHP
const labels = @json(array_column($cards, 'title'));
const values = @json(array_column($cards, 'value'));

// Get the canvas context first
const ctx5 = document.getElementById('salesBarCharts').getContext('2d');

// درجات اللون الأزرق
const blueColors = ['#3c4b96', '#3c4b96', '#99ccff']; // كحلي، أزرق، لبني

// تحديد الألوان بشكل ديناميكي
const backgroundColors = values.map((_, index) => blueColors[index % blueColors.length]);

const salesBarChart = new Chart(ctx5, {
    type: 'bar',
    data: {
        labels: labels, // Labels from PHP array
        datasets: [{
            label: 'المبيعات',
            data: values, // Values from PHP array
            backgroundColor: backgroundColors, // الألوان
            borderColor: backgroundColors, // نفس اللون للحدود
            borderWidth: 1,
            barThickness: 15 // تحديد سمك الأعمدة (عرض الأعمدة)
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    display: false // إخفاء شبكة المحور X
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1000 // تعديل الخطوات إذا لزم الأمر
                },
                grid: {
                    display: false // إخفاء شبكة المحور Y
                }
            }
        },
        plugins: {
            legend: {
                display: false // إخفاء الأسطورة
            }
        }
    }
});
</script>
<script>
const previousSalaries = {{ $perviousSalaries }};
const currentSalaries = {{ $salaries }};
const sellersCredit = {{ $sellerscredit }};
const sellersBalance = {{ $sellersbalance }};

const ctx1 = document.getElementById('salaryDoughnutChart').getContext('2d');
const salaryDoughnutChart = new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['رواتب الشهر الماضي', 'رواتب الشهر الحالي'],
        datasets: [{
            data: [previousSalaries, currentSalaries],
            backgroundColor: ['#efad0a', '#3c4b96'],
            borderColor: ['#efad0a', '#3c4b96'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 20,
                    boxHeight: 20,
                }
            }
        },
        cutout: '0%' // دائرة كاملة بدون أي فراغ بالوسط
    }
});

const ctx2 = document.getElementById('creditBalanceDoughnutChart').getContext('2d');
const creditBalanceDoughnutChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['مديونية الموظفين', 'دائنين الموظفين'],
        datasets: [{
            data: [sellersCredit, sellersBalance],
            backgroundColor: ['#3c4b96', '#efad0a'],
            borderColor: ['#3c4b96', '#efad0a'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 20,
                    boxHeight: 20,
                }
            }
        },
        cutout: '0%' // دائرة كاملة
    }
});
</script>

<script>
    // Prepare the data for the chart
    var months = {!! json_encode($labels) !!};
    var totalIncomeData = {!! json_encode($accountw['total_income']) !!};
    var totalRefundData = {!! json_encode($accountw['total_refund']) !!};

    var ctx = document.getElementById('salesChart').getContext('2d');

    // Create gradient for sales data (green to white with reduced opacity)
    var gradientIncome = ctx.createLinearGradient(0, 0, 0, 400);
    gradientIncome.addColorStop(0, '#3c4b96'); // Green color
    gradientIncome.addColorStop(0.9, 'rgba(0, 171, 85, 0.1)'); // Lighter green with reduced opacity
    gradientIncome.addColorStop(1, 'rgba(255, 255, 255, 0.5)'); // White with reduced opacity

    // Create gradient for refund data (yellow to white with reduced opacity)
    var gradientRefund = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRefund.addColorStop(0, '#3c4b96'); // Yellow color
    gradientRefund.addColorStop(0.9, 'rgba(255, 171, 0, 0.16)'); // Lighter yellow with reduced opacity
    gradientRefund.addColorStop(1, 'rgba(255, 255, 255, 0.5)'); // White with reduced opacity

    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'المبيعات',  // Sales label first
                data: Object.values(totalIncomeData),  // Convert data to an array
                borderColor: '#3c4b96',  // Green color
                backgroundColor: gradientIncome,  // Apply gradient for sales data
                borderWidth: 2,
                fill: true,
                tension: 0.4,  // This creates the curve effect
                // Remove point style related properties
            }, {
                label: 'المرتجعات',  // Refund label second
                data: Object.values(totalRefundData),  // Convert data to an array
                borderColor: '#efad0a',  // Yellow color
                backgroundColor: gradientRefund,  // Apply gradient for refund data
                borderWidth: 2,
                fill: true,
                tension: 0.4,  // This creates the curve effect
                // Remove point style related properties
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',  // Position the legend at the top
                    align: 'end',  // Align legend to the right (end)
                    labels: {
                        usePointStyle: true,  // Make the label a circular point
                        padding: 20,  // Add space between the point and the label
                        boxWidth: 12,  // Adjust size of the legend circle
                        font: {
                            size: 12,  // Adjust font size
                        },
                        // Align legend items horizontally
                        boxHeight: 10,
                        boxWidth: 10,
                        padding: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label + ': ' + tooltipItem.raw + ' ' + '{{ \App\CPU\Helpers::currency_symbol() }}';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false  // This hides the grid lines on the y-axis
                    },
                    ticks: {
                        display: true  // Show the y-axis values
                    }
                },
                x: {
                    grid: {
                        display: false  // This hides the grid lines on the x-axis
                    },
                    ticks: {
                        display: true  // Show the x-axis values
                    }
                }
            }
        }
    });
</script>




<style>
    .s{
        font-size:25px;
        margin-right:8px;
    }
       /* Ensure both charts have the same size */
    .chart-container {
        position: relative;
        width: 100%;
        height: 300px; /* Adjust this value if needed */
    }

    canvas {
        width: 100% !important;
        height: 100% !important;
    }
</style>
