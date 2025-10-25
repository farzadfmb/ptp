<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'دوره‌های توسعه فردی';
$user = $user ?? [];
$courses = $courses ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$statusLabels = [
    'draft' => 'پیش‌نویس',
    'published' => 'منتشر شده',
    'archived' => 'بایگانی شده',
    'presale' => 'پیش‌فروش',
];

$statusColors = [
    'draft' => 'secondary',
    'published' => 'success',
    'archived' => 'warning',
    'presale' => 'info',
];

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<style>
    .courses-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 40px;
        color: white;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    
    .courses-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .courses-hero h2 {
        position: relative;
        z-index: 1;
        margin-bottom: 8px;
    }
    
    .courses-hero p {
        position: relative;
        z-index: 1;
        opacity: 0.9;
    }
    
    .course-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .course-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
        border-color: #667eea;
    }
    
    .course-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 64px;
        position: relative;
        overflow: hidden;
    }
    
    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-image-placeholder {
        font-size: 64px;
        color: white;
    }
    
    .course-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-cover-placeholder {
        font-size: 64px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .course-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .course-category {
        display: inline-block;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
    }
    
    .course-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
        line-height: 1.4;
    }
    
    .course-description {
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }
    
    .course-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        margin-top: auto;
    }
    
    .course-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #64748b;
    }
    
    .course-meta-item ion-icon {
        font-size: 18px;
        color: #94a3b8;
    }
    
    .course-actions {
        display: flex;
        gap: 8px;
        padding: 0 20px 20px;
    }
    
    .btn-edit {
        flex: 1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .btn-edit ion-icon {
        font-size: 18px;
    }
    
    .btn-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 44px;
    }
    
    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    
    .btn-delete ion-icon {
        font-size: 18px;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        border: 2px dashed #e2e8f0;
    }
    
    .empty-state ion-icon {
        font-size: 80px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }
    
    .empty-state h3 {
        color: #475569;
        margin-bottom: 8px;
    }
    
    .empty-state p {
        color: #94a3b8;
        margin-bottom: 24px;
    }
    
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .stats-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }
    
    .stats-icon.primary {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .stats-icon.success {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }
    
    .stats-icon.warning {
        background: rgba(251, 146, 60, 0.1);
        color: #fb923c;
    }
    
    .stats-content h4 {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    
    .stats-content p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }
    
    .filter-section {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }
    
    .course-status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <!-- Hero Section -->
        <div class="courses-hero">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-8">دوره‌های توسعه فردی</h2>
                    <p class="mb-0">مدیریت و ایجاد دوره‌های آموزشی برای توسعه مهارت‌های کارکنان</p>
                </div>
                <a href="<?= UtilityHelper::baseUrl('organizations/courses/create'); ?>" class="btn btn-light rounded-pill px-24 d-inline-flex align-items-center gap-2">
                    <ion-icon name="add-circle-outline" style="font-size: 18px;"></ion-icon>
                    ایجاد دوره جدید
                </a>
            </div>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success rounded-16 d-flex align-items-center gap-12 mb-24" role="alert">
                <ion-icon name="checkmark-circle-outline" style="font-size: 24px;"></ion-icon>
                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12 mb-24" role="alert">
                <ion-icon name="alert-circle-outline" style="font-size: 24px;"></ion-icon>
                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row g-3 mb-24">
            <div class="col-12 col-md-4">
                <div class="stats-card">
                    <div class="stats-icon primary">
                        <ion-icon name="book-outline"></ion-icon>
                    </div>
                    <div class="stats-content">
                        <h4><?= UtilityHelper::englishToPersian((string) count($courses)); ?></h4>
                        <p>کل دوره‌ها</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stats-card">
                    <div class="stats-icon success">
                        <ion-icon name="checkmark-done-outline"></ion-icon>
                    </div>
                    <div class="stats-content">
                        <?php
                            $publishedCount = count(array_filter($courses, function($c) {
                                return ($c['status'] ?? '') === 'published';
                            }));
                        ?>
                        <h4><?= UtilityHelper::englishToPersian((string) $publishedCount); ?></h4>
                        <p>دوره‌های منتشر شده</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stats-card">
                    <div class="stats-icon warning">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                    <div class="stats-content">
                        <?php
                            $totalEnrollments = array_sum(array_map(function($c) {
                                return (int) ($c['enrollment_count'] ?? 0);
                            }, $courses));
                        ?>
                        <h4><?= UtilityHelper::englishToPersian((string) $totalEnrollments); ?></h4>
                        <p>کل ثبت‌نام‌ها</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label">جستجو</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="جستجو در عنوان یا توضیحات...">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">وضعیت</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">همه</option>
                        <option value="published">منتشر شده</option>
                        <option value="draft">پیش‌نویس</option>
                        <option value="presale">پیش‌فروش</option>
                        <option value="archived">بایگانی شده</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">دسته‌بندی</label>
                    <select id="categoryFilter" class="form-select">
                        <option value="">همه دسته‌ها</option>
                        <?php
                            $categories = array_unique(array_filter(array_map(function($c) {
                                return trim((string) ($c['category'] ?? ''));
                            }, $courses)));
                            foreach ($categories as $cat):
                        ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">
                        <ion-icon name="refresh-outline"></ion-icon>
                        بازنشانی
                    </button>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <?php if (!empty($courses)): ?>
            <div class="row g-4" id="coursesGrid">
                <?php foreach ($courses as $course): ?>
                    <?php
                        $courseId = (int) ($course['id'] ?? 0);
                        $title = htmlspecialchars((string) ($course['title'] ?? 'بدون عنوان'), ENT_QUOTES, 'UTF-8');
                        $description = htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $category = htmlspecialchars((string) ($course['category'] ?? 'عمومی'), ENT_QUOTES, 'UTF-8');
                        $status = (string) ($course['status'] ?? 'draft');
                        $statusLabel = $statusLabels[$status] ?? 'نامشخص';
                        $statusColor = $statusColors[$status] ?? 'secondary';
                        $coverImage = (string) ($course['cover_image'] ?? '');
                        $lessonCount = (int) ($course['lesson_count'] ?? 0);
                        $enrollmentCount = (int) ($course['enrollment_count'] ?? 0);
                        $durationHours = (int) ($course['duration_hours'] ?? 0);
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 course-item" 
                         data-title="<?= $title; ?>" 
                         data-description="<?= $description; ?>"
                         data-status="<?= $status; ?>"
                         data-category="<?= $category; ?>">
                        <div class="course-card">
                            <div class="course-image">
                                <?php if (!empty($coverImage)): ?>
                                    <img src="<?= UtilityHelper::baseUrl('public/uploads/courses/' . htmlspecialchars($coverImage, ENT_QUOTES, 'UTF-8')); ?>" alt="<?= $title; ?>">
                                <?php else: ?>
                                    <ion-icon name="book-outline" class="course-image-placeholder"></ion-icon>
                                <?php endif; ?>
                                <span class="badge bg-<?= $statusColor; ?> course-status-badge" style="position: absolute; top: 12px; right: 12px;">
                                    <?= $statusLabel; ?>
                                </span>
                            </div>
                            
                            <div class="course-body">
                                <span class="course-category"><?= $category; ?></span>
                                <h3 class="course-title"><?= $title; ?></h3>
                                <?php if (!empty($description)): ?>
                                    <p class="course-description"><?= $description; ?></p>
                                <?php endif; ?>
                                
                                <div class="course-meta">
                                    <div class="course-meta-item">
                                        <ion-icon name="play-circle-outline"></ion-icon>
                                        <span><?= UtilityHelper::englishToPersian((string) $lessonCount); ?> درس</span>
                                    </div>
                                    <div class="course-meta-item" data-course-enrollment="<?= $courseId; ?>">
                                        <ion-icon name="people-outline"></ion-icon>
                                        <span><span class="course-enrollment-count" data-course-id="<?= $courseId; ?>"><?= UtilityHelper::englishToPersian((string) $enrollmentCount); ?></span> نفر</span>
                                    </div>
                                    <?php if ($durationHours > 0): ?>
                                        <div class="course-meta-item">
                                            <ion-icon name="time-outline"></ion-icon>
                                            <span><?= UtilityHelper::englishToPersian((string) $durationHours); ?> ساعت</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="course-actions">
                                <a href="<?= UtilityHelper::baseUrl('organizations/courses/lessons?course_id=' . $courseId); ?>" 
                                   class="btn-edit" 
                                   style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"
                                   title="مدیریت درس‌ها">
                                    <ion-icon name="list-outline"></ion-icon>
                                    درس‌ها
                                </a>
                                <button type="button" class="btn-edit btn-manage-evaluatees" data-course-id="<?= $courseId; ?>" data-course-title="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                                    <ion-icon name="people-outline"></ion-icon>
                                    مدیریت کاربران
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/courses/edit?id=' . $courseId); ?>" 
                                   class="btn-edit">
                                    <ion-icon name="create-outline"></ion-icon>
                                    ویرایش
                                </a>
                                <button type="button" class="btn-delete" onclick="deleteCourse(<?= $courseId; ?>, '<?= addslashes($title); ?>')">
                                    <ion-icon name="trash-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div id="noResults" class="empty-state mt-24" style="display: none;">
                <ion-icon name="search-outline"></ion-icon>
                <h3>نتیجه‌ای یافت نشد</h3>
                <p>با فیلترهای دیگری جستجو کنید</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <ion-icon name="book-outline"></ion-icon>
                <h3>هنوز دوره‌ای ایجاد نشده است</h3>
                <p>برای شروع، اولین دوره آموزشی خود را ایجاد کنید</p>
                <a href="<?= UtilityHelper::baseUrl('organizations/courses/create'); ?>" class="btn btn-primary rounded-pill px-24 d-flex align-items-center gap-2 justify-content-center mx-auto" style="width: fit-content;">
                    <ion-icon name="add-circle-outline" style="font-size: 18px;"></ion-icon>
                    ایجاد اولین دوره
                </a>
            </div>
        <?php endif; ?>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const resetBtn = document.getElementById('resetFilters');
    const courseItems = document.querySelectorAll('.course-item');
    const noResults = document.getElementById('noResults');
    const coursesGrid = document.getElementById('coursesGrid');
    
    function filterCourses() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        const selectedCategory = categoryFilter.value;
        
        let visibleCount = 0;
        
        courseItems.forEach(function(item) {
            const title = item.getAttribute('data-title').toLowerCase();
            const description = item.getAttribute('data-description').toLowerCase();
            const status = item.getAttribute('data-status');
            const category = item.getAttribute('data-category');
            
            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            const matchesStatus = !selectedStatus || status === selectedStatus;
            const matchesCategory = !selectedCategory || category === selectedCategory;
            
            if (matchesSearch && matchesStatus && matchesCategory) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        if (visibleCount === 0) {
            coursesGrid.style.display = 'none';
            noResults.style.display = 'block';
        } else {
            coursesGrid.style.display = '';
            noResults.style.display = 'none';
        }
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterCourses);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterCourses);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterCourses);
    }
    
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            statusFilter.value = '';
            categoryFilter.value = '';
            filterCourses();
        });
    }
});

