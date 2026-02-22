<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Http\Repositories\ContractRepository;
use App\Http\Requests\Admin\Contract\StoreContractRequest;
use App\Http\Requests\Admin\Contract\UpdateContractRequest;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    protected $repo;

    public function __construct(ContractRepository $repo)
    {
        $this->repo = $repo;
    }

 public function index(Request $request)
{
    $query = Contract::with('client');

    // فلترة برقم العقد
    if ($request->filled('contract_number')) {
        $query->where('contract_number', 'like', '%' . $request->contract_number . '%');
    }

    // فلترة باسم العميل عبر العلاقة
    if ($request->filled('client_name')) {
        $query->whereHas('client', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->client_name . '%');
        });
    }

    // فلترة حسب تاريخ البداية
    if ($request->filled('date_from')) {
        $query->whereDate('start_date', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('start_date', '<=', $request->date_to);
    }

    // فلترة حسب نطاق القيمة
    if ($request->filled('min_value')) {
        $query->where('total_value', '>=', $request->min_value);
    }
    if ($request->filled('max_value')) {
        $query->where('total_value', '<=', $request->max_value);
    }

    // جلب النتائج وترتيبها
    $contracts = $query
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('admin-views.contracts.index', compact('contracts'));
}


  public function create()
{
    $clients = \App\Models\Client::all();
    return view('admin-views.contracts.create', compact('clients'));
}




    public function store(StoreContractRequest $request)
    {
        $contract = $this->repo->create($request->validated());

        return redirect()->route('admin.contracts.show', $contract->id)
                         ->with('success','تم إنشاء العقد بنجاح.');
    }

    public function show(Contract $contract)
    {
        $contract->load('client');
        return view('admin-views.contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
{
    $clients = \App\Models\Client::all();
    return view('admin-views.contracts.edit', compact('contract','clients'));
}
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        $this->repo->update($contract, $request->validated());

        return redirect()->route('admin.contracts.show', $contract->id)
                         ->with('success','تم تحديث العقد بنجاح.');
    }

    public function toggleStatus(Contract $contract)
    {
        $contract->status = $contract->status === 'active' ? 'canceled' : 'active';
        $contract->save();

        return back()->with('success','تم تغيير حالة العقد.');
    }
}
