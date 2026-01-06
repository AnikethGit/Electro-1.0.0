<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/product/get_products.php';
require_once __DIR__ . '/cart/cart_handler.php';

// Get product ID
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    redirect('shop.php');
}

// Fetch product
$product = get_product_by_id($product_id);
if (!$product) {
    redirect('shop.php');
}

$messages = get_messages();
$cart_count = get_cart_count();

// Get related products from same category
$related = get_products_by_category($product['category_id'], 4);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Electro</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 40px 0;
        }
        .product-image {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-image img {
            width: 100%;
            max-width: 500px;
            border-radius: 8px;
        }
        .product-info h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        .product-price-large {
            font-size: 32px;
            color: #27ae60;
            font-weight: bold;
            margin: 20px 0;
        }
        .product-description {
            color: #555;
            line-height: 1.6;
            margin: 20px 0;
        }
        .stock-info {
            margin: 20px 0;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 4px;
        }
        .stock-info.in-stock {
            background: #d4edda;
            color: #155724;
        }
        .stock-info.out-stock {
            background: #f8d7da;
            color: #721c24;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }
        .quantity-selector input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .add-to-cart-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        .add-to-cart-btn:hover {
            background: #2980b9;
        }
        .add-to-cart-btn:disabled {
            background: #999;
            cursor: not-allowed;
        }
        .product-meta {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .product-meta p {
            margin: 10px 0;
        }
        .related-products {
            margin-top: 50px;
        }
        .related-products h2 {
            margin-bottom: 20px;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .related-item {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .related-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .related-item-price {
            color: #27ae60;
            font-weight: bold;
            margin: 10px 0;
        }
        .breadcrumb {
            margin-bottom: 30px;
        }
        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
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

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Home</a> / 
            <a href="shop.php">Shop</a> / 
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <!-- Product Detail -->
        <div class="product-detail">
            <!-- Image -->
            <div class="product-image">
                <?php if ($product['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div style="background: #e0e0e0; width: 100%; height: 400px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                        <span style="color: #999;">No Image Available</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details -->
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-price-large"><?php echo format_price($product['price']); ?></div>

                <!-- Stock Info -->
                <div class="stock-info <?php echo $product['quantity'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                    <?php if ($product['quantity'] > 0): ?>
                        <strong>In Stock:</strong> <?php echo $product['quantity']; ?> available
                    <?php else: ?>
                        <strong>Out of Stock</strong>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available')); ?>
                </div>

                <!-- Add to Cart Form -->
                <?php if ($product['quantity'] > 0): ?>
                    <form method="post" action="cart_actions.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['quantity']; ?>" value="1">
                        </div>

                        <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <button class="add-to-cart-btn" disabled style="background: #999; cursor: not-allowed;">Out of Stock</button>
                <?php endif; ?>

                <!-- Product Meta -->
                <div class="product-meta">
                    <p><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></p>
                    <p><strong>Category:</strong> 
                        <?php 
                            $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                            $cat_stmt->execute([$product['category_id']]);
                            $cat = $cat_stmt->fetch();
                            echo htmlspecialchars($cat['name'] ?? 'N/A');
                        ?>
                    </p>
                    <p><strong>Added:</strong> <?php echo format_date($product['created_at']); ?></p>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related)): ?>
            <div class="related-products">
                <h2>Related Products</h2>
                <div class="related-grid">
                    <?php foreach ($related as $item): ?>
                        <?php if ($item['id'] !== $product['id']): ?>
                            <div class="related-item">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <div style="background: #e0e0e0; height: 150px; display: flex; align-items: center; justify-content: center;">
                                        No Image
                                    </div>
                                <?php endif; ?>
                                <a href="single.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: #333;">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                </a>
                                <div class="related-item-price"><?php echo format_price($item['price']); ?></div>
                                <a href="single.php?id=<?php echo $item['id']; ?>" style="color: #3498db;">View Details</a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2026 Electro. All rights reserved.</p>
    </footer>
</body>
</html>