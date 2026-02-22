<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    public function orders()
    {
        return $this->hasMany(Order::class,'user_id');
    }
     public function installments()
    {
        return $this->hasMany(Installment::class,'customer_id');
    }
      public function regions()
    {
        return $this->belongsto(Region::class,'region_id');
    }
        public function guarantor()
    {
        return $this->belongsTo(Guarantor::class);
    }

    /**
     * Update customer and optional guarantor data from request
     * @param array $data
     * @return void
     */
    public function updateWithGuarantor(array $data)
    {
        // Update base attributes
        $this->fill(
            array_only($data, [
                'name','name_en','mobile','email','region_id',
                'tax_number','c_history','city','zip_code',
                'address','latitude','longitude','type','category_id'
            ])
        );

        // Handle image upload
        if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $this->image = Helpers::update('customer/', $this->image, 'png', $data['image']);
        }

        // Save customer to get id
        $this->save();

        // Handle guarantor if provided
        if (! empty($data['guarantor_name'])) {
            $guarantor = $this->guarantor ?: new Guarantor();
            $guarantor->fill(
                array_only($data, [
                    'guarantor_name'=>'name',
                    'guarantor_national_id'=>'national_id',
                    'guarantor_phone'=>'phone',
                    'guarantor_address'=>'address',
                    'guarantor_job'=>'job',
                    'guarantor_monthly_income'=>'monthly_income',
                    'guarantor_relation'=>'relation'
                ])
            );
            // merge images
            $images = json_decode($guarantor->images ?? '[]', true);
            if (! empty($data['guarantor_images']) && is_array($data['guarantor_images'])) {
                foreach ($data['guarantor_images'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $images[] = $file->store('uploads/guarantors', 'public');
                    }
                }
            }
            $guarantor->images = json_encode($images);
            $guarantor->save();

            // link guarantor
            $this->guarantor_id = $guarantor->id;
            $this->save();
        }
    }
}
