<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\Visitor;
use App\Models\Region;
use App\Models\ResultVisitor;
use App\Models\Customer;
use App\Models\AdminSeller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB; // Add this line
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;

class VisitorController extends Controller
{
    public function __construct(
        private Visitor $visitor,
                private Region $regions,
        private Seller $seller,
        private ResultVisitor $resultVisitor,
        private Customer $customer,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
public function index(Request $request)
{
     $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("visit.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // Retrieve the ID of the authenticated admin
    $adminId = Auth::guard('admin')->id();

    // Retrieve the seller IDs associated with the authenticated admin from the 'admin_sellers' table
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    // Retrieve sellers associated with the authenticated admin
    $sellers = Seller::whereIn('id', $sellerIds)->where('role', 'seller')->get();    $regions = $this->regions->get();

    // Initialize query for Visitor model
    $query = Visitor::query();

    // Filter by seller_id if provided
    if ($request->has('seller_id') && $request->seller_id) {
        $query->where('seller_id', $request->seller_id);
    }

    // Filter by region_id if provided
    if ($request->has('region_id') && $request->region_id) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('region_id', $request->region_id);
        });
    }

    // Filter by date range if both from_date and to_date are provided
    if ($request->has('from_date') && $request->from_date && $request->has('to_date') && $request->to_date) {
        $query->whereBetween('date', [$request->from_date, $request->to_date]);
    } elseif ($request->has('from_date') && $request->from_date) {
        // If only from_date is provided, filter visitors after the from_date
        $query->whereDate('date', '>=', $request->from_date);
    } elseif ($request->has('to_date') && $request->to_date) {
        // If only to_date is provided, filter visitors before the to_date
        $query->whereDate('date', '<=', $request->to_date);
    }

    // Fetch the filtered visitors and paginate the result
    $visitors = $query->with('seller', 'customer')->paginate(10);

    // Return the view with visitors, sellers, and regions
    return view('admin-views.visitors.index', compact('visitors', 'sellers', 'regions'));
}

public function showResultVisitors(Request $request, $seller_id)
{
    // Fetch the seller by ID
    $seller = $this->seller->find($seller_id);

    if (!$seller) {
        // Redirect back with an error message if the seller is not found
        return redirect()->back()->withErrors(['error' => 'Seller not found']);
    }

    // Initialize the query to filter ResultVisitor records for the seller
    $query = $this->resultVisitor->where('admin_id', $seller_id);

    // Filter by customer_id if provided
    if ($request->has('customer_id') && $request->customer_id) {
        $query->where('customer_id', $request->customer_id);
    }

    // Filter by region_id if provided, using a whereHas clause to check customer's region_id
    if ($request->has('region_id') && $request->region_id) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('region_id', $request->region_id);
        });
    }

    // Filter by date range if both from_date and to_date are provided
    if ($request->has('from_date') && $request->from_date && $request->has('to_date') && $request->to_date) {
        $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
    } elseif ($request->has('from_date') && $request->from_date) {
        // If only from_date is provided, filter visitors created after the from_date
        $query->whereDate('created_at', '>=', $request->from_date);
    } elseif ($request->has('to_date') && $request->to_date) {
        // If only to_date is provided, filter visitors created before the to_date
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    // Fetch customers for the seller (using seller_customers relationship)
    $customerIds = DB::table('seller_customers')
        ->where('seller_id', $seller_id)
        ->pluck('customer_id')->toArray();
    $customers = !empty($customerIds) ? $this->customer->whereIn('id', $customerIds)->get() : [];

    // Fetch regions for the seller (using seller_regions relationship)
    $regionIds = DB::table('seller_regions')
        ->where('seller_id', $seller_id)
        ->pluck('region_id')->toArray();
    $regions = !empty($regionIds) ? $this->regions->whereIn('id', $regionIds)->get() : [];

    // Get the filtered result visitors with pagination
    $resultVisitors = $query->paginate(10);

    if ($resultVisitors->isEmpty()) {
        // Redirect back with an error message if no visitors are found
        return redirect()->back()->withErrors(['error' => 'هذا المندوب لم يقم باي زيارات']);
    }

    // Fetch all sellers for dropdown or display purposes
    $sellers = $this->seller->where('role', 'seller')->get();

    // Pass visitors, sellers, and customers to the view
    return view('admin-views.visitors.indexresult', [
        'visitors' => $resultVisitors,
        'sellers' => $sellers,
        'seller_id' => $seller_id,
        'customers' => $customers,
        'regions' => $regions,
    ]);
}



    
    // public function vehicles(Request $request): Factory|View|Application
    // {
    //     $date = $request['search'];
    //     $sellers = $this->seller->get();
    //     return view('admin-views.vehicle_stocks.vehicles', compact('sellers', 'date'));
    // }
    
    // public function vehicle_products($seller_id): Factory|View|Application
    // {
    //     $stocks = $this->confirm_stock->where('seller_id', $seller_id)->whereRaw('stock <= main_stock AND stock != 0')->get();
    //     $remain_stocks = $this->confirm_stock->where('seller_id', $seller_id)->whereRaw('stock = 0')->get();
    //     dd($stocks);
    //     return view('admin-views.vehicle_stocks.products', compact('stocks', 'remain_stocks'));
    // }
    
    // public function stock_products($seller_id): Factory|View|Application
    // {
    //     $stocks = $this->stock->where('seller_id', $seller_id)->whereRaw('stock < main_stock AND stock != 0');
    //     $remain_stocks = $this->stock->where('seller_id', $seller_id)->whereRaw('main_stock = stock');
    //     $seller = Seller::find($seller_id);
    //     $orders = \App\Models\CurrentOrder::where('owner_id', $seller_id);
    //     return view('admin-views.vehicle_stocks.stocks', compact('stocks', 'remain_stocks', 'orders', 'seller'));
    // }

