<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.dash-filter-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; align-items: center; }
.dash-filter-btn { padding: 8px 16px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none; border: 1px solid rgba(0,41,107,.15); background: #fff; color: #00296B; transition: all .2s; }
.dash-filter-btn:hover { background: #00296B; color: #fff; }
.dash-filter-btn.active { background: linear-gradient(135deg, #00296B, #00509d); color: #fff; border-color: transparent; }
.dash-kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem; }
.dash-kpi-row-2 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 768px) { .dash-kpi-row, .dash-kpi-row-2 { grid-template-columns: 1fr; } }
.dash-kpi {
    background: #fff;
    color: #1a1d21;
    border-radius: 16px;
    padding: 1.1rem 1.2rem;
    box-shadow: 0 4px 20px rgba(0,41,107,.08);
    transition: transform .2s, box-shadow .2s;
    border: 1px solid rgba(0,41,107,.06);
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.dash-kpi:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,41,107,.14); }
.dash-kpi-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.dash-kpi:nth-child(1) .dash-kpi-icon { background: linear-gradient(135deg, #00296B, #00509d); color: #fff; }
.dash-kpi:nth-child(2) .dash-kpi-icon { background: linear-gradient(135deg, #0d9488, #14b8a6); color: #fff; }
.dash-kpi:nth-child(3) .dash-kpi-icon { background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff; }
.dash-kpi:nth-child(4) .dash-kpi-icon { background: linear-gradient(135deg, #ea580c, #f97316); color: #fff; }
.dash-kpi:nth-child(5) .dash-kpi-icon { background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; }
.dash-kpi:nth-child(6) .dash-kpi-icon { background: linear-gradient(135deg, #ca8a04, #eab308); color: #fff; }
.dash-kpi .kpi-label { font-size: 0.72rem; color: #6b7280; margin-bottom: 4px; font-weight: 600; }
.dash-kpi .kpi-value { font-size: 1.2rem; font-weight: 700; color: #00296B; }
.chart-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,41,107,.08);
    overflow: hidden;
    transition: box-shadow .25s;
    border-right: 4px solid #00509d;
}
.chart-card:hover { box-shadow: 0 8px 32px rgba(0,41,107,.12); }
.chart-card .card-title {
    background: linear-gradient(135deg, #00296B 0%, #00509d 100%);
    color: white;
    padding: 12px 18px;
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.chart-card .card-title .card-title-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
}
.chart-container { position: relative; height: 100%; min-height: 260px; }
.img-one-dash { width: 120px; opacity: 0.7; }
</style>

@php
if (!empty($totals_filtered)) {
    $total_sales = $totals_filtered['total_sales'] ?? 0;
    $total_income = $totals_filtered['total_income'] ?? 0;
    $total_expense = $totals_filtered['total_expense'] ?? 0;
    $total_installment = $totals_filtered['total_installment'] ?? 0;
    $total_refund = $totals_filtered['total_refund'] ?? 0;
    $net_sales = $totals_filtered['net_sales'] ?? 0;
} else {
    $total_income = is_object($account['total_income']) ? $account['total_income']->sum() : array_sum((array)$account['total_income']);
    $total_expense = is_object($account['total_expense']) ? $account['total_expense']->sum() : array_sum((array)$account['total_expense']);
    $total_refund = is_object($account['total_refund']) ? $account['total_refund']->sum() : array_sum((array)$account['total_refund']);
    $total_installment = is_object($account['total_installment']) ? $account['total_installment']->sum() : array_sum((array)$account['total_installment']);
    $net_sales = round($total_income + $total_expense - $total_refund, 2);
    $total_sales = round($total_income + $total_expense, 2);
}
$period = $period ?? 'all';
$dashboardUrl = route('admin.dashboard');
@endphp
<div class="dash-filter-row">
    <a href="{{ $dashboardUrl }}" class="dash-filter-btn {{ $period === 'all' ? 'active' : '' }}">الكل</a>
    <a href="{{ $dashboardUrl }}?period=today" class="dash-filter-btn {{ $period === 'today' ? 'active' : '' }}">اليوم</a>
    <a href="{{ $dashboardUrl }}?period=week" class="dash-filter-btn {{ $period === 'week' ? 'active' : '' }}">آخر أسبوع</a>
    <a href="{{ $dashboardUrl }}?period=month" class="dash-filter-btn {{ $period === 'month' ? 'active' : '' }}">آخر شهر</a>
    <a href="{{ $dashboardUrl }}?period=year" class="dash-filter-btn {{ $period === 'year' ? 'active' : '' }}">آخر سنة</a>
</div>
<div class="dash-kpi-row">
    <div class="dash-kpi">
        <span class="dash-kpi-icon"><i class="tio-chart-pie-1"></i></span>
        <div><div class="kpi-label">إجمالي المبيعات</div><div class="kpi-value">{{ number_format($total_sales) }}</div></div>
    </div>
    <div class="dash-kpi">
        <span class="dash-kpi-icon"><i class="tio-money"></i></span>
        <div><div class="kpi-label">مبيعات نقدية</div><div class="kpi-value">{{ number_format(round($total_income, 2)) }}</div></div>
    </div>
    <div class="dash-kpi">
        <span class="dash-kpi-icon"><i class="tio-calendar-month"></i></span>
        <div><div class="kpi-label">مبيعات آجلة</div><div class="kpi-value">{{ number_format(round($total_expense, 2)) }}</div></div>
    </div>
</div>
<div class="dash-kpi-row-2">
    <div class="dash-kpi">
        <span class="dash-kpi-icon"><i class="tio-receipt"></i></span>
        <div><div class="kpi-label">التحصيلات</div><div class="kpi-value">{{ number_format(round($total_installment, 2)) }}</div></div>
    </div>
    <div class="dash-kpi">
        <span class="dash-kpi-icon"><i class="tio-undo"></i></span>
        <div><div class="kpi-label">المرتجعات</div><div class="kpi-value">{{ number_format(round($total_refund, 2)) }}</div></div>
    </div>
    <div class="dash-kpi">
        <span class="dash-kpi-icon"><i class="tio-trending-up"></i></span>
        <div><div class="kpi-label">صافي المبيعات</div><div class="kpi-value">{{ number_format($net_sales) }}</div></div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card chart-card" style="min-height: 350px;">
            <div class="card-body d-flex flex-column justify-content-between">
                <h6 class="card-title"><span class="card-title-icon"><i class="tio-dollar"></i></span> فرق المرتبات</h6>
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

    <div class="col-md-6">
        <div class="card chart-card text-black" style="min-height: 350px;">
            <div class="card-body d-flex flex-column justify-content-between">
                <h6 class="card-title"><span class="card-title-icon"><i class="tio-chart-bar-4"></i></span> مخطط بياني للمبيعات</h6>
                <canvas id="salesBarCharts"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 d-flex align-items-stretch">
        <div class="card chart-card w-100">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title"><span class="card-title-icon"><i class="tio-chart-pie-1"></i></span> إجمالي المبيعات الشهرية</h6>
                <div class="chart-container">
                    <canvas id="salesBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 d-flex align-items-stretch">
        <div class="card chart-card w-100">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title"><span class="card-title-icon"><i class="tio-receipt"></i></span> تحصيلات الأقساط الشهرية</h6>
                <div class="chart-container">
                    <canvas id="installmentPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card chart-card">
            <div class="card-body">
                <h6 class="card-title"><span class="card-title-icon"><i class="tio-trending-up"></i></span> صافي المبيعات (مبيعات ومرتجعات) لهذا العام</h6>
                <canvas id="salesChart" height="185"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card chart-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><span class="card-title-icon"><i class="tio-star"></i></span> {{ \App\CPU\translate('أفضل المناديب') }}</h6>
                <a class="btn btn-sm btn-primary" href="{{ route('admin.admin.list') }}"><i class="tio-arrow-forward"></i> {{ \App\CPU\translate('رؤية المزيد') }}</a>
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
