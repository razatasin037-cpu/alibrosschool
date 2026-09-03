BANK MANAGEMENT SYSTEM

Requirements:
- Laragon or XAMPP
- PHP 8+
- MySQL/MariaDB

SETUP:
1. Extract this folder into:
   C:\laragon\www\bank_management

2. Start Apache and MySQL in Laragon.

3. Open phpMyAdmin or HeidiSQL.

4. Import:
   database/bank_management.sql

   This automatically creates the database:
   bank_management

5. Open:
   http://localhost/bank_management/

FEATURES:
- Dashboard
- Total Customers
- Active Customers
- Active Plans
- Opening Balance
- Add Customer
- Customer List
- Plan Management
- User Profile
- MySQL database included

IMPORTANT:
The project uses:
Host: localhost
Username: root
Password: empty
Database: bank_management

If your MySQL root password is different, edit:
config/db.php
