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
    // Note: In the Admin Panel, in the Orders Management section, if the authenticated/logged-in user is 'vendor', we'll show the orders of the products added by/related to that 'vendor' ONLY, but if the authenticated/logged-in user is 'admin', we'll show ALL orders    



    // Render admin/orders/orders.blade.php page (Orders Management section) in the Admin Panel    
    public function orders() {
        // Correcting issues in the Skydash Admin Panel Sidebar using Session
        Session::put('page', 'orders');


        // We determine the authenticated/logged-in user. If the authenticated/logged-in user is 'vendor', we show ONLY the orders of the products added by that specific 'vendor' ONLY, but if the authenticated/logged-in user is 'admin', we show ALL orders    
        $adminType = Auth::guard('admin')->user()->type;      // `type`      is the column in `admins` table    // Accessing Specific Guard Instances: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances    // Retrieving The Authenticated User and getting their `type`      column in `admins` table    
        $vendor_id = Auth::guard('admin')->user()->vendor_id; // `vendor_id` is the column in `admins` table    // Accessing Specific Guard Instances: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances    // Retrieving The Authenticated User and getting their `vendor_id` column in `admins` table    


        if ($adminType == 'vendor') { // if the authenticated user (the logged in user) is 'vendor', check his `status`
            $vendorStatus = Auth::guard('admin')->user()->status; // `status` is the column in `admins` table    // Accessing Specific Guard Instances: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances    // Retrieving The Authenticated User and getting their `status` column in `admins` table    

            if ($vendorStatus == 0) { // if the 'vendor' is inactive/disabled
                return redirect('admin/update-vendor-details/personal')->with('error_message', 'Your Vendor Account is not approved yet. Please make sure to fill your valid personal, business and bank details.'); // the error_message will appear to the vendor in the route: 'admin/update-vendor-details/personal' which is the update_vendor_details.blade.php page
            }
        }


        if ($adminType == 'vendor') { // If the authenticated/logged-in user is 'vendor', we show ONLY the orders of the products added by that specific 'vendor' ONLY
            $orders = Order::with([ // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'orders_products' is the relationship method name in Order.php model    // Constraining Eager Loads: https://laravel.com/docs/9.x/eloquent-relationships#constraining-eager-loads    // Subquery Where Clauses: https://laravel.com/docs/9.x/queries#subquery-where-clauses    // Advanced Subqueries: https://laravel.com/docs/9.x/eloquent#advanced-subqueries
                'orders_products' => function($query) use ($vendor_id) { // function () use ()     syntax: https://www.php.net/manual/en/functions.anonymous.php#:~:text=the%20use%20language%20construct     // 'orders_products' is the Relationship method name in Order.php model
                    $query->where('vendor_id', $vendor_id); // `vendor_id` in `orders_products` table
                }
            ])->orderBy('id', 'Desc')->get()->toArray();
            // dd($orders);

        } else { // if the authenticated/logged-in user is 'admin', we show ALL orders
            $orders = Order::with('orders_products')->orderBy('id', 'Desc')->get()->toArray(); // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'orders_products' is the relationship method name in Order.php model
            // dd($orders);
        }


        return view('admin.orders.orders')->with(compact('orders'));
    }

    // Render admin/orders/order_details.blade.php (View Order Details page) when clicking on the View Order Details icon in admin/orders/orders.blade.php (Orders tab under Orders Management section in Admin Panel)    
    public function orderDetails($id) {
        // Correcting the Admin Panel Sidebar session to show the 'orders' page as active
        Session::put('page', 'orders');
    
        // Determine whether the authenticated user is an admin or a vendor
        $adminType = Auth::guard('admin')->user()->type;
        $vendor_id = Auth::guard('admin')->user()->vendor_id;
    
        if ($adminType == 'vendor') {
            // If the vendor's status is inactive, redirect them to update their details
            if (Auth::guard('admin')->user()->status == 0) {
                return redirect('admin/update-vendor-details/personal')->with('error_message', 'Your Vendor Account is not approved yet.');
            }
    
            // Retrieve order details with related products for the specific vendor
            $orderDetails = Order::with(['orders_products' => function ($query) use ($vendor_id) {
                $query->where('vendor_id', $vendor_id); // Only retrieve products for this vendor
            }])->where('id', $id)->first()->toArray();
        } else {
            // If the user is an admin, retrieve the entire order and its related products
            $orderDetails = Order::with('orders_products').where('id', $id).first()->toArray();
        }
    
        // Retrieve user details associated with the order
        $userDetails = User::where('id', $orderDetails['user_id'])->first()->toArray();
    
        // Retrieve active order statuses
        $orderStatuses = OrderStatus::where('status', 1)->get()->toArray();
    
        // Retrieve active order item statuses
        $orderItemStatuses = OrderItemStatus::where('status', 1)->get()->toArray();
    
        // Calculate the total number of items in the order
        $total_items = 0;
        foreach ($orderDetails['orders_products'] as $product) {
            $total_items += $product['product_qty']; // Total quantity of all items in the order
        }
    
        return view('admin.orders.order_details')->with(compact('orderDetails', 'userDetails', 'orderStatuses', 'orderItemStatuses', 'total_items'));
    }
    

    // Update Order Status (by 'admin'-s ONLY, not 'vendor'-s, in contrast to "Update Item Status" which can be updated by both 'vendor'-s and 'admin'-s) (Pending, Shipped, In Progress, Canceled, ...) in admin/orders/order_details.blade.php in Admin Panel    
    // Note: The `order_statuses` table contains all kinds of order statuses (that can be updated by 'admin'-s ONLY in `orders` table) like: pending, in progress, shipped, canceled, ...etc. In `order_statuses` table, the `name` column can be: 'New', 'Pending', 'Canceled', 'In Progress', 'Shipped', 'Partially Shipped', 'Delivered', 'Partially Delivered' and 'Paid'. 'Partially Shipped': If one order has products from different vendors, and one vendor has shipped their product to the customer while other vendor (or vendors) didn't!. 'Partially Delivered': if one order has products from different vendors, and one vendor has shipped and DELIVERED their product to the customer while other vendor (or vendors) didn't!    // The `order_item_statuses` table contains all kinds of order statuses (that can be updated by both 'vendor'-s and 'admin'-s in `orders_products` table) like: pending, in progress, shipped, canceled, ...etc.
    public function updateOrderStatus(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);

            // Note: There are two types of Shipping Process: "manual" and "automatic". "Manual" is in the case like small businesses, where the courier arrives at the owner warehouse to to pick up the order for shipping, and the small business owner takes the shipment details (like courier name, tracking number, ...) from the courier, and inserts those details themselves in the Admin Panel when they "Update Order Status" Section (by an 'admin') or "Update Item Status" Section (by a 'vendor' or 'admin') (in admin/orders/order_details.blade.php). With "automatic" shipping process, we're integrating third-party APIs (e.g. Shiprocket API) and orders go directly to the shipping partner, and the updates comes from the courier's end, and orders are automatically delivered to customers
            // "Automatic" Shipping Process (when 'admin' does NOT enter the Courier Name and Tracking Number): Configure the Shiprocket API in our Admin Panel in admin/orders/order_details.blade.php (to automate Pushing Orders to Shiprocket API by selecting "Shipped" from the drop-down menu)    
            if (empty($data['courier_name']) && empty($data['tracking_number']) && $data['order_status'] == 'Shipped') { // if the 'admin' didn't enter the Courier Name and Tracking Nubmer when they selected "Shipped" from the drop-down menu in admin/orders/order_details.blade.php, use the "Automatic" Shipping Process (Push Orders to Shiprocket API), not the "Manual" Shipping process. Check the "Manual" Shipping process in the next if statement
                // dd('Inside Automatic Shipping Process if statement in updateOrderStatus() method in Admin/OrderController.php<br>');
                // echo 'Inside Automatic Shipping Process if statement in updateOrderStatus() method in Admin/OrderController.php<br>';
                // exit;

                $getResults = Order::pushOrder($data['order_id']);
                // dd($getResults);
                if (!isset($getResults['status']) || (isset($getResults['status']) && $getResults['status'] == false)) { // If Status is not coming at all, or it's coming but it's false
                    Session::put('error_message', $getResults['message']); // The message is coming from the Shiprocket API    // Storing Data: https://laravel.com/docs/9.x/session#storing-data

                    return redirect()->back(); // Redirecting With Flashed Session Data: https://laravel.com/docs/10.x/responses#redirecting-with-flashed-session-data
                    // return redirect()->back()->with('error_message', $getResults['message']); // Redirecting With Flashed Session Data: https://laravel.com/docs/10.x/responses#redirecting-with-flashed-session-data
                }
            }


            // Update Order Status in `orders` table
            Order::where('id', $data['order_id'])->update(['order_status' => $data['order_status']]);


            // Note: There are two types of Shipping Process: "manual" and "automatic". "Manual" is in the case like small businesses, where the courier arrives at the owner warehouse to to pick up the order for shipping, and the small business owner takes the shipment details (like courier name, tracking number, ...) from the courier, and inserts those details themselves in the Admin Panel when they "Update Order Status" Section (by an 'admin') or "Update Item Status" Section (by a 'vendor' or 'admin') (in admin/orders/order_details.blade.php). With "automatic" shipping process, we're integrating third-party APIs (e.g. Shiprocket API) and orders go directly to the shipping partner, and the updates comes from the courier's end, and orders are automatically delivered to customers
            // First: "Manual" Shipping Process (when 'admin' enters the Courier Name and Tracking Number. Check the last if statement for the "Automatic" Shipping Process) (Business owner takes the order shipment information from the courier and inserts them themselves when they "Update Order Status" (by an 'admin') (in admin/orders/order_details.blade.php)) i.e. Updating `courier_name` and `tracking_number` columns in `orders` table
            if (!empty($data['courier_name']) && !empty($data['tracking_number'])) { // if an 'admin' Updates the Order Status to 'Shipped' in admin/orders/order_details.blade.php, and submits both Courier Name and Tracking Number HTML input fields
                Order::where('id', $data['order_id'])->update([
                    'courier_name'    => $data['courier_name'],
                    'tracking_number' => $data['tracking_number']
                ]);
            }


            // We'll save the "Update Order Status" History/Logs in `orders_logs` database table (whenever an 'admin' updates an order status)    
            $log = new OrdersLog;
            $log->order_id     = $data['order_id'];
            $log->order_status = $data['order_status'];
            $log->save();


            // "Update Order Status" email: We send an email and SMS to the user when the general Order Status is updated by an 'admin' (pending, shipped, in progress, …)
            $deliveryDetails = Order::select('mobile', 'email', 'name')->where('id', $data['order_id'])->first()->toArray();
            $orderDetails    = Order::with('orders_products')->where('id', $data['order_id'])->first()->toArray(); // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'orders_products' is the relationship method name in Order.php model

            
            if (!empty($data['courier_name']) && !empty($data['tracking_number'])) { // if an 'admin' Updates the Order Status to 'Shipped' in admin/orders/order_details.blade.php, and submits both Courier Name and Tracking Number HTML input fields, include the Courier Name and Tracking Nubmer data in the email (send them with the email)
                $email = $deliveryDetails['email'];

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'email'           => $email,
                    'name'            => $deliveryDetails['name'],
                    'order_id'        => $data['order_id'],
                    'orderDetails'    => $orderDetails,
                    'order_status'    => $data['order_status'],
                    'courier_name'    => $data['courier_name'],
                    'tracking_number' => $data['tracking_number']
                ];

                \Illuminate\Support\Facades\Mail::send('emails.order_status', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.order_status' is the order_status.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that order_status.blade.php will use    // https://www.php.net/manual/en/functions.anonymous.php
                    $message->to($email)->subject('Order Status Updated - MultiVendorEcommerceApplication.com.eg');
                });

            } else { // if there are no Courier Name and Tracking Number data, don't include them in the email
                $email = $deliveryDetails['email'];

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'email'        => $email,
                    'name'         => $deliveryDetails['name'],
                    'order_id'     => $data['order_id'],
                    'orderDetails' => $orderDetails,
                    'order_status' => $data['order_status']
                ];
    
                \Illuminate\Support\Facades\Mail::send('emails.order_status', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.order_status' is the order_status.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that order_status.blade.php will use    // https://www.php.net/manual/en/functions.anonymous.php
                    $message->to($email)->subject('Order Status Updated - MultiVendorEcommerceApplication.com.eg');
                });
            }

            $message = 'Order Status has been updated successfully!';


            return redirect()->back()->with('success_message', $message);
        }
    }

    // Update Item Status (which can be determined by both 'vendor'-s and 'admin'-s, in contrast to "Update Order Status" which is updated by 'admin'-s ONLY, not 'vendor'-s) (Pending, In Progress, Shipped, Delivered, ...) in admin/orders/order_details.blade.php in Admin Panel    
    // Note: The `order_statuses` table contains all kinds of order statuses (that can be updated by 'admin'-s ONLY in `orders` table) like: pending, in progress, shipped, canceled, ...etc. In `order_statuses` table, the `name` column can be: 'New', 'Pending', 'Canceled', 'In Progress', 'Shipped', 'Partially Shipped', 'Delivered', 'Partially Delivered' and 'Paid'. 'Partially Shipped': If one order has products from different vendors, and one vendor has shipped their product to the customer while other vendor (or vendors) didn't!. 'Partially Delivered': if one order has products from different vendors, and one vendor has shipped and DELIVERED their product to the customer while other vendor (or vendors) didn't!    // The `order_item_statuses` table contains all kinds of order statuses (that can be updated by both 'vendor'-s and 'admin'-s in `orders_products` table) like: pending, in progress, shipped, canceled, ...etc.
    public function updateOrderItemStatus(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);

            // Update Order Item Status in `orders_products` table
            OrdersProduct::where('id', $data['order_item_id'])->update(['item_status' => $data['order_item_status']]);


            // Note: There are two types of Shipping Process: "manual" and "automatic". "Manual" is in the case like small businesses, where the courier arrives at the owner warehouse to to pick up the order for shipping, and the small business owner takes the shipment details (like courier name, tracking number, ...) from the courier, and inserts those details themselves in the Admin Panel when they "Update Order Status" Section (by an 'admin') or "Update Item Status" Section (by a 'vendor' or 'admin') (in admin/orders/order_details.blade.php). With "automatic" shipping process, we're integrating third-party APIs and orders go directly to the shipping partner, and the updates comes from the courier's end, and orders are automatically delivered to customers
            // First: "Manual" Shipping Process (Business owner takes the order shipment information from the courier and inserts them themselves when they "Update Order Item Status" (by a 'vendor' or 'admin') (in admin/orders/order_details.blade.php)) i.e. Updating `courier_name` and `tracking_number` columns in `orders_products` table
            if (!empty($data['item_courier_name']) && !empty($data['item_tracking_number'])) { // if a 'vendor' or 'admin' updates the order Item Status to 'Shipped' in admin/orders/order_details.blade.php, and submits both Courier Name and Tracking Number HTML input fields
                OrdersProduct::where('id', $data['order_item_id'])->update([
                    'courier_name'    => $data['item_courier_name'],
                    'tracking_number' => $data['item_tracking_number']
                ]);
            }


            // Get the `order_id` column (which is the foreign key to the `id` column in `orders` table) value from `orders_products` table
            $getOrderId = OrdersProduct::select('order_id')->where('id', $data['order_item_id'])->first()->toArray();


            // We'll save the Update "Item Status" History/Logs in `orders_logs` database table (whenever a 'vendor' or 'admin' updates an order item status)    
            // Note: In `orders_logs` table, if the `order_item_id` column is zero 0, this means the "Item Status" has never been updated, and if it's not zero 0, this means it's been previously updated by a 'vendor' or 'admin' and the number references/denotes the `id` column (foreign key) of the `orders_products` table
            $log = new OrdersLog;
            $log->order_id      = $getOrderId['order_id'];
            $log->order_item_id = $data['order_item_id'];
            $log->order_status  = $data['order_item_status'];
            $log->save();


            // "Item Status" update email: We send an email and SMS to the user when the Item Status (in the "Ordered Products" section) is updated by a 'vendor' or 'admin' (pending, shipped, in progress, …) for every product on its own in the email (not the whole order products, but the email is about the product that has been updated ONLY)
            $deliveryDetails = Order::select('mobile', 'email', 'name')->where('id', $getOrderId['order_id'])->first()->toArray();

            // Making sure that ONLY ONE order product (from the `orders_products` table) that has been item status updated, NOT all the order products, are sent in the email
            $order_item_id = $data['order_item_id'];
            $orderDetails  = Order::with([ // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'orders_products' is the relationship method name in Order.php model    // Constraining Eager Loads: https://laravel.com/docs/9.x/eloquent-relationships#constraining-eager-loads    // Subquery Where Clauses: https://laravel.com/docs/9.x/queries#subquery-where-clauses    // Advanced Subqueries: https://laravel.com/docs/9.x/eloquent#advanced-subqueries
                'orders_products' => function($query) use ($order_item_id) { // function () use ()     syntax: https://www.php.net/manual/en/functions.anonymous.php#:~:text=the%20use%20language%20construct     // 'orders_products' is the Relationship method name in Order.php model
                    $query->where('id', $order_item_id); // `id` column in `orders_products` table
                }
            ])->where('id', $getOrderId['order_id'])->first()->toArray(); // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'orders_products' is the relationship method name in Order.php model
            // dd($orderDetails);
            // Note: Now in this case, updating the item status of one product will send an email to user but with telling the item statuses of all of the Order items (not ONLY the item with the status updated!). The solution to this is using a subquery (Constraining Eager Loads)


            if (!empty($data['item_courier_name']) && !empty($data['item_tracking_number'])) { // if a 'vendor' or 'admin' Updates the Order "Item Status" to 'Shipped' in admin/orders/order_details.blade.php, and submits both Courier Name and Tracking Number HTML input fields, include the Courier Name and Tracking Nubmer data in the email (send them with the email)
                $email = $deliveryDetails['email'];

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'email'           => $email,
                    'name'            => $deliveryDetails['name'],
                    'order_id'        => $getOrderId['order_id'],
                    'orderDetails'    => $orderDetails,
                    'order_status'    => $data['order_item_status'],
                    'courier_name'    => $data['item_courier_name'],
                    'tracking_number' => $data['item_tracking_number']
                ];

                \Illuminate\Support\Facades\Mail::send('emails.order_item_status', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.order_item_status' is the order_item_status.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that order_item_status.blade.php will use    // https://www.php.net/manual/en/functions.anonymous.php
                    $message->to($email)->subject('Order Item Status Updated - MultiVendorEcommerceApplication.com.eg');
                });

            } else { // if there are no Courier Name and Tracking Number data, don't include them in the email
                $email = $deliveryDetails['email'];

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'email'        => $email,
                    'name'         => $deliveryDetails['name'],
                    'order_id'     => $getOrderId['order_id'],
                    'orderDetails' => $orderDetails,
                    'order_status' => $data['order_item_status']
                ];
    
                \Illuminate\Support\Facades\Mail::send('emails.order_item_status', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.order_item_status' is the order_item_status.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that order_item_status.blade.php will use    // https://www.php.net/manual/en/functions.anonymous.php
                    $message->to($email)->subject('Order Item Status Updated - MultiVendorEcommerceApplication.com.eg');
                });
            }

            $message = 'Order Item Status has been updated successfully!';


            return redirect()->back()->with('success_message', $message);
        }
    }

    // Render order invoice page (HTML) in order_invoice.blade.php    
    public function viewOrderInvoice($order_id) { // Route Parameters: Required Parameters: https://laravel.com/docs/9.x/routing#required-parameters
        $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray(); // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'orders_products' is the relationship method name in Order.php model
        // dd($orderDetails);
        $userDetails = User::where('id', $orderDetails['user_id'])->first()->toArray(); // details of the user who made the order


        return view('admin.orders.order_invoice')->with(compact('orderDetails', 'userDetails'));
    }
}