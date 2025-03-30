const express = require('express');
const mysql = require('mysql');
const bodyParser = require('body-parser');

const app = express();

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));

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

// Simple HTML form
app.get('/', (req, res) => {
    res.send(`
        <h2>Test Login</h2>
        <form action="/login" method="POST">
            <input type="text" name="username" placeholder="Username"><br><br>
            <input type="password" name="password" placeholder="Password"><br><br>
            <button type="submit">Login</button>
        </form>
    `);
});

// Test login route
app.post('/login', (req, res) => {
    const { username, password } = req.body;
    
    console.log('Login attempt with:', { username, password });

    // First, let's see what users are in the database
    db.query('SELECT * FROM users', (err, allUsers) => {
        if (err) {
            console.error('Error fetching users:', err);
            return res.send('Database error when fetching users');
        }
        
        console.log('All users in database:', allUsers);

        // Now try to login
        const query = 'SELECT * FROM users WHERE username = ? AND password = ?';
        db.query(query, [username, password], (err, results) => {
            if (err) {
                console.error('Login query error:', err);
                return res.send('Login query error');
            }
            
            console.log('Login query results:', results);
            
            if (results.length > 0) {
                res.send('Login successful! User found: ' + JSON.stringify(results[0]));
            } else {
                res.send('Login failed: Invalid credentials');
            }
        });
    });
});

// Start server
app.listen(3000, () => {
    console.log('Test server running on port 3000');
}); 