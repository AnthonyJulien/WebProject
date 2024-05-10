<?php


namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\ProductsAttribute;

use Omnipay\Omnipay;



class PaypalController extends Controller
{
    private $gateway;
   public function __construct() {
        $this->gateway = Omnipay::create('PayPal_Rest');

        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));  
        $this->gateway->setSecret(env('PAYPAL_CLIENT_SECRET')); 
        $this->gateway->setTestMode(true);
    }

    public function pay(Request $request) {
        try {
            $paypal_amount = round(Session::get('grand_total') / 80, 2); 

            $response = $this->gateway->purchase(array( 
                'amount'    => $paypal_amount,
                'currency'  => env('PAYPAL_CURRENCY'), 
                'returnUrl' => url('success'), 
                'cancelUrl' => url('error')
            ))->send();

            if ($response->isRedirect()) { 
                $response->redirect();
            } else {
                return $response->getMessage();
            }

        } catch (\Throwable $th) {   
            return $th->getMessage(); 
        }
    }

    public function success(Request $request) {
        if (!Session::has('order_id')) { 
            return view('cart');
        }


        if ($request->input('paymentId') && $request->input('PayerID')) {
            $transaction = $this->gateway->completePurchase(array(
                'payer_id'             => $request->input('PayerID'),
                'transactionReference' => $request->input('paymentId'),
            ));

            $response = $transaction->send(); 

            if ($response->isSuccessful()) { 
                $arr = $response->getData();

                $payment = new \App\Models\Payment;
                $payment->order_id       = Session::get('order_id'); 
                $payment->user_id        = Auth::user()->id;
                $payment->payment_id     = $arr['id']; 
                $payment->payer_id       = $arr['payer']['payer_info']['payer_id'];  
                $payment->payer_email    = $arr['payer']['payer_info']['email'];     
                $payment->amount         = $arr['transactions'][0]['amount']['total']; 
                $payment->currency       = env('PAYPAL_CURRENCY'); 
                $payment->payment_status = $arr['state'];

                $payment->save();

                $order_id = Session::get('order_id');
                Order::where('id', $order_id)->update(['order_status' => 'Paid']);


                $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray(); 
                $email = Auth::user()->email; 
                $messageData = [
                    'email'        => $email,
                    'name'         => Auth::user()->name,
                    'order_id'     => $order_id,
                    'orderDetails' => $orderDetails
                ];

                \Illuminate\Support\Facades\Mail::send('emails.order', $messageData, function ($message) use ($email) {
                    $message->to($email)->subject('Order Paid through PayPal - MultiVendorEcommerceApplication.com.eg');
                });


                foreach ($orderDetails['orders_products'] as $key => $order) {
                    $getProductStock = ProductsAttribute::getProductStock($order['product_id'], $order['product_size']);

                    $newStock = $getProductStock - $order['product_qty'];

                    ProductsAttribute::where([
                        'product_id' => $order['product_id'],
                        'size'       => $order['product_size']
                    ])->update(['stock' => $newStock]);
                }


                \App\Models\Cart::where('user_id', Auth::user()->id)->delete();


                return view('front.paypal.success');

            } else {
                return $response->getMessage(); 
            }

        } else {
            return 'Payment Declined!';
        }
    }

    
    public function error() {
        
        return view('front.paypal.fail');
    }

    public function paypal() {
        if (Session::has('order_id')) { 
            return view('front.paypal.paypal');

        } else { 
            return redirect('cart');
        }
    }

}