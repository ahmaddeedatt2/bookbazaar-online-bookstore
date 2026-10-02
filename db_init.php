```php
<?php

/**
 * BookBazaar
 * Database Initialization
 *
 * Creates the BookBazaar database and required tables when the
 * application is started for the first time.
 *
 * This file contains database structure only.
 * Demo/sample data should be imported separately.
 */

function init_database($host, $user, $password, $dbname)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | Connect to MySQL Server
        |--------------------------------------------------------------------------
        */

        $pdo = new PDO(
            "mysql:host={$host};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Create Database
        |--------------------------------------------------------------------------
        */

        $safeDatabaseName = str_replace('`', '``', $dbname);

        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$safeDatabaseName}`
             CHARACTER SET utf8mb4
             COLLATE utf8mb4_unicode_ci"
        );

        $pdo->exec("USE `{$safeDatabaseName}`");

        /*
        |--------------------------------------------------------------------------
        | Users Table
        |--------------------------------------------------------------------------
        */

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(150) NOT NULL UNIQUE,
                `email` VARCHAR(150) NOT NULL UNIQUE,
                `password_hash` VARCHAR(256) NOT NULL,
                `profile_picture` VARCHAR(255) DEFAULT NULL,
                `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
                `preferred_categories` VARCHAR(500) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
        |--------------------------------------------------------------------------
        | Books Table
        |--------------------------------------------------------------------------
        */

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `books` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `author` VARCHAR(255) NOT NULL,
                `price` DECIMAL(10,2) NOT NULL,
                `description` TEXT NOT NULL,
                `category` VARCHAR(100) NOT NULL,
                `cover_image` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
        |--------------------------------------------------------------------------
        | Cart Table
        |--------------------------------------------------------------------------
        */

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cart` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `book_id` INT NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT `fk_cart_user`
                    FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`)
                    ON DELETE CASCADE,

                CONSTRAINT `fk_cart_book`
                    FOREIGN KEY (`book_id`)
                    REFERENCES `books` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
        |--------------------------------------------------------------------------
        | Purchases Table
        |--------------------------------------------------------------------------
        */

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `purchases` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `book_id` INT NOT NULL,
                `quantity` INT NOT NULL,
                `price_paid` DECIMAL(10,2) NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
                `purchased_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT `fk_purchases_user`
                    FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`)
                    ON DELETE CASCADE,

                CONSTRAINT `fk_purchases_book`
                    FOREIGN KEY (`book_id`)
                    REFERENCES `books` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
        |--------------------------------------------------------------------------
        | Browsing History Table
        |--------------------------------------------------------------------------
        */

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `browsing_history` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `book_id` INT NOT NULL,
                `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT `fk_history_user`
                    FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`)
                    ON DELETE CASCADE,

                CONSTRAINT `fk_history_book`
                    FOREIGN KEY (`book_id`)
                    REFERENCES `books` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
        |--------------------------------------------------------------------------
        | Reviews Table
        |--------------------------------------------------------------------------
        */

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `reviews` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `book_id` INT NOT NULL,
                `rating` INT NOT NULL,
                `review_text` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT `chk_review_rating`
                    CHECK (`rating` BETWEEN 1 AND 5),

                CONSTRAINT `fk_reviews_user`
                    FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`)
                    ON DELETE CASCADE,

                CONSTRAINT `fk_reviews_book`
                    FOREIGN KEY (`book_id`)
                    REFERENCES `books` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
        |--------------------------------------------------------------------------
        | Comments / Discussion Table
        |--------------------------------------------------------------------------
        */

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `comments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `book_id` INT NOT NULL,
                `comment_text` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT `fk_comments_user`
                    FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`)
                    ON DELETE CASCADE,

                CONSTRAINT `fk_comments_book`
                    FOREIGN KEY (`book_id`)
                    REFERENCES `books` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
        |--------------------------------------------------------------------------
        | Compatibility Checks
        |--------------------------------------------------------------------------
        | These checks allow older BookBazaar installations to continue
        | working when new columns are introduced.
        */

        ensure_column(
            $pdo,
            'users',
            'profile_picture',
            "ALTER TABLE `users`
             ADD COLUMN `profile_picture` VARCHAR(255) DEFAULT NULL
             AFTER `password_hash`"
        );

        ensure_column(
            $pdo,
            'users',
            'is_approved',
            "ALTER TABLE `users`
             ADD COLUMN `is_approved` TINYINT(1) NOT NULL DEFAULT 0
             AFTER `profile_picture`"
        );

        ensure_column(
            $pdo,
            'users',
            'preferred_categories',
            "ALTER TABLE `users`
             ADD COLUMN `preferred_categories` VARCHAR(500) DEFAULT NULL
             AFTER `is_approved`"
        );

        ensure_column(
            $pdo,
            'books',
            'category',
            "ALTER TABLE `books`
             ADD COLUMN `category` VARCHAR(100) NOT NULL
             AFTER `description`"
        );

        ensure_column(
            $pdo,
            'purchases',
            'status',
            "ALTER TABLE `purchases`
             ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'Pending'
             AFTER `price_paid`"
        );

    } catch (PDOException $e) {

        /*
        |--------------------------------------------------------------------------
        | Safe Error Handling
        |--------------------------------------------------------------------------
        */

        error_log(
            'BookBazaar database initialization failed: ' .
            $e->getMessage()
        );

        die(
            'BookBazaar could not initialize the database. ' .
            'Make sure MySQL is running and the database configuration is correct.'
        );
    }
}


/**
 * Check whether a column exists and add it when necessary.
 *
 * @param PDO    $pdo
 * @param string $table
 * @param string $column
 * @param string $alterQuery
 */
function ensure_column(PDO $pdo, $table, $column, $alterQuery)
{
    $allowedTables = [
        'users',
        'books',
        'purchases'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return;
    }

    $stmt = $pdo->prepare(
        "SHOW COLUMNS FROM `{$table}` LIKE ?"
    );

    $stmt->execute([$column]);

    if (!$stmt->fetch()) {
        $pdo->exec($alterQuery);
    }
}
```
