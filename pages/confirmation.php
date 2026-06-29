<?php
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: product.php");
    exit();
}

// Query order details
$stmt = mysqli_prepare($conn, "SELECT order_id, total_price, order_status, order_date, customer_email FROM orders WHERE order_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $db_order_id, $total_price, $order_status, $order_date, $customer_email);
if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: product.php");
    exit();
}
mysqli_stmt_close($stmt);

// Fetch item details purchased in this order to display
$items = [];
$items_stmt = mysqli_prepare($conn, "SELECT p.product_name, oi.quantity, oi.price FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
mysqli_stmt_bind_result($items_stmt, $product_name, $quantity, $price);
while (mysqli_stmt_fetch($items_stmt)) {
    $items[] = [
        'name' => $product_name,
        'qty' => $quantity,
        'price' => $price
    ];
}
mysqli_stmt_close($items_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - DysCover Nexus</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .confirmation-container {
            max-width: 700px;
            margin: 5rem auto;
            text-align: center;
            padding: 4rem 2rem;
        }
        .success-icon {
            font-size: 5rem;
            color: #00ff88;
            margin-bottom: 2rem;
            animation: pulse-glow 2s infinite;
        }
        .pending-icon {
            font-size: 5rem;
            color: #ffaa00;
            margin-bottom: 2rem;
        }
        .order-details {
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }
        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .order-row:last-child {
            border-bottom: none;
        }
        .purchased-items {
            background: rgba(0, 0, 0, 0.2);
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
        }
        .purchased-item-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .purchased-item-row:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="glass-panel confirmation-container">
            <?php if ($order_status === 'Pending'): ?>
                <i class="fa-solid fa-clock pending-icon"></i>
                <h1 class="gradient-text">Receipt Uploaded!</h1>
                <p style="color: var(--text-muted); margin-top: 1rem; font-size: 1.1rem;">Your order #DYS-<?php echo $order_id; ?> was successfully submitted and is awaiting administrative receipt verification.</p>
            <?php else: ?>
                <i class="fa-solid fa-circle-check success-icon"></i>
                <h1 class="gradient-text">Payment Confirmed!</h1>
                <p style="color: var(--text-muted); margin-top: 1rem; font-size: 1.1rem;">Thank you for your purchase. Your access keys have been approved and activated.</p>
            <?php endif; ?>
            
            <div class="order-details">
                <h3 style="margin-bottom: 1rem; color: var(--primary);">Order Information</h3>
                <div class="order-row">
                    <span style="color: var(--text-muted);">Order Number:</span>
                    <span style="font-family: var(--font-heading); font-weight: bold;">#DYS-<?php echo $order_id; ?></span>
                </div>
                <div class="order-row">
                    <span style="color: var(--text-muted);">Order Date:</span>
                    <span><?php echo date("F j, Y, g:i a", strtotime($order_date)); ?></span>
                </div>
                <div class="order-row">
                    <span style="color: var(--text-muted);">Status:</span>
                    <span style="color: <?php echo ($order_status === 'Pending') ? '#ffaa00' : '#00ff88'; ?>; font-weight: bold;"><?php echo htmlspecialchars($order_status); ?></span>
                </div>
                <div class="order-row">
                    <span style="color: var(--text-muted);">Total:</span>
                    <span style="color: var(--primary); font-weight: bold;">RM <?php echo number_format($total_price, 2); ?></span>
                </div>
                <div class="order-row">
                    <span style="color: var(--text-muted);">Delivery Method:</span>
                    <span>Digital Keys (Pending verification)</span>
                </div>

                <div class="purchased-items">
                    <p style="font-weight: bold; margin-bottom: 0.8rem; font-size: 0.9rem; color: var(--primary);">Items Ordered:</p>
                    <?php foreach ($items as $item): ?>
                        <div class="purchased-item-row">
                            <span><?php echo htmlspecialchars($item['name']); ?> <em>(x<?php echo $item['qty']; ?>)</em></span>
                            <span style="color: #fff;">RM <?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <p style="margin-bottom: 2rem; color: var(--text-muted); font-size: 0.95rem;">
                We have logged your order to <strong><?php echo htmlspecialchars($customer_email); ?></strong>. Once our staff validates your transaction, game activation links will appear in your member dashboard.
            </p>
            
            <div style="display: flex; justify-content: center; gap: 1rem;">
                <a href="dashboard.php" class="btn btn-primary"><i class="fa-solid fa-user-astronaut"></i> Member Area</a>
                <a href="../index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Clear local storage cart once checkout is successfully completed -->
    <script>
        localStorage.removeItem('nexus_cart');
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
