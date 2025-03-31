const express = require('express');
const session = require('express-session');
const mysql = require('mysql');
const bodyParser = require('body-parser');
const path = require('path');
const userRoutes = require('./routes/users');
const announcementRoutes = require('./routes/announcements');
const internalLinkRoutes = require('./routes/internal-links');
const authRoutes = require('./routes/auth');
const mountableServicesRouter = require('./routes/mountable-services');
const myAccountRouter = require('./routes/my-account');
const teamRouter = require('./routes/team');

const app = express();

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.static('public'));
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(session({
    secret: 'your-secret-key',
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false } // Set to true if using HTTPS
}));

// Add this after your other middleware
app.use('/uploads', express.static('uploads'));

// Database connection
const db = mysql.createConnection({
    host: '127.0.0.1',
    user: 'intranet_user',
    password: 'tl((C@8r7Kp[Y_Yi',
    database: 'intranet_db'
});

// Make db available to routes
app.locals.db = db;

// Routes
app.get('/', (req, res) => {
    res.render('login', { error: null, path: '/login' });
});

// Add auth routes
app.use('/auth', authRoutes);

// Add user routes
app.use('/users', userRoutes);

// Add announcement routes
app.use('/announcements', announcementRoutes);

// Add internal link routes
app.use('/internal-links', internalLinkRoutes);

// Add mountable services routes
app.use('/mountable-services', mountableServicesRouter);

// Add my account routes
app.use('/my-account', myAccountRouter);

// Add team routes
app.use('/team', teamRouter);

// Dashboard route
app.get('/dashboard', (req, res) => {
    if (!req.session.user) {
        return res.redirect('/');
    }

    // Get announcements
    db.query('SELECT a.*, u.username FROM announcements a LEFT JOIN users u ON a.user_id = u.id ORDER BY created_at DESC LIMIT 5', 
        (error, announcements) => {
            if (error) {
                console.error(error);
                announcements = [];
            }

            // Get internal links
            db.query('SELECT * FROM internal_links ORDER BY title ASC', 
                (error, links) => {
                    if (error) {
                        console.error(error);
                        links = [];
                    }

                    // Get mountable services
                    db.query('SELECT * FROM mountable_services ORDER BY name ASC',
                        (error, services) => {
                            if (error) {
                                console.error(error);
                                services = [];
                            }

                            res.render('dashboard', { 
                                user: req.session.user,
                                announcements: announcements,
                                links: links,
                                services: services,
                                path: '/dashboard'
                            });
                        }
                    );
                }
            );
        }
    );
});

app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/');
});

// Announcements routes
app.get('/announcements', (req, res) => {
    if (!req.session.user) {
        return res.redirect('/');
    }

    db.query('SELECT a.*, u.username FROM announcements a LEFT JOIN users u ON a.user_id = u.id ORDER BY created_at DESC', 
        (error, announcements) => {
            if (error) {
                console.error(error);
                return res.render('announcements', { 
                    user: req.session.user, 
                    announcements: [],
                    error: 'Failed to load announcements'
                });
            }
            res.render('announcements', { 
                user: req.session.user, 
                announcements: announcements,
                error: null
            });
        }
    );
});

app.get('/announcements/new', (req, res) => {
    if (!req.session.user) {
        return res.redirect('/');
    }
    res.render('announcements/create', { user: req.session.user });
});

app.post('/announcements', (req, res) => {
    if (!req.session.user) {
        return res.redirect('/');
    }

    const { title, content } = req.body;
    db.query('INSERT INTO announcements (title, content, user_id) VALUES (?, ?, ?)',
        [title, content, req.session.user.id],
        (error) => {
            if (error) {
                console.error(error);
                return res.redirect('/announcements');
            }
            res.redirect('/announcements');
        }
    );
});

// Internal Links routes
app.get('/internal-links', (req, res) => {
    if (!req.session.user) {
        return res.redirect('/');
    }

    db.query('SELECT * FROM internal_links ORDER BY category, title', 
        (error, links) => {
            if (error) {
                console.error(error);
                return res.render('internal-links', { 
                    user: req.session.user, 
                    links: [],
                    error: 'Failed to load links'
                });
            }
            res.render('internal-links', { 
                user: req.session.user, 
                links: links,
                error: null
            });
        }
    );
});

app.get('/internal-links/new', (req, res) => {
    if (!req.session.user) {
        return res.redirect('/');
    }
    res.render('internal-links/create', { user: req.session.user });
});

app.post('/internal-links', (req, res) => {
    if (!req.session.user) {
        return res.redirect('/');
    }

    const { title, url, description, category } = req.body;
    db.query('INSERT INTO internal_links (title, url, description, category) VALUES (?, ?, ?, ?)',
        [title, url, description, category],
        (error) => {
            if (error) {
                console.error(error);
                return res.redirect('/internal-links');
            }
            res.redirect('/internal-links');
        }
    );
});

// Start server
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
}); 