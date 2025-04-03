# IRCAD Africa Intranet

A modern, secure, and user-friendly intranet system for IRCAD Africa, built with PHP and MySQL.

## Features

- **User Authentication**
  - Secure login system
  - Role-based access control (Admin/User)
  - Password hashing and security measures
  - Session management

- **Announcements**
  - Create and manage announcements
  - Rich text content support
  - File attachments
  - Featured images
  - Carousel display on dashboard

- **Internal Links**
  - Quick access to important resources
  - Categorized organization
  - Custom icons and colors
  - External link support

- **Mountable Services**
  - Network drive mounting instructions
  - Platform-specific commands
  - Copy-to-clipboard functionality
  - Service descriptions

- **Team Directory**
  - Team member profiles
  - Department organization
  - Contact information
  - Profile pictures
  - Quick access to team member details

- **User Management**
  - User profiles
  - Role management
  - Account settings
  - Profile picture upload

## Technology Stack

- **Backend**
  - PHP 7.4+
  - MySQL 5.7+
  - Apache/Nginx web server

- **Frontend**
  - HTML5
  - CSS3
  - JavaScript (ES6+)
  - Bootstrap 5
  - Font Awesome 6

- **Security**
  - Password hashing (PHP's password_hash)
  - CSRF protection
  - XSS prevention
  - SQL injection prevention
  - Secure session handling

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)
- GD or Imagick PHP extension for image handling
- File upload permissions

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/akiradawe/Intranet_Web.git
   cd ircad-intranet
   ```

2. Install dependencies (if using Composer):
   ```bash
   composer install
   ```

3. Set up the database:
   - Create a new MySQL database
   - Import the database schema from `/sql/intranet.sql`
   - Configure database connection in `.env`

4. Configure environment:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and other settings
   ```

5. Set up required directories:
   ```bash
   mkdir -p public/uploads/{profile-pictures,announcements}
   chmod -R 755 public/uploads
   ```

6. Configure your web server:
   - Point document root to the project directory
   - Enable mod_rewrite (Apache)
   - Ensure proper permissions are set

## Development

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes and commit:
   ```bash
   git add .
   git commit -m "Description of your changes"
   ```

3. Push to your branch:
   ```bash
   git push origin feature/your-feature-name
   ```

4. Create a Pull Request

## Directory Structure

```
intranet/
├── announcements/     # Announcements management
├── assets/           # Static assets (CSS, JS, images)
├── auth/             # Authentication system
├── config/           # Configuration files
├── includes/         # Common PHP includes
├── internal-links/   # Internal links management
├── mountable-services/ # Network services management
├── my-account/       # User account management
├── public/           # Public assets and uploads
├── sql/              # Database schema and migrations
├── team/             # Team management
├── users/            # User management
├── utils/            # Utility functions
├── .env.example      # Example environment configuration
└── index.php         # Main entry point
```

## Security Considerations

- Never commit sensitive information (API keys, passwords, etc.)
- Keep `.env` file in `.gitignore`
- Regularly update dependencies
- Follow security best practices for file uploads
- Implement proper access controls

## License

This project is proprietary and confidential. All rights reserved.

## Support

For support, please contact the IT department at IRCAD Africa. # Intranet_Web
# Intranet_Web
