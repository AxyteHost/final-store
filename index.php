<?php
session_start();
if (isset($_POST['username'])) { $_SESSION['username'] = htmlspecialchars($_POST['username']); }
$products = json_decode(file_get_contents('data/products.json'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RuneMC Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        function hideLoader() {
            const loader = document.getElementById('loader-wrapper');
            if(loader) { loader.style.opacity='0'; setTimeout(() => { loader.style.display='none'; }, 500); }
        }
        window.addEventListener('load', hideLoader);
        setTimeout(hideLoader, 2000); // Forces loader to close after 2s
    </script>
</head>
<body>
    <div id="loader-wrapper">
        <div class="spinner"></div>
        <div class="loading-text">RUNEMC LOADING...</div>
    </div>

    <nav class="navbar animate-entry">
        <div class="logo">RUNEMC</div>
        <?php if(isset($_SESSION['username'])): ?>
            <div class="user-info">
                <img src="https://mc-heads.net/avatar/<?php echo $_SESSION['username']; ?>/25" alt="">
                <?php echo $_SESSION['username']; ?>
            </div>
        <?php endif; ?>
    </nav>

    <div class="hero">
        <h1 class="animate-entry delay-1">DOMINATE THE SERVER</h1>
        <p class="animate-entry delay-2">Get exclusive Ranks, Coins & Keys. Instant Delivery.</p>
    </div>

    <div class="features animate-entry delay-2">
        <div class="feature-box"><h3>⚡ Instant</h3><p>Automated delivery system.</p></div>
        <div class="feature-box"><h3>🛡️ Secure</h3><p>Verified manual checks.</p></div>
        <div class="feature-box"><h3>💎 Premium</h3><p>Best value items.</p></div>
    </div>

    <div class="container animate-entry delay-3">
        <?php if (!isset($_SESSION['username'])): ?>
        <div class="checkout-box" style="text-align: center; max-width: 450px; margin: 40px auto; transform: scale(1.05);">
            <h2 style="margin-bottom: 20px; font-family:'Russo One'; color: var(--primary);">PLAYER LOGIN</h2>
            <form method="POST">
                <input type="text" name="username" class="form-control" placeholder="Enter Username" required style="margin-bottom: 20px; text-align:center;">
                <button class="btn btn-primary">ENTER STORE</button>
            </form>
        </div>
        <?php else: ?>

        <h2 style="margin-bottom: 30px; color:white; font-family:'Russo One'; border-left: 5px solid var(--primary); padding-left: 15px;">STORE ITEMS</h2>
        <div class="grid">
            <?php if($products): foreach ($products as $p): ?>
            <div class="card">
                <div class="card-header"><img src="<?php echo $p['image']; ?>" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'"></div>
                <div class="card-body">
                    <div class="card-title"><?php echo $p['name']; ?></div>
                    <span class="card-price">$<?php echo number_format($p['price'], 2); ?></span>
                    <a href="checkout.php?id=<?php echo $p['id']; ?>" class="btn btn-primary">PURCHASE</a>
                </div>
            </div>
            <?php endforeach; else: echo "<p style='padding:30px; text-align:center;'>No products available.</p>"; endif; ?>
        </div>

        <?php endif; ?>
    </div>

    <div class="footer animate-entry delay-3">
        <p>Copyright © RuneMC 2025.</p>
        <br>
        <a href="terms.php">Terms</a> <a href="refund.php">Refunds</a> <a href="disclaimer.php">Disclaimer</a>
    </div>
</body>
</html>
