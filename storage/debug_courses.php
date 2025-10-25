<?php
// Debug page for courses
session_start();

echo "<!DOCTYPE html>";
echo "<html lang='fa' dir='rtl'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Debug - دوره‌ها</title>";
echo "<style>
    body { font-family: Tahoma; padding: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
    h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
    pre { background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Debug اطلاعات دوره‌ها</h1>";

// 1. Session Info
echo "<div class='section'>";
echo "<h2>1️⃣ اطلاعات Session</h2>";
if (isset($_SESSION['organization_user'])) {
    echo "<p class='success'>✓ کاربر وارد سیستم شده است</p>";
    echo "<pre>";
    print_r($_SESSION['organization_user']);
    echo "</pre>";
} else {
    echo "<p class='error'>✗ کاربر وارد سیستم نشده است</p>";
    echo "<p>برای حل این مشکل وارد شوید: <a href='http://localhost:8888/ptp/organizations/login'>ورود</a></p>";
}
echo "</div>";

// 2. Organization Info
echo "<div class='section'>";
echo "<h2>2️⃣ اطلاعات سازمان</h2>";
if (isset($_SESSION['organization'])) {
    echo "<p class='success'>✓ سازمان شناسایی شده است</p>";
    echo "<pre>";
    print_r($_SESSION['organization']);
    echo "</pre>";
    $orgId = $_SESSION['organization']['id'] ?? 0;
    echo "<p><strong>Organization ID: </strong>" . $orgId . "</p>";
} else {
    echo "<p class='error'>✗ سازمان شناسایی نشده است</p>";
}
echo "</div>";

// 3. Database Connection
echo "<div class='section'>";
echo "<h2>3️⃣ اتصال به دیتابیس</h2>";
try {
    require_once __DIR__ . '/../app/Helpers/DatabaseHelper.php';
    $db = \DatabaseHelper::getConnection();
    echo "<p class='success'>✓ اتصال به دیتابیس موفق</p>";
    
    // Check courses
    if (isset($orgId) && $orgId > 0) {
        $stmt = $db->prepare("SELECT * FROM organization_courses WHERE organization_id = :org_id");
        $stmt->execute(['org_id' => $orgId]);
        $courses = $stmt->fetchAll();
        
        echo "<p><strong>تعداد دوره‌های سازمان شما: </strong>" . count($courses) . "</p>";
        
        if (count($courses) > 0) {
            echo "<p class='success'>✓ دوره‌ها یافت شدند</p>";
            echo "<pre>";
            print_r($courses);
            echo "</pre>";
        } else {
            echo "<p class='warning'>⚠ هیچ دوره‌ای برای سازمان شما یافت نشد</p>";
            
            // Check all courses
            $stmt = $db->query("SELECT id, organization_id, title FROM organization_courses");
            $allCourses = $stmt->fetchAll();
            
            echo "<p><strong>تمام دوره‌های موجود در دیتابیس:</strong></p>";
            echo "<pre>";
            print_r($allCourses);
            echo "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ خطا در اتصال: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 4. Permissions
echo "<div class='section'>";
echo "<h2>4️⃣ دسترسی‌ها</h2>";
if (isset($_SESSION['organization_user']['permissions'])) {
    echo "<p class='success'>✓ دسترسی‌های کاربر:</p>";
    echo "<pre>";
    print_r($_SESSION['organization_user']['permissions']);
    echo "</pre>";
    
    $permissions = $_SESSION['organization_user']['permissions'];
    if (in_array('courses_view', $permissions) || in_array('courses_manage', $permissions)) {
        echo "<p class='success'>✓ دسترسی مشاهده دوره‌ها وجود دارد</p>";
    } else {
        echo "<p class='error'>✗ دسترسی مشاهده دوره‌ها وجود ندارد</p>";
    }
} else {
    echo "<p class='warning'>⚠ اطلاعات دسترسی یافت نشد</p>";
}
echo "</div>";

// 5. Solution
echo "<div class='section'>";
echo "<h2>5️⃣ راه حل</h2>";
echo "<ul>";
echo "<li>اگر وارد نشده‌اید: <a href='http://localhost:8888/ptp/organizations/login'>وارد شوید</a></li>";
echo "<li>اگر دوره ندارید: <a href='http://localhost:8888/ptp/organizations/courses/create'>دوره جدید ایجاد کنید</a></li>";
echo "<li>صفحه دوره‌ها: <a href='http://localhost:8888/ptp/organizations/courses'>مشاهده دوره‌ها</a></li>";
echo "</ul>";
echo "</div>";

echo "</body>";
echo "</html>";
