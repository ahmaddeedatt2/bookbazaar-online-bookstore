<?php
// Admin Book Action Controller
// File: admin_book_action.php

require_once __DIR__ . '/config.php';

// Route guards: strictly require admin username
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    $_SESSION['flash'] = ['message' => 'Access denied. Administrator privileges required.', 'type' => 'error'];
    header("Location: index.php");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $author = isset($_POST['author']) ? trim($_POST['author']) : '';
            $category = isset($_POST['category']) ? trim($_POST['category']) : '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            
            if ($title === '' || $author === '' || $category === '' || $description === '') {
                $_SESSION['flash'] = ['message' => 'All fields are required to add a book.', 'type' => 'error'];
                header("Location: admin.php?tab=add_book");
                exit;
            }
            
            // Price validator constraint (₦1000 and above)
            if ($price < 1000.0) {
                $_SESSION['flash'] = ['message' => 'Book price must vary from 1000 Naira and above.', 'type' => 'error'];
                header("Location: admin.php?tab=add_book");
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO books (title, author, category, price, description) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $author, $category, $price, $description]);
                $_SESSION['flash'] = ['message' => '"' . $title . '" successfully uploaded to catalog.', 'type' => 'success'];
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['message' => 'Failed to upload book to database: ' . $e->getMessage(), 'type' => 'error'];
            }
            break;
            
        case 'edit':
            if ($book_id <= 0) {
                $_SESSION['flash'] = ['message' => 'Invalid book request.', 'type' => 'error'];
                header("Location: admin.php?tab=books");
                exit;
            }
            
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $author = isset($_POST['author']) ? trim($_POST['author']) : '';
            $category = isset($_POST['category']) ? trim($_POST['category']) : '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            
            if ($title === '' || $author === '' || $category === '' || $description === '') {
                $_SESSION['flash'] = ['message' => 'All fields are required to modify a book.', 'type' => 'error'];
                header("Location: admin.php?tab=edit_book&id=" . $book_id);
                exit;
            }
            
            // Price validator constraint (₦1000 and above)
            if ($price < 1000.0) {
                $_SESSION['flash'] = ['message' => 'Book price must vary from 1000 Naira and above.', 'type' => 'error'];
                header("Location: admin.php?tab=edit_book&id=" . $book_id);
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, price = ?, description = ? WHERE id = ?");
                $stmt->execute([$title, $author, $category, $price, $description, $book_id]);
                $_SESSION['flash'] = ['message' => '"' . $title . '" details successfully updated.', 'type' => 'success'];
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['message' => 'Failed to save book modifications.', 'type' => 'error'];
            }
            break;
            
        case 'delete':
            if ($book_id <= 0) {
                $_SESSION['flash'] = ['message' => 'Invalid book request.', 'type' => 'error'];
                break;
            }
            try {
                // Delete from catalog (cascades automatically delete items in carts/purchases)
                $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
                $stmt->execute([$book_id]);
                $_SESSION['flash'] = ['message' => 'Book successfully removed from catalog.', 'type' => 'success'];
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['message' => 'Failed to delete book: ' . $e->getMessage(), 'type' => 'error'];
            }
            break;
            
        default:
            $_SESSION['flash'] = ['message' => 'Invalid admin action request.', 'type' => 'error'];
            break;
    }
}

// Redirect back to books tab by default
header("Location: admin.php?tab=books");
exit;
?>
