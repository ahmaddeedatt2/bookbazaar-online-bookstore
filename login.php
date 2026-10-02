<?php
// User Authentication Script
// File: login.php

require_once __DIR__ . '/config.php';

// Redirect authenticated users
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($username_or_email === '' || $password === '') {
        $_SESSION['flash'] = ['message' => 'Please provide both username/email and password.', 'type' => 'error'];
    } else {
        try {
            // Find user by username or email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username_or_email, $username_or_email]);
            $user = $stmt->fetch();
            
            // Verify password using secure native function
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                update_session_cart_count($pdo); // Sync cart count
                
                $_SESSION['flash'] = ['message' => "Welcome back, " . $user['username'] . "!", 'type' => 'success'];
                header("Location: index.php");
                exit;
            } else {
                $_SESSION['flash'] = ['message' => 'Invalid username/email or password.', 'type' => 'error'];
            }
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['message' => 'Database authentication error occurred.', 'type' => 'error'];
        }
    }
    // Reload login page to show error
    header("Location: login.php");
    exit;
}

$page_title = 'Sign In | BOOK BAZAAR';
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card" id="login-card">
                <!-- Premium Header -->
                <div class="auth-header">
                    <h3>Welcome Back</h3>
                    <p class="text-muted-light mb-0 small">Access your customer account and cart</p>
                </div>
                
                <!-- Login Form -->
                <div class="auth-body">
                    <form action="login.php" method="POST" id="login-form">
                        <!-- Username/Email Field -->
                        <div class="form-floating mb-3">
                            <input type="text" name="username" class="form-control" id="floatingUsername" placeholder="Username" required autocomplete="username">
                            <label for="floatingUsername"><i class="fa-solid fa-user me-1 text-primary"></i> Username or Email</label>
                        </div>
                        
                        <!-- Password Field -->
                        <div class="form-floating mb-4">
                            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required autocomplete="current-password">
                            <label for="floatingPassword"><i class="fa-solid fa-lock me-1 text-primary"></i> Password</label>
                        </div>

                        <!-- Sign In Button -->
                        <button type="submit" class="btn btn-premium-primary w-100 py-3 fs-5 mb-3" id="login-submit-btn">
                            Sign In
                        </button>
                        
                        <!-- Toggle to register -->
                        <div class="text-center">
                            <span class="text-muted-dark small">New to BOOK BAZAAR? </span>
                            <a href="register.php" class="text-primary fw-semibold small text-decoration-none">Create an Account</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
