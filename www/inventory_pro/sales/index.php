<?php $pageTitle='Sales'; require_once __DIR__.'/../includes/header.php';
$rows=$pdo->query("SELECT s.*,c.name customer FROM sales s LEFT JOIN customers c ON c.id=s.customer_id ORDER BY s.id DESC")->fetchAll();
?>
<div class="toolbar"><div><h1 class="page-title">Sales</h1><div class="muted">Sales reduce stock and create a complete movement trail.</div></div><a class="btn" href="create.php">+ New Sale</a></div>
<div class="card table-wrap"><table class="table"><thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Subtotal</th><th>Tax</th><th>Total</th><th>Payment</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['invoice_no'])?></td><td><?=e($r['customer']??'Walk-in')?></td><td><?=e($r['sale_date'])?></td><td><?=money($r['subtotal'])?></td><td><?=money($r['tax'])?></td><td><strong><?=money($r['total'])?></strong></td><td><span class="badge badge-success"><?=e($r['payment_status'])?></span></td></tr><?php endforeach;?></tbody></table></div>
<?php require_once __DIR__.'/../includes/footer.php';?>
