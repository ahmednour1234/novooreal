<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    // تغيير اسم الجدول إذا كان مختلفًا
    protected $table = 'assets';

    // الحقول التي يُمكن تعبئتها بشكل جماعي
    protected $fillable = [
        'asset_name',
        'purchase_price',
        'additional_costs',
        'total_cost',
        'salvage_value',
        'branch_id',
        'description',
        'code',
        'useful_life',
        'commencement_date',
        'depreciation_method',
        'depreciation_rate',
        'invoice_number',
        'purchase_date',
        'location',
        'status',
        'asset_img',  // اسم الحقل الذي يخزن صورة الأصل
    ];

    public $timestamps = true;
  public function branch()
{
    return $this->belongsTo(Branch::class);
}

}
