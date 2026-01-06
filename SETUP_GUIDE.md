# Electro 1.0.0 - Setup & Development Guide

## 📋 Overview

This e-commerce system has been refactored to use **dynamic PHP with database integration** instead of hardcoded HTML. Products and cart items are now managed from the database, enabling real shopping cart functionality.

---

## 🗂️ Directory Structure

```
Electro-1.0.0/
├── config/
│   ├── db.php              # Database connection (PDO)
│   └── helpers.php         # Reusable utility functions
├── product/
│   └── get_products.php    # Product fetching & filtering
├── cart/
│   ├── cart_handler.php    # Add/update/remove cart items
│   └── get_cart.php        # Retrieve & calculate cart totals
├── index.php               # Homepage with featured products
├── shop.php                # Shop page with categories & filters
├── single.php              # Product detail page
├── cart.php                # Shopping cart page
├── cart_actions.php        # Cart action processor
├── css/                    # Stylesheets
├── js/                     # JavaScript files
├── img/                    # Images
└── (old HTML files remain as backup)
```

---

## 🔧 Installation & Setup

### Step 1: Database Configuration

1. **Create/Import Database**
   - Import `u701659873_distributor.sql` into your MySQL server
   - Database name: `u701659873_distributor`

2. **Update Database Credentials** in `config/db.php`:
   ```php
   define('DB_HOST', 'localhost');   // Your database host
   define('DB_USER', 'root');        // Your DB username
   define('DB_PASS', '');            // Your DB password
   define('DB_NAME', 'u701659873_distributor');
   ```

### Step 2: Directory Permissions

```bash
# Make sure these directories are writable (Linux/Mac)
chmod 755 .
chmod 755 css js img
```

### Step 3: Start Local Server

```bash
# Using PHP's built-in server
cd /path/to/Electro-1.0.0
php -S localhost:8000
```

Then visit: `http://localhost:8000`

---

## 📦 Database Schema Overview

### Key Tables:

**`products`** - Product catalog
- `id`, `name`, `slug`, `description`, `price`, `quantity`, `image_url`, `category_id`, `featured`

**`categories`** - Product categories  
- `id`, `name`, `slug`, `description`, `is_active`

**`cart`** - Shopping cart items
- `id`, `user_id`, `session_id`, `product_id`, `quantity`, `added_at`

**`users`** - User accounts
- `id`, `email`, `password`, `full_name`, `phone`, `address`, etc.

**`orders`** - Customer orders
- `id`, `order_id`, `user_id`, `email`, `shipping_address`, `total_amount`, `order_status`

**`order_items`** - Items in each order
- `id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`

---

## 🚀 Key Features Implemented

### 1. **Product Management**
- ✅ Display all active products from database
- ✅ Filter products by category
- ✅ Search products by name/description
- ✅ Display featured products on homepage
- ✅ Pagination support (12 products per page)
- ✅ Product detail page with related products

### 2. **Shopping Cart**
- ✅ Add products to cart
- ✅ Update item quantities
- ✅ Remove items from cart
- ✅ Display cart summary with totals
- ✅ Calculate tax & shipping (configurable)
- ✅ Cart persists using sessions (for guests) or user account (for logged-in users)
- ✅ Stock validation before adding to cart

### 3. **Session Management**
- ✅ Flash messages for user feedback
- ✅ Session-based cart for anonymous users
- ✅ User migration: cart transfers to user account on login

---

## 📝 Code Examples

### Adding a Product to Cart

```html
<form method="post" action="cart_actions.php">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
    <input type="number" name="quantity" value="1" min="1">
    <button type="submit">Add to Cart</button>
</form>
```

### Displaying Product Grid

```php
<?php
require_once 'config/db.php';
require_once 'product/get_products.php';

$result = get_products(['category' => 1], 12);
$products = $result['products'];

foreach ($products as $product) {
    echo $product['name'] . ' - ' . format_price($product['price']);
}
?>
```

### Calculating Cart Totals

```php
<?php
require_once 'cart/get_cart.php';

$cart = get_cart_summary($tax_rate = 0.08, $shipping = 0);
echo 'Total: ' . format_price($cart['totals']['total']);
?>
```

---

## 🔄 Page Flow

