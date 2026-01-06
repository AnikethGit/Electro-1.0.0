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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Electro</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .product-name {
            font-weight: bold;
            margin: 10px 0;
        }
        .product-price {
            color: #27ae60;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .add-to-cart-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .add-to-cart-btn:hover {
            background: #2980b9;
        }
        .sidebar {
            float: left;
            width: 20%;
            padding-right: 20px;
        }
        .products-section {
            float: left;
            width: 80%;
        }
        .filter-section {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .filter-section h3 {
            margin-top: 0;
        }
        .filter-option {
            margin: 10px 0;
        }
        .filter-option a {
            color: #3498db;
            text-decoration: none;
        }
        .filter-option a:hover {
            text-decoration: underline;
        }
        .pagination {
            text-align: center;
            margin: 30px 0;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #3498db;
        }
        .pagination a.active {
            background: #3498db;
            color: white;
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
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="shop.php" class="active">Shop</a>
            <a href="cart.php">Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
        </nav>
    </header>

    <div class="container">
        <!-- Messages -->
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?php echo $msg['type']; ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
        <?php endforeach; ?>

        <h1>Shop Products</h1>

        <div class="content-wrapper">
            <!-- Sidebar with filters -->
            <div class="sidebar">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <div class="filter-option">
                        <a href="shop.php">All Categories</a>
                    </div>
                    <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <a href="shop.php?category=<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="filter-section">
                    <h3>Search</h3>
                    <form method="get" action="shop.php">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <button type="submit" style="width: 100%; margin-top: 10px; padding: 8px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <?php if (empty($products)): ?>
                    <p>No products found. Try adjusting your filters.</p>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div style="background: #e0e0e0; height: 200px; display: flex; align-items: center; justify-content: center;">
                                        No Image
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-price"><?php echo format_price($product['price']); ?></div>
                                <p style="font-size: 12px; color: #666;"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 50)); ?>...</p>
                                
                                <?php if ($product['quantity'] > 0): ?>
                                    <form method="post" action="cart_actions.php" style="display: inline;">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                                    </form>
                                    <a href="single.php?id=<?php echo $product['id']; ?>" style="display: block; margin-top: 10px; color: #3498db; text-decoration: none;">
                                        View Details →
                                    </a>
                                <?php else: ?>
                                    <button class="add-to-cart-btn" disabled style="background: #999; cursor: not-allowed;">Out of Stock</button>
                                <?php endif; ?>
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
                                <a href="shop.php?page=<?php echo $i; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="<?php echo $i === $result['current_page'] ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
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

    <footer>
        <p>&copy; 2026 Electro. All rights reserved.</p>
    </footer>

    <script>
        // Update cart count if this page is bookmarked or refreshed
        function updateCartCount() {
            // This would typically fetch via AJAX
            // For now, cart count is set from PHP
        }
    </script>
</body>
</html>