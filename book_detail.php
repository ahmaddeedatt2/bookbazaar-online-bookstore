<?php
// Book details page
// File: book_detail.php

require_once __DIR__ . '/config.php';

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
} catch (PDOException $e) {
    $book = null;
}

if (!$book) {
    $page_title = 'Book Not Found | BOOK BAZAAR';
    require_once __DIR__ . '/header.php';
    echo '<div class="container py-5 text-center">
            <h1 class="display-4 text-danger mb-4">404 - Book Not Found</h1>
            <p class="text-muted-dark">The book you are looking for does not exist or has been removed.</p>
            <a href="index.php" class="btn btn-premium-primary px-4 mt-3">Back to Homepage</a>
          </div>';
    require_once __DIR__ . '/footer.php';
    exit;
}

// Log browsing history for personalized recommendations if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $log_stmt = $pdo->prepare("INSERT INTO browsing_history (user_id, book_id) VALUES (?, ?)");
        $log_stmt->execute([$_SESSION['user_id'], $book_id]);
    } catch (PDOException $e) {
        // Fail silently
    }
}

// Process Review Posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['message' => 'Please log in to submit a review.', 'type' => 'error'];
        header("Location: login.php");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
    
    if ($rating < 1 || $rating > 5 || $review_text === '') {
        $_SESSION['flash'] = ['message' => 'Please provide a valid rating and review text.', 'type' => 'error'];
    } else {
        try {
            // Verify they have purchased the book
            $p_stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND book_id = ? AND status IN ('Accepted', 'Delivered')");
            $p_stmt->execute([$user_id, $book_id]);
            if ($p_stmt->fetchColumn() > 0) {
                $rev_stmt = $pdo->prepare("INSERT INTO reviews (user_id, book_id, rating, review_text) VALUES (?, ?, ?, ?)");
                $rev_stmt->execute([$user_id, $book_id, $rating, $review_text]);
                $_SESSION['flash'] = ['message' => 'Thank you! Your review has been submitted successfully.', 'type' => 'success'];
            } else {
                $_SESSION['flash'] = ['message' => 'You can only review books you have successfully purchased.', 'type' => 'error'];
            }
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['message' => 'Failed to submit review.', 'type' => 'error'];
        }
    }
    header("Location: book_detail.php?id=" . $book_id);
    exit;
}

// Process Discussion Comment Posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['message' => 'Please log in to join the discussion.', 'type' => 'error'];
        header("Location: login.php");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
    
    if ($comment_text === '') {
        $_SESSION['flash'] = ['message' => 'Comment text cannot be empty.', 'type' => 'error'];
    } else {
        try {
            // Verify user is approved
            $u_stmt = $pdo->prepare("SELECT is_approved FROM users WHERE id = ?");
            $u_stmt->execute([$user_id]);
            $is_app = (int)$u_stmt->fetchColumn() === 1;
            
            if ($is_app || $_SESSION['username'] === 'admin') {
                $com_stmt = $pdo->prepare("INSERT INTO comments (user_id, book_id, comment_text) VALUES (?, ?, ?)");
                $com_stmt->execute([$user_id, $book_id, $comment_text]);
                $_SESSION['flash'] = ['message' => 'Your comment has been posted to the discussion board.', 'type' => 'success'];
            } else {
                $_SESSION['flash'] = ['message' => 'Pending accounts cannot post comments. Please wait for approval.', 'type' => 'error'];
            }
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['message' => 'Failed to post comment.', 'type' => 'error'];
        }
    }
    header("Location: book_detail.php?id=" . $book_id);
    exit;
}

