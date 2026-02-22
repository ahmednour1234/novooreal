<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\PosSession;
use App\Models\Product;
use App\Models\Admin;
use App\Models\Order;
use App\CPU\Helpers;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use function App\CPU\translate;

class POSSessionController extends Controller
{
        public function index(Request $request)
    {
        $query = PosSession::query()->with(['admin', 'branch'])->orderBy('start_time', 'desc');

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('start_time', [$request->from_date, $request->to_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('admin_id')) {
            $query->where('user_id', $request->admin_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $sessions = $query->paginate(20);
        $admins = Admin::where('role','admin')->get();
        $branches = Branch::all();

        return view('admin-views.pos.sessions.index', compact('sessions', 'admins', 'branches'));
    }
    public function openSession(Request $request)
{
    // جلب المستخدم
    $user = Auth::guard('admin')->user();

    // تحقق من وجود المستخدم
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'لم يتم العثور على المستخدم.'
        ]);
    }
    // تحقق من كلمة المرور
    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'كلمة المرور غير صحيحة.'
        ]);
    }

    // هل يوجد جلسة مفتوحة بالفعل؟
    $existingSession = PosSession::where('user_id', $user->id)
        ->where('status', 'open')
        ->first();

    if ($existingSession) {
        return response()->json([
            'status' => false,
            'message' => 'يوجد جلسة مفتوحة بالفعل!',
            'session_id' => $existingSession->id
        ]);
    }

    // إنشاء جلسة جديدة
    $session = PosSession::create([
        'user_id' => $user->id,
        'branch_id' => $user->branch_id ?? null, // تأكد إذا عندك فروع
        'start_time' => Carbon::now(),
        'status' => 'open',
    ]);

    return response()->json([
        'status' => true,
        'message' => 'تم فتح الجلسة بنجاح',
        'session_id' => $session->id
    ]);
}
public function getCurrentSession()
{
    $user = Auth::guard('admin')->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'لم يتم العثور على المستخدم.'
        ]);
    }

    $session = PosSession::where('user_id', $user->id)
        ->where('status', 'open')
        ->first();

    if (!$session) {
        return response()->json([
            'status' => false,
            'message' => 'لا توجد جلسة حالية مفتوحة.'
        ]);
    }
$orders = DB::table('orders')
    ->where('session_id', $session->id)
    ->selectRaw('
        SUM(CASE WHEN type = 4 THEN 1 ELSE 0 END) as total_orders,
        SUM(CASE WHEN type = 7 THEN 1 ELSE 0 END) as total_returns,
        SUM(CASE WHEN type = 4 THEN order_amount ELSE 0 END) as total_amount,
        SUM(CASE WHEN type = 7 THEN order_amount ELSE 0 END) as total_amount_returns,
        SUM(CASE WHEN cash = 2 THEN order_amount ELSE 0 END) as total_credit,
        SUM(extra_discount) as total_discount
    ')
    ->first();



    return response()->json([
        'status' => true,
        'session' => [
            'start_time' => $session->start_time,
            'status' => $session->status,
            'total_orders' => number_format($orders->total_orders,2) ?? 0,
            'total_returns' => number_format($orders->total_returns,2) ?? 0,
            'total_amount' => number_format($orders->total_amount,2) ?? 0,
            'total_amount_returns' => number_format($orders->total_amount_returns,2) ?? 0,
            'total_credit' => number_format($orders->total_credit,2) ?? 0,
            'total_discount' => number_format($orders->total_discount,2) ?? 0,
            'safy'=>number_format(number_format($orders->total_amount,2)-number_format($orders->total_amount_returns,2),2)??0,
            'currency' => 'ر.س' // غيّر حسب العملة في نظامك
        ]
    ]);
}
public function closeSession()
{
    $user = Auth::guard('admin')->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'المستخدم غير موجود.'
        ]);
    }

    // الحصول على الجلسة المفتوحة
    $session = PosSession::where('user_id', $user->id)
        ->where('status', 'open')
        ->first();

    if (!$session) {
        return response()->json([
            'status' => false,
            'message' => 'لا توجد جلسة مفتوحة حالياً.'
        ]);
    }
