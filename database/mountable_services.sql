-- Create mountable_services table
CREATE TABLE IF NOT EXISTS mountable_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    windows_command VARCHAR(255) NOT NULL,
    mac_linux_command VARCHAR(255) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-network-wired',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert some sample mountable services
INSERT INTO mountable_services (name, description, windows_command, mac_linux_command) VALUES
('Shared Documents', 'Access shared documents and resources', '\\\\ircad-africa\\shared-docs', 'smb://ircad-africa/shared-docs'),
('Project Files', 'Access project-related files and documents', '\\\\ircad-africa\\projects', 'smb://ircad-africa/projects'),
('Staff Resources', 'Access staff-specific resources and documents', '\\\\ircad-africa\\staff', 'smb://ircad-africa/staff'); 