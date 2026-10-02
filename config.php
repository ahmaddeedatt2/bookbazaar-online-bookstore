```php
<?php

/**
 * BookBazaar
 * Core Application Settings & Database Connection
 *
 * For local XAMPP development:
 * - Host: localhost
 * - User: root
 * - Password: blank by default
 * - Database: bookstore
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
| Environment variables can be used in production.
| The values below provide sensible defaults for local XAMPP development.
*/

$db_host = getenv('BOOKBAZAAR_DB_HOST') ?: 'localhost';
$db_user = getenv('BOOKBAZAAR_DB_USER') ?: 'root';
$db_pass = getenv('BOOKBAZAAR_DB_PASS') ?: '';
$db_name = getenv('BOOKBAZAAR_DB_NAME') ?: 'bookstore';

/*
|--------------------------------------------------------------------------
| Database Initialization
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/db_init.php';

init_database(
    $db_host,
    $db_user,
    $db_pass,
    $db_name
);

/*
|--------------------------------------------------------------------------
| PDO Database Connection
|--------------------------------------------------------------------------
*/

try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);

} catch (PDOException $e) {

    /*
     * Avoid exposing database details to users.
     * Detailed errors should be checked in the server logs during development.
     */
    error_log('BookBazaar database connection failed: ' . $e->getMessage());

    die('Unable to connect to the database. Please check the application configuration.');
}

/*
|--------------------------------------------------------------------------
| Cart Count Helper
|--------------------------------------------------------------------------
*/

function update_session_cart_count(PDO $pdo): void
{
    if (isset($_SESSION['user_id'])) {

        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(quantity), 0)
             FROM cart
             WHERE user_id = ?"
        );

        $stmt->execute([$_SESSION['user_id']]);

        $_SESSION['cart_count'] = (int) $stmt->fetchColumn();

    } else {

        $_SESSION['cart_count'] = 0;
    }
}

/*
|--------------------------------------------------------------------------
| Synchronize Cart Count
|--------------------------------------------------------------------------
*/

update_session_cart_count($pdo);

?>
```
