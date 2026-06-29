<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - DysCover Nexus</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-container {
            margin: 4rem auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        .cart-items {
            padding: 2rem;
        }
        .cart-item {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--glass-border);
            align-items: center;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .item-details {
            flex-grow: 1;
        }
        .item-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary);
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(0,0,0,0.3);
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            border: 1px solid var(--glass-border);
        }
        .quantity-control button {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .cart-summary {
            padding: 2rem;
            height: fit-content;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: var(--text-muted);
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--glass-border);
            font-size: 1.2rem;
            font-weight: bold;
            color: #fff;
        }
        .empty-msg {
            text-align: center;
            padding: 3rem 0;
            color: var(--text-muted);
            font-family: var(--font-heading);
        }
        @media (max-width: 992px) {
            .cart-container { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .cart-item { flex-direction: column; text-align: center; }
            .cart-item img { width: 100%; max-width: 200px; }
            .quantity-control { justify-content: center; margin: 1rem 0; }
        }
    </style>
</head>
<body>
    
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <h2 class="gradient-text" style="margin-top: 3rem;">Your Nexus Cart</h2>
        <div class="cart-container">
            <div class="glass-panel cart-items" id="cartItemsList">
                <!-- Javascript will load products dynamically here -->
            </div>

            <div class="glass-panel cart-summary">
                <h3 style="margin-bottom: 2rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 1rem;">Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotalVal">RM 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Estimated Tax (8%)</span>
                    <span id="taxVal">RM 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Digital Delivery</span>
                    <span>Free</span>
                </div>
                <div class="summary-row" style="margin-top: 1rem;">
                    <input type="text" class="form-control" placeholder="Promo Code" style="width: 70%; display: inline-block;">
                    <button class="btn btn-secondary" style="padding: 0.8rem 1rem;">Apply</button>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span style="color: var(--primary);" id="totalVal">RM 0.00</span>
                </div>
                <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 2rem; text-align: center;">Secure Checkout <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function renderCart() {
            const listContainer = document.getElementById('cartItemsList');
            let cart = JSON.parse(localStorage.getItem('nexus_cart')) || [];
            
            if (cart.length === 0) {
                listContainer.innerHTML = `<div class="empty-msg"><h3>Your cart is empty</h3><p style='margin-top:1rem;'>Browse our expansions page to load contents.</p></div>`;
                updateSummary(0);
                return;
            }

            listContainer.innerHTML = '';
            let subtotal = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                const itemRow = document.createElement('div');
                itemRow.className = 'cart-item';
                itemRow.innerHTML = `
                    <img src="${item.img}" alt="${item.name}">
                    <div class="item-details">
                        <h3>${item.name}</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Digital Download - PC/Mac</p>
                    </div>
                    <div class="quantity-control">
                        <button onclick="changeQty(${index}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button onclick="changeQty(${index}, 1)">+</button>
                    </div>
                    <div class="item-price">RM ${itemTotal.toFixed(2)}</div>
                    <button class="btn" onclick="removeItem(${index})" style="padding: 0.5rem; color: #ff3366;"><i class="fa-solid fa-trash"></i></button>
                `;
                listContainer.appendChild(itemRow);
            });

            updateSummary(subtotal);
        }

        function changeQty(index, amount) {
            let cart = JSON.parse(localStorage.getItem('nexus_cart')) || [];
            cart[index].quantity += amount;
            
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            
            localStorage.setItem('nexus_cart', JSON.stringify(cart));
            renderCart();
        }

        function removeItem(index) {
            let cart = JSON.parse(localStorage.getItem('nexus_cart')) || [];
            cart.splice(index, 1);
            localStorage.setItem('nexus_cart', JSON.stringify(cart));
            renderCart();
        }

        function updateSummary(subtotal) {
            const tax = subtotal * 0.08;
            const total = subtotal + tax;

            document.getElementById('subtotalVal').innerText = `RM ${subtotal.toFixed(2)}`;
            document.getElementById('taxVal').innerText = `RM ${tax.toFixed(2)}`;
            document.getElementById('totalVal').innerText = `RM ${total.toFixed(2)}`;
        }

        document.addEventListener('DOMContentLoaded', renderCart);
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
