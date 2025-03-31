const express = require('express');
const router = express.Router();
const db = require('../config/database');

// Middleware to check if user is admin
const isAdmin = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'admin') {
        next();
    } else {
        res.redirect('/dashboard');
    }
};

// Middleware to check if user is authenticated
const isAuthenticated = (req, res, next) => {
    if (req.session.user) {
        next();
    } else {
        res.redirect('/login');
    }
};

// Apply authentication middleware to all routes
router.use(isAuthenticated);

// List all internal links - accessible to all authenticated users
router.get('/', (req, res) => {
    db.query('SELECT * FROM internal_links ORDER BY category, title', 
        (error, links) => {
            if (error) {
                console.error(error);
                return res.render('internal-links/index', { 
                    user: req.session.user, 
                    links: [],
                    error: 'Failed to load links'
                });
            }
            res.render('internal-links/index', { 
                user: req.session.user, 
                links: links,
                error: null
            });
        }
    );
});

// Apply admin middleware only to routes that modify data
router.use('/new', isAdmin);
router.use('/', isAdmin);
router.use('/:id/edit', isAdmin);
router.use('/:id', isAdmin);
router.use('/:id/delete', isAdmin);

// Show create form
router.get('/new', (req, res) => {
    res.render('internal-links/create', { 
        user: req.session.user,
        error: null
    });
});

// Create new link
router.post('/', (req, res) => {
    const { title, url, description, category, icon } = req.body;

    if (!title || !url || !category) {
        return res.render('internal-links/create', {
            user: req.session.user,
            error: 'Title, URL and category are required'
        });
    }

    // Save the full icon class
    const formattedIcon = icon || 'fas fa-link';

    db.query(
        'INSERT INTO internal_links (title, url, description, category, icon) VALUES (?, ?, ?, ?, ?)',
        [title, url, description, category, formattedIcon],
        (error) => {
            if (error) {
                console.error(error);
                return res.render('internal-links/create', {
                    user: req.session.user,
                    error: 'Failed to create link'
                });
            }
            res.redirect('/internal-links');
        }
    );
});

// Show edit form
router.get('/:id/edit', (req, res) => {
    db.query('SELECT * FROM internal_links WHERE id = ?', [req.params.id], 
        (error, results) => {
            if (error || results.length === 0) {
                return res.redirect('/internal-links');
            }
            res.render('internal-links/edit', {
                user: req.session.user,
                link: results[0],
                error: null
            });
        }
    );
});

// Update link
router.post('/:id', (req, res) => {
    const { title, url, description, category, icon } = req.body;
    
    if (!title || !url || !category) {
        return res.render('internal-links/edit', {
            user: req.session.user,
            link: { ...req.body, id: req.params.id },
            error: 'Title, URL and category are required'
        });
    }

    // Save the full icon class
    const formattedIcon = icon || 'fas fa-link';

    db.query(
        'UPDATE internal_links SET title = ?, url = ?, description = ?, category = ?, icon = ? WHERE id = ?',
        [title, url, description, category, formattedIcon, req.params.id],
        (error) => {
            if (error) {
                console.error(error);
                return res.render('internal-links/edit', {
                    user: req.session.user,
                    link: { ...req.body, id: req.params.id },
                    error: 'Failed to update link'
                });
            }
            res.redirect('/internal-links');
        }
    );
});

// Delete link
router.post('/:id/delete', (req, res) => {
    db.query('DELETE FROM internal_links WHERE id = ?', [req.params.id], (error) => {
        if (error) {
            console.error(error);
        }
        res.redirect('/internal-links');
    });
});

module.exports = router;
