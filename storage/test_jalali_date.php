<?php
/**
 * Test Jalali Date Conversion
 */

require_once __DIR__ . '/../app/Helpers/JalaliHelper.php';

echo "=== تست تبدیل تاریخ شمسی ===\n\n";

// Get today's date
$today = date('Y-m-d'); // 2025-10-22
list($year, $month, $day) = explode('-', $today);

echo "📅 تاریخ میلادی امروز: $today\n";
echo "   - سال: $year\n";
echo "   - ماه: $month\n";
echo "   - روز: $day\n\n";

// Convert to Jalali
$jalali = \JalaliHelper::toJalali($today);
echo "📅 تاریخ شمسی امروز: $jalali\n";

// Expected: 1404/08/01 (1 Aban 1404)
// October 22, 2025 = 1404/08/01

// Let's verify manually
$expectedJalali = '1404/08/01';
if ($jalali === $expectedJalali) {
    echo "   ✅ تبدیل صحیح است!\n";
} else {
    echo "   ⚠️ تبدیل نادرست: انتظار می‌رفت $expectedJalali\n";
}

echo "\n=== تست تبدیل معکوس ===\n\n";

// Convert back
$gregorian = \JalaliHelper::toGregorian($jalali);
echo "تبدیل $jalali به میلادی: $gregorian\n";

if ($gregorian === $today) {
    echo "✅ تبدیل دوطرفه صحیح است!\n";
} else {
    echo "❌ تبدیل معکوس نادرست: انتظار می‌رفت $today\n";
}

echo "\n=== نام ماه‌های شمسی ===\n\n";

list($jy, $jm, $jd) = explode('/', $jalali);
$monthName = \JalaliHelper::getMonthName((int)$jm);
echo "ماه $jm: $monthName\n";
echo "امروز: $jd $monthName $jy\n";
