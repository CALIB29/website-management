## Admin Dashboard Functionality

This system is an admin dashboard for managing a collection of websites. Below is a summary of its features and structure:

### Features
- **Authentication:** Only logged-in admins can access the dashboard (session-based check).
- **Website Listing:** All websites are listed as cards with:
  - Thumbnail (auto-generated)
  - Name and description
  - Action buttons: Edit, Analyze, Delete (with confirmation), and Visit Site (external link)
- **Add Website:** Button to add a new website.
- **Navigation:** Includes sidebar and bottom navigation for easy access to other admin pages.
- **Styling:** Uses external CSS and FontAwesome for icons.

### Removed Features
- Customizable widgets (modal, JS logic, and UI for toggling widgets) have been removed.
- Uptime status and performance suggestions are no longer shown.

### Security
- Session-based authentication for admin access.
- Confirmation prompt for destructive actions (delete).

### Extensibility
- Modular structure makes it easy to add new features or actions per website.

### File Overview
- `dashboard.php`: Main admin dashboard (see above for details)
- `add_website.php`, `edit_website.php`, `delete_website.php`: CRUD operations for websites
- `analysis_report.php`, `website-analyzer.php`, `analyzer.php`: Website analysis tools
- `activity_log.php`, `activity_log.sql`: Admin activity tracking
- `create_admin.php`, `login.php`, `logout.php`, `unlock_account.php`: Admin user management
- `settings.php`: Admin settings
- `sidebar.php`, `bottom-nav.php`: Navigation components
- `style.css`, `script.js`: Styling and client-side interactivity

---
For further details or to add new features, see the code comments in each file or contact the developer.
# Website Management System for Sta. Rita College (SRC)

A comprehensive web application designed to manage and analyze multiple websites for Sta. Rita College (SRC). This system provides an intuitive interface for administrators to track, monitor, and analyze their web properties.

## 🌟 Features

- **User Authentication**
  - Secure login/logout functionality
  - Session management
  - Admin account management
  - Account recovery system

- **Website Management**
  - Add new websites with details
  - Edit existing website information
  - Delete websites
  - View all websites in a responsive grid
  - Website thumbnail generation

- **Security Analysis**
  - Automated website security scanning
  - Detailed analysis reports
  - Security recommendations
  - Vulnerability assessment

- **User Interface**
  - Responsive design for all devices
  - Intuitive dashboard
  - Clean and modern interface
  - Loading animations and visual feedback
  - Mobile-optimized navigation

## 🛠️ Technical Stack

- **Frontend**
  - HTML5, CSS3, JavaScript
  - Responsive design with CSS Grid and Flexbox
  - Font Awesome for icons
  - Custom loading animations and skeleton screens

- **Backend**
  - PHP 7.4+
  - MySQL Database
  - Session-based authentication
  - RESTful API design principles

- **Security Features**
  - Prepared statements to prevent SQL injection
  - Input validation and sanitization
  - Password hashing with PHP's password_hash()
  - CSRF protection
  - Secure session management

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Installation

1. **Database Setup**
   - Create a new database named `website_management` in your MySQL server
   - Import the following SQL to create the required tables:

   ```sql
   CREATE TABLE `admins` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `username` varchar(255) NOT NULL,
     `password` varchar(255) NOT NULL,
     PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

2. **Configuration**
   - Update `database.php` with your database credentials
   - Set proper file permissions for uploads directory

3. **Create Admin Account**
   - Use the following PHP snippet to generate a hashed password:
   ```php
   <?php
   $password = 'your_secure_password';
   echo password_hash($password, PASSWORD_DEFAULT);
   ?>
   ```
   - Insert the generated hash into the `admins` table

## 📁 Project Structure

```
website-management/
├── add_website.php      # Form to add new websites
├── analysis_report.php  # Displays security analysis results
├── analyzer.php         # Handles website security analysis
├── bottom-nav.php       # Bottom navigation bar
├── create_admin.php     # Admin account creation utility
├── dashboard.php        # Main dashboard showing all websites
├── database.php         # Database connection settings
├── delete_website.php   # Handles website deletion
├── download.php         # File download handler
├── edit_website.php     # Edit website details
├── images/              # Contains application images
├── index.php            # Entry point (redirects to login)
├── login.php            # User authentication
├── logout.php           # Session termination
├── script.js            # Client-side JavaScript
├── settings.php         # User settings and profile management
├── sidebar.php          # Side navigation component
├── style.css            # Main stylesheet
└── unlock_account.php   # Account recovery/unlock functionality
```

## 🔍 Usage Guide

### Dashboard
- Displays all managed websites in a responsive grid
- Each card shows website name, description, and quick actions
- Access analysis reports with a single click

### Adding a Website
1. Click "Add Website" in the sidebar
2. Fill in website details (name, URL, description)
3. Submit the form to add to your dashboard

### Security Analysis
1. Click the shield icon on any website card
2. View detailed security report
3. Follow recommendations to improve security

### Account Management
- Update your username and password in Settings
- Secure your account with a strong password
- Log out when using shared devices

## 📱 Mobile Responsiveness

The application features a fully responsive design that works seamlessly across all device sizes. The interface automatically adjusts to provide the best user experience on both desktop and mobile devices.

## 🔒 Security Best Practices

- Always use strong, unique passwords
- Keep the application and server software updated
- Regularly backup your database
- Monitor access logs for suspicious activity
- Restrict access to sensitive files and directories

## 📝 License

This project is licensed under the MIT License.

## 🙏 Acknowledgments

- Built for Sta. Rita College (SRC)
- Uses Font Awesome for icons
- Security analysis powered by custom algorithms

## 📧 Support

For technical support or inquiries, please contact the development team.

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
