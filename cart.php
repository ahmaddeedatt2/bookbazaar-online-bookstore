<?php
// Cart View Page
// File: cart.php

require_once __DIR__ . '/config.php';

// Route guards: require login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = ['message' => 'Please log in to view your shopping cart.', 'type' => 'error'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check user status
$is_approved = false;
$is_admin = false;
try {
    $stmt = $pdo->prepare("SELECT is_approved, username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_row = $stmt->fetch();
    if ($user_row) {
        $is_approved = (int)$user_row['is_approved'] === 1;
        $is_admin = $user_row['username'] === 'admin';
    }
} catch (PDOException $e) {
    // Fail silently
}

if ($is_admin) {
    $_SESSION['flash'] = ['message' => 'Administrators do not have shopping carts.', 'type' => 'error'];
    header("Location: index.php");
    exit;
}

$page_title = 'Shopping Cart | BOOK BAZAAR';
require_once __DIR__ . '/header.php';

$cart_items = [];
$total_price = 0.0;

try {
    // Join cart and books table via PDO statement
    $stmt = $pdo->prepare("
        SELECT c.book_id, c.quantity, b.title, b.author, b.price, b.category 
        FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    $_SESSION['flash'] = ['message' => 'Failed to load cart items.', 'type' => 'error'];
}
?>

<div class="container py-5">
    <h1 class="h2 mb-4" id="cart-title">Your Shopping Cart</h1>

    <?php if (!empty($cart_items)): ?>
        <div class="row g-4">
            <!-- Cart Items List Column -->
            <div class="col-lg-8">
                <?php if (!$is_approved): ?>
                    <div class="alert alert-danger alert-premium border-0 shadow-sm mb-4">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        Checkout is disabled because your account is pending administrator approval. Please wait for an administrator to activate your profile.
                    </div>
                <?php endif; ?>

                <div class="cart-table-wrapper">
                    <div class="table-responsive">
                        <table class="table cart-table align-middle">
                            <thead>
                                <tr>
                                    <th>Book Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr id="cart-row-<?php echo $item['book_id']; ?>">
                                        <!-- Book Details -->
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <!-- Tiny cover graphic -->
                                                <div class="d-none d-md-block me-3">
                                                    <?php $c_idx = ($item['book_id'] % 6) + 1; ?>
                                                    <div class="book-cover-fallback cover-theme-<?php echo $c_idx; ?> shadow-sm" style="width: 50px; height: 75px; padding: 4px; border-radius: 2px 5px 5px 2px; border-left: 2px solid rgba(0,0,0,0.4);">
                                                        <div style="font-size: 0.35rem; font-weight: 700; line-height: 1.1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;"><?php echo htmlspecialchars($item['title']); ?></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="text-uppercase text-secondary fw-bold" style="font-size: 0.65rem;"><?php echo htmlspecialchars($item['category']); ?></span>
                                                    <h3 class="h6 mb-1">
                                                        <a href="book_detail.php?id=<?php echo $item['book_id']; ?>" class="text-dark text-decoration-none fw-semibold">
                                                            <?php echo htmlspecialchars($item['title']); ?>
                                                        </a>
                                                    </h3>
                                                    <p class="text-muted-dark small mb-0">By <?php echo htmlspecialchars($item['author']); ?></p>
                                                    <p class="text-primary fw-medium small mb-0">₦<?php echo number_format($item['price'], 2); ?> each</p>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Quantity Form Controls -->
                                        <td style="min-width: 130px;">
                                            <form action="cart_action.php?action=update&id=<?php echo $item['book_id']; ?>" method="POST" class="d-flex justify-content-center">
                                                <div class="input-group quantity-control shadow-sm rounded-3 overflow-hidden border border-secondary-subtle">
                                                    <button type="button" class="btn btn-qty btn-qty-minus btn-sm px-2" <?php echo !$is_approved ? 'disabled' : ''; ?>><i class="fa-solid fa-minus text-dark small"></i></button>
                                                    <input type="number" name="quantity" class="form-control form-control-sm quantity-input py-1" value="<?php echo $item['quantity']; ?>" min="1" max="99" readonly>
                                                    <button type="button" class="btn btn-qty btn-qty-plus btn-sm px-2" <?php echo !$is_approved ? 'disabled' : ''; ?>><i class="fa-solid fa-plus text-dark small"></i></button>
                                                </div>
                                            </form>
                                        </td>
                                        
                                        <!-- Subtotal price -->
                                        <td class="text-end fw-semibold text-dark">
                                            ₦<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </td>
                                        
                                        <!-- Remove Item Form Button -->
                                        <td class="text-center">
                                            <form action="cart_action.php?action=remove&id=<?php echo $item['book_id']; ?>" method="POST">
                                                <button type="submit" class="btn btn-link text-danger p-0" title="Remove Book" <?php echo !$is_approved ? 'disabled' : ''; ?>>
                                                    <i class="fa-solid fa-trash-can fs-5"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary Receipt Column -->
            <div class="col-lg-4">
                <div class="summary-card">
                    <h2 class="h5 mb-4 text-dark font-weight-bold">Order Summary</h2>
                    
                    <div class="summary-line">
                        <span class="text-muted-dark">Items Subtotal</span>
                        <span class="text-dark fw-medium">₦<?php echo number_format($total_price, 2); ?></span>
                    </div>
                    
                    <div class="summary-line">
                        <span class="text-muted-dark">Shipping / Delivery</span>
                        <span class="text-success fw-medium">FREE</span>
                    </div>

                    <div class="summary-line">
                        <span class="text-muted-dark">Estimated VAT (7.5%)</span>
                        <span class="text-dark fw-medium">₦<?php echo number_format($total_price * 0.075, 2); ?></span>
                    </div>
                    
                    <div class="summary-line total">
                        <span>Total Due</span>
                        <span class="text-primary">₦<?php echo number_format($total_price * 1.075, 2); ?></span>
                    </div>
                    
                    <?php if ($is_approved): ?>
                        <a href="payment.php" class="btn btn-premium-primary w-100 py-3 mt-4 fs-5 text-center d-block text-decoration-none" id="checkout-btn">
                            Proceed to Checkout
                        </a>
                    <?php else: ?>
                        <button class="btn btn-premium-primary w-100 py-3 mt-4 fs-5" id="checkout-btn" disabled>
                            Proceed to Checkout
                        </button>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-muted-dark small text-decoration-none">
                            <i class="fa-solid fa-arrow-left me-1"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty Cart Display -->
        <div class="text-center py-5 my-5">
            <div class="mb-4">
                <i class="fa-solid fa-cart-arrow-down text-muted-light" style="font-size: 5rem;"></i>
            </div>
            <h2 class="h4 text-secondary">Your Cart is Empty</h2>
            <p class="text-muted-dark">Browse our catalog and add books to check out items in this section.</p>
            <a href="index.php" class="btn btn-premium-primary px-4 mt-3 py-2">
                Discover Books
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/header.php'; ?>
