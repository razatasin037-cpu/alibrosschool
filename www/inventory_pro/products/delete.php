<?php require_once __DIR__.'/../config/database.php'; require_once __DIR__.'/../config/auth.php'; require_once __DIR__.'/../config/helpers.php'; require_login();
$id=(int)($_GET['id']??0);
$st=$pdo->prepare("SELECT stock_qty FROM products WHERE id=?");$st->execute([$id]);$r=$st->fetch();
if($r && (float)$r['stock_qty']==0){$pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);flash('success','Product deleted.');}else flash('error','Product can only be deleted when stock is zero.');
header('Location: index.php'); exit;
