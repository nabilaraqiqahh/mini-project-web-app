<?php
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch the core game details
$core_stmt = mysqli_prepare($conn, "SELECT product_code, product_name, product_subtitle, description, feature_1, feature_2, feature_3, price, image FROM products WHERE product_code = 'core_edition'");
mysqli_stmt_execute($core_stmt);
mysqli_stmt_bind_result($core_stmt, $core_code, $core_name, $core_subtitle, $core_description, $core_f1, $core_f2, $core_f3, $core_price, $core_image);
mysqli_stmt_fetch($core_stmt);
mysqli_stmt_close($core_stmt);

// Fetch expansions / DLCs
$dlc_query = "SELECT product_code, product_name, description, price, image FROM products WHERE product_code != 'core_edition'";
$dlc_result = mysqli_query($conn, $dlc_query);
$dlcs = [];
if ($dlc_result) {
    while ($row = mysqli_fetch_assoc($dlc_result)) {
        $dlcs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store - DysCover Nexus</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-hero {
            padding: 4rem 0;
        }
        .product-gallery img {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--primary);
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.2);
        }
        .price-tag {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            margin: 1.5rem 0;
            font-family: var(--font-heading);
        }
        .rating {
            color: #ffd700;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .rec-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 16px 16px 0 0;
        }
        .rec-card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
        }
        /* Notification toast style */
        .toast-notif {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(5, 5, 7, 0.9);
            border: 1px solid var(--primary);
            box-shadow: 0 0 15px rgba(0, 210, 255, 0.4);
            color: #fff;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-family: var(--font-heading);
            font-size: 0.9rem;
            z-index: 1000;
            display: none;
        }
    </style>
</head>
<body>
    
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <!-- Core Product Section -->
        <?php if ($core_name): ?>
        <section class="product-hero grid-2" data-id="<?php echo htmlspecialchars($core_code); ?>" data-name="<?php echo htmlspecialchars($core_name); ?>" data-price="<?php echo htmlspecialchars($core_price); ?>" data-img="<?php echo htmlspecialchars($core_image); ?>">
            <div class="product-gallery">
                <img src="<?php echo htmlspecialchars($core_image); ?>" alt="<?php echo htmlspecialchars($core_name); ?>">
            </div>
            <div class="product-details glass-panel" style="padding: 3rem;">
                <span style="color: var(--primary); font-family: var(--font-heading); font-size: 0.9rem; letter-spacing: 2px; text-transform: uppercase;"><?php echo htmlspecialchars($core_subtitle); ?></span>
                <h1 class="gradient-text" style="margin-top: 0.5rem;"><?php echo htmlspecialchars($core_name); ?></h1>
                
                <div class="rating" style="margin-top: 1rem;">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                    <span style="color: var(--text-muted); font-size: 0.9rem; margin-left: 0.5rem;">(1,284 reviews)</span>
                </div>

                <p style="color: var(--text-muted); margin-bottom: 1.5rem; margin-top: 1rem;"><?php echo htmlspecialchars($core_description); ?></p>
                
                <ul style="color: #fff; margin-bottom: 2rem; padding-left: 1rem; line-height: 2;">
                    <?php if ($core_f1): ?><li><i class="fa-solid fa-circle-check" style="color: var(--primary); margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($core_f1); ?></li><?php endif; ?>
                    <?php if ($core_f2): ?><li><i class="fa-solid fa-circle-check" style="color: var(--primary); margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($core_f2); ?></li><?php endif; ?>
                    <?php if ($core_f3): ?><li><i class="fa-solid fa-circle-check" style="color: var(--primary); margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($core_f3); ?></li><?php endif; ?>
                </ul>

                <div class="price-tag">RM <?php echo number_format($core_price, 2); ?></div>
                
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary add-to-cart-btn" style="flex: 1;"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
                    <button class="btn btn-secondary"><i class="fa-regular fa-heart"></i></button>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Recommended / DLCs Section -->
        <section class="section-padding">
            <h2 class="gradient-text" style="margin-bottom: 2rem;">Recommended Expansions</h2>
            <div class="grid-3">
                <?php foreach ($dlcs as $dlc): ?>
                    <div class="glass-panel rec-card" data-id="<?php echo htmlspecialchars($dlc['product_code']); ?>" data-name="<?php echo htmlspecialchars($dlc['product_name']); ?>" data-price="<?php echo htmlspecialchars($dlc['price']); ?>" data-img="<?php echo htmlspecialchars($dlc['image']); ?>">
                        <img src="<?php echo htmlspecialchars($dlc['image']); ?>" alt="<?php echo htmlspecialchars($dlc['product_name']); ?>">
                        <div class="rec-card-body">
                            <div>
                                <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($dlc['product_name']); ?></h3>
                                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($dlc['description']); ?></p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                <span style="font-weight: bold; color: var(--primary); font-family: var(--font-heading);">RM <?php echo number_format($dlc['price'], 2); ?></span>
                                <button class="btn btn-secondary add-to-cart-btn" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Add</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <div id="toast" class="toast-notif">Item added to Nexus cart!</div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/main.js"></script>
    
    <script>
        // Client-side cart persistence via localStorage
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const targetEl = this.closest('[data-id]');
                const product = {
                    id: targetEl.getAttribute('data-id'),
                    name: targetEl.getAttribute('data-name'),
                    price: parseFloat(targetEl.getAttribute('data-price')),
                    img: targetEl.getAttribute('data-img'),
                    quantity: 1
                };

                let cart = JSON.parse(localStorage.getItem('nexus_cart')) || [];
                const existingIndex = cart.findIndex(item => item.id === product.id);

                if (existingIndex > -1) {
                    cart[existingIndex].quantity += 1;
                } else {
                    cart.push(product);
                }

                localStorage.setItem('nexus_cart', JSON.stringify(cart));
                
                // Trigger Toast Animation
                const toast = document.getElementById('toast');
                toast.innerText = `${product.name} added to cart!`;
                toast.style.display = 'block';
                setTimeout(() => { toast.style.display = 'none'; }, 2500);
            });
        });
    </script>
</body>
</html>
