<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/cart/get_cart.php';
require_once __DIR__ . '/cart/cart_handler.php';

// Redirect to cart if empty
if (is_cart_empty()) {
    redirect('cart.php');
}

$messages = get_messages();
$cart_summary = get_cart_summary(0.08, 5.00); // 8% tax, $5 shipping
$cart_items = $cart_summary['items'];
$totals = $cart_summary['totals'];
$cart_count = get_cart_count();

// Get current user if logged in
$user = current_user();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Electro</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 30px 0;
        }
        .checkout-form {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-row.full {
            grid-template-columns: 1fr;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .checkbox-group input {
            cursor: pointer;
        }
        .order-summary {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .order-summary h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .summary-item:last-of-type {
            border-bottom: 2px solid #ddd;
        }
        .summary-item.total {
            font-weight: bold;
            font-size: 16px;
            color: #27ae60;
            padding: 15px 0;
        }
        .payment-methods {
            margin: 20px 0;
        }
        .payment-methods label {
            display: block;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .payment-methods label:hover {
            background: #f5f5f5;
        }
        .payment-methods input[type="radio"] {
            margin-right: 10px;
        }
        .place-order-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .place-order-btn:hover {
            background: #229954;
        }
        .place-order-btn:disabled {
            background: #999;
            cursor: not-allowed;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .required {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="cart.php">Cart</a>
            <a href="checkout.php" class="active">Checkout</a>
        </nav>
    </header>

    <div class="container">
        <!-- Messages -->
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?php echo $msg['type']; ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
        <?php endforeach; ?>

        <h1>Checkout</h1>

        <div class="checkout-container">
            <!-- Checkout Form -->
            <form method="post" action="orders/create.php" class="checkout-form">
                <!-- Customer Information -->
                <div class="form-section">
                    <h3>Customer Information</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label for="phone">Phone Number <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="form-section">
                    <h3>Shipping Address</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="address">Street Address <span class="required">*</span></label>
                            <input type="text" id="address" name="address" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City <span class="required">*</span></label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State/Province <span class="required">*</span></label>
                            <input type="text" id="state" name="state" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="postal_code">Postal Code <span class="required">*</span></label>
                            <input type="text" id="postal_code" name="postal_code" required>
                        </div>
                        <div class="form-group">
                            <label for="country">Country <span class="required">*</span></label>
                            <input type="text" id="country" name="country" value="USA" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="form-section">
                    <h3>Payment Method <span class="required">*</span></h3>
                    <div class="payment-methods">
                        <label>
                            <input type="radio" name="payment_method" value="COD" checked>
                            <strong>Cash on Delivery (COD)</strong>
                            <p style="margin: 5px 0 0 26px; color: #666; font-size: 12px;">Pay when your order arrives</p>
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Credit Card">
                            <strong>Credit/Debit Card</strong>
                            <p style="margin: 5px 0 0 26px; color: #666; font-size: 12px;">Secure payment processing</p>
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Bank Transfer">
                            <strong>Bank Transfer</strong>
                            <p style="margin: 5px 0 0 26px; color: #666; font-size: 12px;">Direct bank transfer</p>
                        </label>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="form-section">
                    <h3>Order Notes (Optional)</h3>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="notes">Special Instructions</label>
                            <textarea id="notes" name="notes" placeholder="Any special requests or instructions?"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" style="margin: 0;">
                        I agree to the <a href="#" style="color: #3498db;">Terms & Conditions</a>
                    </label>
                </div>

                <button type="submit" class="place-order-btn">Place Order</button>
            </form>

            <!-- Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <div style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="padding: 10px 0; border-bottom: 1px solid #eee; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between;">
                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                <span><?php echo $item['quantity']; ?> x <?php echo format_price($item['price']); ?></span>
                            </div>
                            <div style="text-align: right; color: #666;">
                                Subtotal: <?php echo format_price($item['price'] * $item['quantity']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span><?php echo format_price($totals['subtotal']); ?></span>
                </div>
                <div class="summary-item">
                    <span>Tax (<?php echo number_format($totals['tax_rate'], 0); ?>%):</span>
                    <span><?php echo format_price($totals['tax']); ?></span>
                </div>
                <div class="summary-item">
                    <span>Shipping:</span>
                    <span><?php echo format_price($totals['shipping']); ?></span>
                </div>
                <div class="summary-item total">
                    <span>Total:</span>
                    <span><?php echo format_price($totals['total']); ?></span>
                </div>

                <a href="cart.php" style="display: block; text-align: center; color: #3498db; margin-top: 20px; text-decoration: none;">
                    &larr; Back to Cart
                </a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Electro. All rights reserved.</p>
    </footer>
</body>
</html>