<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$course = $course ?? [];
$lesson = $lesson ?? null;
$isEdit = isset($isEdit) ? (bool) $isEdit : false;
$user = $user ?? [];

$formAction = '';
if ($isEdit) {
    $formAction = UtilityHelper::baseUrl('organizations/courses/lessons/update');
    if ($lesson && !empty($lesson['id'])) {
        $formAction .= '?lesson_id=' . (int) $lesson['id'];
    }
} else {
    $formAction = UtilityHelper::baseUrl('organizations/courses/lessons?course_id=' . (int) ($course['id'] ?? 0));
}

$backUrl = UtilityHelper::baseUrl('organizations/courses/lessons?course_id=' . ($course['id'] ?? 0));
$selectedContentType = $lesson['content_type'] ?? 'video';

$availableAtValue = '';
if ($lesson && !empty($lesson['available_at'])) {
    try {
        $availableAtValue = (new DateTime($lesson['available_at']))->format('Y-m-d\\TH:i');
    } catch (Exception $e) {
        $availableAtValue = '';
    }
}

$existingThumbnailUrl = null;
if ($lesson && !empty($lesson['thumbnail_path'])) {
    $existingThumbnailUrl = UtilityHelper::baseUrl('public/uploads/lessons/' . ltrim($lesson['thumbnail_path'], '/'));
}

$existingContentFileUrl = null;
if ($lesson && !empty($lesson['content_file'])) {
    $existingContentFileUrl = UtilityHelper::baseUrl('public/uploads/lessons/' . ltrim($lesson['content_file'], '/'));
}

