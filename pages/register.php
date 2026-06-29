<?php
require_once __DIR__ . '/../includes/db.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Server-side validation
    if (strlen($username) < 3) {
        $errors['username'] = "Username must be at least 3 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = "Passwords do not match.";
    }
    
    // Check if user already exists
    if (empty($errors)) {
        // Prepare select query to check uniqueness
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors['general'] = "Username or email is already registered.";
        }
        mysqli_stmt_close($stmt);
    }
    
    // Insert new user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $fullname = $username; // Default fullname to username initially
        
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, fullname, email, password) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $username, $fullname, $email, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Registration successful! You can now log in.";
            // Redirect to login page after a brief delay, or directly
            header("Location: login.php?registered=1");
            exit();
        } else {
            $errors['general'] = "Registration failed. Please try again later.";
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
    <title>Register - DysCover Nexus</title>
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
            max-width: 450px;
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
        #passwordStrength {
            font-size: 0.8rem;
            margin-top: 0.3rem;
            font-weight: bold;
            display: block;
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
                <h2 class="gradient-text">Create Account</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Join the Nexus and transform your learning journey.</p>
            </div>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="server-error"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <form id="registerForm" action="register.php" method="POST" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="PlayerOne" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <?php if (isset($errors['username'])): ?>
                        <span class="form-error"><?php echo $errors['username']; ?></span>
                    <?php endif; ?>
                </div>
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
                    <span id="passwordStrength" style="color: var(--primary);"></span>
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="••••••••">
                    <?php if (isset($errors['confirmPassword'])): ?>
                        <span class="form-error"><?php echo $errors['confirmPassword']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div style="margin-bottom: 1.5rem; font-size: 0.8rem; color: var(--text-muted);">
                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="terms" required style="margin-top: 0.2rem;" checked> 
                        <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-user-plus"></i> Register</button>
            </form>
            
            <p class="text-center" style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted);">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/main.js"></script>
    <script>
        // Password strength checker UI logic
        const passwordInput = document.getElementById('password');
        const strengthDisplay = document.getElementById('passwordStrength');
        
        if (passwordInput && strengthDisplay) {
            passwordInput.addEventListener('input', () => {
                const val = passwordInput.value;
                if (val.length === 0) strengthDisplay.innerText = '';
                else if (val.length < 6) {
                    strengthDisplay.innerText = 'Weak';
                    strengthDisplay.style.color = '#ff3366';
                }
                else if (val.length < 10) {
                    strengthDisplay.innerText = 'Medium';
                    strengthDisplay.style.color = '#ffaa00';
                }
                else {
                    strengthDisplay.innerText = 'Strong';
                    strengthDisplay.style.color = 'var(--primary)';
                }
            });
        }
    </script>
</body>
</html>
