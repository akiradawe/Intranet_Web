const express = require('express');
const router = express.Router();
const { isAuthenticated, isPublisher, isAdminOrEditor } = require('../middleware/auth');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const moment = require('moment');

// Configure multer for file uploads
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        const uploadDir = 'public/uploads';
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
        if (file.fieldname === 'image') {
            // Accept images only
            if (!file.originalname.match(/\.(jpg|jpeg|png|gif)$/)) {
                return cb(new Error('Only image files are allowed!'), false);
            }
        }
        cb(null, true);
    }
});

// List all announcements
router.get('/', (req, res) => {
    req.app.locals.db.query(`
        SELECT a.*, u.username,
        GROUP_CONCAT(
            JSON_OBJECT(
                'id', att.id,
                'filename', att.filename,
                'filepath', att.filepath,
                'filetype', att.filetype
            )
        ) as attachments
        FROM announcements a 
        LEFT JOIN users u ON a.user_id = u.id 
        LEFT JOIN attachments att ON a.id = att.announcement_id
        GROUP BY a.id
        ORDER BY a.created_at DESC
    `, (error, announcements) => {
        if (error) {
            console.error(error);
            return res.render('announcements/index', { 
                user: req.session.user, 
                announcements: [],
                error: 'Failed to load announcements',
                path: '/announcements'
            });
        }

        // Parse the attachments JSON string
        announcements = announcements.map(announcement => {
            if (announcement.attachments) {
                announcement.attachments = JSON.parse(`[${announcement.attachments}]`);
            } else {
                announcement.attachments = [];
            }
            return announcement;
        });

        res.render('announcements/index', { 
            user: req.session.user, 
            announcements: announcements,
            error: null,
            path: '/announcements'
        });
    });
});

// Show create announcement form
router.get('/new', isAdminOrEditor, (req, res) => {
    res.render('announcements/create', { 
        user: req.session.user,
        path: '/announcements',
        error: null 
    });
});

// Create new announcement
router.post('/', upload.fields([
    { name: 'image', maxCount: 1 },
    { name: 'attachments', maxCount: 5 }
]), (req, res) => {
    const { title, content } = req.body;
    const userId = req.session.user.id;

    if (!title || !content) {
        return res.render('announcements/create', {
            user: req.session.user,
            error: 'Title and content are required'
        });
    }

    const imagePath = req.files['image'] ? req.files['image'][0].filename : null;
    const attachments = req.files['attachments'] ? req.files['attachments'].map(file => file.filename) : [];

    req.app.locals.db.query(
        'INSERT INTO announcements (title, content, user_id, image_path) VALUES (?, ?, ?, ?)',
        [title, content, userId, imagePath],
        (error, result) => {
            if (error) {
                console.error(error);
                return res.render('announcements/create', {
                    user: req.session.user,
                    error: 'Failed to create announcement'
                });
            }

            const announcementId = result.insertId;

            // Save attachments
            if (attachments.length > 0) {
                const attachmentValues = attachments.map(filename => [announcementId, filename]);
                req.app.locals.db.query(
                    'INSERT INTO attachments (announcement_id, filename) VALUES ?',
                    [attachmentValues],
                    (error) => {
                        if (error) {
                            console.error('Failed to save attachments:', error);
                        }
                        res.redirect('/announcements');
                    }
                );
            } else {
                res.redirect('/announcements');
            }
        }
    );
});

// Add comment to announcement
router.post('/:id/comments', isAuthenticated, (req, res) => {
    const { content } = req.body;
    const announcementId = req.params.id;
    const userId = req.session.user.id;

    req.app.locals.db.query(
        'INSERT INTO comments (announcement_id, user_id, content) VALUES (?, ?, ?)',
        [announcementId, userId, content],
        (error) => {
            if (error) {
                console.error(error);
            }
            res.redirect(`/announcements/${announcementId}`);
        }
    );
});

// Delete announcement
router.post('/delete/:id', isAdminOrEditor, (req, res) => {
    req.app.locals.db.query(
        'DELETE FROM announcements WHERE id = ?',
        [req.params.id],
        (error) => {
            if (error) {
                console.error(error);
            }
            res.redirect('/announcements');
        }
    );
});

// Protected routes (admin and editor only)
router.get('/:id/edit', isAdminOrEditor, (req, res) => {
    // ... existing code ...
});

router.post('/:id/edit', isAdminOrEditor, (req, res) => {
    // ... existing code ...
});

router.post('/:id/delete', isAdminOrEditor, (req, res) => {
    // ... existing code ...
});

module.exports = router;
