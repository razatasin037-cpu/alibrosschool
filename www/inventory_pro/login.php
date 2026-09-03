<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/config/auth.php';
require_once __DIR__.'/config/helpers.php';
if (!empty($_SESSION['user'])) header('Location: index.php');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $st=$pdo->prepare("SELECT * FROM users WHERE email=? AND status=1 LIMIT 1");
    $st->execute([$email]); $user=$st->fetch();
    if ($user && password_verify($password,$user['password'])) {
        $_SESSION['user']=$user;
        session_regenerate_id(true);
        header('Location: index.php'); exit;
    }
    $error='Invalid email or password.';
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login · Inventory Pro</title><link rel="stylesheet" href="/inventory_pro/assets/css/app.css"></head>
<body class="login-page"><div class="login-card">
<div class="brand" style="padding:0 0 18px"><span class="brand-mark">IP</span><span>Inventory Pro</span></div>
<h1>Welcome back</h1><p class="muted">Sign in to manage your inventory.</p>
<?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<form method="post">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="form-group"><label>Email</label><input class="input" type="email" name="email" value="admin@inventory.local" required></div>
<div class="form-group"><label>Password</label><input class="input" type="password" name="password" value="admin123" required></div>
<button class="btn" type="submit">Sign In</button>
</form>
<p class="muted" style="margin-top:18px">Default: admin@inventory.local / admin123</p>
</div></body></html>
