<?php
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user profile details to prefill the checkout form
$stmt = mysqli_prepare($conn, "SELECT email, fullname, shipping_address FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $email, $fullname, $shipping_address);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Split fullname into first name and last name
$name_parts = explode(' ', $fullname, 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - DysCover Nexus</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .checkout-container {
            margin: 4rem auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        .checkout-form {
            padding: 2rem;
        }
        .form-section {
            margin-bottom: 2.5rem;
        }
        .form-section h3 {
            margin-bottom: 1.5rem;
            color: var(--primary);
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 0.5rem;
        }
        .bank-details-panel {
            background: rgba(0, 210, 255, 0.05);
            border: 1px dashed var(--primary);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .bank-details-panel p {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        .checkout-item-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }
        .checkout-item-row img {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--glass-border);
        }
        @media (max-width: 992px) {
            .checkout-container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="checkout-container">
            <div class="glass-panel checkout-form">
                <form action="place_order.php" method="POST" enctype="multipart/form-data" id="checkoutForm">
                    <!-- Hidden input to serialize client-side cart data to POST -->
                    <input type="hidden" name="cart_data" id="cartDataInput">

                    <div class="form-section">
                        <h3>1. Billing & Delivery Information</h3>
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address (For Digital Key Delivery)</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Shipping Address / Billing Address</label>
                            <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($shipping_address); ?>" placeholder="No. 12, Jalan Hang Tuah" required>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" placeholder="Melaka" required>
                            </div>
                            <div class="form-group">
                                <label for="zipcode">Zip / Postal Code</label>
                                <input type="text" id="zipcode" name="zipcode" class="form-control" placeholder="75450" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>2. Payment Method: Bank Transfer</h3>
                        
                        <div class="bank-details-panel">
                            <p><strong>Bank:</strong> Malayan Banking Berhad (Maybank)</p>
                            <p><strong>Account Name:</strong> DYSCOVER NEXUS CO.</p>
                            <p><strong>Account Number:</strong> 5123-4567-8901</p>
                            <p style="color: var(--primary); font-size: 0.85rem; margin-top: 0.8rem;">
                                <i class="fa-solid fa-circle-info"></i> Please transfer the exact total amount shown on the right, screenshot the transaction, and upload the receipt below.
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_receipt">Upload Payment Receipt (JPEG, PNG, or PDF)</label>
                            <input type="file" id="payment_receipt" name="payment_receipt" class="form-control" accept="image/*,application/pdf" style="padding-top: 0.7rem;" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.2rem; padding: 1rem;"><i class="fa-solid fa-receipt"></i> Complete Purchase & Upload Receipt</button>
                </form>
            </div>

            <!-- Dynamic Order Summary Column -->
            <div class="glass-panel" style="padding: 2rem; height: fit-content;">
                <h3 style="margin-bottom: 2rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 1rem;">Order Summary</h3>
                
                <div id="checkoutItemsList">
                    <!-- Loaded dynamically via JS -->
                </div>
                
                <div style="border-top: 1px solid var(--glass-border); padding-top: 1.5rem; margin-top: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--text-muted);">
                        <span>Subtotal</span>
                        <span id="subtotalVal">RM 0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--text-muted);">
                        <span>Tax (8%)</span>
                        <span id="taxVal">RM 0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 1rem; font-size: 1.2rem; font-weight: bold; color: var(--primary);">
                        <span>Total</span>
                        <span id="totalVal">RM 0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const listContainer = document.getElementById('checkoutItemsList');
            const cartDataInput = document.getElementById('cartDataInput');
            
            let cart = JSON.parse(localStorage.getItem('nexus_cart')) || [];
            
            if (cart.length === 0) {
                listContainer.innerHTML = '<p style="color:var(--text-muted);">Your cart is empty.</p>';
                setTimeout(() => { window.location.href = 'product.php'; }, 2000);
                return;
            }

            // Serialize cart list for POST request processing
            cartDataInput.value = JSON.stringify(cart);

            listContainer.innerHTML = '';
            let subtotal = 0;

            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                const itemRow = document.createElement('div');
                itemRow.className = 'checkout-item-row';
                itemRow.innerHTML = `
                    <img src="${item.img}" alt="${item.name}">
                    <div>
                        <div style="font-weight: bold; font-size: 0.95rem;">${item.name}</div>
                        <div style="color: var(--text-muted); font-size: 0.8rem;">Qty: ${item.quantity}</div>
                    </div>
                    <div style="margin-left: auto; font-weight: 500;">RM ${itemTotal.toFixed(2)}</div>
                `;
                listContainer.appendChild(itemRow);
            });

            // Update Summary calculations
            const tax = subtotal * 0.08;
            const total = subtotal + tax;

            document.getElementById('subtotalVal').innerText = `RM ${subtotal.toFixed(2)}`;
            document.getElementById('taxVal').innerText = `RM ${tax.toFixed(2)}`;
            document.getElementById('totalVal').innerText = `RM ${total.toFixed(2)}`;
        });
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
