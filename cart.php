<?php
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopping Cart - Electro</title>
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
</head>
<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

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
                            <a href="#" class="dropdown-item"> Dolar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid px-5 py-4 d-none d-lg-block">
        <div class="row gx-0 align-items-center text-center">
            <div class="col-md-4 col-lg-3 text-center text-lg-start">
                <a href="index.php" class="navbar-brand p-0">
                    <h1 class="display-5 text-primary m-0"><i class="fas fa-shopping-bag text-secondary me-2"></i>Electro</h1>
                </a>
            </div>
            <div class="col-md-4 col-lg-6 text-center">
                <div class="position-relative ps-4">
                    <div class="d-flex border rounded-pill">
                        <input class="form-control border-0 rounded-pill w-100 py-3" type="text" placeholder="Search Looking For?">
                        <select class="form-select text-dark border-0 border-start rounded-0 p-3" style="width: 200px;">
                            <option value="All Category">All Category</option>
                        </select>
                        <button type="button" class="btn btn-primary rounded-pill py-3 px-5" style="border: 0;"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-center text-lg-end">
                <a href="cart.php" class="text-muted d-flex align-items-center justify-content-center"><span class="rounded-circle btn-md-square border"><i class="fas fa-shopping-cart"></i></span>
                    <span class="text-dark ms-2">$<?php echo number_format($cart_total, 2); ?></span></a>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar p-0">
        <div class="row gx-0 bg-primary px-5 align-items-center">
            <div class="col-12 col-lg-9 ms-auto">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary">
                    <a href="index.php" class="navbar-brand d-block d-lg-none">
                        <h1 class="display-5 text-secondary m-0"><i class="fas fa-shopping-bag text-white me-2"></i>Electro</h1>
                    </a>
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars fa-1x"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="index.php" class="nav-item nav-link">Home</a>
                            <a href="shop.php" class="nav-item nav-link">Shop</a>
                            <a href="cart.php" class="nav-item nav-link active">Cart</a>
                            <a href="contact.php" class="nav-item nav-link me-2">Contact</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->

    <!-- Single Page Header start -->
    <div class="container-fluid page-header py-5">
        <h1 class="text-center text-white display-6">Cart Page</h1>
    </div>
    <!-- Single Page Header End -->

    <!-- Cart Page Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <?php foreach ($messages as $msg): ?>
                <div class="alert alert-<?php echo htmlspecialchars($msg['type']); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($msg['text']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>

            <?php if ($cart_summary['is_empty']): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; color: #ccc;"></i>
                    <p style="font-size: 18px; color: #666; margin: 20px 0;">Your cart is empty</p>
                    <a href="shop.php" class="btn btn-primary rounded-pill px-4 py-3">Continue Shopping</a>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Total</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="post" style="display: inline; width: 100px;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <div class="input-group">
                                        <button class="btn btn-sm btn-outline-secondary" type="submit" name="qty" value="<?php echo max(1, $item['quantity'] - 1); ?>">-</button>
                                        <input type="text" class="form-control form-control-sm text-center" value="<?php echo $item['quantity']; ?>" readonly>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit" name="qty" value="<?php echo $item['quantity'] + 1; ?>">+</button>
                                    </div>
                                </form>
                            </td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Remove this item?');">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-4 justify-content-end mt-5">
                <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
                    <div class="bg-light rounded p-4">
                        <h3 class="mb-4">Cart Total</h3>
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Subtotal:</h5>
                            <p>$<?php echo number_format($totals['subtotal'], 2); ?></p>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Shipping:</h5>
                            <p>$<?php echo number_format($totals['shipping'], 2); ?></p>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <h5 class="fw-bold">Total:</h5>
                            <p class="fw-bold">$<?php echo number_format($totals['total'], 2); ?></p>
                        </div>
                        <a href="checkout.php" class="btn btn-primary rounded-pill w-100 py-3">Proceed Checkout</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Cart Page End -->

    <!-- Footer Start -->
    <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-primary mb-4">Customer Service</h4>
                        <a href="#"><i class="fas fa-angle-right me-2"></i> Contact Us</a>
                        <a href="#"><i class="fas fa-angle-right me-2"></i> Returns</a>
                        <a href="#"><i class="fas fa-angle-right me-2"></i> Order History</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-primary mb-4">Information</h4>
                        <a href="#"><i class="fas fa-angle-right me-2"></i> About Us</a>
                        <a href="#"><i class="fas fa-angle-right me-2"></i> Privacy Policy</a>
                        <a href="#"><i class="fas fa-angle-right me-2"></i> Terms & Conditions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Copyright Start -->
    <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-md-6 text-center text-md-start mb-md-0">
                    <span class="text-white"><a href="#" class="border-bottom text-white"><i class="fas fa-copyright text-light me-2"></i>Your Site Name</a>, All right reserved.</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Copyright End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>
</html>