# Inventory Pro — Advanced Inventory Management System

A complete PHP + MySQL inventory management starter system for XAMPP.

## Included modules
- Secure login/session system
- Dashboard with live stock/value/sales/purchase metrics
- Product master with SKU, barcode, category, supplier, purchase/sale price, tax, reorder level
- Categories
- Suppliers
- Customers
- Purchase entry with stock increase
- Sales entry with stock decrease
- Automatic stock movement ledger
- Low-stock and out-of-stock alerts
- Expense management
- Reports: stock valuation, sales, purchases, profit snapshot, movement history
- User management
- Company settings
- Responsive admin UI
- CSRF protection
- Prepared SQL statements
- Automatic database transaction handling for sales/purchases

## Requirements
- XAMPP / Apache
- PHP 8.1+
- MySQL/MariaDB
- Browser

## Installation
1. Copy the `inventory_pro` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from XAMPP.
3. Open phpMyAdmin and import `database/setup.sql`.
4. Open:
   `http://localhost/inventory_pro/`
5. Default login:
   Email: `admin@inventory.local`
   Password: `admin123`

Change the password after first login.

## Database configuration
Edit:
`config/database.php`

Default:
- host: 127.0.0.1
- database: inventory_pro
- user: root
- password: empty

## Important
This project is designed as a strong local/XAMPP foundation. For production deployment, enable HTTPS, use a strong database password, configure backups, disable error display, and add server-side operational controls appropriate for your business.
