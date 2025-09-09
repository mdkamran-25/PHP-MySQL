-- Product Catalog Database Schema
-- Creates products table with sample data

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text,
  `image_url` varchar(500),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_price` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `products` (`name`, `price`, `category`, `description`, `image_url`) VALUES
('iPhone 15 Pro', 999.99, 'Electronics', 'Latest Apple iPhone with advanced features and camera system', 'https://via.placeholder.com/300x300/007bff/fff?text=iPhone+15'),
('Samsung Galaxy S24', 899.99, 'Electronics', 'Flagship Android smartphone with AI capabilities', 'https://via.placeholder.com/300x300/28a745/fff?text=Galaxy+S24'),
('MacBook Air M3', 1299.99, 'Computers', '13-inch laptop with Apple M3 chip and all-day battery', 'https://via.placeholder.com/300x300/6c757d/fff?text=MacBook+Air'),
('Dell XPS 13', 1199.99, 'Computers', 'Premium ultrabook with InfinityEdge display', 'https://via.placeholder.com/300x300/dc3545/fff?text=Dell+XPS'),
('Sony WH-1000XM5', 399.99, 'Audio', 'Industry-leading noise canceling wireless headphones', 'https://via.placeholder.com/300x300/ffc107/000?text=Sony+WH'),
('AirPods Pro 2', 249.99, 'Audio', 'Apple wireless earbuds with active noise cancellation', 'https://via.placeholder.com/300x300/17a2b8/fff?text=AirPods+Pro'),
('Nike Air Max 270', 150.00, 'Fashion', 'Comfortable running shoes with Max Air cushioning', 'https://via.placeholder.com/300x300/fd7e14/fff?text=Nike+Air'),
('Adidas Ultraboost 22', 180.00, 'Fashion', 'High-performance running shoes with Boost technology', 'https://via.placeholder.com/300x300/6f42c1/fff?text=Ultraboost'),
('Instant Pot Duo 7-in-1', 99.99, 'Home', 'Multi-functional pressure cooker and slow cooker', 'https://via.placeholder.com/300x300/20c997/fff?text=Instant+Pot'),
('Dyson V15 Detect', 749.99, 'Home', 'Cordless vacuum with laser dust detection', 'https://via.placeholder.com/300x300/e83e8c/fff?text=Dyson+V15'),
('The Great Gatsby', 12.99, 'Books', 'Classic American novel by F. Scott Fitzgerald', 'https://via.placeholder.com/300x300/6610f2/fff?text=Gatsby'),
('Atomic Habits', 18.99, 'Books', 'Life-changing guide to building good habits by James Clear', 'https://via.placeholder.com/300x300/0d6efd/fff?text=Atomic+Habits');