const express = require('express');
const router = express.Router();

// Middleware to check if user is authenticated
const isAuthenticated = (req, res, next) => {
    if (req.session.user) {
        next();
    } else {
        res.redirect('/login');
    }
};

// Apply authentication middleware
router.use(isAuthenticated);

// Show team page
router.get('/', (req, res) => {
    req.app.locals.db.query(
        'SELECT id, username, full_name, email, role, phone, mobile_phone, job_title, department, profile_picture, bio FROM users WHERE status = "active" ORDER BY department, full_name',
        (error, users) => {
            if (error) {
                console.error('Error fetching users:', error);
                return res.render('team/index', {
                    user: req.session.user,
                    users: [],
                    error: 'Failed to load team members'
                });
            }

            // Group users by department
            const departments = {
                'Administration': [],
                'Research and Development': [],
                'Medical': [],
                'Engineering': [],
                'Marketing': [],
                'Parttime and Intern': []
            };

            users.forEach(user => {
                if (departments[user.department]) {
                    departments[user.department].push(user);
                }
            });

            res.render('team/index', {
                user: req.session.user,
                departments: departments,
                error: null
            });
        }
    );
});

module.exports = router; 