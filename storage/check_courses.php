<?php
/**
 * Check courses in database
 */

// Load DatabaseHelper without namespace
$dbHelperPath = __DIR__ . '/../app/Helpers/DatabaseHelper.php';
if (!file_exists($dbHelperPath)) {
    die("DatabaseHelper not found at: $dbHelperPath\n");
}
require_once $dbHelperPath;

echo "=== بررسی دوره‌ها در دیتابیس ===\n\n";

try {
    $db = \DatabaseHelper::getConnection();
    
    // Check if table exists
    echo "1️⃣ بررسی وجود جدول organization_courses...\n";
    $stmt = $db->query("SHOW TABLES LIKE 'organization_courses'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "   ❌ جدول organization_courses وجود ندارد!\n";
        echo "   💡 باید وارد صفحه دوره‌ها شوید تا جدول ایجاد شود.\n";
        exit(0);
    }
    
    echo "   ✅ جدول وجود دارد\n\n";
    
    // Count all courses
    echo "2️⃣ شمارش کل دوره‌ها...\n";
    $stmt = $db->query("SELECT COUNT(*) as total FROM organization_courses");
    $result = $stmt->fetch();
    $totalCourses = $result['total'];
    
    echo "   📊 تعداد کل دوره‌ها: $totalCourses\n\n";
    
    if ($totalCourses == 0) {
        echo "   ⚠️ هیچ دوره‌ای در دیتابیس وجود ندارد!\n";
        echo "   💡 برای حل مشکل، یک دوره نمونه ایجاد کنید:\n";
        echo "      http://localhost:8888/ptp/organizations/courses/create\n\n";
        exit(0);
    }
    
    // Show all courses
    echo "3️⃣ لیست دوره‌ها:\n";
    $stmt = $db->query("SELECT id, organization_id, title, status, created_at FROM organization_courses ORDER BY created_at DESC");
    $courses = $stmt->fetchAll();
    
    foreach ($courses as $course) {
        echo "\n   📚 دوره #{$course['id']}\n";
        echo "      - عنوان: {$course['title']}\n";
        echo "      - سازمان: {$course['organization_id']}\n";
        echo "      - وضعیت: {$course['status']}\n";
        echo "      - تاریخ ایجاد: {$course['created_at']}\n";
    }
    
    echo "\n\n4️⃣ بررسی session...\n";
    echo "   💡 برای مشاهده دوره‌ها باید:\n";
    echo "      - وارد سیستم شده باشید\n";
    echo "      - organization_id شما با organization_id دوره‌ها یکسان باشد\n";
    echo "      - دسترسی courses_view یا courses_manage داشته باشید\n\n";
    
    echo "=== بررسی کامل شد ===\n";
    
} catch (PDOException $e) {
    echo "❌ خطا در اتصال به دیتابیس: " . $e->getMessage() . "\n";
    echo "کد خطا: " . $e->getCode() . "\n";
    exit(1);
}
