-- Direct SQL to update tile_size enum to include 'mini'
-- Run this in your MySQL database if migration doesn't work

USE bonamiapp;

ALTER TABLE staff_notes 
MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') 
DEFAULT 'medium';

-- Verify the change
DESCRIBE staff_notes;