-- Add department column to users table
ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER job_title; 