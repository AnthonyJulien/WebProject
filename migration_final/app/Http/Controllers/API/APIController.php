<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ProductsAttribute;

class APIController extends Controller
{

    public function getUsers(Request $request, $id = null) { 
        if ($request->isMethod('get')) { 
            $header = $request->header('Authorization'); 

            if (empty($header)) { 
                $message = '\'Authorization\' HTTP Request Header value (i.e. Bearer Access Token) is missing!';

                return response()->json([ 
                    'status'  => false,
                    'message' => $message
                ], 422);     

            } elseif ($header != 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkFtaXQgR3VwdGEiLCJpYXQiOjE1MTYyMzkwMjJ9.cNrgi6Sso9wvs4GlJmFnA4IqJY4o2QEcKXgshJTjfNg') { 
                $message = 'Authorization Header value is Incorrect!';

                return response()->json([ 
                    'status'  => false,
                    'message' => $message
                ], 422);
            }
            if (empty($id)) { 
                $users = User::get();
            } else {  
                $users = User::find($id);
            }
            return response()->json([
                'users'   => $users
            ], 200); 

        } else { 
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422);
        }
    }

    public function addUser(Request $request) {
        if ($request->isMethod('post')) { 
            $userData = $request->input();

            $rules = [
                'name'     => 'required|regex:/^[\pL\s\-]+$/u',
                'email'    => 'required|email|unique:users',
                'password' => 'required'
            ];
            $customMessages = [
                'name.required'     => 'Your Name (field) is required',

                'email.required'    => 'Your email (field) is required',
                'email.email'       => 'Your email (field) must be a Valid email',
                'email.unique'      => 'Your email already exists in our database!',

                'password.required' => 'Your password (field) is required'
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);       
 
            }
            $user = new User;
            $user->name     = $userData['name'];
            $user->email    = $userData['email'];
            $user->password = bcrypt($userData['password']); 
            $user->save();
            return response()->json([ 
                'message' => 'User added successfully!',
                'user'    => $user
            ], 201);        

        } else { 
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([
                'status'  => false,
                'message' => $message
            ], 422);
        }
    }

    public function addMultipleUsers(Request $request) {
        if ($request->isMethod('post')) {
            $userData = $request->input();
            
            $rules = [
                'users.*.name'     => 'required|regex:/^[\pL\s\-]+$/u', 
                'users.*.email'    => 'required|email|unique:users',    
                'users.*.password' => 'required'                        
            ];

            $customMessages = [
                'users.*.name.required'     => 'Your Name (field) is required',              

                'users.*.email.required'    => 'Your email (field) is required',             
                'users.*.email.email'       => 'Your email (field) must be a Valid email',   
                'users.*.email.unique'      => 'Your email already exists in our database!', 

                'users.*.password.required' => 'Your password (field) is required'           
            ];
            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);       
 
            }
            foreach ($userData['users'] as $key => $value) {
                $user = new User;
                $user->name     = $value['name'];
                $user->email    = $value['email'];
                $user->password = bcrypt($value['password']); 
                $user->save();
            }

            return response()->json([ 
                'message' => 'Users added successfully!'
            ], 201);      

        } else {
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422); 
        }
    }

    public function updateUserDetails(Request $request, $id = null) {
        if ($request->isMethod('put')) { 
            $userData = $request->input();

            $rules = [
                'name'     => 'required|regex:/^[\pL\s\-]+$/u',
                'email'    => 'required|email',
                'password' => 'required'
            ];

            $customMessages = [
                'name.required'     => 'Your Name (field) is required',

                'email.required'    => 'Your email (field) is required',
                'email.email'       => 'Your email (field) must be a Valid email',

                'password.required' => 'Your password (field) is required'
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);       
 
            }
            User::where('id', isset($id) ? $id : $userData['id'])->update([ 
                'name'     => $userData['name'],
                'email'    => $userData['email'],
                'password' => bcrypt($userData['password'])
            ]);
            return response()->json([ 
                'message' => 'User details updated successfully!'
            ], 202);        

        } else {
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422); 
        }
    }

    public function updateUserName(Request $request, $id = null) {
        if ($request->isMethod('patch')) { 
            $userData = $request->input();
            $rules = [
                'name'     => 'required|regex:/^[\pL\s\-]+$/u', 
            ];
            $customMessages = [
                'name.required'     => 'Your Name (field) is required'
            ];
            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);       
 
            }
            User::where('id', isset($id) ? $id : $userData['id'])->update(['name' => $userData['name']]); 

            return response()->json([
                'message' => 'User\'s name ONLY updated successfully!'

            ], 202);       

        } else { 
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422);  
        }
    }

    public function deleteUser(Request $request, $id = null) { 
        if ($request->isMethod('delete')) {
            $userData = $request->input();

            User::where('id', isset($id) ? $id : $userData['id'])->delete(); 

            return response()->json([ 
                'message' => 'User deleted successfully!'
            ], 202/* 204 */);

        } else {
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422); 
        }
    }

    public function deleteMultipleUsers(Request $request, $ids = null) { 
        if ($request->isMethod('delete')) { 
            $userData = $request->input();

            if (isset($ids)) { 
                $ids = array_map('trim', (explode(',', $ids)));
                User::whereIn('id', $ids)->delete();

                return response()->json([ 
                    'message' => 'Users deleted successfully!'
                ], 202/* 204 */);

            } else { 

                User::whereIn('id', $userData['ids'])->delete(); 
                return response()->json([ 
                    'message' => 'Users deleted successfully!'
                ], 202/* 204 */); 
            }

        } else { 
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422);    
        }
    }

    public function registerUser(Request $request) {
        if ($request->isMethod('post')) { 
            $userData = $request->input();

            
            $rules = [
                'name'     => 'required|regex:/^[\pL\s\-]+$/u', 
                'email'    => 'required|email|unique:users', 
                'password' => 'required'
            ];
            $customMessages = [
                'name.required'     => 'Your Name (field) is required',

                'email.required'    => 'Your email (field) is required',
                'email.email'       => 'Your email (field) must be a Valid email',
                'email.unique'      => 'Your email already exists in our database!',

                'password.required' => 'Your password (field) is required'
            ];
            
            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);        
            }
            $accessToken = \Illuminate\Support\Str::random(60); 
            $user = new User;

            $user->name     = $userData['name'];
            $user->email    = $userData['email'];
            $user->password = bcrypt($userData['password']); 
            $user->access_token = $accessToken;

            $user->save();


            return response()->json([
                'status'  => true,
                'message' => 'User registered successfully!',
                'token'   => $accessToken,
                'user'    => $user
            ], 201);       

        } else { 
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422);
        }
    }

    public function loginUser(Request $request) {
        if ($request->isMethod('post')) { 
            $userData = $request->input();

            $rules = [
                'email'    => 'required|email|exists:users',
                'password' => 'required'
            ];

            $customMessages = [
                'email.required'    => 'Your email (field) is required',
                'email.email'       => 'Your email (field) must be a Valid email',
                'email.exists'      => 'Your email does not exist in our database!',

                'password.required' => 'Your password (field) is required'
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);       
 
            }
            $userDetails = User::where('email', $userData['email'])->first();
            if (password_verify($userData['password'], $userDetails->password)) {
                $accessToken = \Illuminate\Support\Str::random(60); 
                User::where('email', $userData['email'])->update(['access_token' => $accessToken]);


                return response()->json([
                    'status'  => true,
                    'message' => 'User logged in successfully!',
                    'token'   => $accessToken
                ], 201); 

            } else { 
                return response()->json([ 
                    'status'  => false,
                    'message' => 'Password is incorrect!'
                ], 422); 
            }

        } else {
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422);

        }
    }

    public function logoutUser(Request $request) {
        if ($request->isMethod('post')) { 
            $api_token = $request->header('Authorization'); 

            if (empty($api_token)) {
                return response()->json([ 
                    'status'  => false,
                    'message' => '\'Authorization\' HTTP Request Header value (i.e. Bearer Access Token) is missing!'
                ], 422);            

            } else { 
                $api_token = str_replace('Bearer ', '', $api_token);

                $api_token_count = User::where('access_token', $api_token)->count();
                if ($api_token_count > 0) { 
                    User::where('access_token', $api_token)->update(['access_token' => null]);

                   
                    return response()->json([ 
                        'status'  => true,
                        'message' => 'User logged out successfully!',
                    ], 200);    

                } else {
                    return response()->json([ 
                        'status'  => false,
                        'message' => 'Your submitted \'Authorization\' HTTP Request Header value (i.e. the Bearer Access Token) does not exist in our database!'
                    ], 422); 
                }

            }

        } else { 

            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';

            return response()->json([
                'status'  => false,
                'message' => $message
            ], 422);    

        }
    }
    public function registerUserWithPassport(Request $request) {
        if ($request->isMethod('post')) { 
            $userData = $request->input();

            
            $rules = [
                'name'     => 'required|regex:/^[\pL\s\-]+$/u', 
                'email'    => 'required|email|unique:users', 
                'password' => 'required'
            ];

            $customMessages = [
                'name.required'     => 'Your Name (field) is required',

                'email.required'    => 'Your email (field) is required',
                'email.email'       => 'Your email (field) must be a Valid email',
                'email.unique'      => 'Your email already exists in our database!',

                'password.required' => 'Your password (field) is required'
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);       
 
            }
            $user = new User;

            $user->name     = $userData['name'];
            $user->email    = $userData['email'];
            $user->password = bcrypt($userData['password']); 

            $user->save();

            if (\Illuminate\Support\Facades\Auth::attempt(['email' => $userData['email'], 'password' => $userData['password']])) {
                $user = User::where('email', $userData['email'])->first();

                $accessToken = $user->createToken($userData['email'])->accessToken;

                User::where('email', $userData['email'])->update(['access_token' => $accessToken]);

                return response()->json([ 
                    'status'  => true,
                    'message' => 'User registered successfully! (using Passport)',
                    'token'   => $accessToken,
                    'user'    => $user
                ], 201);       
            
            } else { 
                return response()->json([ 
                    'status'  => false,
                    'message' => 'Wrong credentials! Incorrect email or password!'
                ], 422); 
            }

        } else { 
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';
            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422); 
    }
}

    public function loginUserWithPassport(Request $request) {
        if ($request->isMethod('post')) {
            $userData = $request->input();

            $rules = [
                'email'    => 'required|email|exists:users', 
                'password' => 'required'
            ];

            $customMessages = [
                'email.required'    => 'Your email (field) is required',
                'email.email'       => 'Your email (field) must be a Valid email',
                'email.exists'      => 'Your email does not exist in our database!',

                'password.required' => 'Your password (field) is required'
            ];
            
            $validator = \Illuminate\Support\Facades\Validator::make($userData, $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);       
 
            }


            if (\Illuminate\Support\Facades\Auth::attempt(['email' => $userData['email'], 'password' => $userData['password']])) {
                $user = User::where('email', $userData['email'])->first();


                $accessToken = $user->createToken($userData['email'])->accessToken; 

                User::where('email', $userData['email'])->update(['access_token' => $accessToken]);


                return response()->json([ 
                    'status'  => true,
                    'message' => 'User logged in successfully! (using Passport)',
                    'token'   => $accessToken,
                    'user'    => $user
                ], 201);    
            
            } else {
                return response()->json([ 
                    'status'  => false,
                    'message' => 'Wrong credentials! Incorrect email or password!'
                ], 422);   
            }

        } else { 
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';
            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422); 
        }
    }

    public function updateStock(Request $request) {
        if ($request->isMethod('post')) { 
            $userData = $request->input();

            
            $header = $request->header('Authorization'); 
            if (empty($header)) {
                $message = '\'Authorization\' HTTP Request Header value (i.e. Bearer Access Token) is missing!';
                return response()->json([ 
                    'status'  => false,
                    'message' => $message
                ], 422); 

            } else { 

                if ($header == 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkFtaXQgR3VwdGEiLCJpYXQiOjE1MTYyMzkwMjJ9.cNrgi6Sso9wvs4GlJmFnA4IqJY4o2QEcKXgshJTjfNg') { // if the user sends the correct HTTP Request Header value (i.e. correct Bearer Access Token) with their HTTP Request    // JWT token generated by (we wrote in "Ahmed Yahya" to generate this token in the website): https://jwt.io/    // User must send an "Authorization" HTTP Header with all their HTTP Requests with this value (Bearer Token (JWT)): "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkFtaXQgR3VwdGEiLCJpYXQiOjE1MTYyMzkwMjJ9.cNrgi6Sso9wvs4GlJmFnA4IqJY4o2QEcKXgshJTjfNg"

                    $url = 'http://sitemakers.in/stocks.json'; 
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL            => $url,
                        CURLOPT_RETURNTRANSFER => true, 
                        CURLOPT_HEADER         => false 
                    ]);

                    $data = curl_exec($curl);

                    curl_close($curl);

                    $data = json_decode($data, true); 

                    if (isset($data['items'])) { 
                        foreach ($data['items'] as $key => $value) {
                            ProductsAttribute::where('sku', $value['sku'])->update([ 'stock' => $value['stock'] ]);
                        }

                        return response()->json([ 
                            'status'  => true,
                            'message' => 'Products stocks updated successfully!',
                        ], 200);  
                    } else { 
                        return response()->json([
                            'status'  => false,
                            'message' => 'No items found!'
                        ], 422);
                    }

                } else {
                    return response()->json([ 
                        'status'  => false,
                        'message' => 'Your submitted \'Authorization\' HTTP Request Header value (i.e. the Bearer Access Token) is incorrect!'
                    ], 422); 
                }

            }

        } else {
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';
            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422); 
        }
    }

    public function updateStockWithWebhook(Request $request) {
        if ($request->isMethod('post')) {

           
            $header = $request->header('Authorization');

            if (empty($header)) {
                $message = '\'Authorization\' HTTP Request Header value (i.e. Bearer Access Token) is missing!';
                return response()->json([
                    'status'  => false,
                    'message' => $message
                ], 422);

            } else { 

               
                if ($header == 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkFtaXQgR3VwdGEiLCJpYXQiOjE1MTYyMzkwMjJ9.cNrgi6Sso9wvs4GlJmFnA4IqJY4o2QEcKXgshJTjfNg') { // if the user sends the correct HTTP Request Header value (i.e. correct Bearer Access Token) with their HTTP Request    // JWT token generated by (we wrote in "Ahmed Yahya" to generate this token in the website): https://jwt.io/    // User must send an "Authorization" HTTP Header with all their HTTP Requests with this value (Bearer Token (JWT)): "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkFtaXQgR3VwdGEiLCJpYXQiOjE1MTYyMzkwMjJ9.cNrgi6Sso9wvs4GlJmFnA4IqJY4o2QEcKXgshJTjfNg"
                    $data = $request->all();

                    if (isset($data['items'])) {
                        foreach ($data['items'] as $key => $value) {
                            ProductsAttribute::where('sku', $value['sku'])->update([ 'stock' => $value['stock'] ]);
                        }

                        return response()->json([
                            'status'  => true,
                            'message' => 'Products stocks updated successfully! (through/via Webhook)',
                        ], 200);

                    } else {
                        return response()->json([
                            'status'  => false,
                            'message' => 'No items found!'
                        ], 422); 
                    }

                } else { 
                    return response()->json([ 
                        'status'  => false,
                        'message' => 'Your submitted \'Authorization\' HTTP Request Header value (i.e. the Bearer Access Token) is incorrect!'
                    ], 422);  
                }

            }

        } else {
            $message = 'You\'re using an incorrect/invalid HTTP Request Method/Verb to access this route/endpoint in our API!';
            return response()->json([ 
                'status'  => false,
                'message' => $message
            ], 422);
        }
    }

}