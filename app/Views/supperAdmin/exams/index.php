<?php
$title = 'مدیریت آزمون‌ها';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

AuthHelper::startSession();

$successMessage = $successMessage ?? flash('success');
$errorMessage = $errorMessage ?? flash('error');
$warningMessage = $warningMessage ?? flash('warning');
$infoMessage = $infoMessage ?? flash('info');
$examTypesMeta = $examTypesMeta ?? [];

$formatDateTime = function ($dateTime) {
    if (!$dateTime) {
        return '-';
    }

    $timestamp = strtotime($dateTime);
    if (!$timestamp) {
        return UtilityHelper::englishToPersian($dateTime);
    }

    $formatted = date('H:i Y/m/d', $timestamp);
    return UtilityHelper::englishToPersian($formatted);
};

$translateStatus = function ($status) {
    $map = [
        'draft' => ['label' => 'پیش‌نویس', 'class' => 'bg-gray-100 text-gray-600'],
        'scheduled' => ['label' => 'زمان‌بندی شده', 'class' => 'bg-info-50 text-info-600'],
        'published' => ['label' => 'منتشر شده', 'class' => 'bg-success-50 text-success-600'],
        'archived' => ['label' => 'بایگانی شده', 'class' => 'bg-warning-50 text-warning-600'],
    ];

    $fallback = ['label' => UtilityHelper::englishToPersian($status ?? 'نامشخص'), 'class' => 'bg-gray-100 text-gray-600'];

    return $map[$status] ?? $fallback;
};

$truncateText = function ($text, $limit = 120) {
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }

    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }

    return mb_substr($text, 0, $limit, 'UTF-8') . '…';
};

$exams = $exams ?? [];
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body pb-0 text-end">
                        <div class="mb-16">
                            <h3 class="mb-4">مدیریت آزمون‌ها</h3>
                            <p class="text-gray-500 mb-0">در این بخش می‌توانید فهرست آزمون‌های سیستم را مشاهده کنید.</p>
                        </div>
                        <div class="d-flex justify-content-between flex-wrap gap-12 mb-16">
                            <div class="d-flex flex-wrap gap-8">
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/exams/create'); ?>" class="btn btn-main rounded-pill px-20">
                                    <i class="fas fa-plus ms-6"></i>
                                    ثبت آزمون جدید
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-8 text-gray-500">
                                <span class="badge bg-main-50 text-main-600 rounded-pill py-8 px-16">
                                    مجموع آزمون‌ها: <?= UtilityHelper::englishToPersian(count($exams)); ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-12 text-end" role="alert">
                                <i class="fas fa-check-circle ms-6"></i>
                                <?= $successMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($warningMessage)): ?>
                            <div class="alert alert-warning rounded-12 text-end" role="alert">
                                <i class="fas fa-info-circle ms-6"></i>
                                <?= $warningMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($infoMessage)): ?>
                            <div class="alert alert-info rounded-12 text-end" role="alert">
                                <i class="fas fa-lightbulb ms-6"></i>
                                <?= $infoMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-12 text-end" role="alert">
                                <i class="fas fa-exclamation-triangle ms-6"></i>
                                <?= $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <?php if (empty($exams)): ?>
                            <div class="p-40 text-center text-gray-500">
                                <i class="fas fa-file-alt mb-16 text-3xl d-block"></i>
                                هنوز هیچ آزمونی ثبت نشده است.
                                <div class="mt-12">
                                    <a href="<?= UtilityHelper::baseUrl('supperadmin/exams/create'); ?>" class="btn btn-main rounded-pill">
                                        <i class="fas fa-plus ms-6"></i>
                                        افزودن اولین آزمون
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-end mb-0">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th scope="col" class="text-end">عنوان آزمون</th>
                                            <th scope="col" class="text-end">نوع آزمون</th>
                                            <th scope="col" class="text-end">وضعیت</th>
                                            <th scope="col" class="text-end">تاریخ شروع</th>
                                            <th scope="col" class="text-end">تاریخ پایان</th>
                                            <th scope="col" class="text-end">آخرین بروزرسانی</th>
                                            <th scope="col" class="text-center">اقدامات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($exams as $exam): ?>
                                            <?php $statusMeta = $translateStatus($exam['status'] ?? 'draft'); ?>
                                            <tr>
                                                <td>
                                                    <div class="text-end">
                                                        <strong class="d-block text-gray-900"><?= htmlspecialchars($exam['title'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <?php if (!empty($exam['description'])): ?>
                                                            <small class="text-gray-500"><?= nl2br(htmlspecialchars($truncateText($exam['description'], 120), ENT_QUOTES, 'UTF-8')); ?></small>
                                                        <?php endif; ?>
                                                        <?php if (isset($exam['passing_score']) && $exam['passing_score'] !== null): ?>
                                                            <div class="mt-6">
                                                                <span class="badge bg-gray-100 text-gray-600 rounded-pill">
                                                                    نمره قبولی: <?= UtilityHelper::englishToPersian((string) $exam['passing_score']); ?>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill bg-main-50 text-main-600">
                                                        <?= htmlspecialchars($exam['type_label'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill <?= $statusMeta['class']; ?>">
                                                        <?= $statusMeta['label']; ?>
                                                    </span>
                                                </td>
                                                <td><?= $formatDateTime($exam['start_at'] ?? null); ?></td>
                                                <td><?= $formatDateTime($exam['end_at'] ?? null); ?></td>
                                                <td><?= $formatDateTime($exam['updated_at'] ?? $exam['created_at'] ?? null); ?></td>
                                                <td class="text-center">
                                                    <div class="d-inline-flex flex-wrap justify-content-center gap-8">
                                                        <?php
                                                            $examId = (int) ($exam['id'] ?? 0);
                                                            $editUrl = UtilityHelper::baseUrl('supperadmin/exams/edit') . '?id=' . $examId;
                                                            $deleteUrl = UtilityHelper::baseUrl('supperadmin/exams/delete');
                                                            $questionsUrl = UtilityHelper::baseUrl('supperadmin/exams/questions') . '?id=' . $examId;
                                                        ?>
                                                            <a href="<?= $questionsUrl; ?>" class="btn btn-sm btn-outline-main rounded-pill px-16">
                                                                <i class="fas fa-tasks ms-4"></i>
                                                                سوالات آزمون
                                                            </a>
                                                        <a href="<?= $editUrl; ?>" class="btn btn-sm btn-primary rounded-pill px-16">
                                                            <i class="fas fa-edit ms-4"></i>
                                                            ویرایش
                                                        </a>
                                                        <form action="<?= $deleteUrl; ?>" method="post" class="d-inline" onsubmit="return confirm('آیا از حذف این آزمون اطمینان دارید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="exam_id" value="<?= $examId; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-16">
                                                                <i class="fas fa-trash ms-4"></i>
                                                                حذف
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>
