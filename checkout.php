<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['id'])) { header("Location: index.php"); exit(); }
$pid = $_GET['id'];
$products = json_decode(file_get_contents('data/products.json'), true);
$coupons = json_decode(file_get_contents('data/coupons.json'), true);
$item = null;
if($products) { foreach ($products as $p) { if ($p['id'] == $pid) { $item = $p; break; } } }
if(!$item) { header("Location: index.php"); exit(); }

$discount = 0; $code = ""; $final_price = $item['price']; $msg = "";
if (isset($_POST['apply_coupon'])) {
    $input = strtoupper($_POST['coupon_code']);
    if($coupons) {
        foreach ($coupons as $c) {
            if ($c['code'] === $input) {
                $discount = $c['discount']; $code = $c['code'];
                $final_price = $item['price'] - (($item['price'] * $discount) / 100);
                $msg = "<span style='color:#22c55e'>✅ Applied! $discount% OFF</span>"; break;
            }
        }
    }
    if (!$code) $msg = "<span style='color:var(--primary)'>❌ Invalid Code</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        function hideLoader() {
            const loader = document.getElementById('loader-wrapper');
            if(loader) { loader.style.opacity='0'; setTimeout(() => { loader.style.display='none'; }, 500); }
        }
        window.addEventListener('load', hideLoader);
        setTimeout(hideLoader, 2000);

        function updateFileName(input) {
            document.getElementById('fileName').innerText = input.files[0] ? "✅ Selected: " + input.files[0].name : "📸 Click to Upload Proof";
            document.getElementById('fileName').style.color = input.files[0] ? "#22c55e" : "#aaa";
        }
    </script>
</head>
<body>
    <div id="loader-wrapper"><div class="spinner"></div></div>

    <nav class="navbar animate-entry">
        <div class="logo">RUNEMC</div>
        <div class="user-info"><img src="https://mc-heads.net/avatar/<?php echo $_SESSION['username']; ?>/25"> <?php echo $_SESSION['username']; ?></div>
    </nav>

    <div class="container animate-entry delay-1">
        
        <div class="checkout-box" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
            <h2 style="font-family:'Russo One'; color: white;"><?php echo $item['name']; ?></h2>
            <div style="text-align:right;">
                <?php if($code): ?>
                    <span style="text-decoration:line-through; color:#555">$<?php echo number_format($item['price'],2); ?></span><br>
                    <span style="color:#22c55e; font-size:1.8rem; font-weight:bold; font-family:'Russo One';">$<?php echo number_format($final_price, 2); ?></span>
                <?php else: ?>
                    <span style="color:var(--primary); font-size:1.8rem; font-weight:bold; font-family:'Russo One';">$<?php echo number_format($item['price'], 2); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="checkout-box animate-entry delay-2">
            <h4 style="color:#888; margin-bottom:10px;">PROMO CODE</h4>
            <form method="POST" style="display:flex; gap:10px;">
                <input type="text" name="coupon_code" class="form-control" value="<?php echo $code; ?>" placeholder="Enter Code">
                <button name="apply_coupon" class="btn btn-primary" style="width:120px;">APPLY</button>
            </form>
            <p style="margin-top:10px; font-weight:bold;"><?php echo $msg; ?></p>
        </div>

        <form action="process_order.php" method="POST" enctype="multipart/form-data" class="animate-entry delay-3">
            <input type="hidden" name="product_name" value="<?php echo $item['name']; ?>">
            <input type="hidden" name="price" value="<?php echo number_format($final_price, 2); ?>">
            <input type="hidden" name="coupon_used" value="<?php echo $code; ?>">
            
            <div class="checkout-box" style="text-align:center;">
                <h3 style="margin-bottom:20px; font-family:'Russo One'; color: var(--primary);">SCAN TO PAY</h3>
                <div class="qr-area">
                    <img src="assets/qrcode.jpg" alt="QR Missing" onerror="this.parentElement.innerHTML='<p style=\'color:black\'>Upload qrcode.jpg to assets folder</p>'">
                </div>
                <p style="color:#ccc; margin-bottom:25px; font-size:1.1rem;">Total to Pay: <b style="color:var(--primary)">$<?php echo number_format($final_price, 2); ?></b></p>

                <div class="form-group">
                    <label class="file-upload-wrapper">
                        <input type="file" name="proof" accept="image/*" required onchange="updateFileName(this)">
                        <span class="file-upload-text" id="fileName">📸 Click here to Upload Proof</span>
                    </label>
                </div>
            </div>

            <div class="checkout-box" style="background: rgba(220, 38, 38, 0.05); border-color: var(--primary);">
                 <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" required style="width:20px; height:20px; accent-color:var(--primary);">
                    <span style="font-size:0.9rem;">I agree to the <a href="terms.php" target="_blank" style="color:var(--primary)">Terms & Conditions</a>.</span>
                </label>
            </div>

            <button class="btn btn-primary" style="padding: 20px; font-size: 1.3rem;">CONFIRM & PAY</button>
        </form>
    </div>
</body>
</html>
