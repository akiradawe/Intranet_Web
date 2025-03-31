const mysql = require('mysql');

const db = mysql.createConnection({
    host: '127.0.0.1',
    user: 'intranet_user',
    password: 'tl((C@8r7Kp[Y_Yi',
    database: 'intranet_db'
});

// Connect to database
db.connect((err) => {
    if (err) {
        console.error('Error connecting to database:', err);
        return;
    }
    console.log('Connected to database');
});

module.exports = db; 