<?php
/**
 * Home Page / Dashboard
 * index.php
 * 
 * Main landing page and user dashboard
 */

session_start();

// Include database
require_once 'config/database.php';

$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';

// Fetch featured products
$products = [];
$result = $conn->query('SELECT id, name, price, image_url, description FROM products LIMIT 6');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
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
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
        }
        
        nav a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        nav a:hover {
            opacity: 0.8;
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
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
        }
        
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 48px;
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
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 22px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .product-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            max-height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .btn-add-cart {
            width: 100%;
            padding: 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
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
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <a href="index.php" class="logo">🛍️ Ecommerce Store</a>
            <nav>
                <a href="#products">Products</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                
                <div class="auth-buttons">
                    <?php if ($is_logged_in): ?>
                        <span class="user-greeting">Welcome, <?php echo htmlspecialchars($username); ?></span>
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
                            <div class="product-image">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    📦 No Image
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-description"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 50)); ?>...</div>
                                
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
                    <p>No products available at the moment</p>
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