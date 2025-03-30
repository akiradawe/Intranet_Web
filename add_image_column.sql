-- Add image_path column to announcements table
ALTER TABLE announcements ADD COLUMN image_path VARCHAR(255) DEFAULT NULL; 