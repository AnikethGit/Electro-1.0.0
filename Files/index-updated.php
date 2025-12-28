<?php
/**
 * Home Page / Dashboard
 * index.php
 * 
 * Main landing page with products
 */

session_start();

require_once 'config/database.php';

$is_logged_in = isset($_SESSION['user_id']);
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';

// Fetch featured products
$products = [];
$result = $conn->query('SELECT p.id, p.name, p.price, p.discount_price, p.image, p.short_description FROM products p WHERE p.is_active = TRUE AND p.is_featured = TRUE LIMIT 6');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch categories for navigation
$categories = [];
$cat_result = $conn->query('SELECT id, name, slug FROM categories LIMIT 8');
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecommerce Store - Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        
        nav {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
            font-size: 14px;
        }
        
        nav a:hover {
            opacity: 0.8;
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .user-greeting {
            color: white;
            font-size: 14px;
        }
        
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 42px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .products-section {
            margin-top: 60px;
        }
        
        .section-title {
            font-size: 32px;
            margin-bottom: 30px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 60px;
        }
        
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .product-description {
            color: #666;
            font-size: 13px;
            margin-bottom: 10px;
            max-height: 35px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .product-price {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .current-price {
            font-size: 18px;
            color: #667eea;
            font-weight: bold;
        }
        
        .original-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
        }
        
        .btn-add-cart {
            width: 100%;
            padding: 9px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.3s;
        }
        
        .btn-add-cart:hover {
            background: #764ba2;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 60px;
        }
        
        .no-products {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .featured-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <a href="index.php" class="logo">🛍️ Ecommerce</a>
            <nav>
                <a href="#products">Products</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                
                <div class="auth-buttons">
                    <?php if ($is_logged_in): ?>
                        <span class="user-greeting">Welcome, <?php echo htmlspecialchars($first_name); ?></span>
                        <a href="user/logout.php" class="btn btn-primary">Logout</a>
                    <?php else: ?>
                        <a href="user/login.php" class="btn btn-secondary">Login</a>
                        <a href="user/register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    
    <section class="hero">
        <h1>Welcome to Our Store</h1>
        <p>Discover amazing products at unbeatable prices</p>
        <?php if (!$is_logged_in): ?>
            <a href="user/register.php" class="btn btn-primary" style="display: inline-block;">Get Started</a>
        <?php endif; ?>
    </section>
    
    <div class="container">
        <section class="products-section" id="products">
            <h2 class="section-title">Featured Products</h2>
            
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="featured-badge">FEATURED</div>
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    📦 No Image
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-description"><?php echo htmlspecialchars(substr($product['short_description'] ?? '', 0, 50)); ?></div>
                                <div class="product-price">
                                    <span class="current-price">₹<?php echo number_format($product['discount_price'] ?? $product['price'], 2); ?></span>
                                    <?php if ($product['discount_price']): ?>
                                        <span class="original-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($is_logged_in): ?>
                                    <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                                <?php else: ?>
                                    <a href="user/login.php" class="btn-add-cart" style="display: block; text-align: center; text-decoration: none;">Login to Buy</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <p>No featured products available at the moment</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
    
    <footer>
        <p>&copy; 2024 Ecommerce Store. All rights reserved.</p>
    </footer>
    
    <script>
        function addToCart(productId) {
            alert('Product added to cart! (Feature coming soon)');
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>