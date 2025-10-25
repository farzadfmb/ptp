<?php
/**
 * Migration: Add published_at column to organization_courses table
 * Run this file once to add the published_at field to existing database
 */

require_once __DIR__ . '/../app/Helpers/autoload.php';

try {
    echo "Starting migration...\n";
    
    // Check if column already exists
    $result = DatabaseHelper::query("SHOW COLUMNS FROM organization_courses LIKE 'published_at'");
    
    if (empty($result)) {
        echo "Adding published_at column...\n";
        DatabaseHelper::query('ALTER TABLE organization_courses ADD COLUMN published_at DATE NULL AFTER sort_order');
        echo "✓ Column added successfully\n";
        
        echo "Adding index...\n";
        DatabaseHelper::query('ALTER TABLE organization_courses ADD INDEX idx_published_at (published_at)');
        echo "✓ Index added successfully\n";
    } else {
        echo "✓ Column already exists\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
