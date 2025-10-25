<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!class_exists('UtilityHelper')) {
    require_once dirname(__DIR__, 3) . '/Helpers/autoload.php';
}

$course = $course ?? [];
$lessons = $lessons ?? [];
$user = $user ?? [];

$pageTitle = 'مدیریت درس‌های دوره: ' . htmlspecialchars($course['title'] ?? '');

$contentTypeLabels = [
    'video' => 'ویدیو',
    'pdf' => 'فایل PDF',
    'ppt' => 'فایل ارائه',
    'link' => 'لینک خارجی',
    'text' => 'محتوای متنی'
];

$contentTypeIcons = [
    'video' => 'videocam-outline',
    'pdf' => 'document-text-outline',
    'ppt' => 'easel-outline',
    'link' => 'link-outline',
    'text' => 'document-text-outline'
];

$totalLessons = count($lessons);
$publishedLessons = 0;
$draftLessons = 0;
$scheduledLessons = 0;
$totalDuration = 0;
$nextAvailableLesson = null;

$now = new DateTime();

foreach ($lessons as $lesson) {
    $isPublished = (int)($lesson['is_published'] ?? 1) === 1;
    if ($isPublished) {
        $publishedLessons++;
    } else {
        $draftLessons++;
    }

    if (!empty($lesson['available_at'])) {
        try {
            $availableAt = new DateTime($lesson['available_at']);
            if ($availableAt > $now) {
                $scheduledLessons++;
                if ($nextAvailableLesson === null || $availableAt < $nextAvailableLesson) {
                    $nextAvailableLesson = $availableAt;
                }
            }
        } catch (Exception $e) {
            // Ignore invalid dates
        }
    }

    $totalDuration += (int)($lesson['duration_minutes'] ?? 0);
}

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<style>
    .page-content {
        background: #f8fafc;
        min-height: 100vh;
        padding: 24px;
    }

    .course-header-card {
        background: white;
        border-radius: 20px;
        padding: 32px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .course-header {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: space-between;
        align-items: flex-start;
    }

    .course-title {
        margin: 0;
        font-weight: 700;
        font-size: 24px;
        color: #1e293b;
    }

    .course-description {
        margin-top: 8px;
        color: #475569;
        line-height: 1.8;
        font-size: 14px;
        max-width: 720px;
    }

    .course-meta {
        margin-top: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        color: #64748b;
        font-size: 13px;
    }

    .course-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f1f5f9;
        padding: 8px 14px;
        border-radius: 999px;
    }

    .course-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        border: 1px solid #e2e8f0;
        display: flex;
        gap: 16px;
        align-items: center;
        box-shadow: 0 8px 24px rgba(100, 116, 139, 0.08);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f46e5;
        font-size: 24px;
    }

    .stat-content h3 {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
        font-size: 20px;
    }

    .stat-content p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .lessons-container {
        background: white;
        border-radius: 20px;
        padding: 26px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 30px rgba(100, 116, 139, 0.1);
    }

    .lessons-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }

    .lessons-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .lessons-header h2 ion-icon {
        font-size: 24px;
        color: #4f46e5;
    }

    .lesson-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .lesson-item {
        display: flex;
        gap: 18px;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.7) 0%, #ffffff 100%);
        align-items: stretch;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .lesson-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 24px rgba(79, 70, 229, 0.12);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .lesson-drag {
        border: none;
        background: #eef2ff;
        color: #4f46e5;
        border-radius: 12px;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: grab;
        flex-shrink: 0;
    }

    .lesson-number {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: #e0f2fe;
        color: #0284c7;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        align-self: center;
        flex-shrink: 0;
    }

    .lesson-thumbnail {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 30px;
        flex-shrink: 0;
    }

    .lesson-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .lesson-content {
        flex: 1;
        min-width: 0;
    }

    .lesson-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .lesson-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
    }

    .lesson-statuses {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .lesson-summary {
        margin: 10px 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
    }

    .lesson-meta {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #64748b;
    }

    .lesson-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .lesson-objectives {
        margin-top: 14px;
        background: #eef2ff;
        padding: 12px 16px;
        border-radius: 14px;
        display: flex;
        gap: 10px;
        color: #4338ca;
        font-size: 13px;
        line-height: 1.6;
    }

    .lesson-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: flex-end;
        flex-shrink: 0;
    }

    .btn-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        padding: 0;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(99, 102, 241, 0.35);
        color: white;
    }

    .badge-free {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-draft {
        background: rgba(239, 68, 68, 0.15);
        color: #dc2626;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-scheduled {
        background: rgba(14, 165, 233, 0.15);
        color: #0284c7;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #94a3b8;
    }

    .empty-state ion-icon {
        font-size: 88px;
        opacity: 0.25;
        display: block;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        margin: 0 0 10px;
        color: #64748b;
        font-weight: 600;
    }

    .sortable-ghost {
        opacity: 0.6;
        transform: rotate(-1deg);
    }

    @media (max-width: 768px) {
        .lesson-item {
            flex-direction: column;
            align-items: stretch;
        }

        .lesson-actions {
            flex-direction: row;
            justify-content: flex-end;
        }

        .lesson-drag {
            align-self: flex-end;
        }

        .lesson-number {
            align-self: flex-start;
        }
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($warningMessage)): ?>
            <div class="alert alert-warning" role="alert">
                <?= htmlspecialchars($warningMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($infoMessage)): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($infoMessage); ?>
            </div>
        <?php endif; ?>

        <div class="course-header-card">
            <div class="course-header">
                <div>
                    <h1 class="course-title"><?= htmlspecialchars($course['title'] ?? 'دوره بدون عنوان'); ?></h1>
                    <?php if (!empty($course['description'])): ?>
                        <p class="course-description"><?= nl2br(htmlspecialchars($course['description'])); ?></p>
                    <?php endif; ?>
                </div>
                <div class="course-actions">
                    <a class="btn btn-outline-secondary" href="<?= UtilityHelper::baseUrl('organizations/courses'); ?>">
                        <ion-icon name="arrow-back-outline" style="font-size: 20px; margin-left: 6px;"></ion-icon>
                        بازگشت به لیست دوره‌ها
                    </a>
                    <a class="btn btn-primary" href="<?= UtilityHelper::baseUrl('organizations/courses/lessons/create?course_id=' . ($course['id'] ?? 0)); ?>">
                        <ion-icon name="add-circle-outline" style="font-size: 20px; margin-left: 6px;"></ion-icon>
                        افزودن درس جدید
                    </a>
                </div>
            </div>

            <div class="course-meta">
                <span>
                    <ion-icon name="layers-outline"></ion-icon>
                    <?= $totalLessons; ?> درس
                </span>
                <span>
                    <ion-icon name="time-outline"></ion-icon>
                    <?= $totalDuration > 0 ? $totalDuration . ' دقیقه محتوا' : 'مدت زمان مشخص نشده'; ?>
                </span>
                <span>
                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                    <?= $publishedLessons; ?> منتشر شده
                </span>
                <?php if ($draftLessons > 0): ?>
                    <span>
                        <ion-icon name="alert-circle-outline"></ion-icon>
                        <?= $draftLessons; ?> پیش‌نویس
                    </span>
                <?php endif; ?>
                <?php if ($scheduledLessons > 0): ?>
                    <span>
                        <ion-icon name="calendar-outline"></ion-icon>
                        <?= $scheduledLessons; ?> برنامه‌ریزی شده
                    </span>
                <?php endif; ?>
                <?php if ($nextAvailableLesson instanceof DateTime): ?>
                    <span>
                        <ion-icon name="alarm-outline"></ion-icon>
                        انتشار بعدی: <?= $nextAvailableLesson->format('Y/m/d H:i'); ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($course['status'])): ?>
                    <span>
                        <ion-icon name="information-circle-outline"></ion-icon>
                        وضعیت دوره: <?= htmlspecialchars($course['status']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <ion-icon name="book-outline"></ion-icon>
                </div>
                <div class="stat-content">
                    <h3><?= $totalLessons; ?></h3>
                    <p>تعداد کل درس‌ها</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #10b981;">
                    <ion-icon name="sparkles-outline"></ion-icon>
                </div>
                <div class="stat-content">
                    <h3><?= $publishedLessons; ?></h3>
                    <p>درس‌های منتشر شده</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #ef4444;">
                    <ion-icon name="hourglass-outline"></ion-icon>
                </div>
                <div class="stat-content">
                    <h3><?= $draftLessons; ?></h3>
                    <p>در انتظار انتشار</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); color: #7c3aed;">
                    <ion-icon name="time-outline"></ion-icon>
                </div>
                <div class="stat-content">
                    <h3><?= $totalDuration; ?> دقیقه</h3>
                    <p>زمان کل محتوا</p>
                </div>
            </div>
        </div>

        <div class="lessons-container">
            <div class="lessons-header">
                <h2>
                    <ion-icon name="list-outline"></ion-icon>
                    لیست درس‌ها
                </h2>
            </div>

            <?php if (empty($lessons)): ?>
                <div class="empty-state">
                    <ion-icon name="folder-open-outline"></ion-icon>
                    <h3>هنوز درسی برای این دوره ثبت نشده است</h3>
                    <p>برای شروع از دکمه افزودن درس جدید استفاده کنید</p>
                </div>
            <?php else: ?>
                <div id="lessonsList" class="lesson-list">
                    <?php foreach ($lessons as $index => $lesson): 
                        $thumbnailUrl = !empty($lesson['thumbnail_path'])
                            ? UtilityHelper::baseUrl('public/uploads/lessons/' . ltrim($lesson['thumbnail_path'], '/'))
                            : null;
                        $isPublished = (int)($lesson['is_published'] ?? 1) === 1;
                        $availableAtText = '';
                        if (!empty($lesson['available_at'])) {
                            try {
                                $availableAtDate = new DateTime($lesson['available_at']);
                                $availableAtText = $availableAtDate->format('Y/m/d H:i');
                            } catch (Exception $e) {
                                $availableAtText = $lesson['available_at'];
                            }
                        }
                    ?>
                        <div class="lesson-item" data-id="<?= $lesson['id']; ?>">
                            <button class="lesson-drag" type="button" title="تغییر ترتیب">
                                <ion-icon name="menu-outline"></ion-icon>
                            </button>

                            <div class="lesson-number"><?= $index + 1; ?></div>

                            <div class="lesson-thumbnail">
                                <?php if ($thumbnailUrl): ?>
                                    <img src="<?= htmlspecialchars($thumbnailUrl); ?>" alt="<?= htmlspecialchars($lesson['title']); ?>">
                                <?php else: ?>
                                    <ion-icon name="<?= $contentTypeIcons[$lesson['content_type']] ?? 'document-text-outline'; ?>"></ion-icon>
                                <?php endif; ?>
                            </div>

                            <div class="lesson-content">
                                <div class="lesson-title-row">
                                    <h3 class="lesson-title"><?= htmlspecialchars($lesson['title']); ?></h3>
                                    <div class="lesson-statuses">
                                        <?php if (!$isPublished): ?>
                                            <span class="badge badge-draft">پیش‌نویس</span>
                                        <?php endif; ?>
                                        <?php if ($availableAtText): ?>
                                            <span class="badge badge-scheduled">انتشار: <?= $availableAtText; ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($lesson['is_free'])): ?>
                                            <span class="badge badge-free">رایگان</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($lesson['short_description'])): ?>
                                    <p class="lesson-summary"><?= nl2br(htmlspecialchars($lesson['short_description'])); ?></p>
                                <?php endif; ?>

                                <div class="lesson-meta">
                                    <span>
                                        <ion-icon name="play-circle-outline"></ion-icon>
                                        <?= $contentTypeLabels[$lesson['content_type']] ?? 'نوع محتوا مشخص نیست'; ?>
                                    </span>
                                    <?php if ((int)($lesson['duration_minutes'] ?? 0) > 0): ?>
                                        <span>
                                            <ion-icon name="time-outline"></ion-icon>
                                            <?= (int)$lesson['duration_minutes']; ?> دقیقه
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($lesson['resources'])): ?>
                                        <span>
                                            <ion-icon name="attach-outline"></ion-icon>
                                            منابع تکمیلی موجود
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($lesson['learning_objectives'])): ?>
                                    <div class="lesson-objectives">
                                        <ion-icon name="flag-outline"></ion-icon>
                                        <span><?= nl2br(htmlspecialchars($lesson['learning_objectives'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="lesson-actions">
                                <a class="btn btn-outline-primary btn-icon" href="<?= UtilityHelper::baseUrl('organizations/courses/lessons/edit?lesson_id=' . $lesson['id']); ?>" title="ویرایش درس">
                                    <ion-icon name="create-outline"></ion-icon>
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-icon" onclick="deleteLesson(<?= $lesson['id']; ?>)" title="حذف درس">
                                    <ion-icon name="trash-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lessonsList = document.getElementById('lessonsList');
    if (lessonsList && typeof Sortable !== 'undefined') {
        new Sortable(lessonsList, {
            animation: 150,
            handle: '.lesson-drag',
            ghostClass: 'sortable-ghost',
            onEnd: updateLessonsOrder
        });
    }
});

function updateLessonsOrder() {
    const lessonItems = document.querySelectorAll('.lesson-item');
    if (lessonItems.length === 0) {
        return;
    }

    const lessonIds = Array.from(lessonItems).map(item => item.dataset.id);

    fetch('<?= UtilityHelper::baseUrl('organizations/courses/lessons/reorder'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'course_id=<?= (int)($course['id'] ?? 0); ?>&lesson_ids=' + encodeURIComponent(JSON.stringify(lessonIds)),
        credentials: 'same-origin'
    })
        .then(response => response.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (error) {
                console.error('Reorder lessons response (non-JSON):', text);
                throw new Error('invalid-json');
            }
        })
        .then(data => {
            if (data.success) {
                lessonItems.forEach((item, index) => {
                    const numberElement = item.querySelector('.lesson-number');
                    if (numberElement) {
                        numberElement.textContent = index + 1;
                    }
                });
            } else {
                alert('خطا در به‌روزرسانی ترتیب: ' + data.message);
                location.reload();
            }
        })
        .catch(error => {
            if (error.message === 'invalid-json') {
                alert('خطا در ارتباط با سرور. پاسخ نامعتبر دریافت شد.');
            } else {
                alert('خطایی در ذخیره ترتیب درس‌ها رخ داد.');
                console.error(error);
            }
            location.reload();
        });
}

function deleteLesson(lessonId) {
    if (!confirm('آیا از حذف این درس اطمینان دارید؟')) {
        return;
    }

    fetch('<?= UtilityHelper::baseUrl('organizations/courses/lessons/delete'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'lesson_id=' + encodeURIComponent(lessonId),
        credentials: 'same-origin'
    })
        .then(response => response.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (error) {
                console.error('Delete lesson response (non-JSON):', text);
                throw new Error('invalid-json');
            }
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('خطا در حذف درس: ' + data.message);
            }
        })
        .catch(error => {
            if (error.message === 'invalid-json') {
                alert('خطا در ارتباط با سرور. پاسخ نامعتبر دریافت شد.');
            } else {
                alert('خطایی در حذف درس رخ داد.');
                console.error(error);
            }
        });
}
</script>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>

