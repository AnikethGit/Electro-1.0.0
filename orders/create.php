<?php
/**
 * Order Creation & Processing
 * Processes checkout form, creates order, and clears cart
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../cart/get_cart.php';
require_once __DIR__ . '/../cart/cart_handler.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

// Check if cart is empty
if (is_cart_empty()) {
    add_message('Your cart is empty', 'error');
    redirect('cart.php');
}

try {
    // Validate required fields
    $required_fields = ['email', 'first_name', 'last_name', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'payment_method'];
    $errors = [];
    $data = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        } else {
            $data[$field] = sanitize($_POST[$field]);
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            add_message($error, 'error');
        }
        redirect('checkout.php');
    }

    // Validate email
    if (!is_valid_email($data['email'])) {
        add_message('Invalid email address', 'error');
        redirect('checkout.php');
    }

    // Get cart data
    $cart_summary = get_cart_summary(0.08, 5.00);
    $cart_items = $cart_summary['items'];
    $totals = $cart_summary['totals'];

    // Validate stock again
    $stock_validation = validate_cart_stock($cart_items);
    if (!$stock_validation['valid']) {
        foreach ($stock_validation['errors'] as $error) {
            add_message($error, 'error');
        }
        redirect('cart.php');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Create order
    $order_id = generate_order_id();
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    
    $shipping_address = $data['address'] . ', ' . $data['city'] . ', ' . $data['state'];
    
    $stmt = $pdo->prepare(
        "INSERT INTO orders (order_id, user_id, email, phone, shipping_address, shipping_city, 
         shipping_state, shipping_postal_code, shipping_country, total_amount, payment_method, 
         order_status, notes, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())"
    );
    
    $notes = sanitize($_POST['notes'] ?? '');
    $stmt->execute([
        $order_id,
        $user_id,
        $data['email'],
        $data['phone'],
        $shipping_address,
        $data['city'],
        $data['state'],
        $data['postal_code'],
        $data['country'],
        $totals['total'],
        $data['payment_method'],
        $notes
    ]);

    // Get the created order's ID
    $order_db_id = $pdo->lastInsertId();

    // Add items to order_items
    $item_stmt = $pdo->prepare(
        "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    foreach ($cart_items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $item_stmt->execute([
            $order_db_id,
            $item['product_id'],
            $item['name'],
            $item['quantity'],
            $item['price'],
            $subtotal
        ]);

        // Update product stock
        $stock_stmt = $pdo->prepare(
            "UPDATE products SET quantity = quantity - ? WHERE id = ?"
        );
        $stock_stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Clear cart
    $user_id_val = is_logged_in() ? $_SESSION['user_id'] : null;
    $session_id_val = !is_logged_in() ? session_id() : null;
    $clear_stmt = $pdo->prepare(
        "DELETE FROM cart WHERE (user_id = ? OR session_id = ?)"
    );
    $clear_stmt->execute([$user_id_val, $session_id_val]);

    // Commit transaction
    $pdo->commit();

    // Redirect to thank you page
    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['last_order_db_id'] = $order_db_id;
    redirect('orders/thank_you.php');

} catch (Exception $e) {
    // Rollback if error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    add_message('Error processing order: ' . $e->getMessage(), 'error');
    redirect('checkout.php');
}

?>