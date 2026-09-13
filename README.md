# UniFind — Campus Lost & Found System

UniFind is a web-based Lost and Found management system designed for university and college campuses. It allows students and staff to report lost belongings, post items they have discovered, browse recent reports with interactive filtering, and directly connect with owners or finders to recover items quickly and securely.

---

## 🌟 Key Features

### For Students & Campus Community
* **Account Management**: Simple registration and secure login with bcrypt password hashing.
* **Report Lost Items**: Submit detailed reports with photos, categories, lost location on campus, and date.
* **Report Found Items**: Post details and pictures of items discovered around campus.
* **Smart Search & Filters**: Search items by title/keywords and filter by status (Lost/Found/Returned), categories, and campus locations.
* **Item Details & Contact**: View high-resolution item photos, detailed descriptions, and verified reporter contact information.
* **My Reports Dashboard**: Manage, edit, or delete items you have personally reported.

### For Administrators
* **Admin Dashboard**: Real-time metrics on total reports, active items, recovered items, and registered users.
* **Item Moderation**: Review, approve, update item status (Active, Pending, Returned), or remove invalid entries.
* **User Management**: View registered students and administrators.
* **Category Management**: Add, update, or remove item categories dynamically.

---

## 🛠️ Technology Stack

* **Backend**: PHP (Object-oriented PDO database abstraction)
* **Database**: MySQL / MariaDB
* **Frontend**: HTML5, CSS3, Vanilla JavaScript (no heavy runtime frameworks required)
* **Styling & UI**: Modern Dark UI theme, responsive mobile-first grid, custom confirmation modals, and micro-animations.

---

## 🚀 Getting Started Locally

### Prerequisites
* [XAMPP](https://www.apachefriends.org/) (or WampServer / LAMP) with **PHP 7.4+ or 8.x** and **MySQL**.

### Installation Steps

1. **Clone or Download the Project**:
   Place the project folder inside your web server root directory:
   ```bash
   # For XAMPP on Windows:
   C:\xampp\htdocs\lost-found
   ```

2. **Start Services**:
   Open XAMPP Control Panel and start **Apache** and **MySQL**.

3. **Import the Database**:
   * Open your browser and navigate to `http://localhost/phpmyadmin`.
   * Create a new database named: `unifind_db`
   * Select `unifind_db`, go to the **Import** tab, and import:
     1. `database/unifind_db.sql` (creates schema and tables)
     2. `database/demo_data.sql` (optional: preloads sample items and demo users)

4. **Verify Database Configuration**:
   Open `config/database.php` and verify your local credentials:
   ```php
   $base_url = '/lost-found';
   $host     = 'localhost';
   $dbname   = 'unifind_db';
   $username = 'root';
   $password = ''; // Default for XAMPP is empty
   ```

5. **Launch the Application**:
   Navigate to:
   ```text
   http://localhost/lost-found
   ```

---

## 🔐 Default Demo Accounts

If you imported `database/demo_data.sql`, the following test accounts are readily available:

| Role | Email | Password | Access |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@sliate.edu` | `password` | Full administrative dashboard & moderation |
| **Student** | `student@sliate.edu` | `password` | Student portal & report management |

---

## 📁 Project Directory Structure

```text
lost-found/
├── admin/                 # Admin panel views and controllers
│   ├── dashboard.php      # Main administration metrics & overview
│   ├── items.php          # Manage & moderate campus item reports
│   ├── users.php          # User list and management
│   ├── categories.php     # Category configuration
│   └── admin-action.php   # Admin status/delete controller
├── assets/
│   ├── css/
│   │   └── style.css      # Core responsive stylesheets and themes
│   └── js/
│       └── script.js      # Modal controls, search helpers, interactive UI
├── config/
│   └── database.php       # PDO connection settings
├── database/
│   ├── unifind_db.sql     # Database schema definition
│   └── demo_data.sql      # Sample data with campus reports
├── includes/
│   ├── auth.php           # Session guard & authentication helpers
│   ├── header.php         # Navigation bar and global layout head
│   ├── footer.php         # Footer with quick links & info
│   └── report_card.php    # Reusable card component for item listings
├── uploads/               # Item photo storage directory
├── index.php              # Homepage with hero section & recent items
├── items.php              # Full item catalog with search & filters
├── item-details.php       # Item inspection & reporter contact
├── report-lost.php        # Lost item submission form
├── report-found.php       # Found item submission form
├── my-reports.php         # Student personal item dashboard
├── edit-report.php        # Edit existing submitted report
├── login.php              # User authentication
├── register.php           # Student registration
└── logout.php             # Session termination
```

---

## 🌐 Hosting & Deployment Guide

To deploy this project online:

1. **Choose a PHP & MySQL Host**:
   * Free options: **InfinityFree**, **000webhost**, or **Render / Railway** with a Dockerfile/PHP buildpack.
   * Standard cPanel/Shared Hosting: Any host (Namecheap, Hostinger, GoDaddy, Bluehost) with PHP & cPanel.

2. **Upload Files**:
   * Upload all project files to `public_html` (or your subdomain folder).
   * Note: Ensure the `uploads/` directory has write permissions (`chmod 775` or `777`).

3. **Import Database**:
   * Create a MySQL database and user in your hosting control panel (e.g. cPanel MySQL Database Wizard).
   * In phpMyAdmin on your hosting server, import `database/unifind_db.sql` and `database/demo_data.sql`.

4. **Update `config/database.php`**:
   * Change `$base_url = '';` (if your app is in the domain root) or `$base_url = '/subfolder';`
   * Update `$host`, `$dbname`, `$username`, and `$password` with the live database credentials provided by your hosting host.

---

## 📄 License
This project was developed for educational purposes as an HNDIT software development project. Feel free to use and adapt it for academic demonstrations.
