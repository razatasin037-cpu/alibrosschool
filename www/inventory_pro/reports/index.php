<?php $pageTitle='Reports & Analytics'; require_once __DIR__.'/../includes/header.php';
$from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-d');
$st=$pdo->prepare("SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date BETWEEN ? AND ?");$st->execute([$from,$to]);$sales=(float)$st->fetchColumn();
$st=$pdo->prepare("SELECT COALESCE(SUM(total),0) FROM purchases WHERE purchase_date BETWEEN ? AND ?");$st->execute([$from,$to]);$purchases=(float)$st->fetchColumn();
$st=$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?");$st->execute([$from,$to]);$expenses=(float)$st->fetchColumn();
$stock=(float)$pdo->query("SELECT COALESCE(SUM(stock_qty*purchase_price),0) FROM products WHERE status=1")->fetchColumn();
$top=$pdo->query("SELECT p.name,p.sku,SUM(si.qty) qty,SUM(si.total) revenue FROM sale_items si JOIN products p ON p.id=si.product_id GROUP BY p.id ORDER BY qty DESC LIMIT 10")->fetchAll();
?>
<div class="toolbar"><div><h1 class="page-title">Reports & Analytics</h1><div class="muted">Management snapshot with date filters.</div></div></div>
<div class="card"><form class="toolbar" method="get"><div class="left"><div><label class="muted">From</label><input class="input" type="date" name="from" value="<?=e($from)?>"></div><div><label class="muted">To</label><input class="input" type="date" name="to" value="<?=e($to)?>"></div><button class="btn">Apply</button></div></form></div>
<div style="height:18px"></div><div class="grid grid-4">
<div class="card stat"><div class="label">Sales</div><div class="value"><?=money($sales)?></div></div>
<div class="card stat"><div class="label">Purchases</div><div class="value"><?=money($purchases)?></div></div>
<div class="card stat"><div class="label">Expenses</div><div class="value"><?=money($expenses)?></div></div>
<div class="card stat"><div class="label">Stock at Cost</div><div class="value"><?=money($stock)?></div></div>
</div>
<div style="height:18px"></div><div class="card"><div class="toolbar"><strong>Top Selling Products</strong><span class="muted">All-time by quantity</span></div><div class="table-wrap"><table class="table"><thead><tr><th>SKU</th><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead><tbody><?php foreach($top as $r):?><tr><td><?=e($r['sku'])?></td><td><?=e($r['name'])?></td><td><?=e($r['qty'])?></td><td><?=money($r['revenue'])?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require_once __DIR__.'/../includes/footer.php';?>
