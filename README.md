# IRCAD Africa Intranet

A modern, secure, and feature-rich intranet platform for IRCAD Africa, designed to facilitate internal communication, document sharing, and resource management.

## Features

### User Management
- Secure user authentication and authorization
- Role-based access control (Admin, Staff)
- User profile management
- Password security with bcrypt encryption

### Announcements
- Create and manage announcements
- Rich text editor for content creation
- File attachments support
- Categorized announcements
- Priority-based display

### Internal Links
- Quick access to important resources
- Customizable link categories
- Icon support for visual organization
- Admin-managed link collection

### Dashboard
- Personalized user dashboard
- Recent announcements display
- Quick access to internal links
- Responsive design for all devices

## Tech Stack

- **Backend**: Node.js with Express.js
- **Frontend**: EJS templating engine
- **Database**: MySQL
- **Authentication**: Express-session
- **File Upload**: Multer
- **Rich Text Editor**: Quill
- **Styling**: Custom CSS with modern design principles

## Prerequisites

- Node.js (v14 or higher)
- MySQL Server
- XAMPP (or similar local development environment)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/intranet.ircad.africa.git
cd intranet.ircad.africa
```

2. Install dependencies:
```bash
npm install
```

3. Set up the database:
- Create a MySQL database named `ircad_intranet`
- Import the database schema from `database/ircad_intranet.sql`

4. Configure environment variables:
- Create a `.env` file in the root directory
- Add the following variables:
```
DB_HOST=localhost
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=ircad_intranet
SESSION_SECRET=your_session_secret
```

5. Start the application:
```bash
npm start
```

The application will be available at `http://localhost:3000`

## Project Structure

```
intranet.ircad.africa/
├── config/
│   └── database.js
├── public/
│   ├── css/
│   ├── js/
│   └── uploads/
├── routes/
│   ├── announcements.js
│   ├── auth.js
│   ├── internal-links.js
│   └── users.js
├── utils/
│   └── fileUpload.js
├── views/
│   ├── announcements/
│   ├── internal-links/
│   ├── users/
│   └── partials/
├── app.js
└── package.json
```

## Usage

### Admin Features
1. User Management
   - Create new user accounts
   - Edit user profiles
   - Manage user roles
   - Reset passwords

2. Announcements
   - Create new announcements
   - Upload attachments
   - Set announcement priority
   - Manage existing announcements

3. Internal Links
   - Add new quick links
   - Organize links by category
   - Customize link icons
   - Manage link visibility

### Staff Features
1. Dashboard
   - View recent announcements
   - Access quick links
   - Update personal profile

2. Announcements
   - View all announcements
   - Download attachments
   - Filter by category

## Security Features

- Secure password hashing with bcrypt
- Session-based authentication
- Role-based access control
- Input validation and sanitization
- Secure file upload handling
- XSS protection

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please contact the IT department at IRCAD Africa or create an issue in the repository.

## Acknowledgments

- IRCAD Africa for providing the opportunity to develop this intranet
- All contributors who have helped shape this project
- The open-source community for the tools and libraries used 