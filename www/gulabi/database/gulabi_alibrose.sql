CREATE DATABASE IF NOT EXISTS gulabi_alibrose CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gulabi_alibrose;

CREATE TABLE customers(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) NOT NULL,
father_name VARCHAR(150) NULL,
mobile VARCHAR(30) NULL,
aadhaar VARCHAR(30) NULL,
address TEXT NULL,
opening_date DATE NOT NULL,
plan ENUM('Weekly','Monthly') NOT NULL,
amount DECIMAL(12,2) NOT NULL DEFAULT 0,
total_installments INT NOT NULL DEFAULT 0,
paid_installments INT NOT NULL DEFAULT 0, absent_installments INT NOT NULL DEFAULT 0,
next_due_date DATE NULL,
status ENUM('Active','Completed','Closed') NOT NULL DEFAULT 'Active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
INDEX(plan,status),
INDEX(next_due_date)
) ENGINE=InnoDB;

CREATE TABLE installment_payments(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
customer_id INT UNSIGNED NOT NULL,
installment_no INT NOT NULL,
amount DECIMAL(12,2) NOT NULL,
payment_date DATE NOT NULL,
due_date DATE NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
UNIQUE KEY customer_installment(customer_id,installment_no),
INDEX(payment_date),
CONSTRAINT fk_payment_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE accounts(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
customer_id INT UNSIGNED NULL,
account_number VARCHAR(60) NOT NULL UNIQUE,
account_type VARCHAR(60) NOT NULL DEFAULT 'Savings',
balance DECIMAL(14,2) NOT NULL DEFAULT 0,
status ENUM('Active','Closed') NOT NULL DEFAULT 'Active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT fk_account_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;
