<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\User;

class UserController extends Controller
{
       
    public function users() {
       
        Session::put('page', 'users');


        $users = User::get()->toArray();
        


        return view('admin.users.users')->with(compact('users'));
    }



       
    public function updateUserStatus(Request $request) {
        if ($request->ajax()) { 
            $data = $request->all(); 
            

            if ($data['status'] == 'Active') { 
                $status = 0;
            } else {
                $status = 1;
            }

            User::where('id', $data['user_id'])->update(['status' => $status]); 

            return response()->json([ 
                'status'  => $status,
                'user_id' => $data['user_id']
            ]);
        }
    }


    
public function resetUserPassword(Request $request) {
    if ($request->ajax()) { 
        $data = $request->all(); 
        

        $new_password=$data['new_password'] ;

        $new_password_hashed = bcrypt($new_password); 

        User::where('id', $data['user_id'])->update(['password' => $new_password_hashed]); 
        
        return response()->json([ 
            'user_id' => $data['user_id']
        ]);
    }
}


}