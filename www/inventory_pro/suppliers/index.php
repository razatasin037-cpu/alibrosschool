<?php $pageTitle='Suppliers'; require_once __DIR__.'/../includes/header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$st=$pdo->prepare("INSERT INTO suppliers(name,phone,email,address,gstin) VALUES(?,?,?,?,?)");$st->execute([trim($_POST['name']),trim($_POST['phone']),trim($_POST['email']),trim($_POST['address']),trim($_POST['gstin'])]);flash('success','Supplier added.');redirect('index.php');}
$rows=$pdo->query("SELECT s.*,COUNT(p.id) product_count FROM suppliers s LEFT JOIN products p ON p.supplier_id=s.id GROUP BY s.id ORDER BY s.id DESC")->fetchAll();
?>
<div class="toolbar"><div><h1 class="page-title">Suppliers</h1><div class="muted">Supplier directory and purchasing relationships.</div></div></div>
<div class="grid grid-2"><div class="card"><h3>Add Supplier</h3><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid">
<div class="form-group"><label>Name *</label><input class="input" name="name" required></div><div class="form-group"><label>Phone</label><input class="input" name="phone"></div><div class="form-group"><label>Email</label><input class="input" name="email" type="email"></div><div class="form-group"><label>GSTIN</label><input class="input" name="gstin"></div><div class="form-group full"><label>Address</label><textarea class="textarea" name="address"></textarea></div>
</div><button class="btn" style="margin-top:12px">Add Supplier</button></form></div>
<div class="card table-wrap"><table class="table"><thead><tr><th>Name</th><th>Phone</th><th>GSTIN</th><th>Products</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><strong><?=e($r['name'])?></strong><div class="muted"><?=e($r['email']??'')?></div></td><td><?=e($r['phone']??'')?></td><td><?=e($r['gstin']??'')?></td><td><?=$r['product_count']?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require_once __DIR__.'/../includes/footer.php';?>
