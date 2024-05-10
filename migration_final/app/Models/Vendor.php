<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;


    public function vendorbusinessdetails() {    
        return $this->belongsTo('App\Models\VendorsBusinessDetail', 'id', 'vendor_id'); 
    }



    
    public static function getVendorShop($vendorid) { 
        $getVendorShop = \App\Models\VendorsBusinessDetail::select('shop_name')->where('vendor_id', $vendorid)->first()->toArray();


        return $getVendorShop['shop_name'];
    }

    public static function getVendorCommission($vendor_id) {
        $getVendorCommission = Vendor::select('commission')->where('id', $vendor_id)->first()->toArray();


        return $getVendorCommission['commission'];
    }

}