const db = require('./config/database');

const createTables = () => {
    const queries = [
        // Attachments table
        `CREATE TABLE IF NOT EXISTS attachments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            announcement_id INT,
            filename VARCHAR(255),
            filepath VARCHAR(255),
            filetype VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE
        )`,

        // Comments table
        `CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            announcement_id INT,
            user_id INT,
            content TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )`,

        // Favorites table
        `CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            link_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (link_id) REFERENCES internal_links(id),
            UNIQUE KEY unique_favorite (user_id, link_id)
        )`,

        // Activity logs table
        `CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100),
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )`,

        // Add new columns to users table
        `ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS email VARCHAR(100),
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN IF NOT EXISTS last_login TIMESTAMP,
        ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active',
        ADD COLUMN IF NOT EXISTS theme VARCHAR(20) DEFAULT 'light'`
    ];

    queries.forEach(query => {
        db.query(query, (err) => {
            if (err) {
                console.error('Error executing query:', err);
            } else {
                console.log('Query executed successfully');
            }
        });
    });
};

createTables(); 