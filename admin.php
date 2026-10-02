```php
<?php

// Administrative Control Dashboard
// File: admin.php

require_once __DIR__ . '/config.php';

// Require administrator access
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['username']) ||
    $_SESSION['username'] !== 'admin'
) {
    $_SESSION['flash'] = [
        'message' => 'Access denied. Administrator privileges required.',
        'type' => 'error'
    ];

    header("Location: index.php");
    exit;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';

// Available book categories
$categories_list = [
    'Romance',
    'Fantasy',
    'Science Fiction',
    'Horror',
    'Mystery & Thriller',
    'Literary Fiction',
    'Biography & Memoir',
    'Health & Awareness',
    'Animation'
];


/*
 * ============================================================
 * APPROVE USER
 * ============================================================
 */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['approve_user_id'])
) {
    $approve_id = (int) $_POST['approve_user_id'];

    try {

        $name_stmt = $pdo->prepare(
            "SELECT username
             FROM users
             WHERE id = ? AND username != 'admin'"
        );

        $name_stmt->execute([$approve_id]);

        $uname = $name_stmt->fetchColumn();

        if ($uname) {

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET is_approved = 1
                 WHERE id = ? AND username != 'admin'"
            );

            $stmt->execute([$approve_id]);

            $_SESSION['flash'] = [
                'message' => "You have successfully approved $uname as a new member.",
                'type' => 'success'
            ];

        } else {

            $_SESSION['flash'] = [
                'message' => 'User not found.',
                'type' => 'error'
            ];
        }

    } catch (PDOException $e) {

        error_log(
            'BookBazaar user approval failed: ' . $e->getMessage()
        );

        $_SESSION['flash'] = [
            'message' => 'Failed to approve user.',
            'type' => 'error'
        ];
    }

    header("Location: admin.php?tab=users");
    exit;
}


/*
 * ============================================================
 * DELETE USER
 * ============================================================
 */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_user_id'])
) {
    $delete_id = (int) $_POST['delete_user_id'];

    try {

        // Never allow the administrator account to be deleted
        $name_stmt = $pdo->prepare(
            "SELECT username
             FROM users
             WHERE id = ? AND username != 'admin'"
        );

        $name_stmt->execute([$delete_id]);

        $uname = $name_stmt->fetchColumn();

        if ($uname) {

            $stmt = $pdo->prepare(
                "DELETE FROM users
                 WHERE id = ? AND username != 'admin'"
            );

            $stmt->execute([$delete_id]);

            $_SESSION['flash'] = [
                'message' => "User $uname has been successfully removed.",
                'type' => 'success'
            ];

        } else {

            $_SESSION['flash'] = [
                'message' => 'User not found.',
                'type' => 'error'
            ];
        }

    } catch (PDOException $e) {

        error_log(
            'BookBazaar user deletion failed: ' . $e->getMessage()
        );

        $_SESSION['flash'] = [
            'message' => 'Failed to remove user account.',
            'type' => 'error'
        ];
    }

    header("Location: admin.php?tab=users");
    exit;
}


/*
 * ============================================================
 * UPDATE PURCHASE STATUS
 * ============================================================
 */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action_purchase_id']) &&
    isset($_POST['status'])
) {
    $purchase_id = (int) $_POST['action_purchase_id'];
    $status = $_POST['status'];

    $allowed_statuses = [
        'Shipped',
        'Delivered',
        'Rejected'
    ];

    if (in_array($status, $allowed_statuses, true)) {

        try {

            $stmt = $pdo->prepare(
                "UPDATE purchases
                 SET status = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $status,
                $purchase_id
            ]);

            if ($stmt->rowCount() > 0) {

                if ($status === 'Shipped') {
                    $message = 'The order has been accepted and marked as shipped.';
                } elseif ($status === 'Delivered') {
                    $message = 'The order has been marked as delivered.';
                } else {
                    $message = 'The order has been rejected.';
                }

                $_SESSION['flash'] = [
                    'message' => $message,
                    'type' => 'success'
                ];

            } else {

                $_SESSION['flash'] = [
                    'message' => 'Purchase order not found.',
                    'type' => 'error'
                ];
            }

        } catch (PDOException $e) {

            error_log(
                'BookBazaar purchase status update failed: ' . $e->getMessage()
            );

            $_SESSION['flash'] = [
                'message' => 'Failed to update order status.',
                'type' => 'error'
            ];
        }

    } else {

        $_SESSION['flash'] = [
            'message' => 'Invalid order status.',
            'type' => 'error'
        ];
    }

    header("Location: admin.php?tab=purchases");
    exit;
}


/*
 * ============================================================
 * LOAD BOOK FOR EDITING
 * ============================================================
 */

$edit_book = null;

if (
    $tab === 'edit_book' &&
    isset($_GET['id'])
) {
    $edit_id = (int) $_GET['id'];

    try {

        $stmt = $pdo->prepare(
            "SELECT *
             FROM books
             WHERE id = ?"
        );

        $stmt->execute([$edit_id]);

        $edit_book = $stmt->fetch();

        if (!$edit_book) {

            $_SESSION['flash'] = [
                'message' => 'Book not found.',
                'type' => 'error'
            ];

            header("Location: admin.php?tab=books");
            exit;
        }

    } catch (PDOException $e) {

        error_log(
            'BookBazaar book lookup failed: ' . $e->getMessage()
        );

        $_SESSION['flash'] = [
            'message' => 'Failed to load book information.',
            'type' => 'error'
        ];

        header("Location: admin.php?tab=books");
        exit;
    }
}


$page_title = 'Admin Panel | BOOK BAZAAR';

require_once __DIR__ . '/header.php';

?>

<div class="container py-5">

    <h1 class="h2 mb-4 brand-font text-dark">
        <i class="fa-solid fa-gauge-high text-primary me-2"></i>
        Admin Dashboard Panel
    </h1>


    <!-- Tab Navigation -->
    <div class="admin-card mb-4 border shadow-sm overflow-hidden bg-white p-3 rounded-4">

        <ul
            class="nav nav-pills admin-nav-tabs"
            id="adminTabs"
            role="tablist"
        >

            <li class="nav-item">

                <a
                    class="nav-link <?php echo $tab === 'users' ? 'active' : ''; ?>"
                    href="admin.php?tab=users"
                >
                    <i class="fa-solid fa-users-gear me-1"></i>
                    User Approvals
                </a>

            </li>


            <li class="nav-item">

                <a
                    class="nav-link <?php echo $tab === 'purchases' ? 'active' : ''; ?>"
                    href="admin.php?tab=purchases"
                >
                    <i class="fa-solid fa-receipt me-1"></i>
                    Purchase Approvals
                </a>

            </li>


            <li class="nav-item">

                <a
                    class="nav-link <?php echo $tab === 'books' ? 'active' : ''; ?>"
                    href="admin.php?tab=books"
                >
                    <i class="fa-solid fa-book-open-reader me-1"></i>
                    Manage Catalog
                </a>

            </li>


            <li class="nav-item ms-auto">

                <a
                    class="btn btn-premium-primary text-white <?php echo $tab === 'add_book' ? 'active' : ''; ?>"
                    href="admin.php?tab=add_book"
                >
                    <i class="fa-solid fa-circle-plus me-1"></i>
                    Upload New Book
                </a>

            </li>

        </ul>

    </div>


    <div class="tab-content" id="adminTabsContent">


        <!-- =====================================================
             USERS
        ====================================================== -->

        <?php if ($tab === 'users'): ?>

            <?php

            $stmt = $pdo->prepare(
                "SELECT *
                 FROM users
                 WHERE username != 'admin'
                 ORDER BY created_at DESC"
            );

            $stmt->execute();

            $users = $stmt->fetchAll();

            ?>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                <div class="card-header bg-white py-3 border-bottom border-light">

                    <h3 class="h5 mb-0 text-dark">
                        Review Registered Users
                    </h3>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="py-3 px-4">
                                    User Details
                                </th>

                                <th class="py-3 text-center">
                                    Registration Date
                                </th>

                                <th class="py-3 text-center">
                                    Approval Status
                                </th>

                                <th class="py-3 text-end px-4">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!empty($users)): ?>

                                <?php foreach ($users as $u): ?>

                                    <?php
                                    $username = (string) $u['username'];
                                    $email = (string) $u['email'];
                                    $is_approved = (int) $u['is_approved'] === 1;
                                    ?>

                                    <tr>

                                        <!-- User Details -->
                                        <td class="py-3 px-4">

                                            <div class="d-flex align-items-center">

                                                <?php
                                                $profile_picture = $u['profile_picture'] ?? '';
                                                $profile_path = __DIR__ . '/' . $profile_picture;
                                                ?>

                                                <?php if (
                                                    !empty($profile_picture) &&
                                                    file_exists($profile_path)
                                                ): ?>

                                                    <img
                                                        src="<?php echo htmlspecialchars($profile_picture); ?>"
                                                        alt="User avatar"
                                                        class="navbar-avatar me-3"
                                                    >

                                                <?php else: ?>

                                                    <div
                                                        class="navbar-avatar me-3 bg-secondary text-white d-flex align-items-center justify-content-center"
                                                        style="
                                                            font-size: 0.8rem;
                                                            font-weight: 700;
                                                            width: 35px;
                                                            height: 35px;
                                                            border-radius: 50%;
                                                        "
                                                    >
                                                        <?php
                                                        echo htmlspecialchars(
                                                            strtoupper(substr($username, 0, 2))
                                                        );
                                                        ?>
                                                    </div>

                                                <?php endif; ?>


                                                <div>

                                                    <h4 class="h6 mb-0 text-dark fw-bold">
                                                        <?php echo htmlspecialchars($username); ?>
                                                    </h4>

                                                    <span class="text-muted-dark small">
                                                        <?php echo htmlspecialchars($email); ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- Registration Date -->
                                        <td class="py-3 text-center text-muted-dark small">

                                            <?php
                                            echo date(
                                                'd M Y, h:i A',
                                                strtotime($u['created_at'])
                                            );
                                            ?>

                                        </td>


                                        <!-- Approval Status -->
                                        <td class="py-3 text-center">

                                            <?php if ($is_approved): ?>

                                                <span class="badge-status badge-status-approved">
                                                    Approved
                                                </span>

                                            <?php else: ?>

                                                <span class="badge-status badge-status-pending">
                                                    Pending
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- Actions -->
                                        <td class="py-3 text-end px-4">

                                            <div class="d-flex justify-content-end gap-2">

                                                <?php if (!$is_approved): ?>

                                                    <form
                                                        action="admin.php?tab=users"
                                                        method="POST"
                                                        class="d-inline"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="approve_user_id"
                                                            value="<?php echo (int) $u['id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-success px-3 py-1.5 rounded-3"
                                                        >
                                                            <i class="fa-solid fa-check me-1"></i>
                                                            Approve
                                                        </button>

                                                    </form>

                                                <?php endif; ?>


                                                <form
                                                    action="admin.php?tab=users"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to remove this user account?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="delete_user_id"
                                                        value="<?php echo (int) $u['id']; ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger px-3 py-1.5 rounded-3"
                                                    >
                                                        <i class="fa-solid fa-trash-can me-1"></i>
                                                        Remove User
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="4"
                                        class="text-center py-5 text-muted-dark"
                                    >
                                        No registered users found.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             PURCHASES
        ====================================================== -->

        <?php if ($tab === 'purchases'): ?>

            <?php

            $stmt = $pdo->prepare("
                SELECT
                    p.id,
                    p.quantity,
                    p.price_paid,
                    p.status,
                    p.purchased_at,
                    u.username,
                    b.title,
                    b.author
                FROM purchases p
                JOIN users u ON p.user_id = u.id
                JOIN books b ON p.book_id = b.id
                ORDER BY p.purchased_at DESC
            ");

            $stmt->execute();

            $orders = $stmt->fetchAll();

            ?>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                <div class="card-header bg-white py-3 border-bottom border-light">

                    <h3 class="h5 mb-0 text-dark">
                        Review Purchases
                    </h3>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="py-3 px-4">
                                    Customer
                                </th>

                                <th class="py-3">
                                    Book Requested
                                </th>

                                <th class="py-3 text-center">
                                    Qty
                                </th>

                                <th class="py-3 text-end">
                                    Price Paid
                                </th>

                                <th class="py-3 text-center">
                                    Status
                                </th>

                                <th class="py-3 text-end px-4">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!empty($orders)): ?>

                                <?php foreach ($orders as $o): ?>

                                    <?php
                                    $order_status = (string) $o['status'];

                                    $status_class = strtolower(
                                        preg_replace(
                                            '/[^a-zA-Z0-9_-]/',
                                            '-',
                                            $order_status
                                        )
                                    );

                                    $order_total =
                                        (float) $o['price_paid'] *
                                        (int) $o['quantity'];
                                    ?>

                                    <tr>

                                        <!-- Customer -->
                                        <td class="py-3 px-4">

                                            <span class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($o['username']); ?>
                                            </span>

                                            <div
                                                class="text-muted-dark small"
                                                style="font-size: 0.7rem;"
                                            >
                                                <?php
                                                echo date(
                                                    'd M, H:i',
                                                    strtotime($o['purchased_at'])
                                                );
                                                ?>
                                            </div>

                                        </td>


                                        <!-- Book -->
                                        <td class="py-3">

                                            <div
                                                class="fw-semibold text-dark text-truncate-1"
                                                style="max-width: 250px;"
                                            >
                                                <?php echo htmlspecialchars($o['title']); ?>
                                            </div>

                                            <span class="text-muted-dark small">
                                                By <?php echo htmlspecialchars($o['author']); ?>
                                            </span>

                                        </td>


                                        <!-- Quantity -->
                                        <td class="py-3 text-center fw-bold">

                                            <?php echo (int) $o['quantity']; ?>

                                        </td>


                                        <!-- Price -->
                                        <td class="py-3 text-end text-dark fw-bold">

                                            ₦<?php echo number_format($order_total, 2); ?>

                                        </td>


                                        <!-- Status -->
                                        <td class="py-3 text-center">

                                            <span
                                                class="badge-status badge-status-<?php echo htmlspecialchars($status_class); ?>"
                                            >
                                                <?php echo htmlspecialchars($order_status); ?>
                                            </span>

                                        </td>


                                        <!-- Actions -->
                                        <td class="py-3 text-end px-4">

                                            <div class="d-flex justify-content-end gap-2">

                                                <?php if ($order_status === 'Pending'): ?>

                                                    <!-- Accept / Ship -->
                                                    <form
                                                        action="admin.php?tab=purchases"
                                                        method="POST"
                                                        class="d-inline"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action_purchase_id"
                                                            value="<?php echo (int) $o['id']; ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="status"
                                                            value="Shipped"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-success px-2 py-1 rounded-3"
                                                            title="Accept and ship order"
                                                        >
                                                            <i class="fa-solid fa-check me-1"></i>
                                                            Accept & Ship
                                                        </button>

                                                    </form>


                                                    <!-- Reject -->
                                                    <form
                                                        action="admin.php?tab=purchases"
                                                        method="POST"
                                                        class="d-inline"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action_purchase_id"
                                                            value="<?php echo (int) $o['id']; ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="status"
                                                            value="Rejected"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-danger px-2 py-1 rounded-3"
                                                            title="Reject order"
                                                        >
                                                            <i class="fa-solid fa-xmark me-1"></i>
                                                            Reject
                                                        </button>

                                                    </form>


                                                <?php elseif ($order_status === 'Shipped'): ?>

                                                    <!-- Deliver -->
                                                    <form
                                                        action="admin.php?tab=purchases"
                                                        method="POST"
                                                        class="d-inline"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action_purchase_id"
                                                            value="<?php echo (int) $o['id']; ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="status"
                                                            value="Delivered"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-primary px-2 py-1 rounded-3"
                                                            title="Mark order as delivered"
                                                        >
                                                            <i class="fa-solid fa-truck-ramp-box me-1"></i>
                                                            Mark Delivered
                                                        </button>

                                                    </form>

                                                <?php else: ?>

                                                    <span class="text-muted-dark small">
                                                        Completed
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center py-5 text-muted-dark"
                                    >
                                        No orders logged in history.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             BOOK CATALOG
        ====================================================== -->

        <?php if ($tab === 'books'): ?>

            <?php

            $stmt = $pdo->prepare(
                "SELECT *
                 FROM books
                 ORDER BY created_at DESC"
            );

            $stmt->execute();

            $catalog = $stmt->fetchAll();

            ?>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                <div class="card-header bg-white py-3 border-bottom border-light">

                    <h3 class="h5 mb-0 text-dark">
                        Book Catalog
                    </h3>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="py-3 px-4">
                                    Book Details
                                </th>

                                <th class="py-3 text-center">
                                    Category
                                </th>

                                <th class="py-3 text-end">
                                    Price
                                </th>

                                <th class="py-3 text-center">
                                    Uploaded Date
                                </th>

                                <th class="py-3 text-end px-4">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!empty($catalog)): ?>

                                <?php foreach ($catalog as $b): ?>

                                    <?php
                                    $book_id = (int) $b['id'];
                                    $cover_index = ($book_id % 6) + 1;
                                    ?>

                                    <tr>

                                        <!-- Book Details -->
                                        <td class="py-3 px-4">

                                            <div class="d-flex align-items-center">

                                                <div
                                                    class="book-cover-fallback cover-theme-<?php echo $cover_index; ?> shadow-sm me-3"
                                                    style="
                                                        width: 40px;
                                                        height: 60px;
                                                        padding: 4px;
                                                        border-radius: 2px 4px 4px 2px;
                                                        border-left: 2px solid rgba(0,0,0,0.4);
                                                        flex-shrink: 0;
                                                    "
                                                >

                                                    <div
                                                        style="
                                                            font-size: 0.28rem;
                                                            font-weight: 700;
                                                            line-height: 1.1;
                                                            overflow: hidden;
                                                            display: -webkit-box;
                                                            -webkit-line-clamp: 3;
                                                            -webkit-box-orient: vertical;
                                                        "
                                                    >
                                                        <?php echo htmlspecialchars($b['title']); ?>
                                                    </div>

                                                </div>


                                                <div
                                                    class="text-truncate-1"
                                                    style="max-width: 300px;"
                                                >

                                                    <h4 class="h6 mb-0 text-dark fw-bold">
                                                        <?php echo htmlspecialchars($b['title']); ?>
                                                    </h4>

                                                    <span class="text-muted-dark small">
                                                        By <?php echo htmlspecialchars($b['author']); ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- Category -->
                                        <td class="py-3 text-center small fw-semibold text-secondary">

                                            <?php echo htmlspecialchars($b['category']); ?>

                                        </td>


                                        <!-- Price -->
                                        <td class="py-3 text-end text-primary fw-bold">

                                            ₦<?php echo number_format((float) $b['price'], 2); ?>

                                        </td>


                                        <!-- Date -->
                                        <td class="py-3 text-center text-muted-dark small">

                                            <?php
                                            echo date(
                                                'd M Y',
                                                strtotime($b['created_at'])
                                            );
                                            ?>

                                        </td>


                                        <!-- Actions -->
                                        <td class="py-3 text-end px-4">

                                            <a
                                                href="admin.php?tab=edit_book&id=<?php echo $book_id; ?>"
                                                class="btn btn-sm btn-outline-primary px-3 py-1.5 rounded-3 me-2"
                                            >
                                                <i class="fa-solid fa-pen-to-square me-1"></i>
                                                Edit
                                            </a>


                                            <form
                                                action="admin_book_action.php?action=delete&id=<?php echo $book_id; ?>"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this book?');"
                                            >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger px-3 py-1.5 rounded-3"
                                                >
                                                    <i class="fa-solid fa-trash-can me-1"></i>
                                                    Delete
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-5 text-muted-dark"
                                    >
                                        Your bookstore catalog is empty.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             ADD BOOK
        ====================================================== -->

        <?php if ($tab === 'add_book'): ?>

            <div class="row justify-content-center">

                <div class="col-md-8 col-lg-7">

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">

                        <div class="card-header bg-white py-3 border-bottom border-light">

                            <h3 class="h5 mb-0 text-dark">

                                <i class="fa-solid fa-plus-circle text-primary me-2"></i>

                                Upload a New Book Entry

                            </h3>

                        </div>


                        <div class="card-body p-4 p-md-5">

                            <form
                                action="admin_book_action.php?action=add"
                                method="POST"
                            >

                                <!-- Title -->
                                <div class="form-floating mb-3">

                                    <input
                                        type="text"
                                        name="title"
                                        class="form-control"
                                        id="bookTitle"
                                        placeholder="Book Title"
                                        maxlength="255"
                                        required
                                    >

                                    <label for="bookTitle">
                                        <i class="fa-solid fa-book text-primary me-1"></i>
                                        Book Title
                                    </label>

                                </div>


                                <!-- Author -->
                                <div class="form-floating mb-3">

                                    <input
                                        type="text"
                                        name="author"
                                        class="form-control"
                                        id="bookAuthor"
                                        placeholder="Author Name"
                                        maxlength="255"
                                        required
                                    >

                                    <label for="bookAuthor">
                                        <i class="fa-solid fa-user text-primary me-1"></i>
                                        Author Name
                                    </label>

                                </div>


                                <!-- Category -->
                                <div class="form-floating mb-3">

                                    <select
                                        name="category"
                                        class="form-select"
                                        id="bookCategory"
                                        required
                                    >

                                        <option value="" disabled selected>
                                            Select Genre Category
                                        </option>

                                        <?php foreach ($categories_list as $cat): ?>

                                            <option value="<?php echo htmlspecialchars($cat); ?>">
                                                <?php echo htmlspecialchars($cat); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                    <label for="bookCategory">
                                        <i class="fa-solid fa-list text-primary me-1"></i>
                                        Category / Genre
                                    </label>

                                </div>


                                <!-- Price -->
                                <div class="form-floating mb-3">

                                    <input
                                        type="number"
                                        name="price"
                                        class="form-control"
                                        id="bookPrice"
                                        placeholder="Price"
                                        min="1000"
                                        step="0.01"
                                        required
                                    >

                                    <label for="bookPrice">
                                        <i class="fa-solid fa-naira-sign text-primary me-1"></i>
                                        Price in Naira (Minimum ₦1,000)
                                    </label>

                                    <div class="form-text text-muted-dark small ms-1">
                                        Must be equal to or greater than ₦1,000.00
                                    </div>

                                </div>


                                <!-- Description -->
                                <div class="mb-4">

                                    <label
                                        for="bookDesc"
                                        class="form-label fw-semibold text-dark"
                                    >
                                        Synopsis / Book Description
                                    </label>

                                    <textarea
                                        name="description"
                                        id="bookDesc"
                                        class="form-control"
                                        rows="5"
                                        maxlength="5000"
                                        placeholder="Write a short detailed description about this book..."
                                        required
                                    ></textarea>

                                </div>


                                <div class="d-flex gap-3">

                                    <button
                                        type="submit"
                                        class="btn btn-premium-primary py-3 fs-6 flex-grow-1"
                                    >
                                        Add Book to Store
                                    </button>

                                    <a
                                        href="admin.php?tab=books"
                                        class="btn btn-premium-secondary py-3 px-4 fs-6"
                                    >
                                        Cancel
                                    </a>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             EDIT BOOK
        ====================================================== -->

        <?php if ($tab === 'edit_book' && $edit_book): ?>

            <div class="row justify-content-center">

                <div class="col-md-8 col-lg-7">

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">

                        <div class="card-header bg-white py-3 border-bottom border-light">

                            <h3 class="h5 mb-0 text-dark">

                                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>

                                Modify Book Specifications

                            </h3>

                        </div>


                        <div class="card-body p-4 p-md-5">

                            <form
                                action="admin_book_action.php?action=edit&id=<?php echo (int) $edit_book['id']; ?>"
                                method="POST"
                            >

                                <!-- Title -->
                                <div class="form-floating mb-3">

                                    <input
                                        type="text"
                                        name="title"
                                        class="form-control"
                                        id="editBookTitle"
                                        placeholder="Book Title"
                                        value="<?php echo htmlspecialchars($edit_book['title']); ?>"
                                        maxlength="255"
                                        required
                                    >

                                    <label for="editBookTitle">
                                        <i class="fa-solid fa-book text-primary me-1"></i>
                                        Book Title
                                    </label>

                                </div>


                                <!-- Author -->
                                <div class="form-floating mb-3">

                                    <input
                                        type="text"
                                        name="author"
                                        class="form-control"
                                        id="editBookAuthor"
                                        placeholder="Author Name"
                                        value="<?php echo htmlspecialchars($edit_book['author']); ?>"
                                        maxlength="255"
                                        required
                                    >

                                    <label for="editBookAuthor">
                                        <i class="fa-solid fa-user text-primary me-1"></i>
                                        Author Name
                                    </label>

                                </div>


                                <!-- Category -->
                                <div class="form-floating mb-3">

                                    <select
                                        name="category"
                                        class="form-select"
                                        id="editBookCategory"
                                        required
                                    >

                                        <?php foreach ($categories_list as $cat): ?>

                                            <option
                                                value="<?php echo htmlspecialchars($cat); ?>"
                                                <?php echo $edit_book['category'] === $cat ? 'selected' : ''; ?>
                                            >
                                                <?php echo htmlspecialchars($cat); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                    <label for="editBookCategory">
                                        <i class="fa-solid fa-list text-primary me-1"></i>
                                        Category / Genre
                                    </label>

                                </div>


                                <!-- Price -->
                                <div class="form-floating mb-3">

                                    <input
                                        type="number"
                                        name="price"
                                        class="form-control"
                                        id="editBookPrice"
                                        placeholder="Price"
                                        min="1000"
                                        step="0.01"
                                        value="<?php echo htmlspecialchars($edit_book['price']); ?>"
                                        required
                                    >

                                    <label for="editBookPrice">
                                        <i class="fa-solid fa-naira-sign text-primary me-1"></i>
                                        Price in Naira (Minimum ₦1,000)
                                    </label>

                                </div>


                                <!-- Description -->
                                <div class="mb-4">

                                    <label
                                        for="editBookDesc"
                                        class="form-label fw-semibold text-dark"
                                    >
                                        Synopsis / Book Description
                                    </label>

                                    <textarea
                                        name="description"
                                        id="editBookDesc"
                                        class="form-control"
                                        rows="5"
                                        maxlength="5000"
                                        required
                                    ><?php echo htmlspecialchars($edit_book['description']); ?></textarea>

                                </div>


                                <div class="d-flex gap-3">

                                    <button
                                        type="submit"
                                        class="btn btn-premium-primary py-3 fs-6 flex-grow-1"
                                    >
                                        Save Book Updates
                                    </button>

                                    <a
                                        href="admin.php?tab=books"
                                        class="btn btn-premium-secondary py-3 px-4 fs-6"
                                    >
                                        Cancel
                                    </a>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
```
