<?php
/**
 * Direct database migration without using DatabaseHelper
 */

echo "=== Database Migration Tool ===\n\n";

// Try different connection methods
$connections = [
    ['host' => 'localhost', 'port' => null],
    ['host' => 'localhost', 'port' => 8889],
    ['host' => '127.0.0.1', 'port' => null],
    ['host' => '127.0.0.1', 'port' => 8889],
    ['host' => 'localhost', 'port' => 3306],
];

$pdo = null;
$config = [
    'database' => 'ptp_db',
    'username' => 'root',
    'password' => 'root',
];

foreach ($connections as $conn) {
    try {
        $host = $conn['host'];
        $port = $conn['port'];
        
        if ($port) {
            $dsn = "mysql:host={$host};port={$port};dbname={$config['database']};charset=utf8mb4";
        } else {
            $dsn = "mysql:host={$host};dbname={$config['database']};charset=utf8mb4";
        }
        
        echo "Trying connection: {$dsn}... ";
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "✓ CONNECTED!\n\n";
        break;
        
    } catch (PDOException $e) {
        echo "✗ Failed\n";
    }
}

if (!$pdo) {
    die("\n❌ Could not connect to database with any configuration.\n");
}

try {
    // Check current structure
    echo "Checking table structure...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM organization_courses");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasPublishedAt = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'published_at') {
            $hasPublishedAt = true;
            break;
        }
    }
    
    if ($hasPublishedAt) {
        echo "✓ Column 'published_at' already exists\n";
    } else {
        echo "Adding 'published_at' column...\n";
        $pdo->exec("ALTER TABLE organization_courses ADD COLUMN published_at DATE NULL AFTER sort_order");
        echo "✓ Column added successfully\n";
        
        echo "Adding index...\n";
        $pdo->exec("ALTER TABLE organization_courses ADD INDEX idx_published_at (published_at)");
        echo "✓ Index added successfully\n";
    }
    
    echo "\nFinal table structure:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-25s %-20s %-10s %-10s %s\n", 'Field', 'Type', 'Null', 'Key', 'Default');
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM organization_courses");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        printf("%-25s %-20s %-10s %-10s %s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key'],
            $col['Default'] ?? 'NULL'
        );
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}
