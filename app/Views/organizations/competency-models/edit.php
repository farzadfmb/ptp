<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ویرایش مدل شایستگی';
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
$reportSettings = $reportSettings ?? [];
$competencies = $competencies ?? [];
$scoringTypeOptions = $scoringTypeOptions ?? [];
$reportLevelOptions = $reportLevelOptions ?? [];
$preselectedCompetencyIds = $preselectedCompetencyIds ?? [];
$competencyModel = $competencyModel ?? [];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competency-model-form label,
    .competency-model-form small {
        text-align: right;
        display: block;
    }
    .competency-model-form .form-control,
    .competency-model-form .form-select {
        text-align: right;
        direction: rtl;
    }
    .competency-model-form .ltr-input {
        direction: ltr;
        text-align: left;
    }
    .competency-model-form textarea {
        min-height: 140px;
        resize: vertical;
    }
    .competencies-selection-table tbody tr td {
        vertical-align: middle;
    }
    .competency-toggle-btn.selected {
        background: var(--bs-main-500);
        border-color: var(--bs-main-500);
        color: #fff;
    }
    .current-image-preview {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-top: 8px;
    }
    .current-image-preview img {
        width: 68px;
        height: 68px;
        border-radius: 18px;
        object-fit: cover;
        border: 1px solid rgba(15, 23, 42, 0.12);
    }
CSS;

$inline_scripts .= <<<'JS'
    document.addEventListener('DOMContentLoaded', function () {
        const selectedContainer = document.getElementById('selectedCompetenciesContainer');
        const toggleButtons = document.querySelectorAll('[data-competency-id]');
        const preselected = Array.isArray(window.preselectedCompetencyIds) ? window.preselectedCompetencyIds : [];
        const selectedSet = new Set(preselected.map(function (value) { return parseInt(value, 10); }).filter(function (id) { return id > 0; }));

        const syncHiddenInputs = function () {
            if (!selectedContainer) {
                return;
            }
            selectedContainer.innerHTML = '';
            Array.from(selectedSet).forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_competencies[]';
                input.value = id.toString();
                selectedContainer.appendChild(input);
            });
        };

        const reflectButtonState = function (button, isSelected) {
            if (!button) {
                return;
            }
            if (isSelected) {
                button.classList.add('selected');
                button.textContent = 'حذف از مدل';
            } else {
                button.classList.remove('selected');
                button.textContent = 'افزودن';
            }
        };

        toggleButtons.forEach(function (button) {
            const competencyId = parseInt(button.getAttribute('data-competency-id'), 10);
            if (!Number.isInteger(competencyId) || competencyId <= 0) {
                return;
            }

            reflectButtonState(button, selectedSet.has(competencyId));

            button.addEventListener('click', function () {
                if (selectedSet.has(competencyId)) {
                    selectedSet.delete(competencyId);
                } else {
                    selectedSet.add(competencyId);
                }
                reflectButtonState(button, selectedSet.has(competencyId));
                syncHiddenInputs();
            });
        });

        syncHiddenInputs();
    });
