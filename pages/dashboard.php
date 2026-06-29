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
$errors = [];
$success_msg = "";

// ---------------------------------------------------------
// POST Actions Handler
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. UPDATE PROFILE
    if ($action === 'update_profile') {
        $fullname = trim($_POST['fullname'] ?? '');
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $new_password = $_POST['new_password'] ?? '';

        if (empty($fullname)) {
            $errors[] = "Full Name cannot be empty.";
        }

        if (empty($errors)) {
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $errors[] = "New password must be at least 6 characters.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = mysqli_prepare($conn, "UPDATE users SET fullname = ?, shipping_address = ?, password = ? WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "sssi", $fullname, $shipping_address, $hashed_password, $user_id);
                }
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE users SET fullname = ?, shipping_address = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($stmt, "ssi", $fullname, $shipping_address, $user_id);
            }

            if (isset($stmt)) {
                if (mysqli_stmt_execute($stmt)) {
                    $success_msg = "Profile updated successfully.";
                    $_SESSION['fullname'] = $fullname; // Refresh session cache
                } else {
                    $errors[] = "Failed to update profile. Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // 2. DELETE ACCOUNT
    elseif ($action === 'delete_account') {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            // Destroy session and redirect
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
            session_destroy();
            header("Location: ../index.php?deleted_account=1");
            exit();
        } else {
            $errors[] = "Failed to delete account.";
            mysqli_stmt_close($stmt);
        }
    }

    // 3. ADD REVIEW (CREATE)
    elseif ($action === 'add_review') {
        $rating = intval($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');

        if (empty($comment)) {
            $errors[] = "Review comment cannot be empty.";
        }
        if ($rating < 1 || $rating > 5) {
            $errors[] = "Invalid rating score.";
        }

        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iis", $user_id, $rating, $comment);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Thank you for your feedback! Review posted.";
            } else {
                $errors[] = "Failed to submit review.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // 4. UPDATE REVIEW (UPDATE)
    elseif ($action === 'update_review') {
        $review_id = intval($_POST['review_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');

        if (empty($comment)) {
            $errors[] = "Review comment cannot be empty.";
        }
        if ($rating < 1 || $rating > 5) {
            $errors[] = "Invalid rating score.";
        }

        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, "UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, "isii", $rating, $comment, $review_id, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Review updated successfully.";
            } else {
                $errors[] = "Failed to update review.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // 5. DELETE REVIEW (DELETE)
    elseif ($action === 'delete_review') {
        $review_id = intval($_POST['review_id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $review_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Review deleted successfully.";
        } else {
            $errors[] = "Failed to delete review.";
        }
        mysqli_stmt_close($stmt);
    }
}

// ---------------------------------------------------------
// Load Data for Dashboard View
// ---------------------------------------------------------

// Query user details
$user_stmt = mysqli_prepare($conn, "SELECT username, email, fullname, shipping_address, created_at FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
mysqli_stmt_bind_result($user_stmt, $username, $email, $fullname, $shipping_address, $created_at);
mysqli_stmt_fetch($user_stmt);
mysqli_stmt_close($user_stmt);

// Query Order History
$orders = [];
$order_query = "SELECT order_id, total_price, receipt_file, order_status, order_date FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$order_res = mysqli_query($conn, $order_query);
if ($order_res) {
    while ($row = mysqli_fetch_assoc($order_res)) {
        $orders[] = $row;
    }
}

// Query User Reviews
$reviews = [];
$review_query = "SELECT review_id, rating, comment, created_at FROM reviews WHERE user_id = $user_id ORDER BY created_at DESC";
$review_res = mysqli_query($conn, $review_query);
if ($review_res) {
    while ($row = mysqli_fetch_assoc($review_res)) {
        $reviews[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Area - DysCover Nexus</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin: 4rem auto;
        }
        .dashboard-sidebar {
            padding: 2.5rem;
            height: fit-content;
            text-align: center;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), #7000ff);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 2px solid var(--primary);
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.3);
        }
        .dashboard-content-panel {
            padding: 3rem;
        }
        .dashboard-section {
            margin-bottom: 3rem;
        }
        .dashboard-section h3 {
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
        .alert-error {
            background: rgba(255, 51, 102, 0.15);
            border: 1px solid #ff3366;
            color: #ff5588;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .alert-success {
            background: rgba(0, 210, 255, 0.15);
            border: 1px solid var(--primary);
            color: #d1f7ff;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .status-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-Pending { background: rgba(255,170,0,0.2); color: #ffaa00; border: 1px solid #ffaa00; }
        .status-Approved { background: rgba(0,255,136,0.2); color: #00ff88; border: 1px solid #00ff88; }
        .status-Completed { background: rgba(0,210,255,0.2); color: var(--primary); border: 1px solid var(--primary); }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        .history-table th, .history-table td {
            text-align: left;
            padding: 0.8rem;
            border-bottom: 1px solid var(--glass-border);
        }
        .history-table th {
            color: var(--text-muted);
            font-weight: 500;
        }
        .review-card {
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--glass-border);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            position: relative;
        }
        .review-card .actions {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            display: flex;
            gap: 0.5rem;
        }
        @media (max-width: 992px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    
    <?php include '../includes/header.php'; ?>

    <main class="container">
        
        <!-- Alerts Display -->
        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <p><strong>Please correct the following errors:</strong></p>
                <ul style="padding-left:1.5rem; margin-top:0.5rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            
            <!-- Sidebar: User Summary -->
            <div class="glass-panel dashboard-sidebar">
                <div class="profile-avatar">
                    <i class="fa-solid fa-user-astronaut"></i>
                </div>
                <h2><?php echo htmlspecialchars($fullname); ?></h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.3rem;">@<?php echo htmlspecialchars($username); ?></p>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.8rem;">
                    <i class="fa-solid fa-calendar-days"></i> Joined <?php echo date("M Y", strtotime($created_at)); ?>
                </p>
                
                <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 2rem 0;">

                <nav style="display: flex; flex-direction: column; gap: 0.8rem; text-align: left;">
                    <a href="#orders" style="color: #fff;"><i class="fa-solid fa-receipt" style="width: 25px; color: var(--primary);"></i> Order History</a>
                    <a href="#feedback" style="color: #fff;"><i class="fa-solid fa-comment-dots" style="width: 25px; color: var(--primary);"></i> My Reviews</a>
                    <a href="#settings" style="color: #fff;"><i class="fa-solid fa-gears" style="width: 25px; color: var(--primary);"></i> Profile Settings</a>
                </nav>

                <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 2rem 0;">
                
                <!-- ACCOUNT DEACTIVATION (DELETE) -->
                <form action="dashboard.php" method="POST" onsubmit="return confirm('WARNING: Are you absolutely sure you want to delete your account? This action cannot be undone.');">
                    <input type="hidden" name="action" value="delete_account">
                    <button type="submit" class="btn btn-secondary" style="width: 100%; border-color: #ff3366; color: #ff3366; background: none;">
                        <i class="fa-solid fa-user-slash"></i> Close My Account
                    </button>
                </form>
            </div>

            <!-- Content Area: Tabs/Panels -->
            <div class="glass-panel dashboard-content-panel">
                
                <!-- SECTION 1: ORDER HISTORY -->
                <div class="dashboard-section" id="orders">
                    <h3>Order History (View)</h3>
                    <?php if (empty($orders)): ?>
                        <p style="color: var(--text-muted);">You haven't placed any orders yet. Visit the <a href="product.php" style="color: var(--primary);">store</a> to explore products.</p>
                    <?php else: ?>
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total Paid</th>
                                    <th>Status</th>
                                    <th>Uploaded Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $ord): ?>
                                    <tr>
                                        <td style="font-weight: bold;">#DYS-<?php echo $ord['order_id']; ?></td>
                                        <td><?php echo date("d/m/Y", strtotime($ord['order_date'])); ?></td>
                                        <td style="color: var(--primary); font-weight: bold;">RM <?php echo number_format($ord['total_price'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $ord['order_status']; ?>">
                                                <?php echo htmlspecialchars($ord['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($ord['receipt_file']): ?>
                                                <a href="../uploads/receipts/<?php echo urlencode($ord['receipt_file']); ?>" target="_blank" style="color: var(--primary);"><i class="fa-solid fa-file-image"></i> View Receipt</a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">None</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- SECTION 2: REVIEWS CRUD -->
                <div class="dashboard-section" id="feedback">
                    <h3>Game Testimonials (CRUD Feedback)</h3>
                    
                    <!-- Form to Post Feedback (CREATE) -->
                    <form action="dashboard.php" method="POST" class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                        <input type="hidden" name="action" value="add_review">
                        <h4 style="margin-bottom: 1rem; color: #fff;">Write a Testimonial</h4>
                        
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <select id="rating" name="rating" class="form-control" style="background:#111; color:#fff;">
                                <option value="5">★★★★★ (5 Stars)</option>
                                <option value="4">★★★★☆ (4 Stars)</option>
                                <option value="3">★★★☆☆ (3 Stars)</option>
                                <option value="2">★★☆☆☆ (2 Stars)</option>
                                <option value="1">★☆☆☆☆ (1 Star)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Comment</label>
                            <textarea id="comment" name="comment" class="form-control" rows="3" placeholder="Tell us how DysCover helped your child..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-size: 0.9rem;">Submit Testimonial</button>
                    </form>

                    <!-- Feed of existing feedback (READ, UPDATE, DELETE) -->
                    <h4 style="margin-bottom: 1rem; color: #fff;">Your Submissions</h4>
                    <?php if (empty($reviews)): ?>
                        <p style="color: var(--text-muted);">You haven't left any reviews yet.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-card" id="rev-card-<?php echo $rev['review_id']; ?>">
                                <div style="color: #ffd700; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?>
                                </div>
                                <p style="font-style: italic; color: #fff; font-size: 0.95rem;">"<?php echo htmlspecialchars($rev['comment']); ?>"</p>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.8rem;">
                                    Posted on <?php echo date("d M Y", strtotime($rev['created_at'])); ?>
                                </span>
                                
                                <div class="actions">
                                    <!-- Edit Trigger Button -->
                                    <button class="btn btn-secondary" onclick="toggleEditForm(<?php echo $rev['review_id']; ?>)" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-color: var(--primary); color: var(--primary); background:none;"><i class="fa-solid fa-pen"></i></button>
                                    
                                    <!-- Delete Button (DELETE) -->
                                    <form action="dashboard.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                        <input type="hidden" name="action" value="delete_review">
                                        <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-color:#ff3366; color:#ff3366; background:none;"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>

                                <!-- Hidden Inline Edit Form (UPDATE) -->
                                <form action="dashboard.php" method="POST" id="edit-form-<?php echo $rev['review_id']; ?>" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px dashed var(--glass-border);">
                                    <input type="hidden" name="action" value="update_review">
                                    <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                                    
                                    <div class="form-group">
                                        <label>Edit Rating</label>
                                        <select name="rating" class="form-control" style="background:#111; color:#fff;">
                                            <option value="5" <?php if($rev['rating'] == 5) echo 'selected'; ?>>★★★★★</option>
                                            <option value="4" <?php if($rev['rating'] == 4) echo 'selected'; ?>>★★★★☆</option>
                                            <option value="3" <?php if($rev['rating'] == 3) echo 'selected'; ?>>★★★☆☆</option>
                                            <option value="2" <?php if($rev['rating'] == 2) echo 'selected'; ?>>★★☆☆☆</option>
                                            <option value="1" <?php if($rev['rating'] == 1) echo 'selected'; ?>>★☆☆☆☆</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Edit Comment</label>
                                        <textarea name="comment" class="form-control" rows="3" required><?php echo htmlspecialchars($rev['comment']); ?></textarea>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Save Changes</button>
                                        <button type="button" class="btn btn-secondary" onclick="toggleEditForm(<?php echo $rev['review_id']; ?>)" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- SECTION 3: PROFILE SETTINGS -->
                <div class="dashboard-section" id="settings">
                    <h3>Profile Settings (Update)</h3>
                    
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label>Email Address (Cannot be changed)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled style="opacity: 0.5;">
                        </div>
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo htmlspecialchars($fullname); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Default Shipping/Billing Address</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" placeholder="Enter your delivery location..."><?php echo htmlspecialchars($shipping_address); ?></textarea>
                        </div>
                        
                        <div class="form-group" style="margin-top: 2rem;">
                            <label for="new_password">Change Password (Leave blank to keep current)</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 0.95rem; margin-top: 1rem;"><i class="fa-solid fa-floppy-disk"></i> Save Profile Settings</button>
                    </form>
                </div>

            </div>

        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function toggleEditForm(reviewId) {
            const form = document.getElementById(`edit-form-${reviewId}`);
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