function deleteCourse(courseId, courseTitle) {
    if (confirm('آیا از حذف دوره "' + courseTitle + '" اطمینان دارید؟\n\nتوجه: تمام دروس و داده‌های مرتبط با این دوره نیز حذف خواهند شد.')) {
        fetch('<?= UtilityHelper::baseUrl('organizations/courses/delete'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'course_id=' + courseId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show updated list
                window.location.reload();
            } else {
                alert('خطا در حذف دوره: ' + (data.message || 'خطای نامشخص'));
            }
        })
        .catch(error => {
            alert('خطا در ارتباط با سرور');
            console.error('Error:', error);
        });
    }
}
</script>

<!-- Evaluatees modal -->
<div id="evaluateesModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1200; align-items:center; justify-content:center;">
    <div style="width:940px; max-width:95%; background:#fff; border-radius:12px; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #eef2f6; display:flex; justify-content:space-between; align-items:center;">
            <h4 id="evaluateesModalTitle" style="margin:0; font-size:16px;">مدیریت کاربران دوره</h4>
            <button type="button" id="evaluateesModalClose" style="background:transparent;border:none;font-size:20px;cursor:pointer;">&times;</button>
        </div>
        <div style="display:flex; gap:12px; padding:18px;">
            <div style="flex:1; min-height:240px; border-right:1px solid #f1f5f9; padding-right:12px;">
                <h5 style="margin-top:0;">کاربران ثبت‌شده</h5>
                <div id="enrolledList" style="max-height:420px; overflow:auto;"></div>
            </div>
            <div style="width:420px;">
                <h5 style="margin-top:0;">افزودن کاربر</h5>
                <input id="availableSearch" type="text" class="form-control" placeholder="جستجو کاربران..." style="margin-bottom:8px;">
                <div id="availableList" style="max-height:360px; overflow:auto;"></div>
            </div>
        </div>
        <div style="padding:12px 18px; border-top:1px solid #eef2f6; text-align:right;">
            <button type="button" id="evaluateesModalCloseBottom" class="btn btn-outline-secondary">بستن</button>
        </div>
    </div>
