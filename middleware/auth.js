const isAuthenticated = (req, res, next) => {
    if (req.session.user) {
        next();
    } else {
        res.redirect('/login');
    }
};

const isAdmin = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'admin') {
        next();
    } else {
        res.redirect('/dashboard');
    }
};

const isAdminOrEditor = (req, res, next) => {
    if (req.session.user && (req.session.user.role === 'admin' || req.session.user.role === 'editor')) {
        next();
    } else {
        res.redirect('/dashboard');
    }
};

module.exports = {
    isAuthenticated,
    isAdmin,
    isAdminOrEditor
}; 