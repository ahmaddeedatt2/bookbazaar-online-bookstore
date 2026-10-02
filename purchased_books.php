<?php
// Purchased Books History & Order Tracking Page
// File: purchased_books.php

require_once __DIR__ . '/config.php';

// Route guards: require login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = ['message' => 'Please log in to view your purchased books.', 'type' => 'error'];
    header("Location: login.php");
    exit;
}

$page_title = 'My Purchases & Order Tracking | BOOK BAZAAR';
require_once __DIR__ . '/header.php';


$user_id = $_SESSION['user_id'];
$purchases = [];

try {
    // Join purchases and books table to fetch completed orders via PDO statement
    $stmt = $pdo->prepare("
        SELECT p.id, p.quantity, p.price_paid, p.status, p.purchased_at, b.id AS book_id, b.title, b.author, b.category 
        FROM purchases p 
        JOIN books b ON p.book_id = b.id 
        WHERE p.user_id = ?
        ORDER BY p.purchased_at DESC
    ");
    $stmt->execute([$user_id]);
    $purchases = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['flash'] = ['message' => 'Failed to load purchase history.', 'type' => 'error'];
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0" id="purchases-title">My Purchased Books & Order Tracking</h1>
        <a href="index.php" class="btn btn-premium-secondary"><i class="fa-solid fa-cart-shopping me-1"></i> Browse More Books</a>
    </div>

    <?php if (!empty($purchases)): ?>
        <div class="d-flex flex-column gap-4">
            <?php foreach ($purchases as $item): ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-4">
                    <div class="row align-items-center g-3">
                        <!-- Book Description Column -->
                        <div class="col-md-5">
                            <div class="d-flex align-items-center">
                                <!-- Miniature cover -->
                                <div class="me-3">
                                    <?php $c_idx = ($item['book_id'] % 6) + 1; ?>
                                    <div class="book-cover-fallback cover-theme-<?php echo $c_idx; ?> shadow-sm" style="width: 50px; height: 75px; padding: 4px; border-radius: 2px 5px 5px 2px; border-left: 2px solid rgba(0,0,0,0.4);">
                                        <div style="font-size: 0.35rem; font-weight: 700; line-height: 1.1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;"><?php echo htmlspecialchars($item['title']); ?></div>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-uppercase text-secondary fw-bold" style="font-size: 0.65rem;"><?php echo htmlspecialchars($item['category']); ?></span>
                                    <h3 class="h6 mb-0">
                                        <a href="book_detail.php?id=<?php echo $item['book_id']; ?>" class="text-dark fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-muted-dark small mb-0">By <?php echo htmlspecialchars($item['author']); ?></p>
                                    <p class="text-muted-dark small mb-0" style="font-size: 0.75rem;">Ordered on <?php echo date('d M Y, h:i A', strtotime($item['purchased_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Column -->
                        <div class="col-6 col-md-3">
                            <div class="text-md-center">
                                <span class="d-block text-muted-dark small">Price Paid</span>
                                <span class="fw-semibold text-dark">₦<?php echo number_format($item['price_paid'], 2); ?> <span class="small text-muted-dark">x<?php echo $item['quantity']; ?></span></span>
                                <span class="d-block text-primary fw-bold">Total: ₦<?php echo number_format($item['price_paid'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>

                        <!-- Active Tracking status Column -->
                        <div class="col-6 col-md-4">
                            <div class="text-end text-md-start">
                                <span class="d-block text-muted-dark small mb-2 d-md-none">Order Status</span>
                                <span class="badge-status badge-status-<?php echo strtolower($item['status']); ?>">
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <hr class="border-light my-3">

                    <!-- Visual Step-by-Step Order Progress Tracker -->
                    <div>
                        <?php if ($item['status'] === 'Rejected'): ?>
                            <div class="alert alert-danger alert-premium py-2 px-3 small mb-0 d-inline-block">
                                <i class="fa-solid fa-circle-xmark me-1"></i> Order Rejected by Administrator. Please contact support or place a new request.
                            </div>
                        <?php else: ?>
                            <?php
                            $step = 1; // Pending
                            if ($item['status'] === 'Shipped') $step = 2;
                            if ($item['status'] === 'Delivered') $step = 3;
                            ?>
                            <div class="tracking-progress-bar progress-step-<?php echo $step; ?> max-width-500">
                                <div class="tracking-step <?php echo $step >= 1 ? 'active' : ''; ?>">
                                    <div class="step-icon"><i class="fa-solid fa-receipt"></i></div>
                                    <div class="step-label">Ordered</div>
                                </div>
                                <div class="tracking-step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                                    <div class="step-icon"><i class="fa-solid fa-truck-fast"></i></div>
                                    <div class="step-label">Shipped</div>
                                </div>
                                <div class="tracking-step <?php echo $step >= 3 ? 'active' : ''; ?>">
                                    <div class="step-icon"><i class="fa-solid fa-circle-check"></i></div>
                                    <div class="step-label">Delivered</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty Purchase History -->
        <div class="text-center py-5 my-5">
            <div class="mb-4">
                <i class="fa-solid fa-book-open text-muted-light" style="font-size: 5rem;"></i>
            </div>
            <h2 class="h4 text-secondary">No Purchases Yet</h2>
            <p class="text-muted-dark">You haven\'t bought any books yet. Add some to your shopping cart and checkout to see them here.</p>
            <a href="index.php" class="btn btn-premium-primary px-4 mt-3 py-2">
                Start Exploring
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/header.php'; ?>
