<?php
// User Registration Script
// File: register.php

require_once __DIR__ . '/config.php';

// Redirect authenticated users
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$categories_list = [
    'Romance', 'Fantasy', 'Science Fiction', 'Horror', 
    'Mystery & Thriller', 'Literary Fiction', 
    'Biography & Memoir', 'Health & Awareness', 'Animation'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $selected_cats = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Validations
    if ($username === '' || $email === '' || $password === '') {
        $_SESSION['flash'] = ['message' => 'Please fill in all fields.', 'type' => 'error'];
        header("Location: register.php");
        exit;
    } 
    
    if ($password !== $confirm_password) {
        $_SESSION['flash'] = ['message' => 'Passwords do not match.', 'type' => 'error'];
        header("Location: register.php");
        exit;
    } 
    
    try {
        // Check username conflict
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['message' => 'Username is already taken.', 'type' => 'error'];
            header("Location: register.php");
            exit;
        } 
        
        // Check email conflict
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['message' => 'Email address is already registered.', 'type' => 'error'];
            header("Location: register.php");
            exit;
        } 
        
        // Safe BCRYPT password hashing
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Join selected category preferences as comma-separated string
        $preferred_categories = !empty($selected_cats) ? implode(',', $selected_cats) : null;
        
        // Insert secure record
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, preferred_categories) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash, $preferred_categories]);
        
        $_SESSION['flash'] = ['message' => 'Registration successful! You can now log in after administrator approval.', 'type' => 'success'];
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['message' => 'An error occurred during registration. Please try again.', 'type' => 'error'];
        header("Location: register.php");
        exit;
    }
}

$page_title = 'Create Account | BOOK BAZAAR';
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="auth-card" id="register-card">
                <!-- Premium Header -->
                <div class="auth-header">
                    <h3>Get Started</h3>
                    <p class="text-muted-light mb-0 small">Create your BOOK BAZAAR profile in seconds</p>
                </div>
                
                <!-- Registration Form -->
                <div class="auth-body">
                    <form action="register.php" method="POST" id="register-form">
                        <!-- Username Field -->
                        <div class="form-floating mb-3">
                            <input type="text" name="username" class="form-control" id="floatingRegUsername" placeholder="Username" required autocomplete="username">
                            <label for="floatingRegUsername"><i class="fa-solid fa-user me-1 text-primary"></i> Username</label>
                            <div class="form-text ms-1 text-muted-dark small">Must be unique, alphanumeric characters only.</div>
                        </div>

                        <!-- Email Field -->
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="floatingRegEmail" placeholder="Email Address" required autocomplete="email">
                            <label for="floatingRegEmail"><i class="fa-solid fa-envelope me-1 text-primary"></i> Email Address</label>
                        </div>
                        
                        <!-- Password Field -->
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="floatingRegPassword" placeholder="Password" required autocomplete="new-password">
                            <label for="floatingRegPassword"><i class="fa-solid fa-lock me-1 text-primary"></i> Password</label>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="form-floating mb-4">
                            <input type="password" name="confirm_password" class="form-control" id="floatingRegConfirmPassword" placeholder="Confirm Password" required autocomplete="new-password">
                            <label for="floatingRegConfirmPassword"><i class="fa-solid fa-circle-check me-1 text-primary"></i> Confirm Password</label>
                        </div>

                        <!-- Category Preferences Section (Personalized Recommendation objective) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2"><i class="fa-solid fa-heart text-danger me-1"></i> Choose Your Favorite Book Genres</label>
                            <p class="text-muted-dark small mb-3">Select the categories you enjoy reading most. We will use these to suggest personalized recommendations on your home screen!</p>
                            <div class="row row-cols-2 g-2">
                                <?php foreach ($categories_list as $cat): ?>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($cat); ?>" id="cat_<?php echo str_replace(' & ', '_', strtolower($cat)); ?>">
                                            <label class="form-check-label text-dark small" for="cat_<?php echo str_replace(' & ', '_', strtolower($cat)); ?>">
                                                <?php echo htmlspecialchars($cat); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Sign Up Button -->
                        <button type="submit" class="btn btn-premium-primary w-100 py-3 fs-5 mb-3" id="register-submit-btn">
                            Create Account
                        </button>
                        
                        <!-- Toggle to login -->
                        <div class="text-center">
                            <span class="text-muted-dark small">Already have an account? </span>
                            <a href="login.php" class="text-primary fw-semibold small text-decoration-none">Sign In Here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
