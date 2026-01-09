<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/product/get_products.php';
require_once __DIR__ . '/cart/cart_handler.php';

// Get filter parameters
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$page = get_page_number();

// Fetch products
$result = get_products([
    'category' => $category,
    'search' => $search,
    'page' => $page
], 12);

$products = $result['products'];
$categories = get_categories();
$messages = get_messages();
$cart_count = get_cart_count();

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
    <title>Shop - Shopspree</title>
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
        .shop-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            padding: 40px 0;
        }
        .shop-sidebar {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .filter-section {
            margin-bottom: 30px;
        }
        .filter-section h5 {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        .filter-option {
            margin-bottom: 10px;
        }
        .filter-option a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            display: block;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            transition: color 0.3s;
        }
        .filter-option a:hover {
            color: #0d6efd;
        }
        .shop-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .product-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #f0f0f0;
        }
        .product-body {
            padding: 15px;
        }
        .product-name {
            font-weight: 600;
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        .product-price {
            color: #0d6efd;
            font-weight: bold;
            font-size: 16px;
            margin: 10px 0;
        }
        .product-desc {
            font-size: 12px;
            color: #999;
            margin: 10px 0;
        }
        .btn-add-cart {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 13px;
            transition: background 0.3s;
        }
        .btn-add-cart:hover {
            background: #0b5ed7;
        }
        .btn-view {
            display: inline-block;
            margin-top: 8px;
            color: #0d6efd;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-view:hover {
            text-decoration: underline;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 40px;
            padding: 20px 0;
        }
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #0d6efd;
            transition: all 0.3s;
        }
        .pagination a:hover {
            background: #0d6efd;
            color: white;
        }
        .pagination a.active {
            background: #0d6efd;
            color: white;
        }
        .search-box {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .btn-search {
            width: 100%;
            padding: 10px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-search:hover {
            background: #0b5ed7;
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
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted mx-2" data-bs-toggle="dropdown"><small>English</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item"> English</a>
                            <a href="#" class="dropdown-item"> Turkish</a>
                            <a href="#" class="dropdown-item"> Spanish</a>
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
                        <input class="form-control border-0 rounded-pill w-100 py-3" type="text" name="search" placeholder="Search Looking For?" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <button type="submit" class="btn btn-primary rounded-pill py-3 px-5" style="border: 0;"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-center text-lg-end">
                <div class="d-inline-flex align-items-center">
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center me-3"><span class="rounded-circle btn-md-square border"><i class="fas fa-sync-alt"></i></span></a>
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center me-3"><span class="rounded-circle btn-md-square border"><i class="fas fa-heart"></i></span></a>
                    <a href="cart.php" class="text-muted d-flex align-items-center justify-content-center"><span class="rounded-circle btn-md-square border"><i class="fas fa-shopping-cart"></i></span>
                        <span class="text-dark ms-2">$<?php echo number_format($cart_total, 2); ?></span></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar p-0">
        <div class="row gx-0 bg-primary px-5 align-items-center">
            <div class="col-lg-3 d-none d-lg-block">
                <nav class="navbar navbar-light position-relative" style="width: 250px;">
                    <button class="navbar-toggler border-0 fs-4 w-100 px-0 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#allCat">
                        <h4 class="m-0"><i class="fa fa-bars me-2"></i>All Categories</h4>
                    </button>
                    <div class="collapse navbar-collapse rounded-bottom" id="allCat">
                        <div class="navbar-nav ms-auto py-0">
                            <ul class="list-unstyled categories-bars">
                                <li><div class='categories-bars-item'><a href='shop.php'>All Products</a></div></li>
                                <?php foreach($categories as $cat): ?>
                                <li><div class='categories-bars-item'><a href='shop.php?category=<?php echo htmlspecialchars($cat['id']); ?>'><?php echo htmlspecialchars($cat['name']); ?></a></div></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="col-12 col-lg-9">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary ">
                    <a href="index.php" class="navbar-brand d-block d-lg-none">
                        <h1 class="display-5 text-secondary m-0"><i class="fas fa-shopping-bag text-white me-2"></i>Shopspree</h1>
                    </a>
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars fa-1x"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="index.php" class="nav-item nav-link">Home</a>
                            <a href="shop.php" class="nav-item nav-link active">Shop</a>
                            <a href="cart.php" class="nav-item nav-link">Cart</a>
                            <a href="checkout.php" class="nav-item nav-link">Checkout</a>
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
            <h1 class="mb-4">Shop Products</h1>
            
            <?php foreach ($messages as $msg): ?>
                <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($msg['text']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>

            <div class="shop-container">
                <!-- Sidebar Filters -->
                <div class="shop-sidebar">
                    <div class="filter-section">
                        <h5><i class="fas fa-filter me-2"></i>Categories</h5>
                        <div class="filter-option">
                            <a href="shop.php">All Products</a>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <a href="shop.php?category=<?php echo htmlspecialchars($cat['id']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-section">
                        <h5><i class="fas fa-search me-2"></i>Search</h5>
                        <form method="get" action="shop.php">
                            <input type="text" class="search-box" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            <button type="submit" class="btn-search">Search</button>
                        </form>
                    </div>
                </div>

                <!-- Products Grid -->
                <div>
                    <?php if (empty($products)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted">No products found. Try adjusting your filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="shop-products">
                            <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: #e0e0e0; color: #999;">No Image</div>
                                <?php endif; ?>
                                <div class="product-body">
                                    <p class="product-name"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <p class="product-price"><?php echo format_price($product['price']); ?></p>
                                    <p class="product-desc"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 40)); ?>...</p>
                                    
                                    <?php if ($product['quantity'] > 0): ?>
                                        <form method="post" action="cart_actions.php" style="display: inline;">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn-add-cart"><i class="fas fa-shopping-cart me-1"></i> Add</button>
                                        </form>
                                        <a href="single.php?id=<?php echo $product['id']; ?>" class="btn-view">View Details →</a>
                                    <?php else: ?>
                                        <button class="btn-add-cart" disabled style="background: #ccc; cursor: not-allowed;">Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($result['pages'] > 1): ?>
                            <div class="pagination">
                                <?php if ($result['current_page'] > 1): ?>
                                    <a href="shop.php?page=1<?php echo $category ? '&category=' . $category : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">« First</a>
                                    <a href="shop.php?page=<?php echo $result['current_page'] - 1; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">‹ Prev</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                                    <a href="shop.php?page=<?php echo $i; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="<?php echo $i === $result['current_page'] ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>

                                <?php if ($result['current_page'] < $result['pages']): ?>
                                    <a href="shop.php?page=<?php echo $result['current_page'] + 1; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next ›</a>
                                    <a href="shop.php?page=<?php echo $result['pages']; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Last »</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
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
                    <div class="d-flex align-items-center">
                        <img class="img-fluid flex-shrink-0" src="img/footer-logo.png" alt="" onerror="this.style.display='none'">
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                    <h5 class="text-light mb-4">Address</h5>
                    <p><i class="fa fa-map-marker-alt me-3"></i>123 Street, New York, USA</p>
                    <p><i class="fa fa-phone-alt me-3"></i>+012 345 67890</p>
                    <p><i class="fa fa-envelope me-3"></i>info@example.com</p>
                    <div class="d-flex pt-2">
                        <a class="btn btn-square btn-outline-light rounded-circle me-2" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-square btn-outline-light rounded-circle me-2" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-outline-light rounded-circle me-2" href=""><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-outline-light rounded-circle rounded-0 me-0" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
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