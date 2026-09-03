<?php
$pageTitle='Dashboard';
require_once __DIR__.'/includes/header.php';

$products=(int)$pdo->query("SELECT COUNT(*) FROM products WHERE status=1")->fetchColumn();
$low=(int)$pdo->query("SELECT COUNT(*) FROM products WHERE status=1 AND stock_qty>0 AND stock_qty<=reorder_level")->fetchColumn();
$out=(int)$pdo->query("SELECT COUNT(*) FROM products WHERE status=1 AND stock_qty<=0")->fetchColumn();
$salesToday=(float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date=CURDATE()")->fetchColumn();
$purchasesToday=(float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM purchases WHERE purchase_date=CURDATE()")->fetchColumn();
$stockValue=(float)$pdo->query("SELECT COALESCE(SUM(stock_qty*purchase_price),0) FROM products WHERE status=1")->fetchColumn();
$recent=$pdo->query("SELECT s.invoice_no,s.sale_date,s.total,c.name customer FROM sales s LEFT JOIN customers c ON c.id=s.customer_id ORDER BY s.id DESC LIMIT 7")->fetchAll();
$lowProducts=$pdo->query("SELECT sku,name,stock_qty,reorder_level,unit FROM products WHERE status=1 AND stock_qty<=reorder_level ORDER BY stock_qty ASC LIMIT 7")->fetchAll();
?>
<div class="toolbar"><div><div class="muted" style="margin-bottom:5px">OVERVIEW</div><h1 class="page-title">Good day, <?=e($_SESSION["user"]["name"])?> 👋</h1><div class="muted">Your inventory is ready. Here is today’s business pulse.</div></div><div class="actions"><a class="btn" href="/inventory_pro/sales/create.php">+ New Sale</a><a class="btn btn-secondary" href="/inventory_pro/purchases/create.php">+ New Purchase</a></div></div>
<div class="card" style="margin-bottom:18px;overflow:hidden;position:relative;background:linear-gradient(120deg,#10182a,#1a2440);color:#fff;border:0">
<div style="position:absolute;width:280px;height:280px;border-radius:50%;background:rgba(105,122,255,.22);filter:blur(35px);right:-90px;top:-130px"></div>
<div style="position:relative;display:flex;align-items:center;justify-content:space-between;gap:20px">
<div><div style="font-size:10px;letter-spacing:1.5px;color:#9ca9c7;font-weight:800;margin-bottom:8px">INVENTORY COMMAND CENTER</div>
<div style="font:800 22px Manrope,sans-serif;letter-spacing:-.7px">Everything under control.</div>
<div style="font-size:11px;color:#aeb8ca;margin-top:7px">Monitor stock, sales, purchases and business performance from one place.</div></div>
<div style="min-width:100px;text-align:right"><div style="font:800 28px Manrope,sans-serif"><?=date('d')?></div><div style="font-size:10px;color:#9ca9c7"><?=date('M Y')?></div></div>
</div></div>
<div class="grid grid-4">
<div class="card stat"><div class="label">Active Products</div><div class="value"><?=$products?></div><div class="hint">Product master</div></div>
<div class="card stat"><div class="label">Stock Value</div><div class="value"><?=money($stockValue)?></div><div class="hint">At purchase cost</div></div>
<div class="card stat"><div class="label">Today's Sales</div><div class="value"><?=money($salesToday)?></div><div class="hint">Revenue recorded today</div></div>
<div class="card stat"><div class="label">Today's Purchases</div><div class="value"><?=money($purchasesToday)?></div><div class="hint">Purchasing recorded today</div></div>
</div>
<div style="height:18px"></div>
<div class="grid grid-3">
<div class="card"><div class="label">Low Stock</div><div class="value" style="font-size:30px"><?=$low?></div><div class="muted">Items at/below reorder level</div></div>
<div class="card"><div class="label">Out of Stock</div><div class="value" style="font-size:30px"><?=$out?></div><div class="muted">Immediate action required</div></div>
<div class="card"><div class="label">Quick Actions</div><div class="actions" style="margin-top:12px"><a class="btn btn-sm" href="/inventory_pro/products/create.php">Add Product</a><a class="btn btn-sm btn-success" href="/inventory_pro/customers/create.php">Add Customer</a><a class="btn btn-sm btn-warning" href="/inventory_pro/expenses/create.php">Add Expense</a></div></div>
</div>
<div style="height:18px"></div>
<div class="grid grid-2">
<div class="card"><div class="toolbar"><strong>Recent Sales</strong><a class="muted" href="/inventory_pro/sales/index.php">View all</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th></tr></thead><tbody>
<?php foreach($recent as $r):?><tr><td><?=e($r['invoice_no'])?></td><td><?=e($r['customer']??'Walk-in')?></td><td><?=e($r['sale_date'])?></td><td><?=money($r['total'])?></td></tr><?php endforeach;?>
<?php if(!$recent):?><tr><td colspan="4" class="empty">No sales recorded.</td></tr><?php endif;?>
</tbody></table></div></div>
<div class="card"><div class="toolbar"><strong>Stock Alerts</strong><a class="muted" href="/inventory_pro/products/index.php?filter=low">View products</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>SKU</th><th>Product</th><th>Stock</th><th>Reorder</th></tr></thead><tbody>
<?php foreach($lowProducts as $r):?><tr><td><?=e($r['sku'])?></td><td><?=e($r['name'])?></td><td><span class="badge <?=($r['stock_qty']<=0?'badge-danger':'badge-warning')?>"><?=e($r['stock_qty'].' '.$r['unit'])?></span></td><td><?=e($r['reorder_level'])?></td></tr><?php endforeach;?>
<?php if(!$lowProducts):?><tr><td colspan="4" class="empty">Stock levels look healthy.</td></tr><?php endif;?>
</tbody></table></div></div>
</div>
<?php require_once __DIR__.'/includes/footer.php';?>
