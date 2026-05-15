<!-- resources/views/emails/order-invoice.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Invoice</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif;">
    <table align="center" cellpadding="0" cellspacing="0" width="600" style="border: 2px solid #000; background-color: #fff; margin: 20px auto;">
        <tr>
            <td align="center" style="padding: 20px;">
                <!-- Logo -->
                <img src="https://pos-dev.canngopi.com/images/logo.png" alt="Can Ngopi Logo" style="height: 150px; display: block; margin-bottom: 10px;">
                <!-- Judul -->
                <h2 style="margin: 0; color: #333;">Order Invoice - #{{ $order->id }} - {{ $order->customer_name }}</h2>
            </td>
        </tr>

        <!-- Daftar Item -->
        <tr>
            <td style="padding: 20px;">
                <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f5f5f5;">
                            <th align="left" style="border-bottom: 1px solid #ccc;">Item</th>
                            <th align="center" style="border-bottom: 1px solid #ccc;">Qty</th>
                            <th align="right" style="border-bottom: 1px solid #ccc;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td style="padding: 8px 0;">{{ $item->name }}</td>
                            <td align="center">{{ $item->quantity }}</td>
                            <td align="right">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>

        <!-- Order Summary -->
        <tr>
            <td style="padding: 20px;">
                <table width="100%" cellpadding="5" cellspacing="0" style="border-top: 1px solid #ccc;">
                    <tr>
                        <td align="left">Subtotal</td>
                        <td align="right">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->discount_name)
                    <tr>
                        <td align="left">Discount ({{ $order->discount_name }})</td>
                        <td align="right">- Rp{{ number_format($order->discount_value, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td align="left"><strong>Grand Total</strong></td>
                        <td align="right"><strong>Rp{{ number_format($order->grand_total, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td align="center" style="padding: 20px; color: #555;">
                <p style="margin: 0;">Terima kasih,</p>
                <p style="margin: 0;"><strong>{{ $order->cashier_name }}</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