$uploadMaxFilesize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');

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

    .page-header-card {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 24px;
        padding: 32px;
        margin-bottom: 24px;
        color: white;
        box-shadow: 0 18px 40px rgba(99, 102, 241, 0.25);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .page-header-card h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
    }

    .page-header-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .page-header-meta span {
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 14px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
    }

    .lesson-form-card {
        background: white;
        border-radius: 20px;
        padding: 32px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .form-section {
        margin-bottom: 32px;
        padding-bottom: 32px;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .form-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title ion-icon {
        font-size: 24px;
        color: #6366f1;
    }

    .required-mark {
        color: #ef4444;
        margin-right: 4px;
    }

    .form-hint {
        font-size: 13px;
        color: #64748b;
        margin-top: 6px;
    }

    .textarea-small {
        min-height: 120px;
    }

    .textarea-large {
        min-height: 200px;
    }

    .thumbnail-preview {
        width: 240px;
        height: 160px;
        border-radius: 16px;
        border: 2px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        overflow: hidden;
        position: relative;
    }

    .thumbnail-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumbnail-remove-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
    }

    .thumbnail-preview.has-image .thumbnail-remove-btn {
        display: flex;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        border-radius: 12px;
        padding: 12px 28px;
        font-weight: 600;
        color: white;
        box-shadow: 0 12px 24px rgba(99, 102, 241, 0.25);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .btn-secondary {
        background: white;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 28px;
        font-weight: 600;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        color: #334155;
    }

    .toggle-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
    }

    .toggle-wrapper label {
        margin: 0;
    }

    .metadata-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(148, 163, 184, 0.15);
        color: #475569;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .page-header-card {
            flex-direction: column;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .form-actions a,
        .form-actions button {
            width: 100%;
        }
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <?php if (!empty($successMessage ?? null)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage ?? null)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($warningMessage ?? null)): ?>
            <div class="alert alert-warning" role="alert">
                <?= htmlspecialchars($warningMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($infoMessage ?? null)): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($infoMessage); ?>
            </div>
        <?php endif; ?>

        <div class="page-header-card">
            <div>
                <h1><?= $isEdit ? 'ویرایش درس' : 'افزودن درس جدید'; ?></h1>
                <p style="margin-top: 10px; opacity: 0.85; font-size: 14px; line-height: 1.8;">
                    <?= htmlspecialchars($course['title'] ?? ''); ?>
                </p>
            </div>
            <div class="page-header-meta">
                <span>
                    <ion-icon name="layers-outline"></ion-icon>
                    شناسه دوره: <?= (int) ($course['id'] ?? 0); ?>
                </span>
                <span>
                    <ion-icon name="person-outline"></ion-icon>
                    مسئول: <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                </span>
                <?php if (!empty($course['status'])): ?>
                    <span>
                        <ion-icon name="information-circle-outline"></ion-icon>
                        وضعیت دوره: <?= htmlspecialchars($course['status']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="lesson-form-card">
            <form id="lessonForm" action="<?= $formAction; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0); ?>">
                <?php if ($isEdit && $lesson): ?>
                    <input type="hidden" name="lesson_id" value="<?= (int) $lesson['id']; ?>">
                <?php endif; ?>
                <input type="hidden" name="remove_thumbnail" id="removeThumbnail" value="0">

                <div class="form-section">
                    <div class="form-section-title">
                        <ion-icon name="create-outline"></ion-icon>
                        اطلاعات پایه درس
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="lessonTitle" class="form-label"><span class="required-mark">*</span>عنوان درس</label>
                            <input type="text" class="form-control" id="lessonTitle" name="title" required value="<?= htmlspecialchars($lesson['title'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="durationMinutes" class="form-label">مدت زمان (دقیقه)</label>
                            <input type="number" min="0" class="form-control" id="durationMinutes" name="duration_minutes" value="<?= htmlspecialchars((string) ($lesson['duration_minutes'] ?? '')); ?>">
                            <div class="form-hint">مثال: 45</div>
                        </div>
                        <div class="col-md-3">
                            <label for="contentType" class="form-label"><span class="required-mark">*</span>نوع محتوا</label>
                            <select class="form-select" id="contentType" name="content_type" required>
                                <?php
                                $types = [
                                    'video' => 'ویدیو',
                                    'pdf' => 'فایل PDF',
                                    'ppt' => 'فایل ارائه',
                                    'link' => 'لینک خارجی',
                                    'text' => 'محتوای متنی'
                                ];
                                foreach ($types as $value => $label):
                                ?>
                                    <option value="<?= $value; ?>" <?= $selectedContentType === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="shortDescription" class="form-label">توضیح کوتاه</label>
                            <textarea class="form-control textarea-small" id="shortDescription" name="short_description" placeholder="خلاصه‌ای از درس (نمایش در کارت درس)"><?= htmlspecialchars($lesson['short_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="lessonDescription" class="form-label">توضیحات کامل</label>
                            <textarea class="form-control textarea-large" id="lessonDescription" name="description" placeholder="جزئیات کامل درس، پیش‌نیازها و نکات مهم را وارد کنید."><?= htmlspecialchars($lesson['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <ion-icon name="albums-outline"></ion-icon>
                        محتوای آموزشی
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6" id="fileField" style="<?= in_array($selectedContentType, ['video', 'pdf', 'ppt']) ? '' : 'display:none;'; ?>">
                            <label for="contentFile" class="form-label">فایل محتوا</label>
                            <input type="file" class="form-control" id="contentFile" name="content_file" <?= (!$isEdit && !$existingContentFileUrl && in_array($selectedContentType, ['video', 'pdf', 'ppt'])) ? 'required' : ''; ?>>
                            <div class="form-hint">فرمت‌های مجاز بر اساس نوع محتوا: ویدیو، PDF یا ارائه</div>
                            <div class="form-hint" style="margin-top:6px; color:#0f172a;">
                                حداکثر حجم مجاز: <?= htmlspecialchars($uploadMaxFilesize); ?> (محدودیت فرم: <?= htmlspecialchars($postMaxSize); ?>)
                            </div>
                            <?php if ($existingContentFileUrl): ?>
                                <div class="form-hint" style="margin-top: 10px;">
                                    <a href="<?= $existingContentFileUrl; ?>" target="_blank" class="metadata-pill">
                                        <ion-icon name="download-outline"></ion-icon>
                                        مشاهده فایل فعلی
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6" id="linkField" style="<?= $selectedContentType === 'link' ? '' : 'display:none;'; ?>">
                            <label for="contentUrl" class="form-label">لینک محتوا</label>
                            <input type="url" class="form-control" id="contentUrl" name="content_url" value="<?= htmlspecialchars($lesson['content_url'] ?? ''); ?>" placeholder="https://example.com/lesson" <?= $selectedContentType === 'link' ? 'required' : ''; ?>>
                            <div class="form-hint">برای درس‌های لینک یا ویدیوهای پلتفرم‌های خارجی</div>
                        </div>
                        <div class="col-12" id="textContentField" style="<?= $selectedContentType === 'text' ? '' : 'display:none;'; ?>">
                            <label for="textContent" class="form-label">محتوای متنی</label>
                            <textarea class="form-control textarea-large" id="textContent" name="text_content" placeholder="متن کامل درس را اینجا وارد کنید." <?= $selectedContentType === 'text' ? 'required' : ''; ?>><?= htmlspecialchars($lesson['text_content'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="learningObjectives" class="form-label">اهداف یادگیری</label>
                            <textarea class="form-control textarea-small" id="learningObjectives" name="learning_objectives" placeholder="اهداف هر درس را در خطوط جداگانه بنویسید."><?= htmlspecialchars($lesson['learning_objectives'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="resources" class="form-label">منابع تکمیلی</label>
                            <textarea class="form-control textarea-small" id="resources" name="resources" placeholder="لینک‌ها یا توضیحات مربوط به منابع تکمیلی را وارد کنید."><?= htmlspecialchars($lesson['resources'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <ion-icon name="rocket-outline"></ion-icon>
                        انتشار و دسترسی
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">وضعیت انتشار</label>
                            <div class="toggle-wrapper">
                                <input class="form-check-input" type="checkbox" id="isPublished" name="is_published" value="1" <?= $lesson ? (!empty($lesson['is_published']) ? 'checked' : '') : 'checked'; ?>>
                                <label for="isPublished">انتشار برای دانشجویان</label>
                            </div>
                            <div class="form-hint">در صورت خاموش بودن، درس به حالت پیش‌نویس ذخیره می‌شود.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">دسترسی رایگان</label>
                            <div class="toggle-wrapper">
                                <input class="form-check-input" type="checkbox" id="isFree" name="is_free" value="1" <?= !empty($lesson['is_free']) ? 'checked' : ''; ?>>
                                <label for="isFree">این درس رایگان است</label>
                            </div>
                            <div class="form-hint">در صورت انتخاب، درس بدون ثبت‌نام قابل مشاهده خواهد بود.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="availableAt" class="form-label">زمان‌بندی انتشار</label>
                            <input type="datetime-local" class="form-control" id="availableAt" name="available_at" value="<?= htmlspecialchars($availableAtValue); ?>">
                            <div class="form-hint">در صورت خالی بودن، درس بلافاصله پس از انتشار فعال می‌شود.</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <ion-icon name="image-outline"></ion-icon>
                        تصویر جلد درس
                    </div>

                    <div class="row g-4 align-items-center">
                        <div class="col-md-4">
                            <div id="thumbnailPreview" class="thumbnail-preview <?= $existingThumbnailUrl ? 'has-image' : ''; ?>">
                                <?php if ($existingThumbnailUrl): ?>
                                    <img src="<?= $existingThumbnailUrl; ?>" alt="Course thumbnail">
                                <?php else: ?>
                                    <div style="text-align: center; color: #94a3b8;">
                                        <ion-icon name="image-outline" style="font-size: 48px; display: block; margin: 0 auto 12px;"></ion-icon>
                                        <div>تصویر انتخاب نشده است</div>
                                    </div>
                                <?php endif; ?>
                                <button type="button" class="thumbnail-remove-btn" id="removeThumbnailBtn" title="حذف تصویر">
                                    <ion-icon name="close-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label for="thumbnail" class="form-label">انتخاب تصویر جدید</label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                            <div class="form-hint">فرمت‌های پیشنهادی: JPG یا PNG با نسبت 16:9 و حجم حداکثر 2 مگابایت</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= $backUrl; ?>" class="btn btn-secondary">انصراف</a>
                    <button type="submit" class="btn btn-primary" id="submitLessonBtn">
                        <?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد درس'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const contentTypeSelect = document.getElementById('contentType');
const fileField = document.getElementById('fileField');
const linkField = document.getElementById('linkField');
const textContentField = document.getElementById('textContentField');
const contentFileInput = document.getElementById('contentFile');
const contentUrlInput = document.getElementById('contentUrl');
const textContentInput = document.getElementById('textContent');
const thumbnailInput = document.getElementById('thumbnail');
const thumbnailPreview = document.getElementById('thumbnailPreview');
const removeThumbnailBtn = document.getElementById('removeThumbnailBtn');
const removeThumbnailInput = document.getElementById('removeThumbnail');
const submitLessonBtn = document.getElementById('submitLessonBtn');
const lessonForm = document.getElementById('lessonForm');
const isEditLesson = <?= $isEdit ? 'true' : 'false'; ?>;
const hasExistingContentFile = <?= $existingContentFileUrl ? 'true' : 'false'; ?>;

function updateContentFields() {
    const type = contentTypeSelect.value;

    if (['video', 'pdf', 'ppt'].includes(type)) {
        fileField.style.display = '';
        contentFileInput.required = !isEditLesson && !hasExistingContentFile;
    } else {
        fileField.style.display = 'none';
        contentFileInput.value = '';
        contentFileInput.required = false;
    }

    if (type === 'link') {
        linkField.style.display = '';
        contentUrlInput.required = true;
    } else {
        linkField.style.display = 'none';
        contentUrlInput.required = false;
    }

    if (type === 'text') {
        textContentField.style.display = '';
        textContentInput.required = true;
    } else {
        textContentField.style.display = 'none';
        textContentInput.required = false;
    }
}

function resetThumbnailPreview() {
    thumbnailPreview.classList.remove('has-image');
    thumbnailPreview.innerHTML = '<div style="text-align: center; color: #94a3b8;">' +
        '<ion-icon name="image-outline" style="font-size: 48px; display: block; margin: 0 auto 12px;"></ion-icon>' +
        '<div>تصویر انتخاب نشده است</div>' +
        '</div>' +
        '<button type="button" class="thumbnail-remove-btn" id="removeThumbnailBtn" title="حذف تصویر">' +
        '<ion-icon name="close-outline"></ion-icon>' +
        '</button>';
    const newRemoveBtn = thumbnailPreview.querySelector('#removeThumbnailBtn');
    newRemoveBtn.addEventListener('click', handleRemoveThumbnail);
}

function handleRemoveThumbnail() {
    thumbnailInput.value = '';
    removeThumbnailInput.value = '1';
    resetThumbnailPreview();
}

contentTypeSelect.addEventListener('change', updateContentFields);
updateContentFields();

if (thumbnailInput) {
    thumbnailInput.addEventListener('change', function () {
        if (thumbnailInput.files && thumbnailInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                thumbnailPreview.classList.add('has-image');
                thumbnailPreview.innerHTML = '<img src="' + e.target.result + '" alt="Lesson thumbnail preview">' +
                    '<button type="button" class="thumbnail-remove-btn" id="removeThumbnailBtn" title="حذف تصویر">' +
                    '<ion-icon name="close-outline"></ion-icon>' +
                    '</button>';
                const newRemoveBtn = thumbnailPreview.querySelector('#removeThumbnailBtn');
                newRemoveBtn.addEventListener('click', handleRemoveThumbnail);
            };
            reader.readAsDataURL(thumbnailInput.files[0]);
            removeThumbnailInput.value = '0';
        }
    });
}

if (removeThumbnailBtn) {
    removeThumbnailBtn.addEventListener('click', handleRemoveThumbnail);
}

lessonForm.addEventListener('submit', function () {
    submitLessonBtn.disabled = true;
    submitLessonBtn.innerText = 'در حال ذخیره...';
});
</script>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
