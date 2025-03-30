-- Add icon column to internal_links table
ALTER TABLE internal_links ADD COLUMN icon VARCHAR(50) DEFAULT 'fa-link'; 