</div>

<script>
// Base endpoints (rendered by PHP)
const ENDPOINTS = {
    list: '<?= UtilityHelper::baseUrl('organizations/courses/evaluatees'); ?>',
    enroll: '<?= UtilityHelper::baseUrl('organizations/courses/enroll'); ?>',
    unenroll: '<?= UtilityHelper::baseUrl('organizations/courses/unenroll'); ?>'
};

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-manage-evaluatees');
    if (!btn) return;
    const courseId = btn.getAttribute('data-course-id');
    const courseTitle = btn.getAttribute('data-course-title');
    openEvaluateesModal(courseId, courseTitle);
});

const modal = document.getElementById('evaluateesModal');
const modalTitle = document.getElementById('evaluateesModalTitle');
const modalClose = document.getElementById('evaluateesModalClose');
const modalCloseBottom = document.getElementById('evaluateesModalCloseBottom');
const enrolledList = document.getElementById('enrolledList');
const availableList = document.getElementById('availableList');
const availableSearch = document.getElementById('availableSearch');

let currentCourseId = null;
let currentCourseTitle = '';
let currentEnrolled = [];
let currentAvailable = [];

modalClose.addEventListener('click', closeEvaluateesModal);
modalCloseBottom.addEventListener('click', closeEvaluateesModal);

