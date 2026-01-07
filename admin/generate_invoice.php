<?php
/**
 * Manual Invoice Generation System
 * Admin tool to create invoices for offline purchases
 * Generates PDF and stores in database like online orders
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Check admin access (you can implement proper admin auth later)
// For now, basic protection
if (!isset($_GET['admin_key']) || $_GET['admin_key'] !== 'admin_access_key') {
    // Redirect or show access denied
    // Uncomment the line below for production:
    // redirect('../index.php');
}

$messages = get_messages();
$products = [];
$cart_items = [];

// Get available products from database
try {
    $stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY name");
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    add_message('Error loading products: ' . $e->getMessage(), 'error');
}

// Handle AJAX product selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    header('Content-Type: application/json');
    
    $product_id = sanitize($_POST['product_id'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (empty($product_id) || $quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        exit();
    }
    
    // Get product details
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'product' => $product,
        'quantity' => $quantity,
        'subtotal' => $product['price'] * $quantity
    ]);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Invoice - Electro Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 28px;
        }
        
        .admin-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .form-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
        }
        
        .form-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-row.full {
            grid-template-columns: 1fr;
        }
        
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .invoice-summary {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .invoice-summary h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .invoice-items {
            margin-bottom: 20px;
        }
        
        .invoice-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .invoice-item .item-name {
            font-weight: bold;
            flex: 1;
        }
        
        .invoice-item .item-qty {
            width: 60px;
            text-align: center;
        }
        
        .invoice-item .item-price {
            width: 80px;
            text-align: right;
        }
        
        .invoice-item .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .invoice-item .remove-btn:hover {
            background: #c0392b;
        }
        
        .invoice-totals {
            border-top: 2px solid #ddd;
            padding-top: 15px;
        }
        
        .invoice-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .invoice-total-row.total {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-weight: bold;
            font-size: 16px;
            color: #27ae60;
        }
        
        .empty-invoice {
            text-align: center;
            color: #999;
            padding: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .action-buttons button {
            flex: 1;
        }
        
        .product-selection {
            display: grid;
            grid-template-columns: 1fr 80px 80px;
            gap: 10px;
            align-items: flex-end;
        }
        
        .product-selection select,
        .product-selection input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .product-selection button {
            padding: 10px;
        }
        
        @media (max-width: 1024px) {
            .admin-content {
                grid-template-columns: 1fr;
            }
            
            .invoice-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>📋 Manual Invoice Generation</h1>
            <p style="margin-top: 10px; opacity: 0.9;">Create invoices for offline purchases</p>
        </div>
        
        <!-- Messages -->
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?php echo $msg['type']; ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
        <?php endforeach; ?>
        
        <div class="admin-content">
            <!-- Invoice Form -->
            <div class="form-section">
                <h2>Customer & Order Details</h2>
                <form id="invoiceForm" method="post" action="create_invoice.php">
                    <!-- Customer Information -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px; border-bottom: 1px solid #ecf0f1; padding-bottom: 10px;">Customer Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_name">Full Name <span class="required">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" required>
                            </div>
                            <div class="form-group">
                                <label for="customer_email">Email Address <span class="required">*</span></label>
                                <input type="email" id="customer_email" name="customer_email" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_phone">Phone Number <span class="required">*</span></label>
                                <input type="tel" id="customer_phone" name="customer_phone" required>
                            </div>
                            <div class="form-group">
                                <label for="customer_company">Company (Optional)</label>
                                <input type="text" id="customer_company" name="customer_company">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px; border-bottom: 1px solid #ecf0f1; padding-bottom: 10px;">Shipping Address</h3>
                        
                        <div class="form-group form-row full">
                            <label for="shipping_address">Street Address <span class="required">*</span></label>
                            <input type="text" id="shipping_address" name="shipping_address" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">City <span class="required">*</span></label>
                                <input type="text" id="shipping_city" name="shipping_city" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_state">State <span class="required">*</span></label>
                                <input type="text" id="shipping_state" name="shipping_state" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_postal">Postal Code <span class="required">*</span></label>
                                <input type="text" id="shipping_postal" name="shipping_postal" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_country">Country <span class="required">*</span></label>
                                <input type="text" id="shipping_country" name="shipping_country" value="USA" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method & Notes -->
                    <div>
                        <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px; border-bottom: 1px solid #ecf0f1; padding-bottom: 10px;">Payment & Notes</h3>
                        
                        <div class="form-group">
                            <label for="payment_method">Payment Method <span class="required">*</span></label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Check">Check</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="invoice_notes">Invoice Notes (Optional)</label>
                            <textarea id="invoice_notes" name="invoice_notes" rows="4" placeholder="Special instructions or notes..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Invoice Summary & Products -->
            <div>
                <!-- Product Selection -->
                <div class="form-section">
                    <h2>Add Products</h2>
                    
                    <div class="form-group form-row full">
                        <div>
                            <label for="product_select">Select Product <span class="required">*</span></label>
                            <div class="product-selection">
                                <select id="product_select" required>
                                    <option value="">-- Select a product --</option>
                                    <?php foreach ($products as $prod): ?>
                                        <option value="<?php echo $prod['id']; ?>">
                                            <?php echo htmlspecialchars($prod['name']); ?> 
                                            (₹<?php echo number_format($prod['price'], 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" id="product_qty" min="1" value="1" placeholder="Qty">
                                <button type="button" class="btn" onclick="addItemToInvoice()">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Invoice Summary -->
                <div class="invoice-summary">
                    <h2>Invoice Summary</h2>
                    
                    <div id="invoiceItems" class="invoice-items">
                        <div class="empty-invoice">No items added yet</div>
                    </div>
                    
                    <div class="invoice-totals">
                        <div class="invoice-total-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">₹0.00</span>
                        </div>
                        <div class="invoice-total-row">
                            <span>Tax (8%):</span>
                            <span id="tax_amount">₹0.00</span>
                        </div>
                        <div class="invoice-total-row">
                            <span>Shipping:</span>
                            <span id="shipping_amount">₹0.00</span>
                        </div>
                        <div class="invoice-total-row total">
                            <span>Total:</span>
                            <span id="grand_total">₹0.00</span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn btn-success" onclick="submitInvoice()" style="width: 100%;">Generate & Download PDF</button>
                    </div>
                    <div class="action-buttons">
                        <button type="reset" class="btn btn-secondary" onclick="resetForm()" style="width: 100%;">Clear All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let invoiceItems = [];
        const TAX_RATE = 0.08;
        const SHIPPING_COST = 5.00;
        
        function addItemToInvoice() {
            const productSelect = document.getElementById('product_select');
            const qtyInput = document.getElementById('product_qty');
            
            if (!productSelect.value) {
                alert('Please select a product');
                return;
            }
            
            const quantity = parseInt(qtyInput.value) || 1;
            const productId = productSelect.value;
            const productText = productSelect.options[productSelect.selectedIndex].text;
            
            // Extract product name and price from option text
            const match = productText.match(/(.+)\s*\((₹|\$)([\d.]+)\)/);
            if (!match) {
                alert('Error parsing product information');
                return;
            }
            
            const productName = match[1].trim();
            const price = parseFloat(match[3]);
            
            // Check if item already exists and update quantity
            const existingItem = invoiceItems.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                invoiceItems.push({
                    id: productId,
                    name: productName,
                    price: price,
                    quantity: quantity
                });
            }
            
            updateInvoiceDisplay();
            productSelect.value = '';
            qtyInput.value = '1';
        }
        
        function removeItemFromInvoice(index) {
            invoiceItems.splice(index, 1);
            updateInvoiceDisplay();
        }
        
        function updateInvoiceDisplay() {
            const container = document.getElementById('invoiceItems');
            
            if (invoiceItems.length === 0) {
                container.innerHTML = '<div class="empty-invoice">No items added yet</div>';
            } else {
                container.innerHTML = invoiceItems.map((item, index) => `
                    <div class="invoice-item">
                        <span class="item-name">${item.name}</span>
                        <span class="item-qty">${item.quantity}</span>
                        <span class="item-price">₹${(item.price * item.quantity).toFixed(2)}</span>
                        <button type="button" class="remove-btn" onclick="removeItemFromInvoice(${index})">Remove</button>
                    </div>
                `).join('');
            }
            
            updateTotals();
        }
        
        function updateTotals() {
            const subtotal = invoiceItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * TAX_RATE;
            const shipping = invoiceItems.length > 0 ? SHIPPING_COST : 0;
            const total = subtotal + tax + shipping;
            
            document.getElementById('subtotal').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('tax_amount').textContent = '₹' + tax.toFixed(2);
            document.getElementById('shipping_amount').textContent = '₹' + shipping.toFixed(2);
            document.getElementById('grand_total').textContent = '₹' + total.toFixed(2);
            
            // Store totals in form for submission
            document.getElementById('subtotal_value').value = subtotal.toFixed(2);
            document.getElementById('tax_value').value = tax.toFixed(2);
            document.getElementById('shipping_value').value = shipping.toFixed(2);
            document.getElementById('total_value').value = total.toFixed(2);
        }
        
        function submitInvoice() {
            if (invoiceItems.length === 0) {
                alert('Please add at least one product');
                return;
            }
            
            // Validate form
            const form = document.getElementById('invoiceForm');
            if (!form.reportValidity()) {
                return;
            }
            
            // Add items to form
            document.getElementById('invoice_items_json').value = JSON.stringify(invoiceItems);
            
            // Submit form
            form.submit();
        }
        
        function resetForm() {
            if (confirm('Clear all invoice data?')) {
                document.getElementById('invoiceForm').reset();
                invoiceItems = [];
                updateInvoiceDisplay();
            }
        }
    </script>
    
    <!-- Hidden form fields for submission -->
    <form id="invoiceForm" style="display: none;" method="post" action="create_invoice.php">
        <input type="hidden" id="customer_name" name="customer_name">
        <input type="hidden" id="customer_email" name="customer_email">
        <input type="hidden" id="customer_phone" name="customer_phone">
        <input type="hidden" id="customer_company" name="customer_company">
        <input type="hidden" id="shipping_address" name="shipping_address">
        <input type="hidden" id="shipping_city" name="shipping_city">
        <input type="hidden" id="shipping_state" name="shipping_state">
        <input type="hidden" id="shipping_postal" name="shipping_postal">
        <input type="hidden" id="shipping_country" name="shipping_country">
        <input type="hidden" id="payment_method" name="payment_method">
        <input type="hidden" id="invoice_notes" name="invoice_notes">
        <input type="hidden" id="subtotal_value" name="subtotal">
        <input type="hidden" id="tax_value" name="tax">
        <input type="hidden" id="shipping_value" name="shipping">
        <input type="hidden" id="total_value" name="total">
        <input type="hidden" id="invoice_items_json" name="items_json">
    </form>
</body>
</html>