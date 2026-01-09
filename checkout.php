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
$cart_summary = get_cart_summary(0.08, 5.00);
$cart_items = $cart_summary['items'];
$totals = $cart_summary['totals'];
$cart_count = get_cart_count();
$user = current_user();

// Calculate cart total
$cart_total = 0;
if (is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = intval($pid);
        $qty = intval($qty);
        
        if ($stmt = $conn->prepare("SELECT price FROM products WHERE id = ?")) {
            $stmt->bind_param("i", $pid);
            if ($stmt->execute()) {
                $price_result = $stmt->get_result();
                if ($price_result && $price_result->num_rows > 0) {
                    $row = $price_result->fetch_assoc();
                    $cart_total += floatval($row['price']) * $qty;
                }
            }
            $stmt->close();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Checkout - Shopspree</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        .checkout-wrapper {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            padding: 40px 0;
        }
        .checkout-form {
            background: white;
            border-radius: 8px;
        }
        .form-section {
            padding: 25px;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .form-section h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
        }
        .form-section h4 i {
            margin-right: 10px;
            color: #0d6efd;
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
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
            font-size: 13px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
        }
        .checkbox-group input {
            margin-top: 4px;
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-size: 13px;
        }
        .payment-option {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover {
            border-color: #0d6efd;
            background: #f8f9fa;
        }
        .payment-option input {
            margin-right: 10px;
        }
        .order-summary {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .order-summary h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        .summary-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .summary-item-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .summary-item-row:last-child {
            border-bottom: none;
        }
        .summary-item-name {
            font-weight: 500;
            color: #333;
            flex: 1;
        }
        .summary-item-qty {
            color: #999;
            margin: 0 10px;
        }
        .summary-item-price {
            text-align: right;
            font-weight: 600;
            color: #0d6efd;
        }
        .summary-total {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
        }
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 15px;
            padding: 10px 0;
            color: #0d6efd;
        }
        .btn-place-order {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn-place-order:hover {
            background: #218838;
        }
        .btn-place-order:disabled {
            background: #999;
            cursor: not-allowed;
        }
        .btn-back-cart {
            display: block;
            text-align: center;
            color: #0d6efd;
            margin-top: 15px;
            font-size: 13px;
            text-decoration: none;
        }
        .btn-back-cart:hover {
            text-decoration: underline;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Topbar Start -->
    <div class="container-fluid px-5 d-none border-bottom d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-4 text-center text-lg-start mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a href="#" class="text-muted me-2"> Help</a><small> / </small>
                    <a href="#" class="text-muted mx-2"> Support</a><small> / </small>
                    <a href="#" class="text-muted ms-2"> Contact</a>
                </div>
            </div>
            <div class="col-lg-4 text-center d-flex align-items-center justify-content-center">
                <small class="text-dark">Call Us:</small>
                <a href="#" class="text-muted">(+012) 1234 567890</a>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted me-2" data-bs-toggle="dropdown"><small>USD</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item"> Euro</a>
                            <a href="#" class="dropdown-item"> Dollar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid px-5 py-4 d-none d-lg-block">
        <div class="row gx-0 align-items-center text-center">
            <div class="col-md-4 col-lg-3 text-center text-lg-start">
                <div class="d-inline-flex align-items-center">
                    <a href="index.php" class="navbar-brand p-0">
                        <h1 class="display-5 text-primary m-0"><i class="fas fa-shopping-bag text-secondary me-2"></i>Shopspree</h1>
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-lg-6 text-center">
                <div class="position-relative ps-4">
                    <form method="GET" action="shop.php" class="d-flex border rounded-pill">
                        <input class="form-control border-0 rounded-pill w-100 py-3" type="text" name="search" placeholder="Search Looking For?">
                        <button type="submit" class="btn btn-primary rounded-pill py-3 px-5" style="border: 0;"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-center text-lg-end">
                <div class="d-inline-flex align-items-center">
                    <a href="cart.php" class="text-muted d-flex align-items-center justify-content-center"><span class="rounded-circle btn-md-square border"><i class="fas fa-shopping-cart"></i></span>
                        <span class="text-dark ms-2">$<?php echo number_format($cart_total, 2); ?></span></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar p-0">
        <div class="row gx-0 bg-primary px-5 align-items-center">
            <div class="col-12 col-lg-9 offset-lg-3">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary">
                    <a href="index.php" class="navbar-brand d-block d-lg-none">
                        <h1 class="display-5 text-secondary m-0"><i class="fas fa-shopping-bag text-white me-2"></i>Shopspree</h1>
                    </a>
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars fa-1x"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="index.php" class="nav-item nav-link">Home</a>
                            <a href="shop.php" class="nav-item nav-link">Shop</a>
                            <a href="cart.php" class="nav-item nav-link">Cart</a>
                            <a href="checkout.php" class="nav-item nav-link active">Checkout</a>
                        </div>
                        <a href="" class="btn btn-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0"><i class="fa fa-mobile-alt me-2"></i> +0123 456 7890</a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->

    <!-- Main Content Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <h1 class="mb-4">Checkout</h1>

            <?php foreach ($messages as $msg): ?>
                <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($msg['text']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>

            <div class="checkout-wrapper">
                <!-- Checkout Form -->
                <form method="post" action="orders/create.php" class="checkout-form">
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h4><i class="fas fa-user"></i>Customer Information</h4>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
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
                        <h4><i class="fas fa-map-marker-alt"></i>Shipping Address</h4>
                        
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
                        <h4><i class="fas fa-credit-card"></i>Payment Method <span class="required">*</span></h4>
                        <div>
                            <div class="payment-option">
                                <input type="radio" id="cod" name="payment_method" value="COD" checked>
                                <label for="cod" style="display: inline;">
                                    <strong>Cash on Delivery (COD)</strong><br>
                                    <small style="color: #666;">Pay when your order arrives</small>
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="card" name="payment_method" value="Credit Card">
                                <label for="card" style="display: inline;">
                                    <strong>Credit/Debit Card</strong><br>
                                    <small style="color: #666;">Secure payment processing</small>
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="bank" name="payment_method" value="Bank Transfer">
                                <label for="bank" style="display: inline;">
                                    <strong>Bank Transfer</strong><br>
                                    <small style="color: #666;">Direct bank transfer</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="form-section">
                        <h4><i class="fas fa-sticky-note"></i>Order Notes (Optional)</h4>
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="notes">Special Instructions</label>
                                <textarea id="notes" name="notes" placeholder="Any special requests or instructions?"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div style="padding: 0 25px; padding-bottom: 25px;">
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="#" style="color: #0d6efd;">Terms & Conditions</a> and <a href="#" style="color: #0d6efd;">Privacy Policy</a></label>
                        </div>
                        <button type="submit" class="btn-place-order"><i class="fas fa-check-circle me-2"></i>Place Order</button>
                    </div>
                </form>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h4><i class="fas fa-receipt me-2"></i>Order Summary</h4>
                    
                    <div class="summary-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item-row">
                                <span class="summary-item-name"><?php echo htmlspecialchars(substr($item['name'], 0, 30)); ?></span>
                                <span class="summary-item-qty">x<?php echo $item['quantity']; ?></span>
                                <span class="summary-item-price"><?php echo format_price($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-total">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo format_price($totals['subtotal']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Tax:</span>
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
                    </div>

                    <a href="cart.php" class="btn-back-cart"><i class="fas fa-arrow-left me-1"></i>Back to Cart</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Content End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light footer pt-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="text-light mb-4">Why Choose Us</h5>
                    <p class="mb-4">Trusted electronics store with premium products, fast delivery, and excellent customer service.</p>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                    <h5 class="text-light mb-4">Address</h5>
                    <p><i class="fa fa-map-marker-alt me-3"></i>123 Street, New York, USA</p>
                    <p><i class="fa fa-phone-alt me-3"></i>+012 345 67890</p>
                    <p><i class="fa fa-envelope me-3"></i>info@example.com</p>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                    <h5 class="text-light mb-4">Quick Links</h5>
                    <a class="btn btn-link" href="index.php">Home</a>
                    <a class="btn btn-link" href="shop.php">Shop</a>
                    <a class="btn btn-link" href="cart.php">Cart</a>
                    <a class="btn btn-link" href="checkout.php">Checkout</a>
                    <a class="btn btn-link" href="">Support</a>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                    <h5 class="text-light mb-4">Newsletter</h5>
                    <p>Subscribe to get special offers and updates</p>
                    <div class="position-relative w-100 mt-3">
                        <input class="form-control border-light w-100 py-2 ps-4 pe-5" type="text" placeholder="Your Email" style="background: rgba(255, 255, 255, 0.87);">
                        <button type="button" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">SignUp</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid copyright">
            <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="text-center text-md-start mb-3 mb-md-0">
                    &copy; <a class="border-bottom" href="#">Shopspree</a>, All Right Reserved.
                </div>
                <div class="text-center text-md-end">
                    Designed By <a class="border-bottom" href="https://htmlcodex.com">HTML Codex</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>