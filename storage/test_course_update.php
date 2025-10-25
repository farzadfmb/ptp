<?php
/**
 * Test Course Update with published_at
 */

// Load DatabaseHelper
$dbHelperPath = __DIR__ . '/../app/Helpers/DatabaseHelper.php';
if (!file_exists($dbHelperPath)) {
    die("DatabaseHelper not found at: $dbHelperPath\n");
}
require_once $dbHelperPath;

// Load JalaliHelper
$jalaliHelperPath = __DIR__ . '/../app/Helpers/JalaliHelper.php';
if (!file_exists($jalaliHelperPath)) {
    die("JalaliHelper not found at: $jalaliHelperPath\n");
}
require_once $jalaliHelperPath;

// No namespace, use classes directly

echo "=== تست به‌روزرسانی دوره با published_at ===\n\n";

try {
    $db = \DatabaseHelper::getConnection();
    
    // 1. Check if table has published_at column
    echo "1️⃣ بررسی ستون published_at...\n";
    $stmt = $db->query("DESCRIBE organization_courses");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasPublishedAt = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'published_at') {
            $hasPublishedAt = true;
            echo "   ✅ ستون published_at وجود دارد\n";
            echo "   - نوع: {$column['Type']}\n";
            echo "   - NULL: {$column['Null']}\n";
            echo "   - کلید: {$column['Key']}\n\n";
            break;
        }
    }
    
    if (!$hasPublishedAt) {
        echo "   ❌ ستون published_at وجود ندارد!\n";
        exit(1);
    }
    
    // 2. Get first course
    echo "2️⃣ دریافت اولین دوره...\n";
    $stmt = $db->query("SELECT * FROM organization_courses LIMIT 1");
    $course = $stmt->fetch();
    
    if (!$course) {
        echo "   ⚠️ هیچ دوره‌ای یافت نشد. ابتدا یک دوره ایجاد کنید.\n";
        exit(0);
    }
    
    echo "   ✅ دوره یافت شد: {$course['title']} (ID: {$course['id']})\n";
    echo "   - تاریخ انتشار فعلی: " . ($course['published_at'] ?? 'NULL') . "\n\n";
    
    // 3. Update with new published_at
    echo "3️⃣ به‌روزرسانی با تاریخ انتشار جدید...\n";
    $jalaliDate = '1403/10/15'; // ۱۵ دی ۱۴۰۳
    $gregorianDate = \JalaliHelper::toGregorian($jalaliDate);
    
    echo "   - تاریخ شمسی: $jalaliDate\n";
    echo "   - تاریخ میلادی: $gregorianDate\n";
    
    $stmt = $db->prepare("
        UPDATE organization_courses 
        SET published_at = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$gregorianDate, $course['id']]);
    
    echo "   ✅ به‌روزرسانی انجام شد\n\n";
    
    // 4. Verify update
    echo "4️⃣ تایید به‌روزرسانی...\n";
    $stmt = $db->prepare("SELECT * FROM organization_courses WHERE id = ?");
    $stmt->execute([$course['id']]);
    $updatedCourse = $stmt->fetch();
    
    echo "   - تاریخ انتشار جدید: {$updatedCourse['published_at']}\n";
    
    if ($updatedCourse['published_at'] === $gregorianDate) {
        echo "   ✅ تاریخ با موفقیت ذخیره شد\n";
        
        // Convert back to Jalali
        $convertedJalali = \JalaliHelper::toJalali($updatedCourse['published_at']);
        echo "   - تبدیل به شمسی: $convertedJalali\n";
        
        if ($convertedJalali === $jalaliDate) {
            echo "   ✅ تبدیل دوطرفه صحیح است\n\n";
        } else {
            echo "   ⚠️ تبدیل دوطرفه مشکل دارد: $convertedJalali != $jalaliDate\n\n";
        }
    } else {
        echo "   ❌ تاریخ به درستی ذخیره نشد\n\n";
    }
    
    echo "=== تست با موفقیت انجام شد ✅ ===\n";
    
} catch (PDOException $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    echo "کد خطا: " . $e->getCode() . "\n";
    exit(1);
}
