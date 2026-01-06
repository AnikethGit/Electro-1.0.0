<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/cart/get_cart.php';
require_once __DIR__ . '/cart/cart_handler.php';

$messages = get_messages();
$cart_summary = get_cart_summary();
$cart_items = $cart_summary['items'];
$totals = $cart_summary['totals'];
$cart_count = get_cart_count();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Electro</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 30px 0;
        }
        .cart-items {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr 100px 100px 50px;
            gap: 20px;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
        .cart-item-image-placeholder {
            width: 100px;
            height: 100px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #999;
        }
        .cart-item-details h3 {
            margin: 0;
            font-size: 16px;
        }
        .cart-item-details p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .cart-item-quantity input {
            width: 50px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .cart-item-price {
            text-align: right;
            font-weight: bold;
            color: #27ae60;
        }
        .remove-item {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .remove-item:hover {
            background: #c0392b;
        }
        .cart-summary {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .cart-summary h2 {
            margin-top: 0;
            font-size: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 16px;
            padding-top: 10px;
        }
        .checkout-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
        }
        .checkout-btn:hover {
            background: #229954;
        }
        .continue-shopping {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .continue-shopping:hover {
            background: #2980b9;
        }
        .empty-cart {
            text-align: center;
            padding: 40px;
        }
        .empty-cart p {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }
        .empty-cart a {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="cart.php" class="active">Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
        </nav>
    </header>

    <div class="container">
        <!-- Messages -->
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?php echo $msg['type']; ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
        <?php endforeach; ?>

        <h1>Shopping Cart</h1>

        <?php if ($cart_summary['is_empty']): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="shop.php">Continue Shopping</a>
            </div>
        <?php else: ?>
            <!-- Cart Content -->
            <div class="cart-container">
                <!-- Cart Items -->
                <div class="cart-items">
                    <h2>Cart Items</h2>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <!-- Image -->
                            <div>
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         class="cart-item-image" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <div class="cart-item-image-placeholder">No Image</div>
                                <?php endif; ?>
                            </div>

                            <!-- Details -->
                            <div class="cart-item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Price: <?php echo format_price($item['price']); ?></p>
                                <p>SKU: <?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></p>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <form method="post" action="cart_actions.php" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="10" style="width: 50px; padding: 5px;">
                                    <button type="submit" style="background: #3498db; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Update</button>
                                </form>
                            </div>

                            <!-- Subtotal -->
                            <div class="cart-item-price">
                                <?php echo format_price($item['price'] * $item['quantity']); ?>
                            </div>

                            <!-- Remove -->
                            <div>
                                <form method="post" action="cart_actions.php" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="remove-item" onclick="return confirm('Remove this item?');">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><?php echo format_price($totals['subtotal']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (<?php echo number_format($totals['tax_rate'], 0); ?>%):</span>
                        <span><?php echo format_price($totals['tax']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span><?php echo format_price($totals['shipping']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Total:</span>
                        <span><?php echo format_price($totals['total']); ?></span>
                    </div>
                    <form method="post" action="checkout.php">
                        <button type="submit" class="checkout-btn">Proceed to Checkout</button>
                    </form>
                    <a href="shop.php" class="continue-shopping">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2026 Electro. All rights reserved.</p>
    </footer>
</body>
</html>