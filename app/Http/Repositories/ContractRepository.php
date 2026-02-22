<?php

namespace App\Http\Repositories;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;

class ContractRepository
{
    /**
     * أنشئ عقدًا واربطه محاسبيًا إذا كان مفعلًا
     */
 public function create(array $data): Contract
{
    return DB::transaction(function () use ($data) {
        // احصل على حساب الذمم الخاص بالعميل مباشرة
        $receivableAccountId = \App\Models\Client::find($data['client_id'])->account_id;

        // احصل على حساب الإيرادات من الإعداد (مثلاً parent_id=20)
        // يمكنك تخزينه في config/accounting.php
        $revenueAccountId =118;

        $contract = Contract::create(array_merge($data, [
            'receivable_account_id' => $receivableAccountId,
            'revenue_account_id'   => $revenueAccountId,
        ]));

        if ($contract->status === 'active') {
            // قيد محاسبي مثال
           
        }

        return $contract;
    });
}


    /**
     * حدّث العقد وربطه بالمحاسبة إن تغيّر الوضع
     */
    public function update(Contract $contract, array $data): Contract
    {
        return DB::transaction(function () use ($contract, $data) {
            $contract->update($data);
            // هنا يمكنك تعديل القيود المحاسبية حسب الحاجة...
            return $contract;
        });
    }
}
