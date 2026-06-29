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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: product.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$zipcode = trim($_POST['zipcode'] ?? '');
$cart_data_json = $_POST['cart_data'] ?? '';

$customer_name = $first_name . ' ' . $last_name;
$full_address = $address . ', ' . $city . ', ' . $zipcode;

$errors = [];

// Validate fields
if (empty($first_name) || empty($last_name) || empty($email) || empty($address) || empty($city) || empty($zipcode)) {
    $errors[] = "All profile and billing fields are required.";
}

// Parse cart data
$cart = json_decode($cart_data_json, true);
if (empty($cart)) {
    $errors[] = "Your shopping cart is empty.";
}

// Validate file upload (payment receipt)
$receipt_filename = null;
if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['payment_receipt']['tmp_name'];
    $file_name = $_FILES['payment_receipt']['name'];
    $file_size = $_FILES['payment_receipt']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (!in_array($file_ext, $allowed_exts)) {
        $errors[] = "Invalid receipt format. Allowed formats: JPG, JPEG, PNG, PDF.";
    }
    
    if ($file_size > 5 * 1024 * 1024) { // Limit size to 5MB
        $errors[] = "Receipt file size exceeds 5MB limit.";
    }
    
    if (empty($errors)) {
        // Create uploads folder dynamically if it doesn't exist
        $upload_dir = __DIR__ . '/../uploads/receipts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename to avoid conflict
        $receipt_filename = 'receipt_' . $user_id . '_' . time() . '.' . $file_ext;
        $dest_path = $upload_dir . $receipt_filename;
        
        if (!move_uploaded_file($file_tmp, $dest_path)) {
            $errors[] = "Failed to upload receipt. Please try again.";
        }
    }
} else {
    $errors[] = "Please upload a bank transaction receipt file.";
}

if (!empty($errors)) {
    // Render error page
    echo "<div style='color:#ff3366; background:#111; padding:2rem; font-family:sans-serif; text-align:center;'>";
    echo "<h2>Checkout Error</h2>";
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    echo "<a href='checkout.php' style='color:#00d2ff;'>Return to Checkout</a>";
    echo "</div>";
    exit();
}

// Compute total price
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.08;
$total_price = $subtotal + $tax;

// Begin Database Transaction to ensure consistency
mysqli_begin_transaction($conn);

try {
    // Insert into orders table
    $order_stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, customer_name, customer_email, total_price, shipping_address, payment_method, receipt_file, order_status) VALUES (?, ?, ?, ?, ?, 'Bank Transfer', ?, 'Pending')");
    mysqli_stmt_bind_param($order_stmt, "issdss", $user_id, $customer_name, $email, $total_price, $full_address, $receipt_filename);
    mysqli_stmt_execute($order_stmt);
    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($order_stmt);
    
    // Insert individual items into order_items table
    foreach ($cart as $item) {
        $product_code = $item['id'];
        $qty = intval($item['quantity']);
        $item_price = floatval($item['price']);
        
        // Find product_id
        $prod_stmt = mysqli_prepare($conn, "SELECT product_id FROM products WHERE product_code = ?");
        mysqli_stmt_bind_param($prod_stmt, "s", $product_code);
        mysqli_stmt_execute($prod_stmt);
        mysqli_stmt_bind_result($prod_stmt, $product_id);
        mysqli_stmt_fetch($prod_stmt);
        mysqli_stmt_close($prod_stmt);
        
        if ($product_id) {
            $item_stmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($item_stmt, "iiid", $order_id, $product_id, $qty, $item_price);
            mysqli_stmt_execute($item_stmt);
            mysqli_stmt_close($item_stmt);
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Redirect to confirmation
    header("Location: confirmation.php?order_id=" . $order_id);
    exit();
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<div style='color:#ff3366; background:#111; padding:2rem; font-family:sans-serif; text-align:center;'>";
    echo "<h2>Database Error</h2>";
    echo "<p>Something went wrong during checkout. Your account has not been charged.</p>";
    echo "<a href='checkout.php' style='color:#00d2ff;'>Return to Checkout</a>";
    echo "</div>";
    exit();
}
?>
