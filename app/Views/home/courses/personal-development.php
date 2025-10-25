<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'دوره‌های توسعه فردی';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$courses = isset($courses) && is_array($courses) ? $courses : [];

$inline_styles .= <<<'CSS'
.course-hero-card {
    background: linear-gradient(135deg, #4c6ef5 0%, #7950f2 100%);
    border-radius: 24px;
    padding: 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
    border: none;
    box-shadow: 0 16px 40px rgba(76, 110, 245, 0.25);
}
.course-hero-card::after {
    content: '';
    position: absolute;
    inset: auto -120px -120px auto;
    width: 280px;
    height: 280px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
}
.course-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.course-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(99, 102, 241, 0.18);
    border-color: rgba(99, 102, 241, 0.4);
}
.course-cover {
    position: relative;
    height: 220px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.course-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.course-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.course-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    color: #64748b;
    font-size: 0.86rem;
}
.course-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 999px;
}
.course-progress {
    background: #f1f5f9;
    border-radius: 18px;
    padding: 16px;
    margin: 20px 0;
}
.course-progress .progress {
    height: 12px;
    border-radius: 999px;
    overflow: hidden;
}
.lesson-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.lesson-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 18px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.lesson-item:hover {
    border-color: rgba(99, 102, 241, 0.35);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.18);
}
.lesson-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(129, 140, 248, 0.25) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4f46e5;
    font-size: 24px;
    flex-shrink: 0;
}
.lesson-content {
    flex: 1;
    min-width: 0;
}
.lesson-actions {
    display: flex;
    align-items: center;
}
.lesson-status.badge {
    border-radius: 999px;
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
}
.lesson-viewer-iframe {
    width: 100%;
    height: 70vh;
    border-radius: 16px;
    border: 1px solid #dee2e6;
    background: #fff;
}
.lesson-text-content {
    line-height: 1.8;
    color: #1e293b;
}
CSS;

$inline_scripts .= <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('lessonViewerModal');
    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    const modalTitle = modalElement.querySelector('.lesson-viewer-title');
    const modalBody = modalElement.querySelector('#lessonViewerContainer');
    const downloadButton = modalElement.querySelector('#lessonViewerDownload');

    const decodeBase64 = function (value) {
        if (!value) {
            return '';
        }
        try {
            const decoded = atob(value);
            const escaped = decoded.split('').map(function (char) {
                const hex = char.charCodeAt(0).toString(16).padStart(2, '0');
                return '%' + hex;
            }).join('');
            return decodeURIComponent(escaped);
        } catch (error) {
            return '';
        }
    };

    document.querySelectorAll('[data-lesson-viewer="1"]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.disabled) {
                return;
            }

            const lessonTitle = button.getAttribute('data-lesson-title') || '';
            const lessonType = button.getAttribute('data-lesson-type') || '';
            const mediaUrl = button.getAttribute('data-media-url') || '';
            const viewerUrl = button.getAttribute('data-viewer-url') || '';
            const encodedText = button.getAttribute('data-text-content') || '';

            modalTitle.textContent = lessonTitle !== '' ? lessonTitle : 'نمایش محتوا';
            modalBody.innerHTML = '';
            downloadButton.classList.add('d-none');
            downloadButton.removeAttribute('href');

            if (lessonType === 'video' && viewerUrl) {
                const video = document.createElement('video');
                video.src = viewerUrl;
                video.controls = true;
                video.playsInline = true;
                video.className = 'w-100 rounded-4';
                modalBody.appendChild(video);

                if (mediaUrl) {
                    downloadButton.href = mediaUrl;
                    downloadButton.classList.remove('d-none');
                }
            } else if (lessonType === 'pdf' && viewerUrl) {
                const iframe = document.createElement('iframe');
                iframe.src = viewerUrl.includes('#') ? viewerUrl : viewerUrl + '#toolbar=0';
                iframe.className = 'lesson-viewer-iframe';
                iframe.setAttribute('frameborder', '0');
                modalBody.appendChild(iframe);

                if (mediaUrl) {
                    downloadButton.href = mediaUrl;
                    downloadButton.classList.remove('d-none');
                }
            } else if (lessonType === 'ppt' && viewerUrl) {
                const iframe = document.createElement('iframe');
                iframe.src = viewerUrl;
                iframe.className = 'lesson-viewer-iframe';
                iframe.setAttribute('frameborder', '0');
                modalBody.appendChild(iframe);

                if (mediaUrl) {
                    downloadButton.href = mediaUrl;
                    downloadButton.classList.remove('d-none');
                }
            } else if (lessonType === 'text') {
                const textWrapper = document.createElement('div');
                textWrapper.className = 'lesson-text-content';
                const decoded = decodeBase64(encodedText);
                textWrapper.innerHTML = decoded !== '' ? decoded : '<p class="text-muted mb-0">محتوای متنی برای این درس ثبت نشده است.</p>';
                modalBody.appendChild(textWrapper);
            } else if (lessonType === 'link' && mediaUrl) {
                const linkWrapper = document.createElement('div');
                linkWrapper.innerHTML = '<p class="mb-3">برای مشاهده محتوای این درس روی لینک زیر کلیک کنید:</p>';
                const anchor = document.createElement('a');
                anchor.href = mediaUrl;
                anchor.target = '_blank';
                anchor.rel = 'noopener noreferrer';
                anchor.className = 'btn btn-primary';
                anchor.textContent = 'باز کردن لینک در صفحه جدید';
                linkWrapper.appendChild(anchor);
                modalBody.appendChild(linkWrapper);
            } else if (mediaUrl) {
                const iframe = document.createElement('iframe');
                iframe.src = viewerUrl || mediaUrl;
                iframe.className = 'lesson-viewer-iframe';
                iframe.setAttribute('frameborder', '0');
                modalBody.appendChild(iframe);
            } else {
                modalBody.innerHTML = '<div class="alert alert-warning mb-0">محتوای این درس در حال حاضر در دسترس نیست.</div>';
            }

            modal.show();
        });
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        modalBody.innerHTML = '';
    });
});
JS;

