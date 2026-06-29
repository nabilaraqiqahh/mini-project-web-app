<?php
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = "";

if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success = "Registration successful! Enter your credentials to log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if (empty($password)) {
        $errors['password'] = "Please enter your password.";
    }
    
    if (empty($errors)) {
        // Query user details from DB
        $stmt = mysqli_prepare($conn, "SELECT user_id, username, fullname, password FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $user_id, $username, $fullname, $hashed_password);
        
        if (mysqli_stmt_fetch($stmt)) {
            // Verify bcrypt password
            if (password_verify($password, $hashed_password)) {
                // Start Session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['fullname'] = $fullname;
                
                header("Location: dashboard.php");
                exit();
            } else {
                $errors['general'] = "Incorrect password. Please try again.";
            }
        } else {
            $errors['general'] = "No account found with that email address.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DysCover Nexus</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            padding: 3rem;
            position: relative;
        }
        .auth-card::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: var(--neon-gradient);
            z-index: -1;
            border-radius: 18px;
            opacity: 0.5;
        }
        .server-error {
            background: rgba(255, 51, 102, 0.15);
            border: 1px solid #ff3366;
            color: #ff5588;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }
        .server-success {
            background: rgba(0, 210, 255, 0.15);
            border: 1px solid var(--primary);
            color: #d1f7ff;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }
        .form-error {
            color: #ff3366;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: block;
        }
    </style>
</head>
<body>
    
    <?php include '../includes/header.php'; ?>

    <main class="auth-container">
        <div class="glass-panel auth-card">
            <div class="text-center" style="margin-bottom: 2rem;">
                <h2 class="gradient-text">Welcome Back</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Enter your credentials to access the Nexus.</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="server-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="server-error"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>
            
            <form id="loginForm" action="login.php" method="POST" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="player@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <?php if (isset($errors['email'])): ?>
                        <span class="form-error"><?php echo $errors['email']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.8rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-muted);">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" style="color: var(--text-muted);">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
            </form>
            
            <p class="text-center" style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/main.js"></script>
</body>
</html>
