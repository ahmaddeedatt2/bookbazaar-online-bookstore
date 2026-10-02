<?php
// User Profile Management Page
// File: profile.php

require_once __DIR__ . '/config.php';

// Route guards: require login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = ['message' => 'Please log in to access your profile.', 'type' => 'error'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = null;

// Fetch fresh details from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $_SESSION['flash'] = ['message' => 'Failed to load user profile.', 'type' => 'error'];
}

if (!$user) {
    die("User profile could not be loaded.");
}

$is_approved = (int)$user['is_approved'] === 1;
$is_admin = $user['username'] === 'admin';

$categories_list = [
    'Romance', 'Fantasy', 'Science Fiction', 'Horror', 
    'Mystery & Thriller', 'Literary Fiction', 
    'Biography & Memoir', 'Health & Awareness', 'Animation'
];

// Current user preferred categories as array
$user_preferred = !empty($user['preferred_categories']) ? explode(',', $user['preferred_categories']) : [];

// Process update requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Route guard: block unapproved users
    if (!$is_approved && !$is_admin) {
        $_SESSION['flash'] = ['message' => 'Your account is pending administrator approval. Profile edits are locked.', 'type' => 'error'];
        header("Location: profile.php");
        exit;
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';
    $selected_cats = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    if ($username === '' || $email === '') {
        $_SESSION['flash'] = ['message' => 'Username and email cannot be empty.', 'type' => 'error'];
        header("Location: profile.php");
        exit;
    }

    try {
        // Check username duplication for OTHER users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['message' => 'Username is already taken by another user.', 'type' => 'error'];
            header("Location: profile.php");
            exit;
        }

        // Check email duplication for OTHER users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['message' => 'Email address is already in use by another user.', 'type' => 'error'];
            header("Location: profile.php");
            exit;
        }

        $profile_pic_path = $user['profile_picture']; // Keep existing by default

        // 1. Process Avatar Upload if file is present
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_pic']['tmp_name'];
            $file_name = $_FILES['profile_pic']['name'];
            $file_size = $_FILES['profile_pic']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024; // 2MB Limit

            if (!in_array($file_ext, $allowed_extensions)) {
                $_SESSION['flash'] = ['message' => 'Only JPG, JPEG, PNG, and GIF images are allowed.', 'type' => 'error'];
                header("Location: profile.php");
                exit;
            }

            if ($file_size > $max_size) {
                $_SESSION['flash'] = ['message' => 'Image size must be less than 2MB.', 'type' => 'error'];
                header("Location: profile.php");
                exit;
            }

            // Create target upload directories
            $upload_dir = __DIR__ . '/static/uploads/profile_pics/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Unique name for safe file storage
            $new_file_name = 'avatar_' . $user_id . '_' . uniqid() . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                // Delete previous avatar file if exists
                if ($user['profile_picture'] && file_exists(__DIR__ . '/' . $user['profile_picture'])) {
                    @unlink(__DIR__ . '/' . $user['profile_picture']);
                }
                // Save relative path in database
                $profile_pic_path = 'static/uploads/profile_pics/' . $new_file_name;
            } else {
                $_SESSION['flash'] = ['message' => 'Failed to upload profile picture.', 'type' => 'error'];
                header("Location: profile.php");
                exit;
            }
        }

        // 2. Process Category Preferences
        $preferred_categories = !empty($selected_cats) ? implode(',', $selected_cats) : null;

        // 3. Perform DB Updates
        if ($new_password !== '') {
            $hashed_pass = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password_hash = ?, profile_picture = ?, preferred_categories = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hashed_pass, $profile_pic_path, $preferred_categories, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ?, preferred_categories = ? WHERE id = ?");
            $stmt->execute([$username, $email, $profile_pic_path, $preferred_categories, $user_id]);
        }

        // Sync session details
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;

        $_SESSION['flash'] = ['message' => 'Profile updated successfully!', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['message' => 'An error occurred while updating profile.', 'type' => 'error'];
    }

    header("Location: profile.php");
    exit;
}

