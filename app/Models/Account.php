<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
         protected $fillable = [
        'code'
      
    ];
public static function generateAccountCode($accountType, $parentId = null)
{
    $accountTypePrefixes = [
        'asset'     => 1,
        'liability' => 2,
        'equity'    => 3,
        'revenue'   => 4,
        'expense'   => 5,
        'other'     => 6
    ];

    if (!isset($accountTypePrefixes[$accountType]) && is_null($parentId)) {
        throw new \Exception("Invalid account type");
    }

    if ($parentId) {
        // جلب الحساب الأب
        $parentAccount = self::find($parentId);
        if (!$parentAccount) {
            throw new \Exception("Parent account not found");
        }

        // حساب المستوى الفعلي للابن
        $level = self::getAccountLevelByParents($parentAccount) + 1;

        // آخر حساب فرعي لنفس الأب
        $lastSubAccount = self::where('parent_id', $parentId)
            ->orderBy('code', 'desc')
            ->first();

        $counter = 1;
        if ($lastSubAccount) {
            // لو المستوى >= 3 نقرأ آخر 4 أرقام، غير كده نقرأ آخر رقم
            $lastNumber = ($level >= 3)
                ? (int) substr($lastSubAccount->code, -4)
                : (int) substr($lastSubAccount->code, -1);

            $counter = $lastNumber + 1;
        }

        if ($level >= 3) {
            // ابتداءً من المستوى الثالث → 0001
            $newCode = $parentAccount->code . str_pad($counter, 4, '0', STR_PAD_LEFT);
        } else {
            // المستويات 1 و 2 → رقم عادي
            $newCode = $parentAccount->code . $counter;
        }

    } else {
        // إنشاء حساب رئيسي
        $lastAccount = self::where('account_type', $accountType)
            ->whereNull('parent_id')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastAccount) {
            $lastNumber = (int) substr($lastAccount->code, -1);
            $newCode = $accountTypePrefixes[$accountType] . ($lastNumber + 1);
        } else {
            // أول حساب رئيسي
            $newCode = $accountTypePrefixes[$accountType] . '1';
        }
    }

    return (string) $newCode;
}

/**
 * حساب المستوى من عدد الآباء
 */
protected static function getAccountLevelByParents($account)
{
    $level = 1;
    while ($account->parent_id) {
        $account = self::find($account->parent_id);
        $level++;
    }
    return $level;
}




    public function transections()
    {
        return $this->hasMany(Transection::class);
    }
        public function storage()
    {
        return $this->belongsTo(Storage::class, 'storage_id');
    }
          public function expense()
    {
        return $this->hasMany(Expense::class, 'expense_id');
    }
       // علاقة مركز التكلفة الرئيسي
    public function parent()
    {
        return $this->belongsTo(CostCenter::class, 'parent_id');
    }
public function childrenn()
{
    return $this->hasMany(self::class, 'parent_id');
}


    // علاقة مراكز التكلفة الفرعية
    public function children()
    {
        return $this->hasMany(CostCenter::class, 'parent_id');
    }
        public function ancestors()
    {
        $ancestors = collect();
        $current   = $this->parent;
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }
        return $ancestors;
    }

    // نتحقق مما إذا كان هذا الحساب منسوباً (حفيد أو حفيد حفيد...) إلى ID معيّن
    public function isDescendantOf(int $ancestorId): bool
    {
        return $this->ancestors()->pluck('id')->contains($ancestorId);
    }
}
