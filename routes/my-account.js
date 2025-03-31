const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');

// Configure multer for profile picture upload
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        const uploadDir = 'public/uploads/profile-pictures';
        if (!fs.existsSync(uploadDir)) {
            fs.mkdirSync(uploadDir, { recursive: true });
        }
        cb(null, uploadDir);
    },
    filename: function (req, file, cb) {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + path.extname(file.originalname));
    }
});

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 5 * 1024 * 1024 // 5MB limit
    },
    fileFilter: function (req, file, cb) {
        const filetypes = /jpeg|jpg|png|gif/;
        const mimetype = filetypes.test(file.mimetype);
        const extname = filetypes.test(path.extname(file.originalname).toLowerCase());
        
        if (mimetype && extname) {
            return cb(null, true);
        }
        cb(new Error('Only image files are allowed!'));
    }
});

// Middleware to check if user is logged in
const isAuthenticated = (req, res, next) => {
    if (req.session.user) {
        next();
    } else {
        res.redirect('/login');
    }
};

// Show My Account page
router.get('/', isAuthenticated, (req, res) => {
    const userId = req.session.user.id;
    
    // Fetch complete user data
    req.app.locals.db.query(
        'SELECT id, username, full_name, email, role, phone, mobile_phone, job_title, bio, profile_picture FROM users WHERE id = ?',
        [userId],
        (err, results) => {
            if (err) {
                console.error('Error fetching user data:', err);
                return res.status(500).send('Error fetching user data');
            }
            
            if (results.length === 0) {
                return res.status(404).send('User not found');
            }
            
            // Update session with complete user data
            req.session.user = {
                ...req.session.user,
                ...results[0]
            };
            
            res.render('my-account/index', { user: req.session.user });
        }
    );
});

// Show edit profile form
router.get('/edit', isAuthenticated, (req, res) => {
    res.render('my-account/edit', {
        user: req.session.user,
        path: '/my-account',
        error: null
    });
});

// Handle profile update
router.post('/edit', isAuthenticated, (req, res) => {
    const { 
        name, 
        full_name,
        email, 
        phone, 
        mobile_phone, 
        job_title,
        department 
    } = req.body;

    // Validate required fields
    if (!name || !email) {
        return res.render('my-account/edit', {
            user: req.session.user,
            path: '/my-account',
            error: 'Name and email are required'
        });
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        return res.render('my-account/edit', {
            user: req.session.user,
            path: '/my-account',
            error: 'Please enter a valid email address'
        });
    }

    // Check if email is already taken by another user
    req.app.locals.db.query(
        'SELECT id FROM users WHERE email = ? AND id != ?',
        [email, req.session.user.id],
        (error, results) => {
            if (error) {
                console.error('Error checking email:', error);
                return res.render('my-account/edit', {
                    user: req.session.user,
                    path: '/my-account',
                    error: 'An error occurred while updating your profile'
                });
            }

            if (results.length > 0) {
                return res.render('my-account/edit', {
                    user: req.session.user,
                    path: '/my-account',
                    error: 'This email address is already in use'
                });
            }

            // Update user profile
            const updateQuery = `
                UPDATE users 
                SET username = ?, 
                    full_name = ?,
                    email = ?, 
                    phone = ?, 
                    mobile_phone = ?, 
                    job_title = ?,
                    department = ?
                WHERE id = ?
            `;
            
            req.app.locals.db.query(updateQuery, [
                name,
                full_name,
                email,
                phone,
                mobile_phone,
                job_title,
                department,
                req.session.user.id
            ], (err, result) => {
                if (err) {
                    console.error('Error updating profile:', err);
                    return res.render('my-account/edit', {
                        user: req.session.user,
                        path: '/my-account',
                        error: 'An error occurred while updating your profile'
                    });
                }

                // Update session user data
                req.session.user.username = name;
                req.session.user.full_name = full_name;
                req.session.user.email = email;
                req.session.user.phone = phone;
                req.session.user.mobile_phone = mobile_phone;
                req.session.user.job_title = job_title;
                req.session.user.department = department;

                res.redirect('/my-account');
            });
        }
    );
});

// Handle profile picture upload
router.post('/upload-picture', isAuthenticated, upload.single('profile_picture'), (req, res) => {
    if (!req.file) {
        return res.redirect('/my-account');
    }

    // Delete old profile picture if exists
    if (req.session.user.profile_picture) {
        const oldPicturePath = path.join('public/uploads/profile-pictures', req.session.user.profile_picture);
        if (fs.existsSync(oldPicturePath)) {
            fs.unlinkSync(oldPicturePath);
        }
    }

    // Update user's profile picture in database
    req.app.locals.db.query(
        'UPDATE users SET profile_picture = ? WHERE id = ?',
        [req.file.filename, req.session.user.id],
        (error) => {
            if (error) {
                console.error('Error updating profile picture:', error);
                return res.redirect('/my-account');
            }

            // Update session user data
            req.session.user.profile_picture = req.file.filename;
            res.redirect('/my-account');
        }
    );
});

// Handle profile picture deletion
router.post('/delete-picture', isAuthenticated, (req, res) => {
    if (!req.session.user.profile_picture) {
        return res.redirect('/my-account');
    }

    const picturePath = path.join('public/uploads/profile-pictures', req.session.user.profile_picture);
    
    // Delete file from filesystem
    if (fs.existsSync(picturePath)) {
        fs.unlinkSync(picturePath);
    }

    // Update database
    req.app.locals.db.query(
        'UPDATE users SET profile_picture = NULL WHERE id = ?',
        [req.session.user.id],
        (error) => {
            if (error) {
                console.error('Error deleting profile picture:', error);
                return res.redirect('/my-account');
            }

            // Update session user data
            req.session.user.profile_picture = null;
            res.redirect('/my-account');
        }
    );
});

// Update bio
router.post('/edit-bio', isAuthenticated, async (req, res) => {
    try {
        const { bio } = req.body;
        
        // Update user's bio
        await req.app.locals.db.query('UPDATE users SET bio = ? WHERE id = ?', [bio, req.session.user.id]);
        
        // Update session data
        req.session.user.bio = bio;
        
        res.redirect('/my-account');
    } catch (error) {
        console.error('Error updating bio:', error);
        res.redirect('/my-account');
    }
});

module.exports = router; 