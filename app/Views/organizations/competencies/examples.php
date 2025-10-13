<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'مصداق‌های رفتاری شایستگی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

AuthHelper::startSession();

$competency = $competency ?? [];
$behaviorExamples = $behaviorExamples ?? [];
$validationErrors = $_SESSION['validation_errors'] ?? [];
unset($_SESSION['validation_errors']);
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
$successMessage = $successMessage ?? flash('success');
$errorMessage = $errorMessage ?? flash('error');

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .behavior-example-form label,
    .behavior-example-form small {
        text-align: right;
        display: block;
    }
    .behavior-example-form .form-control {
        text-align: right;
        direction: rtl;
    }
    .behavior-example-form textarea {
        min-height: 100px;
        resize: vertical;
    }
    .behavior-examples-table tbody tr td {
        vertical-align: middle;
    }
    .behavior-examples-table .table-actions button {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
CSS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<?php
    $competencyTitle = trim((string) ($competency['title'] ?? ''));
    $competencyCode = trim((string) ($competency['code'] ?? ''));
    $dimensionName = trim((string) ($competency['dimension_name'] ?? ''));
    $competencyId = (int) ($competency['id'] ?? 0);
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div class="flex-grow-1 text-start">
                                <h2 class="mb-6 text-gray-900">مصداق‌های رفتاری شایستگی</h2>
                                <p class="text-gray-500 mb-2">
                                    <span class="fw-semibold">شایستگی:</span>
                                    <?= htmlspecialchars($competencyTitle !== '' ? $competencyTitle : 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ($competencyCode !== ''): ?>
                                        <span class="badge bg-main-soft text-main fw-semibold ms-2">کد: <?= htmlspecialchars($competencyCode, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </p>
                                <?php if ($dimensionName !== ''): ?>
                                    <p class="text-gray-500 mb-0"><span class="fw-semibold">بعد شایستگی:</span> <?= htmlspecialchars($dimensionName, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competencies'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت به شایستگی‌ها">
                                    بازگشت
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به فهرست شایستگی‌ها</span>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/competencies/examples'); ?>" method="post" class="behavior-example-form text-start mb-24">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="competency_id" value="<?= htmlspecialchars((string) $competencyId, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row g-16 align-items-end">
                                <div class="col-lg-9 col-md-8">
                                    <label class="form-label fw-semibold">مصداق رفتاری <span class="text-danger">*</span></label>
                                    <textarea name="behavior_example" class="form-control" placeholder="مثال رفتاری مرتبط با این شایستگی را وارد کنید" required><?= htmlspecialchars($oldInput['behavior_example'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <?php if (!empty($validationErrors['behavior_example'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['behavior_example'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-lg-3 col-md-4 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8 w-100 justify-content-center mt-3 mt-md-0">
                                        <ion-icon name="add-circle-outline"></ion-icon>
                                        <span>افزودن مصداق</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive rounded-16 border border-gray-100" style="direction: rtl;">
                            <table class="table align-middle mb-0 behavior-examples-table">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search nowrap" style="width: 120px;">عملیات</th>
                                        <th scope="col">مصداق رفتاری</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($behaviorExamples)): ?>
                                        <?php foreach ($behaviorExamples as $example): ?>
                                            <tr>
                                                <td>
                                                    <div class="table-actions">
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/competencies/examples/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این مصداق رفتاری اطمینان دارید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($example['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="competency_id" value="<?= htmlspecialchars((string) $competencyId, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($example['behavior_example'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center py-28 text-gray-500">
                                                هنوز مصداقی برای این شایستگی ثبت نشده است. برای افزودن اولین مورد از فرم بالا استفاده کنید.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
