<?php
require_once "db.php";
function runQuery($pdo, $sql, $params = [], $fetch = false)
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($fetch === true) {
            return $stmt->fetchAll(); // for SELECT multiple rows
        }

        return $stmt; // return statement for flexibility
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

//OLTP TABLES
// USERS
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    profile_pic VARCHAR(255) NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

// CATEGORIES
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS categories (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;
");

runQuery($pdo, "
    INSERT INTO categories (category_id, category_name) VALUES
    (1, 'GPU'),
    (2, 'CPU'),
    (3, 'Motherboard'),
    (4, 'RAM'),
    (5, 'Storage'),
    (6, 'PSU'),
    (7, 'Cooling'),
    (8, 'Accessories'),
    (9, 'Case')
");

// PRODUCTS
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS products (
    product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;
");

// ADDRESSES
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS addresses (
    address_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address_line TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_addresses_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;
");

// CART
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_cart_product
        FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    UNIQUE (user_id, product_id)
) ENGINE=InnoDB;
");

// ORDERS
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS orders (
    order_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    address_id INT UNSIGNED NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL CHECK (total_amount >= 0),
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') 
        NOT NULL DEFAULT 'pending',

    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_orders_address
        FOREIGN KEY (address_id)
        REFERENCES addresses(address_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;
");

// ORDER ITEMS
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    subtotal DECIMAL(10,2) NOT NULL CHECK (subtotal >= 0),

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;
");

// PAYMENTS
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    payment_method ENUM('COD', 'GCash', 'Maya', 'Credit Card') NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount >= 0),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;
");

// REVIEWS
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_reviews_product
        FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    UNIQUE (user_id, product_id)
) ENGINE=InnoDB;
");

// NOTIFICATIONS
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    role_target ENUM('admin', 'user') NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;
");


// DIM PRODUCT
runQuery($pdo, "
CREATE TABLE IF NOT EXISTS dim_product (
    dim_product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source_product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    product_status ENUM('active', 'inactive') NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (source_product_id, product_name, category_name, product_status)
) ENGINE=InnoDB;
");



// DIM CUSTOMER

runQuery($pdo, "
CREATE TABLE IF NOT EXISTS dim_customer (
    dim_customer_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source_user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NULL,
    city VARCHAR(100) NULL,
    province VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (source_user_id, full_name, gender, city, province)
) ENGINE=InnoDB;
");



// DIM TIME

runQuery($pdo, "
CREATE TABLE IF NOT EXISTS dim_time (
    dim_time_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    full_date DATE NOT NULL,
    day INT UNSIGNED NOT NULL,
    month INT UNSIGNED NOT NULL,
    month_name VARCHAR(20) NOT NULL,
    quarter INT UNSIGNED NOT NULL,
    year INT UNSIGNED NOT NULL,

    UNIQUE (full_date)
) ENGINE=InnoDB;
");


// DIM PAYMENT

runQuery($pdo, "
CREATE TABLE IF NOT EXISTS dim_payment (
    dim_payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    payment_method ENUM('COD', 'GCash', 'Maya', 'Credit Card') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL,

    UNIQUE (payment_method, payment_status)
) ENGINE=InnoDB;
");



// DIM ORDER STATUS

runQuery($pdo, "
CREATE TABLE IF NOT EXISTS dim_order_status (
    dim_status_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL,

    UNIQUE (order_status)
) ENGINE=InnoDB;
");



// FACT SALES

runQuery($pdo, "
CREATE TABLE IF NOT EXISTS fact_sales (
    fact_sales_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    dim_product_id INT UNSIGNED NOT NULL,
    dim_customer_id INT UNSIGNED NOT NULL,
    dim_time_id INT UNSIGNED NOT NULL,
    dim_payment_id INT UNSIGNED NOT NULL,
    dim_status_id INT UNSIGNED NOT NULL,

    source_order_id INT UNSIGNED NOT NULL,
    source_order_item_id INT UNSIGNED NOT NULL,
    source_product_id INT UNSIGNED NOT NULL,
    source_user_id INT UNSIGNED NOT NULL,

    quantity_sold INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price >= 0),
    subtotal DECIMAL(10,2) NOT NULL CHECK (subtotal >= 0),
    total_amount DECIMAL(10,2) NOT NULL CHECK (total_amount >= 0),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_fact_sales_product
        FOREIGN KEY (dim_product_id)
        REFERENCES dim_product(dim_product_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_fact_sales_customer
        FOREIGN KEY (dim_customer_id)
        REFERENCES dim_customer(dim_customer_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_fact_sales_time
        FOREIGN KEY (dim_time_id)
        REFERENCES dim_time(dim_time_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_fact_sales_payment
        FOREIGN KEY (dim_payment_id)
        REFERENCES dim_payment(dim_payment_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_fact_sales_status
        FOREIGN KEY (dim_status_id)
        REFERENCES dim_order_status(dim_status_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    UNIQUE (source_order_item_id)
) ENGINE=InnoDB;
");


echo "Database and tables created successfully.";



