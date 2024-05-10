<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Category;
use App\Models\DeliveryAddress;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\Rating;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductsFilter;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Country;
use App\Models\ShippingCharge;
use App\Models\OrdersProduct;

class ProductsController extends Controller
{
    public function listing(Request $request) { 
        if ($request->ajax()) {
            $data = $request->all();
            $url          = $data['url'];
            $_GET['sort'] = $data['sort'];
            $categoryCount = Category::where([
                'url'    => $url,
                'status' => 1
            ])->count();
    
            if ($categoryCount > 0) { 
                $categoryDetails = Category::categoryDetails($url);
                $categoryProducts = Product::with('brand')->whereIn('category_id', $categoryDetails['catIds'])->where('status', 1); 
                $productFilters = ProductsFilter::productFilters(); 
                foreach ($productFilters as $key => $filter) {
                    if (isset($filter['filter_column']) && isset($data[$filter['filter_column']]) && !empty($filter['filter_column']) && !empty($data[$filter['filter_column']])) {
                        $categoryProducts->whereIn($filter['filter_column'], $data[$filter['filter_column']]);
                    }
                }
                    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
                    if ($_GET['sort'] == 'product_latest') {
                        $categoryProducts->orderBy('products.id', 'Desc');
                    } elseif ($_GET['sort'] == 'price_lowest') {
                        $categoryProducts->orderBy('products.product_price', 'Asc');
                    } elseif ($_GET['sort'] == 'price_highest') {
                        $categoryProducts->orderBy('products.product_price', 'Desc');
                    } elseif ($_GET['sort'] == 'name_z_a') {
                        $categoryProducts->orderBy('products.product_name', 'Desc');
                    } elseif ($_GET['sort'] == 'name_a_z') {
                        $categoryProducts->orderBy('products.product_name', 'Asc');
                    }
                }
                if (isset($data['size']) && !empty($data['size'])) {
                    $productIds = ProductsAttribute::select('product_id')->whereIn('size', $data['size'])->pluck('product_id')->toArray(); 

                    $categoryProducts->whereIn('products.id', $productIds); 
                }   
                if (isset($data['color']) && !empty($data['color'])) { 
                    $productIds = Product::select('id')->whereIn('product_color', $data['color'])->pluck('id')->toArray(); 

                    $categoryProducts->whereIn('products.id', $productIds); 
                }
                $productIds = array();

                if (isset($data['price']) && !empty($data['price'])) {
                    foreach($data['price'] as $key => $price){
                        $priceArr = explode('-', $price); 
                        if (isset($priceArr[0]) && isset($priceArr[1])) {
                        }
                    }

                    $productIds = array_unique(\Illuminate\Support\Arr::flatten($productIds));
                    $categoryProducts->whereIn('products.id', $productIds);
                }                    
                if (isset($data['brand']) && !empty($data['brand'])) {
                    $productIds = Product::select('id')->whereIn('brand_id', $data['brand'])->pluck('id')->toArray(); 

                    $categoryProducts->whereIn('products.id', $productIds);
                }
                $categoryProducts = $categoryProducts->paginate(30); 

                $meta_title       = $categoryDetails['categoryDetails']['meta_title'];
                $meta_description = $categoryDetails['categoryDetails']['meta_description'];
                $meta_keywords    = $categoryDetails['categoryDetails']['meta_keywords'];


                return view('front.products.ajax_products_listing')->with(compact('categoryDetails', 'categoryProducts', 'url', 'meta_title', 'meta_description', 'meta_keywords'));

            } else {
                abort(404); 
            }
        
        } else { 

            if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
                if ($_REQUEST['search'] == 'new-arrivals') {
                    $search_product = $_REQUEST['search'];

                    $categoryDetails['breadcrumbs']                      = 'New Arrival Products';
                    $categoryDetails['categoryDetails']['category_name'] = 'New Arrival Products';
                    $categoryDetails['categoryDetails']['description']   = 'New Arrival Products';

                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join(
                        'categories', 
                        'categories.id', '=', 'products.category_id'
                    )->where('products.status', 1)->orderBy('id', 'Desc');

                } elseif ($_REQUEST['search'] == 'best-sellers') {
                    $search_product = $_REQUEST['search'];

                    $categoryDetails['breadcrumbs']                      = 'Best Sellers Products';
                    $categoryDetails['categoryDetails']['category_name'] = 'Best Sellers Products';
                    $categoryDetails['categoryDetails']['description']   = 'Best Sellers Products';

                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join(
                        'categories', 
                        'categories.id', '=', 'products.category_id'
                    )->where('products.status', 1)->where('products.is_bestseller', 'Yes');

                } elseif ($_REQUEST['search'] == 'featured') {
                    $search_product = $_REQUEST['search'];

                    $categoryDetails['breadcrumbs']                      = 'Featured Products';
                    $categoryDetails['categoryDetails']['category_name'] = 'Featured Products';
                    $categoryDetails['categoryDetails']['description']   = 'Featured Products';

                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join(
                        'categories', 
                        'categories.id', '=', 'products.category_id' 
                    )->where('products.status', 1)->where('products.is_featured', 'Yes');

                } elseif ($_REQUEST['search'] == 'discounted') {
                    $search_product = $_REQUEST['search'];

                    $categoryDetails['breadcrumbs']                      = 'Discounted Products';
                    $categoryDetails['categoryDetails']['category_name'] = 'Discounted Products';
                    $categoryDetails['categoryDetails']['description']   = 'Discounted Products';

                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join( 
                        'categories', 
                        'categories.id', '=', 'products.category_id' 
                    )->where('products.status', 1)->where('products.product_discount', '>', 0); 


                } else { 
                    $search_product = $_REQUEST['search'];

                    $categoryDetails['breadcrumbs']                      = $search_product;
                    $categoryDetails['categoryDetails']['category_name'] = $search_product;
                    $categoryDetails['categoryDetails']['description']   = 'Search Products for ' . $search_product;

                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join(
                        'categories.id', '=', 'products.category_id' 
                    )->where(function($query) use ($search_product) { 
                        $query->where('products.product_name',    'like', '%' . $search_product . '%') 
                            ->orWhere('products.product_code',    'like', '%' . $search_product . '%') 
                            ->orWhere('products.product_color',   'like', '%' . $search_product . '%') 
                            ->orWhere('products.description',     'like', '%' . $search_product . '%')  
                            ->orWhere('categories.category_name', 'like', '%' . $search_product . '%'); 
                    })->where('products.status', 1);
                }


                if (isset($_REQUEST['section_id']) && !empty($_REQUEST['section_id'])) { 
                    $categoryProducts = $categoryProducts->where('products.section_id', $_REQUEST['section_id']);
                }

                $categoryProducts = $categoryProducts->get();


                return view('front.products.listing')->with(compact('categoryDetails', 'categoryProducts'));

            } else { 
                $url = \Illuminate\Support\Facades\Route::getFacadeRoot()->current()->uri(); 
                $categoryCount = Category::where([
                    'url'    => $url,
                    'status' => 1
                ])->count();
        
                if ($categoryCount > 0) {
                    $categoryDetails = Category::categoryDetails($url);
                    $categoryProducts = Product::with('brand')->whereIn('category_id', $categoryDetails['catIds'])->where('status', 1); 
        
        
                    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
                        if ($_GET['sort'] == 'product_latest') {
                            $categoryProducts->orderBy('products.id', 'Desc');
                        } elseif ($_GET['sort'] == 'price_lowest') {
                            $categoryProducts->orderBy('products.product_price', 'Asc');
                        } elseif ($_GET['sort'] == 'price_highest') {
                            $categoryProducts->orderBy('products.product_price', 'Desc');
                        } elseif ($_GET['sort'] == 'name_z_a') {
                            $categoryProducts->orderBy('products.product_name', 'Desc');
                        } elseif ($_GET['sort'] == 'name_a_z') {
                            $categoryProducts->orderBy('products.product_name', 'Asc');
                        }
                    }
                        $categoryProducts = $categoryProducts->paginate(30); 
                    $meta_title       = $categoryDetails['categoryDetails']['meta_title'];
                    $meta_description = $categoryDetails['categoryDetails']['meta_description'];
                    $meta_keywords    = $categoryDetails['categoryDetails']['meta_keywords'];


                    return view('front.products.listing')->with(compact('categoryDetails', 'categoryProducts', 'url', 'meta_title', 'meta_description', 'meta_keywords'));

                } else {
                    abort(404); 
                }

            }

        }
    }

    public function detail($id) {
        $productDetails = Product::with([
            'section', 'category', 'brand', 'attributes' => function($query) { 
                $query->where('stock', '>', 0)->where('status', 1); 
            }, 'images', 'vendor'
        ])->find($id)->toArray(); 

        $categoryDetails = Category::categoryDetails($productDetails['category']['url']); 
        

        $similarProducts = Product::with('brand')->where('category_id', $productDetails['category']['id'])->where('id', '!=', $id)->limit(4)->inRandomOrder()->get()->toArray(); 

        if (empty(Session::get('session_id'))) { 
            $session_id = md5(uniqid(rand(), true));
        } else { 
            $session_id = Session::get('session_id');
        }
        Session::put('session_id', $session_id); 

        $countRecentlyViewedProducts = DB::table('recently_viewed_products')->where([ 
            'product_id' => $id,
            'session_id' => $session_id 
        ])->count(); 

        if ($countRecentlyViewedProducts == 0) {
            DB::table('recently_viewed_products')->INSERT([ 
                'product_id' => $id,
                'session_id' => $session_id
            ]);
        }
        $recentProductsIds = DB::table('recently_viewed_products')->select('product_id')->where('product_id', '!=', $id)->where('session_id', $session_id)->inRandomOrder()->get()->take(4)->pluck('product_id'); 
        $recentlyViewedProducts = Product::with('brand')->whereIn('id', $recentProductsIds)->get()->toArray(); 

        $groupProducts = array();
        if (!empty($productDetails['group_code'])) { 
            $groupProducts = Product::select('id', 'product_image')->where('id', '!=', $id)->where([
                'group_code' => $productDetails['group_code'],
                'status'     => 1
            ])->get()->toArray();
        }

        $ratings = Rating::with('user')->where([
            'product_id' => $id,
            'status'     => 1
        ])->get()->toArray();

        $ratingSum = Rating::where([
            'product_id' => $id,
            'status'     => 1
        ])->sum('rating');

        $ratingCount = Rating::where([
            'product_id' => $id,
            'status'     => 1
        ])->count();

        if ($ratingCount > 0) { 
            $avgRating     = round($ratingSum / $ratingCount, 2);
            $avgStarRating = round($ratingSum / $ratingCount); 
        } else {
            $avgRating     = 0;
            $avgStarRating = 0;
        }

        $ratingOneStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 1
        ])->count();

        $ratingTwoStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 2
        ])->count();

        $ratingThreeStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 3
        ])->count();

        $ratingFourStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 4
        ])->count();

        $ratingFiveStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 5
        ])->count();


        $totalStock = ProductsAttribute::where('product_id', $id)->sum('stock'); 
        $meta_title       = $productDetails['meta_title'];
        $meta_description = $productDetails['meta_description'];
        $meta_keywords    = $productDetails['meta_keywords'];


        return view('front.products.detail')->with(compact('productDetails', 'categoryDetails', 'totalStock', 'similarProducts', 'recentlyViewedProducts', 'groupProducts', 'meta_title', 'meta_description', 'meta_keywords', 'ratings', 'avgRating', 'avgStarRating', 'ratingOneStarCount', 'ratingTwoStarCount', 'ratingThreeStarCount', 'ratingFourStarCount', 'ratingFiveStarCount'));
    }

    public function vendorListing($vendorid) { 
        $getVendorShop = Vendor::getVendorShop($vendorid);

        $vendorProducts = Product::with('brand')->where('vendor_id', $vendorid)->where('status', 1); 

        $vendorProducts = $vendorProducts->paginate(30); 


        return view('front.products.vendor_listing')->with(compact('getVendorShop', 'vendorProducts'));
    }

    public function cartAdd(Request $request) {
        if ($request->isMethod('post')) { 
            $data = $request->all();

            if ($data['quantity'] <= 0) {
                $data['quantity'] = 1;
            }

            $getProductStock = ProductsAttribute::getProductStock($data['product_id'], $data['size']);

            if ($getProductStock < $data['quantity']) { 
                return redirect()->back()->with('error_message', 'Required Quantity is not available!');
            }

            $session_id = Session::get('session_id'); 
            if (empty($session_id)) { 
                $session_id = Session::getId(); 
                Session::put('session_id', $session_id); 
            }

            if (Auth::check()) {
                $user_id = Auth::user()->id; 
                $countProducts = Cart::where([
                    'user_id'    => $user_id, 
                    'product_id' => $data['product_id'],
                    'size'       => $data['size']
                ])->count();

            } else { 
                $user_id = 0;
                $countProducts = Cart::where([
                    'session_id' => $session_id,
                    'product_id' => $data['product_id'],
                    'size'       => $data['size']
                ])->count();
            }



            if ($countProducts > 0) { 
                Cart::where([
                    'session_id' => $session_id, 
                    'user_id'    => $user_id ?? 0, 
                    'product_id' => $data['product_id'],
                    'size'       => $data['size']
                ])->increment('quantity', $data['quantity']);
            } else {
                $item = new Cart; 

                $item->session_id = $session_id; 
                $item->user_id    = $user_id; 
                $item->product_id = $data['product_id'];
                $item->size       = $data['size'];
                $item->quantity   = $data['quantity'];

                $item->save();
            }


            return redirect()->back()->with('success_message', 'Product has been added in Cart! <a href="/cart" style="text-decoration: underline !important">View Cart</a>');
        }
    }

    public function cart() {
      
        $getCartItems = Cart::getCartItems();

        $meta_title       = 'Shopping Cart';
        $meta_keywords    = 'shopping cart';


        return view('front.products.cart')->with(compact('getCartItems', 'meta_title', 'meta_keywords'));
    }

    public function cartUpdate(Request $request) {
        if ($request->ajax()) { 
            $data = $request->all();

            $cartDetails = Cart::find($data['cartid']);

            $availableStock = ProductsAttribute::select('stock')->where([
                'product_id' => $cartDetails['product_id'],
                'size'       => $cartDetails['size']
            ])->first()->toArray();

            if ($data['qty'] > $availableStock['stock']) { 
                $getCartItems = Cart::getCartItems();

                return response()->json([
                    'status'     => false,
                    'message'    => 'Product Stock is not available',
                    'view'       => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 

                    'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
                ]);
            }
            $availableSize =  ProductsAttribute::where([
                'product_id' => $cartDetails['product_id'],
                'size'       => $cartDetails['size'],
                'status'     => 1 
            ])->count();

            if ($availableSize == 0) { 
                $getCartItems = Cart::getCartItems();


                return response()->json([ 
                    'status'  => false,
                    'message' => 'Product Size is not available. Please remove this Product and choose another one!', 
                    'view'    => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 
                    'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
                ]);
            }


            Cart::where('id', $data['cartid'])->update([ 
                'quantity' => $data['qty']
            ]);

            $getCartItems = Cart::getCartItems();
            $totalCartItems = totalCartItems();

            return response()->json([ 
                'status'         => true,
                'totalCartItems' => $totalCartItems, 
                'view'           => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 
                'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
            ]);
        }
    }

    public function cartDelete(Request $request) {
        if ($request->ajax()) {
            $data = $request->all(); 
            Cart::where('id', $data['cartid'])->delete();

            $getCartItems = Cart::getCartItems();
            $totalCartItems = totalCartItems();

            return response()->json([ 
                'totalCartItems' => $totalCartItems,
                'view'   => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')),
                'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems'))
            ]);
        }
    }

