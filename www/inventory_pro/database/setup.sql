CREATE DATABASE IF NOT EXISTS inventory_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_pro;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS purchase_items;
DROP TABLE IF EXISTS purchases;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;

SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    address TEXT NULL,
    gstin VARCHAR(40) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    address TEXT NULL,
    gstin VARCHAR(40) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(80) NOT NULL UNIQUE,
    barcode VARCHAR(100) NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    category_id INT UNSIGNED NULL,
    supplier_id INT UNSIGNED NULL,
    unit VARCHAR(30) NOT NULL DEFAULT 'pcs',
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    stock_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
    reorder_level DECIMAL(12,2) NOT NULL DEFAULT 5,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_product_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_product_name(name),
    INDEX idx_product_stock(stock_qty)
) ENGINE=InnoDB;

CREATE TABLE purchases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(80) NOT NULL UNIQUE,
    supplier_id INT UNSIGNED NULL,
    purchase_date DATE NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_status ENUM('paid','partial','due') NOT NULL DEFAULT 'paid',
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_purchase_date(purchase_date)
) ENGINE=InnoDB;

CREATE TABLE purchase_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    qty DECIMAL(12,2) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY(purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE sales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(80) NOT NULL UNIQUE,
    customer_id INT UNSIGNED NULL,
    sale_date DATE NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_status ENUM('paid','partial','due') NOT NULL DEFAULT 'paid',
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sale_date(sale_date)
) ENGINE=InnoDB;

CREATE TABLE sale_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    qty DECIMAL(12,2) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    movement_type ENUM('opening','purchase','sale','adjustment') NOT NULL,
    reference_type VARCHAR(40) NULL,
    reference_id INT UNSIGNED NULL,
    qty_in DECIMAL(12,2) NOT NULL DEFAULT 0,
    qty_out DECIMAL(12,2) NOT NULL DEFAULT 0,
    balance_after DECIMAL(12,2) NOT NULL DEFAULT 0,
    note VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_stock_product_date(product_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    category VARCHAR(100) NULL,
    amount DECIMAL(12,2) NOT NULL,
    expense_date DATE NOT NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(180) NOT NULL DEFAULT 'Inventory Pro',
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    address TEXT NULL,
    gstin VARCHAR(40) NULL,
    currency VARCHAR(10) NOT NULL DEFAULT '₹',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO settings(company_name, phone, email, address, currency)
VALUES('Inventory Pro', '', '', '', '₹');

INSERT INTO users(name,email,password,role)
VALUES('Administrator','admin@inventory.local',
'$2y$12$OIfRQkwtWK8fLNG.SCyTvuXnu26BD68x6iy1ogdEEfW98hMXO4ztO',
'admin');

INSERT INTO categories(name,description) VALUES
('General','General inventory items'),
('Electronics','Electronic items'),
('Office','Office supplies'),
('Grocery','Grocery and consumables');

INSERT INTO suppliers(name,phone,email,address,gstin) VALUES
('Demo Supplier','9000000000','supplier@example.com','Demo Address','');

INSERT INTO customers(name,phone,email,address) VALUES
('Walk-in Customer','', '', '');

INSERT INTO products(sku,barcode,name,category_id,supplier_id,unit,purchase_price,sale_price,tax_rate,stock_qty,reorder_level)
VALUES
('SKU-1001','890000000001','Demo Keyboard',2,1,'pcs',700,999,18,25,5),
('SKU-1002','890000000002','Demo Mouse',2,1,'pcs',300,499,18,40,8),
('SKU-1003','890000000003','A4 Paper Pack',3,1,'pack',180,250,5,12,4);

INSERT INTO stock_movements(product_id,movement_type,reference_type,qty_in,balance_after,note,created_by)
SELECT id,'opening','seed',stock_qty,stock_qty,'Opening stock',1 FROM products;
