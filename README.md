# D.I.S Property Management System

A comprehensive property management system that handles real estate properties, device sales, and service applications.

## Features

- Property Management
  - Add, edit, delete properties
  - Property listings with images
  - Property status tracking
  - Purchase and application management

- User Management
  - User registration and authentication
  - Role-based access control (Admin/User)
  - User profile management
  - Activity tracking

- Device Sales
  - Manage device inventory
  - Device listings with specs
  - Purchase tracking

- Service Applications
  - Certificate of Occupancy (C of O)
  - Land Survey
  - Bill of Quantities (BOQ)
  - Application status tracking

- Blog Management
  - Create and manage blog posts
  - Support for images, videos, and YouTube links
  - Content management system

- Dashboard Analytics
  - Revenue tracking
  - Visitor analytics
  - Sales statistics
  - Application monitoring

## Installation

1. Clone the repository
```bash
git clone https://github.com/OGluck/D.I.S-property.app.git
```

2. Set up your XAMPP environment
- Place the project in `htdocs` directory
- Start Apache and MySQL services

3. Configure the database
- Import the database schema from `database/schema.sql`
- Update database credentials in `includes/db.php`

4. Configure PayStack API (for payments)
- Add your PayStack public key in the meta tag
- Update PayStack secret key in payment handlers

## Technology Stack

- Frontend:
  - HTML5, CSS3, JavaScript
  - Bootstrap 5
  - Chart.js for analytics
  - SweetAlert2 for notifications

- Backend:
  - PHP 7.4+
  - MySQL/MariaDB
  - PDO for database operations

- APIs & Services:
  - PayStack Payment Gateway
  - YouTube API integration
  - WhatsApp Business API

## Directory Structure

```
D.I.S-property.app/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── handler/
├── includes/
├── uploads/
│   ├── properties/
│   ├── devices/
│   └── blog/
└── favicon_io/
```

## Environment Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- mod_rewrite enabled
- PHP Extensions:
  - PDO
  - GD Library
  - FileInfo
  - JSON

## Security Features

- Password hashing
- SQL injection prevention
- XSS protection
- CSRF protection
- Input validation
- Secure file upload handling

## License

Copyright © 2024 D.I.S Groups. All rights reserved.

## Contact

For support or inquiries:
- Email: disrealty360@gmail.com
- Phone: +2349013020302
- Address: 3rd floor, Opic tower, Oke ilewo Abeokuta, Ogun state Nigeria
