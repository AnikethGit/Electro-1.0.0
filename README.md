# Electro 1.0.0 - E-Commerce Platform

A full-featured, modern e-commerce platform built with PHP, MySQL, and vanilla JavaScript. Electro provides a complete shopping experience with product catalog, shopping cart, order management, and user authentication.

## Features

### Core E-Commerce Features
- **Product Catalog**: Browse products with filtering, search, and sorting
- **Shopping Cart**: Add/remove items, update quantities, persistent cart storage
- **Order Processing**: Complete checkout flow with order confirmation
- **Order Management**: Track orders, view order history and details
- **Payment Methods**: Support for COD, Credit Card, and Bank Transfer
- **User Accounts**: Registration, login, profile management
- **Responsive Design**: Mobile-friendly interface across all pages

### Product Features
- Product categories and filtering
- Product search functionality
- Detailed product pages with images
- Stock management and availability tracking
- Bestseller/featured products showcase
- Product reviews and ratings (expandable)

### Cart & Checkout
- Real-time cart updates
- Tax calculation (configurable)
- Shipping cost calculation
- Order summary with itemized breakdown
- Guest checkout option
- Customer information collection
- Shipping address management

### User Features
- User registration and email verification
- Secure login/logout
- Profile management
- Order history and tracking
- Wishlist (expandable)
- Account settings

### Admin Features (Expandable)
- Order management dashboard
- Product inventory management
- Contact form submissions review
- User management

## Project Structure

```
Electro-1.0.0/
├── index.html              # Home page
├── index.php               # Home page (PHP)
├── shop.html               # Product listing
├── shop.php                # Shop logic
├── single.html             # Product detail page
├── single.php              # Product detail logic
├── bestseller.html         # Best sellers page
├── cart.html               # Shopping cart page
├── cart.php                # Cart logic
├── checkout.php            # Checkout page (NEW)
├── contact.html            # Contact form
├── contact_handler.php     # Contact form handler (NEW)
├── 404.html                # Error page
│
├── config/
│   ├── config.php          # Application configuration
│   ├── database.php        # Database configuration
│   ├── db.php              # PDO database connection
│   └── helpers.php         # Utility functions
│
├── api/
│   └── cart_api.php        # Cart API endpoints
│
├── cart/
│   ├── add_to_cart.php     # Add to cart handler
│   ├── cart_handler.php    # Cart business logic
│   └── get_cart.php        # Retrieve cart data
│
├── product/
│   └── get_products.php    # Product retrieval
│
├── orders/
│   ├── create.php          # Order creation handler (NEW)
│   └── thank_you.php       # Order confirmation page (NEW)
│
├── user/
│   └── profile.php         # User profile page (NEW)
│
├── css/
│   └── style.css           # Global styles
│
├── js/
│   └── main.js             # Global scripts
│
├── img/
│   └── [product images]
│
├── .htaccess               # Apache configuration (NEW)
├── .gitattributes          # Git settings
└── README.md               # This file
```

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- Composer (optional, for future dependencies)

### Step 1: Clone Repository
```bash
git clone https://github.com/AnikethGit/Electro-1.0.0.git
cd Electro-1.0.0
```

### Step 2: Configure Database
1. Create a new MySQL database:
```sql
CREATE DATABASE electro;
```

2. Update database credentials in `config/db.php`:
```php
$host = 'localhost';
$db_name = 'electro';
$user = 'root';
$password = 'your_password';
```

### Step 3: Import Database Schema
Run the provided SQL schema file to create tables:
```bash
mysql -u root -p electro < database.sql
```

### Step 4: Set Permissions
Ensure proper file permissions:
```bash
chmod 755 -R .
chmod 644 .htaccess
```

### Step 5: Configure Apache
Enable `.htaccess` in your Apache virtual host:
```apache
<Directory /path/to/electro>
    AllowOverride All
</Directory>
```

### Step 6: Access the Application
Visit `http://localhost/Electro-1.0.0` in your browser

## Key Files & Their Purposes

### Configuration Files
- **config/db.php** - Database connection using PDO
- **config/database.php** - Alternate database configuration
- **config/config.php** - Application-wide settings
- **config/helpers.php** - Utility functions for common operations

### API & Business Logic
- **api/cart_api.php** - RESTful API for cart operations
- **cart/cart_handler.php** - Shopping cart logic and calculations
- **product/get_products.php** - Product retrieval and filtering
- **orders/create.php** - Order processing with transaction support (NEW)

### Frontend Pages
- **checkout.php** - Complete checkout form with payment options (NEW)
- **orders/thank_you.php** - Order confirmation and details (NEW)
- **user/profile.php** - User dashboard with order history (NEW)
- **contact_handler.php** - Contact form submission processor (NEW)

## Database Schema

### Key Tables
- **users** - User account information
- **products** - Product catalog
- **cart** - Shopping cart items (session-based or user-based)
- **orders** - Customer orders (NEW)
- **order_items** - Order line items (NEW)
- **contacts** - Contact form submissions (NEW)

For detailed schema, see `database.sql`

## Configuration

### Checkout Settings (in checkout.php)
```php
$tax_rate = 0.08;        // 8% tax
$shipping_cost = 5.00;   // $5 flat rate
```

### Payment Methods
Configurable in checkout form:
- Cash on Delivery (COD)
- Credit/Debit Card
- Bank Transfer

## Helpers & Utilities

Common helper functions available in `config/helpers.php`:

