const express = require('express');
const mysql = require('mysql');
const app = express();

// Enable parsing of POST data
app.use(express.urlencoded({ extended: true }));

// Database connection
const db = mysql.createConnection({
    host: '127.0.0.1',
    user: 'intranet_user',
    password: 'tl((C@8r7Kp[Y_Yi',
    database: 'intranet_db',
    port: 3306,
    insecureAuth: true
});

// Connect to database
db.connect((err) => {
    if (err) {
        console.error('Database connection failed:', err);
    } else {
        console.log('Connected to database!');
    }
});

// Simple login form
app.get('/', (req, res) => {
    res.send(`
        <form action="/test-login" method="POST">
            <input type="text" name="username" placeholder="Username"><br>
            <input type="password" name="password" placeholder="Password"><br>
            <button type="submit">Login</button>
        </form>
    `);
});

// Test login route
app.post('/test-login', (req, res) => {
    const { username, password } = req.body;
    
    console.log('Login attempt:', { username, password });
    
    const query = 'SELECT * FROM users';
    db.query(query, (err, results) => {
        if (err) {
            console.error('Query error:', err);
            return res.send('Database error: ' + err.message);
        }
        
        console.log('All users in database:', results);
        res.send('Check console for user data');
    });
});

// Start server
app.listen(3000, () => {
    console.log('Test server running on port 3000');
}); 