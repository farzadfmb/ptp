<?php
/**
 * Create sample course for testing
 */

require_once __DIR__ . '/../app/Helpers/DatabaseHelper.php';

use DatabaseHelper;

echo "=== ایجاد دوره نمونه ===\n\n";

try {
    $db = \DatabaseHelper::getConnection();
    
    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'organization_courses'");
    if (!$stmt->fetch()) {
        echo "❌ جدول organization_courses وجود ندارد!\n";
        echo "💡 ابتدا وارد صفحه دوره‌ها شوید تا جدول ایجاد شود.\n";
        exit(1);
    }
    
    echo "1️⃣ بررسی دوره‌های موجود...\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM organization_courses WHERE organization_id = 1");
    $result = $stmt->fetch();
    $existingCount = $result['count'];
    
    echo "   تعداد دوره‌های موجود برای سازمان 1: $existingCount\n\n";
    
    if ($existingCount >= 3) {
        echo "✅ دوره‌های کافی وجود دارد!\n";
        echo "برای مشاهده: http://localhost:8888/ptp/organizations/courses\n";
        exit(0);
    }
    
    echo "2️⃣ ایجاد دوره‌های نمونه...\n\n";
    
    $sampleCourses = [
        [
            'title' => 'دوره مهارت‌های ارتباطی',
            'description' => 'یادگیری تکنیک‌های موثر در ارتباط با دیگران',
            'category' => 'مهارت‌های نرم',
            'instructor_name' => 'دکتر احمد محمدی',
            'price' => 500000,
            'duration_hours' => 20,
            'status' => 'published'
        ],
        [
            'title' => 'مدیریت زمان و بهره‌وری',
            'description' => 'افزایش بهره‌وری و مدیریت بهتر زمان',
            'category' => 'توسعه فردی',
            'instructor_name' => 'مریم کریمی',
            'price' => 350000,
            'duration_hours' => 15,
            'status' => 'published'
        ],
        [
            'title' => 'رهبری و مدیریت تیم',
            'description' => 'آموزش اصول رهبری موثر و مدیریت تیم',
            'category' => 'مدیریت',
            'instructor_name' => 'حسین رضایی',
            'price' => 750000,
            'duration_hours' => 25,
            'status' => 'published'
        ]
    ];
    
    foreach ($sampleCourses as $index => $course) {
        try {
            $stmt = $db->prepare("
                INSERT INTO organization_courses 
                (organization_id, title, description, category, instructor_name, price, duration_hours, status, sort_order, created_at, updated_at) 
                VALUES 
                (:organization_id, :title, :description, :category, :instructor_name, :price, :duration_hours, :status, :sort_order, NOW(), NOW())
            ");
            
            $stmt->execute([
                'organization_id' => 1,
                'title' => $course['title'],
                'description' => $course['description'],
                'category' => $course['category'],
                'instructor_name' => $course['instructor_name'],
                'price' => $course['price'],
                'duration_hours' => $course['duration_hours'],
                'status' => $course['status'],
                'sort_order' => ($index + 1) * 10
            ]);
            
            echo "   ✅ دوره ایجاد شد: {$course['title']}\n";
            
        } catch (PDOException $e) {
            echo "   ❌ خطا در ایجاد دوره: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n3️⃣ بررسی نهایی...\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM organization_courses WHERE organization_id = 1");
    $result = $stmt->fetch();
    $finalCount = $result['count'];
    
    echo "   📊 تعداد کل دوره‌ها: $finalCount\n\n";
    
    echo "✅ عملیات با موفقیت انجام شد!\n";
    echo "🔗 مشاهده دوره‌ها: http://localhost:8888/ptp/organizations/courses\n";
    
} catch (PDOException $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    exit(1);
}
