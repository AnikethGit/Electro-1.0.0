<?php
/**
 * Cart Handler
 * Manages shopping cart operations in database
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

/**
 * Get user identifier for cart (logged-in user ID or session ID)
 * @return string|int
 */
function get_cart_identifier() {
    if (is_logged_in()) {
        return 'user_' . $_SESSION['user_id'];
    }
    return 'session_' . session_id();
}

/**
 * Add product to cart
 * @param int $product_id
 * @param int $quantity
 * @return bool|array Success or error details
 */
function add_to_cart($product_id, $quantity = 1) {
    global $pdo;
    
    try {
        // Validate product exists and is in stock
        $product_check = $pdo->prepare(
            "SELECT id, quantity, price, name FROM products WHERE id = ? AND is_active = 1"
        );
        $product_check->execute([$product_id]);
        $product = $product_check->fetch();
        
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        if ($product['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock available'];
        }
        
        // Prepare cart data
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $session_id = !is_logged_in() ? session_id() : null;
        
        // Check if product already in cart
        $cart_check = $pdo->prepare(
            "SELECT id, quantity FROM cart WHERE product_id = ? AND (user_id = ? OR session_id = ?)"
        );
        $cart_check->execute([$product_id, $user_id, $session_id]);
        $existing = $cart_check->fetch();
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            if ($product['quantity'] < $new_quantity) {
                return ['success' => false, 'message' => 'Cannot add more items - insufficient stock'];
            }
            
            $update = $pdo->prepare(
                "UPDATE cart SET quantity = ?, added_at = NOW() WHERE id = ?"
            );
            $update->execute([$new_quantity, $existing['id']]);
        } else {
            // Insert new cart item
            $insert = $pdo->prepare(
                "INSERT INTO cart (user_id, session_id, product_id, quantity, added_at) 
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $insert->execute([$user_id, $session_id, $product_id, $quantity]);
        }
        
        add_message($product['name'] . ' added to cart', 'success');
        return ['success' => true, 'message' => 'Product added to cart'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding to cart: ' . $e->getMessage()];
    }
}

/**
 * Update cart item quantity
 * @param int $cart_id
 * @param int $quantity
 * @return bool
 */
function update_cart_quantity($cart_id, $quantity) {
    global $pdo;
    
    if ($quantity <= 0) {
        return remove_from_cart($cart_id);
    }
    
    try {
        // Verify stock before updating
        $cart_item = $pdo->prepare(
            "SELECT product_id FROM cart WHERE id = ?"
        );
        $cart_item->execute([$cart_id]);
        $item = $cart_item->fetch();
        
        $product = $pdo->prepare(
            "SELECT quantity FROM products WHERE id = ?"
        );
        $product->execute([$item['product_id']]);
        $prod = $product->fetch();
        
        if ($prod['quantity'] < $quantity) {
            return false;
        }
        
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, added_at = NOW() WHERE id = ?");
        return $stmt->execute([$quantity, $cart_id]);
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove item from cart
 * @param int $cart_id
 * @return bool
 */
function remove_from_cart($cart_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        return $stmt->execute([$cart_id]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Clear entire cart for user/session
 * @return bool
 */
function clear_cart() {
    global $pdo;
    
    try {
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $session_id = !is_logged_in() ? session_id() : null;
        
        $stmt = $pdo->prepare(
            "DELETE FROM cart WHERE (user_id = ? OR session_id = ?)"
        );
        return $stmt->execute([$user_id, $session_id]);
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get cart count
 * @return int
 */
function get_cart_count() {
    global $pdo;
    
    try {
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $session_id = !is_logged_in() ? session_id() : null;
        
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ? OR session_id = ?"
        );
        $stmt->execute([$user_id, $session_id]);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
        
    } catch (Exception $e) {
        return 0;
    }
}

?>