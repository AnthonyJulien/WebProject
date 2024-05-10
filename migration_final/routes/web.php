<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotmanController;
use App\Http\Controllers\GoogleController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__.'/auth.php';




Route::prefix('/admin')->namespace('App\Http\Controllers\Admin')->group(function() {
    Route::match(['get', 'post'], 'login', 'AdminController@login'); 


    Route::group(['middleware' => ['admin']], function() {
        Route::get('dashboard', 'AdminController@dashboard');
        Route::get('logout', 'AdminController@logout');
        Route::match(['get', 'post'], 'update-admin-password', 'AdminController@updateAdminPassword');
        Route::post('check-admin-password', 'AdminController@checkAdminPassword'); 
        Route::match(['get', 'post'], 'update-admin-details', 'AdminController@updateAdminDetails');
        Route::match(['get', 'post'], 'update-vendor-details/{slug}', 'AdminController@updateVendorDetails'); 

        Route::post('update-vendor-commission', 'AdminController@updateVendorCommission');

        Route::get('admins/{type?}', 'AdminController@admins'); 
        Route::get('view-vendor-details/{id}', 'AdminController@viewVendorDetails'); 
        Route::post('update-admin-status', 'AdminController@updateAdminStatus');
    

        Route::get('sections', 'SectionController@sections');
        Route::post('update-section-status', 'SectionController@updateSectionStatus'); 
        Route::get('delete-section/{id}', 'SectionController@deleteSection');
        Route::match(['get', 'post'], 'add-edit-section/{id?}', 'SectionController@addEditSection'); 

        Route::get('categories', 'CategoryController@categories'); 
        Route::post('update-category-status', 'CategoryController@updateCategoryStatus'); 
        Route::match(['get', 'post'], 'add-edit-category/{id?}', 'CategoryController@addEditCategory');
        Route::get('append-categories-level', 'CategoryController@appendCategoryLevel');
        Route::get('delete-category/{id}', 'CategoryController@deleteCategory');
        Route::get('delete-category-image/{id}', 'CategoryController@deleteCategoryImage');

        Route::get('brands', 'BrandController@brands');
        Route::post('update-brand-status', 'BrandController@updateBrandStatus');
        Route::get('delete-brand/{id}', 'BrandController@deleteBrand'); 
        Route::match(['get', 'post'], 'add-edit-brand/{id?}', 'BrandController@addEditBrand');

        Route::get('products', 'ProductsController@products');
        Route::post('update-product-status', 'ProductsController@updateProductStatus');
        Route::get('delete-product/{id}', 'ProductsController@deleteProduct'); 
        Route::match(['get', 'post'], 'add-edit-product/{id?}', 'ProductsController@addEditProduct'); 
        Route::get('delete-product-image/{id}', 'ProductsController@deleteProductImage');
        Route::get('delete-product-video/{id}', 'ProductsController@deleteProductVideo');

        Route::match(['get', 'post'], 'add-edit-attributes/{id}', 'ProductsController@addAttributes'); 
        Route::post('update-attribute-status', 'ProductsController@updateAttributeStatus');
        Route::get('delete-attribute/{id}', 'ProductsController@deleteAttribute');
        Route::match(['get', 'post'], 'edit-attributes/{id}', 'ProductsController@editAttributes'); 

        Route::match(['get', 'post'], 'add-images/{id}', 'ProductsController@addImages'); 
        Route::post('update-image-status', 'ProductsController@updateImageStatus'); 
        Route::get('delete-image/{id}', 'ProductsController@deleteImage');

        Route::get('banners', 'BannersController@banners');
        Route::post('update-banner-status', 'BannersController@updateBannerStatus'); 
        Route::get('delete-banner/{id}', 'BannersController@deleteBanner');
        Route::match(['get', 'post'], 'add-edit-banner/{id?}', 'BannersController@addEditBanner'); 

        Route::get('filters', 'FilterController@filters'); 
        Route::post('update-filter-status', 'FilterController@updateFilterStatus');
        Route::post('update-filter-value-status', 'FilterController@updateFilterValueStatus');
        Route::get('filters-values', 'FilterController@filtersValues');
        Route::match(['get', 'post'], 'add-edit-filter/{id?}', 'FilterController@addEditFilter'); 
        Route::match(['get', 'post'], 'add-edit-filter-value/{id?}', 'FilterController@addEditFilterValue'); 
        Route::post('category-filters', 'FilterController@categoryFilters');

        Route::get('users', 'UserController@users'); 
        Route::post('update-user-status', 'UserController@updateUserStatus');

        Route::post('reset-user-password', 'UserController@resetUserPassword');

        Route::post('reset-admin-password', 'AdminController@resetAdminPassword');
                
        Route::get('orders', 'OrderController@orders');
        Route::get('orders/{id}', 'OrderController@orderDetails'); 
        Route::post('update-order-status', 'OrderController@updateOrderStatus');
        Route::post('update-order-item-status', 'OrderController@updateOrderItemStatus');
        Route::get('orders/invoice/{id}', 'OrderController@viewOrderInvoice'); 

        Route::get('ratings', 'RatingController@ratings');
        Route::post('update-rating-status', 'RatingController@updateRatingStatus');
        Route::get('delete-rating/{id}', 'RatingController@deleteRating'); 
    });

});

