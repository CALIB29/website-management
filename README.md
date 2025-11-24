# Website Management for Sta. Rita College (SRC)

This project is a website management system for Sta. Rita College (SRC) to handle 7 of their web systems with admin access.

## Setup

1.  **Database:**
    *   Create a new database named `website_management` in your MySQL server.
    *   Import the following SQL to create the `admins` table:

        ```sql
        CREATE TABLE `admins` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ```

    *   To create a new admin user, you can insert a new record into the `admins` table. Make sure to hash the password. You can use a PHP script with `password_hash()` to generate a secure password hash.

        Example PHP for creating a password hash:
        ```php
        <?php
        echo password_hash('your_password_here', PASSWORD_DEFAULT);
        ?>
        ```

2.  **Configuration:**
    *   Open `database.php` and update the database credentials (`$servername`, `$username`, `$password`, `$dbname`) if they are different from the defaults.

3.  **Running the application:**
    *   Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP).
    *   Access the application through your browser (e.g., `http://localhost/website-management`).

## Features

*   Admin login and authentication.
*   A central dashboard to manage multiple websites.
*   Secure logout functionality.
