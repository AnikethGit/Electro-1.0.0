<?php
/**
 * Get Cart Items
 * Retrieves cart items from SESSION and calculates totals
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

/**
 * Get all cart items for current session
 * @return array Cart items with product details
 */
function get_cart_items() {
    global $conn;
    
    if (!is_array($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $cart_items = [];
    
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        
        if ($quantity <= 0) continue;
        
        // Get product details from database
        if ($stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?")) {
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $cart_items[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => floatval($product['price']),
                        'quantity' => $quantity,
                        'image' => $product['image']
                    ];
                }
            }
            $stmt->close();
        }
    }
    
    return $cart_items;
}

/**
 * Calculate cart totals
 * @param array $cart_items
 * @param float $tax_rate Tax as decimal (e.g., 0.08 for 8%)
 * @param float $shipping Fixed shipping cost
 * @return array Totals: subtotal, tax, shipping, total
 */
function calculate_cart_totals($cart_items, $tax_rate = 0.08, $shipping = 50) {
    $subtotal = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += floatval($item['price']) * intval($item['quantity']);
    }
    
    $tax = round($subtotal * $tax_rate, 2);
    $total = round($subtotal + $tax + $shipping, 2);
    
    return [
        'subtotal' => round($subtotal, 2),
        'tax' => $tax,
        'tax_rate' => $tax_rate * 100,
        'shipping' => $shipping,
        'total' => $total,
        'item_count' => count($cart_items)
    ];
}

/**
 * Get cart summary (items + totals)
 * @param float $tax_rate
 * @param float $shipping
 * @return array Complete cart data
 */
function get_cart_summary($tax_rate = 0.08, $shipping = 50) {
    $items = get_cart_items();
    $totals = calculate_cart_totals($items, $tax_rate, $shipping);
    
    return [
        'items' => $items,
        'totals' => $totals,
        'is_empty' => empty($items)
    ];
}

/**
 * Get cart count (number of items)
 * @return int
 */
function get_cart_count() {
    if (!is_array($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $count += intval($qty);
    }
    return $count;
}

/**
 * Check if cart is empty
 * @return bool
 */
function is_cart_empty() {
    return empty($_SESSION['cart']) || !is_array($_SESSION['cart']);
}

/**
 * Get cart item by product ID
 * @param int $product_id
 * @return array|null
 */
function get_cart_item_by_product($product_id) {
    global $conn;
    
    $product_id = intval($product_id);
    
    if (!isset($_SESSION['cart'][$product_id])) {
        return null;
    }
    
    $quantity = intval($_SESSION['cart'][$product_id]);
    
    // Get product details
    if ($stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?")) {
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $product = $result->fetch_assoc();
                return [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => floatval($product['price']),
                    'quantity' => $quantity,
                    'image' => $product['image']
                ];
            }
        }
        $stmt->close();
    }
    
    return null;
}

/**
 * Validate cart items have sufficient stock
 * @param array $cart_items
 * @return array ['valid' => bool, 'errors' => array]
 */
function validate_cart_stock($cart_items) {
    global $conn;
    
    $errors = [];
    
    foreach ($cart_items as $item) {
        $product_id = intval($item['id']);
        
        if ($stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?")) {
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $available = intval($product['quantity']);
                    
                    if ($available < intval($item['quantity'])) {
                        $errors[] = htmlspecialchars($item['name']) . ' - Insufficient stock (Available: ' . $available . ', Requested: ' . intval($item['quantity']) . ')';
                    }
                } else {
                    $errors[] = htmlspecialchars($item['name']) . ' - Product not found';
                }
            }
            $stmt->close();
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

?>
