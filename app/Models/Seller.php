<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Seller extends Model
{
    protected $table = 'admins';
    // protected $fillable = ['vehicle_code'];
    
    use HasFactory, Notifiable, HasApiTokens;

    public function regions()
    {
        return $this->hasMany(SellerRegion::class);
    }

    public function cats()
    {
        return $this->hasMany(SellerCategory::class);
    }
     public function customers()
    {
        return $this->hasMany(SellerCustomer::class);
    }
  public function storages()
    {
        return $this->hasMany(StorageSeller::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
       public function detail()
{
    return $this->hasOne(AdminDetail::class,'admin_id');
}
     


    public function orders()
    {
        return $this->hasMany(Order::class, 'owner_id');
    }
     public function adminSellers()
    {
        return $this->hasMany(AdminSeller::class, 'seller_id');
    }
        public function branch()
  {
    return $this->belongsTo(Branch::class,'branch_id');
  }
     public function shift()
  {
    return $this->belongsTo(Shift::class,'shift_id');
  }
  protected $casts = [
    'shift_id' => 'array',
];
}
