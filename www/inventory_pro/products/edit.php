<?php
require_once __DIR__.'/../includes/header.php';
$id=(int)($_GET['id']??0);
$edit=$id>0;
if($edit){$st=$pdo->prepare("SELECT * FROM products WHERE id=?");$st->execute([$id]);$row=$st->fetch();if(!$row) exit('Product not found.');}
else $row=['sku'=>'','barcode'=>'','name'=>'','category_id'=>'','supplier_id'=>'','unit'=>'pcs','purchase_price'=>'0','sale_price'=>'0','tax_rate'=>'0','stock_qty'=>'0','reorder_level'=>'5','status'=>1];
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();
 $data=[trim($_POST['sku']),trim($_POST['barcode'])?:null,trim($_POST['name']),($_POST['category_id']?:null),($_POST['supplier_id']?:null),trim($_POST['unit']),max(0,(float)$_POST['purchase_price']),max(0,(float)$_POST['sale_price']),max(0,(float)$_POST['tax_rate']),max(0,(float)$_POST['stock_qty']),max(0,(float)$_POST['reorder_level']),isset($_POST['status'])?1:0];
 try{
  $pdo->beginTransaction();
  if($edit){
   $old=$row['stock_qty'];
   $st=$pdo->prepare("UPDATE products SET sku=?,barcode=?,name=?,category_id=?,supplier_id=?,unit=?,purchase_price=?,sale_price=?,tax_rate=?,stock_qty=?,reorder_level=?,status=? WHERE id=?");
   $st->execute([...$data,$id]);
   if((float)$data[9] !== (float)$old){
    $delta=(float)$data[9]-(float)$old;
    $st=$pdo->prepare("INSERT INTO stock_movements(product_id,movement_type,reference_type,qty_in,qty_out,balance_after,note,created_by) VALUES(?,?,?,?,?,?,?,?)");
    $st->execute([$id,'adjustment','product_edit',$delta>0?$delta:0,$delta<0?abs($delta):0,$data[9],'Manual stock adjustment',$_SESSION['user']['id']]);
   }
  } else {
   $st=$pdo->prepare("INSERT INTO products(sku,barcode,name,category_id,supplier_id,unit,purchase_price,sale_price,tax_rate,stock_qty,reorder_level,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
   $st->execute($data); $newId=(int)$pdo->lastInsertId();
   if($data[9]>0){$st=$pdo->prepare("INSERT INTO stock_movements(product_id,movement_type,reference_type,qty_in,balance_after,note,created_by) VALUES(?,?,?,?,?,?,?)");$st->execute([$newId,'opening','product_create',$data[9],$data[9],'Opening stock',$_SESSION['user']['id']]);}
  }
  $pdo->commit(); flash('success',$edit?'Product updated.':'Product created.'); redirect('index.php');
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error','Could not save product. SKU/barcode may already exist.');}
 }
$cats=$pdo->query("SELECT id,name FROM categories WHERE status=1 ORDER BY name")->fetchAll();
$sups=$pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll();
$pageTitle=$edit?'Edit Product':'Add Product';
?>
<div class="toolbar"><div><h1 class="page-title"><?=e($pageTitle)?></h1><div class="muted">Configure product, pricing and stock policy.</div></div><a class="btn btn-secondary" href="index.php">Back</a></div>
<div class="card"><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="form-grid">
<div class="form-group"><label>SKU *</label><input class="input" name="sku" required value="<?=e($row['sku'])?>"></div>
<div class="form-group"><label>Barcode</label><input class="input" name="barcode" value="<?=e($row['barcode'])?>"></div>
<div class="form-group full"><label>Product Name *</label><input class="input" name="name" required value="<?=e($row['name'])?>"></div>
<div class="form-group"><label>Category</label><select class="select" name="category_id"><option value="">None</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$row['category_id']==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Supplier</label><select class="select" name="supplier_id"><option value="">None</option><?php foreach($sups as $s):?><option value="<?=$s['id']?>" <?=$row['supplier_id']==$s['id']?'selected':''?>><?=e($s['name'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Unit</label><input class="input" name="unit" value="<?=e($row['unit'])?>"></div>
<div class="form-group"><label>Purchase Price</label><input class="input" type="number" step="0.01" min="0" name="purchase_price" value="<?=e($row['purchase_price'])?>"></div>
<div class="form-group"><label>Sale Price</label><input class="input" type="number" step="0.01" min="0" name="sale_price" value="<?=e($row['sale_price'])?>"></div>
<div class="form-group"><label>Tax %</label><input class="input" type="number" step="0.01" min="0" name="tax_rate" value="<?=e($row['tax_rate'])?>"></div>
<div class="form-group"><label>Current Stock</label><input class="input" type="number" step="0.01" min="0" name="stock_qty" value="<?=e($row['stock_qty'])?>"></div>
<div class="form-group"><label>Reorder Level</label><input class="input" type="number" step="0.01" min="0" name="reorder_level" value="<?=e($row['reorder_level'])?>"></div>
<div class="form-group"><label><input type="checkbox" name="status" <?=$row['status']?'checked':''?>> Active product</label></div>
</div><div style="margin-top:18px"><button class="btn" type="submit">Save Product</button></div></form></div>
<?php require_once __DIR__.'/../includes/footer.php';?>