Route::namespace('App\Http\Controllers\Front')->group(function() {
    Route::get('/', 'IndexController@index');

    $catUrls = \App\Models\Category::select('url')->where('status', 1)->get()->pluck('url')->toArray(); 
    foreach ($catUrls as $key => $url) {
        Route::match(['get', 'post'], '/' . $url, 'ProductsController@listing'); 
    }


    Route::get('vendor/login-register', 'VendorController@loginRegister'); 

    Route::post('vendor/register', 'VendorController@vendorRegister'); 

    Route::get('vendor/confirm/{code}', 'VendorController@confirmVendor');

    Route::get('/product/{id}', 'ProductsController@detail');

    Route::post('get-product-price', 'ProductsController@getProductPrice');

    Route::get('/products/{vendorid}', 'ProductsController@vendorListing');

    Route::post('cart/add', 'ProductsController@cartAdd');

    Route::get('cart', 'ProductsController@cart')->name('cart');

    Route::post('cart/update', 'ProductsController@cartUpdate');

    Route::post('cart/delete', 'ProductsController@cartDelete');

    Route::get('user/login-register', ['as' => 'login', 'uses' => 'UserController@loginRegister']); 
    Route::get('auth/google', [GoogleController::class, 'signInwithGoogle']);
    Route::get('callback/google', [GoogleController::class, 'callbackToGoogle']);

    Route::post('user/register', 'UserController@userRegister');

    Route::post('user/login', 'UserController@userLogin');

    Route::get('user/logout', 'UserController@userLogout');

    Route::match(['get', 'post'], 'user/forgot-password', 'UserController@forgotPassword'); 

    Route::get('user/confirm/{code}', 'UserController@confirmAccount');

    Route::get('search-products', 'ProductsController@listing');

    Route::post('check-pincode', 'ProductsController@checkPincode');

    Route::post('update_currency', 'ProductsController@UpdateCurrency');

    Route::match(['get', 'post'], 'contact', 'CmsController@contact');

    Route::post('add-rating', 'RatingController@addRating');


    Route::group(['middleware' => ['auth']], function() {
        Route::match(['GET', 'POST'], 'user/account', 'UserController@userAccount');

        Route::post('user/update-password', 'UserController@userUpdatePassword');

        Route::match(['GET', 'POST'], '/checkout', 'ProductsController@checkout');

        Route::post('get-delivery-address', 'AddressController@getDeliveryAddress');

        Route::post('save-delivery-address', 'AddressController@saveDeliveryAddress');

        Route::post('remove-delivery-address', 'AddressController@removeDeliveryAddress');

        Route::get('thanks', 'ProductsController@thanks');

        Route::get('user/orders/{id?}', 'OrderController@orders');

        Route::get('paypal', 'PaypalController@pay');

        Route::post('pay', 'PaypalController@pay')->name('payment'); 

        Route::get('success', 'PaypalController@success');

        Route::get('error', 'PaypalController@error');

        Route::get('iyzipay', 'IyzipayController@iyzipay');

        Route::get('iyzipay/pay', 'IyzipayController@pay'); 
    });

});
Route::match(['get', 'post'], '/botman', 'App\Http\Controllers\BotManController@handle');
Route::get('/chatbot',function(){
    return view('chatbot');
});