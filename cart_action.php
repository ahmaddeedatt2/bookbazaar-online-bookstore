```php
<?php

// Cart Action Controller
// File: cart_action.php

require_once __DIR__ . '/config.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = [
        'message' => 'Please log in to manage your cart.',
        'type' => 'error'
    ];

    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$action = isset($_GET['action']) ? $_GET['action'] : '';
$book_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch approval status and admin role
$is_approved = false;
$is_admin = false;

try {
    $stmt = $pdo->prepare(
        "SELECT is_approved, username FROM users WHERE id = ?"
    );

    $stmt->execute([$user_id]);

    $user_row = $stmt->fetch();

    if ($user_row) {
        $is_approved = (int) $user_row['is_approved'] === 1;
        $is_admin = $user_row['username'] === 'admin';
    }
} catch (PDOException $e) {
    error_log('BookBazaar user status check failed: ' . $e->getMessage());
}

// Prevent administrators from purchasing
if ($is_admin) {
    $_SESSION['flash'] = [
        'message' => 'Administrators cannot purchase books. Please register or log in as a customer.',
        'type' => 'error'
    ];

    header("Location: index.php");
    exit;
}

// Prevent unapproved accounts from using cart features
if (!$is_approved) {
    $_SESSION['flash'] = [
        'message' => 'Your account is pending administrator approval. Purchases and cart features are locked.',
        'type' => 'error'
    ];

    header("Location: index.php");
    exit;
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($action) {

        /*
         * ADD BOOK TO CART
         */
        case 'add':

            if ($book_id <= 0) {
                $_SESSION['flash'] = [
                    'message' => 'Invalid book request.',
                    'type' => 'error'
                ];
                break;
            }

            try {

                // Verify that the book exists
                $stmt = $pdo->prepare(
                    "SELECT title FROM books WHERE id = ?"
                );

                $stmt->execute([$book_id]);

                $book = $stmt->fetch();

                if (!$book) {
                    $_SESSION['flash'] = [
                        'message' => 'Book not found.',
                        'type' => 'error'
                    ];
                    break;
                }

                // Check whether the book is already in the cart
                $stmt = $pdo->prepare(
                    "SELECT id, quantity
                     FROM cart
                     WHERE user_id = ? AND book_id = ?"
                );

                $stmt->execute([$user_id, $book_id]);

                $cart_item = $stmt->fetch();

                if ($cart_item) {

                    // Increase quantity
                    $stmt = $pdo->prepare(
                        "UPDATE cart
                         SET quantity = quantity + 1
                         WHERE id = ?"
                    );

                    $stmt->execute([$cart_item['id']]);

                } else {

                    // Add new cart item
                    $stmt = $pdo->prepare(
                        "INSERT INTO cart (user_id, book_id, quantity)
                         VALUES (?, ?, 1)"
                    );

                    $stmt->execute([$user_id, $book_id]);
                }

                update_session_cart_count($pdo);

                $_SESSION['flash'] = [
                    'message' => '"' . $book['title'] . '" added to your cart!',
                    'type' => 'success'
                ];

            } catch (PDOException $e) {

                error_log('BookBazaar add-to-cart failed: ' . $e->getMessage());

                $_SESSION['flash'] = [
                    'message' => 'Failed to add book to cart.',
                    'type' => 'error'
                ];
            }

            break;


        /*
         * UPDATE CART QUANTITY
         */
        case 'update':

            $quantity = isset($_POST['quantity'])
                ? (int) $_POST['quantity']
                : 1;

            if ($quantity < 1) {
                $quantity = 1;
            }

            if ($quantity > 99) {
                $quantity = 99;
            }

            if ($book_id <= 0) {
                $_SESSION['flash'] = [
                    'message' => 'Invalid book request.',
                    'type' => 'error'
                ];
                break;
            }

            try {

                $stmt = $pdo->prepare(
                    "UPDATE cart
                     SET quantity = ?
                     WHERE user_id = ? AND book_id = ?"
                );

                $stmt->execute([
                    $quantity,
                    $user_id,
                    $book_id
                ]);

                update_session_cart_count($pdo);

                $_SESSION['flash'] = [
                    'message' => 'Cart updated successfully.',
                    'type' => 'success'
                ];

            } catch (PDOException $e) {

                error_log('BookBazaar cart update failed: ' . $e->getMessage());

                $_SESSION['flash'] = [
                    'message' => 'Failed to update quantity.',
                    'type' => 'error'
                ];
            }

            break;


        /*
         * REMOVE BOOK FROM CART
         */
        case 'remove':

            if ($book_id <= 0) {
                $_SESSION['flash'] = [
                    'message' => 'Invalid book request.',
                    'type' => 'error'
                ];
                break;
            }

            try {

                $stmt = $pdo->prepare(
                    "DELETE FROM cart
                     WHERE user_id = ? AND book_id = ?"
                );

                $stmt->execute([
                    $user_id,
                    $book_id
                ]);

                update_session_cart_count($pdo);

                $_SESSION['flash'] = [
                    'message' => 'Item removed from cart.',
                    'type' => 'success'
                ];

            } catch (PDOException $e) {

                error_log('BookBazaar cart removal failed: ' . $e->getMessage());

                $_SESSION['flash'] = [
                    'message' => 'Failed to remove item from cart.',
                    'type' => 'error'
                ];
            }

            break;


        /*
         * CHECKOUT
         *
         * This is a simulated checkout.
         * No card number, CVV, PIN, or banking credentials
         * are stored or processed by BookBazaar.
         */
        case 'checkout':

            try {

                /*
                 * Fetch the current cart and prices directly
                 * from the database.
                 */
                $stmt = $pdo->prepare("
                    SELECT
                        c.book_id,
                        c.quantity,
                        b.price
                    FROM cart c
                    JOIN books b ON c.book_id = b.id
                    WHERE c.user_id = ?
                ");

                $stmt->execute([$user_id]);

                $cart_items = $stmt->fetchAll();

                if (empty($cart_items)) {

                    $_SESSION['flash'] = [
                        'message' => 'Your cart is empty. Cannot checkout.',
                        'type' => 'error'
                    ];

                    header("Location: cart.php");
                    exit;
                }

                /*
                 * Start transaction so either the entire
                 * order succeeds or nothing is changed.
                 */
                $pdo->beginTransaction();

                /*
                 * Insert each cart item into purchases.
                 *
                 * The purchase price is taken from the
                 * database rather than from the browser.
                 */
                $insert_stmt = $pdo->prepare("
                    INSERT INTO purchases
                    (
                        user_id,
                        book_id,
                        quantity,
                        price_paid,
                        status
                    )
                    VALUES (?, ?, ?, ?, 'Pending')
                ");

                foreach ($cart_items as $item) {

                    $insert_stmt->execute([
                        $user_id,
                        (int) $item['book_id'],
                        (int) $item['quantity'],
                        (float) $item['price']
                    ]);
                }

                /*
                 * Clear the cart after creating the order.
                 */
                $delete_stmt = $pdo->prepare(
                    "DELETE FROM cart WHERE user_id = ?"
                );

                $delete_stmt->execute([$user_id]);

                /*
                 * Complete the transaction.
                 */
                $pdo->commit();

                // Reset cart badge
                update_session_cart_count($pdo);

                $_SESSION['flash'] = [
                    'message' => 'Order placed successfully! It is pending administrator approval.',
                    'type' => 'success'
                ];

                header("Location: purchased_books.php");
                exit;

            } catch (Exception $e) {

                /*
                 * Roll back any changes if checkout fails.
                 */
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log(
                    'BookBazaar checkout failed: ' . $e->getMessage()
                );

                $_SESSION['flash'] = [
                    'message' => 'Checkout failed. Please try again.',
                    'type' => 'error'
                ];
            }

            break;


        /*
         * INVALID ACTION
         */
        default:

            $_SESSION['flash'] = [
                'message' => 'Invalid action.',
                'type' => 'error'
            ];

            break;
    }
}

// Redirect back to the previous page when possible
$redirect_target = isset($_SERVER['HTTP_REFERER'])
    ? $_SERVER['HTTP_REFERER']
    : 'cart.php';

header("Location: " . $redirect_target);
exit;
?>
```