if (availableSearch) {
    availableSearch.addEventListener('input', renderAvailableList);
}

function openEvaluateesModal(courseId, courseTitle) {
    currentCourseId = Number(courseId);
    currentCourseTitle = courseTitle;
    currentEnrolled = [];
    currentAvailable = [];

    modalTitle.textContent = 'مدیریت کاربران دوره: ' + courseTitle;
    modal.style.display = 'flex';
    modal.setAttribute('data-course-id', courseId);
    enrolledList.innerHTML = '<p>در حال بارگذاری...</p>';
    availableList.innerHTML = '<p>در حال بارگذاری...</p>';
    if (availableSearch) {
        availableSearch.value = '';
    }

    fetch(ENDPOINTS.list + '?course_id=' + encodeURIComponent(courseId), {
        credentials: 'same-origin'
    }).then(r => r.json()).then(data => {
        if (!data.success) {
            enrolledList.innerHTML = '<div class="alert alert-danger">' + (data.message || 'خطا در دریافت اطلاعات') + '</div>';
            availableList.innerHTML = '';
            return;
        }

        currentEnrolled = sanitizeEnrolledList(data.data.enrolled_users || []);
        currentAvailable = sanitizeAvailableList(data.data.available_users || []);
        renderEnrolledList();
        renderAvailableList();
    }).catch(err => {
        enrolledList.innerHTML = '<div class="alert alert-danger">خطا در ارتباط با سرور</div>';
        availableList.innerHTML = '';
        console.error(err);
    });
}