$page_title = 'My Profile | BOOK BAZAAR';
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="h2 mb-4">Edit Profile Settings</h1>
            
            <div class="profile-card">
                <div class="row g-0">
                    <!-- Left Section: Avatar Upload View -->
                    <div class="col-md-4 bg-light text-center py-5 border-end border-light d-flex flex-column align-items-center justify-content-center">
                        <div class="profile-avatar-container">
                            <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-avatar-large" id="profile-img-preview">
                            <?php else: ?>
                                <div class="profile-avatar-placeholder">
                                    <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h4 class="h5 mb-1 text-dark"><?php echo htmlspecialchars($user['username']); ?></h4>
                        <p class="text-muted-dark small mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-muted-dark fs-8 mt-1">Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    </div>

                    <!-- Right Section: Details Form -->
                    <div class="col-md-8">
                        <div class="p-4 p-md-5">
                            <form action="profile.php" method="POST" enctype="multipart/form-data">
                                <!-- Profile Pic Upload Input -->
                                <div class="mb-4">
                                    <label for="profile_pic" class="form-label fw-semibold text-dark">Change Profile Picture</label>
                                    <input type="file" name="profile_pic" id="profile_pic" class="form-control form-control-sm" accept="image/*" <?php echo (!$is_approved && !$is_admin) ? 'disabled' : ''; ?>>
                                    <div class="form-text small text-muted-dark">Accepts JPG, PNG, GIF files up to 2MB.</div>
                                </div>
                                
                                <hr class="border-light my-4">

                                <!-- Username Input -->
                                <div class="form-floating mb-3">
                                    <input type="text" name="username" class="form-control" id="profileUsername" placeholder="Username" value="<?php echo htmlspecialchars($user['username']); ?>" required <?php echo (!$is_approved && !$is_admin) ? 'disabled' : ''; ?>>
                                    <label for="profileUsername"><i class="fa-solid fa-user me-1 text-primary"></i> Username</label>
                                </div>

                                <!-- Email Input -->
                                <div class="form-floating mb-3">
                                    <input type="email" name="email" class="form-control" id="profileEmail" placeholder="Email Address" value="<?php echo htmlspecialchars($user['email']); ?>" required <?php echo (!$is_approved && !$is_admin) ? 'disabled' : ''; ?>>
                                    <label for="profileEmail"><i class="fa-solid fa-envelope me-1 text-primary"></i> Email Address</label>
                                </div>

                                <!-- Password Change Input -->
                                <div class="form-floating mb-4">
                                    <input type="password" name="password" class="form-control" id="profilePassword" placeholder="New Password" autocomplete="new-password" <?php echo (!$is_approved && !$is_admin) ? 'disabled' : ''; ?>>
                                    <label for="profilePassword"><i class="fa-solid fa-lock me-1 text-primary"></i> New Password (Leave blank to keep current)</label>
                                </div>

                                <hr class="border-light my-4">

                                <!-- Preferred Categories Checkboxes -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark mb-2"><i class="fa-solid fa-heart text-danger me-1"></i> Update Your Genre Preferences</label>
                                    <div class="row row-cols-2 g-2">
                                        <?php foreach ($categories_list as $cat): ?>
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($cat); ?>" id="profile_cat_<?php echo str_replace(' & ', '_', strtolower($cat)); ?>" <?php echo in_array($cat, $user_preferred) ? 'checked' : ''; ?> <?php echo (!$is_approved && !$is_admin) ? 'disabled' : ''; ?>>
                                                    <label class="form-check-label text-dark small" for="profile_cat_<?php echo str_replace(' & ', '_', strtolower($cat)); ?>">
                                                        <?php echo htmlspecialchars($cat); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-3 mt-4">
                                    <button type="submit" class="btn btn-premium-primary px-4 py-2 flex-grow-1" <?php echo (!$is_approved && !$is_admin) ? 'disabled' : ''; ?>>Save Profile Changes</button>
                                    <a href="index.php" class="btn btn-premium-secondary px-4 py-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