AuthHelper::startSession();
$user = AuthHelper::getUser();
$navbarUser = $user;

include __DIR__ . '/../../layouts/home-header.php';
include __DIR__ . '/../../layouts/home-sidebar.php';
?>
<?php include __DIR__ . '/../../layouts/home-navbar.php'; ?>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="course-hero-card">
                    <div class="position-relative" style="z-index: 2;">
                        <h1 class="h4 mb-2">دوره‌های توسعه فردی</h1>
                        <p class="mb-3 mb-md-4 opacity-85">در این بخش می‌توانید دوره‌هایی را که سازمان شما در اختیار شما قرار داده است مشاهده کنید، پیشرفت خود را دنبال نمایید و محتوای هر درس را به صورت آنلاین مشاهده کنید.</p>
                        <div class="d-flex flex-wrap gap-3 small">
                            <span class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-pill px-3 py-2"><ion-icon name="calendar-outline"></ion-icon> <?= htmlspecialchars(UtilityHelper::getTodayDate(), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-pill px-3 py-2"><ion-icon name="book-outline"></ion-icon> <?= htmlspecialchars(UtilityHelper::englishToPersian((string)count($courses)), ENT_QUOTES, 'UTF-8'); ?> دوره فعال</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($courses)): ?>
                <div class="col-12">
                    <div class="card course-card text-center py-5">
                        <div class="card-body">
                            <div class="display-5 text-muted mb-3"><ion-icon name="school-outline"></ion-icon></div>
                            <h2 class="h5 mb-2">دوره‌ای برای شما ثبت نشده است</h2>
                            <p class="text-secondary mb-0">به محض اینکه سازمان شما دوره‌ای را برایتان فعال کند، از این بخش قابل مشاهده خواهد بود.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $index => $course):
                    $courseId = (int)($course['id'] ?? 0);
                    $courseTitle = trim((string)($course['title'] ?? 'بدون عنوان'));
                    $courseDescription = trim((string)($course['description'] ?? ''));
                    $instructor = trim((string)($course['instructor_name'] ?? ''));
                    $category = trim((string)($course['category'] ?? ''));
                    $durationHours = (int)($course['duration_hours'] ?? 0);
                    $progress = isset($course['progress']) && is_array($course['progress']) ? $course['progress'] : ['percentage' => 0, 'total_lessons' => 0, 'completed_lessons' => 0, 'in_progress_lessons' => 0];
                    $lessons = isset($course['lessons']) && is_array($course['lessons']) ? $course['lessons'] : [];
                    $coverImageUrl = trim((string)($course['cover_image_url'] ?? ''));
                    $enrolledAt = trim((string)($course['enrolled_at_display'] ?? ''));
                    $completedAt = trim((string)($course['completed_at_display'] ?? ''));
                    $collapseId = 'courseLessons' . $courseId . '_' . $index;
                ?>
                <div class="col-12 col-xl-6">
                    <div class="course-card">
                        <div class="course-cover">
                            <?php if ($coverImageUrl !== ''): ?>
                                <img src="<?= htmlspecialchars($coverImageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="کاور دوره">
                            <?php else: ?>
                                <ion-icon name="school-sharp"></ion-icon>
                            <?php endif; ?>
                        </div>
                        <div class="course-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h2 class="h5 mb-1 text-dark"><?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <?php if ($category !== ''): ?>
                                        <div class="small text-primary fw-semibold"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['total_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> درس</span>
                            </div>
                            <?php if ($courseDescription !== ''): ?>
                                <p class="text-secondary small mb-3" style="line-height: 1.8;">
                                    <?= nl2br(htmlspecialchars($courseDescription, ENT_QUOTES, 'UTF-8')); ?>
                                </p>
                            <?php endif; ?>
                            <div class="course-meta mb-3">
                                <?php if ($instructor !== ''): ?>
                                    <span><ion-icon name="person-circle-outline"></ion-icon><?= htmlspecialchars($instructor, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if ($durationHours > 0): ?>
                                    <span><ion-icon name="time-outline"></ion-icon><?= htmlspecialchars(UtilityHelper::englishToPersian((string)$durationHours), ENT_QUOTES, 'UTF-8'); ?> ساعت محتوا</span>
                                <?php endif; ?>
                                <?php if ($enrolledAt !== ''): ?>
                                    <span><ion-icon name="calendar-number-outline"></ion-icon> ثبت نام: <?= htmlspecialchars($enrolledAt, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if ($completedAt !== ''): ?>
                                    <span><ion-icon name="checkbox-outline"></ion-icon> اتمام: <?= htmlspecialchars($completedAt, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="course-progress">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold text-dark">پیشرفت دوره</span>
                                    <span class="text-primary fw-semibold"><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['percentage'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>٪</span>
                                </div>
                                <div class="progress bg-white">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= (int)($progress['percentage'] ?? 0); ?>%;" aria-valuenow="<?= (int)($progress['percentage'] ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 small text-secondary mt-3">
                                    <span><ion-icon name="checkmark-circle-outline"></ion-icon> تکمیل شده: <?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['completed_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span><ion-icon name="ellipse-outline"></ion-icon> در حال پیشرفت: <?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['in_progress_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span><ion-icon name="list-outline"></ion-icon> کل دروس: <?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['total_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-dark">لیست درس‌ها</span>
                                <button class="btn btn-sm btn-outline-primary rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>">
                                    مشاهده / پنهان کردن
                                </button>
                            </div>
                            <div class="collapse <?= $index === 0 ? 'show' : ''; ?>" id="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="lesson-list">
                                    <?php if (empty($lessons)): ?>
                                        <div class="text-center text-secondary small">درسی برای این دوره ثبت نشده است.</div>
                                    <?php else: ?>
                                        <?php foreach ($lessons as $lesson):
                                            $lessonTitle = trim((string)($lesson['title'] ?? 'بدون عنوان'));
                                            $lessonState = (string)($lesson['progress_state'] ?? 'pending');
                                            $durationMinutes = (int)($lesson['duration_minutes'] ?? 0);
                                            $watchLabel = trim((string)($lesson['watch_duration_display'] ?? ''));
                                            $availableDisplay = trim((string)($lesson['available_at_display'] ?? ''));
                                            $shortDescription = trim((string)($lesson['short_description'] ?? ''));
                                            $contentType = (string)($lesson['content_type'] ?? 'video');
                                            $isAvailable = (int)($lesson['is_available'] ?? 0) === 1;
                                            $isCompleted = (int)($lesson['is_completed'] ?? 0) === 1;
                                            $mediaUrl = trim((string)($lesson['media_url'] ?? ''));
                                            $viewerUrl = trim((string)($lesson['viewer_url'] ?? ''));
                                            $textContent = (string)($lesson['text_content'] ?? '');
                                            $base64Text = base64_encode($textContent);

                                            $statusMap = [
                                                'completed' => ['label' => 'تکمیل شده', 'class' => 'bg-success-subtle text-success'],
                                                'in_progress' => ['label' => 'در حال پیشرفت', 'class' => 'bg-info-subtle text-info'],
                                                'scheduled' => ['label' => 'به زودی', 'class' => 'bg-warning-subtle text-warning'],
                                                'pending' => ['label' => 'آماده شروع', 'class' => 'bg-secondary-subtle text-secondary'],
                                            ];

                                            $status = $statusMap[$lessonState] ?? $statusMap['pending'];

                                            $iconMap = [
                                                'video' => 'play-circle-outline',
                                                'pdf' => 'document-text-outline',
                                                'ppt' => 'easel-outline',
                                                'link' => 'link-outline',
                                                'text' => 'document-outline',
                                            ];
                                            $iconName = $iconMap[$contentType] ?? 'play-circle-outline';

                                            $buttonDisabled = !$isAvailable || ($contentType !== 'text' && $mediaUrl === '' && $viewerUrl === '');
                                        ?>
                                        <div class="lesson-item">
                                            <div class="lesson-icon"><ion-icon name="<?= htmlspecialchars($iconName, ENT_QUOTES, 'UTF-8'); ?>"></ion-icon></div>
                                            <div class="lesson-content">
                                                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-2">
                                                    <h3 class="h6 mb-0 text-dark"><?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                                                    <span class="badge lesson-status <?= htmlspecialchars($status['class'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($status['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <?php if ($shortDescription !== ''): ?>
                                                    <p class="text-secondary small mb-2" style="line-height: 1.7;">
                                                        <?= nl2br(htmlspecialchars($shortDescription, ENT_QUOTES, 'UTF-8')); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="d-flex flex-wrap gap-3 small text-secondary">
                                                    <?php if ($durationMinutes > 0): ?>
                                                        <span><ion-icon name="time-outline"></ion-icon> مدت: <?= htmlspecialchars(UtilityHelper::englishToPersian((string)$durationMinutes), ENT_QUOTES, 'UTF-8'); ?> دقیقه</span>
                                                    <?php endif; ?>
                                                    <?php if ($watchLabel !== ''): ?>
                                                        <span><ion-icon name="timer-outline"></ion-icon> در حال تماشا: <?= htmlspecialchars($watchLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!$isAvailable && $availableDisplay !== ''): ?>
                                                        <span><ion-icon name="lock-closed-outline"></ion-icon> دسترسی از <?= htmlspecialchars($availableDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($isCompleted && !empty($lesson['last_watched_display'])): ?>
                                                        <span><ion-icon name="checkmark-done-outline"></ion-icon> آخرین مشاهده: <?= htmlspecialchars((string)$lesson['last_watched_display'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="lesson-actions">
                                                <button type="button" class="btn btn-sm <?= $buttonDisabled ? 'btn-outline-secondary' : 'btn-primary'; ?> rounded-pill"
                                                    data-lesson-viewer="1"
                                                    data-lesson-title="<?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-lesson-type="<?= htmlspecialchars($contentType, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-media-url="<?= htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-viewer-url="<?= htmlspecialchars($viewerUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-text-content="<?= htmlspecialchars($base64Text, ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?= $buttonDisabled ? 'disabled' : ''; ?>>
                                                    <?= $buttonDisabled ? 'در دسترس نیست' : 'مشاهده محتوا'; ?>
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="lessonViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title lesson-viewer-title fw-semibold"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body" id="lessonViewerContainer"></div>
            <div class="modal-footer border-0 d-flex justify-content-between">
                <a href="#" class="btn btn-outline-secondary d-none" id="lessonViewerDownload" download>دانلود فایل</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
