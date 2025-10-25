-- Migration: Add published_at column to organization_courses table
-- Run this SQL in phpMyAdmin or MySQL command line

-- Check if the column exists first, then add if not exists
ALTER TABLE organization_courses 
ADD COLUMN IF NOT EXISTS published_at DATE NULL AFTER sort_order;

-- Add index for better performance
ALTER TABLE organization_courses 
ADD INDEX IF NOT EXISTS idx_published_at (published_at);

-- Verify the change
SHOW COLUMNS FROM organization_courses LIKE 'published_at';
