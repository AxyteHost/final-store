<?php
session_start();
$pass = "Rune@600"; 
$ordersF = 'data/orders.json'; $prodsF = 'data/products.json'; $couponsF = 'data/coupons.json';

if (isset($_POST['p']) && $_POST['p'] === $pass) $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit(); }

if (!isset($_SESSION['admin'])) {
    echo "<!DOCTYPE html><html lang='en'><head><meta name='viewport' content='width=device-width, initial-scale=1'><link rel='stylesheet' href='assets/style.css'><title>Admin Login</title></head>
    <body style='justify-content:center; align-items:center;'>
    <div class='checkout-box animate-entry' style='text-align:center; max-width:400px; width:90%;'>
    <h2 style='color:var(--primary); font-family:\"Russo One\"; margin-bottom:20px;'>ADMIN PANEL</h2>
    <form method='POST'><input type='password' name='p' class='form-control' placeholder='Password' style='text-align:center; margin-bottom:20px;' required>
    <button class='btn btn-primary'>LOGIN</button></form></div></body></html>"; exit();
}

$orders = file_exists($ordersF) ? json_decode(file_get_contents($ordersF), true) : []; if(!$orders)$orders=[];
$products = file_exists($prodsF) ? json_decode(file_get_contents($prodsF), true) : []; if(!$products)$products=[];
$coupons = file_exists($couponsF) ? json_decode(file_get_contents($couponsF), true) : []; if(!$coupons)$coupons=[];

if(isset($_POST['status'])) { foreach($orders as &$o) if($o['id']==$_POST['oid']) $o['status']=$_POST['status']; file_put_contents($ordersF, json_encode($orders, JSON_PRETTY_PRINT)); header("Refresh:0"); }

if(isset($_POST['add_prod'])) { 
    $img="https://via.placeholder.com/150";
    if(isset($_FILES['prod_img']) && $_FILES['prod_img']['error']==0) { 
        $ext=pathinfo($_FILES['prod_img']['name'], PATHINFO_EXTENSION); $new="prod_".time().".".$ext; 
        move_uploaded_file($_FILES['prod_img']['tmp_name'], "assets/products/".$new); $img="assets/products/".$new; 
    }
    $products[]=["id"=>time(),"name"=>$_POST['name'],"price"=>$_POST['price'],"image"=>$img,"command"=>$_POST['cmd']]; 
    file_put_contents($prodsF, json_encode($products, JSON_PRETTY_PRINT)); header("Refresh:0"); 
}

if(isset($_POST['del_prod'])) { 
    $pid=$_POST['pid']; 
    foreach($products as $p) { if($p['id']==$pid && file_exists($p['image'])) unlink($p['image']); }
    $products=array_filter($products, function($p)use($pid){return $p['id']!=$pid;}); 
    file_put_contents($prodsF, json_encode(array_values($products), JSON_PRETTY_PRINT)); header("Refresh:0"); 
}

if(isset($_POST['add_coup'])) { $coupons[]=["code"=>strtoupper($_POST['code']),"discount"=>$_POST['disc']]; file_put_contents($couponsF, json_encode($coupons, JSON_PRETTY_PRINT)); header("Refresh:0"); }
if(isset($_POST['del_coup'])) { $code=$_POST['code']; $coupons=array_filter($coupons, function($c)use($code){return $c['code']!=$code;}); file_put_contents($couponsF, json_encode(array_values($coupons), JSON_PRETTY_PRINT)); header("Refresh:0"); }
$tab=$_GET['tab']??'orders';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Panel</title><link rel="stylesheet" href="assets/style.css">
<script>
    function hideLoader() { const loader = document.getElementById('loader-wrapper'); if(loader) { loader.style.opacity='0'; setTimeout(() => { loader.style.display='none'; }, 500); } }
    window.addEventListener('load', hideLoader); setTimeout(hideLoader, 2000);
    function updateFileName(input) { document.getElementById('fileName').innerText = input.files[0] ? "✅ " + input.files[0].name : "📸 Upload Image"; }