function closeEvaluateesModal() {
    modal.style.display = 'none';
    modal.removeAttribute('data-course-id');
    currentCourseId = null;
    currentCourseTitle = '';
    currentEnrolled = [];
    currentAvailable = [];
    enrolledList.innerHTML = '';
    availableList.innerHTML = '';
    if (availableSearch) {
        availableSearch.value = '';
    }
}

function sanitizeEnrolledList(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items.map(function(item) {
        return {
            id: Number(item.id || item.user_id || 0),
            name: String(item.name || ((item.first_name || '') + ' ' + (item.last_name || '')) || 'کاربر بدون نام').trim() || 'کاربر بدون نام',
            email: item.email ? String(item.email) : '',
            evaluation_code: item.evaluation_code ? String(item.evaluation_code) : '',
            enrolled_at: item.enrolled_at || '',
            completed_at: item.completed_at || '',
            progress_percentage: parseFloat(item.progress_percentage || 0)
        };
    }).filter(function(item) {
        return item.id > 0;
    });
}

function sanitizeAvailableList(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items.map(function(item) {
        return {
            id: Number(item.id || 0),
            name: String(item.name || ((item.first_name || '') + ' ' + (item.last_name || '')) || 'کاربر بدون نام').trim() || 'کاربر بدون نام',
            email: item.email ? String(item.email) : '',
            evaluation_code: item.evaluation_code ? String(item.evaluation_code) : ''
        };
    }).filter(function(item) {
        return item.id > 0;
    }).sort(function(a, b) {
        return a.name.localeCompare(b.name);
    });
}

function renderEnrolledList() {
    if (!currentEnrolled.length) {
        enrolledList.innerHTML = '<p class="text-muted">هیچ کاربری برای این دوره ثبت‌نام نکرده است.</p>';
        updateEnrollmentBadge();
        return;
    }

    enrolledList.innerHTML = '';
    currentEnrolled.forEach(function(u) {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.justifyContent = 'space-between';
        row.style.alignItems = 'center';
        row.style.padding = '8px 6px';
        row.style.borderBottom = '1px solid #f1f5f9';

        const detail = document.createElement('div');
        const metadata = [u.email, u.evaluation_code].filter(Boolean).join(' • ');
        detail.innerHTML = '<strong>' + escapeHtml(u.name) + '</strong>' + (metadata ? '<div style="font-size:12px;color:#6b7280;">' + escapeHtml(metadata) + '</div>' : '');

        const actions = document.createElement('div');
        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn btn-outline-danger btn-sm';
        removeBtn.textContent = 'حذف';
        removeBtn.addEventListener('click', function() {
            handleUnenroll(u);
        });

        actions.appendChild(removeBtn);
        row.appendChild(detail);
        row.appendChild(actions);
        enrolledList.appendChild(row);
    });

    updateEnrollmentBadge();
}

