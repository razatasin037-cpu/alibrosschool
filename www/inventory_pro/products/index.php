<?php
$pageTitle='Products'; require_once __DIR__.'/../includes/header.php';
$q=trim($_GET['q']??''); $where=''; $params=[];
if($q!==''){ $where="WHERE p.name LIKE ? OR p.sku LIKE ? OR COALESCE(p.barcode,'') LIKE ?"; $params=["%$q%","%$q%","%$q%"]; }
$st=$pdo->prepare("SELECT p.*,c.name category,s.name supplier FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN suppliers s ON s.id=p.supplier_id $where ORDER BY p.id DESC");
$st->execute($params); $rows=$st->fetchAll();
?>
<div class="toolbar"><div><h1 class="page-title">Products</h1><div class="muted">Product master, pricing and stock controls.</div></div><div class="right"><a class="btn" href="create.php">+ Add Product</a></div></div>
<form class="toolbar" method="get"><div class="left"><input class="input search" name="q" placeholder="Search name, SKU or barcode" value="<?=e($q)?>"><button class="btn btn-secondary">Search</button></div></form>
<div class="card table-wrap"><table class="table"><thead><tr><th>SKU</th><th>Product</th><th>Category</th><th>Buy</th><th>Sell</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?=e($r['sku'])?></td><td><strong><?=e($r['name'])?></strong><div class="muted"><?=e($r['barcode']??'')?></div></td><td><?=e($r['category']??'—')?></td><td><?=money($r['purchase_price'])?></td><td><?=money($r['sale_price'])?></td><td><span class="badge <?=($r['stock_qty']<=0?'badge-danger':($r['stock_qty']<=$r['reorder_level']?'badge-warning':'badge-success'))?>"><?=e($r['stock_qty'].' '.$r['unit'])?></span></td><td><?= $r['status']?'<span class="badge badge-success">Active</span>':'<span class="badge badge-danger">Inactive</span>' ?></td><td class="actions"><a class="btn btn-sm btn-secondary" href="edit.php?id=<?=$r['id']?>">Edit</a><a class="btn btn-sm btn-danger" data-confirm="Delete this product?" href="delete.php?id=<?=$r['id']?>">Delete</a></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="8" class="empty">No products found.</td></tr><?php endif;?>
</tbody></table></div>
<?php require_once __DIR__.'/../includes/footer.php';?>
