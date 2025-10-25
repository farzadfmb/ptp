<?php
/**
 * Migration: Add published_at column
 * Access this file via browser: http://localhost/ptp/public/migrate.php
 */

require_once __DIR__ . '/../app/Helpers/autoload.php';

// Security: Only run in development
if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
    die('Migration can only be run on localhost');
}

echo '<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #22c55e; padding: 10px; background: #f0fdf4; border-radius: 5px; margin: 10px 0; }
        .error { color: #ef4444; padding: 10px; background: #fef2f2; border-radius: 5px; margin: 10px 0; }
        .info { color: #3b82f6; padding: 10px; background: #eff6ff; border-radius: 5px; margin: 10px 0; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #1e293b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Migration</h1>
        <p>اضافه کردن ستون <code>published_at</code> به جدول <code>organization_courses</code></p>
';

try {
    echo '<div class="info">در حال بررسی جدول...</div>';
    
    // Check if column already exists
    $columns = DatabaseHelper::query("SHOW COLUMNS FROM organization_courses LIKE 'published_at'");
    
    if (!empty($columns)) {
        echo '<div class="success">✓ ستون published_at قبلاً وجود دارد</div>';
    } else {
        echo '<div class="info">در حال اضافه کردن ستون published_at...</div>';
        
        // Add column
        DatabaseHelper::query('ALTER TABLE organization_courses ADD COLUMN published_at DATE NULL AFTER sort_order');
        echo '<div class="success">✓ ستون با موفقیت اضافه شد</div>';
        
        echo '<div class="info">در حال اضافه کردن index...</div>';
        
        // Add index
        DatabaseHelper::query('ALTER TABLE organization_courses ADD INDEX idx_published_at (published_at)');
        echo '<div class="success">✓ Index با موفقیت اضافه شد</div>';
    }
    
    // Show final structure
    echo '<h2>ساختار نهایی جدول:</h2>';
    $structure = DatabaseHelper::query('DESCRIBE organization_courses');
    echo '<pre>';
    foreach ($structure as $column) {
        echo str_pad($column['Field'], 20) . ' | ' . 
             str_pad($column['Type'], 20) . ' | ' . 
             str_pad($column['Null'], 5) . ' | ' . 
             str_pad($column['Key'], 5) . ' | ' . 
             ($column['Default'] ?? 'NULL') . "\n";
    }
    echo '</pre>';
    
    echo '<div class="success"><strong>✓ Migration کامل شد!</strong></div>';
    echo '<p><a href="/ptp/organizations/courses">بازگشت به صفحه دوره‌ها</a></p>';
    
} catch (Exception $e) {
    echo '<div class="error">خطا: ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<h3>دستور SQL:</h3>';
    echo '<pre>ALTER TABLE organization_courses ADD COLUMN published_at DATE NULL AFTER sort_order;
ALTER TABLE organization_courses ADD INDEX idx_published_at (published_at);</pre>';
    echo '<p>این دستور را در phpMyAdmin اجرا کنید.</p>';
}

echo '
    </div>
</body>
</html>';
