<?php
$pageTitle='New Purchase'; require_once __DIR__.'/../includes/header.php';
$sups=$pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll();
$products=$pdo->query("SELECT id,sku,name,purchase_price,tax_rate,stock_qty,unit FROM products WHERE status=1 ORDER BY name")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();
 $supplier=$_POST['supplier_id']?:null;$date=$_POST['purchase_date']?:date('Y-m-d');$invoice=trim($_POST['invoice_no'])?:('PUR-'.date('YmdHis'));$status=$_POST['payment_status']??'paid';$notes=trim($_POST['notes']??'');
 $ids=$_POST['product_id']??[];$qtys=$_POST['qty']??[];$prices=$_POST['price']??[];
 try{
  $pdo->beginTransaction();$subtotal=0;$tax=0;$items=[];
  for($i=0;$i<count($ids);$i++){ $pid=(int)$ids[$i];$q=(float)($qtys[$i]??0);$pr=(float)($prices[$i]??0);if($pid<=0||$q<=0)continue;$st=$pdo->prepare("SELECT tax_rate FROM products WHERE id=? FOR UPDATE");$st->execute([$pid]);$p=$st->fetch();if(!$p)throw new Exception('Invalid product');$base=$q*$pr;$t=$base*((float)$p['tax_rate']/100);$subtotal+=$base;$tax+=$t;$items[]=[$pid,$q,$pr,(float)$p['tax_rate'],$base+$t];}
  if(!$items)throw new Exception('Add at least one item.');
  $total=$subtotal+$tax;$st=$pdo->prepare("INSERT INTO purchases(invoice_no,supplier_id,purchase_date,subtotal,tax,total,payment_status,notes,created_by) VALUES(?,?,?,?,?,?,?,?,?)");$st->execute([$invoice,$supplier,$date,$subtotal,$tax,$total,$status,$notes,$_SESSION['user']['id']]);$purchaseId=(int)$pdo->lastInsertId();
  foreach($items as $it){$st=$pdo->prepare("INSERT INTO purchase_items(purchase_id,product_id,qty,price,tax_rate,total) VALUES(?,?,?,?,?,?)");$st->execute([$purchaseId,...$it]);$st=$pdo->prepare("UPDATE products SET stock_qty=stock_qty+? WHERE id=?");$st->execute([$it[1],$it[0]]);$st=$pdo->prepare("SELECT stock_qty FROM products WHERE id=?");$st->execute([$it[0]]);$bal=$st->fetchColumn();$st=$pdo->prepare("INSERT INTO stock_movements(product_id,movement_type,reference_type,reference_id,qty_in,balance_after,note,created_by) VALUES(?,?,?,?,?,?,?,?)");$st->execute([$it[0],'purchase','purchase',$purchaseId,$it[1],$bal,$invoice,$_SESSION['user']['id']]);}
  $pdo->commit();flash('success','Purchase saved and stock increased.');redirect('index.php');
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error',$e->getMessage());}
}
?>
<div class="toolbar"><div><h1 class="page-title">New Purchase</h1><div class="muted">Every saved line updates stock atomically.</div></div><a class="btn btn-secondary" href="index.php">Back</a></div>
<form method="post" id="purchaseForm"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="card"><div class="form-grid"><div class="form-group"><label>Invoice No.</label><input class="input" name="invoice_no" placeholder="Auto-generated if blank"></div><div class="form-group"><label>Date</label><input class="input" type="date" name="purchase_date" value="<?=date('Y-m-d')?>" required></div><div class="form-group"><label>Supplier</label><select class="select" name="supplier_id"><option value="">Select supplier</option><?php foreach($sups as $s):?><option value="<?=$s['id']?>"><?=e($s['name'])?></option><?php endforeach;?></select></div><div class="form-group"><label>Payment Status</label><select class="select" name="payment_status"><option>paid</option><option>partial</option><option>due</option></select></div></div></div>
<div style="height:14px"></div><div class="card"><div class="toolbar"><strong>Items</strong><button class="btn btn-secondary" type="button" onclick="addRow()">+ Add Row</button></div><div class="table-wrap"><table class="table" id="items"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Line Total</th><th></th></tr></thead><tbody></tbody></table></div><div style="margin-top:18px;text-align:right"><strong>Subtotal + tax calculated on save.</strong></div></div>
<div style="height:14px"></div><div class="card"><div class="form-group"><label>Notes</label><textarea class="textarea" name="notes"></textarea></div><button class="btn btn-success" style="margin-top:14px">Save Purchase</button></div></form>
<script>
const products=<?=json_encode($products)?>;
function addRow(){const tb=document.querySelector('#items tbody'),tr=document.createElement('tr');tr.innerHTML=`<td><select class="select" name="product_id[]" required><option value="">Select product</option>${products.map(p=>`<option value="${p.id}" data-price="${p.purchase_price}">${p.sku} · ${p.name} · Stock ${p.stock_qty} ${p.unit}</option>`).join('')}</select></td><td><input class="input" type="number" min="0.01" step="0.01" name="qty[]" required></td><td><input class="input" type="number" min="0" step="0.01" name="price[]" required></td><td class="line">0.00</td><td><button class="btn btn-sm btn-danger" type="button" onclick="this.closest('tr').remove()">Remove</button></td>`;tb.appendChild(tr);const sel=tr.querySelector('select');sel.onchange=()=>tr.querySelector('[name="price[]"]').value=sel.selectedOptions[0]?.dataset.price||0;}
addRow();
</script>
<?php require_once __DIR__.'/../includes/footer.php';?>
