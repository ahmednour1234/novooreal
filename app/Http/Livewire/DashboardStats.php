<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\Installment;
use Carbon\Carbon;
use Livewire\Component;

class DashboardStats extends Component
{
    public $period = 'all';
    public $totals = [];
    public $charts = [];
    public $bestsellers = [];
    public $perviousSalaries = 0;
    public $salaries = 0;
    public $sellerscredit = 0;
    public $sellersbalance = 0;

    public function mount($bestsellers = [], $perviousSalaries = 0, $salaries = 0, $sellerscredit = 0, $sellersbalance = 0)
    {
        $this->bestsellers = $bestsellers;
        $this->perviousSalaries = $perviousSalaries;
        $this->salaries = $salaries;
        $this->sellerscredit = $sellerscredit;
        $this->sellersbalance = $sellersbalance;
        $this->loadData();
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->loadData();
        $this->dispatchBrowserEvent('dashboardChartsUpdate', $this->charts);
    }

    protected function loadData()
    {
        $to = Carbon::now()->endOfDay();
        $from = null;
        if ($this->period === 'today') {
            $from = Carbon::today();
        } elseif ($this->period === 'week') {
            $from = Carbon::now()->subWeek()->startOfDay();
        } elseif ($this->period === 'month') {
            $from = Carbon::now()->subMonth()->startOfDay();
        } elseif ($this->period === 'year') {
            $from = Carbon::now()->subYear()->startOfDay();
        }
        $baseOrder = $from ? Order::whereBetween('created_at', [$from, $to]) : Order::query();
        $total_income = (clone $baseOrder)->where('cash', 1)->where('type', 4)->sum('order_amount');
        $total_expense = (clone $baseOrder)->where('cash', 2)->where('type', 4)->sum('order_amount');
        $total_refund = (clone $baseOrder)->where('type', 7)->sum('order_amount');
        $total_installment = $from
            ? Installment::whereBetween('created_at', [$from, $to])->sum('total_price')
            : Installment::sum('total_price');
        $total_sales = $total_income + $total_expense;
        $net_sales = $total_sales - $total_refund;
        $this->totals = [
            'total_sales' => round($total_sales, 2),
            'total_income' => round($total_income, 2),
            'total_expense' => round($total_expense, 2),
            'total_installment' => round($total_installment, 2),
            'total_refund' => round($total_refund, 2),
            'net_sales' => round($net_sales, 2),
        ];
        $labels = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        $chartIncome = array_fill(0, 12, 0);
        $chartExpense = array_fill(0, 12, 0);
        $chartShabaka = array_fill(0, 12, 0);
        $chartRefund = array_fill(0, 12, 0);
        $chartInstallment = array_fill(0, 12, 0);
        if ($this->period === 'all' || $this->period === 'year' || !$from) {
            $year = ($this->period === 'year' && $from) ? $from->format('Y') : date('Y');
            for ($m = 1; $m <= 12; $m++) {
                $start = Carbon::createFromDate($year, $m, 1)->startOfDay();
                $end = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                if ($end->gt($to)) $end = $to;
                if ($start->gt($to)) continue;
                $o = Order::whereBetween('created_at', [$start, $end]);
                $chartIncome[$m - 1] = (clone $o)->where('cash', 1)->where('type', 4)->sum('order_amount');
                $chartExpense[$m - 1] = (clone $o)->where('cash', 2)->where('type', 4)->sum('order_amount');
                $chartShabaka[$m - 1] = (clone $o)->where('cash', 3)->where('type', 4)->sum('order_amount');
                $chartRefund[$m - 1] = (clone $o)->where('type', 7)->sum('order_amount');
                $chartInstallment[$m - 1] = Installment::whereBetween('created_at', [$start, $end])->sum('total_price');
            }
        } elseif ($this->period === 'month' || $this->period === 'week') {
            $days = $this->period === 'week' ? 7 : 30;
            $labels = [];
            $chartIncome = []; $chartExpense = []; $chartShabaka = []; $chartRefund = []; $chartInstallment = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $labels[] = $day->format('d/m');
                $start = $day->copy()->startOfDay();
                $end = $day->copy()->endOfDay();
                $o = Order::whereBetween('created_at', [$start, $end]);
                $chartIncome[] = (clone $o)->where('cash', 1)->where('type', 4)->sum('order_amount');
                $chartExpense[] = (clone $o)->where('cash', 2)->where('type', 4)->sum('order_amount');
                $chartShabaka[] = (clone $o)->where('cash', 3)->where('type', 4)->sum('order_amount');
                $chartRefund[] = (clone $o)->where('type', 7)->sum('order_amount');
                $chartInstallment[] = Installment::whereBetween('created_at', [$start, $end])->sum('total_price');
            }
        } else {
            $labels = [Carbon::now()->format('d/m')];
            $o = Order::whereDate('created_at', Carbon::today());
            $chartIncome = [(clone $o)->where('cash', 1)->where('type', 4)->sum('order_amount')];
            $chartExpense = [(clone $o)->where('cash', 2)->where('type', 4)->sum('order_amount')];
            $chartShabaka = [(clone $o)->where('cash', 3)->where('type', 4)->sum('order_amount')];
            $chartRefund = [(clone $o)->where('type', 7)->sum('order_amount')];
            $chartInstallment = [Installment::whereDate('created_at', Carbon::today())->sum('total_price')];
        }
        $this->charts = [
            'labels' => $labels,
            'income' => array_values($chartIncome),
            'expense' => array_values($chartExpense),
            'shabaka' => array_values($chartShabaka),
            'refund' => array_values($chartRefund),
            'installment' => array_values($chartInstallment),
            'cards_labels' => ['إجمالي المبيعات', 'مبيعات نقدية', 'مبيعات آجلة', 'التحصيلات', 'المرتجعات', 'صافي المبيعات'],
            'cards_values' => [$this->totals['total_sales'], $this->totals['total_income'], $this->totals['total_expense'], $this->totals['total_installment'], $this->totals['total_refund'], $this->totals['net_sales']],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
