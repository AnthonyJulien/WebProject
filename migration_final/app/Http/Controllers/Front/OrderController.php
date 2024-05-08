<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;

class OrderController extends Controller
{
    public function orders($id = null) { 
        if (empty($id)) { 
            $orders = Order::with('orders_products')->where('user_id', \Illuminate\Support\Facades\Auth::user()->id)->orderBy('id', 'Desc')->get()->toArray(); 
            return view('front.orders.orders')->with(compact('orders'));

        } else { 
            $orderDetails = Order::with('orders_products')->where('id', $id)->first()->toArray();
            return view('front.orders.order_details')->with(compact('orderDetails'));
        }

    }

}