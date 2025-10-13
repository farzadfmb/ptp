<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

AuthHelper::startSession();

$title = $title ?? 'ویرایش ویژگی تیپ MBTI';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$mbtiType = $mbtiType ?? [];
$featureCategories = $featureCategories ?? [];
$featureRecord = $featureRecord ?? [];
$validationErrors = $validationErrors ?? [];
$oldInput = $oldInput ?? [];
$selectedCategoryKey = isset($selectedCategoryKey) ? (string) $selectedCategoryKey : '';
$errorMessage = $errorMessage ?? null;

$mbtiTypeId = (int) ($mbtiType['id'] ?? 0);
$featureId = (int) ($featureRecord['id'] ?? 0);
$typeCode = (string) ($mbtiType['type_code'] ?? '');
$typeTitle = (string) ($mbtiType['title'] ?? '');
$categoryQuery = $selectedCategoryKey !== '' ? '&tab=' . urlencode($selectedCategoryKey) : '';
$backUrl = UtilityHelper::baseUrl('organizations/tools/mbti-settings/features?id=' . urlencode((string) $mbtiTypeId) . $categoryQuery);

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .card-rounded {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
        background: #ffffff;
    }
    .mbti-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 9999px;
        background: rgba(14, 116, 144, 0.12);
        color: #0f766e;
        padding: 6px 14px;
        font-size: 0.82rem;
    }
CSS;

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card card-rounded shadow-sm border-0 p-24">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-16 mb-20">
                        <div>
                            <h2 class="mb-6 text-gray-900">ویرایش ویژگی ثبت‌شده</h2>
                            <p class="text-gray-500 mb-0">تغییرات لازم را اعمال کنید و سپس ذخیره نمایید.</p>
                        </div>
                        <a href="<?= $backUrl; ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت به ویژگی‌ها">
                            بازگشت به ویژگی‌ها
                            <ion-icon name="arrow-undo-outline"></ion-icon>
                        </a>
                    </div>

                    <div class="row g-16 mb-24">
                        <div class="col-12 col-lg-4">
                            <div class="bg-gray-50 rounded-20 p-16 h-100">
                                <div class="mb-12">
                                    <span class="mbti-badge">
                                        <ion-icon name="finger-print-outline"></ion-icon>
                                        تیپ مرتبط
                                    </span>
                                </div>
                                <h3 class="text-teal-800 fw-bold mb-8">
                                    <?= htmlspecialchars($typeCode !== '' ? $typeCode : '---', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                                <p class="text-gray-600 mb-0">
                                    <?= htmlspecialchars($typeTitle !== '' ? $typeTitle : 'عنوانی ثبت نشده است.', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-lg-8">
                            <?php if (!empty($errorMessage)): ?>
                                <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12 mb-16" role="alert">
                                    <ion-icon name="warning-outline"></ion-icon>
                                    <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php
                                $formAction = UtilityHelper::baseUrl('organizations/tools/mbti-settings/features/update');
                                $submitLabel = 'ذخیره تغییرات';
                                $cancelUrl = $backUrl;
                                $isEdit = true;
                                include __DIR__ . '/feature-form-fields.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
