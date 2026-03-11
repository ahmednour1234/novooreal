<div class="dash-stats-wrap" wire:key="dashboard-stats-{{ $period }}">
    <div class="dash-filter-row">
        @foreach(['all' => 'الكل', 'today' => 'اليوم', 'week' => 'آخر أسبوع', 'month' => 'آخر شهر', 'year' => 'آخر سنة'] as $p => $label)
        <button type="button" class="dash-filter-btn {{ $period === $p ? 'active' : '' }}" wire:click="setPeriod('{{ $p }}')">{{ $label }}</button>
        @endforeach
    </div>
    <div class="dash-kpi-row">
        <div class="dash-kpi"><span class="dash-kpi-icon"><i class="tio-chart-pie-1"></i></span><div><div class="kpi-label">إجمالي المبيعات</div><div class="kpi-value">{{ number_format($totals['total_sales'] ?? 0) }}</div></div></div>
        <div class="dash-kpi"><span class="dash-kpi-icon"><i class="tio-money"></i></span><div><div class="kpi-label">مبيعات نقدية</div><div class="kpi-value">{{ number_format($totals['total_income'] ?? 0) }}</div></div></div>
        <div class="dash-kpi"><span class="dash-kpi-icon"><i class="tio-calendar-month"></i></span><div><div class="kpi-label">مبيعات آجلة</div><div class="kpi-value">{{ number_format($totals['total_expense'] ?? 0) }}</div></div></div>
    </div>
    <div class="dash-kpi-row-2">
        <div class="dash-kpi"><span class="dash-kpi-icon"><i class="tio-receipt"></i></span><div><div class="kpi-label">التحصيلات</div><div class="kpi-value">{{ number_format($totals['total_installment'] ?? 0) }}</div></div></div>
        <div class="dash-kpi"><span class="dash-kpi-icon"><i class="tio-undo"></i></span><div><div class="kpi-label">المرتجعات</div><div class="kpi-value">{{ number_format($totals['total_refund'] ?? 0) }}</div></div></div>
        <div class="dash-kpi"><span class="dash-kpi-icon"><i class="tio-trending-up"></i></span><div><div class="kpi-label">صافي المبيعات</div><div class="kpi-value">{{ number_format($totals['net_sales'] ?? 0) }}</div></div></div>
    </div>
</div>