function renderAvailableList() {
    const query = availableSearch ? availableSearch.value.trim().toLowerCase() : '';
    const filtered = currentAvailable.filter(function(u) {
        if (!query) {
            return true;
        }
        const haystack = [u.name, u.email, u.evaluation_code].join(' ').toLowerCase();
        return haystack.includes(query);
    });

    availableList.innerHTML = '';

    if (!filtered.length) {
        availableList.innerHTML = '<p class="text-muted">هیچ کاربر ارزیابی‌شونده‌ای موجود نیست.</p>';
        return;
    }

    filtered.forEach(function(u) {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.justifyContent = 'space-between';
        row.style.alignItems = 'center';
        row.style.padding = '8px 6px';
        row.style.borderBottom = '1px solid #f1f5f9';

        const detail = document.createElement('div');
        const metadata = [u.email, u.evaluation_code].filter(Boolean).join(' • ');
        detail.innerHTML = '<strong>' + escapeHtml(u.name) + '</strong>' + (metadata ? '<div style="font-size:12px;color:#6b7280;">' + escapeHtml(metadata) + '</div>' : '');

        const actions = document.createElement('div');
        const addBtn = document.createElement('button');
        addBtn.className = 'btn btn-primary btn-sm';
        addBtn.textContent = 'افزودن';
        addBtn.addEventListener('click', function() {
            handleEnroll(u, addBtn);
        });

        actions.appendChild(addBtn);
        row.appendChild(detail);
        row.appendChild(actions);
        availableList.appendChild(row);
    });
}

function handleEnroll(user, button) {
    if (!currentCourseId) {
        return;
    }

    button.disabled = true;

    fetch(ENDPOINTS.enroll, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'course_id=' + encodeURIComponent(currentCourseId) + '&user_id=' + encodeURIComponent(user.id)
    }).then(r => r.json()).then(resp => {
        button.disabled = false;
        if (!resp.success) {
            alert(resp.message || 'خطا در افزودن کاربر');
            return;
        }

        const newUser = sanitizeEnrolledList([resp.data && resp.data.user ? resp.data.user : user])[0];
        if (!newUser) {
            return;
        }

        currentEnrolled = [newUser].concat(currentEnrolled.filter(function(item) { return item.id !== newUser.id; }));
        currentAvailable = currentAvailable.filter(function(item) { return item.id !== newUser.id; });
        renderEnrolledList();
        renderAvailableList();
    }).catch(err => {
        button.disabled = false;
        alert('خطا در ارتباط');
        console.error(err);
    });
}

function handleUnenroll(user) {
    if (!currentCourseId) {
        return;
    }

    if (!confirm('آیا مطمئنید این کاربر از دوره حذف شود؟')) {
        return;
    }

    fetch(ENDPOINTS.unenroll, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'course_id=' + encodeURIComponent(currentCourseId) + '&user_id=' + encodeURIComponent(user.id)
    }).then(r => r.json()).then(resp => {
        if (!resp.success) {
            alert(resp.message || 'خطا در عملیات');
            return;
        }

        currentEnrolled = currentEnrolled.filter(function(item) { return item.id !== user.id; });

        if (!currentAvailable.some(function(item) { return item.id === user.id; })) {
            currentAvailable.push({
                id: user.id,
                name: user.name,
                email: user.email,
                evaluation_code: user.evaluation_code
            });
            currentAvailable.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
        }

        renderEnrolledList();
        renderAvailableList();
    }).catch(err => {
        alert('خطا در ارتباط');
        console.error(err);
    });
}

function updateEnrollmentBadge() {
    if (!currentCourseId && currentCourseId !== 0) {
        return;
    }

    const badge = document.querySelector('.course-enrollment-count[data-course-id="' + currentCourseId + '"]');
    if (badge) {
        badge.textContent = toPersianDigits(currentEnrolled.length);
    }
}

function toPersianDigits(value) {
    const map = {'0': '۰', '1': '۱', '2': '۲', '3': '۳', '4': '۴', '5': '۵', '6': '۶', '7': '۷', '8': '۸', '9': '۹'};
    return String(value).replace(/[0-9]/g, function (digit) {
        return map[digit] || digit;
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function (s) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[s];
    });
}
</script>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
