<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/product/get_products.php';
require_once __DIR__ . '/cart/cart_handler.php';

// Fetch featured products
$featured = get_featured_products(6);
$categories = get_categories();
$messages = get_messages();
$cart_count = get_cart_count();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electro - Online Store</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        header {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        header nav {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }
        header nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        header nav a:hover,
        header nav a.active {
            background: #3498db;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 8px;
            margin: 30px 0;
        }
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
        }
        .hero-btn {
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .hero-btn:hover {
            background: #f0f0f0;
        }
        .section-title {
            font-size: 32px;
            margin: 50px 0 30px 0;
            text-align: center;
            color: #2c3e50;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        .product-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            padding: 15px;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .product-card-placeholder {
            width: 100%;
            height: 200px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            border-radius: 4px;
            color: #999;
        }
        .product-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .product-price {
            color: #27ae60;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .product-description {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .product-actions {
            display: flex;
            gap: 10px;
        }
        .add-to-cart-btn {
            flex: 1;
            background: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .add-to-cart-btn:hover {
            background: #2980b9;
        }
        .view-details-btn {
            flex: 1;
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        .view-details-btn:hover {
            background: #7f8c8d;
        }
        .categories-section {
            margin: 50px 0;
        }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .category-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: #2c3e50;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .category-card h3 {
            margin: 0;
            color: #3498db;
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
            <strong>Electro</strong>
            <a href="index.php" class="active">Home</a>
            <a href="shop.php">Shop</a>
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

        <!-- Hero Section -->
        <div class="hero">
            <h1>Welcome to Electro</h1>
            <p>Discover amazing products at great prices</p>
            <a href="shop.php" class="hero-btn">Shop Now</a>
        </div>

        <!-- Categories Section -->
        <div class="categories-section">
            <h2 class="section-title">Shop by Category</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="shop.php?category=<?php echo $cat['id']; ?>" class="category-card">
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p><?php echo htmlspecialchars($cat['description'] ?? ''); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Featured Products -->
        <h2 class="section-title">Featured Products</h2>
        <?php if (!empty($featured)): ?>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <div class="product-card">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-card-placeholder">No Image</div>
                        <?php endif; ?>
                        
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price"><?php echo format_price($product['price']); ?></div>
                        <div class="product-description">
                            <?php echo htmlspecialchars(substr($product['description'] ?? 'No description', 0, 80)); ?>...
                        </div>
                        
                        <div class="product-actions">
                            <?php if ($product['quantity'] > 0): ?>
                                <form method="post" action="cart_actions.php" style="flex: 1;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <button class="add-to-cart-btn" disabled style="background: #999; cursor: not-allowed; flex: 1;">Out of Stock</button>
                            <?php endif; ?>
                            <a href="single.php?id=<?php echo $product['id']; ?>" class="view-details-btn">Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #999;">No featured products available</p>
        <?php endif; ?>

        <!-- View All Products Link -->
        <div style="text-align: center; margin: 40px 0;">
            <a href="shop.php" style="background: #3498db; color: white; padding: 12px 30px; border-radius: 4px; text-decoration: none; font-weight: bold;">View All Products</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Electro Store. All rights reserved. | Fast Shipping | Secure Checkout | 24/7 Support</p>
    </footer>
</body>
</html>