public function create(Request $request): Factory|View|Application|JsonResponse
{
     $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("visit.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $adminId = Auth::guard('admin')->id();

    // Retrieve the seller IDs associated with the authenticated admin from the 'admin_sellers' table
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    // Retrieve sellers associated with the authenticated admin
    $sellers = Seller::whereIn('id', $sellerIds)->where('role', 'seller')->get();
    $customers = [];

    if ($request->has('seller')) {
        $customerIds = DB::table('seller_customers')
            ->where('seller_id', $request->seller)
            ->pluck('customer_id')->toArray();

        if (!empty($customerIds)) {
            // Fetch the customers without pagination metadata
            $customers = $this->customer->whereIn('id', $customerIds)->get();

            return response()->json([
                'option' => $customers // Return only the customer array
            ]);
        }
    }

    return view('admin-views.visitors.create', compact('sellers', 'customers'));
}


public function export(Request $request)
{
    // Retrieve filters from request
    $sellerId = $request->seller_id;
    $regionId = $request->region_id;
    $fromDate = $request->from_date;
    $toDate = $request->to_date;

    // Initialize query
    $query = Visitor::query();

    // Apply filters
    if ($sellerId) {
        $query->where('seller_id', $sellerId);
    }

    if ($regionId) {
        $query->whereHas('customer', function ($q) use ($regionId) {
            $q->where('region_id', $regionId);
        });
    }

    if ($fromDate && $toDate) {
        $query->whereBetween('date', [$fromDate, $toDate]);
    } elseif ($fromDate) {
        $query->whereDate('date', '>=', $fromDate);
    } elseif ($toDate) {
        $query->whereDate('date', '<=', $toDate);
    }

    // Fetch the filtered visitors data
    $visitors = $query->with('seller', 'customer')->get();

    // Prepare the data for the Excel export
    $data = $visitors->map(function ($visitor) {
        return [
            'Visitor ID' => $visitor->id,
            'Seller Name' => $visitor->seller->email ?? 'N/A',
            'Customer Name' => $visitor->customer->name ?? 'N/A',
            'Region' => $visitor->customer->regions->name ?? 'N/A',
            'Date' => $visitor->date,
            'Note' => $visitor->note ?? 'N/A',
        ];
    });

    // Use FastExcel to download the data as an Excel file
    return (new FastExcel($data))->download('visitors.xlsx');
}




public function store(Request $request): Factory|RedirectResponse|Application
{
    // Validate the input fields
    $request->validate([
        'seller_id' => 'required|exists:admins,id',
        'customer_id' => 'required|array',   // customer_id is an array
        'date' => 'required|array',          // date is an array
        'note' => 'required|array',          // note is an array
    ]);

    $success = 0;
    $newVisitorsCount = 0; // Track the number of new visitors

    // Fetch the seller instance
    $seller = Seller::find($request->seller_id);
    if (!$seller) {
        Toastr::error(translate('Seller not found'));
        return back();
    }

    // Loop through the customer_id array, allowing multiple entries for the same customer
    foreach ($request->customer_id as $i => $customerId) {
        // Find the customer by ID
        $customer = $this->customer->find($customerId);
        if (!$customer) {
            continue; // Skip if customer not found
        }

        // Get the date and note for each visitor/customer from the arrays
        $date = $request->date[$i];
        $note = $request->note[$i];

        // Always create a new visitor record
        $visitor = new $this->visitor;
        $visitor->seller_id = $request->seller_id;
        $visitor->customer_id = $customerId;
        $visitor->date = $date;
        $visitor->note = $note;
        $visitor->save();

        // Increment the new visitors count
        $newVisitorsCount++;
        $success = 1;
    }

    // Increment the seller's visitors column by the number of new visitors
    if ($newVisitorsCount > 0) {
        $seller->increment('visitors', $newVisitorsCount); // Increment by the number of new visitors added
    }

    // Display a success message if visitors were added or updated
    if ($success == 1) {
        Toastr::success(translate('تم إنشاء قايمة الزيارات بنجاح'));
    }

    return back();
}


    public function edit(Request $request, $id): Factory|View|Application|JsonResponse
    {
    $adminId = Auth::guard('admin')->id();

    // Retrieve the seller IDs associated with the authenticated admin from the 'admin_sellers' table
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    // Retrieve sellers associated with the authenticated admin
    $sellers = Seller::whereIn('id', $sellerIds)->where('role', 'seller')->get();
    $visitor = $this->visitor->find($id);
        $products = [];
        if($request->has('seller')) {
            $sel = $this->seller->find($request->seller);
            foreach($sel->cats as $c) {
                $id = $c->cat->id;
                $customers[] = $this->customer->where('category_id', $id)->get();
            }

            return response()->json([
                'option' => $customers
            ]);
        }
        else {
            foreach($visitor->seller->cats as $c) {
                $id = $c->cat->id;
                $customers[] = $this->customer->where('category_id', $id)->get();
            }
        }
        return view('admin-views.vehicle_stocks.edit', compact('sellers', 'customers', 'visitor'));
    }

    public function update(Request $request, $id): Factory|RedirectResponse|Application
    {
        $customer = $this->customer->find($request->product_id);
        $request->validate([
            'seller_id' => 'required',
            'customer_id' => 'required',
            'date' => 'required',
            'note' => 'nullable',
        ]);

        $visitor = $this->visitor->find($id);
        $visitor->seller_id = $request->seller_id;
        $visitor->customer_id = $request->customer_id;
        $visitor->date = $request->date;
        $visitor->note = $request->note;
        $visitor->update();


        Toastr::success(translate('تم تحديث الزيارات بنجاح'));
        return redirect()->route('admin.visitor.index');
    }
public function delete(Request $request): RedirectResponse
{
     $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("visit.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Find the visitor by ID
    $visitor = $this->visitor->find($request->id);
    
    if (!$visitor) {
        Toastr::error(translate('Visitor not found'));
        return back();
    }

    // Find the seller associated with this visitor
    $seller = $this->seller->find($visitor->seller_id);

    if ($seller) {
        // Decrease the visitors count by 1
        $seller->decrement('visitors', 1);
    }

    // Delete the visitor record
    $visitor->delete();

    Toastr::success(translate('تم حذف الزيارة بنجاح'));
    return back();
}

    
    // public function history(Request $request)
    // {
    //     // $limit = $request['limit'] ?? 10;
    //     // $offset = $request['offset'] ?? 1;
    //     // $date = $request['date'] ?? null;
    //     $search = $request->input('search');
        
    //     $fromDate = $request->input('from_date');
    //     $toDate = $request->input('to_date');

    //     $stocks = $this->stock_order->latest()
    //                           ->with(['seller']);
    
    //     if (!empty($search)) {
    //         $stocks->where(function($query) use ($search) {
    //             $query->where('id', 'like', "%{$search}%")
    //                   ->orWhereHas('seller', function($query) use ($search) {
    //                       $query->where('f_name', 'like', "%{$search}%")
    //                             ->orWhere('l_name', 'like', "%{$search}%");
    //                   });
    //         });
    //     }
    
    //     if (!empty($fromDate) && !empty($toDate)) {
    //         $stocks->whereBetween('created_at', [$fromDate, $toDate]);
    //     }
    
    //     $stocks = $stocks->paginate(Helpers::pagination_limit())->appends([
    //         'search' => $search,
    //         'from_date' => $fromDate,
    //         'to_date' => $toDate,
    //     ]);
        
    //     // $items = $stocks->items();
    //     foreach($stocks as $key => $item) {
    //         $item['statistcs'] = json_decode($item->statistcs);
    //     }
    //     // dd($stocks);
    //     // return $stocks[0]->statistcs->products[0]->price . ' ' . \App\CPU\Helpers::currency_symbol();
    //     return view('admin-views.pos.stocks.list', ['orders' => $stocks, 'fromDate' => $fromDate, 'toDate' => $toDate, 'search' => $search]);
    //     // return response()->json($data, 200);
    // }
}
