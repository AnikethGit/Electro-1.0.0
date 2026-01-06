<?php
/**
 * Get Cart Items
 * Retrieves cart items and calculates totals
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

/**
 * Get all cart items for current user/session
 * @return array Cart items with product details
 */
function get_cart_items() {
    global $pdo;
    
    try {
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $session_id = !is_logged_in() ? session_id() : null;
        
        $stmt = $pdo->prepare(
            "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image_url, p.sku
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE (c.user_id = ? OR c.session_id = ?)
             ORDER BY c.added_at DESC"
        );
        $stmt->execute([$user_id, $session_id]);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Calculate cart totals
 * @param array $cart_items
 * @param float $tax_rate Tax as decimal (e.g., 0.08 for 8%)
 * @param float $shipping Fixed shipping cost
 * @return array Totals: subtotal, tax, shipping, total
 */
function calculate_cart_totals($cart_items, $tax_rate = 0.08, $shipping = 0) {
    $subtotal = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $tax = round($subtotal * $tax_rate, 2);
    $total = round($subtotal + $tax + $shipping, 2);
    
    return [
        'subtotal' => $subtotal,
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
function get_cart_summary($tax_rate = 0.08, $shipping = 0) {
    $items = get_cart_items();
    $totals = calculate_cart_totals($items, $tax_rate, $shipping);
    
    return [
        'items' => $items,
        'totals' => $totals,
        'is_empty' => empty($items)
    ];
}

/**
 * Check if cart is empty
 * @return bool
 */
function is_cart_empty() {
    $items = get_cart_items();
    return empty($items);
}

/**
 * Get cart item by ID
 * @param int $cart_id
 * @return array|null
 */
function get_cart_item($cart_id) {
    global $pdo;
    
    try {
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $session_id = !is_logged_in() ? session_id() : null;
        
        $stmt = $pdo->prepare(
            "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image_url, p.sku, p.quantity as stock
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.id = ? AND (c.user_id = ? OR c.session_id = ?)"
        );
        $stmt->execute([$cart_id, $user_id, $session_id]);
        return $stmt->fetch() ?: null;
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Validate cart items have sufficient stock
 * @param array $cart_items
 * @return array ['valid' => bool, 'errors' => array]
 */
function validate_cart_stock($cart_items) {
    global $pdo;
    
    $errors = [];
    
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch();
        
        if (!$product || $product['quantity'] < $item['quantity']) {
            $errors[] = $item['name'] . ' - Insufficient stock (Available: ' . ($product['quantity'] ?? 0) . ')';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Migrate cart from session to user (on login)
 * @param int $user_id
 * @return bool
 */
function migrate_cart_to_user($user_id) {
    global $pdo;
    
    try {
        $session_id = session_id();
        
        // Get all session cart items
        $stmt = $pdo->prepare(
            "SELECT product_id, quantity FROM cart WHERE session_id = ? AND user_id IS NULL"
        );
        $stmt->execute([$session_id]);
        $session_items = $stmt->fetchAll();
        
        foreach ($session_items as $item) {
            // Check if user already has this product in cart
            $check = $pdo->prepare(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?"
            );
            $check->execute([$user_id, $item['product_id']]);
            $existing = $check->fetch();
            
            if ($existing) {
                // Update existing
                $update = $pdo->prepare(
                    "UPDATE cart SET quantity = quantity + ? WHERE id = ?"
                );
                $update->execute([$item['quantity'], $existing['id']]);
            } else {
                // Add to user cart
                $insert = $pdo->prepare(
                    "UPDATE cart SET user_id = ?, session_id = NULL WHERE product_id = ? AND session_id = ?"
                );
                $insert->execute([$user_id, $item['product_id'], $session_id]);
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

?>