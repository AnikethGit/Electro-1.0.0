<?php
/**
 * Order Tracking Page
 * Allows customers to track their orders using Order ID
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$order_id = isset($_GET['order_id']) ? sanitize($_GET['order_id']) : '';
$order = null;
$order_items = [];
$error_message = '';

if (!empty($order_id)) {
    // Fetch order
    $query = "SELECT * FROM orders WHERE order_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $order_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $order = $result->fetch_assoc();
                
                // Fetch order items
                $items_query = "SELECT * FROM order_items WHERE order_id = ?";
                if ($items_stmt = $conn->prepare($items_query)) {
                    $items_stmt->bind_param("i", $order['id']);
                    if ($items_stmt->execute()) {
                        $items_result = $items_stmt->get_result();
                        while ($item = $items_result->fetch_assoc()) {
                            $order_items[] = $item;
                        }
                    }
                    $items_stmt->close();
                }
            } else {
                $error_message = "Order not found. Please check your Order ID.";
            }
        }
        $stmt->close();
    }
}

// Status color mapping
$status_colors = [
    'Pending' => '#FFC107',
    'Processing' => '#17A2B8',
    'Shipped' => '#007BFF',
    'Delivered' => '#28A745',
    'Cancelled' => '#DC3545'
];

$current_status = $order ? $order['order_status'] : '';
$status_color = isset($status_colors[$current_status]) ? $status_colors[$current_status] : '#6C757D';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - Shopspree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
        }
        
        .tracking-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .page-title {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            font-size: 32px;
            font-weight: 700;
        }
        
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .search-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .search-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .search-group input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .search-group button {
            padding: 12px 30px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .search-group button:hover {
            background: #0056b3;
        }
        
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }
        
        .order-header h2 {
            color: #333;
            margin: 0;
            font-weight: 600;
        }
        
        .status-badge {
            background: <?php echo $status_color; ?>;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .timeline {
            margin: 30px 0;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 20px;
            position: relative;
        }
        
        .timeline-marker {
            width: 40px;
            height: 40px;
            background: #007BFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .timeline-marker.completed {
            background: #28A745;
        }
        
        .timeline-marker.current {
            background: #FFC107;
            color: #333;
        }
        
        .timeline-marker.pending {
            background: #CCCCCC;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-content h4 {
            color: #333;
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .timeline-content p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .info-item {
            border-right: 2px solid #e0e0e0;
            padding-right: 20px;
        }
        
        .info-item:nth-child(2n) {
            border-right: none;
        }
        
        .info-label {
            color: #666;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        .items-section {
            margin-top: 30px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .items-table th {
            background: #f0f0f0;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .back-link {
            color: #007BFF;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .back-link:hover {
            color: #0056b3;
        }
        
        .no-result {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .no-result i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .no-result h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .info-row {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                border-right: none;
                border-bottom: 2px solid #e0e0e0;
                padding-right: 0;
                padding-bottom: 15px;
                margin-bottom: 15px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-title">
            <i class="fas fa-box me-3"></i>Track Your Order
        </div>
        
        <div class="search-section">
            <h3><i class="fas fa-search me-2"></i>Find Your Order</h3>
            <form method="GET" class="search-group">
                <input type="text" name="order_id" placeholder="Enter your Order ID (e.g., ORD-ABC123)" value="<?php echo htmlspecialchars($order_id); ?>" required>
                <button type="submit"><i class="fas fa-search me-2"></i>Track</button>
            </form>
            <small style="color: #666;">You can find your Order ID in the confirmation email we sent you.</small>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="tracking-container">
                <div class="error-message">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            </div>
        <?php elseif ($order): ?>
            <div class="tracking-container">
                <div class="order-header">
                    <div>
                        <h2>Order <?php echo htmlspecialchars($order['order_id']); ?></h2>
                        <small style="color: #666;">Ordered on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></small>
                    </div>
                    <div class="status-badge"><?php echo htmlspecialchars($order['order_status']); ?></div>
                </div>
                
                <!-- Timeline -->
                <div class="timeline">
                    <?php
                    $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered'];
                    foreach ($statuses as $index => $status):
                        $is_completed = in_array($current_status, ['Processing', 'Shipped', 'Delivered']) && $index < array_search($current_status, $statuses);
                        $is_current = $current_status === $status;
                        $is_pending = !$is_completed && !$is_current;
                        
                        $marker_class = $is_completed ? 'completed' : ($is_current ? 'current' : 'pending');
                        $status_text = '';
                        
                        if ($is_completed) {
                            $status_text = 'Completed';
                        } elseif ($is_current) {
                            $status_text = 'Current Status';
                        } else {
                            $status_text = 'Upcoming';
                        }
                    ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?php echo $marker_class; ?>">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h4><?php echo htmlspecialchars($status); ?></h4>
                                <p><?php echo $status_text; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Order Information -->
                <h4 style="margin-top: 30px; margin-bottom: 15px; color: #333; font-weight: 600;">Order Details</h4>
                <div class="order-info">
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">Shipping Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Payment Method</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">Order Total</div>
                            <div class="info-value" style="color: #007BFF;">$<?php echo number_format($order['total_amount'], 2); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Order Status</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['order_status']); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Items Section -->
                <?php if (!empty($order_items)): ?>
                    <div class="items-section">
                        <h4 style="color: #333; font-weight: 600; margin-bottom: 15px;">Items in Your Order</h4>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        </td>
                                        <td><?php echo intval($item['quantity']); ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <a href="../index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>Back to Home
                </a>
            </div>
        <?php else: ?>
            <div class="tracking-container">
                <div class="no-result">
                    <i class="fas fa-search"></i>
                    <h3>No Order Found</h3>
                    <p>Enter your Order ID above to track your order status.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
if ($conn) {
    $conn->close();
}
?>
