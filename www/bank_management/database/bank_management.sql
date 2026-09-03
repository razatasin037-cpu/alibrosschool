CREATE DATABASE IF NOT EXISTS bank_management;
USE bank_management;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') DEFAULT 'customer',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    plan_code VARCHAR(50) NOT NULL UNIQUE,
    interest_rate DECIMAL(5,2) DEFAULT 0,
    duration_months INT NOT NULL,
    min_amount DECIMAL(12,2) DEFAULT 0,
    description TEXT,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    customer_code VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(120),
    dob DATE,
    gender VARCHAR(20),
    address TEXT,
    city VARCHAR(80),
    aadhaar VARCHAR(30),
    account_number VARCHAR(30) UNIQUE,
    plan_id INT DEFAULT NULL,
    opening_balance DECIMAL(12,2) DEFAULT 0,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
);

INSERT INTO plans (plan_name, plan_code, interest_rate, duration_months, min_amount, description)
VALUES
('Savings Account', 'SAV-01', 4.00, 12, 1000, 'Basic savings plan'),
('Premium Savings', 'SAV-02', 6.50, 24, 5000, 'Premium savings plan'),
('Fixed Deposit', 'FD-01', 7.25, 36, 10000, 'Fixed deposit plan')
ON DUPLICATE KEY UPDATE plan_name = VALUES(plan_name);

INSERT INTO users (full_name, email, phone, password, role)
VALUES ('Admin User', 'admin@bank.com', '9999999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9yKf5gY2j8fXqZJq', 'admin')
ON DUPLICATE KEY UPDATE email=email;