```
Visitor arrives
    ↓
index.php (Featured products + Categories)
    ↓
shop.php (All products with filters)
    ↓
single.php (Product details)
    ↓
cart_actions.php (Add to cart)
    ↓
cart.php (Review cart + checkout)
    ↓
checkout.php (Coming soon)
```

---

## 🛠️ Helper Functions Reference

### Formatting & Utilities

```php
// config/helpers.php

format_price($price);           // Returns formatted price ("$99.99")
sanitize($input);               // HTML escape input
is_logged_in();                 // Check if user is authenticated
current_user();                 // Get current user data
get_cart_count();               // Get total items in cart
add_message($msg, 'success');   // Add flash message
get_messages();                 // Retrieve & clear flash messages
redirect($url);                 // Redirect to URL
generate_order_id();            // Create unique order ID
format_date($date);             // Format date for display
```

---

## 📊 Product Fetching Functions

### product/get_products.php

```php
get_products($filters, $per_page);        // Get products with pagination
get_product_by_id($product_id);           // Fetch single product
get_products_by_category($cat_id, $limit);// Get products by category
get_categories();                          // Get all categories
get_featured_products($limit);            // Get featured products
```

---

## 🛒 Cart Functions

### cart/cart_handler.php

```php
add_to_cart($product_id, $quantity);      // Add product to cart
update_cart_quantity($cart_id, $qty);     // Update cart item qty
remove_from_cart($cart_id);               // Remove item from cart
clear_cart();                             // Clear entire cart
get_cart_count();                         // Get total items
```

### cart/get_cart.php

```php
get_cart_items();                         // Retrieve cart items with details
calculate_cart_totals($items);            // Calculate subtotal, tax, total
get_cart_summary($tax_rate, $shipping);   // Get complete cart data
is_cart_empty();                          // Check if cart is empty
validate_cart_stock($items);              // Verify stock availability
migrate_cart_to_user($user_id);          // Move session cart to user (on login)
```

---

## ✅ Configuration

### Tax & Shipping Rates

Edit in `cart.php` and `cart_actions.php`:

```php
// Default: 8% tax, $0 shipping
$cart_summary = get_cart_summary($tax_rate = 0.08, $shipping = 0);

// Example: 10% tax, $5 shipping
$cart_summary = get_cart_summary(0.10, 5.00);
```

---

## 🧪 Testing the System

### 1. Test Product Display
- Visit `http://localhost:8000`
- Check homepage loads with featured products
- Verify categories display

### 2. Test Shopping
- Click "Add to Cart" button
- Verify success message appears
- Check cart icon updates

### 3. Test Cart Operations
- Visit `/cart.php`
- Update quantities
- Remove items
- Verify totals calculate correctly

### 4. Test Filtering
- On `shop.php`, select a category
- Try search functionality
- Test pagination

---

## 🐛 Troubleshooting

### "Database connection failed"
- Check credentials in `config/db.php`
- Verify MySQL server is running
- Confirm database exists and is imported

### "No products showing"
- Check if `products` table has data (expected: 6 products)
- Verify `is_active = 1` for products
- Check `image_url` fields (can be NULL)

### "Cart not persisting"
- Ensure sessions are enabled in PHP
- Check browser cookies are enabled
- Verify `session_start()` is called in `config/helpers.php`

### "Out of stock not working"
- Verify `products.quantity` column has values
- Check stock validation in `cart/cart_handler.php`

---

## 🔐 Security Notes

✅ **Already Implemented:**
- SQL Injection prevention (using prepared statements with PDO)
- XSS prevention (using `htmlspecialchars()` for output)
- Input sanitization with `sanitize()` function

⚠️ **Still Needed (for production):**
- Password hashing (use `password_hash()` for user registration)
- CSRF tokens for forms
- SSL/HTTPS enforcement
- Input validation rules
- Rate limiting for checkout

---

## 📅 Next Steps

1. **Checkout Page** - Create `checkout.php` to finalize orders
2. **Order Processing** - Implement `orders/create.php` to save orders to database
3. **User Authentication** - Connect login/register with cart migration
4. **Email Notifications** - Send order confirmations
5. **Admin Dashboard** - Product & order management

---

## 📧 Support

For questions about the setup, refer to function documentation in code files or create an issue in the repository.

**Happy Coding! 🚀**