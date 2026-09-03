<?php $pageTitle='Customers'; require_once __DIR__.'/../includes/header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$st=$pdo->prepare("INSERT INTO customers(name,phone,email,address,gstin) VALUES(?,?,?,?,?)");$st->execute([trim($_POST['name']),trim($_POST['phone']),trim($_POST['email']),trim($_POST['address']),trim($_POST['gstin'])]);flash('success','Customer added.');redirect('index.php');}
$rows=$pdo->query("SELECT c.*,COUNT(s.id) sales_count,COALESCE(SUM(s.total),0) spent FROM customers c LEFT JOIN sales s ON s.customer_id=c.id GROUP BY c.id ORDER BY c.id DESC")->fetchAll();
?>
<div class="toolbar"><div><h1 class="page-title">Customers</h1><div class="muted">Customer master and purchase history snapshot.</div></div></div>
<div class="grid grid-2"><div class="card"><h3>Add Customer</h3><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid">
<div class="form-group"><label>Name *</label><input class="input" name="name" required></div><div class="form-group"><label>Phone</label><input class="input" name="phone"></div><div class="form-group"><label>Email</label><input class="input" name="email" type="email"></div><div class="form-group"><label>GSTIN</label><input class="input" name="gstin"></div><div class="form-group full"><label>Address</label><textarea class="textarea" name="address"></textarea></div>
</div><button class="btn" style="margin-top:12px">Add Customer</button></form></div>
<div class="card table-wrap"><table class="table"><thead><tr><th>Name</th><th>Phone</th><th>Orders</th><th>Sales Value</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['name'])?></td><td><?=e($r['phone']??'')?></td><td><?=$r['sales_count']?></td><td><?=money($r['spent'])?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require_once __DIR__.'/../includes/footer.php';?>
