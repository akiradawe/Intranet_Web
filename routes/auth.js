const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt');
const db = require('../config/database');

// Login route
router.post('/login', (req, res) => {
    const { username, password } = req.body;

    db.query(
        'SELECT * FROM users WHERE username = ?',
        [username],
        async (error, results) => {
            if (error) {
                console.error('Database error:', error);
                return res.render('login', { 
                    error: 'An error occurred during login',
                    path: '/login'
                });
            }

            if (results.length === 0) {
                return res.render('login', { 
                    error: 'Invalid username or password',
                    path: '/login'
                });
            }

            const user = results[0];
            
            try {
                console.log('Attempting to compare passwords:');
                console.log('Entered password:', password);
                console.log('Stored hash:', user.password);
                
                // Compare the entered password with the hashed password
                const passwordMatch = await bcrypt.compare(password, user.password);
                console.log('Password match result:', passwordMatch);

                if (passwordMatch) {
                    // Password matches, create session
                    req.session.user = {
                        id: user.id,
                        username: user.username,
                        role: user.role
                    };
                    res.redirect('/dashboard');
                } else {
                    res.render('login', { 
                        error: 'Invalid username or password',
                        path: '/login'
                    });
                }
            } catch (err) {
                console.error('Password comparison error:', err);
                res.render('login', { 
                    error: 'An error occurred during login',
                    path: '/login'
                });
            }
        }
    );
});

// Logout route
router.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/login');
});

module.exports = router; 