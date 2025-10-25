<?php
// Simulate data
$course = [
    'id' => 2,
    'title' => 'دوره تست'
];

$lessons = []; // Empty for testing
$totalLessons = 0;
$totalDuration = 0;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست دیباگ صفحه درس‌ها</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <style>
        body {
            font-family: Tahoma, Arial;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .test-info {
            background: #e0f2fe;
            border: 2px solid #0284c7;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-content h3 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .stat-content p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }
        
        .lessons-container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .lessons-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .lessons-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-size: 24px;
            color: #1e293b;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        
        .empty-state ion-icon {
            font-size: 80px;
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
        .debug-section {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="test-info">
    <h2>🔍 صفحه تست دیباگ</h2>
    <p><strong>هدف:</strong> بررسی اینکه آیا ساختار HTML به درستی نمایش داده می‌شود</p>
    <p><strong>داده‌های شبیه‌سازی شده:</strong></p>
    <ul>
        <li>عنوان دوره: <?= htmlspecialchars($course['title']); ?></li>
        <li>تعداد درس‌ها: <?= $totalLessons; ?></li>
        <li>مدت زمان کل: <?= $totalDuration; ?> دقیقه</li>
    </ul>
</div>

<!-- Page Header -->
<div class="page-header">
    <h1>
        <ion-icon name="school-outline"></ion-icon>
        <?= htmlspecialchars($course['title']); ?>
    </h1>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon">
            <ion-icon name="list-outline"></ion-icon>
        </div>
        <div class="stat-content">
            <h3><?= $totalLessons; ?></h3>
            <p>تعداد درس‌ها</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <ion-icon name="time-outline"></ion-icon>
        </div>
        <div class="stat-content">
            <h3><?= $totalDuration; ?></h3>
            <p>دقیقه محتوا</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <ion-icon name="people-outline"></ion-icon>
        </div>
        <div class="stat-content">
            <h3>0</h3>
            <p>دانشجویان</p>
        </div>
    </div>
</div>

<div class="debug-section">
    <h3>⚠️ بخش اصلی - لیست درس‌ها</h3>
    <p>اگر دکمه "افزودن درس جدید" را می‌بینید، یعنی ساختار HTML صحیح است</p>
</div>

<!-- Lessons List -->
<div class="lessons-container">
    <div class="lessons-header">
        <h2>
            <ion-icon name="list-outline"></ion-icon>
            لیست درس‌ها
        </h2>
        <button type="button" class="btn btn-primary" onclick="alert('دکمه کار می‌کند!')">
            <ion-icon name="add-circle-outline" style="font-size: 20px;"></ion-icon>
            افزودن درس جدید
        </button>
    </div>

    <?php if (empty($lessons)): ?>
        <div class="empty-state">
            <ion-icon name="folder-open-outline"></ion-icon>
            <h3>هنوز هیچ درسی اضافه نشده است</h3>
            <p>برای شروع، اولین درس دوره را اضافه کنید</p>
        </div>
    <?php endif; ?>
</div>

<div class="debug-section">
    <h3>✅ نتیجه آزمایش</h3>
    <p>اگر در این صفحه همه چیز را می‌بینید اما در صفحه اصلی نمی‌بینید، احتمالاً:</p>
    <ol>
        <li>مشکل از فایل‌های layout (header/sidebar/navbar) است</li>
        <li>CSS یا JavaScript دیگری در صفحه اصلی تداخل دارد</li>
        <li>متغیرهای PHP در صفحه اصلی مقدار ندارند</li>
    </ol>
</div>

<script>
    console.log('تست دیباگ بارگذاری شد');
    console.log('تعداد درس‌ها:', <?= $totalLessons; ?>);
    console.log('آیا لیست خالی است؟', <?= empty($lessons) ? 'true' : 'false'; ?>);
</script>

</body>
</html>