// Fetch Reviews
$reviews = [];
try {
    $r_stmt = $pdo->prepare("
        SELECT r.*, u.username, u.profile_picture 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.book_id = ? 
        ORDER BY r.created_at DESC
    ");
    $r_stmt->execute([$book_id]);
    $reviews = $r_stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch Discussion Comments
$comments = [];
try {
    $c_stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_picture 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.book_id = ? 
        ORDER BY c.created_at DESC
    ");
    $c_stmt->execute([$book_id]);
    $comments = $c_stmt->fetchAll();
} catch (PDOException $e) {}

// Check if current user has purchased the book
$has_purchased = false;
$is_approved_user = false;
if (isset($_SESSION['user_id'])) {
    try {
        $p_stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND book_id = ? AND status IN ('Accepted', 'Delivered')");
        $p_stmt->execute([$_SESSION['user_id'], $book_id]);
        $has_purchased = $p_stmt->fetchColumn() > 0;
        
        $u_stmt = $pdo->prepare("SELECT is_approved FROM users WHERE id = ?");
        $u_stmt->execute([$_SESSION['user_id']]);
        $is_approved_user = (int)$u_stmt->fetchColumn() === 1;
    } catch (PDOException $e) {}
}

$page_title = $book['title'] . ' | BOOK BAZAAR';
require_once __DIR__ . '/header.php';

// Check if user is admin
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $is_admin = $_SESSION['username'] === 'admin';
}
?>

<div class="container py-5">
    <!-- Back to browsing button -->
    <a href="index.php" class="btn btn-link text-decoration-none text-muted-dark mb-4 ps-0" id="back-btn">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Bookstore
    </a>

    <div class="row g-5">
        <!-- Book Cover Column -->
        <div class="col-md-5 col-lg-4">
            <div class="detail-cover-container">
                <?php $theme_idx = (strlen($book['title']) % 6) + 1; ?>
                <div class="detail-cover-fallback cover-theme-<?php echo $theme_idx; ?>">
                    <span class="fallback-badge">Bazaar</span>
                    <h2 class="detail-cover-title"><?php echo htmlspecialchars($book['title']); ?></h2>
                    <span class="detail-cover-author"><?php echo htmlspecialchars($book['author']); ?></span>
                </div>
            </div>
        </div>

        <!-- Book Details Column -->
        <div class="col-md-7 col-lg-8">
            <span class="text-uppercase text-secondary fw-bold fs-7 tracking-wider"><?php echo htmlspecialchars($book['category']); ?></span>
            <h1 class="display-5 text-dark fw-bold mb-1" id="book-detail-title"><?php echo htmlspecialchars($book['title']); ?></h1>
            <p class="fs-5 text-muted-dark mb-4">By <span class="fw-semibold text-dark"><?php echo htmlspecialchars($book['author']); ?></span></p>
            
            <div class="d-flex align-items-center mb-4">
                <span class="fs-2 text-primary fw-bold me-3" id="book-detail-price">₦<?php echo number_format($book['price'], 2); ?></span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-3">
                    <i class="fa-solid fa-square-check me-1"></i> In Stock
                </span>
            </div>

            <!-- Book description card -->
            <div class="card border-light bg-white rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="h5 mb-3 text-dark">Synopsis & Details</h3>
                    <p class="card-text text-muted-dark" style="line-height: 1.7;" id="book-detail-desc">
                        <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                    </p>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="d-flex flex-wrap gap-3">
                <?php if ($is_admin): ?>
                    <a href="admin.php?tab=edit_book&id=<?php echo $book['id']; ?>" class="btn btn-premium-primary px-5 py-3 fs-5 flex-grow-1 flex-md-grow-0" id="btn-edit-detail">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Modify Book Details
                    </a>
                <?php else: ?>
                    <form action="cart_action.php?action=add&id=<?php echo $book['id']; ?>" method="POST" class="flex-grow-1 flex-md-grow-0">
                        <button type="submit" class="btn btn-premium-primary px-5 py-3 fs-5 w-100" id="btn-add-detail">
                            <i class="fa-solid fa-cart-shopping me-2"></i> Add to Shopping Cart
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr class="my-5 border-light">

    <!-- Reviews and Discussion Grid -->
    <div class="row g-5">
        <!-- Left: Customer Reviews -->
        <div class="col-lg-6">
            <h3 class="h4 mb-4 text-dark fw-bold"><i class="fa-solid fa-star text-warning me-2"></i>Customer Reviews (<?php echo count($reviews); ?>)</h3>

            <!-- Review Posting Block -->
            <?php if ($has_purchased): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
                    <h4 class="h6 text-dark fw-bold mb-2">Write a Customer Review</h4>
                    <p class="text-muted-dark small mb-3">You successfully purchased this book! Share your rating and experience below.</p>
                    <form action="book_detail.php?id=<?php echo $book_id; ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-dark small fw-semibold">Select Rating</label>
                            <div class="star-rating-input">
                                <input type="radio" id="star5" name="rating" value="5" checked><label for="star5"><i class="fa-solid fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4"><label for="star4"><i class="fa-solid fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3"><label for="star3"><i class="fa-solid fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2"><label for="star2"><i class="fa-solid fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1"><label for="star1"><i class="fa-solid fa-star"></i></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="review_text" class="form-label text-dark small fw-semibold">Review Text</label>
                            <textarea name="review_text" id="review_text" class="form-control" rows="3" placeholder="What did you think of the book?" required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-premium-primary btn-sm py-2 px-4">Submit Review</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Existing Reviews Render -->
            <?php if (!empty($reviews)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="review-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($rev['profile_picture']) && file_exists(__DIR__ . '/' . $rev['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($rev['profile_picture']); ?>" alt="Avatar" class="navbar-avatar me-2" style="width: 25px; height: 25px;">
                                    <?php else: ?>
                                        <div class="navbar-avatar me-2 bg-secondary text-white d-flex align-items-center justify-content-center" style="font-size: 0.65rem; font-weight: 700; width: 25px; height: 25px; border-radius: 50%;">
                                            <?php echo strtoupper(substr($rev['username'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="fw-bold text-dark small"><?php echo htmlspecialchars($rev['username']); ?></span>
                                    <span class="badge bg-success-subtle text-success ms-2" style="font-size: 0.6rem;">Verified Buyer</span>
                                </div>
                                <span class="text-muted-dark small" style="font-size: 0.75rem;"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></span>
                            </div>
                            <div class="star-rating-display mb-2" style="font-size: 0.85rem;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo $i <= $rev['rating'] ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted-dark small mb-0"><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted-dark small">No reviews submitted yet for this book.</p>
            <?php endif; ?>
        </div>

        <!-- Right: Open Discussions -->
        <div class="col-lg-6">
            <h3 class="h4 mb-4 text-dark fw-bold"><i class="fa-solid fa-comments text-primary me-2"></i>Discussion Board (<?php echo count($comments); ?>)</h3>

            <!-- Comment Input Block -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($is_approved_user || $is_admin): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
                        <h4 class="h6 text-dark fw-bold mb-2">Join the Discussion</h4>
                        <form action="book_detail.php?id=<?php echo $book_id; ?>" method="POST">
                            <div class="mb-3">
                                <textarea name="comment_text" class="form-control" rows="3" placeholder="Ask a question or post a discussion comment about this book..." required></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn btn-premium-secondary btn-sm py-2 px-4">Post Comment</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary text-muted-dark small mb-4">
                        <i class="fa-solid fa-lock me-1"></i> Discussion board commenting is locked until your profile is approved.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="small text-muted-dark"><a href="login.php">Log in</a> to write comments or review this book.</p>
            <?php endif; ?>

            <!-- Existing Comments Render -->
            <?php if (!empty($comments)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($comments as $com): ?>
                        <div class="comment-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($com['profile_picture']) && file_exists(__DIR__ . '/' . $com['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($com['profile_picture']); ?>" alt="Avatar" class="navbar-avatar me-2" style="width: 25px; height: 25px;">
                                    <?php else: ?>
                                        <div class="navbar-avatar me-2 bg-secondary text-white d-flex align-items-center justify-content-center" style="font-size: 0.65rem; font-weight: 700; width: 25px; height: 25px; border-radius: 50%;">
                                            <?php echo strtoupper(substr($com['username'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="fw-bold text-dark small"><?php echo htmlspecialchars($com['username']); ?></span>
                                </div>
                                <span class="text-muted-dark small" style="font-size: 0.75rem;"><?php echo date('d M Y, h:i A', strtotime($com['created_at'])); ?></span>
                            </div>
                            <p class="text-muted-dark small mb-0"><?php echo nl2br(htmlspecialchars($com['comment_text'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted-dark small">No comments logged. Be the first to start the discussion!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/header.php'; ?>
