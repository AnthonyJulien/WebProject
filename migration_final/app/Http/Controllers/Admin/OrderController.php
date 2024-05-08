<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\OrdersLog;
use App\Models\OrderStatus;
use App\Models\OrderItemStatus;


class OrderController extends Controller
{
    public function orders() {
        Session::put('page', 'orders');

        $adminType = Auth::guard('admin')->user()->type;
        $vendor_id = Auth::guard('admin')->user()->vendor_id;
          if ($adminType == 'vendor') { 
            $vendorStatus = Auth::guard('admin')->user()->status; 

            if ($vendorStatus == 0) { 
                return redirect('admin/update-vendor-details/personal')->with('error_message', 'Your Vendor Account is not approved yet. Please make sure to fill your valid personal, business and bank details.'); 
            }
        }


        if ($adminType == 'vendor') { 
            $orders = Order::with([ 
                'orders_products' => function($query) use ($vendor_id) { 
                    $query->where('vendor_id', $vendor_id);
                }
            ])->orderBy('id', 'Desc')->get()->toArray();

        } else { 
            $orders = Order::with('orders_products')->orderBy('id', 'Desc')->get()->toArray();
        }


        return view('admin.orders.orders')->with(compact('orders'));
    }

    public function orderDetails($id) {
        Session::put('page', 'orders');
    
        $adminType = Auth::guard('admin')->user()->type;
        $vendor_id = Auth::guard('admin')->user()->vendor_id;
    
        if ($adminType == 'vendor') {
            if (Auth::guard('admin')->user()->status == 0) {
                return redirect('admin/update-vendor-details/personal')->with('error_message', 'Your Vendor Account is not approved yet.');
            }
    
            $orderDetails = Order::with(['orders_products' => function ($query) use ($vendor_id) {
                $query->where('vendor_id', $vendor_id); 
            }])->where('id', $id)->first()->toArray();
        } else {
            $orderDetails = Order::with('orders_products').where('id', $id).first()->toArray();
        }
    
        $userDetails = User::where('id', $orderDetails['user_id'])->first()->toArray();
    
        $orderStatuses = OrderStatus::where('status', 1)->get()->toArray();
    
        $orderItemStatuses = OrderItemStatus::where('status', 1)->get()->toArray();
    
        $total_items = 0;
        foreach ($orderDetails['orders_products'] as $product) {
            $total_items += $product['product_qty']; 
        }
    
        return view('admin.orders.order_details')->with(compact('orderDetails', 'userDetails', 'orderStatuses', 'orderItemStatuses', 'total_items'));
    }
    

    public function updateOrderStatus(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();


            Order::where('id', $data['order_id'])->update(['order_status' => $data['order_status']]);

            $log = new OrdersLog;
            $log->order_id     = $data['order_id'];
            $log->order_status = $data['order_status'];
            $log->save();

            return redirect()->back()->with('success_message', $message);
        
    }
}

    public function updateOrderItemStatus(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();

            OrdersProduct::where('id', $data['order_item_id'])->update(['item_status' => $data['order_item_status']]);


            $log = new OrdersLog;
            $log->order_id      = $getOrderId['order_id'];
            $log->order_item_id = $data['order_item_id'];
            $log->order_status  = $data['order_item_status'];
            $log->save();

            return redirect()->back()->with('success_message', $message);
        }
    }

    public function viewOrderInvoice($order_id) { 
        $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray(); 
        $userDetails = User::where('id', $orderDetails['user_id'])->first()->toArray();


        return view('admin.orders.order_invoice')->with(compact('orderDetails', 'userDetails'));
    }
}