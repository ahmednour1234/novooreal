<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.dash-stats-wrap { --dash-primary: #00296B; --dash-accent: #00509d; --dash-gold: #F8C01C; }
.dash-filter-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1.25rem; align-items: center; padding: 12px 16px; background: linear-gradient(145deg, rgba(255,255,255,.95) 0%, rgba(248,250,252,1) 100%); border-radius: 20px; box-shadow: 0 4px 24px rgba(0,41,107,.06), inset 0 1px 0 rgba(255,255,255,.8); border: 1px solid rgba(0,41,107,.06); }
.dash-filter-btn { padding: 10px 20px; border-radius: 14px; font-weight: 700; font-size: 0.9rem; text-decoration: none; border: 2px solid transparent; background: #fff; color: #475569; transition: all .3s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 2px 8px rgba(0,0,0,.04); }
.dash-filter-btn:hover { background: linear-gradient(135deg, var(--dash-primary), var(--dash-accent)); color: #fff; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,41,107,.25); }
.dash-filter-btn.active { background: linear-gradient(135deg, var(--dash-primary), var(--dash-accent)); color: #fff; border-color: rgba(255,255,255,.3); box-shadow: 0 6px 20px rgba(0,41,107,.3); }
.dash-filter-btn.loading { pointer-events: none; opacity: .8; }
.dash-kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.25rem; }
.dash-kpi-row-2 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.5rem; }
@media (max-width: 768px) { .dash-kpi-row, .dash-kpi-row-2 { grid-template-columns: 1fr; } }
.dash-kpi {
    background: linear-gradient(160deg, #ffffff 0%, #f8fafc 100%);
    color: #1a1d21;
    border-radius: 20px;
    padding: 1.35rem 1.4rem;
    box-shadow: 0 8px 32px rgba(0,41,107,.06), 0 2px 8px rgba(0,0,0,.02), inset 0 1px 0 rgba(255,255,255,.9);
    transition: transform .35s cubic-bezier(0.4,0,0.2,1), box-shadow .35s;
    border: 1px solid rgba(0,41,107,.06);
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
    overflow: hidden;
}
.dash-kpi::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: radial-gradient(circle, rgba(0,41,107,.04) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
.dash-kpi:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 20px 40px rgba(0,41,107,.12), 0 8px 16px rgba(0,0,0,.04); }
.dash-kpi-icon {
    width: 52px; height: 52px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(0,0,0,.12);
    transition: transform .3s;
}
.dash-kpi:hover .dash-kpi-icon { transform: scale(1.08); }
.dash-kpi:nth-child(1) .dash-kpi-icon { background: linear-gradient(145deg, #00296B, #00509d); color: #fff; }
.dash-kpi:nth-child(2) .dash-kpi-icon { background: linear-gradient(145deg, #0d9488, #14b8a6); color: #fff; }
.dash-kpi:nth-child(3) .dash-kpi-icon { background: linear-gradient(145deg, #7c3aed, #8b5cf6); color: #fff; }
.dash-kpi-row-2 .dash-kpi:nth-child(1) .dash-kpi-icon { background: linear-gradient(145deg, #ea580c, #f97316); color: #fff; }
.dash-kpi-row-2 .dash-kpi:nth-child(2) .dash-kpi-icon { background: linear-gradient(145deg, #dc2626, #ef4444); color: #fff; }
.dash-kpi-row-2 .dash-kpi:nth-child(3) .dash-kpi-icon { background: linear-gradient(145deg, #ca8a04, #eab308); color: #fff; }
.dash-kpi .kpi-label { font-size: 0.75rem; color: #64748b; margin-bottom: 6px; font-weight: 700; letter-spacing: .02em; }
.dash-kpi .kpi-value { font-size: 1.35rem; font-weight: 800; color: var(--dash-primary); letter-spacing: -.02em; transition: opacity .25s; }
.dash-kpi .kpi-value.loading { opacity: .5; }
.chart-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,41,107,.06), 0 2px 8px rgba(0,0,0,.02);
    overflow: hidden;
    transition: transform .3s, box-shadow .3s;
    border-right: 4px solid var(--dash-accent, #00509d);
}
.chart-card:hover { box-shadow: 0 16px 40px rgba(0,41,107,.1); transform: translateY(-2px); }
.chart-card .card-title {
    background: linear-gradient(135deg, #00296B 0%, #00509d 100%);
    color: white;
    padding: 14px 20px;
    font-weight: 700;
    font-size: 1.05rem;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.chart-card .card-title .card-title-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
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
$statsUrl = route('admin.dashboard.stats');
$useLivewireFilter = $useLivewireFilter ?? false;
@endphp
@if(empty($useLivewireFilter))
<div class="dash-stats-wrap" id="dashStatsLive">
    <div class="dash-filter-row">
        <button type="button" class="dash-filter-btn {{ $period === 'all' ? 'active' : '' }}" data-period="all">الكل</button>
        <button type="button" class="dash-filter-btn {{ $period === 'today' ? 'active' : '' }}" data-period="today">اليوم</button>
        <button type="button" class="dash-filter-btn {{ $period === 'week' ? 'active' : '' }}" data-period="week">آخر أسبوع</button>
        <button type="button" class="dash-filter-btn {{ $period === 'month' ? 'active' : '' }}" data-period="month">آخر شهر</button>
        <button type="button" class="dash-filter-btn {{ $period === 'year' ? 'active' : '' }}" data-period="year">آخر سنة</button>
    </div>
    <div class="dash-kpi-row">
        <div class="dash-kpi">
            <span class="dash-kpi-icon"><i class="tio-chart-pie-1"></i></span>
            <div><div class="kpi-label">إجمالي المبيعات</div><div class="kpi-value" data-key="total_sales">{{ number_format($total_sales) }}</div></div>
        </div>
        <div class="dash-kpi">
            <span class="dash-kpi-icon"><i class="tio-money"></i></span>
            <div><div class="kpi-label">مبيعات نقدية</div><div class="kpi-value" data-key="total_income">{{ number_format(round($total_income, 2)) }}</div></div>
        </div>
        <div class="dash-kpi">
            <span class="dash-kpi-icon"><i class="tio-calendar-month"></i></span>
            <div><div class="kpi-label">مبيعات آجلة</div><div class="kpi-value" data-key="total_expense">{{ number_format(round($total_expense, 2)) }}</div></div>
        </div>
    </div>
    <div class="dash-kpi-row-2">
        <div class="dash-kpi">
            <span class="dash-kpi-icon"><i class="tio-receipt"></i></span>
            <div><div class="kpi-label">التحصيلات</div><div class="kpi-value" data-key="total_installment">{{ number_format(round($total_installment, 2)) }}</div></div>
        </div>
        <div class="dash-kpi">
            <span class="dash-kpi-icon"><i class="tio-undo"></i></span>
            <div><div class="kpi-label">المرتجعات</div><div class="kpi-value" data-key="total_refund">{{ number_format(round($total_refund, 2)) }}</div></div>
        </div>
        <div class="dash-kpi">
            <span class="dash-kpi-icon"><i class="tio-trending-up"></i></span>
            <div><div class="kpi-label">صافي المبيعات</div><div class="kpi-value" data-key="net_sales">{{ number_format($net_sales) }}</div></div>
        </div>
    </div>
</div>
@endif
<script>
(function(){
    var wrap = document.getElementById('dashStatsLive');
    if (!wrap) return;
    var statsUrl = @json($statsUrl);
    var btns = wrap.querySelectorAll('.dash-filter-btn');
    var values = wrap.querySelectorAll('.kpi-value[data-key]');
    function formatNum(n) { return Number(n).toLocaleString('ar-EG', { maximumFractionDigits: 2, minimumFractionDigits: 0 }); }
    function setLoading(loading) {
        values.forEach(function(el){ el.classList.toggle('loading', loading); });
        btns.forEach(function(b){ b.classList.toggle('loading', loading); });
    }
    function applyData(data) {
        values.forEach(function(el){
            var key = el.getAttribute('data-key');
            if (data[key] != null) el.textContent = formatNum(data[key]);
        });
        if (data.charts && window.updateDashboardCharts) window.updateDashboardCharts(data.charts);
    }
    document.addEventListener('dashboardChartsUpdate', function(e){ if (e.detail && window.updateDashboardCharts) window.updateDashboardCharts(e.detail); });
    btns.forEach(function(btn){
        btn.addEventListener('click', function(){
            var period = this.getAttribute('data-period');
            btns.forEach(function(b){ b.classList.remove('active'); });
            this.classList.add('active');
            setLoading(true);
            fetch(statsUrl + '?period=' + encodeURIComponent(period), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(r){ return r.json(); })
                .then(function(data){ applyData(data); })
                .catch(function(){ })
                .finally(function(){ setLoading(false); });
        });
    });
})();
</script>

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
$accountw = $accountw ?? [];
$account = $account ?? [];
$total_income = is_object($account['total_income'] ?? null) ? ($account['total_income']->sum()) : (array_sum((array)($account['total_income'] ?? [])));
$total_expense = is_object($account['total_expense'] ?? null) ? ($account['total_expense']->sum()) : (array_sum((array)($account['total_expense'] ?? [])));
$total_refund = is_object($account['total_refund'] ?? null) ? ($account['total_refund']->sum()) : (array_sum((array)($account['total_refund'] ?? [])));
$total_installment = is_object($account['total_installment'] ?? null) ? ($account['total_installment']->sum()) : (array_sum((array)($account['total_installment'] ?? [])));

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

    window.dashboardCharts = window.dashboardCharts || {};
    var ctxBarq = document.getElementById('salesBarChart');
    var ctxPieq = document.getElementById('installmentPieChart');
    if (ctxBarq) {
        window.dashboardCharts.salesBarChart = new Chart(ctxBarq.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'إجمالي مبيعات أجل', data: expenseData, backgroundColor: '#efad0a', borderColor: '#efad0a', borderWidth: 1 },
                    { label: 'إجمالي مبيعات نقدي', data: incomeData, backgroundColor: '#3c4b96', borderColor: '#3c4b96', borderWidth: 1 },
                    { label: 'إجمالي الشبكة', data: shabakaData, backgroundColor: '#efad0a', borderColor: '#efad0a', borderWidth: 1 }
                ]
            },
            options: { responsive: true, scales: { y: { grid: { display: false }, ticks: { beginAtZero: true } }, x: { grid: { display: false } } }, plugins: { legend: { position: 'top' } } }
        });
    }
    if (ctxPieq) {
        window.dashboardCharts.installmentPieChart = new Chart(ctxPieq.getContext('2d'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{ data: installmentData, backgroundColor: 'rgba(173, 216, 230, 0.2)', borderColor: '#efad0a', borderWidth: 2, fill: true }]
            },
            options: { responsive: true, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { display: false } } } }
        });
    }
</script>


<script>
(function(){
    var labels = @json(array_column($cards, 'title'));
    var values = @json(array_column($cards, 'value'));
    var blueColors = ['#3c4b96', '#3c4b96', '#99ccff'];
    var backgroundColors = values.map(function(_, i){ return blueColors[i % blueColors.length]; });
    var ctx5 = document.getElementById('salesBarCharts');
    if (!ctx5) return;
    window.dashboardCharts = window.dashboardCharts || {};
    window.dashboardCharts.salesBarCharts = new Chart(ctx5.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'المبيعات', data: values, backgroundColor: backgroundColors, borderColor: backgroundColors, borderWidth: 1, barThickness: 15 }]
        },
        options: {
            responsive: true,
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { display: false } } },
            plugins: { legend: { display: false } }
        }
    });
    window.updateDashboardCharts = function(charts) {
        if (!charts || !window.dashboardCharts) return;
        if (charts.cards_labels && charts.cards_values && window.dashboardCharts.salesBarCharts) {
            window.dashboardCharts.salesBarCharts.data.labels = charts.cards_labels;
            window.dashboardCharts.salesBarCharts.data.datasets[0].data = charts.cards_values;
            window.dashboardCharts.salesBarCharts.data.datasets[0].backgroundColor = charts.cards_values.map(function(_, i){ return ['#3c4b96','#3c4b96','#99ccff'][i % 3]; });
            window.dashboardCharts.salesBarCharts.data.datasets[0].borderColor = window.dashboardCharts.salesBarCharts.data.datasets[0].backgroundColor;
            window.dashboardCharts.salesBarCharts.update('active');
        }
        if (charts.labels && window.dashboardCharts.salesBarChart) {
            window.dashboardCharts.salesBarChart.data.labels = charts.labels;
            window.dashboardCharts.salesBarChart.data.datasets[0].data = charts.expense || [];
            window.dashboardCharts.salesBarChart.data.datasets[1].data = charts.income || [];
            window.dashboardCharts.salesBarChart.data.datasets[2].data = charts.shabaka || [];
            window.dashboardCharts.salesBarChart.update('active');
        }
        if (charts.labels && window.dashboardCharts.installmentPieChart) {
            window.dashboardCharts.installmentPieChart.data.labels = charts.labels;
            window.dashboardCharts.installmentPieChart.data.datasets[0].data = charts.installment || [];
            window.dashboardCharts.installmentPieChart.update('active');
        }
        if (charts.labels && window.dashboardCharts.salesChart) {
            window.dashboardCharts.salesChart.data.labels = charts.labels;
            window.dashboardCharts.salesChart.data.datasets[0].data = charts.income || [];
            window.dashboardCharts.salesChart.data.datasets[1].data = charts.refund || [];
            window.dashboardCharts.salesChart.update('active');
        }
    };
})();
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

    window.dashboardCharts = window.dashboardCharts || {};
    window.dashboardCharts.salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                { label: 'المبيعات', data: Object.values(totalIncomeData), borderColor: '#3c4b96', backgroundColor: gradientIncome, borderWidth: 2, fill: true, tension: 0.4 },
                { label: 'المرتجعات', data: Object.values(totalRefundData), borderColor: '#efad0a', backgroundColor: gradientRefund, borderWidth: 2, fill: true, tension: 0.4 }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, padding: 20, boxWidth: 12, boxHeight: 10, font: { size: 12 } } },
                tooltip: { callbacks: { label: function(tooltipItem) { return tooltipItem.dataset.label + ': ' + tooltipItem.raw + ' {{ \App\CPU\Helpers::currency_symbol() }}'; } } }
            },
            scales: { y: { beginAtZero: true, grid: { display: false }, ticks: { display: true } }, x: { grid: { display: false }, ticks: { display: true } } }
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
