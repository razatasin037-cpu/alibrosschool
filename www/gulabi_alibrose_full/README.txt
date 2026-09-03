GULABI ALIBROSE LOGIN SYSTEM

Files:
- login.php
- auth.php
- logout.php
- create_admin.php
- database_login.sql

SETUP:

1. Make sure admin_users table exists.
2. Open:
   http://localhost/gulabi_alibrose_full/create_admin.php

3. Create your first admin username/password.

4. After successful creation, DELETE create_admin.php.

5. Open:
   http://localhost/gulabi_alibrose_full/login.php

6. To protect a ROOT page such as dashboard.php:
   Put this at the very top, before any HTML/output:

   <?php
   require_once 'auth.php';
   require_once 'config/db.php';

   7. For a page inside a subfolder, use:

   <?php
   require_once '../auth.php';
   require_once '../config/db.php';

IMPORTANT:
- auth.php must run before HTML output.
- logout.php destroys the session.
- Passwords are stored using password_hash().