$orders = DB::table('orders')
    ->where('session_id', $session->id)
    ->selectRaw('
        SUM(CASE WHEN type = 4 THEN 1 ELSE 0 END) as total_orders,
        SUM(CASE WHEN type = 7 THEN 1 ELSE 0 END) as total_returns,
        SUM(CASE WHEN type = 4 THEN collected_cash ELSE 0 END) as total_collected_cash,
        SUM(CASE WHEN type = 4 THEN order_amount ELSE 0 END) as total_amount,
        SUM(CASE WHEN type = 7 THEN order_amount ELSE 0 END) as total_amount_returns,
        SUM(CASE WHEN cash = 2 THEN order_amount ELSE 0 END) as total_credit,
        SUM(extra_discount) as total_discount
    ')
    ->first();
  

    // إغلاق الجلسة
    $session->update([
        'end_time' => Carbon::now(),
        'status' => 'closed',
        'total_cash'         => $orders->total_collected_cash ?? 0,
       'total_credit' =>  0,
       'total_returns'=>$orders->total_returns,
       'total_orders'=>$orders->total_orders,
       'total_amount_returns'=>$orders->total_amount_returns,
       'total_amount'=>$orders->total_amount,
      'total_discount'       => $orders->total_discount ?? 0,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'تم إغلاق الجلسة بنجاح.',
        'session_id' => $session->id,
        'summary' => [
            'total_cash'         => $orders->total_collected_cash ?? 0,
            'total_credit' =>  0,
            'total_discount'       => $orders->total_discount ?? 0,
        ]
    ]);
}
public function getSessionInvoices()
{
    $user = Auth::guard('admin')->user();

    if (!$user) {
        return response()->json(['status' => false, 'message' => 'المستخدم غير موجود.']);
    }

    $session = PosSession::where('user_id', $user->id)
        ->where('status', 'open')
        ->first();

    if (!$session) {
        return response()->json(['status' => false, 'message' => 'لا توجد جلسة حالياً.']);
    }

    $orders = DB::table('orders')
        ->where('session_id', $session->id)
        ->orderByDesc('id')
        ->select('id as invoice_no', 'order_amount as total_amount', 'created_at as date','type as type')
        ->get();

    return response()->json([
        'status' => true,
        'invoices' => $orders->map(function ($order) {
            return [
'type' => $order->type ,
                'invoice_no' => $order->invoice_no,
                'total_amount' => number_format($order->total_amount, 2),
                'date' => \Carbon\Carbon::parse($order->date)->format('Y-m-d H:i'),
            ];
        })
    ]);
}

