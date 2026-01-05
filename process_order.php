<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing | RuneMC</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="justify-content:center; align-items:center; text-align:center;">

<?php
// ▼▼▼ PASTE DISCORD WEBHOOK URL BELOW ▼▼▼
$webhook_url = "https://discord.com/api/webhooks/1454832683464261723/aFpis-1jsT4swZu4CdQdljmXrK0RdDt-Lfn4By4HTkuKZmJFR2Pkb0taQMUFLYUEXfxC"; 
// ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'] ?? 'Guest';
    $product = $_POST['product_name'];
    $price = $_POST['price'];
    $coupon = $_POST['coupon_used'] ? $_POST['coupon_used'] : "None";
    
    // 1. Upload Logic
    $proof_path = "No Proof";
    $proof_dir = "assets/proofs/";
    if (!file_exists($proof_dir)) { mkdir($proof_dir, 0777, true); }

    if (isset($_FILES['proof']) && $_FILES['proof']['error'] == 0) {
        $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            $new_name = "proof_" . preg_replace('/[^a-zA-Z0-9]/','',$username) . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['proof']['tmp_name'], $proof_dir . $new_name)) {
                $proof_path = $proof_dir . $new_name;
            } else { $upload_err = "Server folder permission error."; }
        } else { $upload_err = "Only JPG/PNG allowed."; }
    } else { $upload_err = "File upload failed."; }

    if(isset($upload_err)) {
        echo "<div class='checkout-box' style='border-color:var(--primary)'><h2 style='color:var(--primary)'>Upload Failed!</h2><p>$upload_err</p><a href='index.php' class='btn btn-primary'>Try Again</a></div>";
        exit();
    }

    // 2. Save Data
    $order = [
        "id" => uniqid(), "username" => $username, "product" => $product,
        "price" => $price, "proof" => $proof_path, "coupon" => $coupon,
        "status" => "Pending", "date" => date("Y-m-d H:i:s")
    ];
    $file = 'data/orders.json';
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    if(!$data) $data=[];
    $data[] = $order;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    
    // 3. Discord Logic
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $full_img_url = $protocol . $_SERVER['HTTP_HOST'] . "/" . $proof_path;

    $json_data = json_encode([
        "username" => "RuneMC Bot", "tts" => false,
        "embeds" => [[
            "title" => "🔴 New Order Received!", "color" => hexdec("dc2626"),
            "fields" => [
                ["name" => "User", "value" => "```$username```", "inline" => true],
                ["name" => "Product", "value" => "```$product```", "inline" => true],
                ["name" => "Price", "value" => "```$$price```", "inline" => true],
                ["name" => "Coupon", "value" => "`$coupon`", "inline" => true]
            ],
            "image" => ["url" => $full_img_url],
            "footer" => ["text" => "Order ID: " . $order['id']]
        ]]
    ]);

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    $discord_result = curl_exec($ch);
    $discord_err = curl_error($ch);
    curl_close($ch);

    echo "<div class='checkout-box' style='max-width:500px; border-color:#22c55e; animation: fadeIn 1s;'>";
    echo "<h1 style='color:#22c55e; font-family:\"Russo One\"; font-size:2.5rem; margin-bottom:20px;'>ORDER PLACED!</h1>";
    echo "<p style='font-size:1.1rem; margin-bottom:20px;'>Thank you <b>$username</b>. Your proof has been submitted safely.</p>";
    echo "<div style='background:#0a0a0a; padding:15px; border-radius:8px; margin-bottom:20px;'>Status: <b style='color:#d97706'>⏳ PENDING APPROVAL</b></div>";
    
    if($discord_err || $discord_result === false) {
        echo "<p style='color:#aaa; font-size:0.9rem;'>Note: Discord notification failed (host issue), but your order is safely saved in our database.</p>";
    }

    echo "<a href='index.php' class='btn btn-primary' style='margin-top:20px;'>RETURN TO STORE</a>";
    echo "</div>";

} else { header("Location: index.php"); }
?>
</body>
</html>
