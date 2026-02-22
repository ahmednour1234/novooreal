<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

use App\CPU\Helpers;
use function App\CPU\translate;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Transection;
use App\Models\Account;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\HistoryInstallment;
use App\Models\ReserveProduct;
use App\Models\CurrentReserveProduct;
use App\Models\ReserveProductNotification;
use App\Models\StockOrder;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\Branch;
use App\Models\SellerPrice;
use App\Models\Region;
use App\Models\CustomerPrice;
use App\Models\AdminSeller;
use App\Models\Transaction;
use App\Models\TransactionSeller;

use Illuminate\Pagination\LengthAwarePaginator;

class NotificationController extends Controller
{
    public function __construct(
        private Category $category,
        private Product $product,
        private Order $order,
        private Coupon $coupon,
        private Transection $transection,
        private TransactionSeller $TransactionSeller,
        private Region $regions,
        private Account $account,
        private OrderDetail $order_details,
        private StockOrder $stock_order,
        private Customer $customer,
        private CurrentReserveProduct $current_reserve_products,
        private HistoryInstallment $installment,
        private ReserveProduct $reserveProduct,
        private ReserveProductNotification $reserveProductNotification,
    ) {}

    /**
     * Central permission check (Role->data contains allowed routes/permissions).
     */
    private function ensurePermission(string $permissionKey): bool
    {
        $adminId = Auth::guard('admin')->id();
        $admin = DB::table('admins')->where('id', $adminId)->first();

        if (!$admin) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return false;
        }

