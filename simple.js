const express = require('express');
const mysql = require('mysql');
const session = require('express-session');
const app = express();

// Basic middleware
app.use(express.urlencoded({ extended: true }));
app.use(session({
    secret: 'secret',
    resave: true,
    saveUninitialized: true
}));

// Database connection
const db = mysql.createConnection({
    host: '127.0.0.1',
    user: 'intranet_user',
    password: 'tl((C@8r7Kp[Y_Yi',
    database: 'intranet_db'
});

// Simple login page
app.get('/', (req, res) => {
    res.send(`
        <h2>IRCAD Login</h2>
        <form action="/auth" method="POST">
            <input name="username" type="text" placeholder="Username" required><br><br>
            <input name="password" type="password" placeholder="Password" required><br><br>
            <button type="submit">Login</button>
        </form>
    `);
});

// Login handler
app.post('/auth', (req, res) => {
    const username = req.body.username;
    const password = req.body.password;

    if (username && password) {
        db.query('SELECT * FROM users WHERE username = ? AND password = ?', 
            [username, password], 
            (error, results) => {
                if (error) throw error;

                if (results.length > 0) {
                    req.session.loggedin = true;
                    req.session.username = username;
                    res.redirect('/home');
                } else {
                    res.send('Incorrect Username and/or Password!');
                }
            }
        );
    }
});

// Home page
app.get('/home', (req, res) => {
    if (req.session.loggedin) {
        res.send(`
            <h1>Welcome back, ${req.session.username}!</h1>
            <a href="/logout">Logout</a>
        `);
    } else {
        res.redirect('/');
    }
});

// Logout
app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/');
});

app.listen(3000, () => {
    console.log('Server running on port 3000');
}); 