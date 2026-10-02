# BOOK BAZAAR 📚 (XAMPP PHP & MySQL Version)

BOOK BAZAAR is a modern, responsive, and secure full-stack Online Bookstore application built using native **PHP** and **MySQL** for standard **XAMPP Apache** hosting.

---

## 🛠️ Technology Stack
- **Backend:** PHP 7.4+ / PHP 8.x
- **Database Interface:** PDO (PHP Data Objects) - utilizes prepared statements to prevent SQL Injection
- **Database System:** MySQL (via XAMPP / MariaDB)
- **Frontend Theme:** HTML5, Custom CSS3 Variables, JavaScript, and Bootstrap 5 (with Google Fonts 'Outfit' and 'Inter')
- **Authentication Security:** PHP BCRYPT Password Hashing (`password_hash()`)

---

## 📂 Project Structure
```text
bookstore/
│
├── bookstore.sql           # Raw database schema initialization & seed script
├── config.php              # Shared DB credentials and active PDO connection helper
├── db_init.php             # Auto-setup script creating database, tables, and book entries
├── header.php              # Common HTML head, navigation bar, and flash notifications layout
├── footer.php              # Common HTML footer layout and JS imports
├── index.php               # Homepage displaying Hero, Search bar, and book grids
├── book_detail.php         # Book details, verified reviews, and discussion boards
├── payment.php             # Secure mockup payment gateway checkout page
├── register.php            # User registration form and controller
├── login.php               # User login authentication form and controller
├── logout.php              # Logout script destroying sessions
├── cart.php                # Shopping cart summary table and checkout totals
├── cart_action.php         # Central controller for cart mutations & order simulations
├── purchased_books.php     # User orders and visual progress tracking status
├── admin.php               # Administrative panel for managing approvals and catalog
├── admin_book_action.php   # Administrative actions for books
│
└── static/                 # Styles and script assets
    ├── css/
    │   └── style.css       # Custom stylesheets (glassmorphism UI, 3D hardcover covers)
    └── js/
        └── cart.js         # Interface scripts (quantity inputs, popup checks, timers)
```

---

## 🚀 Setup & Run Instructions (using XAMPP)

Running this PHP version is extremely simple because it runs natively inside XAMPP without needing command line Python servers:

### Step 1: Copy the folder to XAMPP htdocs
1. Copy the entire **`bookstore`** folder.
2. Paste it directly into your XAMPP installation directory's **`htdocs`** folder:
   * **Default Windows Path:** `C:\xampp\htdocs\`
   * After copying, the folder structure should look like: `C:\xampp\htdocs\bookstore\index.php`, etc.

### Step 2: Start Apache and MySQL in XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Run and View the App
1. Open your web browser.
2. Type and go to the address: **`http://localhost/bookstore/`**
3. **Database Auto-Setup:** The application has built-in auto-setup logic. When you visit this URL, the script will automatically create the database `bookstore`, set up the tables (`users`, `books`, `cart`, `browsing_history`, `reviews`, `comments`), and populate the 108 books. No extra commands are needed!
4. *(Optional verification)* Go to `http://localhost/phpmyadmin` in your browser to verify that the database `bookstore` and its tables have been successfully created.

---

## 🔐 Key Architectural Highlights
1. **Prepared SQL Statements (PDO):** Built completely on PDO parameterized prepared queries. User inputs (such as search keys, logins, or quantity values) are bound securely to protect against **SQL Injection**.
2. **Standard Cryptography:** Password storage relies on BCRYPT hashing. Plaintext passwords are never stored in the database.
3. **Relational Database Integrations:** Enforces database constraints with cascading deletes across linked tables (`cart` and `purchases` reference `users` and `books` via foreign key bindings).
