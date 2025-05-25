# Medical System with Role-Based Access Control

A comprehensive medical system with role-based access control (RBAC) built using PHP, MySQL, HTML, CSS, and JavaScript.

## Features

- Four user roles: Admin, Doctor, Patient, and Githup (for GitHub OAuth users)
- JWT-based authentication
- Two-factor authentication for doctors and admins
- Role-based access control at both application and database levels
- Secure password hashing
- Activity logging
- Appointment management
- Medical records management
- User profile management

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- XAMPP (or similar local development environment)

## Installation

1. Clone the repository to your XAMPP htdocs directory:
```bash
cd C:\xampp\htdocs
git clone [repository-url] medical-system
```

2. Create a new MySQL database:
```sql
CREATE DATABASE medical_system;
```

3. Import the database schema:
```bash
mysql -u root -p medical_system < database/schema.sql
```

4. Install PHP dependencies:
```bash
cd medical-system
composer install
```

5. Configure the database connection:
- Open `config/database.php`
- Update the database credentials if needed

6. Set up the web server:
- Make sure XAMPP's Apache and MySQL services are running
- Access the application at `http://localhost/medical-system`

## Security Features

- JWT-based authentication
- Password hashing using bcrypt
- Two-factor authentication for sensitive roles
- SQL injection prevention using prepared statements
- XSS protection through output escaping
- CSRF protection
- Role-based access control
- Activity logging
- Secure session management

## Directory Structure

```
medical-system/
├── admin/              # Admin-specific files
├── doctor/            # Doctor-specific files
├── patient/           # Patient-specific files
├── githup/            # GitHub OAuth user dashboard
├── config/            # Configuration files
├── database/          # Database schema and migrations
├── includes/          # Shared PHP includes
├── src/               # Application source code
├── vendor/            # Composer dependencies
├── composer.json      # Composer configuration
└── README.md          # This file
```

## Usage

1. Access the login page at `http://localhost/medical-system/login.php`
2. Use the following default credentials for testing:
   - Admin: admin/admin123
   - Doctor: doctor/doctor123
   - Patient: patient/patient123

## Security Considerations

1. Change default passwords immediately after installation
2. Configure proper SSL/TLS certificates for production
3. Regularly update dependencies
4. Monitor activity logs
5. Implement rate limiting for login attempts
6. Regular database backups

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Social Login

- Users who sign in with GitHub are assigned the 'githup' role and redirected to a custom dashboard at `/githup/dashboard.php`.
- The `users` table now includes an `oauth_provider` column to track social login providers. 