JS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<script>
    window.preselectedCompetencyIds = <?= json_encode(array_values($preselectedCompetencyIds), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div class="text-start flex-grow-1">
                                <h2 class="mb-6 text-gray-900">ویرایش مدل شایستگی</h2>
                                <p class="text-gray-500 mb-0">اطلاعات مدل انتخابی را به‌روزرسانی کنید و شایستگی‌های آن را مدیریت نمایید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-models'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به لیست مدل‌ها</span>
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

                        <form action="<?= UtilityHelper::baseUrl('organizations/competency-models/update'); ?>" method="post" class="competency-model-form text-start" enctype="multipart/form-data">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($competencyModel['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="row g-16">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">کد مدل شایستگی <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control ltr-input" value="<?= htmlspecialchars(old('code', $competencyModel['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: CM-01" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">عنوان مدل شایستگی <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars(old('title', $competencyModel['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدل ارزیابی مدیران" required>
                                    <?php if (!empty($validationErrors['title'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['title'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">نوع امتیازدهی <span class="text-danger">*</span></label>
                                    <select name="scoring_type" class="form-select" required>
                                        <option value="">یکی از گزینه‌ها را انتخاب کنید</option>
                                        <?php foreach ($scoringTypeOptions as $value => $label): ?>
                                            <?php $selected = old('scoring_type', $competencyModel['scoring_type'] ?? '') === (string) $value ? 'selected' : ''; ?>
                                            <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($validationErrors['scoring_type'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['scoring_type'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">گزارش <span class="text-danger">*</span></label>
                                    <select name="report_level" class="form-select" required>
                                        <option value="">گزارش موردنظر را انتخاب کنید</option>
                                        <?php foreach ($reportLevelOptions as $value => $label): ?>
                                            <?php $selected = old('report_level', $competencyModel['report_level'] ?? '') === (string) $value ? 'selected' : ''; ?>
                                            <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($validationErrors['report_level'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['report_level'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">تنظیمات گزارش</label>
                                    <select name="report_setting_id" class="form-select">
                                        <option value="">(اختیاری) انتخاب از تنظیمات گزارش</option>
                                        <?php foreach ($reportSettings as $setting): ?>
                                            <?php
                                                $settingId = (int) ($setting['id'] ?? 0);
                                                $selected = old('report_setting_id', $competencyModel['report_setting_id'] ?? '') === (string) $settingId ? 'selected' : '';
                                            ?>
                                            <option value="<?= htmlspecialchars((string) $settingId, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                                <?= htmlspecialchars($setting['title'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (empty($reportSettings)): ?>
                                        <small class="text-gray-500 mt-6">برای افزودن تنظیم گزارش جدید می‌توانید به بخش «تنظیمات گزارشات» مراجعه کنید.</small>
                                    <?php endif; ?>
                                    <?php if (!empty($validationErrors['report_setting_id'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['report_setting_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">آپلود عکس مدل</label>
                                    <input type="file" name="model_image" class="form-control" accept="image/*">
                                    <small class="text-gray-500 mt-6">در صورت انتخاب تصویر جدید، تصویر قبلی جایگزین خواهد شد. (فرمت‌های مجاز: JPG, PNG, GIF, WEBP)</small>
                                    <?php if (!empty($competencyModel['image_path'])): ?>
                                        <div class="current-image-preview">
                                            <img src="<?= htmlspecialchars(UtilityHelper::baseUrl('public/' . ltrim((string) $competencyModel['image_path'], '/')), ENT_QUOTES, 'UTF-8'); ?>" alt="تصویر فعلی مدل">
                                            <span class="text-gray-500">تصویر فعلی مدل</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($validationErrors['model_image'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['model_image'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr class="my-28">

                            <h4 class="text-gray-900 mb-16">مدیریت شایستگی‌های مدل</h4>
                            <p class="text-gray-500 mb-16">برای افزودن یا حذف شایستگی‌ها از جدول زیر استفاده کنید. تغییرات پس از ذخیره اعمال خواهند شد.</p>

                            <div class="table-responsive rounded-16 border border-gray-100 mb-20">
                                <table class="table align-middle mb-0 competencies-selection-table">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th scope="col" style="width: 120px;" class="no-sort no-search">عملیات</th>
                                            <th scope="col">کد شایستگی</th>
                                            <th scope="col">بعد شایستگی</th>
                                            <th scope="col">عنوان شایستگی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($competencies)): ?>
                                            <?php foreach ($competencies as $competency): ?>
                                                <?php
                                                    $competencyId = (int) ($competency['id'] ?? 0);
                                                    $code = trim((string) ($competency['code'] ?? '-'));
                                                    $titleText = trim((string) ($competency['title'] ?? '-'));
                                                    $dimensionName = trim((string) ($competency['dimension_name'] ?? '—'));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-main competency-toggle-btn" data-competency-id="<?= htmlspecialchars((string) $competencyId, ENT_QUOTES, 'UTF-8'); ?>">افزودن</button>
                                                    </td>
                                                    <td><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($dimensionName !== '' ? $dimensionName : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-gray-500 py-24">هیچ شایستگی‌ای یافت نشد. ابتدا شایستگی‌های سازمان را ثبت کنید.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div id="selectedCompetenciesContainer"></div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ذخیره تغییرات</span>
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-models'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
