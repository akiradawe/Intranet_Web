const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt');
const moment = require('moment');
const db = require('../config/database');

// Middleware to check if user is admin
const isAdmin = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'admin') {
        next();
    } else {
        res.redirect('/dashboard');
    }
};

// Apply admin middleware to all routes
router.use(isAdmin);

// List all users
router.get('/', (req, res) => {
    req.app.locals.db.query('SELECT * FROM users ORDER BY username', (error, users) => {
        if (error) {
            console.error(error);
            return res.render('users/index', { 
                user: req.session.user, 
                users: [],
                error: 'Failed to load users'
            });
        }
        res.render('users/index', { 
            user: req.session.user, 
            users: users,
            error: null
        });
    });
});

// Show create user form
router.get('/new', (req, res) => {
    res.render('users/create', { 
        user: req.session.user,
        error: null
    });
});

// Create new user
router.post('/', async (req, res) => {
    const { 
        username, 
        password, 
        role, 
        full_name,
        email,
        department,
        job_title,
        phone,
        mobile_phone,
        bio
    } = req.body;

    if (!username || !password || !role || !full_name || !email || !department || !job_title) {
        return res.render('users/create', {
            user: req.session.user,
            error: 'Username, password, role, full name, email, department, and job title are required'
        });
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        return res.render('users/create', {
            user: req.session.user,
            error: 'Please enter a valid email address'
        });
    }

    try {
        // Hash the password
        const saltRounds = 10;
        const hashedPassword = await bcrypt.hash(password, saltRounds);

        db.query(
            'INSERT INTO users (username, password, role, full_name, email, department, job_title, phone, mobile_phone, bio, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "active")',
            [username, hashedPassword, role, full_name, email, department, job_title, phone || null, mobile_phone || null, bio || null],
            (error) => {
                if (error) {
                    console.error(error);
                    return res.render('users/create', {
                        user: req.session.user,
                        error: 'Failed to create user'
                    });
                }
                res.redirect('/users');
            }
        );
    } catch (err) {
        console.error('Password hashing error:', err);
        res.render('users/create', {
            user: req.session.user,
            error: 'Failed to create user'
        });
    }
});

// Edit user form
router.get('/:id/edit', (req, res) => {
    db.query('SELECT * FROM users WHERE id = ?', [req.params.id], (error, results) => {
        if (error || results.length === 0) {
            return res.redirect('/users');
        }
        res.render('users/edit', { 
            user: req.session.user,
            editUser: results[0],
            error: null
        });
    });
});

// Update user
router.post('/:id/edit', async (req, res) => {
    const { username, password, role } = req.body;
    const userId = req.params.id;

    if (!username || !role) {
        return res.render('users/edit', {
            user: req.session.user,
            editUser: { id: userId, username, role },
            error: 'Username and role are required'
        });
    }

    try {
        if (password) {
            // If password is provided, hash it and update all fields
            const hashedPassword = await bcrypt.hash(password, 10);
            db.query(
                'UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?',
                [username, hashedPassword, role, userId],
                (error) => {
                    if (error) {
                        console.error(error);
                        return res.render('users/edit', {
                            user: req.session.user,
                            editUser: { id: userId, username, role },
                            error: 'Failed to update user'
                        });
                    }
                    res.redirect('/users');
                }
            );
        } else {
            // If no password provided, update only username and role
            db.query(
                'UPDATE users SET username = ?, role = ? WHERE id = ?',
                [username, role, userId],
                (error) => {
                    if (error) {
                        console.error(error);
                        return res.render('users/edit', {
                            user: req.session.user,
                            editUser: { id: userId, username, role },
                            error: 'Failed to update user'
                        });
                    }
                    res.redirect('/users');
                }
            );
        }
    } catch (err) {
        console.error('Update error:', err);
        res.render('users/edit', {
            user: req.session.user,
            editUser: { id: userId, username, role },
            error: 'Failed to update user'
        });
    }
});

// Delete user
router.post('/:id/delete', (req, res) => {
    const userId = req.params.id;

    // Prevent deleting the last admin
    req.app.locals.db.query('SELECT COUNT(*) as adminCount FROM users WHERE role = "admin"', 
        (error, results) => {
            if (error) {
                console.error(error);
                return res.redirect('/users');
            }

            if (results[0].adminCount <= 1) {
                req.app.locals.db.query('SELECT role FROM users WHERE id = ?', [userId],
                    (error, user) => {
                        if (error || (user[0] && user[0].role === 'admin')) {
                            return res.redirect('/users');
                        }
                        deleteUser();
                    }
                );
            } else {
                deleteUser();
            }
        }
    );

    function deleteUser() {
        req.app.locals.db.query('DELETE FROM users WHERE id = ?', [userId],
            (error) => {
                if (error) {
                    console.error(error);
                }
                res.redirect('/users');
            }
        );
    }
});

module.exports = router; 