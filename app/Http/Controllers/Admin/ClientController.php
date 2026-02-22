<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\Client\StoreClientRequest;
use App\Http\Requests\Admin\Client\UpdateClientRequest;
use Brian2694\Toastr\Facades\Toastr;

class ClientController extends Controller
{
    protected $repo;

    public function __construct()
    {
        $this->repo = new class {
            /**
             * إنشاء عميل مع إنشاء الحساب المحاسبي وربطه به
             */
            public function create(array $data): Client
            {
                return DB::transaction(function () use ($data) {
                    $client = Client::create($data);
                    $this->createFinancialAccount($client);
                    return $client;
                });
            }

            /**
             * تحديث بيانات العميل وتحديث الحساب المحاسبي المرتبط به
             */
            public function update(Client $client, array $data): Client
            {
                $client->update($data);


                return $client;
            }

            /**
             * إنشاء حساب محاسبي وربطه بالعميل
             */
          private function createFinancialAccount(Client $client)
{
    $parentId = 15; // مجلد "عملاء المقاولات" أو الحساب الأب
    $accountCode = Account::generateAccountCode('asset', $parentId);

    // إنشاء حساب جديد ثم حفظه
    $account = new Account();
    $account->account        = "حساب العميل: " . $client->name;
    $account->description    = "حساب العميل: " . $client->name;
    $account->account_number = $accountCode;
    $account->parent_id      = $parentId;
    $account->account_type   = 'asset';
    $account->code           = $accountCode;
    $account->save();

    // ربط الحساب بالعميل وحفظ
    $client->account_id = $account->id;
    $client->save();
}

        };
    }

    /**
     * عرض نموذج إنشاء عميل
     */
     public function index(Request $request)
{
    $query = Client::query();

    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    if ($request->filled('email')) {
        $query->where('email', 'like', '%' . $request->email . '%');
    }

    if ($request->filled('phone')) {
        $query->where('phone', 'like', '%' . $request->phone . '%');
    }

    if ($request->filled('company_name')) {
        $query->where('company_name', 'like', '%' . $request->company_name . '%');
    }

    if ($request->filled('active')) {
        $query->where('active', $request->active);
    }

    $clients = $query->latest()->paginate(15)->withQueryString();

    return view('admin-views.clients.index', compact('clients'));
}

    public function create()
    {
        return view('admin-views.clients.create');
    }

    /**
     * حفظ العميل الجديد وربط الحساب المحاسبي
     */
    public function store(StoreClientRequest $request)
    {
        $client = $this->repo->create($request->validated());

        Toastr::success('تم إنشاء العميل وربط الحساب بنجاح.');
        return redirect()->route('admin.clients.show', $client->id);
    }

    /**
     * عرض تفاصيل عميل
     */
    public function show(Client $client)
    {
        return view('admin-views.clients.show', compact('client'));
    }

    /**
     * عرض نموذج تعديل عميل
     */
    public function edit(Client $client)
    {
        return view('admin-views.clients.edit', compact('client'));
    }

    /**
     * تحديث بيانات العميل
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->repo->update($client, $request->validated());

        Toastr::success('تم تحديث بيانات العميل بنجاح.');
        return redirect()->route('admin.clients.show', $client->id);
    }

    /**
     * تفعيل أو تعطيل العميل
     */
    public function toggleStatus(Client $client)
    {
        $client->active = !$client->active;
        $client->save();

        Toastr::success('تم تحديث حالة العميل.');
        return redirect()->back();
    }
}
