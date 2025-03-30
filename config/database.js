const mysql = require('mysql');

const db = mysql.createConnection({
    host: '127.0.0.1',
    user: 'intranet_user',
    password: 'tl((C@8r7Kp[Y_Yi',
    database: 'intranet_db',
    port: 3306,
    insecureAuth: true
});

// Test connection
db.connect(function(err) {
    if (err) {
        console.error('Error connecting: ' + err.stack);
        return;
    }
    console.log('Connected as id ' + db.threadId);
});

module.exports = db; 