public function checkout(Request $request) {
    $countries = Country::where('status', 1)->get()->toArray(); 
    $getCartItems = Cart::getCartItems();
    if (count($getCartItems) == 0) {
        $message = 'Shopping Cart is empty! Please add products to your Cart to checkout';

        return redirect('cart')->with('error_message', $message);
    }

    $total_price = 0;
    foreach ($getCartItems as $item) {
        $attrPrice = Product::getDiscountAttributePrice($item['product_id'], $item['size']);
        $total_price = $total_price + ($attrPrice['final_price'] * $item['quantity']);
    }
    Session::put('grand_total', $total_price);

    $deliveryAddresses = DeliveryAddress::deliveryAddresses();

    if ($request->isMethod('post')) {
        $data = $request->all();
        foreach ($getCartItems as $item) {
            $product_status = Product::getProductStatus($item['product_id']);
            if ($product_status == 0) { 
                $message = $item['product']['product_name'] . ' with ' . $item['size'] . ' size is not available. Please remove it from the Cart and choose another product.';
                return redirect('/cart')->with('error_message', $message); 
            }

            $getProductStock = ProductsAttribute::getProductStock($item['product_id'], $item['size']);
            if ($getProductStock == 0) { 
                $message = $item['product']['product_name'] . ' with ' . $item['size'] . ' size is not available. Please remove it from the Cart and choose another product.';
                return redirect('/cart')->with('error_message', $message); 
            }
        }

        if (empty($data['address_id'])) {
            $message = 'Please select Delivery Address!';
            return redirect()->back()->with('error_message', $message);
        }

        if (empty($data['payment_gateway'])) {
            $message = 'Please select Payment Method!';
            return redirect()->back()->with('error_message', $message);
        }

        if (empty($data['accept'])) {
            $message = 'Please agree to T&C!';
            return redirect()->back()->with('error_message', $message);
        }

        DB::beginTransaction();
        $deliveryAddress = DeliveryAddress::where('id', $data['address_id'])->first()->toArray();

        $payment_method = $data['payment_gateway'] == 'COD' ? 'COD' : 'Prepaid';
        $order_status = $payment_method == 'COD' ? 'New' : 'Pending';

        $order = new Order; 
        $order->user_id = Auth::user()->id; 
        $order->name = $deliveryAddress['name'];
        $order->address = $deliveryAddress['address'];
        $order->city = $deliveryAddress['city'];
        $order->state = $deliveryAddress['state'];
        $order->country = $deliveryAddress['country'];
        $order->pincode = $deliveryAddress['pincode'];
        $order->mobile = $deliveryAddress['mobile'];
        $order->email = Auth::user()->email;
        $order->coupon_code = Session::get('couponCode'); 
        $order->coupon_amount = Session::get('couponAmount'); 
        $order->order_status = $order_status;
        $order->payment_method = $payment_method;
        $order->payment_gateway = $data['payment_gateway'];
        $order->grand_total = $total_price - Session::get('couponAmount');

        $order->save(); 
        $order_id = DB::getPdo()->lastInsertId();

        foreach ($getCartItems as $item) {
            $cartItem = new OrdersProduct; 
            $cartItem->order_id = $order_id;
            $cartItem->user_id = Auth::user()->id;
            
            $getProductDetails = Product::select('product_code', 'product_name', 'product_color', 'admin_id', 'vendor_id')->where('id', $item['product_id'])->first()->toArray();
            $cartItem->admin_id = $getProductDetails['admin_id'];
            $cartItem->vendor_id = $getProductDetails['vendor_id'];
            
            if ($getProductDetails['vendor_id'] > 0) { 
                $vendorCommission = Vendor::getVendorCommission($getProductDetails['vendor_id']);
                $cartItem->commission = $vendorCommission;
            }
            
            $cartItem->product_id = $item['product_id'];
            $cartItem->product_code = $getProductDetails['product_code'];
            $cartItem->product_name = $getProductDetails['product_name'];
            $cartItem->product_color = $getProductDetails['product_color'];
            $cartItem->product_size = $item['size'];
            $cartItem->product_price = Product::getDiscountAttributePrice($item['product_id'], $item['size'])['final_price'];
            $cartItem->product_qty = $item['quantity'];            
            $getProductStock = ProductsAttribute::getProductStock($item['product_id'], $item['size']);
            $newStock = $getProductStock - $item['quantity'];
            ProductsAttribute::where([ 'product_id' => $item['product_id'], 'size' => $item['size'] ])->update(['stock' => $newStock]);

            $cartItem->save();
        }

        DB::commit(); 
        $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray();

        if ($data['payment_gateway'] == 'COD') {
            $email = Auth::user()->email; 

            $messageData = [
                'email'        => $email,
                'name'         => Auth::user()->name,
                'order_id'     => $order_id,
                'orderDetails' => $orderDetails
            ];

            \Illuminate\Support\Facades\Mail::send('emails.order', $messageData, function ($message) use ($email) {
                $message->to($email)->subject('Order Placed');
            });
        }

if ($getProductDetails['vendor_id'] > 0) {
    $vendorDetails = Vendor::where('id', $getProductDetails['vendor_id'])->first();

    if ($vendorDetails) {
        $vendorEmail = $vendorDetails->email;

        $vendorMessageData = [
            'email'        => $vendorEmail,
            'order_id'     => $order_id,
            'orderDetails' => $orderDetails,
        ];

        \Illuminate\Support\Facades\Mail::send('emails.vendor_order', $vendorMessageData, function ($message) use ($vendorEmail) {
            $message->to($vendorEmail)->subject('New Order Notification');
        });
    }
} elseif ($data['payment_gateway'] == 'Paypal') {
    // redirect the user to the PayPalController.php (after saving the order details in `orders` and `orders_products` tables)
    return redirect('/paypal');
}
        return redirect('thanks');
    }

    return view('front.products.checkout')->with(compact('deliveryAddresses', 'countries', 'getCartItems', 'total_price'));
}



    public function thanks() {
        if (Session::has('order_id')) {
            Cart::where('user_id', Auth::user()->id)->delete(); 


            return view('front.products.thanks');
        } else { 
            return redirect('cart'); 
        }
    }

    public function UpdateCurrency(Request $request) {
        if ($request->ajax()) { 
            $data = $request->all(); 

            $current_cur=$data['currency'] ;
            Session::put('currency',$current_cur);
            return response()->json(['success'=>true]);
        }
    }


}