<?php
require_once __DIR__ . '/../app/Helpers/autoload.php';

echo "Checking database structure...\n\n";

try {
    // Check current columns
    echo "Current columns in organization_courses:\n";
    echo str_repeat("-", 80) . "\n";
    
    $columns = DatabaseHelper::query("SHOW COLUMNS FROM organization_courses");
    
    foreach ($columns as $col) {
        printf("%-25s %-20s %-10s %-10s %s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key'],
            $col['Default'] ?? 'NULL'
        );
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
    
    // Check if published_at exists
    $hasColumn = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'published_at') {
            $hasColumn = true;
            break;
        }
    }
    
    if ($hasColumn) {
        echo "✓ Column 'published_at' EXISTS\n";
    } else {
        echo "✗ Column 'published_at' DOES NOT EXIST\n";
        echo "\nAdding column...\n";
        
        DatabaseHelper::query("ALTER TABLE organization_courses ADD COLUMN published_at DATE NULL AFTER sort_order");
        echo "✓ Column added successfully\n";
        
        DatabaseHelper::query("ALTER TABLE organization_courses ADD INDEX idx_published_at (published_at)");
        echo "✓ Index added successfully\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
