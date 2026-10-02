<?php
// Mock Secure Payment Gateway Checkout Page
// File: payment.php

require_once __DIR__ . '/config.php';

// Route guards: require login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = ['message' => 'Please log in to make a payment.', 'type' => 'error'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check user status
$is_approved = false;
try {
    $stmt = $pdo->prepare("SELECT is_approved, username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_row = $stmt->fetch();
    if ($user_row) {
        $is_approved = (int)$user_row['is_approved'] === 1;
        if ($user_row['username'] === 'admin') {
            $_SESSION['flash'] = ['message' => 'Administrators do not perform purchases.', 'type' => 'error'];
            header("Location: index.php");
            exit;
        }
    }
} catch (PDOException $e) {
    // Fail silently
}

if (!$is_approved) {
    $_SESSION['flash'] = ['message' => 'Purchases are locked until your account is approved.', 'type' => 'error'];
    header("Location: index.php");
    exit;
}

$page_title = 'Secure Payment | BOOK BAZAAR';
require_once __DIR__ . '/header.php';

// Fetch cart list to display order breakdown
$cart_items = [];
$total_price = 0.0;

try {
    $stmt = $pdo->prepare("
        SELECT c.quantity, b.title, b.price 
        FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    // Silent
}

if (empty($cart_items)) {
    $_SESSION['flash'] = ['message' => 'Your cart is empty.', 'type' => 'error'];
    header("Location: cart.php");
    exit;
}

$vat = $total_price * 0.075;
$final_total = $total_price + $vat;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <!-- Details Column -->
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="summary-card h-100">
                <h3 class="h5 mb-4 text-dark fw-bold"><i class="fa-solid fa-receipt text-primary me-2"></i>Order Summary</h3>
                
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between text-muted-dark small">
                            <span class="text-truncate-1 w-75"><?php echo htmlspecialchars($item['title']); ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span class="fw-semibold text-dark">₦<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr class="border-light my-3">

                <div class="summary-line">
                    <span class="text-muted-dark">Subtotal</span>
                    <span class="text-dark fw-medium">₦<?php echo number_format($total_price, 2); ?></span>
                </div>
                <div class="summary-line">
                    <span class="text-muted-dark">Estimated VAT (7.5%)</span>
                    <span class="text-dark fw-medium">₦<?php echo number_format($vat, 2); ?></span>
                </div>
                <div class="summary-line">
                    <span class="text-muted-dark">Shipping</span>
                    <span class="text-success fw-medium">FREE</span>
                </div>
                
                <div class="summary-line total mt-3 pt-3">
                    <span>Total Due</span>
                    <span class="text-primary">₦<?php echo number_format($final_total, 2); ?></span>
                </div>
                
                <div class="alert alert-info alert-premium small border-0 shadow-sm mt-4 mb-0">
                    <i class="fa-solid fa-lock text-info me-2 fs-5"></i>
                    Your card credentials are encrypted and transmitted securely via simulated SSL encryption.
                </div>
            </div>
        </div>

        <!-- Payment input Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h2 class="h4 mb-3 text-dark fw-bold">Payment Details</h2>
                <p class="text-muted-dark small mb-4">Choose your preferred payment method and complete authorization below.</p>

                <!-- Mockup Card graphic details -->
                <div class="payment-card-graphic">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="card-chip"></div>
                        <i class="fa-brands fa-cc-visa fs-2 text-white-50"></i>
                    </div>
                    <div class="card-number-display" id="cardNoPreview">•••• •••• •••• ••••</div>
                    <div class="d-flex justify-content-between small text-white-50">
                        <div>
                            <span class="d-block" style="font-size: 0.55rem; text-transform: uppercase;">Card Holder</span>
                            <span class="fw-bold text-white text-uppercase" id="cardNamePreview">YOUR FULL NAME</span>
                        </div>
                        <div class="text-end">
                            <span class="d-block" style="font-size: 0.55rem; text-transform: uppercase;">Expires</span>
                            <span class="fw-bold text-white" id="cardExpiryPreview">MM/YY</span>
                        </div>
                    </div>
                </div>

                <!-- Input Fields Form -->
                <form action="cart_action.php?action=checkout" method="POST" id="payment-gate-form">
                    <!-- Method Selectors -->
                    <div class="mb-4">
                        <label class="form-label text-dark small fw-semibold">Select Payment Method</label>
                        <div class="d-flex gap-2">
                            <div class="form-check flex-grow-1 border border-secondary-subtle p-3 rounded-3">
                                <input class="form-check-input ms-0 me-2" type="radio" name="pay_method" id="payCard" checked>
                                <label class="form-check-label text-dark fw-medium small" for="payCard"><i class="fa-solid fa-credit-card text-primary me-1"></i> Credit Card</label>
                            </div>
                            <div class="form-check flex-grow-1 border border-secondary-subtle p-3 rounded-3 opacity-50">
                                <input class="form-check-input ms-0 me-2" type="radio" name="pay_method" id="payTransfer" disabled>
                                <label class="form-check-label text-dark fw-medium small" for="payTransfer"><i class="fa-solid fa-building-columns text-muted me-1"></i> Bank Transfer</label>
                            </div>
                        </div>
                    </div>

                    <!-- Cardholder Name -->
                    <div class="form-floating mb-3">
                        <input type="text" name="card_name" class="form-control" id="cardName" placeholder="Cardholder Name" required oninput="document.getElementById('cardNamePreview').innerText = this.value.toUpperCase() || 'YOUR FULL NAME'">
                        <label for="cardName"><i class="fa-solid fa-user text-primary me-1"></i> Cardholder Name</label>
                    </div>

                    <!-- Card Number -->
                    <div class="form-floating mb-3">
                        <input type="text" name="card_number" class="form-control" id="cardNumber" placeholder="Card Number" minlength="16" maxlength="19" required oninput="document.getElementById('cardNoPreview').innerText = this.value || '•••• •••• •••• ••••'">
                        <label for="cardNumber"><i class="fa-solid fa-credit-card text-primary me-1"></i> Card Number</label>
                    </div>

                    <div class="row g-2">
                        <!-- Expiration -->
                        <div class="col-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="card_expiry" class="form-control" id="cardExpiry" placeholder="MM/YY" maxlength="5" required oninput="document.getElementById('cardExpiryPreview').innerText = this.value || 'MM/YY'">
                                <label for="cardExpiry"><i class="fa-solid fa-calendar text-primary me-1"></i> Expiry (MM/YY)</label>
                            </div>
                        </div>
                        <!-- CVV -->
                        <div class="col-6">
                            <div class="form-floating mb-3">
                                <input type="password" name="card_cvv" class="form-control" id="cardCvv" placeholder="CVV" minlength="3" maxlength="4" required>
                                <label for="cardCvv"><i class="fa-solid fa-shield-halved text-primary me-1"></i> CVV</label>
                            </div>
                        </div>
                    </div>

                    <!-- Card Pin -->
                    <div class="form-floating mb-4">
                        <input type="password" name="card_pin" class="form-control" id="cardPin" placeholder="Card Pin" minlength="4" maxlength="6" required>
                        <label for="cardPin"><i class="fa-solid fa-key text-primary me-1"></i> Card PIN (4-6 digits)</label>
                    </div>

                    <button type="submit" class="btn btn-premium-primary w-100 py-3 fs-5" id="btn-pay-complete">
                        <i class="fa-solid fa-shield-halved me-1"></i> Authorize & Pay ₦<?php echo number_format($final_total, 2); ?>
                    </button>
                    
                    <div class="text-center mt-3">
                        <a href="cart.php" class="text-muted-dark small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Cancel & Return to Cart</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/header.php'; ?>
