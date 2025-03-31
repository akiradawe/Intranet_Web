const express = require('express');
const router = express.Router();

// Middleware to check if user is admin
const isAdmin = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'admin') {
        next();
    } else {
        res.redirect('/');
    }
};

// Middleware to check if user is admin or editor
const isAdminOrEditor = (req, res, next) => {
    if (req.session.user && (req.session.user.role === 'admin' || req.session.user.role === 'editor')) {
        next();
    } else {
        res.redirect('/');
    }
};

// List all mountable services
router.get('/', (req, res) => {
    // First check if the table exists
    req.app.locals.db.query(`
        CREATE TABLE IF NOT EXISTS mountable_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            windows_command VARCHAR(255) NOT NULL,
            mac_linux_command VARCHAR(255) NOT NULL,
            icon VARCHAR(50) DEFAULT 'fa-network-wired',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    `, (error) => {
        if (error) {
            console.error('Error creating table:', error);
            return res.render('mountable-services/index', {
                services: [],
                user: req.session.user,
                path: '/mountable-services',
                error: 'Failed to initialize mountable services. Please try again later.'
            });
        }

        // Then fetch the services
        req.app.locals.db.query('SELECT * FROM mountable_services ORDER BY name', (error, services) => {
            if (error) {
                console.error('Error fetching mountable services:', error);
                return res.render('mountable-services/index', {
                    services: [],
                    user: req.session.user,
                    path: '/mountable-services',
                    error: 'Failed to load mountable services. Please try again later.'
                });
            }
            
            res.render('mountable-services/index', { 
                services: services || [],
                user: req.session.user,
                path: '/mountable-services'
            });
        });
    });
});

// Create new mountable service (admin only)
router.post('/create', isAdmin, (req, res) => {
    const { name, description, windows_command, mac_linux_command, icon } = req.body;
    req.app.locals.db.query(
        'INSERT INTO mountable_services (name, description, windows_command, mac_linux_command, icon) VALUES (?, ?, ?, ?, ?)',
        [name, description, windows_command, mac_linux_command, icon],
        (error) => {
            if (error) {
                console.error('Error creating mountable service:', error);
                return res.render('mountable-services/index', {
                    services: [],
                    user: req.session.user,
                    path: '/mountable-services',
                    error: 'Failed to create mountable service. Please try again.'
                });
            }
            res.redirect('/mountable-services');
        }
    );
});

// Delete mountable service (admin only)
router.post('/:id/delete', isAdmin, (req, res) => {
    req.app.locals.db.query('DELETE FROM mountable_services WHERE id = ?', [req.params.id], (error) => {
        if (error) {
            console.error('Error deleting mountable service:', error);
            return res.render('mountable-services/index', {
                services: [],
                user: req.session.user,
                path: '/mountable-services',
                error: 'Failed to delete mountable service. Please try again.'
            });
        }
        res.redirect('/mountable-services');
    });
});

// Show edit form for a service
router.get('/:id/edit', isAdminOrEditor, (req, res) => {
    const serviceId = req.params.id;
    
    req.app.locals.db.query(
        'SELECT * FROM mountable_services WHERE id = ?',
        [serviceId],
        (error, results) => {
            if (error) {
                console.error('Error fetching service:', error);
                return res.redirect('/mountable-services');
            }

            if (results.length === 0) {
                return res.redirect('/mountable-services');
            }

            res.render('mountable-services/edit', {
                user: req.session.user,
                service: results[0],
                path: '/mountable-services',
                error: null
            });
        }
    );
});

// Handle service update
router.post('/:id/edit', isAdminOrEditor, (req, res) => {
    const serviceId = req.params.id;
    const { name, description, windows_command, mac_linux_command, icon } = req.body;

    if (!name || !description || !windows_command || !mac_linux_command || !icon) {
        return res.render('mountable-services/edit', {
            user: req.session.user,
            service: req.body,
            path: '/mountable-services',
            error: 'All fields are required'
        });
    }

    req.app.locals.db.query(
        'UPDATE mountable_services SET name = ?, description = ?, windows_command = ?, mac_linux_command = ?, icon = ? WHERE id = ?',
        [name, description, windows_command, mac_linux_command, icon, serviceId],
        (error) => {
            if (error) {
                console.error('Error updating service:', error);
                return res.render('mountable-services/edit', {
                    user: req.session.user,
                    service: req.body,
                    path: '/mountable-services',
                    error: 'Failed to update service'
                });
            }
            res.redirect('/mountable-services');
        }
    );
});

module.exports = router; 