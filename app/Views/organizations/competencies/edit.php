<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ویرایش شایستگی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

AuthHelper::startSession();

$validationErrors = $validationErrors ?? [];
$errorMessage = $errorMessage ?? flash('error');
$successMessage = $successMessage ?? flash('success');
$competency = $competency ?? [];
$competencyDimensions = $competencyDimensions ?? [];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competency-form label,
    .competency-form small {
        text-align: right;
        display: block;
    }
    .competency-form .form-control {
        text-align: right;
        direction: rtl;
    }
    .competency-form .ltr-input {
        direction: ltr;
        text-align: left;
    }
    .competency-form textarea {
        min-height: 140px;
        resize: vertical;
    }
CSS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div class="text-start flex-grow-1">
                                <h2 class="mb-6 text-gray-900">ویرایش شایستگی</h2>
                                <p class="text-gray-500 mb-0">اطلاعات شایستگی انتخابی را به‌روزرسانی کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competencies'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به لیست شایستگی‌ها</span>
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

                        <form action="<?= UtilityHelper::baseUrl('organizations/competencies/update'); ?>" method="post" class="competency-form text-start">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($competency['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row g-16">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">بعد شایستگی <span class="text-danger">*</span></label>
                                    <select name="competency_dimension_id" class="form-select">
                                        <option value="">یکی از ابعاد را انتخاب کنید</option>
                                        <?php foreach ($competencyDimensions as $dimension): ?>
                                            <?php
                                                $dimensionId = (int) ($dimension['id'] ?? 0);
                                                $selectedValue = (int) old('competency_dimension_id', $competency['competency_dimension_id'] ?? 0);
                                                $selected = $dimensionId === $selectedValue ? 'selected' : '';
                                            ?>
                                            <option value="<?= htmlspecialchars((string) $dimensionId, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                                <?= htmlspecialchars($dimension['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($validationErrors['competency_dimension_id'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['competency_dimension_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">کد شایستگی <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control ltr-input" value="<?= htmlspecialchars(old('code', $competency['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: COMP-01" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">عنوان شایستگی <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars(old('title', $competency['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: رهبری" required>
                                    <?php if (!empty($validationErrors['title'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['title'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">تعریف شایستگی <span class="text-danger">*</span></label>
                                    <textarea name="definition" class="form-control" placeholder="تعریف دقیق شایستگی را وارد کنید" required><?= htmlspecialchars(old('definition', $competency['definition'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <?php if (!empty($validationErrors['definition'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['definition'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ذخیره تغییرات</span>
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/competencies'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