```php
// User authentication
is_logged_in()           // Check if user is logged in
current_user()           // Get current user data
redirect($url)          // Redirect to URL

// Data sanitization
sanitize($input)        // Sanitize user input
is_valid_email($email)  // Validate email

// Formatting
format_price($amount)   // Format currency
format_date($date)      // Format date

// Cart operations
get_cart_summary()      // Get cart totals
get_cart_count()        // Get item count
is_cart_empty()        // Check if cart is empty

// Messages
add_message($text, $type)   // Add message to session
get_messages()              // Retrieve and clear messages
```

## Security Features

### Implemented
- ✅ **SQL Injection Prevention** - PDO prepared statements
- ✅ **XSS Protection** - HTML entity encoding with `htmlspecialchars()`
- ✅ **CSRF Protection** - Session-based requests
- ✅ **Password Security** - `password_hash()` and `password_verify()`
- ✅ **Input Validation** - Server-side validation for all inputs
- ✅ **Apache Security Headers** - Set via `.htaccess` (NEW)
- ✅ **Sensitive File Protection** - Config and database files excluded

### Recommended Additions
- [ ] SSL/TLS certificate (HTTPS)
- [ ] Two-factor authentication
- [ ] Rate limiting on sensitive endpoints
- [ ] CAPTCHA on contact form
- [ ] API rate limiting
- [ ] Content Security Policy (CSP) headers

## API Endpoints

### Cart API (`/api/cart_api.php`)

**Add to Cart**
```
POST /api/cart_api.php
Body: {
  action: 'add',
  product_id: 1,
  quantity: 2
}
```

**Get Cart**
```
GET /api/cart_api.php?action=get
```

**Remove from Cart**
```
POST /api/cart_api.php
Body: {
  action: 'remove',
  cart_id: 5
}
```

**Update Quantity**
```
POST /api/cart_api.php
Body: {
  action: 'update',
  cart_id: 5,
  quantity: 3
}
```

## Checkout Flow

1. **Add Items to Cart** - User adds products from shop or detail pages
2. **Review Cart** - User views cart.html with summary
3. **Proceed to Checkout** - User clicks "Checkout" button
4. **Enter Information** - Fill customer, shipping, and payment details
5. **Place Order** - Submit checkout form to `/orders/create.php`
6. **Order Processing** - Database transaction creates order and items
7. **Confirmation** - Redirect to `/orders/thank_you.php` with order details
8. **Email Notification** - Confirmation email sent to customer

## Contact Form Flow

1. **User submits form** - From contact.html
2. **Server validation** - `contact_handler.php` validates input
3. **Database storage** - Message saved to contacts table
4. **Email notification** - Admin receives submission email
5. **User confirmation** - Confirmation email sent to user
6. **Response** - JSON response with success/error status

## Common Customizations

### Change Tax Rate
In `checkout.php` and cart pages:
```php
$tax_rate = 0.10;  // Change to 10%
```

### Modify Shipping Cost
```php
$shipping_cost = 10.00;  // Change to $10
```

### Update Contact Email
In `contact_handler.php`:
```php
$admin_email = 'your-email@domain.com';
```

### Add Payment Methods
Modify the payment methods section in `checkout.php`

### Customize Styling
Edit `css/style.css` or add custom stylesheets

## Troubleshooting

### "Cannot find database" error
- Check database name in `config/db.php`
- Verify MySQL is running
- Confirm database exists: `SHOW DATABASES;`

### "404 Not Found" errors
- Ensure `.htaccess` is present and enabled
- Check Apache `AllowOverride All` setting
- Verify mod_rewrite is enabled: `a2enmod rewrite`

### Cart not persisting
- Check `session.save_path` in php.ini
- Verify cookies are enabled in browser
- Check browser privacy settings

### Emails not sending
- Configure SMTP settings in php.ini or server
- Check server mail function is enabled
- Verify recipient email is correct

### Database locks/transactions
- Check for long-running processes
- Review error logs for transaction deadlocks
- Ensure proper database cleanup

## Performance Optimization

### Implemented
- ✅ Gzip compression via `.htaccess`
- ✅ Browser caching headers
- ✅ CSS/JS minification ready
- ✅ Database connection pooling (PDO)

### Recommendations
- [ ] Implement query result caching (Redis/Memcached)
- [ ] Add database indexing on frequently queried columns
- [ ] Implement lazy loading for images
- [ ] Use CDN for static assets
- [ ] Implement database query logging
- [ ] Profile and optimize slow queries

## Future Enhancements

### Short Term
- [ ] Email verification for account registration
- [ ] Password reset functionality
- [ ] Advanced product filtering
- [ ] Product reviews and ratings system
- [ ] Wishlist feature
- [ ] Order tracking with real-time updates
- [ ] Search functionality improvements

### Medium Term
- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Admin dashboard
- [ ] Inventory management system
- [ ] Customer support chat
- [ ] Email marketing integration
- [ ] Analytics dashboard

### Long Term
- [ ] Mobile app
- [ ] Multi-vendor marketplace
- [ ] AI-powered recommendations
- [ ] Subscription products
- [ ] Advanced reporting system
- [ ] Multi-language support

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues, questions, or suggestions:
- Email: anikethsahu580@gmail.com
- GitHub Issues: [Report Issues](https://github.com/AnikethGit/Electro-1.0.0/issues)

## Version History

### v1.0.0 (Current)
- ✅ Core e-commerce functionality
- ✅ Product catalog and shopping cart
- ✅ Checkout and order processing (NEW)
- ✅ User authentication and profiles (NEW)
- ✅ Contact form handling (NEW)
- ✅ Security headers and Apache configuration (NEW)
- ✅ Responsive design

## Credits

Developed by **Aniketh Sahu**

---

**Last Updated**: January 7, 2026  
**Status**: ✅ Production Ready