// ProductController.php
public function ajaxDetails($id)
{
    $product = Product::with('taxe','unit.subUnits')->find($id);
    if (!$product) {
        return response()->json(['status' => false]);
    }

    return response()->json([
        'status' => true,
        'product' => $product
    ]);
}
public function order_list(Request $request)
{
    // تفحص صلاحيات المسؤول
    $admin = Auth::guard('admin')->user();
    if (! $admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $role = DB::table('roles')->where('id', $admin->role_id)->first();
    if (! $role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $permissions = json_decode($role->data, true);
    if (is_string($permissions)) {
        $permissions = json_decode($permissions, true);
    }
    if (! is_array($permissions) || ! in_array('order4.index', $permissions)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // مدخلات الفلترة
    $search      = $request->input('search');
    $fromDate    = $request->input('from_date');
    $toDate      = $request->input('to_date');
    $regionId    = $request->input('region_id');
    $seller_id   = $request->input('seller_id');
    $customer_id = $request->input('customer_id');
    $branch_id   = $request->input('branch_id');
    $done        = $request->input('done');       // 1 أو 0
    $type        = $request->input('type');       // نوع العميل

    // نحسب التاريخ النهاية +1 يوم لإشراك اليوم الأخير
    $toNewDate = $toDate
        ? \Carbon\Carbon::parse($toDate)->addDay()->format('Y-m-d')
        : null;

    // جلب جميع IDs للبائعين المرتبطين بالمسؤول


    // بناء استعلام الأوامر
    $ordersQuery = Order::with(['customer', 'seller', 'details', 'branch'])
        ->whereIn('type', [4, 1])->wherenotnull('session_id')
        ->latest()
        ->when($search, function($q) use ($search) {
            $q->where('id', 'like', "%{$search}%");
        })
        ->when($customer_id, function($q) use ($customer_id) {
            $q->where('user_id', $customer_id);
        })
        ->when($branch_id, function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })
        ->when($seller_id, function($q) use ($seller_id) {
            $q->where('owner_id', $seller_id);
        })
        ->when($fromDate && $toNewDate, function($q) use ($fromDate, $toNewDate) {
            $q->whereBetween('created_at', [$fromDate, $toNewDate]);
        })
    
        ->when(! is_null($done), function($q) use ($done) {
            $q->where('done', $done);
        });

    // جلب النتائج لحساب المجموعات
    $allFiltered = (clone $ordersQuery)->get();

    $orderAmountSum   = $allFiltered->sum('order_amount');
    $collectedCashSum = $allFiltered->sum('collected_cash');
    $quantitySum      = $allFiltered->sum(fn($o) => $o->details->sum('quantity'));
    $productCount     = $allFiltered->sum(fn($o) => $o->details->count());

    // ترقيم الصفحات
    $orders = $ordersQuery
        ->paginate(Helpers::pagination_limit())
        ->appends($request->only([
            'search','from_date','to_date',
            'region_id','seller_id','customer_id',
            'branch_id','done','type'
        ]));

    // بيانات القوائم المنسدلة
    $sellers   = Admin::where('role','admin')->get();
    $branches  = Branch::all();

    // بيانات الكائنات المفردة للعرض
    $sellerw   = Admin::find($seller_id);
    $branchw   = Branch::find($branch_id);

    return view('admin-views.pos.sessions.orders', compact(
        'orders','search','fromDate','toDate',
        'regionId','orderAmountSum','collectedCashSum',
        'quantitySum','productCount','done','type',
        'sellers','branches',
        'sellerw','branchw',
        'seller_id','customer_id','branch_id'
    ));
}
public function returns_list(Request $request)
{
    // تفحص صلاحيات المسؤول
    $admin = Auth::guard('admin')->user();
    if (! $admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $role = DB::table('roles')->where('id', $admin->role_id)->first();
    if (! $role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $permissions = json_decode($role->data, true);
    if (is_string($permissions)) {
        $permissions = json_decode($permissions, true);
    }
    if (! is_array($permissions) || ! in_array('order4.index', $permissions)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // مدخلات الفلترة
    $search      = $request->input('search');
    $fromDate    = $request->input('from_date');
    $toDate      = $request->input('to_date');
    $regionId    = $request->input('region_id');
    $seller_id   = $request->input('seller_id');
    $customer_id = $request->input('customer_id');
    $branch_id   = $request->input('branch_id');
    $done        = $request->input('done');       // 1 أو 0
    $type        = $request->input('type');       // نوع العميل

    // نحسب التاريخ النهاية +1 يوم لإشراك اليوم الأخير
    $toNewDate = $toDate
        ? \Carbon\Carbon::parse($toDate)->addDay()->format('Y-m-d')
        : null;

    // جلب جميع IDs للبائعين المرتبطين بالمسؤول


    // بناء استعلام الأوامر
    $ordersQuery = Order::with(['customer', 'seller', 'details', 'branch'])
        ->where('type',7 )->wherenotnull('session_id')
        ->latest()
        ->when($search, function($q) use ($search) {
            $q->where('id', 'like', "%{$search}%");
        })
        ->when($customer_id, function($q) use ($customer_id) {
            $q->where('user_id', $customer_id);
        })
        ->when($branch_id, function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })
        ->when($seller_id, function($q) use ($seller_id) {
            $q->where('owner_id', $seller_id);
        })
        ->when($fromDate && $toNewDate, function($q) use ($fromDate, $toNewDate) {
            $q->whereBetween('created_at', [$fromDate, $toNewDate]);
        })
    
        ->when(! is_null($done), function($q) use ($done) {
            $q->where('done', $done);
        });

    // جلب النتائج لحساب المجموعات
    $allFiltered = (clone $ordersQuery)->get();

    $orderAmountSum   = $allFiltered->sum('order_amount');
    $collectedCashSum = $allFiltered->sum('collected_cash');
    $quantitySum      = $allFiltered->sum(fn($o) => $o->details->sum('quantity'));
    $productCount     = $allFiltered->sum(fn($o) => $o->details->count());

    // ترقيم الصفحات
    $orders = $ordersQuery
        ->paginate(Helpers::pagination_limit())
        ->appends($request->only([
            'search','from_date','to_date',
            'region_id','seller_id','customer_id',
            'branch_id','done','type'
        ]));

    // بيانات القوائم المنسدلة
    $sellers   = Admin::where('role','admin')->get();
    $branches  = Branch::all();

    // بيانات الكائنات المفردة للعرض
    $sellerw   = Admin::find($seller_id);
    $branchw   = Branch::find($branch_id);

    return view('admin-views.pos.sessions.orders', compact(
        'orders','search','fromDate','toDate',
        'regionId','orderAmountSum','collectedCashSum',
        'quantitySum','productCount','done','type',
        'sellers','branches',
        'sellerw','branchw',
        'seller_id','customer_id','branch_id'
    ));
}
}
