# BookBazaar — Online Bookstore

BookBazaar is a responsive full-stack online bookstore application built with native PHP, MySQL, HTML, CSS, and JavaScript. It provides a practical e-commerce experience with user authentication, book browsing, shopping cart management, purchases, reviews, and an administrative catalog.

## Technology Stack

* **Backend:** PHP 7.4+ / PHP 8.x
* **Database:** MySQL / MariaDB
* **Database Access:** PDO with prepared statements
* **Frontend:** HTML5, CSS3, JavaScript
* **UI:** Bootstrap 5
* **Authentication:** PHP sessions and password hashing
* **Development Environment:** XAMPP

## Features

### User Features

* User registration and login
* Secure password hashing
* Browse available books
* Search and view book details
* Add books to cart
* Update cart quantities
* Checkout and purchase tracking
* Purchase history
* Book reviews and comments
* Browsing history

### Admin Features

* Admin authentication
* Manage books in the catalog
* Add new books
* Edit existing books
* Delete books
* Manage users
* Review administrative information

## Project Structure

```text
bookstore/
│
├── bookstore.sql
├── config.php
├── db_init.php
├── header.php
├── footer.php
├── index.php
├── book_detail.php
├── payment.php
├── register.php
├── login.php
├── logout.php
├── cart.php
├── cart_action.php
├── purchased_books.php
├── admin.php
├── admin_book_action.php
│
└── static/
    ├── css/
    │   └── style.css
    └── js/
        └── cart.js
```

## Setup and Installation

### Requirements

Before running the application, install:

* XAMPP
* Apache
* MySQL or MariaDB
* PHP 7.4 or later
* A modern web browser

### Step 1 — Copy the Project

Copy the `bookstore` folder into the XAMPP `htdocs` directory.

On Windows, the default location is:

```text
C:\xampp\htdocs\bookstore\
```

### Step 2 — Start XAMPP

Open the XAMPP Control Panel and start:

* Apache
* MySQL

### Step 3 — Configure the Database

The application uses MySQL through PDO.

The default local development configuration uses:

```text
Host: localhost
Database: bookstore
Username: admin
Password: admin
```

For a local XAMPP environment, the database can be initialized using the provided `bookstore.sql` file through phpMyAdmin.

Open:

```text
http://localhost/phpmyadmin
```

Create/import the `bookstore` database and import `bookstore.sql`.

### Step 4 — Run the Application

Open your browser and visit:

```text
http://localhost/bookstore/
```

The BookBazaar application should now be available locally.

## Database

The project includes the following main database tables:

* `users`
* `books`
* `cart`
* `purchases`
* `browsing_history`
* `reviews`
* `comments`

The database uses relational constraints and foreign keys to maintain relationships between users, books, carts, purchases, reviews, and comments.

## Security Considerations

BookBazaar was developed with several common web application security practices in mind:

* PDO prepared statements to reduce SQL injection risks
* Password hashing using PHP's `password_hash()`
* Session-based authentication
* Server-side input validation
* Environment-variable support for database configuration
* Database credentials are not hard-coded with production passwords
* Database error details are not intentionally exposed to users

> **Note:** `payment.php` is a demonstration/mock payment interface for this portfolio project. It is not connected to a real payment processor and should not be used with real card information.

## Development Purpose

This project was developed as a practical software development project to demonstrate skills in:

* Web development
* PHP programming
* MySQL database management
* Backend development
* Frontend development
* Authentication
* CRUD operations
* Database design
* Application testing
* Problem solving
* Responsive user interface development

## Author

**Ahmad Sagir**

Web Developer | Computer Science & Information Technology

* **GitHub:** [ahmaddeedatt2](https://github.com/ahmaddeedatt2)
* **LinkedIn:** [Ahmad Sagir](https://www.linkedin.com/in/ahmad-sagir-8b0444260)

---

This project is intended for learning, portfolio, and demonstration purposes.
