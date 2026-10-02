<?php
// Bookstore Homepage: Search, Recommendations, and Category Filters
// File: index.php

$page_title = 'BOOK BAZAAR | Premier Online Bookstore';
require_once __DIR__ . '/header.php';

// Retrieve search keyword and category filters safely
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';

$categories_list = [
    'Romance', 'Fantasy', 'Science Fiction', 'Horror', 
    'Mystery & Thriller', 'Literary Fiction', 
    'Biography & Memoir', 'Health & Awareness', 'Animation'
];

try {
    // 1. Fetch main catalog books depending on filters
    if ($selected_category !== '') {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE category = :category ORDER BY created_at DESC");
        $stmt->execute(['category' => $selected_category]);
        $books = $stmt->fetchAll();
    } elseif ($search_query !== '') {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY created_at DESC");
        $stmt->execute(["%$search_query%", "%$search_query%", "%$search_query%"]);
        $books = $stmt->fetchAll();
    } else {
        // Fetch recent 12 books dynamically for the catalog grid
        $stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC LIMIT 12");
        $books = $stmt->fetchAll();
    }

    // 2. Personalized Recommendation Engine (Only for logged-in users)
    $recommended_books = [];
    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        
        // Fetch explicit preferences
        $pref_stmt = $pdo->prepare("SELECT preferred_categories FROM users WHERE id = ?");
        $pref_stmt->execute([$uid]);
        $pref_str = $pref_stmt->fetchColumn();
        $target_categories = !empty($pref_str) ? explode(',', $pref_str) : [];
        
        // Fetch implicit preference (most viewed category in browsing history)
        $history_stmt = $pdo->prepare("
            SELECT b.category, COUNT(*) as cnt 
            FROM browsing_history h 
            JOIN books b ON h.book_id = b.id 
            WHERE h.user_id = ? 
            GROUP BY b.category 
            ORDER BY cnt DESC 
            LIMIT 1
        ");
        $history_stmt->execute([$uid]);
        $most_viewed_cat = $history_stmt->fetchColumn();
        if ($most_viewed_cat && !in_array($most_viewed_cat, $target_categories)) {
            $target_categories[] = $most_viewed_cat;
        }
        
        // Query recommended books from preferred categories (limit to 4 books)
        if (!empty($target_categories)) {
            $in_clause = implode(',', array_fill(0, count($target_categories), '?'));
            $rec_stmt = $pdo->prepare("
                SELECT * FROM books 
                WHERE category IN ($in_clause) 
                ORDER BY RAND() 
                LIMIT 4
            ");
            $rec_stmt->execute($target_categories);
            $recommended_books = $rec_stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    $books = [];
    $recommended_books = [];
    $_SESSION['flash'] = ['message' => 'Database error: ' . $e->getMessage(), 'type' => 'error'];
}

// Check if user is admin
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $uname = $stmt->fetchColumn();
        $is_admin = $uname === 'admin';
    } catch (PDOException $e) {
        // Fail silently
    }
}
?>

<!-- Modern Branded Hero Section -->
<section class="hero-section py-5 mb-5 text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="hero-title" id="hero-heading">Welcome to<br><span>BOOK BAZAAR</span></h1>
                <p class="hero-lead">Explore over 100 titles in romance, sci-fi, horror, animation, and memoirs. Discover your next read with tailored recommendations and discussion boards.</p>
                
                <!-- Dynamic Search Bar Component -->
                <form action="index.php" method="GET" class="mt-4">
                    <div class="search-wrapper d-flex align-items-center mx-auto">
                        <i class="fa-solid fa-magnifying-glass text-muted ms-3"></i>
                        <input type="text" name="search" class="search-input" placeholder="Search by book name, author, or category..." value="<?php echo htmlspecialchars($search_query); ?>" id="book-search-input">
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container">
    
    <!-- Personalized Recommendations Section -->
    <?php if (!empty($recommended_books)): ?>
        <div class="mb-5 p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(6, 182, 212, 0.05) 100%); border: 1px solid rgba(99,102,241,0.15);">
            <div class="d-flex align-items-center mb-3">
                <i class="fa-solid fa-star text-warning me-2 fs-4"></i>
                <h2 class="h4 mb-0 fw-bold text-dark">Recommended for You</h2>
            </div>
            <p class="text-muted-dark small mb-4">Suggested based on your genre preferences and recently viewed categories.</p>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($recommended_books as $index => $rec_book): ?>
                    <div class="col">
                        <div class="book-card" id="rec-card-<?php echo $rec_book['id']; ?>">
                            <!-- Cover Graphic -->
                            <div class="book-cover-container" style="height: 180px;">
                                <?php $theme_idx = (($rec_book['id'] + $index) % 6) + 1; ?>
                                <div class="book-cover-fallback cover-theme-<?php echo $theme_idx; ?>" style="width: 100px; height: 150px; padding: 10px;">
                                    <span class="fallback-badge" style="font-size: 0.5rem; padding: 2px 6px;">Bazaar</span>
                                    <div class="fallback-title" style="font-size: 0.75rem; -webkit-line-clamp: 3;"><?php echo htmlspecialchars($rec_book['title']); ?></div>
                                    <div class="fallback-author" style="font-size: 0.6rem; padding-top: 4px;"><?php echo htmlspecialchars($rec_book['author']); ?></div>
                                </div>
                            </div>
                            <!-- Details -->
                            <div class="book-card-body p-3">
                                <span class="book-card-category" style="font-size: 0.65rem;"><?php echo htmlspecialchars($rec_book['category']); ?></span>
                                <h3 class="h6 mb-1 text-truncate-1">
                                    <a href="book_detail.php?id=<?php echo $rec_book['id']; ?>" class="book-title-link fw-bold">
                                        <?php echo htmlspecialchars($rec_book['title']); ?>
                                    </a>
                                </h3>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <span class="text-primary fw-bold small">₦<?php echo number_format($rec_book['price'], 2); ?></span>
                                    <a href="book_detail.php?id=<?php echo $rec_book['id']; ?>" class="btn btn-premium-primary btn-sm py-1 px-2 text-white" style="font-size: 0.7rem; border-radius: 6px;">Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Catalog Layout grid -->
    <div class="row g-4">
        <!-- Left Sidebar: Category Filters -->
        <div class="col-lg-3">
            <div class="category-sidebar">
                <h3 class="h5 mb-3 text-dark fw-bold"><i class="fa-solid fa-list-ul text-primary me-2"></i>Categories</h3>
                <div class="list-group">
                    <a href="index.php" class="category-list-item <?php echo $selected_category === '' ? 'active' : ''; ?>">
                        <span>All Collections</span>
                        <i class="fa-solid fa-chevron-right small"></i>
                    </a>
                    <?php foreach ($categories_list as $cat): ?>
                        <a href="index.php?category=<?php echo urlencode($cat); ?>" class="category-list-item <?php echo $selected_category === $cat ? 'active' : ''; ?>">
                            <span><?php echo htmlspecialchars($cat); ?></span>
                            <i class="fa-solid fa-chevron-right small"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Catalog Books list -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <?php if ($selected_category !== ''): ?>
                    <h2 class="mb-0 fs-4 text-dark"><?php echo htmlspecialchars($selected_category); ?> Collection</h2>
                    <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill">Clear Filter</a>
                <?php elseif ($search_query !== ''): ?>
                    <h2 class="mb-0 fs-4 text-dark">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
                    <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill">Clear Search</a>
                <?php else: ?>
                    <h2 class="mb-0 fs-4 text-dark">Explore Our Catalog</h2>
                    <span class="text-muted-dark small"><?php echo count($books); ?> books displayed</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($books)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($books as $index => $book): ?>
                        <div class="col">
                            <div class="book-card" id="book-card-<?php echo $book['id']; ?>">
                                <!-- Cover Image Simulation -->
                                <div class="book-cover-container">
                                    <?php $theme_idx = (($index + 1) % 6) + 1; ?>
                                    <div class="book-cover-fallback cover-theme-<?php echo $theme_idx; ?>">
                                        <span class="fallback-badge">Bazaar</span>
                                        <div class="fallback-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                        <div class="fallback-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                    </div>
                                </div>
                                
                                <!-- Card Body -->
                                <div class="book-card-body">
                                    <span class="book-card-category"><?php echo htmlspecialchars($book['category']); ?></span>
                                    <h3 class="h5 mb-1 text-truncate-2" style="height: 48px;">
                                        <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="book-title-link fw-bold" id="book-link-<?php echo $book['id']; ?>">
                                            <?php echo htmlspecialchars($book['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="book-card-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                                    
                                    <div class="book-card-price">₦<?php echo number_format($book['price'], 2); ?></div>
                                    
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-premium-secondary w-100 py-2 fs-7" id="btn-view-<?php echo $book['id']; ?>">
                                                Details
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <?php if ($is_admin): ?>
                                                <!-- Edit Catalog Switch Link for Admin -->
                                                <a href="admin.php?tab=edit_book&id=<?php echo $book['id']; ?>" class="btn btn-premium-primary w-100 py-2 fs-7" id="btn-edit-<?php echo $book['id']; ?>">
                                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                                </a>
                                            <?php else: ?>
                                                <form action="cart_action.php?action=add&id=<?php echo $book['id']; ?>" method="POST">
                                                    <button type="submit" class="btn btn-premium-primary w-100 py-2 fs-7" id="btn-add-<?php echo $book['id']; ?>">
                                                        <i class="fa-solid fa-cart-plus me-1"></i> Add
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty Results -->
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fa-solid fa-box-open text-muted-light" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-secondary">No Books Found</h3>
                    <p class="text-muted-dark">We couldn't find any matches. Try adjusting your filters or search keywords.</p>
                    <a href="index.php" class="btn btn-premium-primary px-4 mt-2">Browse All Books</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/header.php'; ?>
