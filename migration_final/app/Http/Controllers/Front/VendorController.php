<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Vendor;
use App\Models\Admin;

class VendorController extends Controller
{
    public function loginRegister() { 
        return view('front.vendors.login_register');
    }

    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators    
    public function vendorRegister(Request $request) {   
        if ($request->isMethod('post')) { 
            $data = $request->all();
            
            $rules = [
                
                            'name'          => 'required',
                            'email'         => 'required|email|unique:admins|unique:vendors', 
                            'mobile'        => 'required|min:10|numeric|unique:admins|unique:vendors', 
                            'accept'        => 'required'
                            // 'unique:admins' and 'unique:vendors' means check the `admins` table and `vendors` table for the `mobile` uniqueness: https://laravel.com/docs/9.x/validation#rule-unique
            ];

            $customMessages = [ 
                 // Specifying A Custom Message For A Given Attribute: https://laravel.com/docs/9.x/validation#specifying-a-custom-message-for-a-given-attribute

                                'name.required'             => 'Name is required',
                                'email.required'            => 'Email is required',
                                'email.unique'              => 'Email alreay exists',
                                'mobile.required'           => 'Mobile is required',
                                'mobile.unique'             => 'Mobile alreay exists',
                                'accept.required'           => 'Please accept Terms & Conditions',
            ];

            $validator = Validator::make($data, $rules, $customMessages); 
            // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators

            if ($validator->fails()) {
                return \Illuminate\Support\Facades\Redirect::back()->withErrors($validator);
            }

            // Firstly, we'll save the vendor in the `vendors` table, then take the newly generated vendor `id` to use it as a `vendor_id` column value to save the vendor in `admins` table, then we send the Confirmation Mail to the vendor using Mailtrap    
            // Database Transactions: https://laravel.com/docs/9.x/database#database-transactions

            DB::beginTransaction();

            
            $vendor = new Vendor; 
            $vendor->name   = $data['name'];
            $vendor->mobile = $data['mobile'];
            $vendor->email  = $data['email'];
            $vendor->status = 0; 
            date_default_timezone_set('Asia/Beirut'); 
            $vendor->created_at = date('Y-m-d H:i:s'); 
            $vendor->updated_at = date('Y-m-d H:i:s'); 
            $vendor->save();

            $vendor_id = DB::getPdo()->lastInsertId(); 
             // get the vendor `id` of the `vendors` table (which has just been inserted) to insert it in the `vendor_id` column of the `admins` table 
            $admin = new Admin;
            $admin->type      = 'vendor';
            $admin->vendor_id = $vendor_id; 
            $admin->name      = $data['name'];
            $admin->mobile    = $data['mobile'];
            $admin->email     = $data['email'];
            $admin->password  = bcrypt($data['password']);
            $admin->status    = 0;
            date_default_timezone_set('Asia/Beirut'); 
            $admin->created_at = date('Y-m-d H:i:s'); 
            $admin->updated_at = date('Y-m-d H:i:s'); 
            $admin->save();

            $email = $data['email']; 

            $messageData = [
                'email' => $data['email'],
                'name'  => $data['name'],
                'code'  => base64_encode($data['email']) 
                 // We base64 code the vendor $email and send it as a Route Parameter from vendor_confirmation.blade.php to the 'vendor/confirm/{code}' route in web.php, then it gets base64 decoded again in confirmVendor() method in Front/VendorController.php  
                   // we then use the opposite: base64_decode() in the confirmVendor() method (encode X decode)
            ];

            //Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail 
            \Illuminate\Support\Facades\Mail::send('emails.vendor_confirmation', $messageData, function ($message) use ($email) {
                $message->to($email)->subject('Confirm your Vendor Account');

                // We pass in all the variables 
                  // https://www.php.net/manual/en/functions.anonymous.php
            });


            DB::commit();

            $message = 'Thanks for registering as Vendor. Please confirm your email to activate your account.';
            return redirect()->back()->with('success_message', $message);
        }
    }

    public function confirmVendor($email) { 

        $email = base64_decode($email); 

        $vendorCount = Vendor::where('email', $email)->count();
        if ($vendorCount > 0) {
            $vendorDetails = Vendor::where('email', $email)->first();
            if ($vendorDetails->confirm == 'Yes') { 
                $message = 'Your Vendor Account is already confirmed. You can login';
                return redirect('vendor/login-register')->with('error_message', $message);

            } else { 
                Admin::where( 'email', $email)->update(['confirm' => 'Yes']);
                Vendor::where('email', $email)->update(['confirm' => 'Yes']);

                $messageData = [
                    'email'  => $email,
                    'name'   => $vendorDetails->name,
                    'mobile' => $vendorDetails->mobile
                ];
                \Illuminate\Support\Facades\Mail::send('emails.vendor_confirmed', $messageData, function ($message) use ($email) { 
                    $message->to($email)->subject('You Vendor Account Confirmed');
                });

                $message = 'Your Vendor Email account is confirmed. You can login and add your personal, business and bank details to activate your Vendor Account to add products';
                return redirect('vendor/login-register')->with('success_message', $message);
            }
        } else { 
            abort(404);
        }
    }
}