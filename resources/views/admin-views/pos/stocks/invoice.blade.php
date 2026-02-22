<div class="width-inone">
    <div class="text-center mb-3">
        <h2 class="line-inone">{{ \App\Models\BusinessSetting::where(['key' => 'shop_name'])->first()->value }}</h2>
        <h5 class="style-inone">
            {{ \App\Models\BusinessSetting::where(['key' => 'shop_address'])->first()->value }}
        </h5>
        <h5 class="style-intwo">
            {{ \App\CPU\translate('رقم الجوال') }}
            : {{ \App\Models\BusinessSetting::where(['key' => 'shop_phone'])->first()->value }}
        </h5>
        <h5 class="style-intwo">
            {{ \App\CPU\translate('البريد الالكتروني') }}
            : {{ \App\Models\BusinessSetting::where(['key' => 'shop_email'])->first()->value }}
        </h5>
        <h5 class="style-intwo">
            {{ \App\CPU\translate('رقم الضريبي') }}
            : {{ \App\Models\BusinessSetting::where(['key' => 'vat_reg_no'])->first()->value }}
        </h5>
    </div>

    <hr class="line-dot">

    <center class="mt-3">
        <h5>{{ \App\CPU\translate(' رقم الرحلة') }} : {{ $order->id }}</h5>
        
        <h5>{{ \App\CPU\translate('اسم المندوب') }} : {{ optional($order->seller)->f_name . ' ' . optional($order->seller)->l_name }}</h5>

        <h5 class="font-inone fz-10">
            {{ date('d/M/Y h:i a', strtotime($order->created_at)) }}
        </h5>
    </center>

    <hr class="line-dot">
    <h5>قائمة المنتجات</h5>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>{{ \App\CPU\translate('رقم') }}</th>
                <th>{{ \App\CPU\translate('المنتج') }}</th>
                <th>{{ \App\CPU\translate('الكمية المصروفة') }}</th>
                <th>{{ \App\CPU\translate('الكمية المتبقية') }}</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($stocks as $detail)
                <tr>
                    <td>
{{ $detail->product->product_code }}
</td>
                    <td>
                        <span class="style-inthree">{{ $detail->product->name }}</span><br />
                        {{ \App\CPU\translate('price') }} :
                        {{ number_format($detail->product->selling_price, 2) }} <br>
                    </td>
                   <td class="">
    @php
        // التحقق مما إذا كانت main_stock عدد عشري وضربه في unit_value
        $mainStockValue = is_numeric($detail->main_stock) && floor($detail->main_stock) != $detail->main_stock 
            ? $detail->main_stock * $detail->product->unit_value 
            : $detail->main_stock;
               $unitValue = is_numeric($detail->main_stock) && floor($detail->main_stock) != $detail->main_stock 
            ?  $detail->product->unit->unit_type 
            : $detail->product->unit->subUnits->first()?->name;
    @endphp
    {{ $mainStockValue }} {{$unitValue}}
</td>

                    <td class="">
                            @php
        // التحقق مما إذا كانت main_stock عدد عشري وضربه في unit_value
        $StockValue = is_numeric($detail->stock) && floor($detail->stock) != $detail->stock 
            ? $detail->stock * $detail->product->unit_value 
            : $detail->stock;
             $unitValuestock = is_numeric($detail->stock) && floor($detail->stock) != $detail->stock 
            ?  $detail->product->unit->unit_type 
            : $detail->product->unit->subUnits->first()?->name;
    @endphp
                        {{ $StockValue }}{{$unitValuestock}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <hr class="line-dot">
    <dl class="row text-black-50">
        
        <dt class="col-7">{{ \App\CPU\translate('رقم السيارة') }}:</dt>
        <dd class="col-5  text-right">{{ $order->statistcs->vehicle_code }}</dd>

        <dt class="col-7">{{ \App\CPU\translate('اجمالي الكميات المنصرفة') }}:</dt>
<dd class="col-5 text-right">{{ $stocks->sum('main_stock') }}</dd>
        
        <dt class="col-7">{{ \App\CPU\translate('اجمالي الكميات المتبقية') }}:</dt>
<dd class="col-5 text-right">{{ $stocks->sum('stock') }}</dd>
                
        <dt class="col-7">{{ \App\CPU\translate('اجمالي المبيعات النقدي') }}:</dt>
        <dd class="col-5  text-right">{{ $totalordercash }}</dd>
                
        <dt class="col-7">{{ \App\CPU\translate('اجمالي المبيعات الشبكة') }}:</dt>
        <dd class="col-5  text-right">{{ $totalordershabaka }}</dd>
                
        <dt class="col-7">{{ \App\CPU\translate('اجمالي المبيعات الاجل') }}:</dt>
        <dd class="col-5  text-right">{{ $totalordercredit }}</dd>
                
        <dt class="col-7">{{ \App\CPU\translate('اجمالي المرتجعات') }}:</dt>
        <dd class="col-5  text-right">{{ $ordersreturncredit }}</dd>
        
    </dl>
    
    <hr class="line-dot">
    <h5 class="text-center">
        """{{ \App\CPU\translate('شكراََ لك') }}"""
    </h5>
    <hr class="line-dot">
</div>
