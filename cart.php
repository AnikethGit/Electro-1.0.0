<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/config/helpers.php';
    require_once __DIR__ . '/cart/get_cart.php';
    
    // Initialize SESSION cart if not exists
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Handle cart updates from forms
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action']) && isset($_POST['product_id'])) {
            $action = htmlspecialchars($_POST['action']);
            $product_id = intval($_POST['product_id']);
            $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
            
            if ($action == 'update') {
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } else {
                    $_SESSION['cart'][$product_id] = $qty;
                }
            } elseif ($action == 'remove') {
                unset($_SESSION['cart'][$product_id]);
            }
            
            header('Location: cart.php');
            exit;
        }
    }
    
    $messages = get_messages();
    $cart_summary = get_cart_summary();
    $cart_items = $cart_summary['items'];
    $totals = $cart_summary['totals'];
    $cart_count = $totals['item_count'];
    $cart_total = $totals['total'];
    
} catch (Exception $e) {
    die('ERROR: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopping Cart - Electro</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Topbar Start -->
    <div class="container-fluid px-5 py-3">
        <div class="row gx-0 align-items-center">
            <div class="col-md-4">
                <a href="index.php" class="navbar-brand p-0">
                    <h1 class="display-5 text-primary m-0"><i class="fas fa-shopping-bag text-secondary me-2"></i>Electro</h1>
                </a>
            </div>
            <div class="col-md-8 text-end">
                <a href="cart.php" class="btn btn-outline-primary"><i class="fas fa-shopping-cart me-2"></i>Cart: $<?php echo number_format($cart_total, 2); ?></a>
                <a href="shop.php" class="btn btn-outline-primary ms-2">Shop</a>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Page Header -->
    <div class="container-fluid bg-primary text-white py-5">
        <div class="container">
            <h1>Shopping Cart</h1>
        </div>
    </div>

    <!-- Cart Page Start -->
    <div class="container my-5">
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?php echo htmlspecialchars($msg['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($msg['text']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>

        <?php if ($cart_summary['is_empty']): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart" style="font-size: 64px; color: #ccc; margin: 20px 0;"></i>
                <p style="font-size: 20px; color: #666;">Your cart is empty</p>
                <a href="shop.php" class="btn btn-primary btn-lg mt-3">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="table-responsive mb-4">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                                    <div class="input-group" style="width: 120px;">
                                        <button class="btn btn-sm btn-outline-secondary" type="submit" name="qty" value="<?php echo max(1, (int)$item['quantity'] - 1); ?>">−</button>
                                        <input type="text" class="form-control form-control-sm text-center" value="<?php echo (int)$item['quantity']; ?>" readonly>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit" name="qty" value="<?php echo (int)$item['quantity'] + 1; ?>">+</button>
                                    </div>
                                </form>
                            </td>
                            <td><strong>$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></strong></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Remove this item from cart?');">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Order Summary</h5>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format((float)$totals['subtotal'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (<?php echo number_format((float)$totals['tax_rate'], 1); ?>%):</span>
                                <span>$<?php echo number_format((float)$totals['tax'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping:</span>
                                <span>$<?php echo number_format((float)$totals['shipping'], 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <h6 class="mb-0">Total:</h6>
                                <h6 class="mb-0">$<?php echo number_format((float)$totals['total'], 2); ?></h6>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                            <a href="shop.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- Cart Page End -->

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2026 Electro Shop. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>