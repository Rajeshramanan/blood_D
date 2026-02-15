# Blood Donation Management System (BDMS)

A complete, responsive, and secure Blood Donation Web Application built with core PHP, MySQL, HTML5, and CSS3.

## Features

### Public Area
- **Home Page**: Overview and call to action.
- **Login/Register**: Secure authentication system.
- **About**: Information about the platform.

### User Dashboard
- **Donate Blood**: Register as a donor (eligibility check included).
- **Request Blood**: Request blood samples for emergencies.
- **Matching**: Automatic donor matching based on blood group and availability.
- **History**: View donation and request history.
- **Profile**: Manage personal details.

### Admin Dashboard
- **Overview**: System statistics (Users, Donors, Requests).
- **Manage Users**: View and delete users.
- **Manage Requests**: Update request status (Pending, Accepted, Completed).
- **Statistics**: Detailed reports on blood groups and urgency levels.

## Setup Instructions

### Prerequisites
- XAMPP (or any PHP/MySQL environment)
- A web browser

### Installation
1. **Move Files**: Copy the `blood_app` folder to your server's root directory (e.g., `C:\xampp\htdocs\blood_app`).
2. **Database Setup**:
   - Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
   - Create a new database named `blood_donation_db`.
   - Import the `db_schema.sql` file located in the project root.
3. **Configuration**:
   - Open `config/db.php`.
   - Ensure the database credentials match your local setup (Default: user=`root`, password=``).

### Default Admin Credentials
- **Email**: `admin@bloodapp.com`
- **Password**: `admin123`

## Usage
1. Open your browser and go to `http://localhost/blood_app`.
2. Register a new user account to test donor/requester features.
3. Login as Admin to manage the system.

## Security
- Password Hashing (`password_hash`)
- Prepared Statements (PDO) to prevent SQL Injection
- Session Management
- XSS Protection (Output Escaping)