</script>
</head>
<body>
<div id="loader-wrapper"><div class="spinner"></div></div>
<nav class="navbar animate-entry"><div class="logo">ADMIN</div><a href="?logout" class="btn btn-danger" style="width:auto;">LOGOUT</a></nav>
<div class="container animate-entry delay-1">
    <div style="margin-bottom:25px; display:flex; gap:15px; overflow-x:auto; padding-bottom:10px;">
        <a href="?tab=orders" class="btn" style="background:<?php echo $tab=='orders'?'var(--primary)':'#333';?>">ORDERS</a>
        <a href="?tab=prods" class="btn" style="background:<?php echo $tab=='prods'?'var(--primary)':'#333';?>">PRODUCTS</a>
        <a href="?tab=coups" class="btn" style="background:<?php echo $tab=='coups'?'var(--primary)':'#333';?>">COUPONS</a>
    </div>

    <?php if($tab=='orders'): ?>
        <div class="checkout-box table-container">
            <table class="table">
                <thead><tr><th>Date</th><th>User</th><th>Item</th><th>Proof</th><th>Status</th><th>Action</th></tr></thead>
                <tbody><?php foreach(array_reverse($orders) as $o): ?>
                <tr>
                    <td style="color:#777; font-size:0.8rem;"><?php echo date("d M", strtotime($o['date'])); ?></td>
                    <td><?php echo $o['username']; ?></td>
                    <td><?php echo $o['product']; ?> ($<?php echo $o['price']; ?>)<br><?php echo ($o['coupon']!="None" ? "<small style='color:lime'>Coupon: ".$o['coupon']."</small>" : ""); ?></td>
                    <td><?php echo ($o['proof']!="No Proof" ? "<a href='".$o['proof']."' target='_blank' style='color:var(--primary)'>View</a>" : "None"); ?></td>
                    <td><span class="status-badge" style="background:<?php echo $o['status']=='Completed'?'#22c55e':'#d97706';?>"><?php echo $o['status']; ?></span></td>
                    <td>
                        <?php if($o['status']=='Pending'): ?>
                        <form method="POST" style="display:inline-flex; gap:5px;">
                            <input type="hidden" name="oid" value="<?php echo $o['id']; ?>">
                            <button name="status" value="Completed" class="btn btn-tick">✓</button> 
                            <button name="status" value="Rejected" class="btn btn-cross">✗</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?></tbody>
            </table>
        </div>

    <?php elseif($tab=='prods'): ?>
        <div class="checkout-box">
            <h3>ADD PRODUCT</h3>
            <form method="POST" enctype="multipart/form-data" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-top:15px;">
                <input name="name" class="form-control" placeholder="Name" required>
                <input name="price" class="form-control" placeholder="Price" required>
                <input name="cmd" class="form-control" placeholder="Command" required>
                <label class="file-upload-wrapper" style="border:1px solid #444; background:#080808; margin:0;">
                    <input type="file" name="prod_img" accept="image/*" required onchange="updateFileName(this)">
                    <span class="file-upload-text" id="fileName">📸 Upload Image</span>
                </label>
                <button name="add_prod" class="btn btn-primary" style="grid-column: 1/-1;">ADD PRODUCT</button>
            </form>
        </div>
        <div class="grid">
            <?php foreach($products as $p): ?>
            <div class="card" style="flex-direction:row; align-items:center; padding:15px;">
                <img src="<?php echo $p['image']; ?>" style="width:60px; height:60px; object-fit:cover; border-radius:8px; margin-right:15px; border:1px solid #333;">
                <div style="flex:1;"><h4><?php echo $p['name']; ?></h4><p style="color:var(--primary);">$<?php echo $p['price']; ?></p></div>
                <form method="POST"><input type="hidden" name="pid" value="<?php echo $p['id']; ?>"><button name="del_prod" class="btn btn-danger" style="padding:5px 15px;">DEL</button></form>
            </div>
            <?php endforeach; ?>
        </div>
    
    <?php elseif($tab=='coups'): ?>
         <div class="checkout-box"><h3>ADD COUPON</h3><form method="POST" style="display:flex; gap:10px; margin-top:10px;"><input name="code" class="form-control" placeholder="Code"><input name="disc" class="form-control" placeholder="%"><button name="add_coup" class="btn btn-primary">ADD</button></form></div>
         <div class="checkout-box table-container"><table class="table"><tr><th>Code</th><th>%</th><th>Act</th></tr><?php foreach($coupons as $c): ?><tr><td><?php echo $c['code']; ?></td><td><?php echo $c['discount']; ?>%</td><td><form method="POST"><input type="hidden" name="code" value="<?php echo $c['code']; ?>"><button name="del_coup" class="btn btn-danger" style="padding:5px;">X</button></form></td></tr><?php endforeach; ?></table></div>
    <?php endif; ?>
</div>
</body>
</html>
