<?php
/**
 * Cart Actions Handler
 * Processes cart operations (add, update, remove, clear)
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/cart/cart_handler.php';
require_once __DIR__ . '/cart/get_cart.php';

// Get action from request
$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');

try {
    switch ($action) {
        case 'add':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));
            
            if ($product_id <= 0) {
                add_message('Invalid product', 'error');
            } else {
                $result = add_to_cart($product_id, $quantity);
                if (!$result['success']) {
                    add_message($result['message'], 'error');
                }
            }
            break;

        case 'update':
            $cart_id = (int)($_POST['cart_id'] ?? 0);
            $quantity = max(0, (int)($_POST['quantity'] ?? 1));
            
            if ($cart_id <= 0) {
                add_message('Invalid cart item', 'error');
            } else {
                $success = update_cart_quantity($cart_id, $quantity);
                if ($success) {
                    add_message('Cart updated', 'success');
                } else {
                    add_message('Could not update cart. Check product stock.', 'error');
                }
            }
            break;

        case 'remove':
            $cart_id = (int)($_POST['cart_id'] ?? 0);
            
            if ($cart_id <= 0) {
                add_message('Invalid cart item', 'error');
            } else {
                $success = remove_from_cart($cart_id);
                if ($success) {
                    add_message('Item removed from cart', 'success');
                } else {
                    add_message('Could not remove item', 'error');
                }
            }
            break;

        case 'clear':
            $success = clear_cart();
            if ($success) {
                add_message('Cart cleared', 'success');
            } else {
                add_message('Could not clear cart', 'error');
            }
            break;

        default:
            add_message('Invalid action', 'error');
    }
} catch (Exception $e) {
    add_message('An error occurred: ' . $e->getMessage(), 'error');
}

// Redirect back to referrer or cart
$redirect = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'cart.php';
redirect($redirect);

?>