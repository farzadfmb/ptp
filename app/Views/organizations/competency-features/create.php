<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ایجاد ویژگی شایستگی';
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
$competencies = $competencies ?? [];
$availableFeatureTypes = $availableFeatureTypes ?? [];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competency-feature-form label,
    .competency-feature-form small {
        text-align: right;
        display: block;
    }
    .competency-feature-form .form-control,
    .competency-feature-form .form-select {
        text-align: right;
        direction: rtl;
    }
    .competency-feature-form .ltr-input {
        direction: ltr;
        text-align: left;
    }
    .competency-feature-form textarea {
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
                                <h2 class="mb-6 text-gray-900">ایجاد ویژگی شایستگی</h2>
                                <p class="text-gray-500 mb-0">اطلاعات ویژگی جدید را وارد کرده و آن را به شایستگی موردنظر نسبت دهید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-features'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به لیست ویژگی‌های شایستگی</span>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/competency-features'); ?>" method="post" class="competency-feature-form text-start">
                            <?= csrf_field(); ?>
                            <div class="row g-16">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">کد ویژگی <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control ltr-input" value="<?= htmlspecialchars(old('code', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: CF-01" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">نوع ویژگی <span class="text-danger">*</span></label>
                                    <input type="text" name="type" class="form-control" list="competencyFeatureTypes" value="<?= htmlspecialchars(old('type', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: رفتاری" required>
                                    <datalist id="competencyFeatureTypes">
                                        <?php foreach ($availableFeatureTypes as $typeOption): ?>
                                            <option value="<?= htmlspecialchars($typeOption, ENT_QUOTES, 'UTF-8'); ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <small class="text-gray-500 mt-6">می‌توانید نوع جدید وارد کنید یا از موارد پیشنهادی انتخاب کنید.</small>
                                    <?php if (!empty($validationErrors['type'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['type'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">شایستگی مرتبط <span class="text-danger">*</span></label>
                                    <select name="competency_id" class="form-select" required>
                                        <option value="">انتخاب شایستگی</option>
                                        <?php foreach ($competencies as $competency): ?>
                                            <?php
                                                $competencyId = (int) ($competency['id'] ?? 0);
                                                $competencyCode = trim((string) ($competency['code'] ?? ''));
                                                $competencyTitle = trim((string) ($competency['title'] ?? ''));
                                                $optionLabel = $competencyCode !== ''
                                                    ? ($competencyTitle !== '' ? $competencyCode . ' - ' . $competencyTitle : $competencyCode)
                                                    : ($competencyTitle !== '' ? $competencyTitle : 'شناسه ' . $competencyId);
                                                $selected = old('competency_id', '') === (string) $competencyId ? 'selected' : '';
                                            ?>
                                            <option value="<?= htmlspecialchars((string) $competencyId, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                                <?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (empty($competencies)): ?>
                                        <small class="text-gray-500 mt-6">برای ایجاد ویژگی ابتدا باید شایستگی‌های سازمان را تعریف کنید.</small>
                                    <?php endif; ?>
                                    <?php if (!empty($validationErrors['competency_id'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['competency_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">توضیحات</label>
                                    <textarea name="description" class="form-control" placeholder="توضیحات تکمیلی درباره ویژگی شایستگی"><?= htmlspecialchars(old('description', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <?php if (!empty($validationErrors['description'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['description'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ثبت ویژگی</span>
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-features'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
