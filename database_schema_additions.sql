-- Electro 1.0.0 - Database Schema Additions
-- This file contains new tables required for complete e-commerce functionality
-- Run this after creating the base electro database

-- ============================================
-- ORDERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique order identifier for customer',
  `user_id` INT NULL COMMENT 'Reference to users table, NULL for guest checkout',
  `email` VARCHAR(255) NOT NULL COMMENT 'Customer email address',
  `phone` VARCHAR(20) NOT NULL COMMENT 'Customer phone number',
  `shipping_address` TEXT NOT NULL COMMENT 'Street address for shipping',
  `shipping_city` VARCHAR(100) NOT NULL COMMENT 'City for shipping',
  `shipping_state` VARCHAR(50) NOT NULL COMMENT 'State/Province for shipping',
  `shipping_postal_code` VARCHAR(10) NOT NULL COMMENT 'Postal code for shipping',
  `shipping_country` VARCHAR(50) NOT NULL COMMENT 'Country for shipping',
  `total_amount` DECIMAL(10, 2) NOT NULL COMMENT 'Total order amount including tax and shipping',
  `payment_method` VARCHAR(50) NOT NULL COMMENT 'Payment method selected (COD, Credit Card, Bank Transfer)',
  `order_status` VARCHAR(20) NOT NULL DEFAULT 'Pending' COMMENT 'Order status (Pending, Processing, Shipped, Delivered, Cancelled)',
  `notes` TEXT NULL COMMENT 'Optional customer notes or special instructions',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Order creation timestamp',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
  
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`order_status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores customer orders';

-- ============================================
-- ORDER_ITEMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL COMMENT 'Reference to orders table',
  `product_id` INT NOT NULL COMMENT 'Reference to products table',
  `product_name` VARCHAR(255) NOT NULL COMMENT 'Product name snapshot at time of order',
  `quantity` INT NOT NULL COMMENT 'Quantity ordered',
  `price` DECIMAL(10, 2) NOT NULL COMMENT 'Unit price at time of order',
  `subtotal` DECIMAL(10, 2) NOT NULL COMMENT 'Quantity * Price',
  
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores individual items in each order';

-- ============================================
-- CONTACTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL COMMENT 'Visitor name',
  `email` VARCHAR(255) NOT NULL COMMENT 'Visitor email',
  `phone` VARCHAR(20) NULL COMMENT 'Visitor phone (optional)',
  `subject` VARCHAR(255) NOT NULL COMMENT 'Message subject/topic',
  `message` TEXT NOT NULL COMMENT 'Message content',
  `status` VARCHAR(20) NOT NULL DEFAULT 'New' COMMENT 'Status (New, Read, Replied, Resolved)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Submission timestamp',
  `replied_at` TIMESTAMP NULL COMMENT 'Reply timestamp',
  
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores contact form submissions';

-- ============================================
-- USEFUL INDEXES FOR EXISTING TABLES
-- ============================================

-- Add indexes to improve query performance on commonly queried columns
-- Run these only if these tables exist and don't have these indexes

-- For users table (if exists)
ALTER TABLE `users` ADD INDEX `idx_email` (`email`);
ALTER TABLE `users` ADD INDEX `idx_created_at` (`created_at`);

-- For products table (if exists)
ALTER TABLE `products` ADD INDEX `idx_category` (`category`);
ALTER TABLE `products` ADD INDEX `idx_quantity` (`quantity`);
ALTER TABLE `products` ADD INDEX `idx_price` (`price`);

-- For cart table (if exists)
ALTER TABLE `cart` ADD INDEX `idx_user_id` (`user_id`);
ALTER TABLE `cart` ADD INDEX `idx_session_id` (`session_id`);

-- ============================================
-- SAMPLE DATA (OPTIONAL)
-- ============================================

-- Insert sample order for testing (uncomment to use)
-- INSERT INTO orders (order_id, user_id, email, phone, shipping_address, shipping_city, shipping_state, shipping_postal_code, shipping_country, total_amount, payment_method, order_status, notes)
-- VALUES ('ORD-20260107-001', 1, 'customer@example.com', '+1-555-0123', '123 Main St', 'New York', 'NY', '10001', 'USA', 1250.50, 'COD', 'Pending', 'Please deliver after 5 PM');

-- INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
-- VALUES (1, 1, 'Sample Product', 2, 99.99, 199.98);

-- ============================================
-- TABLE VERIFICATION QUERIES
-- ============================================

-- Verify tables were created:
-- SHOW TABLES LIKE 'orders';
-- SHOW TABLES LIKE 'order_items';
-- SHOW TABLES LIKE 'contacts';

-- Show table structure:
-- DESCRIBE orders;
-- DESCRIBE order_items;
-- DESCRIBE contacts;

-- ============================================
-- CLEANUP QUERIES (USE WITH CAUTION)
-- ============================================

-- To drop tables if needed (WARNING: Deletes all data):
-- DROP TABLE IF EXISTS order_items;
-- DROP TABLE IF EXISTS orders;
-- DROP TABLE IF EXISTS contacts;