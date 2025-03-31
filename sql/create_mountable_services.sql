-- Create mountable_services table
CREATE TABLE IF NOT EXISTS mountable_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    windows_command VARCHAR(255) NOT NULL,
    mac_linux_command VARCHAR(255) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-network-wired',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data
INSERT INTO mountable_services (name, description, windows_command, mac_linux_command, icon) VALUES
('Shared Documents', 'Access shared documents and files', '\\\\server\\shared-docs', 'smb://server/shared-docs', 'fa-folder'),
('Project Files', 'Project documentation and resources', '\\\\server\\projects', 'smb://server/projects', 'fa-project-diagram'),
('Media Library', 'Access to media files and resources', '\\\\server\\media', 'smb://server/media', 'fa-photo-video'); 