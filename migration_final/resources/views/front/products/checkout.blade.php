{{-- Note: This page (view) is rendered by the checkout() method in the Front/ProductsController.php --}}
@extends('front.layout.layout')

@section('content')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>Checkout</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="{{ url('/checkout') }}">Checkout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->

    <!-- Checkout-Page -->
    <div class="page-checkout u-s-p-t-80">
        <div class="container">
            {{-- Show form validation errors --}}
            @if (Session::has('error_message'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ Session::get('error_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="row">
                        <!-- Billing-&-Shipping-Details -->
                        <div class="col-lg-6" id="deliveryAddresses">
                            @include('front.products.delivery_addresses') 
                        </div>
                        <!-- Billing-&-Shipping-Details /- -->

                        <!-- Checkout -->
                        <div class="col-lg-6">
                            {{-- The complete form for submitting the order --}}
                            <form name="checkoutForm" id="checkoutForm" method="post">
                                @csrf

                                {{-- Delivery Addresses --}}
                                @if (count($deliveryAddresses) > 0)
                                    <h4 class="section-h4">Delivery Addresses</h4>
                                    @foreach ($deliveryAddresses as $address)
                                        <div class="control-group" style="float: left; margin-right: 5px">
                                            <input type="radio" id="address{{ $address['id'] }}" name="address_id" value="{{ $address['id'] }}">
                                        </div>
                                        <div>
                                            <label class="control-label" for="address{{ $address['id'] }}">
                                                {{ $address['name'] }}, {{ $address['address'] }}, {{ $address['city'] }}, {{ $address['state'] }}, {{ $address['country'] }} ({{ $address['mobile'] }})
                                            </label>
                                            <a href="javascript:;" data-addressid="{{ $address['id'] }}" class="removeAddress" style="float: right; margin-left: 10px">Remove</a>
                                            <a href="javascript:;" data-addressid="{{ $address['id'] }}" class="editAddress" style="float: right">Edit</a>
                                        </div>
                                    @endforeach
                                    <br>
                                @endif

                                {{-- Order summary --}}
                                <h4 class="section-h4">Your Order</h4>
                                <div class="order-table">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $total_price = 0 @endphp
                                            @foreach ($getCartItems as $item)
                                                @php
                                                    $getDiscountAttributePrice = \App\Models\Product::getDiscountAttributePrice($item['product_id'], $item['size']);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <a href="{{ url('product/' . $item['product_id']) }}">
                                                            <img width="50px" src="{{ asset('front/images/product_images/small/' . $item['product']['product_image']) }}" alt="Product">
                                                            <h6>{{ $item['product']['product_name'] }} ({{ $item['size'] }}/{{ $item['product']['product_color'] }})</h6>
                                                            <span>x {{ $item['quantity'] }}</span>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <h6>EGP{{ $getDiscountAttributePrice['final_price'] * $item['quantity'] }}</h6>
                                                    </td>
                                                </tr>
                                                @php
                                                    $total_price = $total_price + ($getDiscountAttributePrice['final_price'] * $item['quantity']);
                                                @endphp
                                            @endforeach
                                            <tr>
                                                <td>
                                                    <h3>Subtotal</h3>
                                                </td>
                                                <td>
                                                    <h3>EGP{{ $total_price }}</h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h6>Shipping Charges</h6>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h6>Coupon Discount</h6>
                                                </td>
                                                <td>
                                                    <h6>
                                                        @if (Session::has('couponAmount'))
                                                            EGP{{ Session::get('couponAmount') }}
                                                        @else
                                                            EGP0
                                                        @endif
                                                    </h6>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h3>Grand Total</h3>
                                                </td>
                                                <td>
                                                    <h3><strong>EGP{{ $total_price - Session::get('couponAmount') }}</strong></h3>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    {{-- Payment Methods --}}
                                    <div class="u-s-m-b-13 codMethod">
                                        <input type="radio" class="radio-box" name="payment_gateway" id="cash-on-delivery" value="COD">
                                        <label class="label-text" for="cash-on-delivery">Cash on Delivery</label>
                                    </div>
                                    <div class="u-s-m-b-13 prepaidMethod">
                                        <input type="radio" class="radio-box" name="payment_gateway" id="paypal" value="Paypal">
                                        <label class="label-text" for="paypal">PayPal</label>
                                    </div>
                                    {{-- Terms & Conditions --}}
                                    <div class="u-s-m-b-13">
    <input type="checkbox" class="check-box" id="accept" name="accept" value="Yes" title="Please agree to T&C">
    <label class="label-text no-color" for="accept">I’ve read and accept the
        <a href="{{ url('/terms-and-conditions') }}" class="u-c-brand">terms & conditions</a>
    </label>
</div>

                                    <button type="submit" id="placeOrder" class="button button-outline-secondary">Place Order</button>
                                </div>
                            </form>
                        </div>
                        <!-- Checkout /- -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Checkout-Page /- -->

    {{-- Add this JavaScript code to handle payment gateway logic --}}
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Function to update the form action based on the selected payment gateway
            function updateFormAction() {
                var paymentMethod = $('input[name="payment_gateway"]:checked').val();
                var formAction = '{{ url("/checkout") }}'; // Default action for COD

                if (paymentMethod === 'Paypal') {
                    formAction = '{{ route("payment") }}'; // Change to the desired PayPal checkout route
                }

                $('#checkoutForm').attr('action', formAction); // Update the form action
            }

            // Update the form action when a payment gateway is selected
            $('input[name="payment_gateway"]').change(function() {
                updateFormAction(); // Call the function to update the action
            });

            // Also update the form action when the form is submitted (for safety)
            $('#checkoutForm').submit(function() {
                updateFormAction();
            });
        });
    </script>
    @endpush
@endsection