        $role = DB::table('roles')->where('id', $admin->role_id)->first();
        if (!$role) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return false;
        }

        $decodedData = json_decode($role->data, true);
        if (is_string($decodedData)) {
            $decodedData = json_decode($decodedData, true);
        }

        if (!is_array($decodedData)) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return false;
        }

        if (!in_array($permissionKey, $decodedData)) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return false;
        }

        return true;
    }

    /**
     * Mark as read WITHOUT redirect (safe to call internally).
     */
    private function markAsReadInternal(int $id, string $type): void
    {
        $notification = null;

        if ($type === 'order' || $type === 'refundOrder') {
            $notification = $this->order->where('id', $id)->first();
        } elseif ($type === 'installment') {
            $notification = $this->installment->where('id', $id)->first();
        } elseif ($type === 'reserveProduct' || $type === 'reReserveProduct') {
            $notification = $this->reserveProduct->where('id', $id)->first();
        }

        if ($notification && (int) $notification->notification === 1) {
            $notification->notification = 0;
            $notification->save();
        }
    }

    /**
     * Keep your original route behavior if you still need a public markAsRead action.
     */
    public function markAsRead($id, $type): RedirectResponse
    {
        $this->markAsReadInternal((int) $id, (string) $type);

        return redirect()->route('admin.admin.notifications.show', [
            'id' => $id,
            'type' => $type,
        ]);
    }

    public function listItems(Request $request)
    {
        if (!$this->ensurePermission('notification.index')) {
            return redirect()->back();
        }

        $adminId = Auth::guard('admin')->id();
        $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

        $refundOrders = $this->order
            ->where('notification', 1)
            ->where('type', 7)
            ->get()
            ->map(function ($order) {
                $order->notification_type = 'refundOrder';
                return $order;
            });

        $orders = $this->order
            ->where('notification', 1)
            ->where('type', 4)
            ->get()
            ->map(function ($order) {
                $order->notification_type = 'order';
                return $order;
            });

        $installments = $this->installment
            ->where('notification', 1)
            ->get()
            ->map(function ($installment) {
                $installment->notification_type = 'installment';
                return $installment;
            });

        // NOTE: you had no notification filter here originally, keeping same behavior.
        $transactionSellers = $this->TransactionSeller
            ->get()
            ->map(function ($transaction) {
                $transaction->notification_type = 'transaction';
                return $transaction;
            });

        $reserveProducts = $this->reserveProduct
            ->where('notification', 1)
            ->where('type', 4)
            ->whereIn('seller_id', $sellerIds)
            ->get()
            ->map(function ($reserveProduct) {
                $reserveProduct->notification_type = 'reserveProduct';
                return $reserveProduct;
            });

        $reReserveProducts = $this->reserveProduct
            ->where('notification', 1)
            ->where('type', 7)
            ->whereIn('seller_id', $sellerIds)
            ->get()
            ->map(function ($reReserveProduct) {
                $reReserveProduct->notification_type = 'reReserveProduct';
                return $reReserveProduct;
            });

        $merged = collect()
            ->merge($orders)
            ->merge($refundOrders)
            ->merge($installments)
            ->merge($transactionSellers)
            ->merge($reserveProducts)
            ->merge($reReserveProducts)
            ->sortByDesc('created_at')
            ->values();

        $currentPage = (int) request()->get('page', 1);
        $perPage = Helpers::pagination_limit();

        $paginatedNotifications = new LengthAwarePaginator(
            $merged->forPage($currentPage, $perPage),
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return view('admin-views.Notification.index', compact('paginatedNotifications'));
    }

    public function showItemById($id, $type)
    {
        // Mark as read first (no redirect).
        $this->markAsReadInternal((int) $id, (string) $type);

        $search   = request('search', '');
        $fromDate = request('from_date', '');
        $toDate   = request('to_date', '');

        $regions  = $this->regions->get();
        $regionId = 1;

        $sellers   = Seller::query()->get();
        $customers = Customer::query()->get();
        $branches  = Branch::query()->get();

        // ✅ FIX: define $accounts once and pass when needed
        $accounts  = Account::query()->get();

        $seller_id   = '';
        $customer_id = '';
        $branch_id   = '';

        switch ($type) {
            case 'order':
                if (!$this->ensurePermission('notification4.index')) {
                    return redirect()->back();
                }

                $notification = $this->order->with(['customer', 'seller'])->find($id);
                if (!$notification) break;

                $orderAmountSum   = 0;
                $collectedCashSum = 0;
                $productCount     = 0;
                $quantitySum      = 0;

                $orders = Order::where('id', $id)
                    ->when($fromDate && $toDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
                    ->paginate(Helpers::pagination_limit())
                    ->appends([
                        'search' => $search,
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                    ]);

                return view('admin-views.pos.order.list', compact(
                    'orders',
                    'search',
                    'fromDate',
                    'toDate',
                    'regions',
                    'regionId',
                    'orderAmountSum',
                    'collectedCashSum',
                    'productCount',
                    'quantitySum',
                    'type',
                    'sellers',
                    'seller_id',
                    'customers',
                    'customer_id',
                    'branches',
                    'branch_id'
                ));

            case 'refundOrder':
                if (!$this->ensurePermission('notification7.index')) {
                    return redirect()->back();
                }

                $notification = $this->order->with(['customer', 'seller'])->find($id);
                if (!$notification) break;

                $orderAmountSum   = 0;
                $collectedCashSum = 0;
                $productCount     = 0;
                $quantitySum      = 0;

                $orders = Order::where('id', $id)
                    ->when($fromDate && $toDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
                    ->paginate(Helpers::pagination_limit())
                    ->appends([
                        'search' => $search,
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                    ]);

                return view('admin-views.pos.order.list', compact(
                    'orders',
                    'search',
                    'fromDate',
                    'toDate',
                    'regions',
                    'regionId',
                    'orderAmountSum',
                    'collectedCashSum',
                    'productCount',
                    'quantitySum',
                    'type',
                    'sellers',
                    'seller_id',
                    'customers',
                    'customer_id',
                    'branches',
                    'branch_id'
                ));

            case 'installment':
                if (!$this->ensurePermission('notification13.index')) {
                    return redirect()->back();
                }

                $notification = $this->installment->with(['customer', 'seller'])->find($id);
                if (!$notification) break;

                $totalAmount = 0;

                $installments = HistoryInstallment::where('id', $id)
                    ->when($fromDate && $toDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
                    ->paginate(Helpers::pagination_limit())
                    ->appends([
                        'search' => $search,
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                    ]);

                return view('admin-views.pos.installment.list', compact(
                    'installments',
                    'search',
                    'fromDate',
                    'toDate',
                    'regions',
                    'regionId',
                    'totalAmount',
                    'sellers',
                    'seller_id',
                    'customers',
                    'customer_id',
                    'branches',
                    'branch_id'
                ));

            case 'reserveProduct':
                if (!$this->ensurePermission('notification41.index')) {
                    return redirect()->back();
                }

                $notification = $this->reserveProduct->with(['customer', 'seller'])->find($id);
                if (!$notification) break;

                $reservations = ReserveProduct::where('id', $id)
                    ->when($fromDate && $toDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
                    ->paginate(Helpers::pagination_limit())
                    ->appends([
                        'search' => $search,
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                    ]);

                return view('admin-views.pos.reservations.list_notification', compact(
                    'reservations',
                    'search',
                    'fromDate',
                    'toDate',
                    'regions',
                    'regionId',
                    'type',
                    'sellers',
                    'seller_id',
                    'customers',
                    'customer_id',
                    'branches',
                    'branch_id'
                ));

            case 'transaction':
                if (!$this->ensurePermission('notification500.index')) {
                    return redirect()->back();
                }

                $notification = $this->TransactionSeller->find($id);
                if (!$notification) break;

                $transactions = TransactionSeller::where('id', $id)
                    ->when($fromDate && $toDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
                    ->paginate(Helpers::pagination_limit())
                    ->appends([
                        'search' => $search,
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                    ]);

                // ✅ هنا غالبًا كان الـ view عايز $accounts
                return view('admin-views.transaction_sellers.index', compact(
                    'transactions',
                    'search',
                    'fromDate',
                    'toDate',
                    'regions',
                    'regionId',
                    'sellers',
                    'branches',
                    'branch_id',
                    'accounts'
                ));

            default:
                break;
        }

        Toastr::error(translate('Notification not found'));
        return redirect()->back();
    }
}
