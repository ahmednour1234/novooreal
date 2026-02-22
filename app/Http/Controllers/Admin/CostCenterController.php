<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\CostCenter;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



class CostCenterController extends Controller{
 public function __construct(
        private CostCenter $costcenter
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

    if (!in_array("costcenter.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $query_param = [];
    $search = $request['search'];

    $costcenters = CostCenter::whereNull('parent_id')->with('children');

    if ($request->has('search')) {
        $key = explode(' ', $request['search']);
        $costcenters = $costcenters->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        });
        $query_param = ['search' => $request['search']];
    }

    $costCenters = $costcenters->latest()->paginate(Helpers::pagination_limit())->appends($query_param);

    return view('admin-views.costcenter.index', compact('costCenters', 'search'));
}
public function show(Request $request, $id)
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

    if (!in_array("costcenter.showindx", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $search = $request->search;
    $query_param = [];

    $costcenters = CostCenter::where('parent_id', $id)->with('children');

    if ($request->has('search')) {
        $key = explode(' ', $search);
        $costcenters = $costcenters->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        });
        $query_param = ['search' => $search];
    }

    $costCenters = $costcenters->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
    $costCenter = CostCenter::findOrFail($id);

    return view('admin-views.costcenter.show', compact('costCenters', 'search', 'costCenter', 'id'));
}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
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

    if (!in_array("costcenter.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
                return redirect()->back();

    }
         $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:cost_centers,code',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'description' => 'nullable|string',
        ]);
      
        $category = $this->costcenter;
        $category->name = $request->name;
        $category->code = $request->code;
        $category->parent_id = $request->parent_id;
        $category->description = $request->description??'';
        $category->save();

        Toastr::success(translate('تمت إضافة بنجاح'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
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

    if (!in_array("costcenter.active", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
                return redirect()->back();

    }
$category = $this->costcenter->find($request->id);
$category->active = $category->active ? 0 : 1;
$category->save();

        Toastr::success(translate('تم تغيير الحالة بنجاح'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id)
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

    if (!in_array("costcenter.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
                return redirect()->back();

    }
        $category = $this->costcenter->find($id);
        return view('admin-views.costcenter.edit', compact('category'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
         $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:cost_centers,code,' . $id,
            'parent_id' => 'nullable|exists:cost_centers,id',
            'description' => 'nullable|string',
        ]);
        $category = $this->costcenter->find($id);
        $category->name = $request->name;
        $category->code = $request->code;
        $category->parent_id = $request->parent_id;
        $category->description = $request->description;
        $category->save();

        Toastr::success(translate('تم التحديث بنجاح'));
        return redirect()->route('admin.costcenter.add');
    }
public function fetch(Request $request)
{
    $query = CostCenter::query()->select('id','name','code','parent_id');

    if ($request->filled('parent_id')) {
        $query->where('parent_id', $request->integer('parent_id'));
    } else {
        // جذور حسب المجموعة (لو عندك عمود group)
        if ($request->filled('group') && $request->get('group') !== 'all') {
            $query->where('group', $request->get('group'));
        }
        $query->whereNull('parent_id');
    }


    $items = $query->orderBy('name')->get();

    return response()->json($items);
}

    /**
     * GET /admin/cost-centers/search?q=term
     * بحث بالاسم/الكود — JSON
     */
    public function search(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $items = CostCenter::query()
            ->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('code', 'like', "%{$q}%");
            })
            ->select('id','name','code','parent_id')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($items);
    }
       public function statement(Request $request)
    {
        $validated = $request->validate([
            'cost_center_id' => 'required|exists:cost_centers,id',
            'from'           => 'nullable|date',
            'to'             => 'nullable|date',
        ]);

        $cc   = CostCenter::findOrFail($validated['cost_center_id']);
        $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
        $to   = $validated['to']   ?? now()->endOfMonth()->toDateString();

        // مثال: لو عندك جدول تفاصيل قيود اليومية فيه cost_center_id
        $lines = DB::table('journal_entry_details as d')
            ->join('journal_entries as e','e.id','=','d.journal_entry_id')
            ->where('d.cost_center_id', $cc->id)
            ->whereBetween('d.entry_date', [$from, $to])
            ->select('e.reference','d.entry_date','d.description','d.debit','d.credit')
            ->orderBy('d.entry_date')
            ->get();

        $totalDebit  = $lines->sum('debit');
        $totalCredit = $lines->sum('credit');

        return view('admin-views.cost_centers.statement', compact('cc','from','to','lines','totalDebit','totalCredit'));
    }


}
