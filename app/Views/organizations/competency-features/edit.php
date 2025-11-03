<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ویرایش ویژگی شایستگی';
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
$competencies = $competencies ?? [];
$availableFeatureTypes = $availableFeatureTypes ?? [];
$competencyFeature = $competencyFeature ?? [];
$competencyFeatureScore = $competencyFeatureScore ?? [];

$formatScoreInput = static function ($value) {
    if ($value === null || $value === '') {
        return '';
    }

    if (!is_numeric($value)) {
        $normalized = str_replace(['٫', ',', '،'], ['.', '.', '.'], UtilityHelper::persianToEnglish((string) $value));
        if (!is_numeric($normalized)) {
            return trim((string) $value);
        }
        $value = $normalized;
    }

    $number = (float) $value;
    if (!is_finite($number)) {
        return '';
    }

    if (floor($number) === $number) {
        return (string) (int) $number;
    }

    return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
};

$scoreMinPrefill = $formatScoreInput($competencyFeatureScore['score_min'] ?? '');
$scoreMaxPrefill = $formatScoreInput($competencyFeatureScore['score_max'] ?? '');

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
                                <h2 class="mb-6 text-gray-900">ویرایش ویژگی شایستگی</h2>
                                <p class="text-gray-500 mb-0">اطلاعات ویژگی انتخابی را به‌روزرسانی کنید و آن را به شایستگی مناسب اختصاص دهید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-features'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به لیست ویژگی‌های شایستگی</span>
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

                        <form action="<?= UtilityHelper::baseUrl('organizations/competency-features/update'); ?>" method="post" class="competency-feature-form text-start">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($competencyFeature['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="row g-16">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">کد ویژگی <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control ltr-input" value="<?= htmlspecialchars(old('code', $competencyFeature['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: CF-01" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">سطح انتظار <span class="text-danger">*</span></label>
                                    <input type="text" name="type" class="form-control" list="competencyFeatureTypes" value="<?= htmlspecialchars(old('type', $competencyFeature['type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: سطح حرفه‌ای" required>
                                    <datalist id="competencyFeatureTypes">
                                        <?php foreach ($availableFeatureTypes as $typeOption): ?>
                                            <option value="<?= htmlspecialchars($typeOption, ENT_QUOTES, 'UTF-8'); ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <small class="text-gray-500 mt-6">برای استفاده مجدد، سطوح ثبت‌شده قبلی در فهرست پیشنهادها موجود است.</small>
                                    <?php if (!empty($validationErrors['type'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['type'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">شایستگی مرتبط <span class="text-danger">*</span></label>
                                    <select name="competency_id" class="form-select" required>
                                        <option value="">انتخاب شایستگی</option>
                                        <?php $selectedCompetencyId = (string) old('competency_id', (string) ($competencyFeature['competency_id'] ?? '')); ?>
                                        <?php foreach ($competencies as $competency): ?>
                                            <?php
                                                $competencyId = (int) ($competency['id'] ?? 0);
                                                $competencyCode = trim((string) ($competency['code'] ?? ''));
                                                $competencyTitle = trim((string) ($competency['title'] ?? ''));
                                                $optionLabel = $competencyCode !== ''
                                                    ? ($competencyTitle !== '' ? $competencyCode . ' - ' . $competencyTitle : $competencyCode)
                                                    : ($competencyTitle !== '' ? $competencyTitle : 'شناسه ' . $competencyId);
                                                $selected = $selectedCompetencyId === (string) $competencyId ? 'selected' : '';
                                            ?>
                                            <option value="<?= htmlspecialchars((string) $competencyId, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                                <?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (empty($competencies)): ?>
                                        <small class="text-gray-500 mt-6">برای ویرایش ویژگی ابتدا باید شایستگی‌های سازمان را تعریف کنید.</small>
                                    <?php endif; ?>
                                    <?php if (!empty($validationErrors['competency_id'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['competency_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">امتیاز شروع <span class="text-danger">*</span></label>
                                    <input type="text" name="score_min" class="form-control ltr-input" inputmode="decimal" value="<?= htmlspecialchars(old('score_min', $scoreMinPrefill), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: 1" required>
                                    <small class="text-gray-500 mt-6">حداقل امتیاز مورد انتظار برای این ویژگی را تعیین کنید.</small>
                                    <?php if (!empty($validationErrors['score_min'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['score_min'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">امتیاز پایان <span class="text-danger">*</span></label>
                                    <input type="text" name="score_max" class="form-control ltr-input" inputmode="decimal" value="<?= htmlspecialchars(old('score_max', $scoreMaxPrefill), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: 10" required>
                                    <small class="text-gray-500 mt-6">حداکثر امتیاز مورد انتظار برای این ویژگی را مشخص کنید.</small>
                                    <?php if (!empty($validationErrors['score_max'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['score_max'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">توضیحات</label>
                                    <textarea name="description" class="form-control" placeholder="توضیحات تکمیلی درباره ویژگی شایستگی"><?= htmlspecialchars(old('description', $competencyFeature['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <?php if (!empty($validationErrors['description'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['description'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ذخیره تغییرات</span>
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
