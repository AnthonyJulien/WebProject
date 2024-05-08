<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>New Order Notification</title>
    </head>
    <body>
        <table style="width: 700px;">
            <tr><td>&nbsp;</td></tr>
            <tr><td><img src="{{ asset('front/images/main-logo/main-logo.png') }}" alt="Company Logo"></td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>Hello Vendor,</td></tr>
            <tr><td>&nbsp;<br></td></tr>
            <tr><td>You have received a new order. Please find the details below:</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>Order no. {{ $order_id }}</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>
                <table style="width: 95%;" cellpadding="5" cellspacing="5" bgcolor="#f7f4f4">
                    <tr bgcolor="#cccccc">
                        <td>Product Name</td>
                        <td>Product Code</td>
                        <td>Product Size</td>
                        <td>Product Color</td>
                        <td>Product Quantity</td>
                        <td>Product Price</td>
                    </tr>
                    @foreach ($orderDetails['orders_products'] as $order)
                        <tr bgcolor="#f9f9f9">
                            <td>{{ $order['product_name'] }}</td>
                            <td>{{ $order['product_code'] }}</td>
                            <td>{{ $order['product_size'] }}</td>
                            <td>{{ $order['product_color'] }}</td>
                            <td>{{ $order['product_qty'] }}</td>
                            <td>{{ $order['product_price'] }}</td>
                        </tr>
                    @endforeach
                </table>    
            </td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>
                <strong>Customer Delivery Address:</strong>
                <table>
                    <tr>
                        <td>{{ $orderDetails['name'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $orderDetails['address'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $orderDetails['city'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $orderDetails['state'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $orderDetails['country'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $orderDetails['pincode'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $orderDetails['mobile'] }}</td>
                    </tr>
                </table>    
            </td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>Thank you for your collaboration with us. If you have any questions, contact us at <a href="mailto:laravel@project.com">laravel@project.com</a>.</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>Best Regards,<br>Team Multi-vendor E-commerce Application</td></tr>
            <tr><td>&nbsp;</td></tr>
        </table>
    </body>
</html>
