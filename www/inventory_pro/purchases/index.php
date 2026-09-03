<?php $pageTitle='Purchases'; require_once __DIR__.'/../includes/header.php';
$rows=$pdo->query("SELECT p.*,s.name supplier FROM purchases p LEFT JOIN suppliers s ON s.id=p.supplier_id ORDER BY p.id DESC")->fetchAll();
?>
<div class="toolbar"><div><h1 class="page-title">Purchases</h1><div class="muted">Goods received increase inventory automatically.</div></div><a class="btn" href="create.php">+ New Purchase</a></div>
<div class="card table-wrap"><table class="table"><thead><tr><th>Invoice</th><th>Supplier</th><th>Date</th><th>Subtotal</th><th>Tax</th><th>Total</th><th>Payment</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['invoice_no'])?></td><td><?=e($r['supplier']??'—')?></td><td><?=e($r['purchase_date'])?></td><td><?=money($r['subtotal'])?></td><td><?=money($r['tax'])?></td><td><strong><?=money($r['total'])?></strong></td><td><span class="badge badge-info"><?=e($r['payment_status'])?></span></td></tr><?php endforeach;?></tbody></table></div>
<?php require_once __DIR__.'/../includes/footer.php';?>
