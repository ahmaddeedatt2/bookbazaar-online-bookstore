<?php
// Shared HTML Header & Responsive Navbar Component
// File: header.php
require_once __DIR__ . '/config.php';

// Dynamically compute base URL folder to make absolute paths work in subdirectories
$project_folder = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__));
if ($project_folder !== '' && $project_folder[0] !== '/') {
    $project_folder = '/' . $project_folder;
}
$base_url = rtrim($project_folder, '/') . '/';

// Fetch profile picture and approval status if logged in
$nav_profile_pic = null;
$user_approved = false;
$is_admin_user = false;

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT profile_picture, is_approved, username FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_row = $stmt->fetch();
        if ($user_row) {
            $nav_profile_pic = $user_row['profile_picture'];
            $user_approved = (int)$user_row['is_approved'] === 1;
            $is_admin_user = $user_row['username'] === 'admin';
        }
    } catch (PDOException $e) {
        // Fail silently
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'BOOK BAZAAR | Online Bookstore'; ?></title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Explore and purchase romance, fantasy, sci-fi, horror, biography, and animation books at BOOK BAZAAR, the premier online bookstore.">
    
    <!-- Google Fonts (Outfit & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- FontAwesome for Premium Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>static/css/style.css">
</head>
<body>

    <!-- Responsive Premium Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand brand-font" href="<?php echo $base_url; ?>index.php" id="nav-logo">
                <i class="fa-solid fa-scroll me-2"></i>BOOK BAZAAR
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-link-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php" id="nav-home">
                            <i class="fa-solid fa-house me-1"></i> Home
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id']) && !$is_admin_user): ?>
                        <li class="nav-link-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'purchased_books.php' ? 'active' : ''; ?>" href="<?php echo $base_url; ?>purchased_books.php" id="nav-purchased">
                                <i class="fa-solid fa-book me-1"></i> My Purchases
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($is_admin_user): ?>
                            <!-- Admin Panel Navigation Link -->
                            <li class="nav-item me-2">
                                <a class="btn btn-outline-primary btn-sm px-3 py-1 fw-bold rounded-pill" href="<?php echo $base_url; ?>admin.php" id="nav-admin">
                                    <i class="fa-solid fa-gauge-high me-1"></i> Admin Panel
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- Shopping Cart link for regular users -->
                            <li class="nav-item me-2">
                                <a class="nav-link position-relative <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>" href="<?php echo $base_url; ?>cart.php" id="nav-cart">
                                    <i class="fa-solid fa-cart-shopping me-1"></i> Cart
                                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                                            <?php echo $_SESSION['cart_count']; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- User Profile Pill -->
                        <li class="nav-item me-2">
                            <a class="nav-link d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="<?php echo $base_url; ?>profile.php" id="nav-profile">
                                <?php if (!empty($nav_profile_pic) && file_exists(__DIR__ . '/' . $nav_profile_pic)): ?>
                                    <img src="<?php echo $base_url . htmlspecialchars($nav_profile_pic); ?>" alt="Avatar" class="navbar-avatar me-2">
                                <?php else: ?>
                                    <div class="navbar-avatar me-2 bg-primary text-white d-flex align-items-center justify-content-center" style="font-size: 0.8rem; font-weight: 700; width: 30px; height: 30px; border-radius: 50%;">
                                        <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="fw-semibold text-dark me-1"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <?php if ($is_admin_user): ?>
                                    <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Admin</span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- Logout Link -->
                        <li class="nav-item">
                            <a class="nav-link text-danger border border-danger-subtle rounded px-3 py-1 mt-2 mt-lg-0" href="<?php echo $base_url; ?>logout.php" id="nav-logout">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" href="<?php echo $base_url; ?>login.php" id="nav-login">
                                <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a class="btn btn-navbar text-white px-4 py-2" href="<?php echo $base_url; ?>register.php" id="nav-register">
                                <i class="fa-solid fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Global Notice Banner for unapproved users -->
    <?php if (isset($_SESSION['user_id']) && !$user_approved && !$is_admin_user): ?>
        <div class="container mt-3">
            <div class="alert alert-warning alert-premium border-0 shadow-sm d-flex align-items-center mb-0" role="alert">
                <i class="fa-solid fa-hourglass-half me-2 fs-5 text-warning"></i>
                <div>
                    <strong>Notice:</strong> Your account is currently pending administrator approval. You will be able to edit your profile, add items to your cart, and complete purchases once approved by an administrator.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Flash Message Container -->
    <div class="container mt-4">
        <?php if (isset($_SESSION['flash'])): ?>
            <?php 
                $flash = $_SESSION['flash']; 
                $category = $flash['type'] === 'error' ? 'danger' : $flash['type'];
                unset($_SESSION['flash']); // Clear immediately
            ?>
            <div class="alert alert-<?php echo $category; ?> alert-dismissible fade show alert-premium d-flex align-items-center" role="alert">
                <i class="fa-solid <?php echo $flash['type'] == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> me-2 fs-5"></i>
                <div><?php echo htmlspecialchars($flash['message']); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Dynamic Content Block Starts here (completed in each template) -->
    <main class="flex-shrink-0 mb-5">
